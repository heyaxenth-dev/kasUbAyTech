"""
adaptive_assessment_v2.py
Adaptive Assessment Service v2.0

Features:
- Connection pooling for MySQL
- In-memory caching of questions/options (auto-refresh)
- Clean scoring formula (normalized 0-100)
- Diagnostic-first flow (configurable count)
- Category-switching based on errors & uncertainty
- Confidence-based stopping (top vs second gap + threshold)
- Max-question limit
- Utility-based fallback (variance + distinction + information gain approx)
- Clear JSON responses for frontend consumption

Adjust DB_CONFIG and runtime parameters below as needed.
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
from mysql.connector import pooling, Error
from typing import List, Dict, Any, Optional, Tuple
from dataclasses import dataclass, field
import math
import time
import threading
import random

app = Flask(__name__)
CORS(app)

# ------------- CONFIG -------------
DB_CONFIG = {
    "host": "localhost",
    "database": "kasubaytech_catlite_db",  # Updated to CAT-lite database
    "user": "root",
    "password": "",
    "pool_name": "adaptive_pool",
    "pool_size": 5,
    "autocommit": True
}

# Algorithm tuning - Re-evaluated Adaptive Algorithm (CAT-lite)
PHASE_1_QUESTIONS = 5               # Questions 1-5: Diagnostic phase (mixed IT/IS/CS)
PHASE_2_QUESTIONS = 10              # Questions 6-10: Adaptive Round 1
PHASE_3_QUESTIONS = 20              # Questions 11-20: Adaptive Round 2
MAX_QUESTIONS = 20                  # hard cap for the assessment
CONFIDENCE_THRESHOLD = 0.80        # 0..1 - if top score >= this and gap >= GAP_THRESHOLD => stop
GAP_THRESHOLD = 0.15               # minimum relative gap between top and second (15%)
SCORE_SCALE = 100.0                # scale normalized scores to 0-100
CACHE_TTL = 300                    # seconds to refresh cached questions/options

# Legacy constant for backward compatibility (now Phase 1 uses 5 questions)
DIAGNOSTIC_COUNT = 5               # Updated to match Phase 1 requirement
# -----------------------------------

# ------------- DB POOL -------------
try:
    db_pool = pooling.MySQLConnectionPool(
        pool_name=DB_CONFIG["pool_name"],
        pool_size=DB_CONFIG["pool_size"],
        **{k: v for k, v in DB_CONFIG.items() if k not in ("pool_name", "pool_size")}
    )
except Exception as e:
    # Fall back to single connection factory if pool fails
    db_pool = None
    print("Warning: DB pool initialization failed:", e)


def get_conn():
    if db_pool:
        return db_pool.get_connection()
    return mysql.connector.connect(**DB_CONFIG)
# -----------------------------------


# ------------- CACHING -------------
@dataclass
class QuestionOption:
    id: int
    option_text: str
    it_score: float
    cs_score: float
    is_score: float


@dataclass
class Question:
    id: int
    question_text: str
    question_type: str    # 'single' or 'multiple' or 'text' - we use 'single'/'multiple'
    category: str         # 'DIAGNOSTIC' or 'ADAPTIVE' (exam phase)
    course_tag: str       # 'IT', 'IS', or 'CS' (course identity) - CAT-lite structure
    is_correct_answer: Optional[int] = None
    order_number: int = 0
    options: List[QuestionOption] = field(default_factory=list)


_cache_lock = threading.Lock()
_cache_timestamp = 0.0
_cached_questions: Dict[int, Question] = {}
_cached_question_order: List[int] = []


def refresh_cache_if_needed():
    global _cache_timestamp, _cached_questions, _cached_question_order
    now = time.time()
    with _cache_lock:
        if now - _cache_timestamp < CACHE_TTL and _cached_questions:
            return  # still fresh

        conn = None
        try:
            conn = get_conn()
            cursor = conn.cursor(dictionary=True)

            # Fetch questions with course_tag (CAT-lite structure)
            cursor.execute("""
                SELECT id, question_text, question_type, category, course_tag, is_correct_answer, order_number
                FROM questions
                WHERE is_active = 1
                ORDER BY order_number, id
            """)
            qrows = cursor.fetchall()

            questions_tmp: Dict[int, Question] = {}
            qids = [r['id'] for r in qrows]

            # Fetch options for these questions in one query
            if qids:
                format_ids = ",".join(["%s"] * len(qids))
                cursor.execute(f"""
                    SELECT id, question_id, option_text, it_score, cs_score, is_score
                    FROM answer_options
                    WHERE question_id IN ({format_ids})
                    ORDER BY id
                """, tuple(qids))
                orows = cursor.fetchall()
            else:
                orows = []

            # Build questions map
            for r in qrows:
                q = Question(
                    id=int(r['id']),
                    question_text=r['question_text'] or "",
                    question_type=r.get('question_type', 'single') or 'single',
                    category=(r.get('category') or 'DIAGNOSTIC').upper(),
                    course_tag=(r.get('course_tag') or 'IT').upper(),  # CAT-lite: course identity
                    is_correct_answer=(int(r['is_correct_answer']) if r.get('is_correct_answer') is not None else None),
                    order_number=int(r.get('order_number') or 0),
                    options=[]
                )
                questions_tmp[q.id] = q

            # Attach options
            for o in orows:
                qid = int(o['question_id'])
                if qid not in questions_tmp:
                    continue
                opt = QuestionOption(
                    id=int(o['id']),
                    option_text=o['option_text'] or '',
                    it_score=float(o.get('it_score') or 0.0),
                    cs_score=float(o.get('cs_score') or 0.0),
                    is_score=float(o.get('is_score') or 0.0)
                )
                questions_tmp[qid].options.append(opt)

            # finalize cache
            _cached_questions = questions_tmp
            _cached_question_order = [int(r['id']) for r in qrows]
            _cache_timestamp = now

        except Exception as e:
            print("Error refreshing cache:", e)
        finally:
            if conn:
                try:
                    conn.close()
                except:
                    pass


def get_all_question_ids() -> List[int]:
    refresh_cache_if_needed()
    return list(_cached_question_order)


def get_question_by_id(qid: int) -> Optional[Question]:
    refresh_cache_if_needed()
    return _cached_questions.get(int(qid))
# -----------------------------------


# ------------- SCORING UTILITIES -------------
def normalize_scores(raw: Dict[str, float]) -> Dict[str, float]:
    """
    raw scores are averages of option-level scores in some 0..maxRange.
    We just clamp and scale them to 0..SCORE_SCALE (e.g., 0..100)
    """
    # Ensure numeric
    it = max(0.0, float(raw.get('IT', 0.0)))
    cs = max(0.0, float(raw.get('CS', 0.0)))
    isv = max(0.0, float(raw.get('IS', 0.0)))

    # Optional: if you know the theoretical max (e.g., options scores in 0..5), you can scale accordingly.
    # Here we assume option scores are in 0..5 and scale by 20 as before; but if different, change this factor.
    # To make this robust, check max observed. For simplicity, we'll scale by 20 (so 5 -> 100).
    SCALE_FACTOR = 20.0
    return {
        'IT': round(min(SCORE_SCALE, it * SCALE_FACTOR), 2),
        'CS': round(min(SCORE_SCALE, cs * SCALE_FACTOR), 2),
        'IS': round(min(SCORE_SCALE, isv * SCALE_FACTOR), 2)
    }


def compute_raw_scores(answered_questions: List[Dict[str, Any]]) -> Dict[str, float]:
    """
    Returns raw average scores per category (unscaled). Each selected option contributes its per-course scores.
    For multiple selected options (checkbox), we average the chosen options for that question.
    """
    refresh_cache_if_needed()

    totals = {'IT': 0.0, 'CS': 0.0, 'IS': 0.0}
    count = 0

    for ans in answered_questions:
        qid = ans.get('question_id')
        selected = ans.get('option_ids', [])
        if not qid or not selected:
            continue
        q = get_question_by_id(int(qid))
        if not q:
            continue

        # For a question with multiple selected options, average their scores
        sel_opts = []
        for opt_id in selected:
            for opt in q.options:
                if opt.id == int(opt_id):
                    sel_opts.append(opt)
                    break

        if not sel_opts:
            continue

        # average per-course scores for selected options
        avg_it = sum(o.it_score for o in sel_opts) / len(sel_opts)
        avg_cs = sum(o.cs_score for o in sel_opts) / len(sel_opts)
        avg_is = sum(o.is_score for o in sel_opts) / len(sel_opts)

        totals['IT'] += avg_it
        totals['CS'] += avg_cs
        totals['IS'] += avg_is
        count += 1

    if count == 0:
        return {'IT': 0.0, 'CS': 0.0, 'IS': 0.0}

    return {'IT': totals['IT'] / count, 'CS': totals['CS'] / count, 'IS': totals['IS'] / count}
# -----------------------------------


# ------------- UTILITY/INFORMATION METRICS -------------
def variance(values: List[float]) -> float:
    if not values:
        return 0.0
    mean = sum(values) / len(values)
    return sum((x - mean) ** 2 for x in values) / len(values)


def approx_information_gain(current_scores_norm: Dict[str, float], option_scores: List[Tuple[float, float, float]]) -> float:
    """
    Approximate how much an option set would change the distribution.
    - current_scores_norm: normalized 0..1 mapping
    - option_scores: list of (it, cs, is) raw scores for the option set
    We'll compute a simple expected KL-ish measure:
        For each option, compute new distribution if the user picked that option (weighted),
        then compute L1 or KL difference vs current distribution, and average.
    """
    # Normalize current to sum=1
    cs = {k: max(0.0, current_scores_norm.get(k, 0.0)) for k in ['IT', 'CS', 'IS']}
    total = cs['IT'] + cs['CS'] + cs['IS'] or 1.0
    cur_dist = [cs['IT'] / total, cs['CS'] / total, cs['IS'] / total]

    diffs = []
    for itv, csv, isv in option_scores:
        # convert option scores to same normalized scale (assuming 0..5 -> 0..1)
        # if scores are in 0..5, divide by 5. We don't strictly know max; assume 5 as before.
        MAX_OPT = 5.0
        od = [itv / MAX_OPT, csv / MAX_OPT, isv / MAX_OPT]
        o_total = sum(od) or 1.0
        od_norm = [x / o_total for x in od]
        # simple L1 diff
        diffs.append(sum(abs(a - b) for a, b in zip(cur_dist, od_norm)))

    return (sum(diffs) / len(diffs)) if diffs else 0.0
# -----------------------------------


# ------------- COURSE RANKING UTILITIES -------------
def determine_course_rankings(scores: Dict[str, float]) -> Dict[str, Any]:
    """
    Determine dominant, secondary, and weakest courses from current scores.
    Supports ties - if two courses are tied, both are marked as dominant.
    
    Returns:
        {
            'dominant': ['IT'] or ['IT', 'CS'] if tied,
            'secondary': 'CS' or 'IS',
            'weakest': 'IS',
            'has_tie': bool,
            'tied_courses': [] if no tie, or list of tied courses
        }
    """
    sorted_scores = sorted(scores.items(), key=lambda x: x[1], reverse=True)
    
    if len(sorted_scores) < 3:
        # Fallback if not all courses have scores
        return {
            'dominant': [sorted_scores[0][0]] if sorted_scores else ['IT'],
            'secondary': sorted_scores[1][0] if len(sorted_scores) > 1 else 'CS',
            'weakest': sorted_scores[2][0] if len(sorted_scores) > 2 else 'IS',
            'has_tie': False,
            'tied_courses': []
        }
    
    top_course, top_score = sorted_scores[0]
    second_course, second_score = sorted_scores[1]
    third_course, third_score = sorted_scores[2]
    
    # Check for ties (within 0.1 point difference to account for rounding)
    TIE_THRESHOLD = 0.1
    dominant = [top_course]
    has_tie = False
    tied_courses = []
    
    # Check if top and second are tied
    if abs(top_score - second_score) <= TIE_THRESHOLD:
        dominant.append(second_course)
        has_tie = True
        tied_courses = [top_course, second_course]
        # If all three are tied (rare)
        if abs(second_score - third_score) <= TIE_THRESHOLD:
            dominant.append(third_course)
            tied_courses = [top_course, second_course, third_course]
            return {
                'dominant': dominant,
                'secondary': second_course,  # Still need to pick one for secondary
                'weakest': third_course,
                'has_tie': True,
                'tied_courses': tied_courses
            }
        # Two-way tie at top
        return {
            'dominant': dominant,
            'secondary': second_course,  # In two-way tie, second is also dominant
            'weakest': third_course,
            'has_tie': True,
            'tied_courses': tied_courses
        }
    
    # Check if second and third are tied (but not top)
    if abs(second_score - third_score) <= TIE_THRESHOLD:
        # Top is clear dominant, second and third are tied
        return {
            'dominant': dominant,
            'secondary': second_course,  # Pick one from the tie
            'weakest': third_course,
            'has_tie': False,  # No tie at top level
            'tied_courses': []
        }
    
    # No ties
    return {
        'dominant': dominant,
        'secondary': second_course,
        'weakest': third_course,
        'has_tie': has_tie,
        'tied_courses': tied_courses
    }


def get_phase_from_question_count(answered_count: int) -> str:
    """
    Determine which phase we're in based on number of answered questions.
    
    Phase 1: Questions 1-5 (0-4 answered)
    Phase 2: Questions 6-10 (5-9 answered)
    Phase 3: Questions 11-20 (10-19 answered)
    """
    if answered_count < PHASE_1_QUESTIONS:
        return 'PHASE_1'
    elif answered_count < PHASE_2_QUESTIONS:
        return 'PHASE_2'
    else:
        return 'PHASE_3'


def count_questions_by_category_in_phase(answered_questions: List[Dict[str, Any]], 
                                         phase_start: int, phase_end: int,
                                         target_category: str) -> int:
    """
    Count how many questions of a specific category have been asked in a given phase.
    """
    count = 0
    phase_answers = answered_questions[phase_start:phase_end]
    
    for ans in phase_answers:
        qid = ans.get('question_id')
        if qid:
            q = get_question_by_id(int(qid))
            # CAT-lite: Check course_tag instead of category
            if q and hasattr(q, 'course_tag') and q.course_tag == target_category:
                count += 1
    
    return count
# -----------------------------------


# ------------- UTILITY: SELECT NEXT QUESTION -------------
def get_diagnostic_question_category_bias(q: Question) -> Optional[str]:
    """
    Determine which category a diagnostic question favors by analyzing option scores.
    Returns 'IT', 'CS', 'IS', or None if balanced/unclear.
    A question favors a category if its options have significantly higher scores for that category.
    """
    if not q.options:
        return None
    
    # Calculate average scores per category across all options
    avg_it = sum(o.it_score for o in q.options) / len(q.options)
    avg_cs = sum(o.cs_score for o in q.options) / len(q.options)
    avg_is = sum(o.is_score for o in q.options) / len(q.options)
    
    # Find the category with the highest average score
    scores = {'IT': avg_it, 'CS': avg_cs, 'IS': avg_is}
    max_cat = max(scores.items(), key=lambda x: x[1])
    
    # Check if the max is significantly higher than others (at least 0.5 point difference)
    # This ensures we only classify questions that clearly favor a category
    sorted_scores = sorted(scores.items(), key=lambda x: x[1], reverse=True)
    if len(sorted_scores) >= 2:
        top_score = sorted_scores[0][1]
        second_score = sorted_scores[1][1]
        if top_score - second_score >= 0.5:
            return max_cat[0]
    
    return None


def select_next_question_logic(answered_questions: List[Dict[str, Any]], all_question_ids: Optional[List[int]] = None) -> Optional[Dict[str, Any]]:
    """
    Re-evaluated Adaptive Algorithm (CAT-lite) - Phase-based selection logic:
    
    Phase 1 (Questions 1-5): Diagnostic phase
    - Mixed IT/IS/CS questions
    - Each question has a course_tag
    - Correct answer → +1 to that course
    - Do NOT lock dominance yet
    - Allow ties
    
    Phase 2 (Questions 6-10): Adaptive Round 1
    - 3 dominant, 1 secondary, 1 weakest
    - Continue accumulating scores
    - Re-evaluation checkpoint after Q10
    
    Phase 3 (Questions 11-20): Adaptive Round 2
    - If one dominant: 6 dominant, 3 secondary, 1 weakest
    - If tie between two: 5 questions each, 1 question for third
    - Continue scoring normally
    """

    refresh_cache_if_needed()
    all_qids = all_question_ids or get_all_question_ids()
    answered_ids = [int(q.get('question_id')) for q in answered_questions if q.get('question_id')]
    answered_count = len(answered_questions)

    # Compute current scores
    raw_scores = compute_raw_scores(answered_questions)
    norm_scores = normalize_scores(raw_scores)  # 0..100
    norm_scores_01 = {k: v / SCORE_SCALE for k, v in norm_scores.items()}  # 0..1 scale

    # Stopping rules check
    sorted_scores = sorted(norm_scores_01.items(), key=lambda x: x[1], reverse=True)
    top_course, top_score = (sorted_scores[0] if sorted_scores else ('IT', 0.0))
    second_course, second_score = (sorted_scores[1] if len(sorted_scores) > 1 else (None, 0.0))
    gap = top_score - second_score if second_course else top_score

    # If criteria met: stop and return recommendation
    if (top_score >= CONFIDENCE_THRESHOLD and gap >= GAP_THRESHOLD) or answered_count >= MAX_QUESTIONS:
        return {
            "stop": True,
            "reason": "confidence" if top_score >= CONFIDENCE_THRESHOLD and gap >= GAP_THRESHOLD else "max_questions",
            "scores": norm_scores,
            "recommended_course": top_course
        }

    # Determine current phase
    current_phase = get_phase_from_question_count(answered_count)

    # ========== PHASE 1: Diagnostic (Questions 1-5) ==========
    if current_phase == 'PHASE_1':
        # Phase 1: Mixed IT/IS/CS diagnostic questions
        # Track which categories have been covered by diagnostic questions
        covered_categories = set()
        for q in answered_questions:
            qid = q.get('question_id')
            qobj = get_question_by_id(int(qid)) if qid else None
            if qobj and qobj.category == 'DIAGNOSTIC':
                bias = get_diagnostic_question_category_bias(qobj)
                if bias:
                    covered_categories.add(bias)
        
        # Collect available diagnostic questions
        available_diag = []
        for qid in all_qids:
            q = get_question_by_id(int(qid))
            if not q or q.id in answered_ids:
                continue
            if q.category == 'DIAGNOSTIC':
                bias = get_diagnostic_question_category_bias(q)
                available_diag.append((q, bias))
        
        # Try to ensure balanced coverage (one of each IT/CS/IS)
        target_categories = ['IT', 'CS', 'IS']
        needed_categories = [cat for cat in target_categories if cat not in covered_categories]
        
        if needed_categories:
            for target_cat in needed_categories:
                for q, bias in available_diag:
                    if bias == target_cat:
                        return {
                            "stop": False,
                            "next_question_id": q.id,
                            "current_scores": norm_scores,
                            "reason": f"phase1_diagnostic_{target_cat}"
                        }
        
        # If all categories covered or no match, pick any available diagnostic
        if available_diag:
            q, _ = available_diag[0]
            return {
                "stop": False,
                "next_question_id": q.id,
                "current_scores": norm_scores,
                "reason": "phase1_diagnostic"
            }
        
        # No more diagnostic questions - fall through to next phase logic

    # ========== PHASE 2: Adaptive Round 1 (Questions 6-10) ==========
    if current_phase == 'PHASE_2':
        # Determine course rankings (dominant, secondary, weakest)
        rankings = determine_course_rankings(norm_scores_01)
        dominant_courses = rankings['dominant']
        secondary_course = rankings['secondary']
        weakest_course = rankings['weakest']
        
        # Count questions already asked in Phase 2 (questions 6-10, index 5-9)
        phase2_start = PHASE_1_QUESTIONS  # 5
        phase2_end = PHASE_2_QUESTIONS     # 10
        phase2_answered = answered_count - phase2_start
        
        # Target distribution: 3 dominant, 1 secondary, 1 weakest
        dominant_count = count_questions_by_category_in_phase(answered_questions, phase2_start, answered_count, dominant_courses[0])
        secondary_count = count_questions_by_category_in_phase(answered_questions, phase2_start, answered_count, secondary_course)
        weakest_count = count_questions_by_category_in_phase(answered_questions, phase2_start, answered_count, weakest_course)
        
        # Determine which category to select next based on distribution
        target_category = None
        if dominant_count < 3:
            target_category = dominant_courses[0]  # Pick first if multiple dominant
        elif secondary_count < 1:
            target_category = secondary_course
        elif weakest_count < 1:
            target_category = weakest_course
        else:
            # All targets met, default to dominant
            target_category = dominant_courses[0]
        
        # Find available adaptive questions for target course_tag
        candidate_ids: List[int] = []
        for qid in all_qids:
            qid_int = int(qid)
            if qid_int in answered_ids:
                continue
            q = get_question_by_id(qid_int)
            if not q:
                continue
            # CAT-lite: Check category='ADAPTIVE' and course_tag matches
            if q.category == 'ADAPTIVE' and hasattr(q, 'course_tag') and q.course_tag == target_category:
                candidate_ids.append(q.id)
        
        if candidate_ids:
            random.shuffle(candidate_ids)
            next_id = candidate_ids[0]
            q_next = get_question_by_id(next_id)
            if q_next:
                return {
                    "stop": False,
                    "next_question_id": q_next.id,
                    "current_scores": norm_scores,
                    "reason": f"phase2_adaptive_{target_category}"
                }

    # ========== RE-EVALUATION CHECKPOINT (After Question 10) ==========
    # After question 10, we recalculate dominance - this happens automatically
    # when we enter Phase 3, as we'll recompute rankings based on all answers

    # ========== PHASE 3: Adaptive Round 2 (Questions 11-20) ==========
    if current_phase == 'PHASE_3':
        # Re-evaluate course rankings based on all answers (including Phase 1 & 2)
        rankings = determine_course_rankings(norm_scores_01)
        dominant_courses = rankings['dominant']
        secondary_course = rankings['secondary']
        weakest_course = rankings['weakest']
        has_tie = rankings['has_tie']
        
        # Count questions already asked in Phase 3 (questions 11-20, index 10-19)
        phase3_start = PHASE_2_QUESTIONS  # 10
        phase3_answered = answered_count - phase3_start
        
        # Determine target distribution based on ties
        if has_tie and len(dominant_courses) == 2:
            # Two-way tie: 5 questions each for the two tied courses, 1 for third
            tied_course1, tied_course2 = dominant_courses[0], dominant_courses[1]
            tied1_count = count_questions_by_category_in_phase(answered_questions, phase3_start, answered_count, tied_course1)
            tied2_count = count_questions_by_category_in_phase(answered_questions, phase3_start, answered_count, tied_course2)
            weakest_count = count_questions_by_category_in_phase(answered_questions, phase3_start, answered_count, weakest_course)
            
            # Determine which category to select
            target_category = None
            if tied1_count < 5:
                target_category = tied_course1
            elif tied2_count < 5:
                target_category = tied_course2
            elif weakest_count < 1:
                target_category = weakest_course
            else:
                # All targets met, default to first tied course
                target_category = tied_course1
        else:
            # One dominant course: 6 dominant, 3 secondary, 1 weakest
            dominant_count = count_questions_by_category_in_phase(answered_questions, phase3_start, answered_count, dominant_courses[0])
            secondary_count = count_questions_by_category_in_phase(answered_questions, phase3_start, answered_count, secondary_course)
            weakest_count = count_questions_by_category_in_phase(answered_questions, phase3_start, answered_count, weakest_course)
            
            # Determine which category to select
            target_category = None
            if dominant_count < 6:
                target_category = dominant_courses[0]
            elif secondary_count < 3:
                target_category = secondary_course
            elif weakest_count < 1:
                target_category = weakest_course
            else:
                # All targets met, default to dominant
                target_category = dominant_courses[0]
        
        # Find available adaptive questions for target course_tag
        candidate_ids: List[int] = []
        for qid in all_qids:
            qid_int = int(qid)
            if qid_int in answered_ids:
                continue
            q = get_question_by_id(qid_int)
            if not q:
                continue
            # CAT-lite: Check category='ADAPTIVE' and course_tag matches
            if q.category == 'ADAPTIVE' and hasattr(q, 'course_tag') and q.course_tag == target_category:
                candidate_ids.append(q.id)
        
        if candidate_ids:
            random.shuffle(candidate_ids)
            next_id = candidate_ids[0]
            q_next = get_question_by_id(next_id)
            if q_next:
                return {
                    "stop": False,
                    "next_question_id": q_next.id,
                    "current_scores": norm_scores,
                    "reason": f"phase3_adaptive_{target_category}"
                }

    # Fallback: utility-based selection across all unanswered questions
    # Track recent question categories to avoid repetition
    recent_categories = []
    category_question_counts = {'IT': 0, 'CS': 0, 'IS': 0}
    
    if answered_questions:
        # Get the last 2-3 questions' course_tags to avoid repetition
        # CAT-lite: Use course_tag instead of category for course identification
        for ans in answered_questions[-3:]:
            qid = ans.get('question_id')
            if qid:
                q = get_question_by_id(int(qid))
                if q and hasattr(q, 'course_tag') and q.course_tag in ['IT', 'CS', 'IS']:
                    recent_categories.append(q.course_tag)
                    category_question_counts[q.course_tag] = category_question_counts.get(q.course_tag, 0) + 1
    
    current_scores_for_metrics = norm_scores_01  # 0..1
    utilities = []
    # Shuffle question order for the utility-based phase so that non-diagnostic
    # questions are not always traversed in the same global order between users.
    shuffled_qids = list(all_qids)
    random.shuffle(shuffled_qids)
    for qid in shuffled_qids:
        qid_int = int(qid)
        if qid_int in answered_ids:
            continue
        q = get_question_by_id(qid_int)
        if not q or not q.options:
            continue
        # Keep diagnostic ("controlled") questions out of the utility-based pool;
        # they are handled deterministically in the earlier diagnostic phase.
        if q.category == 'DIAGNOSTIC':
            continue
        
        # Skip if this course_tag was asked in the last 2 questions (unless it's the only option)
        # CAT-lite: Use course_tag instead of category
        course_tag = q.course_tag if hasattr(q, 'course_tag') else None
        if course_tag and course_tag in recent_categories[-2:]:
            # Apply a penalty but don't exclude completely
            category_penalty = 0.15
        else:
            category_penalty = 0.0

        # gather option score arrays
        opt_scores = [(o.it_score, o.cs_score, o.is_score) for o in q.options]

        # variance across options
        it_var = variance([o[0] for o in opt_scores])
        cs_var = variance([o[1] for o in opt_scores])
        is_var = variance([o[2] for o in opt_scores])
        avg_var = (it_var + cs_var + is_var) / 3.0

        # distinction (how different options are between top two current courses)
        # identify top two course keys
        sorted_courses_local = sorted(current_scores_for_metrics.items(), key=lambda x: x[1], reverse=True)
        top_k = sorted_courses_local[0][0]
        second_k = sorted_courses_local[1][0] if len(sorted_courses_local) > 1 else None
        # map to tuple index
        map_idx = {'IT': 0, 'CS': 1, 'IS': 2}
        t_idx = map_idx[top_k]
        s_idx = map_idx[second_k] if second_k else (t_idx ^ 1)
        distinction_vals = [abs(o[t_idx] - o[s_idx]) for o in opt_scores]
        avg_distinction = sum(distinction_vals) / len(distinction_vals)

        # approx info gain
        info_gain = approx_information_gain(current_scores_for_metrics, opt_scores)

        # Combine with weights (tunable)
        # Give more weight to distinction and information gain
        base_utility = (avg_var * 0.25) + (avg_distinction * 0.45) + (info_gain * 0.30)
        
        # Apply category diversity bonus/penalty
        # CAT-lite: Use course_tag instead of category
        course_tag = q.course_tag if hasattr(q, 'course_tag') else None
        category_count = category_question_counts.get(course_tag, 0) if course_tag in ['IT', 'CS', 'IS'] else 0
        diversity_bonus = 0.05 if category_count < 2 else 0.0
        
        utility = base_utility * (1.0 - category_penalty) + diversity_bonus
        # CAT-lite: Store course_tag for reference
        course_tag = q.course_tag if hasattr(q, 'course_tag') else 'UNKNOWN'
        utilities.append((qid_int, utility, course_tag))

    if not utilities:
        # No unanswered questions left -> stop
        return {
            "stop": True,
            "reason": "no_questions_left",
            "scores": norm_scores,
            "recommended_course": top_course
        }

    utilities.sort(key=lambda x: x[1], reverse=True)
    selected_qid, selected_utility, selected_category = utilities[0]

    return {
        "stop": False,
        "next_question_id": int(selected_qid),
        "current_scores": norm_scores,
        "utility_score": round(float(selected_utility), 6),
        "reason": "utility"
    }
# -----------------------------------


# ------------- API ENDPOINTS -------------
@app.route("/get_next_question", methods=["POST"])
def api_get_next_question():
    """
    Expected JSON:
    {
        "answered_questions": [
            {"question_id": 1, "option_ids": [1]} , ...
        ],
        "all_question_ids": [1,2,3]  // optional
    }
    """
    try:
        data = request.get_json(force=True) or {}
        answered_questions = data.get("answered_questions", []) or []
        all_qids = data.get("all_question_ids", None)

        decision = select_next_question_logic(answered_questions, all_qids)

        if decision is None:
            return jsonify({"success": False, "error": "Internal error selecting next question"}), 500

        if decision.get("stop"):
            return jsonify({
                "success": True,
                "stop": True,
                "reason": decision.get("reason"),
                "scores": decision.get("scores"),
                "recommended_course": decision.get("recommended_course")
            })

        # Fetch question details
        next_qid = int(decision.get("next_question_id"))
        qobj = get_question_by_id(next_qid)
        if not qobj:
            return jsonify({"success": False, "error": "Question not found"}), 404

        # Build response
        question_payload = {
            "question_id": qobj.id,
            "question_text": qobj.question_text,
            "question_type": qobj.question_type,
            "category": qobj.category,
            "options": [
                {
                    "id": opt.id,
                    "option_text": opt.option_text,
                    # do not necessarily need to return scores, but we include for frontend debug if desired
                    "it_score": opt.it_score,
                    "cs_score": opt.cs_score,
                    "is_score": opt.is_score
                }
                for opt in qobj.options
            ],
            "current_scores": decision.get("current_scores"),
            "utility_score": decision.get("utility_score", None),
            "reason": decision.get("reason")
        }
        return jsonify({"success": True, "stop": False, "question": question_payload})

    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({"success": False, "error": str(e)}), 500


@app.route("/calculate_scores", methods=["POST"])
def api_calculate_scores():
    """
    Expected JSON:
    {
        "answered_questions": [ {"question_id": 1, "option_ids": [1]}, ... ]
    }
    """
    try:
        data = request.get_json(force=True) or {}
        answered_questions = data.get("answered_questions", []) or []
        raw = compute_raw_scores(answered_questions)
        normalized = normalize_scores(raw)
        # recommended course
        sorted_scores = sorted(normalized.items(), key=lambda x: x[1], reverse=True)
        recommended = sorted_scores[0][0] if sorted_scores else "IT"
        return jsonify({"success": True, "scores": normalized, "recommended_course": recommended})
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "healthy", "service": "adaptive_assessment_v2"})


if __name__ == "__main__":
    # refresh cache immediately at startup
    refresh_cache_if_needed()
    app.run(host="0.0.0.0", port=5000, debug=True)

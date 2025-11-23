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

app = Flask(__name__)
CORS(app)

# ------------- CONFIG -------------
DB_CONFIG = {
    "host": "localhost",
    "database": "kasubaytech_db",
    "user": "root",
    "password": "",
    "pool_name": "adaptive_pool",
    "pool_size": 5,
    "autocommit": True
}

# Algorithm tuning
DIAGNOSTIC_COUNT = 3               # number of diagnostic questions to ask first
MAX_QUESTIONS = 20                 # hard cap for the assessment
CONFIDENCE_THRESHOLD = 0.80        # 0..1 - if top score >= this and gap >= GAP_THRESHOLD => stop
GAP_THRESHOLD = 0.15               # minimum relative gap between top and second (15%)
SCORE_SCALE = 100.0                # scale normalized scores to 0-100
CACHE_TTL = 300                    # seconds to refresh cached questions/options
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
    category: str         # 'DIAGNOSTIC'/'IT'/'CS'/'IS'
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

            # Fetch questions
            cursor.execute("""
                SELECT id, question_text, question_type, category, is_correct_answer, order_number
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


# ------------- UTILITY: SELECT NEXT QUESTION -------------
def select_next_question_logic(answered_questions: List[Dict[str, Any]], all_question_ids: Optional[List[int]] = None) -> Optional[Dict[str, Any]]:
    """
    Core decision logic:
    1. If no questions answered or diagnostic count < DIAGNOSTIC_COUNT -> pick next DIAGNOSTIC
    2. Compute category performance and identify categories with high wrong ratio
    3. If any category needs testing (wrong ratio > threshold), prefer that category
    4. Else use utility ranking (variance + distinction + approx info gain)
    5. Apply stopping rules: confidence threshold or max questions reached
    """

    refresh_cache_if_needed()
    all_qids = all_question_ids or get_all_question_ids()
    answered_ids = [int(q.get('question_id')) for q in answered_questions if q.get('question_id')]

    # 1) Determine diagnostic progress
    # diagnostic questions are those with category 'DIAGNOSTIC'
    diag_answered = 0
    for q in answered_questions:
        qid = q.get('question_id')
        qobj = get_question_by_id(int(qid)) if qid else None
        if qobj and qobj.category == 'DIAGNOSTIC':
            diag_answered += 1

    # Quick compute raw + normalized scores
    raw_scores = compute_raw_scores(answered_questions)
    norm_scores = normalize_scores(raw_scores)  # 0..100
    # For confidence math, use 0..1 scale
    norm_scores_01 = {k: v / SCORE_SCALE for k, v in norm_scores.items()}

    # Stopping rules check
    #  - If answered count >= 1, check gap between top and second
    sorted_scores = sorted(norm_scores_01.items(), key=lambda x: x[1], reverse=True)
    top_course, top_score = (sorted_scores[0] if sorted_scores else ('IT', 0.0))
    second_course, second_score = (sorted_scores[1] if len(sorted_scores) > 1 else (None, 0.0))
    gap = top_score - second_score if second_course else top_score

    # If criteria met: stop and return recommendation
    if (top_score >= CONFIDENCE_THRESHOLD and gap >= GAP_THRESHOLD) or len(answered_questions) >= MAX_QUESTIONS:
        return {
            "stop": True,
            "reason": "confidence" if top_score >= CONFIDENCE_THRESHOLD and gap >= GAP_THRESHOLD else "max_questions",
            "scores": norm_scores,
            "recommended_course": top_course
        }

    # If still in diagnostic phase -> select next diagnostic question
    if diag_answered < DIAGNOSTIC_COUNT:
        # find first DIAGNOSTIC question not answered
        for qid in all_qids:
            q = get_question_by_id(int(qid))
            if not q:
                continue
            if q.id in answered_ids:
                continue
            if q.category == 'DIAGNOSTIC':
                return {
                    "stop": False,
                    "next_question_id": q.id,
                    "current_scores": norm_scores,
                    "reason": "diagnostic"
                }
        # no more diagnostic available - fall through

    # Compute category performance to identify weak areas
    category_perf = {
        'IS': {'correct': 0, 'wrong': 0, 'total': 0},
        'IT': {'correct': 0, 'wrong': 0, 'total': 0},
        'CS': {'correct': 0, 'wrong': 0, 'total': 0},
    }
    # To evaluate correctness, we compare answered option(s) with question.is_correct_answer if available
    for ans in answered_questions:
        qid = ans.get('question_id')
        selected = ans.get('option_ids', [])
        if not qid:
            continue
        q = get_question_by_id(int(qid))
        if not q:
            continue
        cat = q.category if q.category in category_perf else 'DIAGNOSTIC'
        if cat == 'DIAGNOSTIC':
            continue
        category_perf[cat]['total'] += 1
        if q.is_correct_answer and int(q.is_correct_answer) in [int(s) for s in selected]:
            category_perf[cat]['correct'] += 1
        else:
            # treat unanswered or wrong as wrong
            category_perf[cat]['wrong'] += 1

    # Identify categories with high wrong ratios (>30%)
    categories_to_test = []
    for cat, stats in category_perf.items():
        if stats['total'] == 0:
            continue
        wrong_ratio = stats['wrong'] / stats['total']
        if wrong_ratio > 0.30:
            categories_to_test.append((cat, wrong_ratio))
    categories_to_test.sort(key=lambda x: x[1], reverse=True)

    if categories_to_test:
        # select first question from the top problematic category
        target_cat = categories_to_test[0][0]
        for qid in all_qids:
            if int(qid) in answered_ids:
                continue
            q = get_question_by_id(int(qid))
            if not q:
                continue
            if q.category == target_cat:
                return {
                    "stop": False,
                    "next_question_id": q.id,
                    "current_scores": norm_scores,
                    "reason": f"target_category_{target_cat}"
                }

    # Fallback: utility-based selection across all unanswered questions
    current_scores_for_metrics = norm_scores_01  # 0..1
    utilities = []
    for qid in all_qids:
        qid_int = int(qid)
        if qid_int in answered_ids:
            continue
        q = get_question_by_id(qid_int)
        if not q or not q.options:
            continue

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
        utility = (avg_var * 0.25) + (avg_distinction * 0.45) + (info_gain * 0.30)
        utilities.append((qid_int, utility))

    if not utilities:
        # No unanswered questions left -> stop
        return {
            "stop": True,
            "reason": "no_questions_left",
            "scores": norm_scores,
            "recommended_course": top_course
        }

    utilities.sort(key=lambda x: x[1], reverse=True)
    selected_qid, selected_utility = utilities[0]

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

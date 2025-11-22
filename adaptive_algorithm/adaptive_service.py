"""
Adaptive Assessment Algorithm for Course Compatibility
This service selects the next question based on current compatibility scores
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
from mysql.connector import Error
import json
import os
from typing import List, Dict, Tuple

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP requests

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'database': 'kasubaytech_db',
    'user': 'root',
    'password': ''
}

def get_db_connection():
    """Create database connection"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

def calculate_current_scores(answered_questions: List[Dict]) -> Dict[str, float]:
    """
    Calculate current compatibility scores based on answered questions
    
    Args:
        answered_questions: List of dicts with question_id, option_ids, and scores
    
    Returns:
        Dictionary with IT, CS, IS scores (0-100)
    """
    conn = get_db_connection()
    if not conn:
        return {'IT': 0, 'CS': 0, 'IS': 0}
    
    cursor = conn.cursor(dictionary=True)
    
    total_it = 0
    total_cs = 0
    total_is = 0
    total_weight = 0
    
    for answer in answered_questions:
        option_ids = answer.get('option_ids', [])
        if not option_ids:
            continue
            
        for option_id in option_ids:
            query = "SELECT it_score, cs_score, is_score FROM answer_options WHERE id = %s"
            cursor.execute(query, (option_id,))
            result = cursor.fetchone()
            
            if result:
                total_it += float(result['it_score'])
                total_cs += float(result['cs_score'])
                total_is += float(result['is_score'])
                total_weight += 1
    
    cursor.close()
    conn.close()
    
    if total_weight == 0:
        return {'IT': 0, 'CS': 0, 'IS': 0}
    
    # Scale to 0-100 (assuming max score per option is 5, so multiply by 20)
    it_score = (total_it / total_weight) * 20
    cs_score = (total_cs / total_weight) * 20
    is_score = (total_is / total_weight) * 20
    
    return {
        'IT': round(it_score, 2),
        'CS': round(cs_score, 2),
        'IS': round(is_score, 2)
    }

def calculate_question_utility(question_id: int, current_scores: Dict[str, float], 
                              answered_question_ids: List[int]) -> float:
    """
    Calculate utility score for a question based on:
    1. Variance in scores between courses (higher variance = more informative)
    2. Current score uncertainty (questions that help distinguish top courses)
    3. Whether question has been answered
    
    Args:
        question_id: ID of the question to evaluate
        current_scores: Current IT, CS, IS scores
        answered_question_ids: List of already answered question IDs
    
    Returns:
        Utility score (higher = better question to ask next)
    """
    if question_id in answered_question_ids:
        return -1  # Already answered
    
    conn = get_db_connection()
    if not conn:
        return 0
    
    cursor = conn.cursor(dictionary=True)
    
    # Get all options for this question
    query = """
        SELECT it_score, cs_score, is_score 
        FROM answer_options 
        WHERE question_id = %s
    """
    cursor.execute(query, (question_id,))
    options = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    if not options:
        return 0
    
    # Calculate variance in scores across options
    it_scores = [float(opt['it_score']) for opt in options]
    cs_scores = [float(opt['cs_score']) for opt in options]
    is_scores = [float(opt['is_score']) for opt in options]
    
    # Variance calculation
    def variance(values):
        if not values:
            return 0
        mean = sum(values) / len(values)
        return sum((x - mean) ** 2 for x in values) / len(values)
    
    it_var = variance(it_scores)
    cs_var = variance(cs_scores)
    is_var = variance(is_scores)
    
    # Average variance (higher = more informative)
    avg_variance = (it_var + cs_var + is_var) / 3
    
    # Calculate score differences between courses for each option
    score_differences = []
    for opt in options:
        it = float(opt['it_score'])
        cs = float(opt['cs_score'])
        is_val = float(opt['is_score'])
        
        # Calculate differences between courses
        diff_it_cs = abs(it - cs)
        diff_it_is = abs(it - is_val)
        diff_cs_is = abs(cs - is_val)
        
        score_differences.append(max(diff_it_cs, diff_it_is, diff_cs_is))
    
    avg_difference = sum(score_differences) / len(score_differences) if score_differences else 0
    
    # Identify top 2 courses based on current scores
    sorted_courses = sorted(current_scores.items(), key=lambda x: x[1], reverse=True)
    top_course = sorted_courses[0][0] if sorted_courses else 'IT'
    second_course = sorted_courses[1][0] if len(sorted_courses) > 1 else 'CS'
    
    # Calculate how well this question can distinguish between top 2 courses
    distinction_score = 0
    for opt in options:
        # Map course names to database field names
        course_field_map = {
            'IT': 'it_score',
            'CS': 'cs_score',
            'IS': 'is_score'
        }
        top_field = course_field_map.get(top_course, 'it_score')
        second_field = course_field_map.get(second_course, 'cs_score')
        
        top_score = float(opt[top_field])
        second_score = float(opt[second_field])
        distinction_score += abs(top_score - second_score)
    
    distinction_score = distinction_score / len(options) if options else 0
    
    # Combine factors
    # Weight: variance (40%), distinction (40%), difference (20%)
    utility = (avg_variance * 0.4) + (distinction_score * 0.4) + (avg_difference * 0.2)
    
    return utility

def select_next_question(answered_questions: List[Dict], 
                        all_question_ids: List[int] = None) -> Dict:
    """
    Select the next best question to ask based on adaptive algorithm
    
    Args:
        answered_questions: List of answered questions with option_ids
        all_question_ids: Optional list of all question IDs (if None, fetches from DB)
    
    Returns:
        Dictionary with next question data or None if no more questions
    """
    conn = get_db_connection()
    if not conn:
        return None
    
    cursor = conn.cursor(dictionary=True)
    
    # Get all active questions if not provided
    if all_question_ids is None:
        query = "SELECT id FROM questions WHERE is_active = 1 ORDER BY order_number, id"
        cursor.execute(query)
        all_question_ids = [row['id'] for row in cursor.fetchall()]
    
    # Get answered question IDs - ensure they're integers for comparison
    answered_ids = []
    for q in answered_questions:
        qid = q.get('question_id')
        if qid is not None:
            # Convert to int to ensure type consistency
            answered_ids.append(int(qid))
    
    # Ensure all_question_ids are also integers
    if all_question_ids:
        all_question_ids = [int(qid) for qid in all_question_ids]
    
    print(f"Inside select_next_question:")
    print(f"  Answered question IDs (as ints): {answered_ids}")
    print(f"  All question IDs (as ints): {all_question_ids}")
    
    # Calculate current scores
    current_scores = calculate_current_scores(answered_questions)
    
    # Calculate utility for each unanswered question
    question_utilities = []
    for qid in all_question_ids:
        qid_int = int(qid)  # Ensure integer type
        if qid_int not in answered_ids:
            utility = calculate_question_utility(qid_int, current_scores, answered_ids)
            question_utilities.append((qid_int, utility))
        else:
            print(f"  Skipping question {qid_int} (already answered)")
    
    if not question_utilities:
        cursor.close()
        conn.close()
        return None
    
    # Sort by utility (highest first)
    question_utilities.sort(key=lambda x: x[1], reverse=True)
    
    # Select top question
    next_question_id = question_utilities[0][0]
    
    # Get question details
    query = """
        SELECT q.*, 
               GROUP_CONCAT(
                   CONCAT(ao.id, ':', ao.option_text, ':', ao.it_score, ':', ao.cs_score, ':', ao.is_score)
                   SEPARATOR '||'
               ) as options_data
        FROM questions q
        LEFT JOIN answer_options ao ON q.id = ao.question_id
        WHERE q.id = %s
        GROUP BY q.id
    """
    cursor.execute(query, (next_question_id,))
    question = cursor.fetchone()
    
    cursor.close()
    conn.close()
    
    if not question:
        return None
    
    # Parse options
    options = []
    options_data = question.get('options_data')
    
    if options_data and options_data.strip():
        try:
            for opt_str in options_data.split('||'):
                if not opt_str.strip():
                    continue
                    
                parts = opt_str.split(':')
                # Format: id:option_text:it_score:cs_score:is_score
                # option_text may contain colons, so we need at least 5 parts
                # (id + text + 3 scores), but text might have colons
                if len(parts) >= 5:
                    try:
                        option_id = int(parts[0])
                        # Last 3 parts are always scores, everything in between is option_text
                        # If we have exactly 5 parts: [id, text, it, cs, is]
                        # If we have more: [id, text_part1, text_part2, ..., it, cs, is]
                        if len(parts) == 5:
                            option_text = parts[1]
                            it_score = float(parts[2])
                            cs_score = float(parts[3])
                            is_score = float(parts[4])
                        else:
                            # More than 5 parts means option_text contains colons
                            option_text = ':'.join(parts[1:-3])
                            it_score = float(parts[-3])
                            cs_score = float(parts[-2])
                            is_score = float(parts[-1])
                        
                        options.append({
                            'id': option_id,
                            'option_text': option_text,
                            'it_score': it_score,
                            'cs_score': cs_score,
                            'is_score': is_score
                        })
                    except (ValueError, IndexError) as e:
                        print(f"Error parsing option string '{opt_str}': {e}")
                        print(f"Parts: {parts}")
                        continue
        except Exception as e:
            print(f"Error parsing options_data: {e}")
            print(f"options_data value: {options_data}")
    
    # If no options found, try to fetch them directly from database
    if not options:
        print(f"Warning: No options found via GROUP_CONCAT for question {next_question_id}, fetching directly...")
        conn = get_db_connection()
        if conn:
            cursor = conn.cursor(dictionary=True)
            query = """
                SELECT id, option_text, it_score, cs_score, is_score
                FROM answer_options
                WHERE question_id = %s
                ORDER BY id
            """
            cursor.execute(query, (next_question_id,))
            direct_options = cursor.fetchall()
            cursor.close()
            conn.close()
            
            if direct_options:
                options = [{
                    'id': int(opt['id']),
                    'option_text': opt['option_text'],
                    'it_score': float(opt['it_score']),
                    'cs_score': float(opt['cs_score']),
                    'is_score': float(opt['is_score'])
                } for opt in direct_options]
                print(f"Found {len(options)} options via direct query")
    
    return {
        'question_id': question['id'],
        'question_text': question['question_text'],
        'question_type': question['question_type'],
        'options': options,
        'current_scores': current_scores,
        'utility_score': question_utilities[0][1]
    }

@app.route('/get_next_question', methods=['POST'])
def get_next_question():
    """
    API endpoint to get next adaptive question
    
    Expected JSON:
    {
        "answered_questions": [
            {
                "question_id": 1,
                "option_ids": [1, 2]
            }
        ],
        "all_question_ids": [1, 2, 3, 4, 5]  // optional
    }
    """
    try:
        data = request.get_json()
        answered_questions = data.get('answered_questions', [])
        all_question_ids = data.get('all_question_ids', None)
        
        print(f"\n=== get_next_question called ===")
        print(f"Received answered_questions: {answered_questions}")
        print(f"Number of answered questions: {len(answered_questions)}")
        if answered_questions:
            answered_ids = [q.get('question_id') for q in answered_questions]
            print(f"Answered question IDs: {answered_ids}")
        print(f"All question IDs: {all_question_ids}")
        
        next_question = select_next_question(answered_questions, all_question_ids)
        
        if next_question:
            print(f"Returning question ID: {next_question.get('question_id')}")
            return jsonify({
                'success': True,
                'question': next_question
            })
        else:
            print("No more questions available")
            return jsonify({
                'success': False,
                'message': 'No more questions available'
            })
    
    except Exception as e:
        print(f"Error in get_next_question: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/calculate_scores', methods=['POST'])
def calculate_scores():
    """
    API endpoint to calculate current compatibility scores
    
    Expected JSON:
    {
        "answered_questions": [
            {
                "question_id": 1,
                "option_ids": [1, 2]
            }
        ]
    }
    """
    try:
        data = request.get_json()
        answered_questions = data.get('answered_questions', [])
        
        scores = calculate_current_scores(answered_questions)
        
        # Determine recommended course
        sorted_scores = sorted(scores.items(), key=lambda x: x[1], reverse=True)
        recommended = sorted_scores[0][0] if sorted_scores else 'IT'
        
        return jsonify({
            'success': True,
            'scores': scores,
            'recommended_course': recommended
        })
    
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({'status': 'healthy', 'service': 'adaptive_assessment'})

if __name__ == '__main__':
    # Run on port 5000 (adjust if needed)
    app.run(host='0.0.0.0', port=5000, debug=True)


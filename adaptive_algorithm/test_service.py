"""
Test script for the adaptive assessment service
Run this to verify the service is working correctly
"""

import requests
import json

SERVICE_URL = 'http://localhost:5000'

def test_health():
    """Test health endpoint"""
    print("Testing health endpoint...")
    try:
        response = requests.get(f"{SERVICE_URL}/health")
        print(f"Status: {response.status_code}")
        print(f"Response: {response.json()}")
        return response.status_code == 200
    except Exception as e:
        print(f"Error: {e}")
        return False

def test_calculate_scores():
    """Test score calculation"""
    print("\nTesting score calculation...")
    try:
        data = {
            "answered_questions": [
                {
                    "question_id": 1,
                    "option_ids": [1]  # Assuming option 1 exists
                }
            ]
        }
        response = requests.post(f"{SERVICE_URL}/calculate_scores", json=data)
        print(f"Status: {response.status_code}")
        result = response.json()
        print(f"Response: {json.dumps(result, indent=2)}")
        return response.status_code == 200 and result.get('success')
    except Exception as e:
        print(f"Error: {e}")
        return False

def test_get_next_question():
    """Test getting next question"""
    print("\nTesting get next question...")
    try:
        data = {
            "answered_questions": [
                {
                    "question_id": 1,
                    "option_ids": [1]
                }
            ],
            "all_question_ids": [1, 2, 3, 4, 5]
        }
        response = requests.post(f"{SERVICE_URL}/get_next_question", json=data)
        print(f"Status: {response.status_code}")
        result = response.json()
        print(f"Response: {json.dumps(result, indent=2)}")
        return response.status_code == 200 and result.get('success')
    except Exception as e:
        print(f"Error: {e}")
        return False

def test_adaptive_flow():
    """Test a complete adaptive flow"""
    print("\nTesting adaptive flow...")
    try:
        answered_questions = []
        all_question_ids = [1, 2, 3, 4, 5]
        
        for i in range(3):  # Answer 3 questions
            data = {
                "answered_questions": answered_questions,
                "all_question_ids": all_question_ids
            }
            response = requests.post(f"{SERVICE_URL}/get_next_question", json=data)
            
            if response.status_code != 200:
                print(f"Error at step {i+1}: {response.status_code}")
                break
                
            result = response.json()
            if not result.get('success'):
                print(f"No more questions after {i} answers")
                break
                
            question = result['question']
            print(f"\nQuestion {i+1}: {question['question_text']}")
            print(f"Current scores: {question['current_scores']}")
            print(f"Utility score: {question['utility_score']:.2f}")
            
            # Simulate selecting first option
            if question['options']:
                selected_option = question['options'][0]['id']
                answered_questions.append({
                    "question_id": question['question_id'],
                    "option_ids": [selected_option]
                })
        
        # Calculate final scores
        response = requests.post(f"{SERVICE_URL}/calculate_scores", json={
            "answered_questions": answered_questions
        })
        final_scores = response.json()
        print(f"\nFinal Scores: {final_scores.get('scores')}")
        print(f"Recommended: {final_scores.get('recommended_course')}")
        
        return True
    except Exception as e:
        print(f"Error: {e}")
        return False

if __name__ == '__main__':
    print("=" * 50)
    print("Adaptive Assessment Service Test")
    print("=" * 50)
    
    # Check if service is running
    if not test_health():
        print("\n❌ Service is not running or not accessible!")
        print("Please start the service first:")
        print("  python adaptive_service.py")
        exit(1)
    
    print("\n✅ Service is running!")
    
    # Run tests
    tests = [
        ("Health Check", test_health),
        ("Calculate Scores", test_calculate_scores),
        ("Get Next Question", test_get_next_question),
        ("Adaptive Flow", test_adaptive_flow)
    ]
    
    results = []
    for name, test_func in tests:
        try:
            result = test_func()
            results.append((name, result))
        except Exception as e:
            print(f"\n❌ {name} failed: {e}")
            results.append((name, False))
    
    # Summary
    print("\n" + "=" * 50)
    print("Test Summary")
    print("=" * 50)
    for name, result in results:
        status = "✅ PASS" if result else "❌ FAIL"
        print(f"{status}: {name}")
    
    all_passed = all(result for _, result in results)
    if all_passed:
        print("\n🎉 All tests passed!")
    else:
        print("\n⚠️  Some tests failed. Check the output above.")


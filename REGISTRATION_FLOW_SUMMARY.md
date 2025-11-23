# Registration and Exam Flow Summary

## Complete Student Flow

The system now follows this complete flow from registration to exam completion:

### 1. Registration (`register.php`)
- Student enters first name, middle name, and last name
- Form submits to `client/register_code.php`

### 2. Registration Handler (`client/register_code.php`)
- Validates and sanitizes input
- Inserts student record into `client` table
- Redirects to disclosure page with student ID

### 3. Disclosure Page (`client/disclosure.php`)
- Shows assessment information and instructions
- Displays course descriptions (IT, CS, IS)
- Provides "Start Assessment" button linking to `assessment_adaptive.php?id=<student_id>`

### 4. Assessment Page (`client/assessment_adaptive.php`)
- **NEW**: Uses the new exam API system
- Flow:
  1. **Start Exam Session**: Calls `api/exam.php?action=start-exam` to create exam session
  2. **Get Questions**: Calls `api/exam.php?action=get-question` for each question
  3. **Submit Answers**: Calls `api/exam.php?action=submit-answer` when student answers
  4. **Finish Exam**: Calls `api/exam.php?action=finish-exam` when exam completes

### 5. Exam API (`api/exam.php`)
- Handles all exam operations
- Integrates with Python adaptive service
- Stores data in new exam tables:
  - `exam_sessions` - Tracks exam progress
  - `exam_answers` - Stores individual answers
  - `exam_results` - Stores final results

## Key Changes Made

### Updated Files:
1. **`client/register_code.php`**
   - Updated to use OOP mysqli interface
   - Improved error handling
   - Better code structure

2. **`client/assessment_adaptive.php`**
   - **Completely refactored** to use new exam API
   - Creates exam session on page load
   - Uses new API endpoints for all operations
   - Properly tracks session state
   - Handles exam completion through API

### New Integration Points:
- Exam session management through API
- Answer submission through API
- Result calculation through API
- All data stored in new exam tables

## Data Flow

```
Student Registration
    ↓
Client Table (client record created)
    ↓
Disclosure Page (information shown)
    ↓
Assessment Page Loads
    ↓
API: start-exam (creates exam_sessions record)
    ↓
API: get-question (gets next question via Python service)
    ↓
Student Answers Question
    ↓
API: submit-answer (saves to exam_answers table)
    ↓
Repeat get-question → submit-answer until done
    ↓
API: finish-exam (calculates scores, saves to exam_results)
    ↓
Results Displayed to Student
```

## Benefits

1. **Proper Session Tracking**: Each exam has a session ID tracked in database
2. **Answer Persistence**: All answers saved immediately to `exam_answers` table
3. **Result Storage**: Final results stored in `exam_results` table
4. **Analytics Ready**: All data structured for future analytics
5. **Error Recovery**: Session state allows for potential resume functionality

## Testing Checklist

- [ ] Student can register successfully
- [ ] Registration redirects to disclosure page
- [ ] Disclosure page shows correct student name
- [ ] "Start Assessment" button works
- [ ] Exam session is created on assessment page load
- [ ] Questions load correctly from API
- [ ] Answers submit successfully
- [ ] Exam finishes and shows results
- [ ] Results are saved to database


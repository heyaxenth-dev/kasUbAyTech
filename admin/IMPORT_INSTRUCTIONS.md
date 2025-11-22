# Question Import Instructions

## Steps to Import Questions

### 1. Update Database Schema

Run the SQL file to add category fields:

```sql
source database/add_category_field.sql
```

Or run it through phpMyAdmin.

### 2. Import Questions

The import script `import_questions_complete.php` has been created with a template structure. You need to:

1. **Complete the question data array** - Add all 90 questions from the PDF:

   - 3 Diagnostic questions (already included as examples)
   - 30 IS questions (10 Fundamentals + 10 Programming + 10 Hardware)
   - 30 IT questions (10 Fundamentals + 10 Programming + 10 Hardware)
   - 30 CS questions (10 Fundamentals + 10 Programming + 10 Hardware)

2. **Run the import script**:
   ```bash
   php admin/import_questions_complete.php
   ```
   Or access via browser: `http://localhost/kasUbAyTech/admin/import_questions_complete.php`

### 3. Question Data Structure

Each question should follow this format:

```php
[
    'category' => 'IS', // or 'IT', 'CS', 'DIAGNOSTIC'
    'topic' => 'Fundamentals of Computer', // or 'Programming', 'Computer Hardware'
    'question' => 'Question text here?',
    'options' => [
        ['text' => 'Option A text'],
        ['text' => 'Option B text'], // Correct answer
        ['text' => 'Option C text'],
        ['text' => 'Option D text'],
    ],
    'correct' => 1, // Index of correct answer (0=A, 1=B, 2=C, 3=D)
],
```

### 4. Scoring System

- **Correct answers** get high scores for their category:

  - IS questions: `is_score = 5.0`, `it_score = 2.0`, `cs_score = 1.0`
  - IT questions: `it_score = 5.0`, `is_score = 2.0`, `cs_score = 1.0`
  - CS questions: `cs_score = 5.0`, `it_score = 1.0`, `is_score = 2.0`

- **Wrong answers** get low scores: `it_score = 1.0`, `cs_score = 1.0`, `is_score = 1.0`

## New Adaptive Algorithm Flow

The algorithm now works as follows:

1. **Diagnostic Phase** (First 3 questions):

   - Starts with 3 diagnostic questions to pre-determine course compatibility
   - These questions test basic knowledge across all categories

2. **Adaptive Phase**:

   - Tracks correct/incorrect answers per category (IS/IT/CS)
   - If a student gets wrong answers (>30% wrong) in a category, the algorithm shifts to test that category more
   - This ensures comprehensive testing of knowledge across all courses

3. **Utility-Based Selection**:
   - When no category needs special testing, uses utility scores to select the most informative next question

## Testing

After importing:

1. Restart the Flask adaptive service
2. Test the assessment with a client
3. Check the Flask console logs to see the category shifting logic in action
4. Verify that diagnostic questions appear first, then questions shift based on wrong answers

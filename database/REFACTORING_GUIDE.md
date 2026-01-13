# Database Refactoring Guide: CAT-lite Support

## Overview

This guide explains the database refactoring needed to support the **Re-evaluated Adaptive Algorithm (CAT-lite)**. The refactoring separates **course identity** from **exam phase** to enable proper adaptive question selection.

## Problem Statement

### Before Refactoring

- `category` column was overloaded: `ENUM('IS','IT','CS','DIAGNOSTIC')`
- Used for both:
  - Course identification (IS/IT/CS)
  - Exam phase (DIAGNOSTIC)
- This caused:
  - Inability to have diagnostic questions that map to specific courses
  - Difficulty in implementing re-evaluation logic
  - Dominance bias issues

### After Refactoring

- `course_tag` column: `ENUM('IT','IS','CS')` - Identifies which course
- `category` column: `ENUM('DIAGNOSTIC','ADAPTIVE')` - Identifies exam phase
- Benefits:
  - Diagnostic questions can have course tags
  - Clear separation of concerns
  - Supports re-evaluation after Q10
  - Enables balanced adaptive selection

## Schema Changes

### Questions Table

#### New Column: `course_tag`

```sql
course_tag ENUM('IT', 'IS', 'CS') NOT NULL
```

- **Purpose**: Explicitly identifies which course (IT/IS/CS) a question belongs to
- **Required**: All questions (diagnostic and adaptive) must have a course_tag
- **Indexed**: Yes, for query performance

#### Modified Column: `category`

```sql
-- Before:
category ENUM('IS','IT','CS','DIAGNOSTIC')

-- After:
category ENUM('DIAGNOSTIC','ADAPTIVE')
```

- **Purpose**: Represents exam phase, not course
- **Values**:
  - `DIAGNOSTIC`: Questions used in Phase 1 (Q1-5)
  - `ADAPTIVE`: Questions used in Phase 2 and Phase 3 (Q6-20)

### Exam_Answers Table

#### New Column: `course_tag`

```sql
course_tag ENUM('IT', 'IS', 'CS') NOT NULL
```

- **Purpose**: Stores the course_tag from the question at the time of answer
- **Benefit**: Enables course-based score calculation without joining questions table

## Migration Steps

### Step 1: Backup Database

```sql
-- Always backup before migration!
mysqldump -u root kasubaytech_db > backup_before_refactor.sql
```

### Step 2: Run Migration Script

```bash
mysql -u root kasubaytech_db < database/migration_cat_lite_refactor.sql
```

### Step 3: Verify Migration

Run the verification queries in the migration script to ensure:

- All questions have course_tag
- Category values are correct (DIAGNOSTIC or ADAPTIVE)
- No data loss occurred

### Step 4: Review Diagnostic Questions

Manually review diagnostic questions and adjust `course_tag` if needed:

- Diagnostic questions should have course_tag based on which course they favor
- Use option scores (it_score, cs_score, is_score) to determine course_tag

## Data Migration Strategy

### For Non-Diagnostic Questions (IS/IT/CS)

```sql
-- These questions become ADAPTIVE with course_tag = their old category
UPDATE questions
SET course_tag = category, category = 'ADAPTIVE'
WHERE category IN ('IS', 'IT', 'CS');
```

### For Diagnostic Questions

**Challenge**: Diagnostic questions need course_tag but currently have category='DIAGNOSTIC'

**Solution Options**:

1. **Use Option Scores** (Recommended if available):

   ```sql
   -- Determine course_tag from answer_options scores
   UPDATE questions q
   INNER JOIN (
       SELECT question_id,
           CASE
               WHEN AVG(it_score) >= AVG(cs_score) AND AVG(it_score) >= AVG(is_score) THEN 'IT'
               WHEN AVG(cs_score) >= AVG(is_score) THEN 'CS'
               ELSE 'IS'
           END AS inferred_course
       FROM answer_options
       GROUP BY question_id
   ) AS inferred ON q.id = inferred.question_id
   SET q.course_tag = inferred.inferred_course
   WHERE q.category = 'DIAGNOSTIC';
   ```

2. **Manual Assignment** (If option scores unavailable):
   - Review each diagnostic question
   - Assign course_tag based on question content
   - Update manually:
     ```sql
     UPDATE questions SET course_tag = 'IT' WHERE id = ?;
     ```

## Query Patterns by Phase

### Phase 1: Diagnostic (Q1-5)

```sql
-- Get diagnostic questions for a specific course
SELECT * FROM questions
WHERE category = 'DIAGNOSTIC'
  AND course_tag = 'IT'  -- or 'IS' or 'CS'
  AND is_active = 1
  AND id NOT IN (answered_ids);
```

### Phase 2: Adaptive Round 1 (Q6-10)

```sql
-- Get adaptive questions for dominant course (3 needed)
SELECT * FROM questions
WHERE category = 'ADAPTIVE'
  AND course_tag = 'IT'  -- dominant course
  AND is_active = 1
  AND id NOT IN (answered_ids)
LIMIT 3;
```

### Phase 3: Adaptive Round 2 (Q11-20)

```sql
-- Get adaptive questions for tied courses (5 each)
SELECT * FROM questions
WHERE category = 'ADAPTIVE'
  AND course_tag IN ('IT', 'CS')  -- tied courses
  AND is_active = 1
  AND id NOT IN (answered_ids)
ORDER BY course_tag, RAND()
LIMIT 10;
```

## Application Code Updates

### PHP Code Changes

#### Before:

```php
// Old way - category used for course
$query = "SELECT * FROM questions WHERE category = 'IT'";
```

#### After:

```php
// New way - use course_tag for course, category for phase
$query = "SELECT * FROM questions
          WHERE category = 'ADAPTIVE'
          AND course_tag = 'IT'";
```

### Python Adaptive Service Changes

Update `adaptive_service.py`:

- Use `course_tag` instead of `category` for course identification
- Use `category` to determine exam phase (DIAGNOSTIC vs ADAPTIVE)
- Update question selection logic to use both columns

## Backward Compatibility

### Preserved

- All question IDs remain unchanged
- All relationships preserved
- All answer data intact
- Existing indexes maintained (with additions)

### Breaking Changes

- Application code using `category` for course identification must be updated
- Queries filtering by `category IN ('IS','IT','CS')` must use `course_tag` instead

## Verification Checklist

After migration, verify:

- [ ] All questions have `course_tag` set (no NULLs)
- [ ] All questions have `category` = 'DIAGNOSTIC' or 'ADAPTIVE'
- [ ] Diagnostic questions have appropriate `course_tag`
- [ ] Adaptive questions have appropriate `course_tag`
- [ ] Indexes are created and working
- [ ] Application code updated to use new schema
- [ ] Python adaptive service updated
- [ ] Test exam flow works correctly

## Troubleshooting

### Issue: Diagnostic questions have wrong course_tag

**Solution**: Manually review and update:

```sql
UPDATE questions SET course_tag = 'IT' WHERE id = ?;
```

### Issue: Missing course_tag after migration

**Solution**: Check if questions exist without course_tag:

```sql
SELECT * FROM questions WHERE course_tag IS NULL;
-- Then update them manually
```

### Issue: Category still has old values

**Solution**: Re-run the category update:

```sql
UPDATE questions SET category = 'ADAPTIVE' WHERE category IN ('IS','IT','CS');
```

## Example Queries

See `database/example_queries_cat_lite.sql` for comprehensive query examples for:

- Phase 1 diagnostic selection
- Phase 2 adaptive selection
- Phase 3 tie-aware selection
- Re-evaluation score calculation

## Support

For questions or issues:

1. Check the migration script comments
2. Review example queries
3. Verify data integrity with verification queries
4. Test with a small subset before full migration

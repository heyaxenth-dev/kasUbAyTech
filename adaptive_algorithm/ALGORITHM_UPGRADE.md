# Re-evaluated Adaptive Algorithm (CAT-lite) - Upgrade Summary

## Overview

This document describes the upgrade from the original adaptive algorithm to the **Re-evaluated Adaptive Algorithm (CAT-lite)** while preserving the existing database structure and scoring logic.

## Key Changes

### 1. Phase-Based Structure

The algorithm now operates in three distinct phases:

- **Phase 1 (Questions 1-5)**: Diagnostic phase
- **Phase 2 (Questions 6-10)**: Adaptive Round 1
- **Phase 3 (Questions 11-20)**: Adaptive Round 2

### 2. Configuration Updates

```python
# Old:
DIAGNOSTIC_COUNT = 3

# New:
PHASE_1_QUESTIONS = 5    # Questions 1-5
PHASE_2_QUESTIONS = 10    # Questions 6-10
PHASE_3_QUESTIONS = 20    # Questions 11-20
DIAGNOSTIC_COUNT = 5      # Updated for backward compatibility
```

### 3. New Helper Functions

#### `determine_course_rankings(scores)`

Determines dominant, secondary, and weakest courses with full tie support.

- Returns dictionary with `dominant`, `secondary`, `weakest`, `has_tie`, `tied_courses`
- Supports two-way and three-way ties
- Uses 0.1 point threshold for tie detection

#### `get_phase_from_question_count(answered_count)`

Determines current phase based on number of answered questions.

#### `count_questions_by_category_in_phase(answered_questions, phase_start, phase_end, target_category)`

Counts questions of a specific category within a phase range.

## Phase Implementation Details

### Phase 1: Diagnostic (Questions 1-5)

**Goal**: Mixed IT/IS/CS questions, no dominance lock

- Selects diagnostic questions (category = 'DIAGNOSTIC')
- Tracks which course categories have been covered
- Ensures balanced coverage (one of each IT/CS/IS if possible)
- Each correct answer adds +1 to that course's score
- **No dominance is locked** - allows ties and re-evaluation

**Selection Logic**:

1. Check which categories (IT/CS/IS) have been covered
2. Prioritize uncovered categories
3. If all covered, select any available diagnostic question

### Phase 2: Adaptive Round 1 (Questions 6-10)

**Goal**: 3 dominant, 1 secondary, 1 weakest

- Determines course rankings from current scores
- Distributes questions: 3 to dominant, 1 to secondary, 1 to weakest
- Continues accumulating scores
- Dominance can still change (not locked)

**Selection Logic**:

1. Calculate course rankings (dominant/secondary/weakest)
2. Count questions already asked in Phase 2
3. Select category based on distribution targets:
   - If dominant < 3: select dominant
   - Else if secondary < 1: select secondary
   - Else if weakest < 1: select weakest
   - Else: default to dominant

### Re-evaluation Checkpoint (After Question 10)

**Goal**: Recompute dominance before Phase 3

- Automatically occurs when entering Phase 3
- Recalculates all scores from all answered questions
- Determines new course rankings
- **Dominance can change** - addresses early misclassification

### Phase 3: Adaptive Round 2 (Questions 11-20)

**Goal**: Tie-aware distribution

**If one dominant course**:

- 6 questions to dominant
- 3 questions to secondary
- 1 question to weakest

**If two courses tied**:

- 5 questions to each tied course
- 1 question to third course

**Selection Logic**:

1. Re-evaluate course rankings (checkpoint)
2. Check for ties
3. Count questions already asked in Phase 3
4. Select category based on tie-aware distribution:
   - **Tie scenario**: Ensure 5 each for tied courses, 1 for third
   - **No tie scenario**: Ensure 6 dominant, 3 secondary, 1 weakest

## Preserved Components

### Scoring System

- `compute_raw_scores()` - unchanged
- `normalize_scores()` - unchanged
- Option-level scoring (it_score, cs_score, is_score) - unchanged

### Utility Metrics

- `variance()` - unchanged
- `approx_information_gain()` - unchanged
- Utility-based fallback - preserved as final fallback

### Database Integration

- Question caching - unchanged
- Database connection pooling - unchanged
- API endpoints - unchanged

## Algorithm Flow Diagram

```
Start Exam
    ↓
Phase 1 (Q1-5): Diagnostic
    ├─ Select diagnostic questions
    ├─ Track IT/CS/IS coverage
    └─ Accumulate scores (no lock)
    ↓
Phase 2 (Q6-10): Adaptive Round 1
    ├─ Determine rankings
    ├─ Distribute: 3 dominant, 1 secondary, 1 weakest
    └─ Accumulate scores
    ↓
Re-evaluation Checkpoint (After Q10)
    ├─ Recompute all scores
    ├─ Determine new rankings
    └─ Support dominance switching
    ↓
Phase 3 (Q11-20): Adaptive Round 2
    ├─ Check for ties
    ├─ If tie: 5+5+1 distribution
    ├─ If no tie: 6+3+1 distribution
    └─ Continue until Q20 or confidence reached
    ↓
Final Recommendation
```

## Key Improvements

1. **Reduced Early Misclassification**

   - No dominance lock in Phase 1
   - Re-evaluation checkpoint allows correction

2. **Prevents IS Dominance Bias**

   - Balanced distribution in Phase 1
   - Tie-aware logic prevents single-course bias

3. **Allows Dominance Switching**

   - Rankings recalculated at checkpoint
   - Phase 3 adapts to new rankings

4. **Explainable Logic**
   - Clear phase boundaries
   - Explicit distribution rules
   - No ML libraries required

## Testing Recommendations

1. **Test Phase Transitions**

   - Verify Phase 1 → Phase 2 transition at Q5
   - Verify Phase 2 → Phase 3 transition at Q10

2. **Test Tie Scenarios**

   - Create test cases with tied scores
   - Verify two-way tie distribution (5+5+1)
   - Verify three-way tie handling

3. **Test Dominance Switching**

   - Create scenario where scores change between Phase 2 and Phase 3
   - Verify re-evaluation correctly identifies new dominant course

4. **Test Edge Cases**
   - Insufficient questions in a category
   - All questions answered before reaching target distribution
   - Very close scores (within tie threshold)

## Backward Compatibility

- API endpoints unchanged
- Database schema unchanged
- Scoring formulas unchanged
- Existing question data compatible

## Files Modified

- `adaptive_algorithm/adaptive_service.py`
  - Updated configuration constants
  - Added course ranking functions
  - Refactored `select_next_question_logic()` with phase-based structure
  - Preserved all utility and scoring functions

## Notes

- The algorithm maintains academic explainability
- All logic is deterministic (no ML randomness)
- Distribution targets are hard limits within each phase
- Utility-based fallback ensures questions are always selected if available

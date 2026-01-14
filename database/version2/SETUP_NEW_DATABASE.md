# Setting Up the New CAT-lite Database

## Overview

This guide explains how to set up a **separate database** for the CAT-lite upgrade, keeping your existing database intact while testing and deploying the new adaptive algorithm.

## Why a Separate Database?

1. **Safety**: Your existing system remains untouched
2. **Testing**: Test the new algorithm without affecting production
3. **Comparison**: Run both systems side-by-side
4. **Rollback**: Easy to revert if needed
5. **Clean Slate**: Start with proper structure from the beginning

## Database Names

- **Old Database**: `kasubaytech_db` (unchanged)
- **New Database**: `kasubaytech_catlite_db` (new CAT-lite structure)

## Setup Steps

### Step 1: Create the New Database Schema

```bash
mysql -u root < database/schema_cat_lite.sql
```

Or in MySQL:

```sql
SOURCE database/schema_cat_lite.sql;
```

This creates:

- New database: `kasubaytech_catlite_db`
- All tables with CAT-lite structure
- Proper indexes and relationships

### Step 2: Migrate Data (Optional)

If you want to copy existing data:

```bash
mysql -u root < database/migrate_data_to_catlite.sql
```

**What gets migrated:**

- ✅ Client data (students)
- ✅ Admin accounts
- ✅ Questions (with proper category/course_tag transformation)
- ✅ Answer options
- ⚠️ Exam sessions (optional - commented out by default)

**What changes:**

- Questions with old `category='IS'/'IT'/'CS'` → `category='ADAPTIVE'`, `course_tag='IS'/'IT'/'CS'`
- Questions with old `category='DIAGNOSTIC'` → `category='DIAGNOSTIC'`, `course_tag` inferred from option scores

### Step 3: Review Diagnostic Questions

After migration, review diagnostic questions:

```sql
SELECT id, question_text, course_tag
FROM kasubaytech_catlite_db.questions
WHERE category = 'DIAGNOSTIC';
```

Manually adjust `course_tag` if needed:

```sql
UPDATE kasubaytech_catlite_db.questions
SET course_tag = 'IS'
WHERE id = 1 AND category = 'DIAGNOSTIC';
```

### Step 4: Update Application Configuration

Update your PHP configuration to use the new database:

**File: `database/config.php`** (or wherever your DB config is)

```php
<?php
// Option 1: Switch to new database
define('DB_NAME', 'kasubaytech_catlite_db');

// Option 2: Use environment variable to switch
define('DB_NAME', getenv('USE_CATLITE') ? 'kasubaytech_catlite_db' : 'kasubaytech_db');

// Option 3: Create separate config file
// config_catlite.php
```

**File: `adaptive_algorithm/adaptive_service.py`**

```python
DB_CONFIG = {
    "host": "localhost",
    "database": "kasubaytech_catlite_db",  # Changed
    "user": "root",
    "password": "",
    # ...
}
```

### Step 5: Update Python Adaptive Service

The Python service should automatically work with the new schema since it uses:

- `category` for phase detection (DIAGNOSTIC/ADAPTIVE)
- `course_tag` for course identification (IT/IS/CS)

Just update the database name in `DB_CONFIG`.

### Step 6: Test the System

1. **Test Question Selection**:

   ```sql
   -- Phase 1: Diagnostic questions
   SELECT * FROM kasubaytech_catlite_db.questions
   WHERE category = 'DIAGNOSTIC' AND course_tag = 'IT';

   -- Phase 2: Adaptive questions for dominant course
   SELECT * FROM kasubaytech_catlite_db.questions
   WHERE category = 'ADAPTIVE' AND course_tag = 'IS';
   ```

2. **Test Exam Flow**:

   - Create a new exam session
   - Answer questions through all phases
   - Verify re-evaluation checkpoint works
   - Check final recommendation

3. **Verify Scoring**:
   ```sql
   -- Check course-based scoring
   SELECT course_tag, COUNT(*) as count, SUM(points_awarded) as total_points
   FROM kasubaytech_catlite_db.exam_answers
   WHERE session_id = ?
   GROUP BY course_tag;
   ```

## Running Both Databases Simultaneously

You can run both systems in parallel:

### Option A: Environment-Based Switching

```php
// config.php
$use_catlite = isset($_ENV['USE_CATLITE']) && $_ENV['USE_CATLITE'] === 'true';

define('DB_NAME', $use_catlite ? 'kasubaytech_catlite_db' : 'kasubaytech_db');
```

### Option B: Separate Instances

- Production: `kasubaytech_db` (old system)
- Testing: `kasubaytech_catlite_db` (new system)
- Use different ports or subdomains

### Option C: Feature Flag

```php
// config.php
define('USE_CATLITE_DB', true);  // Toggle this

if (USE_CATLITE_DB) {
    define('DB_NAME', 'kasubaytech_catlite_db');
} else {
    define('DB_NAME', 'kasubaytech_db');
}
```

## Verification Checklist

After setup, verify:

- [ ] New database created: `kasubaytech_catlite_db`
- [ ] All tables created with correct structure
- [ ] Questions migrated (if applicable)
- [ ] Diagnostic questions have proper `course_tag`
- [ ] Adaptive questions have `category='ADAPTIVE'` and proper `course_tag`
- [ ] Application config updated
- [ ] Python service updated
- [ ] Test exam session works
- [ ] Phase 1 (diagnostic) works
- [ ] Phase 2 (adaptive round 1) works
- [ ] Phase 3 (adaptive round 2) works
- [ ] Re-evaluation checkpoint works

## Troubleshooting

### Issue: Can't connect to new database

**Solution**: Check database name in config files matches `kasubaytech_catlite_db`

### Issue: Questions missing course_tag

**Solution**: Run the diagnostic question inference query or manually assign:

```sql
UPDATE kasubaytech_catlite_db.questions
SET course_tag = 'IS'
WHERE category = 'DIAGNOSTIC' AND course_tag = 'IT';
```

### Issue: Python service can't find questions

**Solution**:

1. Verify database name in `adaptive_service.py`
2. Check Python service can connect to MySQL
3. Verify questions table has data

### Issue: Old queries don't work

**Solution**: Update queries to use:

- `course_tag` instead of `category` for course identification
- `category` for phase (DIAGNOSTIC/ADAPTIVE)

## Migration Path

### Phase 1: Development (Current)

- Use `kasubaytech_catlite_db` for development
- Keep `kasubaytech_db` as production

### Phase 2: Testing

- Test thoroughly with `kasubaytech_catlite_db`
- Compare results with old system
- Fix any issues

### Phase 3: Production Switch

- When ready, switch production to `kasubaytech_catlite_db`
- Keep old database as backup
- Monitor for issues

### Phase 4: Cleanup (Optional)

- After successful deployment, you can:
  - Archive old database
  - Or keep both for historical data

## Example Queries

See `database/example_queries_cat_lite.sql` for comprehensive query examples.

## Support

For questions:

1. Check schema: `database/schema_cat_lite.sql`
2. Review migration: `database/migrate_data_to_catlite.sql`
3. Check example queries: `database/example_queries_cat_lite.sql`

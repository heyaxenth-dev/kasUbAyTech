# Adaptive Assessment Setup Guide

## Quick Start

### 1. Install Dependencies

```bash
cd adaptive_algorithm
pip install -r requirements.txt
```

### 2. Configure Database

Edit `adaptive_service.py` and update the database configuration if needed:

```python
DB_CONFIG = {
    'host': 'localhost',
    'database': 'kasubaytech_db',
    'user': 'root',
    'password': ''  # Your MySQL password
}
```

### 3. Start the Service

**Windows:**

```bash
start_service.bat
```

**Linux/Mac:**

```bash
chmod +x start_service.sh
./start_service.sh
```

**Or manually:**

```bash
python adaptive_service.py
```

The service will start on `http://localhost:5000`

### 4. Check Service Status

**Quick check:**

```bash
python check_service.py
```

You should see:

```
✅ Service is RUNNING!
   Status: healthy
   Service: adaptive_assessment
```

**Or test with full test suite:**

```bash
python test_service.py
```

You should see:

```
✅ Service is running!
✅ All tests passed!
```

### 5. Enable Adaptive Assessment

Students can now choose "Use Adaptive Assessment" when registering, or you can modify `client/register_code.php` to always use adaptive:

```php
// Always use adaptive
header("Location: assessment_adaptive.php?id=$lastid");
```

## How It Works

### Algorithm Flow

1. **Initial State**: All questions are available
2. **Answer Question**: Student selects an answer
3. **Calculate Scores**: System updates IT, CS, IS compatibility scores
4. **Select Next Question**: Algorithm evaluates all unanswered questions
5. **Utility Calculation**: Each question gets a utility score based on:
   - Variance in option scores (40%)
   - Ability to distinguish top 2 courses (40%)
   - Score differences between courses (20%)
6. **Display Question**: Most informative question is shown next
7. **Repeat**: Steps 2-6 until assessment complete

### Example Scenario

**After 2 questions:**

- Current scores: IT: 45%, CS: 38%, IS: 42%
- Top courses: IT and IS are close
- Algorithm selects question that best distinguishes IT vs IS

**After 5 questions:**

- Current scores: IT: 52%, CS: 35%, IS: 48%
- Top courses: IT and IS still close
- Algorithm focuses on IT vs IS distinction

**Result**: More accurate assessment with potentially fewer questions!

## Troubleshooting

### Service Won't Start

1. **Check Python version:**

   ```bash
   python --version  # Should be 3.7+
   ```

2. **Check dependencies:**

   ```bash
   pip list | grep -i flask
   pip list | grep -i mysql
   ```

3. **Check port availability:**

   ```bash
   # Windows
   netstat -an | findstr 5000

   # Linux/Mac
   lsof -i :5000
   ```

### Database Connection Error

1. Verify MySQL is running
2. Check database name: `kasubaytech_db`
3. Verify credentials in `DB_CONFIG`
4. Test connection:
   ```python
   import mysql.connector
   conn = mysql.connector.connect(host='localhost', database='kasubaytech_db', user='root', password='')
   print("Connected!" if conn else "Failed")
   ```

### Questions Not Loading

1. Ensure questions exist in database
2. Check `is_active = 1` for questions
3. Verify answer options exist for questions
4. Check browser console for JavaScript errors

### Fallback to Regular Assessment

If the adaptive service is unavailable, the system automatically falls back to regular assessment. Students won't notice any disruption.

## Performance Tips

1. **Run as Background Service**: Use systemd (Linux) or NSSM (Windows) to run the service in the background
2. **Optimize Database**: Add indexes on frequently queried fields
3. **Caching**: Consider caching question data for faster responses
4. **Load Balancing**: For high traffic, run multiple service instances

## Advanced Configuration

### Adjust Algorithm Weights

Edit `calculate_question_utility()` in `adaptive_service.py`:

```python
# Current weights
utility = (avg_variance * 0.4) + (distinction_score * 0.4) + (avg_difference * 0.2)

# Customize:
utility = (avg_variance * 0.5) + (distinction_score * 0.3) + (avg_difference * 0.2)
```

### Minimum Questions

Add a minimum question requirement:

```python
def select_next_question(answered_questions, all_question_ids=None, min_questions=5):
    if len(answered_questions) < min_questions:
        # Use regular order for first N questions
        return get_regular_question(answered_questions, all_question_ids)
    else:
        # Use adaptive algorithm
        return get_adaptive_question(answered_questions, all_question_ids)
```

### Question Pool Size

Limit the number of questions asked:

```python
MAX_QUESTIONS = 10
if len(answered_questions) >= MAX_QUESTIONS:
    return None  # End assessment
```

## Monitoring

### Check Service Status

```bash
curl http://localhost:5000/health
```

### View Logs

The service prints logs to console. For production, redirect to a log file:

```bash
python adaptive_service.py >> adaptive_service.log 2>&1
```

## Support

For issues:

1. Check `test_service.py` output
2. Review service logs
3. Verify database connectivity
4. Test API endpoints manually with curl/Postman

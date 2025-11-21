# Adaptive Assessment Algorithm

This Python service implements an adaptive algorithm that dynamically selects the next question based on the student's previous answers to better assess their compatibility with IT, CS, and IS courses.

## How It Works

The adaptive algorithm uses several factors to select the most informative next question:

1. **Variance Analysis**: Questions with high variance in scores across options are more informative
2. **Course Distinction**: Prioritizes questions that can distinguish between the top 2 courses the student is leaning towards
3. **Score Differences**: Selects questions where answer options have significant differences between courses

## Quick Status Check

**Check if the service is running:**
```bash
python check_service.py
```

Or use the provided scripts:
- Windows: `check_service.bat`
- Linux/Mac: `chmod +x check_service.sh && ./check_service.sh`

**Other ways to check:**
- Open browser: `http://localhost:5000/health`
- Use curl: `curl http://localhost:5000/health`
- Check processes: `tasklist | findstr python` (Windows) or `ps aux | grep python` (Linux/Mac)

## Installation

1. Install Python dependencies:
```bash
pip install -r requirements.txt
```

2. Update database configuration in `adaptive_service.py` if needed:
```python
DB_CONFIG = {
    'host': 'localhost',
    'database': 'kasubaytech_db',
    'user': 'root',
    'password': ''
}
```

3. Run the service:
```bash
python adaptive_service.py
```

The service will run on `http://localhost:5000`

## API Endpoints

### GET /health
Health check endpoint.

**Response:**
```json
{
    "status": "healthy",
    "service": "adaptive_assessment"
}
```

### POST /get_next_question
Get the next adaptive question based on answered questions.

**Request:**
```json
{
    "answered_questions": [
        {
            "question_id": 1,
            "option_ids": [1, 2]
        },
        {
            "question_id": 2,
            "option_ids": [5]
        }
    ],
    "all_question_ids": [1, 2, 3, 4, 5]  // optional
}
```

**Response:**
```json
{
    "success": true,
    "question": {
        "question_id": 3,
        "question_text": "What interests you most?",
        "question_type": "single",
        "options": [
            {
                "id": 10,
                "option_text": "Web Development",
                "it_score": 5.0,
                "cs_score": 3.0,
                "is_score": 5.0
            }
        ],
        "current_scores": {
            "IT": 45.2,
            "CS": 38.5,
            "IS": 42.1
        },
        "utility_score": 2.34
    }
}
```

### POST /calculate_scores
Calculate current compatibility scores based on answered questions.

**Request:**
```json
{
    "answered_questions": [
        {
            "question_id": 1,
            "option_ids": [1]
        }
    ]
}
```

**Response:**
```json
{
    "success": true,
    "scores": {
        "IT": 45.2,
        "CS": 38.5,
        "IS": 42.1
    },
    "recommended_course": "IT"
}
```

## Algorithm Details

### Utility Score Calculation

For each unanswered question, the algorithm calculates a utility score based on:

1. **Variance (40% weight)**: Higher variance in option scores = more informative
2. **Distinction Score (40% weight)**: How well the question distinguishes between top 2 courses
3. **Score Difference (20% weight)**: Average difference between course scores across options

The question with the highest utility score is selected next.

### Benefits

- **Efficiency**: Asks fewer questions by focusing on informative ones
- **Accuracy**: Better distinguishes between similar courses
- **Personalization**: Adapts to each student's responses
- **Real-time**: Updates scores after each answer

## Integration with PHP

The PHP assessment page calls this service via HTTP requests. See `client/assessment_adaptive.php` for integration example.

## Running as a Service

### Windows (using NSSM)
```bash
nssm install AdaptiveAssessment python C:\path\to\python.exe C:\path\to\adaptive_service.py
nssm start AdaptiveAssessment
```

### Linux (using systemd)
Create `/etc/systemd/system/adaptive-assessment.service`:
```ini
[Unit]
Description=Adaptive Assessment Service
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/adaptive_algorithm
ExecStart=/usr/bin/python3 /path/to/adaptive_service.py
Restart=always

[Install]
WantedBy=multi-user.target
```

Then:
```bash
sudo systemctl enable adaptive-assessment
sudo systemctl start adaptive-assessment
```


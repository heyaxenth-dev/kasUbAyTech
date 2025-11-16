# kasUbAyTech - Course Compatibility Assessment System

A web-based assessment system for incoming freshmen to determine their compatibility with computer and technology courses (IT, CS, IS).

## Features

### Student Features
- Student registration
- Dynamic assessment with timer
- Real-time compatibility score calculation
- Course recommendation (IT, CS, or IS)

### Admin Features
- **Question Management**: Add, Edit, Delete assessment questions
- **Answer Options**: Configure multiple choice options with compatibility scores for each course
- **Results Monitoring**: View all student assessment results
- **Compatibility Analysis**: View detailed compatibility scores and statistics
- **Dashboard**: Overview of system statistics

## Installation

### Prerequisites
- XAMPP (or any PHP/MySQL server)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Database Setup**
   - Open phpMyAdmin or MySQL command line
   - Import the database schema: `database/schema.sql`
   - This will create the database `kasubaytech_db` with all required tables

2. **Database Configuration**
   - Edit `database/config.php` if needed (default settings work with XAMPP)
   - Default settings:
     - Host: localhost
     - Database: kasubaytech_db
     - Username: root
     - Password: (empty)

3. **Admin Login**
   - Default admin credentials:
     - Username: `admin`
     - Password: `admin123`
   - Login at: `login-admin.php`

4. **Access the System**
   - Student registration: `register.php`
   - Admin dashboard: `admin/homepage.php` (after login)

## File Structure

```
kasUbAyTech/
├── admin/
│   ├── api/
│   │   └── questions.php          # API for question CRUD operations
│   ├── compatibility.php          # Compatibility scores page
│   ├── homepage.php              # Admin dashboard
│   ├── login_handler.php         # Admin login handler
│   ├── logout.php                # Logout handler
│   ├── questions.php             # Question management page
│   ├── results.php               # Assessment results page
│   └── view_result.php           # Detailed result view
├── client/
│   ├── assessment.php            # Student assessment page
│   ├── register_code.php         # Student registration handler
│   └── submit_assessment.php     # Assessment submission handler
├── database/
│   ├── config.php                # Database configuration
│   └── schema.sql               # Database schema
├── index.php                     # Landing page
├── login-admin.php               # Admin login page
└── register.php                  # Student registration page
```

## Database Schema

### Tables
- `client` - Student information
- `admin` - Admin users
- `questions` - Assessment questions
- `answer_options` - Answer choices with compatibility scores
- `assessment_results` - Assessment attempts
- `student_answers` - Individual answers
- `compatibility_scores` - Calculated compatibility scores

## Usage Guide

### For Administrators

1. **Login**
   - Go to `login-admin.php`
   - Use admin credentials to login

2. **Manage Questions**
   - Navigate to "Manage Questions"
   - Click "Add Question" to create new questions
   - For each question, add answer options
   - Set compatibility scores (IT, CS, IS) for each option (0-5 scale)
   - Questions are automatically ordered by order_number

3. **View Results**
   - Go to "Assessment Results" to see all student assessments
   - Click "View" to see detailed answers and scores
   - View compatibility breakdown for each student

4. **Compatibility Analysis**
   - Go to "Compatibility Scores" for statistics
   - View average scores per course
   - See course recommendation distribution

### For Students

1. **Register**
   - Go to `register.php`
   - Enter first name, middle name, and last name
   - Submit to start assessment

2. **Take Assessment**
   - Answer questions one by one
   - Each question has a 60-second timer
   - Questions can be single or multiple choice
   - Submit when finished

3. **View Results**
   - After submission, view compatibility scores
   - See recommended course (IT, CS, or IS)
   - Scores are calculated based on answer selections

## Compatibility Scoring

The system calculates compatibility scores based on:
- Each answer option has scores for IT, CS, and IS (0-5 scale)
- Scores are averaged across all answered questions
- Final scores are scaled to 0-100%
- The course with the highest score is recommended

## Security Notes

- Admin passwords are hashed (default password is for demo only)
- SQL injection protection using prepared statements
- XSS protection using htmlspecialchars
- Session-based authentication for admin panel

## Customization

### Adding More Questions
1. Go to Admin > Manage Questions
2. Click "Add Question"
3. Enter question text and type (single/multiple)
4. Add answer options with compatibility scores

### Adjusting Compatibility Scores
- Edit existing questions
- Modify IT, CS, and IS scores for each option
- Higher scores indicate better compatibility

## Troubleshooting

### Database Connection Issues
- Check `database/config.php` settings
- Ensure MySQL service is running
- Verify database `kasubaytech_db` exists

### Assessment Not Loading
- Ensure questions are marked as active (is_active = 1)
- Check that questions have answer options

### Admin Login Not Working
- Default password is `admin123`
- Check database for admin user
- Verify session is enabled in PHP

## License

This project is for educational purposes.

## Support

For issues or questions, please check:
- Database schema in `database/schema.sql`
- PHP error logs
- Browser console for JavaScript errors


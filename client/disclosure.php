<?php 
include '../database/config.php';

if (!isset($_GET['id'])) {
    die("ID not provided");
}

$reference_id = intval($_GET['id']);

// Verify client exists
$stmt = $conn->prepare("SELECT id, firstname, middlename, lastname FROM client WHERE id = ?");
$stmt->bind_param("i", $reference_id);
$stmt->execute();
$client_result = $stmt->get_result();
if ($client_result->num_rows == 0) {
    die("Invalid client ID");
}
$client = $client_result->fetch_assoc();
$stmt->close();

// Get total number of active questions for display
$questions_count = $conn->query("SELECT COUNT(*) as count FROM questions WHERE is_active = 1")->fetch_assoc()['count'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Assessment Information - kasUbAyTech</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        .disclosure-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .disclosure-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin: 2rem 0;
        }
        .info-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .feature-item {
            padding: 1rem;
            margin: 0.5rem 0;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .course-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }
        .course-card:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .course-card h5 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        .start-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .start-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.6);
            color: white;
        }
        .highlight-box {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            border-left: 4px solid #667eea;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
    </style>
</head>

<body>

    <main>
        <section class="disclosure-section d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12">
                        
                        <div class="d-flex justify-content-center py-4">
                            <a href="../index.php" class="logo d-flex align-items-center w-auto">
                                <img src="assets/img/logo.png" alt="">
                                <span class="d-none d-lg-block text-white">kasUbAyTech</span>
                            </a>
                        </div>

                        <div class="disclosure-card">
                            <div class="text-center mb-4">
                                <i class="bi bi-info-circle-fill info-icon"></i>
                                <h2 class="mb-3">Welcome to Course Compatibility Assessment</h2>
                                <p class="lead">Hello, <strong><?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?></strong>!</p>
                                <p class="text-muted">Please read the following information before starting your assessment.</p>
                            </div>

                            <hr class="my-4">

                            <!-- System Introduction -->
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="bi bi-mortarboard-fill text-primary"></i> About This Assessment System
                                </h4>
                                <p>
                                    The <strong>kasUbAyTech Course Compatibility Assessment</strong> is designed to help incoming freshmen 
                                    discover which computer and technology course best aligns with their interests, skills, and career goals. 
                                    This intelligent assessment system uses adaptive algorithms to provide personalized course recommendations.
                                </p>
                            </div>

                            <!-- Assessment Composition -->
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="bi bi-list-check text-primary"></i> What This Assessment Is Composed Of
                                </h4>
                                
                                <div class="feature-item">
                                    <h6><i class="bi bi-check-circle-fill text-success"></i> Adaptive Question Selection</h6>
                                    <p class="mb-0">The assessment uses an intelligent algorithm that adapts questions based on your previous answers, 
                                    ensuring the most relevant questions are asked to accurately determine your course compatibility.</p>
                                </div>

                                <div class="feature-item">
                                    <h6><i class="bi bi-check-circle-fill text-success"></i> Multiple Question Types</h6>
                                    <p class="mb-0">You'll encounter both single-choice and multiple-choice questions covering various aspects 
                                    of technology, programming, problem-solving approaches, and career interests.</p>
                                </div>

                                <div class="feature-item">
                                    <h6><i class="bi bi-check-circle-fill text-success"></i> Real-Time Scoring</h6>
                                    <p class="mb-0">Your compatibility scores for each course are calculated and displayed in real-time as you progress 
                                    through the assessment, giving you immediate feedback on your alignment with each course.</p>
                                </div>

                                <div class="feature-item">
                                    <h6><i class="bi bi-check-circle-fill text-success"></i> Time Management</h6>
                                    <p class="mb-0">Each question has a 60-second timer to help you manage your time effectively. 
                                    The system will automatically proceed if time runs out, but we encourage you to answer thoughtfully.</p>
                                </div>

                                <div class="highlight-box">
                                    <strong><i class="bi bi-info-circle"></i> Assessment Details:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Approximately <strong><?php echo $questions_count; ?> questions</strong> (may vary based on adaptive selection)</li>
                                        <li>Estimated time: <strong>10-15 minutes</strong></li>
                                        <li>Questions adapt based on your answers for better accuracy</li>
                                        <li>You can see your progress and current scores throughout the assessment</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- How It Guides Course Selection -->
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="bi bi-compass text-primary"></i> How This Assessment Guides Your Course Selection
                                </h4>
                                
                                <p class="mb-3">
                                    This assessment evaluates your compatibility with three major computer and technology courses:
                                </p>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="course-card">
                                            <h5><i class="bi bi-laptop"></i> Information Technology (IT)</h5>
                                            <p class="small mb-0">
                                                Focuses on practical application of technology, network management, 
                                                system administration, and IT infrastructure. Ideal for those interested 
                                                in implementing and managing technology solutions.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="course-card">
                                            <h5><i class="bi bi-code-square"></i> Computer Science (CS)</h5>
                                            <p class="small mb-0">
                                                Emphasizes theoretical foundations, algorithms, software development, 
                                                and computational thinking. Perfect for those passionate about 
                                                programming, problem-solving, and creating software solutions.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="course-card">
                                            <h5><i class="bi bi-diagram-3"></i> Information Systems (IS)</h5>
                                            <p class="small mb-0">
                                                Combines business and technology, focusing on system analysis, 
                                                database management, and business process optimization. Great for 
                                                those interested in bridging technology and business needs.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="highlight-box mt-3">
                                    <h6><i class="bi bi-lightbulb-fill text-warning"></i> How It Works:</h6>
                                    <ol class="mb-0">
                                        <li><strong>Answer Questions:</strong> Respond to questions about your interests, skills, and preferences</li>
                                        <li><strong>Real-Time Analysis:</strong> The system calculates your compatibility scores for IT, CS, and IS as you answer</li>
                                        <li><strong>Adaptive Selection:</strong> Questions are intelligently selected to better understand your course fit</li>
                                        <li><strong>Final Recommendation:</strong> Receive a personalized course recommendation based on your highest compatibility score</li>
                                        <li><strong>Detailed Results:</strong> View your compatibility percentages for all three courses to make an informed decision</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Important Notes -->
                            <div class="mb-4">
                                <h4 class="mb-3">
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Important Notes
                                </h4>
                                <div class="alert alert-info">
                                    <ul class="mb-0">
                                        <li>There are no right or wrong answers - be honest about your interests and preferences</li>
                                        <li>This assessment is a <strong>guidance tool</strong> to help you make an informed decision</li>
                                        <li>Your results are based on your responses and are designed to highlight your strengths and interests</li>
                                        <li>You can discuss your results with academic advisors for further guidance</li>
                                        <li>All your responses are confidential and used solely for assessment purposes</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Ready to Start -->
                            <div class="text-center mt-5">
                                <div class="mb-4">
                                    <h5>Ready to Begin?</h5>
                                    <p class="text-muted">Click the button below when you're ready to start your assessment.</p>
                                </div>
                                
                                <a href="assessment_adaptive.php?id=<?php echo $reference_id; ?>" class="btn start-btn btn-lg">
                                    <i class="bi bi-play-circle-fill"></i> Start Assessment
                                </a>
                                
                                <div class="mt-3">
                                    <a href="../index.php" class="text-muted text-decoration-none">
                                        <i class="bi bi-arrow-left"></i> Return to Home
                                    </a>
                                </div>
                            </div>

                        </div>

                        <div class="text-center mt-3">
                            <p class="text-white small">
                                <i class="bi bi-shield-check"></i> Your privacy and data are protected
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>

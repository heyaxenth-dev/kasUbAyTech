<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login-admin.php");
    exit();
}

include '../database/config.php';

// In the new exam system, the ID passed from results.php is the exam session ID
$session_id = intval($_GET['id'] ?? 0);

if ($session_id <= 0) {
    die("Invalid session ID");
}

// Get session + result + student details
$session_query = "SELECT 
        es.*,
        c.firstname,
        c.middlename,
        c.lastname,
        er.recommended_course,
        er.final_score,
        er.confidence_score,
        er.created_at AS completed_at
    FROM exam_sessions es
    JOIN client c ON es.user_id = c.id
    LEFT JOIN exam_results er ON er.session_id = es.id
    WHERE es.id = ?";

$stmt = $conn->prepare($session_query);
if (!$stmt) {
    die("Failed to prepare session query");
}

$stmt->bind_param("i", $session_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$session) {
    die("Result not found");
}

// Get student answers with question details (using new exam_answers table)
$answers_query = "SELECT 
        ea.*,
        q.question_text,
        q.question_type,
        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d
    FROM exam_answers ea
    JOIN questions q ON ea.question_id = q.id
    WHERE ea.session_id = ?
    ORDER BY ea.created_at ASC, ea.id ASC";

$stmt2 = $conn->prepare($answers_query);
if (!$stmt2) {
    die("Failed to prepare answers query");
}

$stmt2->bind_param("i", $session_id);
$stmt2->execute();
$answers = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// Basic aggregates for display
$answeredCount = count($answers);
$uniqueQuestionIds = array_unique(array_column($answers, 'question_id'));
$totalQuestions = count($uniqueQuestionIds);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>View Result - kasUbAyTech Admin</title>
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
</head>

<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="homepage.php" class="logo d-flex align-items-center">
                <img src="assets/img/logo.png" alt="">
                <span class="d-none d-lg-block">kasUbAyTech Admin</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <span
                            class="d-none d-md-block dropdown-toggle ps-2"><?php echo $_SESSION['admin_username']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li><a class="dropdown-item d-flex align-items-center" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link" href="homepage.php">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="questions.php">
                    <i class="bi bi-question-circle"></i>
                    <span>Manage Questions</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="results.php">
                    <i class="bi bi-clipboard-data"></i>
                    <span>Assessment Results</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="compatibility.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Compatibility Scores</span>
                </a>
            </li>
        </ul>
    </aside>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Assessment Result Details</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="homepage.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="results.php">Results</a></li>
                    <li class="breadcrumb-item active">View Result</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <!-- Student Info -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Student Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong>
                                        <?php echo htmlspecialchars($session['firstname'] . ' ' . $session['middlename'] . ' ' . $session['lastname']); ?>
                                    </p>
                                    <p><strong>Started At:</strong>
                                        <?php echo date('M d, Y H:i:s', strtotime($session['created_at'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Completed At:</strong>
                                        <?php echo $session['completed_at'] ? date('M d, Y H:i:s', strtotime($session['completed_at'])) : 'Incomplete'; ?>
                                    </p>
                                    <p><strong>Questions Answered:</strong> <?php echo $answeredCount; ?>
                                        <?php if ($totalQuestions > 0): ?>
                                        / <?php echo $totalQuestions; ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exam Result Summary (new exam system) -->
            <?php if ($session['stage'] === 'FINISHED'): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Exam Result Summary</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>Final Score</h6>
                                            <h3><?php echo isset($session['final_score']) ? intval($session['final_score']) : 0; ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>Confidence</h6>
                                            <h3>
                                                <?php echo isset($session['confidence_score']) ? number_format($session['confidence_score'] * 100, 1) . '%' : 'N/A'; ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>Recommended Course</h6>
                                            <h3>
                                                <?php echo $session['recommended_course'] && $session['recommended_course'] !== 'UNDECIDED'
                                                    ? htmlspecialchars($session['recommended_course'])
                                                    : 'UNDECIDED'; ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Answers -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Student Answers</h5>
                            <?php if (count($answers) > 0): ?>
                            <?php
                                $currentQuestionId = null;
                                $questionNum = 0;
                                foreach ($answers as $answer):
                                    if ($currentQuestionId !== $answer['question_id']):
                                        if ($currentQuestionId !== null) {
                                            echo '</ul></div></div>';
                                        }
                                        $currentQuestionId = $answer['question_id'];
                                        $questionNum++;

                                        // Determine selected option text
                                        $selectedLabel = $answer['selected_option'];
                                        $selectedText = '';
                                        switch ($selectedLabel) {
                                            case 'A': $selectedText = $answer['option_a']; break;
                                            case 'B': $selectedText = $answer['option_b']; break;
                                            case 'C': $selectedText = $answer['option_c']; break;
                                            case 'D': $selectedText = $answer['option_d']; break;
                                        }

                                        $isCorrect = !empty($answer['is_correct']);
                                ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6><?php echo $questionNum; ?>.
                                        <?php echo htmlspecialchars($answer['question_text']); ?></h6>
                                    <p class="text-muted small">
                                        Type: <?php echo ucfirst($answer['question_type']); ?> |
                                        Category: <?php echo htmlspecialchars($answer['category']); ?> |
                                        Selected: <?php echo htmlspecialchars($selectedLabel); ?> -
                                        <?php echo htmlspecialchars($selectedText); ?> |
                                        <?php echo $isCorrect ? 'Correct' : 'Incorrect'; ?>
                                    </p>
                                    <ul class="list-group">
                                        <?php
                                    endif;
                                endforeach;
                                if ($currentQuestionId !== null) {
                                    echo '</ul></div></div>';
                                }
                                ?>
                                        <?php else: ?>
                                        <p class="text-center">No answers found for this assessment.</p>
                                        <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <a href="results.php" class="btn btn-secondary">Back to Results</a>
                        </div>
                    </div>
        </section>
    </main>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>
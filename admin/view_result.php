<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login-admin.php");
    exit();
}

include '../database/config.php';

$result_id = intval($_GET['id'] ?? 0);

// Get result details
$result_query = "SELECT ar.*, c.firstname, c.middlename, c.lastname, 
                 cs.it_score, cs.cs_score, cs.is_score, cs.recommended_course
                 FROM assessment_results ar
                 JOIN client c ON ar.client_id = c.id
                 LEFT JOIN compatibility_scores cs ON ar.id = cs.result_id
                 WHERE ar.id = ?";
$stmt = $conn->prepare($result_query);
$stmt->bind_param("i", $result_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$result) {
    die("Result not found");
}

// Get student answers with question and option details
$answers_query = "SELECT sa.*, q.question_text, q.question_type, ao.option_text
                  FROM student_answers sa
                  JOIN questions q ON sa.question_id = q.id
                  JOIN answer_options ao ON sa.option_id = ao.id
                  WHERE sa.result_id = ?
                  ORDER BY q.order_number, q.id";
$stmt2 = $conn->prepare($answers_query);
$stmt2->bind_param("i", $result_id);
$stmt2->execute();
$answers = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
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
                        <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $_SESSION['admin_username']; ?></span>
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
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($result['firstname'] . ' ' . $result['middlename'] . ' ' . $result['lastname']); ?></p>
                                    <p><strong>Started At:</strong> <?php echo date('M d, Y H:i:s', strtotime($result['started_at'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Completed At:</strong> <?php echo $result['completed_at'] ? date('M d, Y H:i:s', strtotime($result['completed_at'])) : 'Incomplete'; ?></p>
                                    <p><strong>Questions:</strong> <?php echo $result['answered_questions']; ?> / <?php echo $result['total_questions']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compatibility Scores -->
            <?php if ($result['it_score']): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Compatibility Scores</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>IT (Information Technology)</h6>
                                            <h3><?php echo number_format($result['it_score'], 2); ?>%</h3>
                                            <div class="progress mt-2">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $result['it_score']; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>CS (Computer Science)</h6>
                                            <h3><?php echo number_format($result['cs_score'], 2); ?>%</h3>
                                            <div class="progress mt-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $result['cs_score']; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6>IS (Information Systems)</h6>
                                            <h3><?php echo number_format($result['is_score'], 2); ?>%</h3>
                                            <div class="progress mt-2">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $result['is_score']; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 text-center">
                                <div class="alert alert-primary">
                                    <strong>Recommended Course: <?php echo $result['recommended_course']; ?></strong>
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
                            $current_question = null;
                            $question_num = 0;
                            foreach ($answers as $answer): 
                                if ($current_question != $answer['question_id']):
                                    if ($current_question !== null) echo '</div></div>';
                                    $current_question = $answer['question_id'];
                                    $question_num++;
                            ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6><?php echo $question_num; ?>. <?php echo htmlspecialchars($answer['question_text']); ?></h6>
                                    <p class="text-muted small">Type: <?php echo ucfirst($answer['question_type']); ?></p>
                                    <ul class="list-group">
                            <?php endif; ?>
                                        <li class="list-group-item">
                                            <i class="bi bi-check-circle-fill text-success"></i> 
                                            <?php echo htmlspecialchars($answer['option_text']); ?>
                                        </li>
                            <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
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


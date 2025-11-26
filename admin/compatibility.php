<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login-admin.php");
    exit();
}

include '../database/config.php';

// Get compatibility statistics from new exam_results table
$stats_query = "SELECT 
    COUNT(*) AS total_assessments,
    SUM(CASE WHEN er.recommended_course = 'IT' THEN 1 ELSE 0 END) AS recommended_it,
    SUM(CASE WHEN er.recommended_course = 'CS' THEN 1 ELSE 0 END) AS recommended_cs,
    SUM(CASE WHEN er.recommended_course = 'IS' THEN 1 ELSE 0 END) AS recommended_is
FROM exam_results er";
$stats_result = $conn->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : [
    'total_assessments' => 0,
    'recommended_it' => 0,
    'recommended_cs' => 0,
    'recommended_is' => 0,
];

// Derive percentage distribution of recommendations per course
$totalAssessments = (int)($stats['total_assessments'] ?? 0);
$itCount = (int)($stats['recommended_it'] ?? 0);
$csCount = (int)($stats['recommended_cs'] ?? 0);
$isCount = (int)($stats['recommended_is'] ?? 0);

$avg_it = $totalAssessments > 0 ? ($itCount / $totalAssessments) * 100 : 0;
$avg_cs = $totalAssessments > 0 ? ($csCount / $totalAssessments) * 100 : 0;
$avg_is = $totalAssessments > 0 ? ($isCount / $totalAssessments) * 100 : 0;

// Get all compatibility scores with student info aligned to new exam schema
$scores_query = "SELECT 
        er.*,
        c.firstname,
        c.lastname,
        es.created_at AS completed_at
    FROM exam_results er
    JOIN exam_sessions es ON er.session_id = es.id
    JOIN client c ON es.user_id = c.id
    ORDER BY er.created_at DESC";
$scores_result = $conn->query($scores_query);
$scores = $scores_result ? $scores_result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Compatibility Scores - kasUbAyTech Admin</title>
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
    <script src="assets/vendor/chart.js/chart.umd.js"></script>

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
                <a class="nav-link active" href="compatibility.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Compatibility Scores</span>
                </a>
            </li>
        </ul>
    </aside>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Compatibility Scores</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="homepage.php">Home</a></li>
                    <li class="breadcrumb-item active">Compatibility</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Assessments</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo $stats['total_assessments'] ?? 0; ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Avg IT Score</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-laptop"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo number_format($avg_it ?? 0, 2); ?>%</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Avg CS Score</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-code-square"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo number_format($avg_cs ?? 0, 2); ?>%</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">Avg IS Score</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <div class="ps-3">
                                    <h6><?php echo number_format($avg_is ?? 0, 2); ?>%</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Average Scores by Course</h5>
                            <canvas id="avgScoresChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Course Recommendations</h5>
                            <canvas id="recommendationsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Scores Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Detailed Compatibility Scores</h5>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Recommended</th>
                                            <th>Final Score</th>
                                            <th>Confidence</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($scores) > 0): ?>
                                        <?php foreach ($scores as $score): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($score['firstname'] . ' ' . $score['lastname']); ?>
                                            </td>
                                            <td>
                                                <?php if ($score['recommended_course'] && $score['recommended_course'] !== 'UNDECIDED'): ?>
                                                <span
                                                    class="badge bg-primary"><?php echo $score['recommended_course']; ?></span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">UNDECIDED</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo isset($score['final_score']) ? intval($score['final_score']) : 0; ?>
                                            </td>
                                            <td>
                                                <?php echo isset($score['confidence_score']) ? number_format($score['confidence_score'] * 100, 1) . '%' : '0.0%'; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($score['completed_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No compatibility scores found.</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    // Average Scores Chart
    const avgCtx = document.getElementById('avgScoresChart').getContext('2d');
    new Chart(avgCtx, {
        type: 'bar',
        data: {
            labels: ['IT', 'CS', 'IS'],
            datasets: [{
                label: 'Average Score (%)',
                data: [
                    <?php echo number_format($avg_it ?? 0, 2); ?>,
                    <?php echo number_format($avg_cs ?? 0, 2); ?>,
                    <?php echo number_format($avg_is ?? 0, 2); ?>
                ],
                backgroundColor: ['#4154f1', '#2eca6a', '#ff771d']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Recommendations Chart
    const recCtx = document.getElementById('recommendationsChart').getContext('2d');
    new Chart(recCtx, {
        type: 'doughnut',
        data: {
            labels: ['IT', 'CS', 'IS'],
            datasets: [{
                data: [
                    <?php echo $stats['recommended_it'] ?? 0; ?>,
                    <?php echo $stats['recommended_cs'] ?? 0; ?>,
                    <?php echo $stats['recommended_is'] ?? 0; ?>
                ],
                backgroundColor: ['#4154f1', '#2eca6a', '#ff771d']
            }]
        },
        options: {
            responsive: true
        }
    });
    </script>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>
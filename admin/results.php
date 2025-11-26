<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login-admin.php");
    exit();
}

include '../database/config.php';

// Get all exam sessions with results and student info (aligned with new exam_results schema)
$query = "SELECT 
            es.id, 
            es.user_id, 
            es.stage, 
            es.created_at, 
            es.dominant_category,
            c.firstname, 
            c.middlename, 
            c.lastname, 
            er.recommended_course, 
            er.final_score, 
            er.confidence_score, 
            er.created_at AS completed_at,
            (SELECT COUNT(*) FROM exam_answers WHERE session_id = es.id) AS answered_questions
          FROM exam_sessions es
          JOIN client c ON es.user_id = c.id
          LEFT JOIN exam_results er ON es.id = er.session_id
          ORDER BY es.created_at DESC";
$results = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Assessment Results - kasUbAyTech Admin</title>
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
                <a class="nav-link active" href="results.php">
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
            <h1>Assessment Results</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="homepage.php">Home</a></li>
                    <li class="breadcrumb-item active">Results</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Student Assessment Results</h5>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Session ID</th>
                                            <th>Student Name</th>
                                            <th>Started At</th>
                                            <th>Completed At</th>
                                            <th>Stage</th>
                                            <th>Answered</th>
                                            <th>Recommended</th>
                                            <th>Final Score</th>
                                            <th>Confidence</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($results) > 0): ?>
                                        <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?php echo $result['id']; ?></td>
                                            <td><?php echo htmlspecialchars($result['firstname'] . ' ' . $result['lastname']); ?>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($result['created_at'])); ?></td>
                                            <td><?php echo $result['completed_at'] ? date('M d, Y H:i', strtotime($result['completed_at'])) : 'Incomplete'; ?>
                                            </td>
                                            <td>
                                                <?php if ($result['stage'] === 'FINISHED'): ?>
                                                <span class="badge bg-success">Finished</span>
                                                <?php elseif ($result['stage'] === 'CATEGORY'): ?>
                                                <span class="badge bg-warning text-dark">Category</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Diagnostic</span>
                                                <?php endif; ?>
                                                <?php if (!empty($result['dominant_category'])): ?>
                                                <span
                                                    class="badge bg-info ms-1"><?php echo $result['dominant_category']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $result['answered_questions']; ?></td>
                                            <td>
                                                <?php if ($result['recommended_course'] && $result['recommended_course'] !== 'UNDECIDED'): ?>
                                                <span
                                                    class="badge bg-primary"><?php echo $result['recommended_course']; ?></span>
                                                <?php else: ?>
                                                <span
                                                    class="badge bg-secondary"><?php echo $result['stage'] === 'FINISHED' ? 'UNDECIDED' : 'In Progress'; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $result['final_score'] !== null ? intval($result['final_score']) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <?php echo $result['confidence_score'] !== null ? number_format($result['confidence_score'] * 100, 1) . '%' : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <a href="view_result.php?id=<?php echo $result['id']; ?>"
                                                    class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No assessment results found.</td>
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

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/js/main.js"></script>

</body>

</html>
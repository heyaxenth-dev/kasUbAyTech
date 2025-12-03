<?php
include './authentication.php';
include '../database/config.php';

// Get statistics (aligned with new exam system tables)
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM client) AS total_students,
    (SELECT COUNT(*) FROM exam_sessions) AS total_assessments,
    (SELECT COUNT(*) FROM questions WHERE is_active = 1) AS active_questions,
    (SELECT COUNT(*) FROM exam_results) AS completed_assessments";
$stats_result = $conn->query($stats_query);
$stats = $stats_result ? $stats_result->fetch_assoc() : [
    'total_students' => 0,
    'total_assessments' => 0,
    'active_questions' => 0,
    'completed_assessments' => 0,
];

// Get recent assessments from new exam system
$recent_query = "SELECT 
        es.id AS session_id,
        es.created_at AS started_at,
        c.firstname,
        c.lastname,
        er.recommended_course,
        er.created_at AS completed_at
    FROM exam_sessions es
    JOIN client c ON es.user_id = c.id
    LEFT JOIN exam_results er ON es.id = er.session_id
    ORDER BY er.created_at DESC, es.created_at DESC
    LIMIT 5";
$recent_result = $conn->query($recent_query);
$recent = $recent_result ? $recent_result->fetch_all(MYSQLI_ASSOC) : [];

// Get course recommendations count from exam_results
$course_stats_query = "SELECT 
    SUM(CASE WHEN recommended_course = 'IT' THEN 1 ELSE 0 END) AS it_count,
    SUM(CASE WHEN recommended_course = 'CS' THEN 1 ELSE 0 END) AS cs_count,
    SUM(CASE WHEN recommended_course = 'IS' THEN 1 ELSE 0 END) AS is_count
    FROM exam_results";
$course_stats_result = $conn->query($course_stats_query);
$course_stats = $course_stats_result ? $course_stats_result->fetch_assoc() : [
    'it_count' => 0,
    'cs_count' => 0,
    'is_count' => 0,
];
$conn->close();

include './includes/header.php';
include './includes/sidebar.php';
?>


<main id="main" class="main">

    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="homepage.php">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">

                    <!-- Total Students Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Students</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['total_students'] ?? 0; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Assessments Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Assessments</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-clipboard-data"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['total_assessments'] ?? 0; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Questions Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Active Questions</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-question-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['active_questions'] ?? 0; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Assessments Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card">
                            <div class="card-body">
                                <h5 class="card-title">Completed</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $stats['completed_assessments'] ?? 0; ?></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Recommendations -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Course Recommendations</h5>
                                <div class="row">
                                    <div class="col-4 text-center">
                                        <h6>IT</h6>
                                        <h3><?php echo $course_stats['it_count'] ?? 0; ?></h3>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h6>CS</h6>
                                        <h3><?php echo $course_stats['cs_count'] ?? 0; ?></h3>
                                    </div>
                                    <div class="col-4 text-center">
                                        <h6>IS</h6>
                                        <h3><?php echo $course_stats['is_count'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Quick Actions</h5>
                                <div class="d-grid gap-2">
                                    <a href="questions.php" class="btn btn-primary">
                                        <i class="bi bi-question-circle"></i> Manage Questions
                                    </a>
                                    <a href="results.php" class="btn btn-success">
                                        <i class="bi bi-clipboard-data"></i> View Results
                                    </a>
                                    <a href="compatibility.php" class="btn btn-info">
                                        <i class="bi bi-graph-up"></i> Compatibility Scores
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Assessments -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Assessments</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Completed</th>
                                                <th>Recommended</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recent) > 0): ?>
                                            <?php foreach ($recent as $r): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['lastname']); ?>
                                                </td>
                                                <td><?php echo $r['completed_at'] ? date('M d, Y H:i', strtotime($r['completed_at'])) : 'Incomplete'; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($r['recommended_course']) && $r['recommended_course'] !== 'UNDECIDED'): ?>
                                                    <span
                                                        class="badge bg-primary"><?php echo $r['recommended_course']; ?></span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="view_result.php?id=<?php echo $r['session_id']; ?>"
                                                        class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No recent assessments</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

</main><!-- End #main -->
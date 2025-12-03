<?php
include './authentication.php';
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


include './includes/header.php';
include './includes/sidebar.php';
?>

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
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Average Scores by Course</h5>
                        <canvas id="avgScoresChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
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
// Defer chart initialization until after all vendor scripts (including Chart.js) are loaded
window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') {
        return; // Chart.js not available
    }

    const avgCanvas = document.getElementById('avgScoresChart');
    const recCanvas = document.getElementById('recommendationsChart');

    if (avgCanvas) {
        const avgCtx = avgCanvas.getContext('2d');
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
    }

    if (recCanvas) {
        const recCtx = recCanvas.getContext('2d');
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
    }
});
</script>

<?php
include './includes/footer.php';
?>
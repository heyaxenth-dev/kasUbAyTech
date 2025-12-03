<?php
include './authentication.php';
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

include './includes/header.php';
include './includes/sidebar.php';
?>

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

<?php 
include './includes/footer.php';
?>
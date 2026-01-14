<?php
include './authentication.php';
include '../database/config.php';

// Get all exam sessions with results and student info (aligned with new exam_results schema)
// First get basic session data
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

// Calculate course scores for each session
foreach ($results as &$result) {
    $sessionId = $result['id'];
    $itScore = 0;
    $csScore = 0;
    $isScore = 0;
    
    // Get all answers for this session
    $answersQuery = "SELECT ea.question_id, ea.selected_option 
                     FROM exam_answers ea 
                     WHERE ea.session_id = ?";
    $stmt = $conn->prepare($answersQuery);
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $answers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // For each answer, get the option scores
    foreach ($answers as $answer) {
        $questionId = $answer['question_id'];
        $selectedOption = $answer['selected_option'];
        
        // Map A/B/C/D to index (0, 1, 2, 3)
        $optionIndex = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3][$selectedOption] ?? 0;
        
        // Get the option scores for the selected option
        $optionQuery = "SELECT it_score, cs_score, is_score 
                        FROM answer_options 
                        WHERE question_id = ? 
                        ORDER BY id 
                        LIMIT 1 OFFSET ?";
        $optStmt = $conn->prepare($optionQuery);
        $optStmt->bind_param("ii", $questionId, $optionIndex);
        $optStmt->execute();
        $optionResult = $optStmt->get_result();
        
        if ($optionRow = $optionResult->fetch_assoc()) {
            $itScore += floatval($optionRow['it_score'] ?? 0);
            $csScore += floatval($optionRow['cs_score'] ?? 0);
            $isScore += floatval($optionRow['is_score'] ?? 0);
        }
        $optStmt->close();
    }
    
    $result['it_score'] = $itScore;
    $result['cs_score'] = $csScore;
    $result['is_score'] = $isScore;
}
unset($result); // Break reference

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
                                        <th>Course Scores</th>
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
                                            <?php if (!empty($result['dominant_category'])): ?>
                                            <span class="badge bg-info ms-1">
                                                <?php echo htmlspecialchars($result['dominant_category']); ?>
                                            </span>
                                            <?php endif; ?>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Diagnostic</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo intval($result['answered_questions'] ?? 0); ?></td>
                                        <td>
                                            <small>
                                                IT: <strong><?php echo number_format(floatval($result['it_score'] ?? 0), 1); ?></strong> |
                                                CS: <strong><?php echo number_format(floatval($result['cs_score'] ?? 0), 1); ?></strong> |
                                                IS: <strong><?php echo number_format(floatval($result['is_score'] ?? 0), 1); ?></strong>
                                            </small>
                                        </td>
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
                                        <td colspan="11" class="text-center">No assessment results found.</td>
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
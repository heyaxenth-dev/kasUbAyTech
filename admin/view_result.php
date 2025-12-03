<?php 
include './authentication.php';
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
include './includes/header.php';
include './includes/sidebar.php';
?>

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

<?php 
include './includes/footer.php';
?>
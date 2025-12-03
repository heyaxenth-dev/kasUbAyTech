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

// Load active questions
$questions_result = $conn->query("SELECT * FROM questions WHERE is_active = 1 ORDER BY order_number, id");
$questions = $questions_result->fetch_all(MYSQLI_ASSOC);

// Load answer options for each question
foreach ($questions as &$question) {
    $qid = $question['id'];
    $options_result = $conn->query("SELECT * FROM answer_options WHERE question_id = $qid ORDER BY id");
    $question['options'] = $options_result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Assessment - kasUbAyTech</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.ico" rel="icon">
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

    <main>
        <section
            class="hero accent-background section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8 d-flex flex-column align-items-center justify-content-center">

                        <div class="d-flex justify-content-center py-4">
                            <a href="../index.php" class="logo d-flex align-items-center w-auto">
                                <img src="assets/img/logo.png" alt="">
                                <span class="d-none d-lg-block text-white">kasUbAyTech</span>
                            </a>
                        </div>

                        <div class="container mt-5">
                            <div class="card">
                                <div class="d-flex justify-content-center py-4">
                                    <div id="timer" class="fw-bold fs-4 text-danger">01:00</div>
                                </div>

                                <div class="card-body" id="quizCard">
                                    <h5 class="card-title text-center pb-0 fs-4">Course Compatibility Assessment</h5>
                                    <p class="text-center small">Welcome,
                                        <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?>
                                    </p>
                                    <p class="text-center small">Answer the questions one by one</p>

                                    <form id="quizForm">
                                        <?php 
                                        $qIndex = 0;
                                        foreach ($questions as $question): 
                                            $qIndex++;
                                            $qId = $question['id'];
                                            $isMultiple = $question['question_type'] === 'multiple';
                                            $inputType = $isMultiple ? 'checkbox' : 'radio';
                                            $inputName = $isMultiple ? "q{$qId}[]" : "q{$qId}";
                                        ?>
                                        <div class="question d-none" id="question<?php echo $qId; ?>"
                                            data-question-id="<?php echo $qId; ?>"
                                            data-type="<?php echo $question['question_type']; ?>">
                                            <h6 class="mb-3"><?php echo $qIndex; ?>.
                                                <?php echo htmlspecialchars($question['question_text']); ?></h6>
                                            <?php foreach ($question['options'] as $option): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="<?php echo $inputType; ?>"
                                                    name="<?php echo $inputName; ?>"
                                                    id="q<?php echo $qId; ?>_opt<?php echo $option['id']; ?>"
                                                    value="<?php echo $option['id']; ?>"
                                                    <?php echo !$isMultiple ? 'required' : ''; ?>>
                                                <label class="form-check-label"
                                                    for="q<?php echo $qId; ?>_opt<?php echo $option['id']; ?>">
                                                    <?php echo htmlspecialchars($option['option_text']); ?>
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                            <?php if ($qIndex < count($questions)): ?>
                                            <button type="button"
                                                class="btn btn-primary w-100 mt-3 next-btn">Next</button>
                                            <?php else: ?>
                                            <button type="submit" class="btn btn-success w-100 mt-3">Submit
                                                Answers</button>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>

                                        <?php if (count($questions) == 0): ?>
                                        <div class="alert alert-warning">
                                            No questions available. Please contact the administrator.
                                        </div>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <style>
                        .slide-out-left {
                            animation: slideOutLeft 0.4s forwards;
                        }

                        .slide-in-right {
                            animation: slideInRight 0.4s forwards;
                        }

                        @keyframes slideOutLeft {
                            from {
                                transform: translateX(0);
                                opacity: 1;
                            }

                            to {
                                transform: translateX(-100%);
                                opacity: 0;
                            }
                        }

                        @keyframes slideInRight {
                            from {
                                transform: translateX(100%);
                                opacity: 0;
                            }

                            to {
                                transform: translateX(0);
                                opacity: 1;
                            }
                        }
                        </style>

                        <script>
                        const quizCard = document.getElementById('quizCard');
                        const questions = document.querySelectorAll('.question');
                        let currentIndex = 0;
                        const clientId = <?php echo $reference_id; ?>;
                        const questionsData = <?php echo json_encode($questions); ?>;

                        // Timer Variables
                        let timerInterval;
                        let timeLeft = 60;

                        // Show first question
                        if (questions.length > 0) {
                            questions[0].classList.remove('d-none');
                            startTimer();
                        }

                        function startTimer() {
                            clearInterval(timerInterval);
                            timeLeft = 60;
                            updateTimerDisplay();

                            timerInterval = setInterval(() => {
                                timeLeft--;
                                updateTimerDisplay();

                                if (timeLeft <= 0) {
                                    clearInterval(timerInterval);
                                    autoNextQuestion();
                                }
                            }, 1000);
                        }

                        function updateTimerDisplay() {
                            const minutes = String(Math.floor(timeLeft / 60)).padStart(2, "0");
                            const seconds = String(timeLeft % 60).padStart(2, "0");
                            document.getElementById("timer").textContent = `${minutes}:${seconds}`;
                        }

                        function autoNextQuestion() {
                            const nextBtn = document.querySelectorAll('.next-btn')[currentIndex];
                            if (nextBtn) {
                                nextBtn.click();
                            } else {
                                document.getElementById('quizForm').requestSubmit();
                            }
                        }

                        // Next button logic
                        document.querySelectorAll('.next-btn').forEach((button, index) => {
                            button.addEventListener('click', function() {
                                const currentQ = questions[currentIndex];
                                const nextQ = questions[currentIndex + 1];

                                if (!nextQ) return;

                                // Validate current question
                                const questionType = currentQ.dataset.type;
                                const inputs = currentQ.querySelectorAll(
                                    'input[type="radio"], input[type="checkbox"]');
                                let hasSelection = false;

                                if (questionType === 'multiple') {
                                    hasSelection = Array.from(inputs).some(input => input.checked);
                                } else {
                                    hasSelection = Array.from(inputs).some(input => input.checked);
                                }

                                if (!hasSelection) {
                                    alert('Please select an answer before proceeding.');
                                    return;
                                }

                                quizCard.classList.add('slide-out-left');

                                quizCard.addEventListener('animationend', function handler() {
                                    currentQ.classList.add('d-none');
                                    nextQ.classList.remove('d-none');

                                    quizCard.classList.remove('slide-out-left');
                                    quizCard.classList.add('slide-in-right');

                                    quizCard.addEventListener('animationend',
                                        function handler2() {
                                            quizCard.classList.remove('slide-in-right');
                                            quizCard.removeEventListener('animationend',
                                                handler2);
                                        });

                                    currentIndex++;
                                    quizCard.removeEventListener('animationend', handler);
                                    startTimer();
                                });
                            });
                        });

                        // Form submit
                        document.getElementById('quizForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            clearInterval(timerInterval);

                            // Collect all answers
                            const answers = [];
                            questions.forEach(question => {
                                const questionId = question.dataset.questionId;
                                const questionType = question.dataset.type;
                                const inputs = question.querySelectorAll('input:checked');

                                const optionIds = Array.from(inputs).map(input => parseInt(input
                                    .value));

                                if (optionIds.length > 0) {
                                    answers.push({
                                        question_id: parseInt(questionId),
                                        option_ids: questionType === 'multiple' ? optionIds : [
                                            optionIds[0]
                                        ]
                                    });
                                }
                            });

                            // Submit to server
                            const formData = new FormData();
                            formData.append('client_id', clientId);
                            formData.append('answers', JSON.stringify(answers));

                            quizCard.classList.add('slide-out-left');

                            quizCard.addEventListener('animationend', function handler() {
                                quizCard.innerHTML =
                                    '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Processing your results...</p></div>';
                                quizCard.classList.remove('slide-out-left');
                                quizCard.classList.add('slide-in-right');
                                quizCard.removeEventListener('animationend', handler);

                                fetch('submit_assessment.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            quizCard.innerHTML = `
                                            <div class="text-center">
                                                <h5 class="mb-4">🎉 Assessment Completed!</h5>
                                                <h6 class="mb-3">Your Compatibility Scores:</h6>
                                                <div class="row mb-3">
                                                    <div class="col-4">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h6>IT</h6>
                                                                <h4>${data.scores.IT}%</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h6>CS</h6>
                                                                <h4>${data.scores.CS}%</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <h6>IS</h6>
                                                                <h4>${data.scores.IS}%</h4>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="alert alert-info">
                                                    <strong>Recommended Course: ${data.recommended}</strong>
                                                </div>
                                                <a href="../index.php" class="btn btn-primary">Return to Home</a>
                                            </div>
                                        `;
                                        } else {
                                            quizCard.innerHTML = `
                                            <div class="text-center">
                                                <h5 class="text-danger">Error</h5>
                                                <p>${data.error || 'An error occurred while processing your assessment.'}</p>
                                                <a href="../index.php" class="btn btn-primary">Return to Home</a>
                                            </div>
                                        `;
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        quizCard.innerHTML = `
                                        <div class="text-center">
                                            <h5 class="text-danger">Error</h5>
                                            <p>An error occurred. Please try again later.</p>
                                            <a href="../index.php" class="btn btn-primary">Return to Home</a>
                                        </div>
                                    `;
                                    });
                            });
                        });
                        </script>

                        <div class="credits">
                            Designed by <a href="https://bootstrapmade.com/" class="text-white">BootstrapMade</a>
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
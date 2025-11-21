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

// Get all active question IDs for reference
$questions_result = $conn->query("SELECT id FROM questions WHERE is_active = 1 ORDER BY order_number, id");
$all_question_ids = [];
while ($row = $questions_result->fetch_assoc()) {
    $all_question_ids[] = $row['id'];
}
$conn->close();

// Adaptive service URL
$adaptive_service_url = 'http://localhost:5000';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Adaptive Assessment - kasUbAyTech</title>
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
                                    <p class="text-center small">Welcome, <?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?></p>
                                    <p class="text-center small">Questions will adapt based on your answers for better accuracy</p>

                                    <!-- Progress Indicator -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Progress</small>
                                            <small id="progressText">0 / 0</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>

                                    <!-- Current Scores Display -->
                                    <div id="currentScores" class="mb-3 d-none">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted">IT</small>
                                                <div id="scoreIT" class="fw-bold">0%</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">CS</small>
                                                <div id="scoreCS" class="fw-bold">0%</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">IS</small>
                                                <div id="scoreIS" class="fw-bold">0%</div>
                                            </div>
                                        </div>
                                    </div>

                                    <form id="quizForm">
                                        <div id="questionContainer">
                                            <!-- Questions will be loaded here dynamically -->
                                        </div>
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
                        const clientId = <?php echo $reference_id; ?>;
                        const adaptiveServiceUrl = '<?php echo $adaptive_service_url; ?>';
                        const allQuestionIds = <?php echo json_encode($all_question_ids); ?>;
                        
                        let answeredQuestions = [];
                        let currentQuestion = null;
                        let timerInterval;
                        let timeLeft = 60;
                        let totalQuestions = 0;
                        let answeredCount = 0;

                        // Start assessment
                        document.addEventListener('DOMContentLoaded', function() {
                            loadNextQuestion();
                        });

                        function loadNextQuestion() {
                            // Show loading
                            document.getElementById('questionContainer').innerHTML = 
                                '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-2">Loading next question...</p></div>';

                            fetch(adaptiveServiceUrl + '/get_next_question', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    answered_questions: answeredQuestions,
                                    all_question_ids: allQuestionIds
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success && data.question) {
                                    displayQuestion(data.question);
                                    updateProgress();
                                    startTimer();
                                } else {
                                    // No more questions, submit assessment
                                    submitAssessment();
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                // Fallback: try regular assessment
                                console.log('Adaptive service unavailable, trying regular assessment...');
                                // Load questions from database directly
                                loadRegularAssessment();
                            });
                        }

                        function displayQuestion(question) {
                            currentQuestion = question;
                            const container = document.getElementById('questionContainer');
                            const isMultiple = question.question_type === 'multiple';
                            const inputType = isMultiple ? 'checkbox' : 'radio';
                            const inputName = isMultiple ? `q${question.question_id}[]` : `q${question.question_id}`;

                            let html = `
                                <div class="question" id="question${question.question_id}">
                                    <h6 class="mb-3">${answeredCount + 1}. ${question.question_text}</h6>
                            `;

                            question.options.forEach((option, index) => {
                                html += `
                                    <div class="form-check">
                                        <input class="form-check-input" type="${inputType}" 
                                            name="${inputName}" 
                                            id="q${question.question_id}_opt${option.id}"
                                            value="${option.id}" 
                                            ${!isMultiple ? 'required' : ''}>
                                        <label class="form-check-label" for="q${question.question_id}_opt${option.id}">
                                            ${option.option_text}
                                        </label>
                                    </div>
                                `;
                            });

                            html += `
                                    <button type="button" class="btn btn-primary w-100 mt-3" onclick="submitAnswer()">
                                        Next Question
                                    </button>
                                </div>
                            `;

                            container.innerHTML = html;

                            // Update current scores display
                            if (question.current_scores) {
                                document.getElementById('currentScores').classList.remove('d-none');
                                document.getElementById('scoreIT').textContent = question.current_scores.IT + '%';
                                document.getElementById('scoreCS').textContent = question.current_scores.CS + '%';
                                document.getElementById('scoreIS').textContent = question.current_scores.IS + '%';
                            }
                        }

                        function submitAnswer() {
                            if (!currentQuestion) return;

                            const questionId = currentQuestion.question_id;
                            const inputs = document.querySelectorAll(`input[name="q${questionId}"], input[name="q${questionId}[]"]:checked`);
                            
                            const selectedOptions = Array.from(inputs)
                                .filter(input => input.checked)
                                .map(input => parseInt(input.value));

                            if (selectedOptions.length === 0) {
                                alert('Please select an answer before proceeding.');
                                return;
                            }

                            // Add to answered questions
                            answeredQuestions.push({
                                question_id: questionId,
                                option_ids: selectedOptions
                            });

                            answeredCount++;
                            clearInterval(timerInterval);

                            // Animate transition
                            const container = document.getElementById('questionContainer');
                            container.classList.add('slide-out-left');

                            container.addEventListener('animationend', function handler() {
                                container.classList.remove('slide-out-left');
                                container.classList.add('slide-in-right');
                                
                                setTimeout(() => {
                                    container.classList.remove('slide-in-right');
                                    loadNextQuestion();
                                }, 400);
                                
                                container.removeEventListener('animationend', handler);
                            }, { once: true });
                        }

                        // Update timer auto-submit to handle both modes
                        function handleTimerExpiry() {
                            clearInterval(timerInterval);
                            if (useRegularMode) {
                                if (regularQuestionIndex < regularQuestions.length) {
                                    submitRegularAnswer();
                                }
                            } else {
                                if (currentQuestion) {
                                    const inputs = document.querySelectorAll(`input[name="q${currentQuestion.question_id}"], input[name="q${currentQuestion.question_id}[]"]:checked`);
                                    if (inputs.length > 0) {
                                        submitAnswer();
                                    } else {
                                        // Skip question if no answer selected
                                        answeredQuestions.push({
                                            question_id: currentQuestion.question_id,
                                            option_ids: []
                                        });
                                        answeredCount++;
                                        loadNextQuestion();
                                    }
                                }
                            }
                        }

                        function updateProgress() {
                            totalQuestions = Math.max(totalQuestions, answeredCount + 1);
                            const progress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
                            document.getElementById('progressBar').style.width = progress + '%';
                            document.getElementById('progressText').textContent = `${answeredCount} / ${totalQuestions}`;
                        }

                        function startTimer() {
                            clearInterval(timerInterval);
                            timeLeft = 60;
                            updateTimerDisplay();

                            timerInterval = setInterval(() => {
                                timeLeft--;
                                updateTimerDisplay();

                                if (timeLeft <= 0) {
                                    handleTimerExpiry();
                                }
                            }, 1000);
                        }

                        function updateTimerDisplay() {
                            const minutes = String(Math.floor(timeLeft / 60)).padStart(2, "0");
                            const seconds = String(timeLeft % 60).padStart(2, "0");
                            document.getElementById("timer").textContent = `${minutes}:${seconds}`;
                        }

                        function submitAssessment() {
                            clearInterval(timerInterval);
                            
                            const container = document.getElementById('questionContainer');
                            container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div><p class="mt-3">Processing your results...</p></div>';

                            // Submit to PHP handler
                            const formData = new FormData();
                            formData.append('client_id', clientId);
                            formData.append('answers', JSON.stringify(answeredQuestions));

                            fetch('submit_assessment.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    container.innerHTML = `
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
                                    container.innerHTML = `
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
                                container.innerHTML = `
                                    <div class="text-center">
                                        <h5 class="text-danger">Error</h5>
                                        <p>An error occurred. Please try again later.</p>
                                        <a href="../index.php" class="btn btn-primary">Return to Home</a>
                                    </div>
                                `;
                            });
                        }
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


<?php 

$_GET['id'] or die("ID not provided");

$reference_id = $_GET['id'];

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
                            <a href="index.html" class="logo d-flex align-items-center w-auto">
                                <img src="assets/img/logo.png" alt="">
                                <span class="d-none d-lg-block text-white">kasUbAyTech</span>
                            </a>
                        </div><!-- End Logo -->

                        <div class="container mt-5">
                            <div class="card">
                                <div class="card-body" id="quizCard">

                                    <h5 class="card-title text-center pb-0 fs-4">Multiple Choice Questionnaire</h5>
                                    <p class="text-center small">Answer the questions one by one</p>

                                    <form id="quizForm">

                                        <!-- Question 1 -->
                                        <div class="question" id="question1">
                                            <h6 class="mb-3">1. What is your favorite programming language?</h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" id="q1a"
                                                    value="Python" required>
                                                <label class="form-check-label" for="q1a">Python</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" id="q1b"
                                                    value="JavaScript">
                                                <label class="form-check-label" for="q1b">JavaScript</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q1" id="q1c"
                                                    value="PHP">
                                                <label class="form-check-label" for="q1c">PHP</label>
                                            </div>
                                            <button type="button"
                                                class="btn btn-primary w-100 mt-3 next-btn">Next</button>
                                        </div>

                                        <!-- Question 2 -->
                                        <div class="question d-none" id="question2">
                                            <h6 class="mb-3">2. Which front-end framework do you prefer?</h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" id="q2a"
                                                    value="Bootstrap" required>
                                                <label class="form-check-label" for="q2a">Bootstrap</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" id="q2b"
                                                    value="Tailwind">
                                                <label class="form-check-label" for="q2b">Tailwind</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q2" id="q2c"
                                                    value="Material UI">
                                                <label class="form-check-label" for="q2c">Material UI</label>
                                            </div>
                                            <button type="button"
                                                class="btn btn-primary w-100 mt-3 next-btn">Next</button>
                                        </div>

                                        <!-- Question 3 -->
                                        <div class="question d-none" id="question3">
                                            <h6 class="mb-3">3. Which databases have you used? (Select all that apply)
                                            </h6>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="q3[]" id="q3a"
                                                    value="MySQL">
                                                <label class="form-check-label" for="q3a">MySQL</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="q3[]" id="q3b"
                                                    value="PostgreSQL">
                                                <label class="form-check-label" for="q3b">PostgreSQL</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="q3[]" id="q3c"
                                                    value="MongoDB">
                                                <label class="form-check-label" for="q3c">MongoDB</label>
                                            </div>
                                            <button type="submit" class="btn btn-success w-100 mt-3">Submit
                                                Answers</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                        <style>
                        /* Animation styles */
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

                        document.querySelectorAll('.next-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const currentQ = questions[currentIndex];
                                const nextQ = questions[currentIndex + 1];

                                if (!nextQ) return;

                                // Slide out current question
                                quizCard.classList.add('slide-out-left');

                                quizCard.addEventListener('animationend', function handler() {
                                    // Hide current, show next
                                    currentQ.classList.add('d-none');
                                    nextQ.classList.remove('d-none');

                                    // Reset animation, slide in new content
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
                                });
                            });
                        });

                        document.getElementById('quizForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            quizCard.classList.add('slide-out-left');

                            quizCard.addEventListener('animationend', function handler() {
                                quizCard.innerHTML =
                                    `<h5 class="text-center">🎉 Thank you for completing the quiz!</h5>`;
                                quizCard.classList.remove('slide-out-left');
                                quizCard.classList.add('slide-in-right');
                                quizCard.removeEventListener('animationend', handler);
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

    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/chart.js/chart.umd.js"></script>
    <script src="assets/vendor/echarts/echarts.min.js"></script>
    <script src="assets/vendor/quill/quill.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>

</body>

</html>
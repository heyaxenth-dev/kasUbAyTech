<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login-admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Manage Questions - kasUbAyTech Admin</title>
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
                        <span class="d-none d-md-block dropdown-toggle ps-2"><?php echo $_SESSION['admin_username']; ?></span>
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
                <a class="nav-link active" href="questions.php">
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
                <a class="nav-link" href="compatibility.php">
                    <i class="bi bi-graph-up"></i>
                    <span>Compatibility Scores</span>
                </a>
            </li>
        </ul>
    </aside>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Manage Questions</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="homepage.php">Home</a></li>
                    <li class="breadcrumb-item active">Questions</li>
                </ol>
            </nav>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title">Assessment Questions</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#questionModal" onclick="openAddModal()">
                                    <i class="bi bi-plus-circle"></i> Add Question
                                </button>
                            </div>

                            <div id="questionsTable">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Question</th>
                                            <th>Type</th>
                                            <th>Options</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="questionsList">
                                        <tr>
                                            <td colspan="6" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Question Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="questionForm">
                        <input type="hidden" id="questionId">
                        <div class="mb-3">
                            <label for="questionText" class="form-label">Question Text</label>
                            <textarea class="form-control" id="questionText" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="questionType" class="form-label">Question Type</label>
                                <select class="form-select" id="questionType" required>
                                    <option value="single">Single Choice</option>
                                    <option value="multiple">Multiple Choice</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="orderNumber" class="form-label">Order Number</label>
                                <input type="number" class="form-control" id="orderNumber" value="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Answer Options</label>
                            <div id="optionsContainer">
                                <!-- Options will be added here -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                                <i class="bi bi-plus"></i> Add Option
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveQuestion()">Save Question</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        let questions = [];
        let editingId = null;

        // Load questions on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadQuestions();
        });

        function loadQuestions() {
            fetch('api/questions.php')
                .then(response => response.json())
                .then(data => {
                    questions = data;
                    renderQuestions();
                })
                .catch(error => console.error('Error:', error));
        }

        function renderQuestions() {
            const tbody = document.getElementById('questionsList');
            if (questions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No questions found. Add your first question!</td></tr>';
                return;
            }

            tbody.innerHTML = questions.map(q => `
                <tr>
                    <td>${q.order_number}</td>
                    <td>${q.question_text}</td>
                    <td><span class="badge bg-${q.question_type === 'single' ? 'primary' : 'info'}">${q.question_type}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" onclick="viewOptions(${q.id})">
                            View Options
                        </button>
                    </td>
                    <td>
                        <span class="badge bg-${q.is_active == 1 ? 'success' : 'secondary'}">
                            ${q.is_active == 1 ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editQuestion(${q.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteQuestion(${q.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function openAddModal() {
            editingId = null;
            document.getElementById('modalTitle').textContent = 'Add Question';
            document.getElementById('questionForm').reset();
            document.getElementById('questionId').value = '';
            document.getElementById('optionsContainer').innerHTML = '';
            addOption(); // Add one default option
        }

        function addOption(option = null) {
            const container = document.getElementById('optionsContainer');
            const index = container.children.length;
            const optionHtml = `
                <div class="card mb-2 option-item">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <input type="text" class="form-control" placeholder="Option text" 
                                    value="${option ? option.option_text : ''}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.1" class="form-control" placeholder="IT" 
                                    value="${option ? option.it_score : '0'}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.1" class="form-control" placeholder="CS" 
                                    value="${option ? option.cs_score : '0'}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" step="0.1" class="form-control" placeholder="IS" 
                                    value="${option ? option.is_score : '0'}" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeOption(this)">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', optionHtml);
        }

        function removeOption(btn) {
            btn.closest('.option-item').remove();
        }

        function editQuestion(id) {
            fetch(`api/questions.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    editingId = id;
                    document.getElementById('modalTitle').textContent = 'Edit Question';
                    document.getElementById('questionId').value = data.id;
                    document.getElementById('questionText').value = data.question_text;
                    document.getElementById('questionType').value = data.question_type;
                    document.getElementById('orderNumber').value = data.order_number;
                    
                    document.getElementById('optionsContainer').innerHTML = '';
                    if (data.options && data.options.length > 0) {
                        data.options.forEach(opt => addOption(opt));
                    } else {
                        addOption();
                    }
                    
                    new bootstrap.Modal(document.getElementById('questionModal')).show();
                })
                .catch(error => console.error('Error:', error));
        }

        function saveQuestion() {
            const form = document.getElementById('questionForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const options = [];
            document.querySelectorAll('.option-item').forEach(item => {
                const inputs = item.querySelectorAll('input');
                options.push({
                    option_text: inputs[0].value,
                    it_score: parseFloat(inputs[1].value) || 0,
                    cs_score: parseFloat(inputs[2].value) || 0,
                    is_score: parseFloat(inputs[3].value) || 0
                });
            });

            const data = {
                id: editingId,
                question_text: document.getElementById('questionText').value,
                question_type: document.getElementById('questionType').value,
                order_number: parseInt(document.getElementById('orderNumber').value),
                options: options
            };

            const url = 'api/questions.php';
            const method = editingId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
                    loadQuestions();
                } else {
                    alert('Error: ' + (result.error || 'Failed to save question'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the question');
            });
        }

        function deleteQuestion(id) {
            if (!confirm('Are you sure you want to delete this question? This will also delete all associated answer options.')) {
                return;
            }

            fetch('api/questions.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    loadQuestions();
                } else {
                    alert('Error: ' + (result.error || 'Failed to delete question'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the question');
            });
        }

        function viewOptions(id) {
            fetch(`api/questions.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    let optionsHtml = '<table class="table table-sm"><thead><tr><th>Option</th><th>IT</th><th>CS</th><th>IS</th></tr></thead><tbody>';
                    if (data.options) {
                        data.options.forEach(opt => {
                            optionsHtml += `<tr><td>${opt.option_text}</td><td>${opt.it_score}</td><td>${opt.cs_score}</td><td>${opt.is_score}</td></tr>`;
                        });
                    }
                    optionsHtml += '</tbody></table>';
                    alert('Options:\n\n' + optionsHtml.replace(/<[^>]*>/g, ' '));
                });
        }
    </script>

</body>

</html>


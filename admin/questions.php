<?php 
include './authentication.php';
include './includes/header.php';
include './includes/sidebar.php';
?>

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
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#questionModal" onclick="openAddModal()">
                                <i class="bi bi-plus-circle"></i> Add Question
                            </button>
                        </div>

                        <div id="questionsTable">
                            <table class="table table-striped questions-table">
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
                        <div class="col-md-4">
                            <label for="questionType" class="form-label">Question Type</label>
                            <select class="form-select" id="questionType" required>
                                <option value="single">Single Choice</option>
                                <option value="multiple">Multiple Choice</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" required>
                                <option value="DIAGNOSTIC">Diagnostic</option>
                                <option value="IS">Information System (IS)</option>
                                <option value="IT">Information Technology (IT)</option>
                                <option value="CS">Computer Science (CS)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="orderNumber" class="form-label">Order Number</label>
                            <input type="number" class="form-control" id="orderNumber" value="0" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="difficulty" class="form-label">Difficulty</label>
                            <select class="form-select" id="difficulty" required>
                                <option value="EASY">Easy</option>
                                <option value="MEDIUM">Medium</option>
                                <option value="HARD">Hard</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="weight" class="form-label">Weight</label>
                            <input type="number" class="form-control" id="weight" value="1" min="1" max="5" required>
                        </div>
                        <div class="col-md-4">
                            <label for="correctOption" class="form-label">Correct Option</label>
                            <select class="form-select" id="correctOption">
                                <option value="">Select...</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer Options (A, B, C, D)</label>
                        <div class="row mb-2">
                            <div class="col-md-11">
                                <label class="form-label small">Option A</label>
                                <input type="text" class="form-control" id="optionA" placeholder="Enter option A text">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-11">
                                <label class="form-label small">Option B</label>
                                <input type="text" class="form-control" id="optionB" placeholder="Enter option B text">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-11">
                                <label class="form-label small">Option C</label>
                                <input type="text" class="form-control" id="optionC" placeholder="Enter option C text">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-11">
                                <label class="form-label small">Option D</label>
                                <input type="text" class="form-control" id="optionD" placeholder="Enter option D text">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer Options (Legacy - for scoring)</label>
                        <div id="optionsContainer">
                            <!-- Options will be added here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOption()">
                            <i class="bi bi-plus"></i> Add Option (for scoring)
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

<script>
let questions = [];
let editingId = null;
let questionsDataTable = null;

// Load questions on page load
document.addEventListener('DOMContentLoaded', function() {
    loadQuestions();
});

function initQuestionsDataTable() {
    const tableEl = document.querySelector('#questionsTable table');
    if (!tableEl || typeof simpleDatatables === 'undefined') {
        return;
    }

    // Destroy existing instance if any (e.g., after save/delete)
    if (questionsDataTable) {
        questionsDataTable.destroy();
        questionsDataTable = null;
    }

    questionsDataTable = new simpleDatatables.DataTable(tableEl, {
        perPageSelect: [5, 10, 15, ['All', -1]]
    });
}

function loadQuestions() {
    fetch('api/questions.php')
        .then(response => response.json())
        .then(data => {
            questions = data;
            renderQuestions();
            initQuestionsDataTable();
        })
        .catch(error => console.error('Error:', error));
}

function renderQuestions() {
    const tbody = document.getElementById('questionsList');
    if (questions.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="6" class="text-center">No questions found. Add your first question!</td></tr>';
        return;
    }

    tbody.innerHTML = questions.map(q => `
                <tr>
                    <td>${q.order_number || 0}</td>
                    <td>${q.question_text}</td>
                    <td>
                        <span class="badge bg-${q.question_type === 'single' ? 'primary' : 'info'}">${q.question_type}</span><br>
                        <span class="badge bg-secondary mt-1">${q.category || 'DIAGNOSTIC'}</span><br>
                        <span class="badge bg-${q.difficulty === 'HARD' ? 'danger' : q.difficulty === 'MEDIUM' ? 'warning' : 'success'} mt-1">${q.difficulty || 'MEDIUM'}</span>
                    </td>
                    <td>
                        ${q.option_a ? 'A: ' + q.option_a.substring(0, 30) + '...<br>' : ''}
                        ${q.option_b ? 'B: ' + q.option_b.substring(0, 30) + '...<br>' : ''}
                        ${q.option_c ? 'C: ' + q.option_c.substring(0, 30) + '...<br>' : ''}
                        ${q.option_d ? 'D: ' + q.option_d.substring(0, 30) + '...' : ''}
                        ${!q.option_a && !q.option_b && !q.option_c && !q.option_d ? '<button class="btn btn-sm btn-outline-info" onclick="viewOptions(' + q.id + ')">View Options</button>' : ''}
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
    document.getElementById('category').value = 'DIAGNOSTIC';
    document.getElementById('difficulty').value = 'MEDIUM';
    document.getElementById('weight').value = 1;
    document.getElementById('correctOption').value = '';
    document.getElementById('optionA').value = '';
    document.getElementById('optionB').value = '';
    document.getElementById('optionC').value = '';
    document.getElementById('optionD').value = '';
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
            document.getElementById('questionText').value = data.question_text || '';
            document.getElementById('questionType').value = data.question_type || 'single';
            document.getElementById('category').value = data.category || 'DIAGNOSTIC';
            document.getElementById('difficulty').value = data.difficulty || 'MEDIUM';
            document.getElementById('weight').value = data.weight || 1;
            document.getElementById('correctOption').value = data.correct_option || '';
            document.getElementById('optionA').value = data.option_a || '';
            document.getElementById('optionB').value = data.option_b || '';
            document.getElementById('optionC').value = data.option_c || '';
            document.getElementById('optionD').value = data.option_d || '';
            document.getElementById('orderNumber').value = data.order_number || 0;

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
        category: document.getElementById('category').value,
        difficulty: document.getElementById('difficulty').value,
        weight: parseInt(document.getElementById('weight').value),
        correct_option: document.getElementById('correctOption').value || null,
        option_a: document.getElementById('optionA').value || null,
        option_b: document.getElementById('optionB').value || null,
        option_c: document.getElementById('optionC').value || null,
        option_d: document.getElementById('optionD').value || null,
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
    if (!confirm(
            'Are you sure you want to delete this question? This will also delete all associated answer options.')) {
        return;
    }

    fetch('api/questions.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id
            })
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
            let optionsHtml =
                '<table class="table table-sm"><thead><tr><th>Option</th><th>IT</th><th>CS</th><th>IS</th></tr></thead><tbody>';
            if (data.options) {
                data.options.forEach(opt => {
                    optionsHtml +=
                        `<tr><td>${opt.option_text}</td><td>${opt.it_score}</td><td>${opt.cs_score}</td><td>${opt.is_score}</td></tr>`;
                });
            }
            optionsHtml += '</tbody></table>';
            alert('Options:\n\n' + optionsHtml.replace(/<[^>]*>/g, ' '));
        });
}
</script>

<?php 
include './includes/footer.php';
?>
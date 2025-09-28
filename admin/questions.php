<?php 
include('../database/config.php');
include('includes/header.php');
include('includes/sidebar.php');

// Create questions table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS assessment_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    difficulty ENUM('Easy', 'Medium', 'Hard') NOT NULL,
    course_type ENUM('IT', 'IS', 'CS', 'General') DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $createTable)) {
    echo "Error creating table: " . mysqli_error($conn);
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
                $option_a = mysqli_real_escape_string($conn, $_POST['option_a']);
                $option_b = mysqli_real_escape_string($conn, $_POST['option_b']);
                $option_c = mysqli_real_escape_string($conn, $_POST['option_c']);
                $option_d = mysqli_real_escape_string($conn, $_POST['option_d']);
                $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);
                $difficulty = mysqli_real_escape_string($conn, $_POST['difficulty']);
                $course_type = mysqli_real_escape_string($conn, $_POST['course_type']);
                
                $insertQuery = "INSERT INTO assessment_questions (question_text, option_a, option_b, option_c, option_d, correct_answer, difficulty, course_type) 
                               VALUES ('$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer', '$difficulty', '$course_type')";
                
                if (mysqli_query($conn, $insertQuery)) {
                    $message = "Question added successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "danger";
                }
                break;
                
            case 'update':
                $id = mysqli_real_escape_string($conn, $_POST['id']);
                $question_text = mysqli_real_escape_string($conn, $_POST['question_text']);
                $option_a = mysqli_real_escape_string($conn, $_POST['option_a']);
                $option_b = mysqli_real_escape_string($conn, $_POST['option_b']);
                $option_c = mysqli_real_escape_string($conn, $_POST['option_c']);
                $option_d = mysqli_real_escape_string($conn, $_POST['option_d']);
                $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);
                $difficulty = mysqli_real_escape_string($conn, $_POST['difficulty']);
                $course_type = mysqli_real_escape_string($conn, $_POST['course_type']);
                
                $updateQuery = "UPDATE assessment_questions 
                               SET question_text='$question_text', option_a='$option_a', option_b='$option_b', option_c='$option_c', option_d='$option_d', correct_answer='$correct_answer', difficulty='$difficulty', course_type='$course_type' 
                               WHERE id='$id'";
                
                if (mysqli_query($conn, $updateQuery)) {
                    $message = "Question updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "danger";
                }
                break;
                
            case 'delete':
                $id = mysqli_real_escape_string($conn, $_POST['id']);
                $deleteQuery = "DELETE FROM assessment_questions WHERE id='$id'";
                
                if (mysqli_query($conn, $deleteQuery)) {
                    $message = "Question deleted successfully!";
                    $messageType = "success";
                } else {
                    $message = "Error: " . mysqli_error($conn);
                    $messageType = "danger";
                }
                break;
        }
    }
}

// Fetch all questions
$questionsQuery = "SELECT * FROM assessment_questions ORDER BY created_at DESC";
$questionsResult = mysqli_query($conn, $questionsQuery);
?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Assessment</strong> Questions</h1>


        <div class="row">
            <!-- Create/Edit Question Form -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <span id="form-title">Create New Question</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="question-form" method="POST">
                            <input type="hidden" name="action" id="form-action" value="create">
                            <input type="hidden" name="id" id="question-id" value="">

                            <div class="mb-3">
                                <label for="question_text" class="form-label">Question Text</label>
                                <textarea class="form-control" id="question_text" name="question_text" rows="4"
                                    placeholder="Enter the question text here..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Answer Options</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-primary text-white fw-bold">A</span>
                                            <input type="text" class="form-control" id="option_a" name="option_a"
                                                placeholder="Option A" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-primary text-white fw-bold">B</span>
                                            <input type="text" class="form-control" id="option_b" name="option_b"
                                                placeholder="Option B" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-primary text-white fw-bold">C</span>
                                            <input type="text" class="form-control" id="option_c" name="option_c"
                                                placeholder="Option C" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-primary text-white fw-bold">D</span>
                                            <input type="text" class="form-control" id="option_d" name="option_d"
                                                placeholder="Option D" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="correct_answer" class="form-label">Correct Answer</label>
                                <select class="form-select" id="correct_answer" name="correct_answer" required>
                                    <option value="">Select Correct Answer</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="difficulty" class="form-label">Difficulty Level</label>
                                <select class="form-select" id="difficulty" name="difficulty" required>
                                    <option value="">Select Difficulty</option>
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="course_type" class="form-label">Course Type</label>
                                <select class="form-select" id="course_type" name="course_type" required>
                                    <option value="">Select Course Type</option>
                                    <option value="IT">Information Technology (IT)</option>
                                    <option value="IS">Information Systems (IS)</option>
                                    <option value="CS">Computer Science (CS)</option>
                                    <option value="General">General</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary" id="cancel-edit"
                                    style="display: none;">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="submit-btn">Add Question</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Questions List -->
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Questions Management</h5>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($questionsResult) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Question & Options</th>
                                        <th>Answer</th>
                                        <th>Difficulty</th>
                                        <th>Course</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($question = mysqli_fetch_assoc($questionsResult)): ?>
                                    <tr>
                                        <td>
                                            <div class="question-preview">
                                                <strong><?php echo substr($question['question_text'], 0, 40) . (strlen($question['question_text']) > 40 ? '...' : ''); ?></strong>
                                                <div class="mt-1">
                                                    <small class="text-muted">
                                                        <span class="badge bg-light text-dark me-1">A:
                                                            <?php echo substr($question['option_a'], 0, 20) . (strlen($question['option_a']) > 20 ? '...' : ''); ?></span>
                                                        <span class="badge bg-light text-dark me-1">B:
                                                            <?php echo substr($question['option_b'], 0, 20) . (strlen($question['option_b']) > 20 ? '...' : ''); ?></span>
                                                        <span class="badge bg-light text-dark me-1">C:
                                                            <?php echo substr($question['option_c'], 0, 20) . (strlen($question['option_c']) > 20 ? '...' : ''); ?></span>
                                                        <span class="badge bg-light text-dark">D:
                                                            <?php echo substr($question['option_d'], 0, 20) . (strlen($question['option_d']) > 20 ? '...' : ''); ?></span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-success fs-6"><?php echo $question['correct_answer']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $question['difficulty'] == 'Easy' ? 'success' : 
                                                    ($question['difficulty'] == 'Medium' ? 'warning' : 'danger'); 
                                            ?>">
                                                <?php echo $question['difficulty']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $question['course_type']; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary"
                                                    onclick="editQuestion(<?php echo htmlspecialchars(json_encode($question)); ?>)">
                                                    <i class="align-middle" data-feather="edit-3">
                                                </button>
                                                <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteQuestion(<?php echo $question['id']; ?>)">
                                                    <i class="align-middle" data-feather="trash-2">
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No questions found. Create your first question using the form on the
                                left.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<script>
function editQuestion(question) {
    document.getElementById('form-title').textContent = 'Edit Question';
    document.getElementById('form-action').value = 'update';
    document.getElementById('question-id').value = question.id;
    document.getElementById('question_text').value = question.question_text;
    document.getElementById('option_a').value = question.option_a;
    document.getElementById('option_b').value = question.option_b;
    document.getElementById('option_c').value = question.option_c;
    document.getElementById('option_d').value = question.option_d;
    document.getElementById('correct_answer').value = question.correct_answer;
    document.getElementById('difficulty').value = question.difficulty;
    document.getElementById('course_type').value = question.course_type;
    document.getElementById('submit-btn').textContent = 'Update Question';
    document.getElementById('cancel-edit').style.display = 'inline-block';

    // Scroll to form
    document.getElementById('question-form').scrollIntoView({
        behavior: 'smooth'
    });
}

function deleteQuestion(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;

            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.getElementById('cancel-edit').addEventListener('click', function() {
    resetForm();
});

function resetForm() {
    document.getElementById('form-title').textContent = 'Create New Question';
    document.getElementById('form-action').value = 'create';
    document.getElementById('question-id').value = '';
    document.getElementById('question-form').reset();
    document.getElementById('submit-btn').textContent = 'Add Question';
    document.getElementById('cancel-edit').style.display = 'none';
}

// Show SweetAlert messages based on PHP response
<?php if ($message): ?>
<?php if ($messageType == 'success'): ?>
Swal.fire({
    title: 'Success!',
    text: '<?php echo addslashes($message); ?>',
    icon: 'success',
    confirmButtonText: 'OK',
    timer: 3000,
    timerProgressBar: true
}).then(() => {
    resetForm();
});
<?php else: ?>
Swal.fire({
    title: 'Error!',
    text: '<?php echo addslashes($message); ?>',
    icon: 'error',
    confirmButtonText: 'OK'
});
<?php endif; ?>
<?php endif; ?>
</script>

<?php 
include('includes/footer.php');
?>
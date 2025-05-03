<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'];

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch project details and verify it belongs to the admin's students
$stmt = $pdo->prepare("
    SELECT p.*, s.first_name, s.last_name, s.department
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE p.id = ? AND s.admin_id = ?
");
$stmt->execute([$project_id, $admin_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: list.php?error=1');
    exit();
}

// Fetch students for the logged-in admin
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, department
    FROM students
    WHERE admin_id = ?
    ORDER BY first_name, last_name
");

if ($stmt && $stmt->execute([$admin_id])) {
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Log error info (check if $stmt is false first)
    $errorInfo = $stmt ? $stmt->errorInfo() : $pdo->errorInfo();
    error_log("SQL Error: " . print_r($errorInfo, true));
    
    $students = []; 
    $error_message = "Failed to fetch students. Please try again later.";
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $student_id = (int)$_POST['student_id'];

    // Verify that the student belongs to the admin
    $stmt = $pdo->prepare("
        SELECT id FROM students 
        WHERE id = ? AND admin_id = ?
    ");
    $stmt->execute([$student_id, $admin_id]);
    
    if (!$stmt->fetch()) {
        $error_message = "Invalid student selected.";
    } else {
        // Update project in database
        $stmt = $pdo->prepare("
            UPDATE projects 
            SET title = ?, description = ?, category = ?, student_id = ?
            WHERE id = ? AND student_id IN (
                SELECT id FROM students WHERE admin_id = ?
            )
        ");
        
        if ($stmt->execute([$title, $description, $category, $student_id, $project_id, $admin_id])) {
            header('Location: list.php?success=1');
            exit();
        } else {
            $error_message = "Error updating project. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading-overlay">
        <div class="spinner-grow text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">TopTrack Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../students/list.php">
                            <i class="bi bi-people"></i> My Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="list.php">
                            <i class="bi bi-folder"></i> My Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../nominations/list.php">
                            <i class="bi bi-trophy"></i> My Nominations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../analytics.php">
                            <i class="bi bi-graph-up"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="modern-card">
                    <div class="card-body">
                        <h2 class="section-title mb-4">Edit Project</h2>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student</label>
                                <select class="form-select" id="student_id" name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" <?php echo $student['id'] == $project['student_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['department'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a student.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="title" class="form-label">Project Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                                <div class="invalid-feedback">
                                    Please enter the project title.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                                <div class="invalid-feedback">
                                    Please enter the project description.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="Research" <?php echo $project['category'] == 'Research' ? 'selected' : ''; ?>>Research</option>
                                    <option value="Development" <?php echo $project['category'] == 'Development' ? 'selected' : ''; ?>>Development</option>
                                    <option value="Innovation" <?php echo $project['category'] == 'Innovation' ? 'selected' : ''; ?>>Innovation</option>
                                    <option value="Design" <?php echo $project['category'] == 'Design' ? 'selected' : ''; ?>>Design</option>
                                    <option value="Analysis" <?php echo $project['category'] == 'Analysis' ? 'selected' : ''; ?>>Analysis</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a category.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update Project
                                </button>
                                <a href="list.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="bi bi-stars me-2"></i>
                TopTrack Admin Dashboard - Managing Student Excellence
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        window.addEventListener('load', function() {
            document.querySelector('.loading-overlay').classList.add('fade-out');
        });

        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
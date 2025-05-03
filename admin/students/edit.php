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

// Get student ID from URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch student details
$stmt = $pdo->prepare("
    SELECT * FROM students 
    WHERE id = ? AND admin_id = ?
");
$stmt->execute([$student_id, $admin_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if student exists and belongs to the admin
if (!$student) {
    header('Location: list.php?error=1');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $class = $_POST['class'];

    // Update student in database
    $stmt = $pdo->prepare("
        UPDATE students 
        SET first_name = ?, last_name = ?, email = ?, department = ?, class = ?
        WHERE id = ? AND admin_id = ?
    ");
    
    if ($stmt->execute([$first_name, $last_name, $email, $department, $class, $student_id, $admin_id])) {
        header('Location: list.php?success=1');
        exit();
    } else {
        $error_message = "Error updating student. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="../../style.css">
    <style>
        .modern-card{
            padding:20px;
        }
    </style>
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
                        <a class="nav-link" href="list.php">
                            <i class="bi bi-people"></i> My Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../projects/list.php">
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
                        <h2 class="section-title mb-4">Edit Student</h2>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                                <div class="invalid-feedback">
                                    Please enter the student's first name.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                                <div class="invalid-feedback">
                                    Please enter the student's last name.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Science" <?php echo $student['department'] === 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                                    <option value="Electrical Engineering" <?php echo $student['department'] === 'Electrical Engineering' ? 'selected' : ''; ?>>Electrical Engineering</option>
                                    <option value="Mechanical Engineering" <?php echo $student['department'] === 'Mechanical Engineering' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                    <option value="Civil Engineering" <?php echo $student['department'] === 'Civil Engineering' ? 'selected' : ''; ?>>Civil Engineering</option>
                                    <option value="Chemical Engineering" <?php echo $student['department'] === 'Chemical Engineering' ? 'selected' : ''; ?>>Chemical Engineering</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a department.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="class" class="form-label">Class</label>
                                <input type="text" class="form-control" id="class" name="class" 
                                       value="<?php echo htmlspecialchars($student['class']); ?>" required>
                                <div class="invalid-feedback">
                                    Please enter the student's class.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Save Changes
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
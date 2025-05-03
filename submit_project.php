<?php
session_start();
require_once 'config/database.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $github_link = trim($_POST['github_link']);
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($category)) {
        $error_message = "Title, description, and category are required";
    } else {
        try {
            // Handle project file upload
            $project_file = null;

if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/png',
        'image/jpeg',
        'image/jpg'
    ];

    $file_type = $_FILES['project_file']['type'];

    if (in_array($file_type, $allowed_types)) {
        $upload_dir = 'uploads/projects/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['project_file']['name'], PATHINFO_EXTENSION);
        $file_name = 'project_' . time() . '_' . $_SESSION['student_id'] . '.' . $file_extension;
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['project_file']['tmp_name'], $target_path)) {
            $project_file = $target_path;
        } else {
            $error_message = "Failed to upload project file.";
        }
    } else {
        $error_message = "Invalid file type. Please upload a PDF, Word document, or image file (PNG, JPG, JPEG).";
    }
}

            
            if (empty($error_message)) {
                // Insert project into database
                $stmt = $pdo->prepare("
                    INSERT INTO projects (student_id, title, description, category, github_link, file_path, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$_SESSION['student_id'], $title, $description, $category, $github_link, $project_file]);
                
                $success_message = "Project submitted successfully!";
                
                // Clear form data after successful submission
                $_POST = array();
            }
        } catch (PDOException $e) {
            $error_message = "An error occurred while submitting your project.";
        }
    }
}

// Fetch available project categories
$categories = ['Web Development', 'Mobile App', 'Machine Learning', 'Data Science', 'IoT', 'Cybersecurity', 'Game Development', 'Other'];
?>

<?php
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, department
    FROM students
    WHERE admin_id = ?
    ORDER BY first_name, last_name
");

if ($stmt->execute([$admin_id])) {
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Log the SQL error for debugging
    error_log("SQL Error: " . print_r($stmt->errorInfo(), true));
    $students = []; // Default to an empty array if the query fails
    $error_message = "Failed to fetch students. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Project - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="style.css">
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
            <a class="navbar-brand" href="student_dashboard.php">InnoLearn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="public_gallery.php">
                            <i class="bi bi-grid me-1"></i> Gallery
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="modern-card" data-aos="fade-up">
                    <div class="card-body p-4">
                        <h2 class="section-title mb-4">Submit New Project</h2>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                           required>
                                    <label for="title">Project Title</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating">
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              style="height: 150px" 
                                              required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    <label for="description">Project Description</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating">
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="" disabled <?php echo !isset($_POST['category']) ? 'selected' : ''; ?>>Select a category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                                    <?php echo (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="category">Project Category</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-floating">
                                    <input type="url" 
                                           class="form-control" 
                                           id="github_link" 
                                           name="github_link" 
                                           value="<?php echo isset($_POST['github_link']) ? htmlspecialchars($_POST['github_link']) : ''; ?>" 
                                           placeholder="https://github.com/username/repository">
                                    <label for="github_link">GitHub Repository Link (Optional)</label>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="project_file" class="form-label">Project Documentation (PDF or Word)</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="project_file" 
                                       name="project_file" 
                                       accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                                <div class="form-text">Upload your project documentation (max 10MB)</div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cloud-upload me-2"></i>Submit Project
                                </button>
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
                InnoLearn - Student Excellence Management System
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Loading animation
        window.addEventListener('load', function() {
            document.querySelector('.loading-overlay').classList.add('fade-out');
        });

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
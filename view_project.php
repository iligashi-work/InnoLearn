<?php
session_start();
require_once 'config/database.php';

// Check if project ID is provided
if (!isset($_GET['id'])) {
    header('Location: public_gallery.php');
    exit();
}

$project_id = $_GET['id'];
$is_owner = false;
$success_message = '';
$error_message = '';

// Fetch project details with student information
$stmt = $pdo->prepare("
    SELECT p.*, s.first_name, s.last_name, s.department 
    FROM projects p 
    JOIN students s ON p.student_id = s.id 
    WHERE p.id = ?
");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    header('Location: public_gallery.php');
    exit();
}

// Check if the logged-in student is the owner
if (isset($_SESSION['student_id']) && $_SESSION['student_id'] == $project['student_id']) {
    $is_owner = true;
}

// Handle form submission for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_owner) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $github_link = trim($_POST['github_link']);
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($category)) {
        $error_message = "Title, description, and category are required";
    } else {
        try {
            // Handle project file upload if new file is provided
            $project_file = $project['file_path']; // Keep existing file by default
            
            if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
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
                        // Delete old file if it exists
                        if ($project['file_path'] && file_exists($project['file_path'])) {
                            unlink($project['file_path']);
                        }
                        $project_file = $target_path;
                    } else {
                        $error_message = "Failed to upload project file";
                    }
                } else {
                    $error_message = "Invalid file type. Please upload a PDF or Word document.";
                }
            }
            
            if (empty($error_message)) {
                // Update project information
                $stmt = $pdo->prepare("
                    UPDATE projects 
                    SET title = ?, description = ?, category = ?, github_link = ?, file_path = ?
                    WHERE id = ? AND student_id = ?
                ");
                $stmt->execute([$title, $description, $category, $github_link, $project_file, $project_id, $_SESSION['student_id']]);
                
                $success_message = "Project updated successfully!";
                
                // Refresh project data
                $stmt = $pdo->prepare("
                    SELECT p.*, s.first_name, s.last_name, s.department 
                    FROM projects p 
                    JOIN students s ON p.student_id = s.id 
                    WHERE p.id = ?
                ");
                $stmt->execute([$project_id]);
                $project = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error_message = "An error occurred while updating your project.";
        }
    }
}

// Fetch available project categories
$categories = ['Web Development', 'Mobile App', 'Machine Learning', 'Data Science', 'IoT', 'Cybersecurity', 'Game Development', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }
        .project-header {
            background: white;
            padding: 2rem 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .project-thumbnail {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .project-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 1rem;
        }
        .project-meta {
            margin-bottom: 2rem;
        }
        .project-meta span {
            margin-right: 1rem;
        }
        .project-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
        }
        .student-info {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        .project-actions {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
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

    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container">
            <a class="navbar-brand" href="index.php">InnoLearn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['student_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="student_dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="public_gallery.php">
                            <i class="bi bi-grid me-1"></i> Back to Gallery
                        </a>
                    </li>
                    <?php if (isset($_SESSION['student_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="student_logout.php">
                                <i class="bi bi-box-arrow-right me-1"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="student_login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="project-header">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="public_gallery.php">Gallery</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($project['title']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-lg-8" data-aos="fade-up">
                <img src="<?php echo htmlspecialchars($project['thumbnail_path']); ?>" 
                     alt="Project Thumbnail" 
                     class="project-thumbnail mb-4">
                
                <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>
                
                <div class="project-meta">
                    <span class="badge bg-primary">
                        <i class="bi bi-bookmark-star me-1"></i>
                        <?php echo htmlspecialchars($project['category']); ?>
                    </span>
                    <span class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        <?php echo date('F j, Y', strtotime($project['submission_date'])); ?>
                    </span>
                </div>
                
                <div class="project-description">
                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                </div>

                <?php if ($project['file_path']): ?>
                <div class="project-actions">
                    <h5 class="mb-3">Project Documentation</h5>
                    <a href="<?php echo htmlspecialchars($project['file_path']); ?>" 
                       class="btn btn-primary" 
                       target="_blank">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        View Documentation
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="student-info">
                    <h5 class="mb-4">Project Creator</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></h6>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($project['department']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (isset($project['github_link']) && $project['github_link']): ?>
                    <div class="mt-4">
                        <h6 class="mb-3">Project Links</h6>
                        <a href="<?php echo htmlspecialchars($project['github_link']); ?>" 
                           class="btn btn-outline-dark w-100" 
                           target="_blank">
                            <i class="bi bi-github me-2"></i>
                            View on GitHub
                        </a>
                    </div>
                    <?php endif; ?>
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
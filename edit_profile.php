<?php
session_start();
require_once 'config/database.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: student_login.php');
    exit();
}

// Fetch student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($department)) {
        $error_message = "All fields are required";
    } else {
        try {
            // Handle profile image upload
            $profile_image = $student['profile_image']; // Keep existing image by default
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['profile_image']['type'];
                
                if (in_array($file_type, $allowed_types)) {
                    $upload_dir = 'uploads/students/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $file_name = 'profile_' . time() . '_' . $student['student_id'] . '.' . $file_extension;
                    $target_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                        $profile_image = $target_path;
                    } else {
                        $error_message = "Failed to upload profile image";
                    }
                } else {
                    $error_message = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
                }
            }
            
            if (empty($error_message)) {
                // Update student information
                $stmt = $pdo->prepare("
                    UPDATE students 
                    SET first_name = ?, last_name = ?, email = ?, department = ?, profile_image = ?
                    WHERE id = ?
                ");
                $stmt->execute([$first_name, $last_name, $email, $department, $profile_image, $_SESSION['student_id']]);
                
                // Update session data
                $_SESSION['student_name'] = $first_name . ' ' . $last_name;
                $_SESSION['student_email'] = $email;
                $_SESSION['student_department'] = $department;
                
                $success_message = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            $error_message = "An error occurred while updating your profile.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="style.css">
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
            <a class="navbar-brand" href="student_dashboard.php">TopTrack</a>
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
                        <h2 class="section-title mb-4">Edit Profile</h2>

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
                            <div class="text-center mb-4">
                                <?php if ($student['profile_image']): ?>
                                    <img src="<?php echo htmlspecialchars($student['profile_image']); ?>" 
                                         alt="Current Profile" 
                                         class="rounded-circle mb-3"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                         style="width: 150px; height: 150px; font-size: 4rem;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="profile_image" class="form-label">Change Profile Picture</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="profile_image" 
                                           name="profile_image" 
                                           accept="image/*">
                                    <div class="form-text">Supported formats: JPEG, PNG, GIF</div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" 
                                               class="form-control" 
                                               id="first_name" 
                                               name="first_name" 
                                               value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                                               required>
                                        <label for="first_name">First Name</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" 
                                               class="form-control" 
                                               id="last_name" 
                                               name="last_name" 
                                               value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                                               required>
                                        <label for="last_name">Last Name</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email" 
                                               value="<?php echo htmlspecialchars($student['email']); ?>" 
                                               required>
                                        <label for="email">Email Address</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" 
                                               class="form-control" 
                                               id="department" 
                                               name="department" 
                                               value="<?php echo htmlspecialchars($student['department']); ?>" 
                                               required>
                                        <label for="department">Department</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>Save Changes
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
                TopTrack - Student Excellence Management System
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
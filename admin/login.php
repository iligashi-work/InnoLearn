<?php
session_start();
require_once '../config/database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    try {
        echo "Attempting admin login with:<br>";
        echo "Username: " . htmlspecialchars($username) . "<br>";
        
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        
        echo "Query executed.<br>";
        
        $admin = $stmt->fetch();
        
        echo "Query result:<br>";
        if ($admin) {
            $debug_admin = $admin;
            unset($debug_admin['password']); 
            var_dump($debug_admin);
        } else {
            echo "No admin found with this username.<br>";
        }
        
        if ($admin && $password === $admin['password']) { 
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = "Invalid username or password";
            
            echo "<br>Checking database contents:<br>";
            $check_stmt = $pdo->query("SELECT id, username FROM admins");
            $all_admins = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "All admins in database (excluding passwords):<br>";
            var_dump($all_admins);
            
            
            if ($admin) {
                echo "<br>Password verification failed. This could mean the password is incorrect or stored in wrong format.<br>";
                echo "Password in database is " . (password_get_info($admin['password'])['algo'] ? "hashed" : "NOT hashed") . "<br>";
            }
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="../style.css">
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
            <a class="navbar-brand" href="../index.php">TopTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../public_gallery.php">
                            <i class="bi bi-grid me-1"></i> Gallery
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="modern-card login-card" data-aos="fade-up">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock display-1 text-primary"></i>
                        <h2 class="section-title">Admin Login</h2>
                    </div>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Username"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control border-start-0" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Password"
                                       required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Login
                            </button>
                        </div>
                    </form>
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
   
        AOS.init({
            duration: 800,
            once: true
        });

    
        window.addEventListener('load', function() {
            document.querySelector('.loading-overlay').classList.add('fade-out');
        });

       
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
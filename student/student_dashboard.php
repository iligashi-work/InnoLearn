<?php
session_start();
require_once '../config/database.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Get student ID
$student_id = $_SESSION['student_id'];

// Fetch student details
$stmt = $pdo->prepare("
    SELECT s.*, d.name as department_name 
    FROM students s 
    LEFT JOIN departments d ON s.department = d.id 
    WHERE s.id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch project statistics
$stats = [];

// Get total projects
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE student_id = ?");
$stmt->execute([$student_id]);
$stats['total_projects'] = $stmt->fetchColumn();

// Get graded projects
$stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE student_id = ? AND grade IS NOT NULL");
$stmt->execute([$student_id]);
$stats['graded_projects'] = $stmt->fetchColumn();

// Get average grade
$stmt = $pdo->prepare("SELECT AVG(grade) FROM projects WHERE student_id = ? AND grade IS NOT NULL");
$stmt->execute([$student_id]);
$stats['average_grade'] = $stmt->fetchColumn();

// Get recent projects
$stmt = $pdo->prepare("
    SELECT * FROM projects 
    WHERE student_id = ? 
    ORDER BY submission_date DESC 
    LIMIT 5
");
$stmt->execute([$student_id]);
$recent_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="../style.css">
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
            <a class="navbar-brand" href="student_dashboard.php">TopTrack Student</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="projects/list.php">
                            <i class="bi bi-folder"></i> My Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="grades.php">
                            <i class="bi bi-journal-check"></i> My Grades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nominations.php">
                            <i class="bi bi-trophy"></i> My Nominations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="section-title mb-0">Welcome, <?php echo htmlspecialchars($student['first_name']); ?>!</h2>
            <div class="btn-group">
                <a href="../public_gallery.php" class="btn btn-outline-primary">
                    <i class="bi bi-eye"></i> View Public Gallery
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-folder"></i>
                    <div class="stats-value"><?php echo $stats['total_projects']; ?></div>
                    <div class="stats-label">Total Projects</div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-check-circle"></i>
                    <div class="stats-value"><?php echo $stats['graded_projects']; ?></div>
                    <div class="stats-label">Graded Projects</div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-graph-up"></i>
                    <div class="stats-value"><?php echo number_format($stats['average_grade'], 1); ?></div>
                    <div class="stats-label">Average Grade</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-5">
            <div class="col-md-6" data-aos="fade-right">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Quick Actions</h5>
                        <div class="d-grid gap-3">
                            <a href="submit_project.php" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-plus me-2"></i>Submit New Project
                            </a>
                            <a href="grades.php" class="btn btn-outline-success">
                                <i class="bi bi-check-circle me-2"></i>View Auto-Grading Process
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Recent Projects</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_projects as $project): ?>
                                <div class="list-group-item bg-transparent">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <i class="bi bi-folder text-primary me-2"></i>
                                            <?php echo htmlspecialchars($project['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($project['submission_date'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        Category: <?php echo htmlspecialchars($project['category']); ?>
                                        <?php if ($project['grade']): ?>
                                            <span class="badge bg-success ms-2">
                                                Grade: <?php echo $project['grade']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="bi bi-stars me-2"></i>
                TopTrack Student Dashboard - Track Your Progress
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
    </script>
</body>
</html> 
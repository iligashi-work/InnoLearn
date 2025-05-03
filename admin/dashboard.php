<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'];

// Fetch statistics for the logged-in admin
$stats = [];

// Get student count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$stats['students'] = $stmt->fetchColumn();

// Get project count
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM projects p 
    JOIN students s ON p.student_id = s.id 
    WHERE s.admin_id = ?
");
$stmt->execute([$admin_id]);
$stats['projects'] = $stmt->fetchColumn();

// Get nomination count
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM nominations n 
    JOIN students s ON n.student_id = s.id 
    WHERE s.admin_id = ?
");
$stmt->execute([$admin_id]);
$stats['nominations'] = $stmt->fetchColumn();

// Get department count
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT department) FROM students 
    WHERE admin_id = ?
");
$stmt->execute([$admin_id]);
$stats['departments'] = $stmt->fetchColumn();

// Fetch recent activities for the logged-in admin
$stmt = $pdo->prepare("
    (SELECT 'project' as type, p.title as title, s.first_name, s.last_name, p.submission_date as date
     FROM projects p
     JOIN students s ON p.student_id = s.id
     WHERE s.admin_id = ?
     ORDER BY p.submission_date DESC
     LIMIT 5)
    UNION ALL
    (SELECT 'nomination' as type, n.category as title, s.first_name, s.last_name, n.nomination_date as date
     FROM nominations n
     JOIN students s ON n.student_id = s.id
     WHERE s.admin_id = ?
     ORDER BY n.nomination_date DESC
     LIMIT 5)
    ORDER BY date DESC
    LIMIT 10
");
$stmt->execute([$admin_id, $admin_id]);
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TopTrack</title>
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
            <a class="navbar-brand" href="dashboard.php">TopTrack Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="students/list.php">
                            <i class="bi bi-people"></i> My Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="projects/list.php">
                            <i class="bi bi-folder"></i> My Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nominations/list.php">
                            <i class="bi bi-trophy"></i> My Nominations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="bi bi-graph-up"></i> Analytics
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
            <h2 class="section-title mb-0">My Dashboard</h2>
            <div class="btn-group">
                <a href="../public_gallery.php" class="btn btn-outline-primary">
                    <i class="bi bi-eye"></i> View Public Gallery
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-people"></i>
                    <div class="stats-value"><?php echo $stats['students']; ?></div>
                    <div class="stats-label">My Students</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-folder"></i>
                    <div class="stats-value"><?php echo $stats['projects']; ?></div>
                    <div class="stats-label">My Projects</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-trophy"></i>
                    <div class="stats-value"><?php echo $stats['nominations']; ?></div>
                    <div class="stats-label">My Nominations</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-building"></i>
                    <div class="stats-value"><?php echo $stats['departments']; ?></div>
                    <div class="stats-label">Departments</div>
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
                            <a href="students/add.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>Add New Student
                            </a>
                            <a href="projects/add.php" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-plus me-2"></i>Add New Project
                            </a>
                            <a href="nominations/create.php" class="btn btn-outline-primary">
                                <i class="bi bi-star me-2"></i>Create Nomination
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Recent Activities</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="list-group-item bg-transparent">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">
                                            <?php if ($activity['type'] === 'project'): ?>
                                                <i class="bi bi-folder text-primary me-2"></i>
                                            <?php else: ?>
                                                <i class="bi bi-trophy text-success me-2"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($activity['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($activity['date'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1">
                                        by <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
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
    </script>
</body>
</html> 
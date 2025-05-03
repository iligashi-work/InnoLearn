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

// Fetch projects for the logged-in admin's students
$stmt = $pdo->prepare("
    SELECT p.*, s.first_name, s.last_name, s.department,
           (SELECT COUNT(*) FROM nominations n WHERE n.student_id = s.id) as nomination_count
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ?
    ORDER BY p.submission_date DESC
");
$stmt->execute([$admin_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - TopTrack</title>
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
                        <a class="nav-link" href="../students/list.php">
                            <i class="bi bi-people"></i> My Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="list.php">
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
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="section-title mb-0">My Projects</h2>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-file-earmark-plus me-2"></i>Add New Project
            </a>
        </div>

        <?php if (empty($projects)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No projects found. Add your first project to get started.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($projects as $project): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up">
                        <div class="modern-card project-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($project['description']); ?>
                                </p>
                                <div class="project-meta">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-person me-1"></i>
                                        <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                                    </span>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-building me-1"></i>
                                        <?php echo htmlspecialchars($project['department']); ?>
                                    </span>
                                    <span class="badge bg-success">
                                        <i class="bi bi-trophy me-1"></i>
                                        <?php echo $project['nomination_count']; ?> Nominations
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <a href="view.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i>View Details
                                    </a>
                                    <a href="edit.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </a>
                                    <a href="delete.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this project?')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
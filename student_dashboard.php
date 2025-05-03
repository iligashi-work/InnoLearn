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

// Fetch student's projects
$stmt = $pdo->prepare("
    SELECT * FROM projects 
    WHERE student_id = ? 
    ORDER BY submission_date DESC
");
$stmt->execute([$_SESSION['student_id']]);
$projects = $stmt->fetchAll();

// Fetch student's nominations
$stmt = $pdo->prepare("
    SELECT n.*, a.username as nominator
    FROM nominations n
    JOIN admins a ON n.nominated_by = a.id
    WHERE n.student_id = ?
    ORDER BY n.nomination_date DESC
");
$stmt->execute([$_SESSION['student_id']]);
$nominations = $stmt->fetchAll();

// Calculate statistics
$stats = [
    'total_projects' => count($projects),
    'total_nominations' => count($nominations),
    'latest_nomination' => !empty($nominations) ? date('M d, Y', strtotime($nominations[0]['nomination_date'])) : 'No nominations yet'
];
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
        <!-- Student Profile -->
        <div class="row mb-5">
            <div class="col-md-4" data-aos="fade-right">
                <div class="modern-card">
                    <div class="card-body text-center">
                        <?php if ($student['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($student['profile_image']); ?>" 
                                 alt="Profile" 
                                 class="rounded-circle mb-3"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                 style="width: 120px; height: 120px; font-size: 3rem;">
                                <i class="bi bi-person"></i>
                            </div>
                        <?php endif; ?>
                        <h4 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($student['department']); ?></p>
                        <div class="d-grid">
                            <a href="edit_profile.php" class="btn btn-outline-primary">
                                <i class="bi bi-pencil me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8" data-aos="fade-left">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="modern-card dashboard-card">
                            <i class="bi bi-folder"></i>
                            <div class="stats-value"><?php echo $stats['total_projects']; ?></div>
                            <div class="stats-label">Projects</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="modern-card dashboard-card">
                            <i class="bi bi-trophy"></i>
                            <div class="stats-value"><?php echo $stats['total_nominations']; ?></div>
                            <div class="stats-label">Nominations</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="modern-card dashboard-card">
                            <i class="bi bi-calendar-check"></i>
                            <div class="stats-value" style="font-size: 1rem;">
                                <?php echo $stats['latest_nomination']; ?>
                            </div>
                            <div class="stats-label">Latest Nomination</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Section -->
        <div class="row mb-5">
            <div class="col-12" data-aos="fade-up">
                <div class="modern-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">My Projects</h5>
                            <a href="submit_project.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-2"></i>Submit New Project
                            </a>
                        </div>
                        <?php if (empty($projects)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder-x display-1 text-muted"></i>
                                <p class="mt-3 text-muted">No projects submitted yet</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Submission Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($projects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($project['submission_date'])); ?></td>
                                                <td>
                                                    <a href="view_project.php?id=<?php echo $project['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nominations Section -->
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">My Nominations</h5>
                        <?php if (empty($nominations)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-trophy-x display-1 text-muted"></i>
                                <p class="mt-3 text-muted">No nominations received yet</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($nominations as $nomination): ?>
                                    <div class="list-group-item bg-transparent">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="bi bi-trophy text-warning me-2"></i>
                                                <?php echo htmlspecialchars($nomination['category']); ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($nomination['nomination_date'])); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($nomination['reason']); ?></p>
                                        <small class="text-muted">
                                            Nominated by <?php echo htmlspecialchars($nomination['nominator']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
    </script>
</body>
</html> 
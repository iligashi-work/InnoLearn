<?php
session_start();
require_once '../config/database.php';

// Check if super admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: login.php');
    exit();
}

// Fetch statistics
$stats = [
    'students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'nominations' => $pdo->query("SELECT COUNT(*) FROM nominations")->fetchColumn(),
    'admins' => $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn(),
    'active_admins' => $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = 1")->fetchColumn(),
    'departments' => $pdo->query("SELECT COUNT(DISTINCT department) FROM students")->fetchColumn()
];

// Fetch recent activities
$recent_activities = $pdo->query("
    (SELECT 'project' as type, p.title as title, s.first_name, s.last_name, p.submission_date as date
     FROM projects p
     JOIN students s ON p.student_id = s.id
     ORDER BY p.submission_date DESC
     LIMIT 5)
    UNION ALL
    (SELECT 'nomination' as type, n.category as title, s.first_name, s.last_name, n.nomination_date as date
     FROM nominations n
     JOIN students s ON n.student_id = s.id
     ORDER BY n.nomination_date DESC
     LIMIT 5)
    ORDER BY date DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin list
$admins = $pdo->query("SELECT id, username, role, is_active FROM admins ORDER BY role DESC, username")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - InnoLearn</title>
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
            <a class="navbar-brand" href="super_admin_dashboard.php">InnoLearn Super Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="students/list.php">
                            <i class="bi bi-people"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="projects/list.php">
                            <i class="bi bi-folder"></i> Projects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nominations/list.php">
                            <i class="bi bi-trophy"></i> Nominations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="bi bi-graph-up"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_admins.php">
                            <i class="bi bi-gear"></i> Manage Admins
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
            <h2 class="section-title mb-0">Super Admin Dashboard</h2>
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
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-folder"></i>
                    <div class="stats-value"><?php echo $stats['projects']; ?></div>
                    <div class="stats-label">Total Projects</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-trophy"></i>
                    <div class="stats-value"><?php echo $stats['nominations']; ?></div>
                    <div class="stats-label">Total Nominations</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-shield-check"></i>
                    <div class="stats-value"><?php echo $stats['active_admins']; ?>/<?php echo $stats['admins']; ?></div>
                    <div class="stats-label">Active Admins</div>
                </div>
            </div>
        </div>

        <!-- Admin Management Section -->
        <div class="row mb-5">
            <div class="col-md-6" data-aos="fade-right">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Admin Management</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $admin['role'] === 'super_admin' ? 'danger' : 'primary'; ?>">
                                                    <?php echo ucfirst($admin['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $admin['is_active'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                                    <a href="manage_admins.php?action=toggle&id=<?php echo $admin['id']; ?>" 
                                                       class="btn btn-sm btn-<?php echo $admin['is_active'] ? 'danger' : 'success'; ?>">
                                                        <?php echo $admin['is_active'] ? 'Disable' : 'Enable'; ?>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Quick Actions</h5>
                        <div class="d-grid gap-3">
                            <a href="manage_admins.php?action=add" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus me-2"></i>Add New Admin
                            </a>
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
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12" data-aos="fade-up">
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
                InnoLearn Super Admin Dashboard - Managing Student Excellence
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
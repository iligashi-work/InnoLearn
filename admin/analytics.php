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

// Fetch department-wise student count
$stmt = $pdo->prepare("
    SELECT department, COUNT(*) as count
    FROM students
    WHERE admin_id = ?
    GROUP BY department
    ORDER BY count DESC
");
$stmt->execute([$admin_id]);
$department_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch project category distribution
$stmt = $pdo->prepare("
    SELECT p.category, COUNT(*) as count
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY p.category
    ORDER BY count DESC
");
$stmt->execute([$admin_id]);
$project_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch nomination category distribution
$stmt = $pdo->prepare("
    SELECT n.category, COUNT(*) as count
    FROM nominations n
    JOIN students s ON n.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY n.category
    ORDER BY count DESC
");
$stmt->execute([$admin_id]);
$nomination_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly project submissions
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(p.submission_date, '%Y-%m') as month,
           COUNT(*) as count
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute([$admin_id]);
$monthly_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly nominations
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(n.nomination_date, '%Y-%m') as month,
           COUNT(*) as count
    FROM nominations n
    JOIN students s ON n.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute([$admin_id]);
$monthly_nominations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="analytics.php">
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
        <h2 class="section-title mb-5">My Analytics Dashboard</h2>

        <div class="row g-4 mb-5">
            <!-- Department Distribution -->
            <div class="col-md-6" data-aos="fade-right">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title">Department Distribution</h5>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Project Categories -->
            <div class="col-md-6" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title">Project Categories</h5>
                        <canvas id="projectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Nomination Categories -->
            <div class="col-md-6" data-aos="fade-right">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title">Nomination Categories</h5>
                        <canvas id="nominationChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Monthly Activity -->
            <div class="col-md-6" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title">Monthly Activity</h5>
                        <canvas id="activityChart"></canvas>
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

        // Department Chart
        new Chart(document.getElementById('departmentChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($department_stats, 'department')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($department_stats, 'count')); ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                        '#e74a3b', '#5a5c69', '#858796', '#6f42c1'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Project Chart
        new Chart(document.getElementById('projectChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($project_stats, 'category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($project_stats, 'count')); ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                        '#e74a3b', '#5a5c69', '#858796', '#6f42c1'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Nomination Chart
        new Chart(document.getElementById('nominationChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($nomination_stats, 'category')); ?>,
                datasets: [{
                    label: 'Nominations',
                    data: <?php echo json_encode(array_column($nomination_stats, 'count')); ?>,
                    backgroundColor: '#4e73df'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Activity Chart
        new Chart(document.getElementById('activityChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_projects, 'month')); ?>,
                datasets: [{
                    label: 'Projects',
                    data: <?php echo json_encode(array_column($monthly_projects, 'count')); ?>,
                    borderColor: '#4e73df',
                    tension: 0.1
                }, {
                    label: 'Nominations',
                    data: <?php echo json_encode(array_column($monthly_nominations, 'count')); ?>,
                    borderColor: '#1cc88a',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 
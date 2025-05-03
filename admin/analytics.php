<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Department Statistics
$dept_stats = $pdo->query("
    SELECT 
        department,
        COUNT(DISTINCT s.id) as student_count,
        COUNT(DISTINCT p.id) as project_count,
        COUNT(DISTINCT n.id) as nomination_count
    FROM students s
    LEFT JOIN projects p ON s.id = p.student_id
    LEFT JOIN nominations n ON s.id = n.student_id
    GROUP BY department
    ORDER BY student_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Monthly Nomination Trends (Last 6 months)
$nomination_trends = $pdo->query("
    SELECT 
        DATE_FORMAT(nomination_date, '%Y-%m') as month,
        category,
        COUNT(*) as count
    FROM nominations
    WHERE nomination_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(nomination_date, '%Y-%m'), category
    ORDER BY month DESC, count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Top Performing Students
$top_students = $pdo->query("
    WITH StudentStats AS (
        SELECT 
            s.id,
            s.first_name,
            s.last_name,
            s.department,
            COUNT(DISTINCT p.id) as project_count,
            COUNT(DISTINCT n.id) as nomination_count
        FROM students s
        LEFT JOIN projects p ON s.id = p.student_id
        LEFT JOIN nominations n ON s.id = n.student_id
        GROUP BY s.id, s.first_name, s.last_name, s.department
    )
    SELECT *
    FROM StudentStats
    WHERE project_count > 0 OR nomination_count > 0
    ORDER BY (project_count + nomination_count) DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Overall Statistics
$overall_stats = [
    'total_students' => $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'total_projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'total_nominations' => $pdo->query("SELECT COUNT(*) FROM nominations")->fetchColumn(),
    'active_departments' => $pdo->query("SELECT COUNT(DISTINCT department) FROM students")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a class="navbar-brand" href="dashboard.php">InnoLearn Admin</a>
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
        <h2 class="section-title mb-5">Analytics Dashboard</h2>

        <!-- Quick Stats -->
        <div class="row g-4 mb-5">
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-people"></i>
                    <div class="stats-value"><?php echo $overall_stats['total_students']; ?></div>
                    <div class="stats-label">Total Students</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-folder"></i>
                    <div class="stats-value"><?php echo $overall_stats['total_projects']; ?></div>
                    <div class="stats-label">Total Projects</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-trophy"></i>
                    <div class="stats-value"><?php echo $overall_stats['total_nominations']; ?></div>
                    <div class="stats-label">Total Nominations</div>
                </div>
            </div>
            <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="modern-card dashboard-card">
                    <i class="bi bi-building"></i>
                    <div class="stats-value"><?php echo $overall_stats['active_departments']; ?></div>
                    <div class="stats-label">Active Departments</div>
                </div>
            </div>
        </div>

        <!-- Department Performance -->
        <div class="row mb-5">
            <div class="col-md-8" data-aos="fade-right">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Department Performance</h5>
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Top Performers</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach ($top_students as $student): ?>
                                <div class="list-group-item bg-transparent">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h6>
                                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($student['department']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small>
                                            <i class="bi bi-folder text-primary"></i> <?php echo $student['project_count']; ?> Projects
                                        </small>
                                        <small>
                                            <i class="bi bi-trophy text-success"></i> <?php echo $student['nomination_count']; ?> Nominations
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nomination Trends -->
        <div class="row">
            <div class="col-12" data-aos="fade-up">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Nomination Trends</h5>
                        <canvas id="nominationTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="bi bi-stars me-2"></i>
                InnoLearn Analytics - Insights into Student Excellence
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

        // Department Performance Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($dept_stats, 'department')); ?>,
                datasets: [{
                    label: 'Students',
                    data: <?php echo json_encode(array_column($dept_stats, 'student_count')); ?>,
                    backgroundColor: 'rgba(33, 150, 243, 0.5)',
                    borderColor: 'rgba(33, 150, 243, 1)',
                    borderWidth: 1
                }, {
                    label: 'Projects',
                    data: <?php echo json_encode(array_column($dept_stats, 'project_count')); ?>,
                    backgroundColor: 'rgba(76, 175, 80, 0.5)',
                    borderColor: 'rgba(76, 175, 80, 1)',
                    borderWidth: 1
                }, {
                    label: 'Nominations',
                    data: <?php echo json_encode(array_column($dept_stats, 'nomination_count')); ?>,
                    backgroundColor: 'rgba(255, 152, 0, 0.5)',
                    borderColor: 'rgba(255, 152, 0, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Nomination Trends Chart
        const trendsCtx = document.getElementById('nominationTrendsChart').getContext('2d');
        const trendData = <?php echo json_encode($nomination_trends); ?>;
        
        // Process data for chart
        const months = [...new Set(trendData.map(item => item.month))];
        const categories = [...new Set(trendData.map(item => item.category))];
        
        const datasets = categories.map(category => {
            const data = months.map(month => {
                const entry = trendData.find(item => item.month === month && item.category === category);
                return entry ? entry.count : 0;
            });
            
            return {
                label: category,
                data: data,
                borderColor: `hsl(${Math.random() * 360}, 70%, 50%)`,
                fill: false
            };
        });

        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 
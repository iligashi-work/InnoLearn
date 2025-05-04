<?php
session_start();
require_once '../config/database.php';
require_once '../config/openai.php'; // We'll create this file for API configuration

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

// Fetch all published projects for this admin's students
$stmt = $pdo->prepare("
    SELECT p.*, 
           g.grade,
           CASE 
               WHEN g.grade >= 90 THEN 'A'
               WHEN g.grade >= 80 THEN 'B'
               WHEN g.grade >= 70 THEN 'C'
               WHEN g.grade >= 60 THEN 'D'
               ELSE 'F'
           END as letter_grade
    FROM projects p
    LEFT JOIN project_grades g ON p.id = g.project_id
    WHERE p.student_id = ?
    ORDER BY p.submission_date DESC
");
$stmt->execute([$admin_id]);
$published_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get AI summary for a project
function getProjectAISummary($title, $description) {
    // You can use your existing OpenAI/DeepSeek integration here
    // For now, we'll use a simple local fallback
    $prompt = "Write a short, engaging summary for a student project titled \"$title\". Project description: $description";
    try {
        // If you have an AI API, call it here (pseudo-code):
        // $summary = callYourAI($prompt);
        // return $summary;
        // Fallback:
        return substr($description, 0, 120) . (strlen($description) > 120 ? '...' : '');
    } catch (Exception $e) {
        return "No summary available.";
    }
}

// Function to generate AI insights
function generateInsights($data, $type) {
    global $openai;
    
    $prompt = "Analyze the following $type data and provide 3 key insights: " . json_encode($data);
    
    try {
        $response = $openai->chat->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an analytics expert. Provide concise, actionable insights.'],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        
        return $response->choices[0]->message->content;
    } catch (Exception $e) {
        return "Unable to generate insights at this time.";
    }
}

// Generate insights for each category
$department_insights = getDeepSeekInsights($department_stats, 'department distribution');
$project_insights = getDeepSeekInsights($project_stats, 'project categories');
$nomination_insights = getDeepSeekInsights($nomination_stats, 'nomination categories');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - InnoLearn</title>
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
            <a class="navbar-brand" href="dashboard.php">InnoLearn Admin</a>
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
                        <div class="insights-container mt-3">
                            <h6 class="text-muted">AI Insights:</h6>
                            <p class="insights-text"><?php echo nl2br(htmlspecialchars($department_insights)); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project Categories -->
            <div class="col-md-6" data-aos="fade-left">
                <div class="modern-card">
                    <div class="card-body">
                        <h5 class="card-title">Project Categories</h5>
                        <canvas id="projectChart"></canvas>
                        <div class="insights-container mt-3">
                            <h6 class="text-muted">AI Insights:</h6>
                            <p class="insights-text"><?php echo nl2br(htmlspecialchars($project_insights)); ?></p>
                        </div>
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
                        <div class="insights-container mt-3">
                            <h6 class="text-muted">AI Insights:</h6>
                            <p class="insights-text"><?php echo nl2br(htmlspecialchars($nomination_insights)); ?></p>
                        </div>
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

    <!-- Documentation Section -->
    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-12">
                <div class="modern-card">
                    <h3 class="mb-4">Analytics Documentation</h3>
                    
                    <div class="documentation-item mb-4">
                        <h5>Department Distribution</h5>
                        <p>This chart shows the distribution of students across different departments. It helps identify:</p>
                        <ul>
                            <li>Most popular departments</li>
                            <li>Student distribution across departments</li>
                            <li>Department-wise student engagement</li>
                        </ul>
                    </div>

                    <div class="documentation-item mb-4">
                        <h5>Project Categories</h5>
                        <p>This visualization displays the types of projects students are working on. It helps track:</p>
                        <ul>
                            <li>Most common project types</li>
                            <li>Project category distribution</li>
                            <li>Student interests and trends</li>
                        </ul>
                    </div>

                    <div class="documentation-item mb-4">
                        <h5>Nomination Categories</h5>
                        <p>This chart shows the distribution of student nominations across different categories. It helps monitor:</p>
                        <ul>
                            <li>Recognition patterns</li>
                            <li>Student achievements by category</li>
                            <li>Nomination trends</li>
                        </ul>
                    </div>

                    <div class="documentation-item">
                        <h5>Monthly Activity</h5>
                        <p>This timeline chart tracks project submissions and nominations over time. It helps analyze:</p>
                        <ul>
                            <li>Activity trends throughout the year</li>
                            <li>Peak periods of student engagement</li>
                            <li>Comparison between projects and nominations</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <h5>AI Insights</h5>
                        <p>Each chart includes AI-generated insights that provide:</p>
                        <ul>
                            <li>Key observations about the data</li>
                            <li>Trend analysis and patterns</li>
                            <li>Actionable recommendations</li>
                        </ul>
                        <p class="text-muted small">Note: If the AI service is unavailable, basic statistical insights will be provided instead.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 mb-5">
        <div class="modern-card">
            <h3 class="mb-4">AI Summaries of Published Projects</h3>
            <?php if (empty($published_projects)): ?>
                <p class="text-muted">No published projects found.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($published_projects as $project): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                                    </p>
                                    <hr>
                                    <strong>AI Summary:</strong>
                                    <p>
                                        <?php echo htmlspecialchars(getProjectAISummary($project['title'], $project['description'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="bi bi-stars me-2"></i>
                InnoLearn Admin Dashboard - Managing Student Excellence
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

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            fetch('analytics_data.php')
                .then(response => response.json())
                .then(data => {
                    updateCharts(data);
                });
        }, 300000);

        function updateCharts(data) {
            // Update department chart
            departmentChart.data.labels = data.department_stats.map(item => item.department);
            departmentChart.data.datasets[0].data = data.department_stats.map(item => item.count);
            departmentChart.update();

            // Update project chart
            projectChart.data.labels = data.project_stats.map(item => item.category);
            projectChart.data.datasets[0].data = data.project_stats.map(item => item.count);
            projectChart.update();

            // Update nomination chart
            nominationChart.data.labels = data.nomination_stats.map(item => item.category);
            nominationChart.data.datasets[0].data = data.nomination_stats.map(item => item.count);
            nominationChart.update();

            // Update activity chart
            activityChart.data.labels = data.monthly_projects.map(item => item.month);
            activityChart.data.datasets[0].data = data.monthly_projects.map(item => item.count);
            activityChart.data.datasets[1].data = data.monthly_nominations.map(item => item.count);
            activityChart.update();
        }
    </script>
</body>
</html> 
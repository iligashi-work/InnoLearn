<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get grading statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_projects,
        SUM(CASE WHEN grade IS NOT NULL THEN 1 ELSE 0 END) as graded_projects,
        AVG(grade) as average_grade,
        MIN(grade) as min_grade,
        MAX(grade) as max_grade
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ?
");
$stmt->execute([$admin_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Get recent grading activity
$stmt = $pdo->prepare("
    SELECT p.*, s.first_name, s.last_name, s.department,
           a.username as grader_name,
           CASE 
               WHEN p.grade >= 90 THEN 'A'
               WHEN p.grade >= 80 THEN 'B'
               WHEN p.grade >= 70 THEN 'C'
               WHEN p.grade >= 60 THEN 'D'
               ELSE 'F'
           END as letter_grade
    FROM projects p
    JOIN students s ON p.student_id = s.id
    LEFT JOIN admins a ON p.graded_by = a.id
    WHERE s.admin_id = ? AND p.grade IS NOT NULL
    ORDER BY p.grading_date DESC
    LIMIT 10
");
$stmt->execute([$admin_id]);
$recent_grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get grade distribution
$stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN grade >= 90 THEN 'A'
            WHEN grade >= 80 THEN 'B'
            WHEN grade >= 70 THEN 'C'
            WHEN grade >= 60 THEN 'D'
            ELSE 'F'
        END as grade_letter,
        COUNT(*) as count
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ? AND p.grade IS NOT NULL
    GROUP BY grade_letter
    ORDER BY grade_letter
");
$stmt->execute([$admin_id]);
$grade_distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Process - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Grading Process Overview</h2>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card bg-primary text-white">
                                    <h3><?php echo $stats['total_projects']; ?></h3>
                                    <p>Total Projects</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-success text-white">
                                    <h3><?php echo $stats['graded_projects']; ?></h3>
                                    <p>Graded Projects</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-info text-white">
                                    <h3><?php echo round($stats['average_grade'], 1); ?>%</h3>
                                    <p>Average Grade</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-warning text-white">
                                    <h3><?php echo round(($stats['graded_projects'] / $stats['total_projects']) * 100, 1); ?>%</h3>
                                    <p>Completion Rate</p>
                                </div>
                            </div>
                        </div>

                        <!-- Grade Distribution Chart -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="modern-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Grade Distribution</h5>
                                        <canvas id="gradeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Recent Grading Activity</h5>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Project</th>
                                                        <th>Grade</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_grades as $grade): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($grade['title']); ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php 
                                                                    echo $grade['grade'] >= 90 ? 'success' : 
                                                                        ($grade['grade'] >= 80 ? 'info' : 
                                                                        ($grade['grade'] >= 70 ? 'warning' : 'danger')); 
                                                                ?>">
                                                                    <?php echo $grade['letter_grade']; ?> (<?php echo $grade['grade']; ?>%)
                                                                </span>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($grade['grading_date'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Grade Distribution Chart
        const gradeData = {
            labels: <?php echo json_encode(array_column($grade_distribution, 'grade_letter')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($grade_distribution, 'count')); ?>,
                backgroundColor: [
                    '#28a745', // A
                    '#17a2b8', // B
                    '#ffc107', // C
                    '#fd7e14', // D
                    '#dc3545'  // F
                ]
            }]
        };

        new Chart(document.getElementById('gradeChart'), {
            type: 'pie',
            data: gradeData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
</body>
</html> 
<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get student ID from URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    header('Location: list.php');
    exit();
}

// Get student details
$stmt = $pdo->prepare("
    SELECT s.*, d.name as department_name
    FROM students s
    LEFT JOIN departments d ON s.department = d.id
    WHERE s.id = ? AND s.admin_id = ?
");
$stmt->execute([$student_id, $_SESSION['admin_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: list.php');
    exit();
}

// Get student's projects
$stmt = $pdo->prepare("
    SELECT p.*, 
           CASE 
               WHEN p.grade >= 90 THEN 'A'
               WHEN p.grade >= 80 THEN 'B'
               WHEN p.grade >= 70 THEN 'C'
               WHEN p.grade >= 60 THEN 'D'
               ELSE 'F'
           END as letter_grade
    FROM projects p
    WHERE p.student_id = ?
    ORDER BY p.submission_date DESC
");
$stmt->execute([$student_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_projects = count($projects);
$graded_projects = 0;
$total_grade = 0;
$project_categories = [];

foreach ($projects as $project) {
    if ($project['grade'] !== null) {
        $graded_projects++;
        $total_grade += $project['grade'];
    }
    if (!isset($project_categories[$project['category']])) {
        $project_categories[$project['category']] = 0;
    }
    $project_categories[$project['category']]++;
}

$average_grade = $graded_projects > 0 ? round($total_grade / $graded_projects, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="card-title mb-0">Student Details</h2>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to List
                            </a>
                        </div>

                        <!-- Student Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label>Student ID</label>
                                    <p><?php echo htmlspecialchars($student['student_id']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Name</label>
                                    <p><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Email</label>
                                    <p><?php echo htmlspecialchars($student['email']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label>Department</label>
                                    <p><?php echo htmlspecialchars($student['department_name']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Registration Date</label>
                                    <p><?php echo date('M d, Y', strtotime($student['created_at'])); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Status</label>
                                    <p>
                                        <span class="badge bg-<?php echo $student['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stat-card bg-primary text-white">
                                    <h3><?php echo $total_projects; ?></h3>
                                    <p>Total Projects</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-success text-white">
                                    <h3><?php echo $graded_projects; ?></h3>
                                    <p>Graded Projects</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-info text-white">
                                    <h3><?php echo $average_grade; ?>%</h3>
                                    <p>Average Grade</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card bg-warning text-white">
                                    <h3><?php echo round(($graded_projects / $total_projects) * 100, 1); ?>%</h3>
                                    <p>Completion Rate</p>
                                </div>
                            </div>
                        </div>

                        <!-- Project Categories Chart -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="modern-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Project Categories</h5>
                                        <canvas id="categoryChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projects Table -->
                        <h4 class="mb-3">Projects</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Submission Date</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['title']); ?></td>
                                            <td><?php echo htmlspecialchars($project['category']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($project['submission_date'])); ?></td>
                                            <td>
                                                <?php if ($project['grade'] !== null): ?>
                                                    <span class="badge bg-<?php 
                                                        echo $project['grade'] >= 90 ? 'success' : 
                                                            ($project['grade'] >= 80 ? 'info' : 
                                                            ($project['grade'] >= 70 ? 'warning' : 'danger')); 
                                                    ?>">
                                                        <?php echo $project['letter_grade']; ?> (<?php echo $project['grade']; ?>%)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $project['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($project['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="../projects/view.php?id=<?php echo $project['id']; ?>" class="btn btn-info btn-sm">
                                                    <i class="bi bi-eye me-1"></i>View
                                                </a>
                                            </td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Project Categories Chart
        const categoryData = {
            labels: <?php echo json_encode(array_keys($project_categories)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($project_categories)); ?>,
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8',
                    '#6c757d'
                ]
            }]
        };

        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: categoryData,
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
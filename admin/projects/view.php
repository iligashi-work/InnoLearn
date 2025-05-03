<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch project details
$stmt = $pdo->prepare("
    SELECT p.*, s.first_name, s.last_name, s.department
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE p.id = ?
");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    echo "Project not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Project - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            margin-top: 50px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 1.5rem;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .modern-card{
            padding:20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                Project Details
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th>Title</th>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?php echo htmlspecialchars($project['description']); ?></td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td><?php echo htmlspecialchars($project['category']); ?></td>
                    </tr>
                    <tr>
                        <th>Student</th>
                        <td><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td><?php echo htmlspecialchars($project['department']); ?></td>
                    </tr>
                    <tr>
                        <th>Submission Date</th>
                        <td><?php echo htmlspecialchars($project['submission_date']); ?></td>
                    </tr>
                    <?php if (!empty($project['file_path'])): ?>
                    <tr>
                        <th>File</th>
                        <td><a href="../../<?php echo htmlspecialchars($project['file_path']); ?>" class="btn btn-sm btn-success" target="_blank"><i class="bi bi-download"></i> Download</a></td>
                    </tr>
                    <?php endif; ?>
                </table>
                <div class="text-center mt-4">
                    <a href="list.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Back to Projects</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
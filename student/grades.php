<?php
session_start();
require_once '../config/database.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];

// Get student's graded projects
$stmt = $pdo->prepare("
    SELECT p.*, a.username as grader_name, 
           CASE 
               WHEN p.grade >= 90 THEN 'A'
               WHEN p.grade >= 80 THEN 'B'
               WHEN p.grade >= 70 THEN 'C'
               WHEN p.grade >= 60 THEN 'D'
               ELSE 'F'
           END as letter_grade
    FROM projects p
    LEFT JOIN admins a ON p.graded_by = a.id
    WHERE p.student_id = ? AND p.grade IS NOT NULL
    ORDER BY p.grading_date DESC
");
$stmt->execute([$student_id]);
$graded_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">My Project Grades</h2>

                        <?php if (empty($graded_projects)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No graded projects found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Project Title</th>
                                            <th>Category</th>
                                            <th>Grade</th>
                                            <th>Graded By</th>
                                            <th>Date Graded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($graded_projects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td><?php echo htmlspecialchars($project['category']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $project['grade'] >= 90 ? 'success' : 
                                                            ($project['grade'] >= 80 ? 'info' : 
                                                            ($project['grade'] >= 70 ? 'warning' : 'danger')); 
                                                    ?>">
                                                        <?php echo $project['letter_grade']; ?> (<?php echo $project['grade']; ?>%)
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($project['grader_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($project['grading_date'])); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" 
                                                            data-bs-target="#feedbackModal<?php echo $project['id']; ?>">
                                                        <i class="bi bi-chat-square-text me-1"></i>View Feedback
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Feedback Modal -->
                                            <div class="modal fade" id="feedbackModal<?php echo $project['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Feedback for <?php echo htmlspecialchars($project['title']); ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <h6>Grade Breakdown</h6>
                                                                <div class="progress mb-2">
                                                                    <div class="progress-bar" role="progressbar" 
                                                                         style="width: <?php echo $project['grade']; ?>%">
                                                                        <?php echo $project['grade']; ?>%
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6>Feedback</h6>
                                                                <div class="feedback-content">
                                                                    <?php 
                                                                    $feedback_points = explode("\n", $project['feedback']);
                                                                    foreach ($feedback_points as $point): 
                                                                    ?>
                                                                        <div class="d-flex align-items-center mb-2">
                                                                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                                            <span><?php echo htmlspecialchars($point); ?></span>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
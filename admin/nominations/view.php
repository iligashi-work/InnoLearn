<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get nomination ID from URL
$nomination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($nomination_id <= 0) {
    header('Location: list.php');
    exit();
}

// Get nomination details
$stmt = $pdo->prepare("
    SELECT n.*, 
           s.first_name, s.last_name, s.department,
           s.student_id as student_number,
           s.admin_id,
           p.title as project_title,
           p.category as project_category,
           p.submission_date as project_date
    FROM nominations n
    JOIN students s ON n.student_id = s.id
    LEFT JOIN projects p ON p.student_id = s.id
    WHERE n.id = ? AND s.admin_id = ?
    ORDER BY p.submission_date DESC
    LIMIT 1
");
$stmt->execute([$nomination_id, $_SESSION['admin_id']]);
$nomination = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nomination) {
    header('Location: list.php');
    exit();
}

// Get student's other nominations
$stmt = $pdo->prepare("
    SELECT n.*, n.category
    FROM nominations n
    WHERE n.student_id = ? AND n.id != ?
    ORDER BY n.nomination_date DESC
");
$stmt->execute([$nomination['student_id'], $nomination_id]);
$other_nominations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Nomination - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="modern-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="card-title mb-0">Nomination Details</h2>
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Back to List
                            </a>
                        </div>

                        <!-- Nomination Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label>Nomination ID</label>
                                    <p><?php echo htmlspecialchars($nomination['id']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Category</label>
                                    <p><?php echo htmlspecialchars($nomination['category']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label>Nomination Date</label>
                                    <p><?php echo date('M d, Y', strtotime($nomination['nomination_date'])); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label>Student</label>
                                    <p>
                                        <?php echo htmlspecialchars($nomination['first_name'] . ' ' . $nomination['last_name']); ?>
                                        <br>
                                        <small class="text-muted">ID: <?php echo htmlspecialchars($nomination['student_number']); ?></small>
                                    </p>
                                </div>
                                <div class="info-group">
                                    <label>Department</label>
                                    <p><?php echo htmlspecialchars($nomination['department']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Project Information -->
                        <?php if ($nomination['project_title']): ?>
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="mb-3">Related Project</h4>
                                    <div class="modern-card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($nomination['project_title']); ?></h5>
                                            <p class="card-text">
                                                <strong>Category:</strong> <?php echo htmlspecialchars($nomination['project_category']); ?><br>
                                                <strong>Submission Date:</strong> <?php echo date('M d, Y', strtotime($nomination['project_date'])); ?>
                                            </p>
                                            <a href="../projects/view.php?id=<?php echo $nomination['project_id']; ?>" class="btn btn-info">
                                                <i class="bi bi-eye me-1"></i>View Project
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Other Nominations -->
                        <?php if (!empty($other_nominations)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="mb-3">Other Nominations</h4>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($other_nominations as $other): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($other['category']); ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($other['nomination_date'])); ?></td>
                                                        <td>
                                                            <a href="view.php?id=<?php echo $other['id']; ?>" class="btn btn-info btn-sm">
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
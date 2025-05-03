<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Handle deletion if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_nomination'])) {
    $nomination_id = $_POST['nomination_id'];
    
    $stmt = $pdo->prepare("DELETE FROM nominations WHERE id = ?");
    if ($stmt->execute([$nomination_id])) {
        $success_message = "Nomination deleted successfully!";
    } else {
        $error_message = "Error deleting nomination!";
    }
}

// Fetch all nominations with student and admin details
$query = "SELECT n.*, 
          s.student_id as student_number, 
          s.first_name, 
          s.last_name,
          a.username as nominated_by_username
          FROM nominations n
          JOIN students s ON n.student_id = s.id
          JOIN admins a ON n.nominated_by = a.id
          ORDER BY n.nomination_date DESC";

$stmt = $pdo->query($query);
$nominations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nominations - TopTrack</title>
    <link rel="stylesheet" href="../../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">TopTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Nominations</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create New Nomination
            </a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Category</th>
                        <th>Reason</th>
                        <th>Nominated By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nominations as $nomination): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($nomination['student_number'] . ' - ' . 
                                      $nomination['first_name'] . ' ' . $nomination['last_name']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($nomination['category']); ?></td>
                            <td><?php echo htmlspecialchars($nomination['reason']); ?></td>
                            <td><?php echo htmlspecialchars($nomination['nominated_by_username']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($nomination['nomination_date'])); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $nomination['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this nomination?');">
                                    <input type="hidden" name="nomination_id" value="<?php echo $nomination['id']; ?>">
                                    <button type="submit" name="delete_nomination" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($nominations)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No nominations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
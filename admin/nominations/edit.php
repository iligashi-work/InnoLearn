<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$error_message = '';
$success_message = '';

// Get nomination ID from URL
$nomination_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($nomination_id <= 0) {
    header('Location: list.php');
    exit();
}

// Fetch nomination data
$stmt = $pdo->prepare("SELECT n.*, s.id as student_id FROM nominations n JOIN students s ON n.student_id = s.id WHERE n.id = ?");
$stmt->execute([$nomination_id]);
$nomination = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nomination) {
    header('Location: list.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $category = $_POST['category'];
    $reason = $_POST['reason'];
    
    // Update nomination
    $stmt = $pdo->prepare("UPDATE nominations SET student_id = ?, category = ?, reason = ? WHERE id = ?");
    
    if ($stmt->execute([$student_id, $category, $reason, $nomination_id])) {
        $success_message = "Nomination updated successfully!";
        // Refresh nomination data
        $stmt = $pdo->prepare("SELECT n.*, s.id as student_id FROM nominations n JOIN students s ON n.student_id = s.id WHERE n.id = ?");
        $stmt->execute([$nomination_id]);
        $nomination = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error_message = "Error updating nomination!";
    }
}

// Fetch all students for the dropdown
$stmt = $pdo->query("SELECT id, student_id, first_name, last_name FROM students ORDER BY first_name, last_name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nomination - InnoLearn</title>
    <link rel="stylesheet" href="../../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .modern-card{
            padding:20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">InnoLearn</a>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Edit Nomination</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student</label>
                                <select class="form-select" id="student_id" name="student_id" required>
                                    <option value="">Select a student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" 
                                                <?php echo ($student['id'] == $nomination['student_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <?php
                                    $categories = ['Academic Excellence', 'Leadership', 'Community Service', 'Sports Achievement', 'Innovation'];
                                    foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>" 
                                                <?php echo ($cat == $nomination['category']) ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Nomination</label>
                                <textarea class="form-control" id="reason" name="reason" rows="4" required><?php echo htmlspecialchars($nomination['reason']); ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Nomination</button>
                                <a href="list.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
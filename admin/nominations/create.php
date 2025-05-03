<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/email_notifications.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $category = $_POST['category'];
    $reason = $_POST['reason'];
    $nominated_by = $_SESSION['admin_id'];
    $nomination_date = date('Y-m-d H:i:s');
    
    // Insert nomination
    $stmt = $pdo->prepare("INSERT INTO nominations (student_id, category, reason, nominated_by, nomination_date) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$student_id, $category, $reason, $nominated_by, $nomination_date])) {
        // Get student email for notification
        $student_stmt = $pdo->prepare("SELECT first_name, last_name, email FROM students WHERE id = ?");
        $student_stmt->execute([$student_id]);
        $student = $student_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Send email notification
        $student_name = $student['first_name'] . ' ' . $student['last_name'];
        sendNominationEmail($student['email'], $student_name, $category, $reason);
        
        $success_message = "Nomination created successfully!";
    } else {
        $error_message = "Error creating nomination!";
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
    <title>Create Nomination - InnoLearn</title>
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
                        <h3 class="mb-0">Create New Nomination</h3>
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
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['first_name'] . ' ' . $student['last_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="Academic Excellence">Academic Excellence</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Community Service">Community Service</option>
                                    <option value="Sports Achievement">Sports Achievement</option>
                                    <option value="Innovation">Innovation</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason for Nomination</label>
                                <textarea class="form-control" id="reason" name="reason" rows="4" required></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Create Nomination</button>
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
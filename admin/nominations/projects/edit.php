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

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id <= 0) {
    header('Location: list.php');
    exit();
}

// Fetch project data
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header('Location: list.php');
    exit();
}

// Fetch all students for the dropdown
$stmt = $pdo->query("SELECT id, first_name, last_name, student_id FROM students ORDER BY first_name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    
    // Handle file upload
    $file_path = $project['file_path']; // Keep existing file path by default
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == 0) {
        $target_dir = "../../uploads/projects/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["project_file"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["project_file"]["tmp_name"], $target_file)) {
            // Delete old file if it exists
            if ($project['file_path'] && file_exists("../../" . $project['file_path'])) {
                unlink("../../" . $project['file_path']);
            }
            $file_path = "uploads/projects/" . $new_filename;
        }
    }
    
    // Update project information
    $stmt = $pdo->prepare("UPDATE projects SET student_id = ?, title = ?, description = ?, category = ?, file_path = ? WHERE id = ?");
    
    if ($stmt->execute([$student_id, $title, $description, $category, $file_path, $project_id])) {
        $success_message = "Project updated successfully!";
        // Refresh project data
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error_message = "Error updating project";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - TopTrack</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Edit Project</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student</label>
                                <select class="form-select" id="student_id" name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>" 
                                                <?php echo $student['id'] == $project['student_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] . ' (' . $student['student_id'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Project Title</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($project['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" 
                                       value="<?php echo htmlspecialchars($project['category']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="project_file" class="form-label">Project File</label>
                                <input type="file" class="form-control" id="project_file" name="project_file">
                                <?php if ($project['file_path']): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">Current file: <?php echo basename($project['file_path']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Project</button>
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
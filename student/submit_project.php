<?php
session_start();
require_once '../config/database.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $document_file = $_FILES['document_file'];
    $image_files = $_FILES['images'];

    // Validate inputs
    if (empty($title) || empty($description) || empty($category)) {
        $error = "Please fill in all required fields";
    } else {
        try {
            $pdo->beginTransaction();

            // Upload document file
            $document_path = '';
            if ($document_file['error'] === UPLOAD_ERR_OK) {
                $allowed_docs = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $doc_type = mime_content_type($document_file['tmp_name']);
                
                if (!in_array($doc_type, $allowed_docs)) {
                    throw new Exception("Invalid document format. Please upload PDF or Word files only.");
                }

                $doc_extension = pathinfo($document_file['name'], PATHINFO_EXTENSION);
                $doc_filename = uniqid('doc_') . '.' . $doc_extension;
                $document_path = 'uploads/documents/' . $doc_filename;
                
                if (!move_uploaded_file($document_file['tmp_name'], '../' . $document_path)) {
                    throw new Exception("Failed to upload document file");
                }
            }

            // Insert project into database
            $stmt = $pdo->prepare("
                INSERT INTO projects (student_id, title, description, category, document_path, submission_date)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$student_id, $title, $description, $category, $document_path]);
            $project_id = $pdo->lastInsertId();

            // Upload and process images
            if (!empty($image_files['name'][0])) {
                $allowed_images = ['image/jpeg', 'image/png', 'image/gif'];
                
                foreach ($image_files['tmp_name'] as $key => $tmp_name) {
                    if ($image_files['error'][$key] === UPLOAD_ERR_OK) {
                        $img_type = mime_content_type($tmp_name);
                        
                        if (!in_array($img_type, $allowed_images)) {
                            throw new Exception("Invalid image format. Please upload JPG, PNG, or GIF files only.");
                        }

                        $img_extension = pathinfo($image_files['name'][$key], PATHINFO_EXTENSION);
                        $img_filename = uniqid('img_') . '.' . $img_extension;
                        $image_path = 'uploads/images/' . $img_filename;
                        
                        if (!move_uploaded_file($tmp_name, '../' . $image_path)) {
                            throw new Exception("Failed to upload image file");
                        }

                        // Insert image record
                        $stmt = $pdo->prepare("
                            INSERT INTO project_images (project_id, image_path)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$project_id, $image_path]);
                    }
                }
            }

            $pdo->commit();
            $success = "Project submitted successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Project - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="modern-card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Submit Project</h2>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="title" class="form-label">Project Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Project Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    <option value="Research">Research</option>
                                    <option value="Innovation">Innovation</option>
                                    <option value="Development">Development</option>
                                    <option value="Design">Design</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="document_file" class="form-label">Project Documentation (PDF/Word)</label>
                                <input type="file" class="form-control" id="document_file" name="document_file" accept=".pdf,.doc,.docx">
                                <div class="form-text">Upload your project documentation in PDF or Word format</div>
                            </div>

                            <div class="mb-3">
                                <label for="images" class="form-label">Project Images</label>
                                <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                                <div class="form-text">Upload multiple images (JPG, PNG, GIF)</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>Submit Project
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 
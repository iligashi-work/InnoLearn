<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Grading criteria
$grading_criteria = [
    'documentation' => [
        'weight' => 0.3,
        'criteria' => [
            'completeness' => 'Documentation completeness and detail',
            'clarity' => 'Clarity of explanation',
            'formatting' => 'Professional formatting and structure'
        ]
    ],
    'technical' => [
        'weight' => 0.4,
        'criteria' => [
            'innovation' => 'Technical innovation and creativity',
            'implementation' => 'Implementation quality',
            'complexity' => 'Project complexity'
        ]
    ],
    'presentation' => [
        'weight' => 0.3,
        'criteria' => [
            'visuals' => 'Quality of visual materials',
            'organization' => 'Project organization',
            'communication' => 'Communication effectiveness'
        ]
    ]
];

// Function to analyze project documentation
function analyzeDocumentation($file_path) {
    $score = 0;
    $feedback = [];
    
    // Check file size (larger files might indicate more detailed documentation)
    $file_size = filesize($file_path);
    if ($file_size > 1000000) { // More than 1MB
        $score += 0.3;
        $feedback[] = "Documentation is comprehensive and detailed";
    } else {
        $feedback[] = "Consider adding more detailed documentation";
    }
    
    // Check file type
    $file_type = mime_content_type($file_path);
    if ($file_type === 'application/pdf') {
        $score += 0.2;
        $feedback[] = "PDF format is professional and well-structured";
    }
    
    // Check for plagiarism (basic check)
    $content = file_get_contents($file_path);
    $plagiarism_score = checkPlagiarism($content);
    if ($plagiarism_score < 0.1) {
        $score += 0.2;
        $feedback[] = "Documentation shows original work";
    } else {
        $feedback[] = "Consider ensuring all content is properly cited";
    }
    
    return ['score' => $score, 'feedback' => $feedback];
}

// Function to analyze project images
function analyzeImages($project_id, $pdo) {
    $score = 0;
    $feedback = [];
    
    // Get project description
    $stmt = $pdo->prepare("SELECT description FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $description = $stmt->fetchColumn();
    
    if ($description) {
        // Check for image references in description
        $image_count = 0;
        
        // Look for common image reference patterns
        $patterns = [
            '/!\[.*?\]\(.*?\)/',  // Markdown image syntax
            '/<img.*?src=["\'](.*?)["\'].*?>/',  // HTML img tag
            '/\[image:.*?\]/',  // Custom image syntax
            '/image:.*?\.(jpg|jpeg|png|gif)/i'  // Direct image references
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $description, $matches)) {
                $image_count += count($matches[0]);
            }
        }
        
        if ($image_count >= 3) {
            $score += 0.3;
            $feedback[] = "Good number of image references found in description ($image_count)";
        } else {
            $feedback[] = "Consider adding more image references to the description (found $image_count)";
        }
        
        // Check for image quality indicators in description
        $quality_indicators = [
            'high resolution',
            'high quality',
            'detailed image',
            'clear picture',
            'professional photo'
        ];
        
        foreach ($quality_indicators as $indicator) {
            if (stripos($description, $indicator) !== false) {
                $score += 0.1;
                $feedback[] = "Description mentions high-quality images";
                break;
            }
        }
    } else {
        $feedback[] = "No project description found";
    }
    
    return ['score' => $score, 'feedback' => $feedback];
}

// Function to analyze project description
function analyzeDescription($description) {
    $score = 0;
    $feedback = [];
    
    // Check length
    $word_count = str_word_count($description);
    if ($word_count > 200) {
        $score += 0.2;
        $feedback[] = "Detailed project description provided";
    } else {
        $feedback[] = "Project description could be more detailed";
    }
    
    // Check for technical terms
    $technical_terms = ['algorithm', 'implementation', 'methodology', 'framework', 'architecture'];
    $found_terms = 0;
    foreach ($technical_terms as $term) {
        if (stripos($description, $term) !== false) {
            $found_terms++;
        }
    }
    
    if ($found_terms >= 3) {
        $score += 0.2;
        $feedback[] = "Good use of technical terminology";
    }
    
    // Check for code quality indicators
    $code_indicators = ['function', 'class', 'method', 'variable', 'loop'];
    $found_indicators = 0;
    foreach ($code_indicators as $indicator) {
        if (stripos($description, $indicator) !== false) {
            $found_indicators++;
        }
    }
    
    if ($found_indicators >= 2) {
        $score += 0.1;
        $feedback[] = "Good technical implementation details";
    }
    
    return ['score' => $score, 'feedback' => $feedback];
}

// Function to send notification to student
function sendNotification($student_id, $project_id, $grade, $pdo) {
    // Get student email
    $stmt = $pdo->prepare("SELECT email FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student_email = $stmt->fetchColumn();
    
    // Get project details
    $stmt = $pdo->prepare("SELECT title FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project_title = $stmt->fetchColumn();
    
    // Prepare notification message
    $subject = "Your project has been graded";
    $message = "Dear Student,\n\n";
    $message .= "Your project '$project_title' has been graded.\n";
    $message .= "Grade: $grade%\n\n";
    $message .= "You can view detailed feedback by logging into your account.\n\n";
    $message .= "Best regards,\nInnoLearn Team";
    
    // Send email notification
    // mail($student_email, $subject, $message);
    
    // Store notification in database
    $stmt = $pdo->prepare("
        INSERT INTO notifications (student_id, project_id, message, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([
        $student_id,
        $project_id,
        "Your project '$project_title' has been graded. Grade: $grade%"
    ]);
}

// Function to grade project
function gradeProject($project, $pdo) {
    global $grading_criteria;
    
    $total_score = 0;
    $feedback = [];
    
    // Analyze documentation
    if (!empty($project['document_path'])) {
        $doc_analysis = analyzeDocumentation('../' . $project['document_path']);
        $total_score += $doc_analysis['score'] * $grading_criteria['documentation']['weight'];
        $feedback = array_merge($feedback, $doc_analysis['feedback']);
    }
    
    // Analyze images
    $img_analysis = analyzeImages($project['id'], $pdo);
    $total_score += $img_analysis['score'] * $grading_criteria['presentation']['weight'];
    $feedback = array_merge($feedback, $img_analysis['feedback']);
    
    // Analyze description
    $desc_analysis = analyzeDescription($project['description']);
    $total_score += $desc_analysis['score'] * $grading_criteria['technical']['weight'];
    $feedback = array_merge($feedback, $desc_analysis['feedback']);
    
    // Calculate final grade
    $final_grade = round($total_score * 100);
    
    return [
        'grade' => $final_grade,
        'feedback' => $feedback
    ];
}

// Get ungraded projects
$stmt = $pdo->prepare("
    SELECT p.*, s.first_name, s.last_name, s.department
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ? AND p.id NOT IN (
        SELECT project_id FROM project_grades
    )
    ORDER BY p.submission_date DESC
");
$stmt->execute([$_SESSION['admin_id']]);
$ungraded_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process auto-grading if requested
if (isset($_POST['grade_project'])) {
    $project_id = $_POST['project_id'];
    
    // Get project details
    $stmt = $pdo->prepare("
        SELECT p.*, s.first_name, s.last_name, s.department
        FROM projects p
        JOIN students s ON p.student_id = s.id
        WHERE p.id = ? AND s.admin_id = ?
    ");
    $stmt->execute([$project_id, $_SESSION['admin_id']]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        try {
            $pdo->beginTransaction();
            
            // Grade the project
            $result = gradeProject($project, $pdo);
            
            // Store the grade in project_grades table
            $stmt = $pdo->prepare("
                INSERT INTO project_grades (project_id, grade, feedback, graded_by, graded_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $project_id,
                $result['grade'],
                implode("\n", $result['feedback']),
                $_SESSION['admin_id']
            ]);
            
            // Send notification to student
            sendNotification($project['student_id'], $project_id, $result['grade'], $pdo);
            
            $pdo->commit();
            $success = "Project graded successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error grading project: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-Grade Projects - InnoLearn</title>
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
                        <h2 class="card-title mb-4">Auto-Grade Projects</h2>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($ungraded_projects)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                No ungraded projects found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Department</th>
                                            <th>Project Title</th>
                                            <th>Category</th>
                                            <th>Submission Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ungraded_projects as $project): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($project['department']); ?></td>
                                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                                <td><?php echo htmlspecialchars($project['category']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($project['submission_date'])); ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                        <button type="submit" name="grade_project" class="btn btn-primary btn-sm">
                                                            <i class="bi bi-check-lg me-1"></i>Grade
                                                        </button>
                                                    </form>
                                                    <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="bi bi-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
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
<?php
require_once 'config/database.php';

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Get unique departments and categories
$dept_stmt = $pdo->query("SELECT DISTINCT department FROM students ORDER BY department");
$departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);

$cat_stmt = $pdo->query("SELECT DISTINCT category FROM nominations UNION SELECT DISTINCT category FROM projects ORDER BY category");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// Build search query based on filters
$students_query = "SELECT DISTINCT s.*, 
                    COUNT(DISTINCT n.id) as nomination_count,
                    COUNT(DISTINCT p.id) as project_count
                  FROM students s
                  LEFT JOIN nominations n ON s.id = n.student_id
                  LEFT JOIN projects p ON s.id = p.student_id
                  WHERE 1=1";

$projects_query = "SELECT p.*, s.first_name, s.last_name, s.student_id as student_number
                  FROM projects p
                  JOIN students s ON p.student_id = s.id
                  WHERE 1=1";

$nominations_query = "SELECT n.*, s.first_name, s.last_name, s.student_id as student_number
                     FROM nominations n
                     JOIN students s ON n.student_id = s.id
                     WHERE 1=1";

$params = [];

if ($search_term) {
    $search_term = "%$search_term%";
    $students_query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id LIKE ? OR s.email LIKE ?)";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
    
    $projects_query .= " AND (p.title LIKE ? OR p.description LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
    
    $nominations_query .= " AND (n.reason LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    array_push($params, $search_term, $search_term, $search_term);
}

if ($department) {
    $students_query .= " AND s.department = ?";
    $projects_query .= " AND s.department = ?";
    $nominations_query .= " AND s.department = ?";
    array_push($params, $department);
}

if ($category) {
    $projects_query .= " AND p.category = ?";
    $nominations_query .= " AND n.category = ?";
    array_push($params, $category);
}

$students_query .= " GROUP BY s.id ORDER BY s.first_name, s.last_name";
$projects_query .= " ORDER BY p.submission_date DESC";
$nominations_query .= " ORDER BY n.nomination_date DESC";

// Execute queries based on search type
$results = [];
if ($type === 'all' || $type === 'students') {
    $stmt = $pdo->prepare($students_query);
    $stmt->execute($params);
    $results['students'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($type === 'all' || $type === 'projects') {
    $stmt = $pdo->prepare($projects_query);
    $stmt->execute($params);
    $results['projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($type === 'all' || $type === 'nominations') {
    $stmt = $pdo->prepare($nominations_query);
    $stmt->execute($params);
    $results['nominations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">TopTrack</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="public_gallery.php">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Search TopTrack</h1>

        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="students" <?php echo $type === 'students' ? 'selected' : ''; ?>>Students</option>
                        <option value="projects" <?php echo $type === 'projects' ? 'selected' : ''; ?>>Projects</option>
                        <option value="nominations" <?php echo $type === 'nominations' ? 'selected' : ''; ?>>Nominations</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" 
                                    <?php echo $department === $dept ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <?php if (isset($results['students']) && ($type === 'all' || $type === 'students')): ?>
            <h2 class="mb-3">Students</h2>
            <div class="row g-4 mb-4">
                <?php foreach ($results['students'] as $student): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo htmlspecialchars($student['profile_image']); ?>" 
                                         class="rounded-circle me-3" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h5 class="card-title mb-0">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($student['student_id']); ?></small>
                                    </div>
                                </div>
                                <p class="card-text">
                                    <strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?><br>
                                    <strong>Nominations:</strong> <?php echo $student['nomination_count']; ?><br>
                                    <strong>Projects:</strong> <?php echo $student['project_count']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($results['projects']) && ($type === 'all' || $type === 'projects')): ?>
            <h2 class="mb-3">Projects</h2>
            <div class="row g-4 mb-4">
                <?php foreach ($results['projects'] as $project): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($project['thumbnail_path']); ?>" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($project['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($project['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        By <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                                    </small>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($project['category']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($results['nominations']) && ($type === 'all' || $type === 'nominations')): ?>
            <h2 class="mb-3">Nominations</h2>
            <div class="row">
                <?php foreach ($results['nominations'] as $nomination): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($nomination['first_name'] . ' ' . $nomination['last_name']); ?>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($nomination['category']); ?></span>
                                </h5>
                                <p class="card-text"><?php echo htmlspecialchars($nomination['reason']); ?></p>
                                <small class="text-muted">
                                    Nominated on <?php echo date('F j, Y', strtotime($nomination['nomination_date'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($results) || (
            (!isset($results['students']) || empty($results['students'])) && 
            (!isset($results['projects']) || empty($results['projects'])) && 
            (!isset($results['nominations']) || empty($results['nominations']))
        )): ?>
            <div class="alert alert-info">No results found.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
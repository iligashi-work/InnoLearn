<?php
require_once 'config/database.php';

// Get search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Get unique categories for the dropdown
$categories_query = "SELECT DISTINCT category FROM projects ORDER BY category";
$categories_stmt = $pdo->query($categories_query);
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch projects with student details
$projects_query = "SELECT p.*, s.first_name, s.last_name, s.student_id as student_number 
                  FROM projects p 
                  JOIN students s ON p.student_id = s.id 
                  WHERE 1=1";
$params = [];

if ($search) {
    $projects_query .= " AND (p.title LIKE ? OR p.description LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}

if ($category) {
    $projects_query .= " AND p.category = ?";
    array_push($params, $category);
}

$projects_query .= " ORDER BY p.submission_date DESC";
$projects_stmt = $pdo->prepare($projects_query);
$projects_stmt->execute($params);
$projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent nominations with student details
$nominations_query = "SELECT n.*, s.first_name, s.last_name, s.student_id as student_number 
                     FROM nominations n 
                     JOIN students s ON n.student_id = s.id 
                     ORDER BY n.nomination_date DESC 
                     LIMIT 10";
$nominations_stmt = $pdo->query($nominations_query);
$nominations = $nominations_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Gallery - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .modern-card{
            padding:20px;
        }
    </style>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(45deg, #2196F3, #00BCD4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .search-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e6ed;
            padding: 12px 20px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.15);
        }

        .btn-primary {
            background: linear-gradient(45deg, #2196F3, #00BCD4);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }

        .section-title {
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 50px;
            height: 4px;
            background: linear-gradient(45deg, #2196F3, #00BCD4);
            border-radius: 2px;
        }

        .project-card {
            background: white;
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .project-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .project-card:hover .overlay-hover {
            opacity: 1 !important;
        }

        .project-card .project-thumbnail {
            transition: transform 0.3s ease;
        }

        .project-card:hover .project-thumbnail {
            transform: scale(1.05);
        }

        .project-card .card-body {
            padding: 1.5rem;
        }

        .project-card .card-title {
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1a237e;
        }

        .badge {
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .badge-primary {
            background: linear-gradient(45deg, #2196F3, #00BCD4);
        }

        .badge-success {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
        }

        .achievement-card {
            background: white;
            border: none;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .achievement-card:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .list-group-item {
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 0.5rem;
            border-radius: 10px !important;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .rounded-pill {
            padding: 8px 15px;
        }

        footer {
            background: white !important;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
        }

        /* Loading animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.5s ease;
        }

        .loading-overlay.fade-out {
            opacity: 0;
            visibility: hidden;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #2196F3, #00BCD4);
            border-radius: 5px;
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: #2196F3;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading-overlay">
        <div class="spinner-grow text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">InnoLearn</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="public_gallery.php">
                            <i class="bi bi-grid me-1"></i> Gallery
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_login.php">
                            <i class="bi bi-mortarboard me-1"></i> Student Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="bi bi-shield-lock me-1"></i> Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Search Form -->
        <div class="card search-card mb-5" data-aos="fade-up">
            <div class="card-body p-4">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Search projects..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
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
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <h2 class="section-title" data-aos="fade-right">Featured Projects</h2>
        <?php if (empty($projects)): ?>
            <div class="empty-state" data-aos="fade-up">
                <i class="bi bi-search"></i>
                <h3>No Projects Found</h3>
                <p class="text-muted">We couldn't find any projects matching your search criteria.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($projects as $index => $project): ?>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="project-card">
                            <div class="position-relative overflow-hidden">
                                <img src="<?php echo htmlspecialchars($project['thumbnail_path']); ?>" 
                                     class="project-thumbnail w-100" 
                                     alt="Project Thumbnail">
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge badge-primary">
                                        <i class="bi bi-bookmark-star me-1"></i>
                                        <?php echo htmlspecialchars($project['category']); ?>
                                    </span>
                                </div>
                                <!-- Add overlay with view button -->
                                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center overlay-hover" style="background: rgba(0,0,0,0.5); opacity: 0; transition: opacity 0.3s;">
                                    <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-light">
                                        <i class="bi bi-eye me-2"></i>View Details
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="view_project.php?id=<?php echo $project['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($project['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex align-items-center mt-3">
                                    <div class="flex-grow-1">
                                        <small class="text-muted">By</small>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></h6>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo date('M d, Y', strtotime($project['submission_date'])); ?>
                                    </small>
                                </div>
                                <div class="mt-3">
                                    <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-eye me-2"></i>View Project Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title mt-5" data-aos="fade-right">Recent Achievements</h2>
        <div class="row">
            <div class="col-md-8">
                <?php foreach ($nominations as $index => $nomination): ?>
                    <div class="achievement-card" data-aos="fade-right" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-0">
                                        <?php echo htmlspecialchars($nomination['first_name'] . ' ' . $nomination['last_name']); ?>
                                    </h5>
                                    <small class="text-muted">Student ID: <?php echo htmlspecialchars($nomination['student_number']); ?></small>
                                </div>
                                <span class="badge badge-success">
                                    <i class="bi bi-trophy me-1"></i>
                                    <?php echo htmlspecialchars($nomination['category']); ?>
                                </span>
                            </div>
                            <p class="card-text"><?php echo htmlspecialchars($nomination['reason']); ?></p>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo date('F j, Y', strtotime($nomination['nomination_date'])); ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4" data-aos="fade-left">
                <div class="stats-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Achievement Categories</h5>
                        <ul class="list-group list-group-flush">
                            <?php
                            $categories = [
                                'Academic Excellence' => 'bi-mortarboard',
                                'Leadership' => 'bi-star',
                                'Community Service' => 'bi-people',
                                'Sports Achievement' => 'bi-trophy',
                                'Innovation' => 'bi-lightbulb'
                            ];
                            
                            foreach ($categories as $cat => $icon):
                                $stmt = $pdo->query("SELECT COUNT(*) FROM nominations WHERE category = " . $pdo->quote($cat));
                                $count = $stmt->fetchColumn();
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi <?php echo $icon; ?> me-2"></i>
                                        <?php echo $cat; ?>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $count; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="bi bi-stars me-2"></i>
                InnoLearn - Showcasing Student Excellence
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Loading animation
        window.addEventListener('load', function() {
            document.querySelector('.loading-overlay').classList.add('fade-out');
        });
    </script>
</body>
</html> 
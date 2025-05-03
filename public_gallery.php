<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Gallery - TopTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            <a class="navbar-brand" href="index.php">TopTrack</a>
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
                TopTrack - Showcasing Student Excellence
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
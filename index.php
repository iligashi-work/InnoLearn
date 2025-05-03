<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopTrack - Student Excellence Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
    <!-- Loading Animation -->
    <div class="loading-overlay">
        <div class="spinner-grow text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-stars me-2"></i>TopTrack
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="public_gallery.php">
                            <i class="bi bi-grid me-1"></i>Gallery
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_login.php">
                            <i class="bi bi-mortarboard me-1"></i>Student Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">
                            <i class="bi bi-shield-lock me-1"></i>Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="hero-title">Celebrating Student Excellence</h1>
                    <p class="hero-subtitle">Track, manage, and showcase outstanding student achievements and projects in one comprehensive platform.</p>
                    <div class="d-flex gap-3">
                        <a href="public_gallery.php" class="btn cta-button primary">
                            <i class="bi bi-collection-play me-2"></i>Explore Projects
                        </a>
                        <a href="student_login.php" class="btn cta-button secondary">
                            <i class="bi bi-person-plus me-2"></i>Join Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo number_format($stats['students']); ?>+</div>
                                <div class="stats-label">Active Students</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo number_format($stats['projects']); ?>+</div>
                                <div class="stats-label">Projects Submitted</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo number_format($stats['nominations']); ?>+</div>
                                <div class="stats-label">Excellence Nominations</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Why Choose TopTrack?</h2>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <i class="bi bi-trophy feature-icon"></i>
                        <h3>Excellence Recognition</h3>
                        <p>Celebrate and showcase outstanding academic achievements, projects, and contributions to the community.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="bi bi-graph-up feature-icon"></i>
                        <h3>Progress Tracking</h3>
                        <p>Monitor your academic journey, track project milestones, and visualize your growth over time.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <i class="bi bi-people feature-icon"></i>
                        <h3>Community Building</h3>
                        <p>Connect with peers, share knowledge, and build a supportive academic community.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Projects -->
    <?php if (!empty($featured_projects)): ?>
    <section class="featured-projects">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Featured Projects</h2>
            <div class="row">
                <?php foreach ($featured_projects as $project): ?>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="project-card">
                        <div class="project-image">
                            <i class="bi bi-laptop"></i>
                        </div>
                        <div class="project-content">
                            <h5><?php echo htmlspecialchars($project['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-person me-2"></i>
                                <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                            </p>
                            <span class="badge bg-primary">
                                <i class="bi bi-tag me-1"></i>
                                <?php echo htmlspecialchars($project['category']); ?>
                            </span>
                            <p class="mt-3"><?php echo substr(htmlspecialchars($project['description']), 0, 100) . '...'; ?></p>
                            <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-outline-primary btn-sm">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4" data-aos="fade-up">
                <a href="public_gallery.php" class="btn btn-primary cta-button">
                    View All Projects
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h2 class="mb-4">Ready to Showcase Your Excellence?</h2>
                    <p class="mb-4">Join TopTrack today and become part of a thriving community of achievers.</p>
                    <a href="student_login.php" class="btn cta-button primary">
                        <i class="bi bi-arrow-right-circle me-2"></i>Get Started
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 bg-light">
        <div class="container text-center">
            <p class="text-muted mb-0">
                <i class="bi bi-stars me-2"></i>
                TopTrack - Student Excellence Management System
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
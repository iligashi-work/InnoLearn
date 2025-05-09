<?php
session_start();
require_once 'config/database.php';

$stats = getStats(); // Ensure this is called before you use $stats in HTML

function getStats() {
    global $pdo; // Use the correct variable for the database connection

    if ($pdo === null) {
        die("Database connection is not established.");
    }
    
    $stats = [];
    
    // Query to fetch the count of students
    $result = $pdo->query("SELECT COUNT(*) AS students FROM students");
    if ($result) {
        $stats['students'] = $result->fetch(PDO::FETCH_ASSOC)['students'];
    }

    // Query to fetch the count of projects
    $result = $pdo->query("SELECT COUNT(*) AS projects FROM projects");
    if ($result) {
        $stats['projects'] = $result->fetch(PDO::FETCH_ASSOC)['projects'];
    }

    // Query to fetch the count of nominations
    $result = $pdo->query("SELECT COUNT(*) AS nominations FROM nominations");
    if ($result) {
        $stats['nominations'] = $result->fetch(PDO::FETCH_ASSOC)['nominations'];
    }

    return $stats;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InnoLearn - Student Excellence Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="style.css">
    <style>
    .hero-section {
        background: linear-gradient(135deg, #19C7E6, #8f94fb);
        color: white;
        padding: 135px 80px 80px; /* Adjusted padding for better spacing */
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0.1;
    }

    .hero-title {
        font-size: 3rem; /* Adjusted for better responsiveness */
        font-weight: 800;
        margin-bottom: 1.5rem;
        line-height: 1.2;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2rem;
        font-weight: 300;
    }

    .feature-card {
        background: white;
        border-radius: 15px;
        padding: 20px; /* Adjusted padding for consistency */
        margin-bottom: 30px; /* Added margin for spacing */
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 2.5rem;
        color: #19C7E6;
        margin-bottom: 15px;
    }

    .stats-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 20px; /* Adjusted padding */
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 20px; /* Added margin for spacing */
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #fff, #f0f0f0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .cta-button {
        padding: 10px 30px; /* Adjusted padding for better button size */
        font-size: 1rem; /* Adjusted font size */
        font-weight: 600;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }

    .cta-button.primary {
        background: #fff;
        color: #19C7E6;
    }

    .cta-button.primary:hover {
        background: #f0f0f0;
        transform: translateY(-2px);
    }

    .cta-button.secondary {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .cta-button.secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .featured-projects {
        padding: 60px 20px; /* Adjusted padding for better spacing */
        background: #f8f9fa;
    }

    .project-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
        margin-bottom: 30px; /* Added margin for spacing */
    }

    .project-card:hover {
        transform: translateY(-5px);
    }

    .project-image {
        height: 200px;
        background: #19C7E6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    .project-content {
        padding: 20px; /* Adjusted padding for consistency */
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 40px; /* Adjusted margin for better spacing */
        text-align: center;
    }

    .navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 10px 20px; /* Adjusted padding for consistency */
    }

    .navbar-brand {
        font-size: 1.5rem;
        font-weight: 700;
        color: #19C7E6;
    }

    .nav-link {
        font-weight: 500;
        padding: 10px 15px; /* Adjusted padding for better spacing */
        margin: 0 5px; /* Adjusted margin for consistency */
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        background: rgba(78, 84, 200, 0.1);
        color: #19C7E6;
    }

    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
        }

        .stats-card {
            margin-bottom: 15px; /* Adjusted margin for smaller screens */
        }
    }

    .modern-card {
        padding: 20px;
    }
</style>
    </style>
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
                <img src="logo.png" width="75">InnoLearn
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
            <h2 class="section-title" data-aos="fade-up">Why Choose InnoLearn?</h2>
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
                    <p class="mb-4">Join InnoLearn today and become part of a thriving community of achievers.</p>
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
                InnoLearn - Student Excellence Management System
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
<?php
require_once 'config/database.php';

// Get top students based on nominations
$nominations_query = "SELECT 
    s.id,
    s.student_id,
    s.first_name,
    s.last_name,
    s.department,
    s.profile_image,
    COUNT(n.id) as nomination_count,
    GROUP_CONCAT(DISTINCT n.category) as achievements
FROM students s
LEFT JOIN nominations n ON s.id = n.student_id
GROUP BY s.id
ORDER BY nomination_count DESC, s.first_name
LIMIT 10";

$nominations_stmt = $pdo->query($nominations_query);
$top_students = $nominations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get project statistics
$project_query = "SELECT 
    s.id,
    COUNT(p.id) as project_count
FROM students s
LEFT JOIN projects p ON s.id = p.student_id
GROUP BY s.id";

$project_stmt = $pdo->query($project_query);
$project_stats = [];
while ($row = $project_stmt->fetch(PDO::FETCH_ASSOC)) {
    $project_stats[$row['id']] = $row['project_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .student-card {
            transition: transform 0.2s;
        }
        .student-card:hover {
            transform: translateY(-5px);
        }
        .achievement-badge {
            font-size: 0.8rem;
            margin: 2px;
        }
        .rank-badge {
            position: absolute;
            top: -10px;
            left: -10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #FFD700, #FFA500);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .profile-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .modern-card{
            padding:20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">InnoLearn</a>
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
        <div class="text-center mb-5">
            <h1 class="display-4">Student Excellence Leaderboard</h1>
            <p class="lead text-muted">Celebrating our top achievers and their accomplishments</p>
        </div>

        <div class="row g-4">
            <?php 
            $rank = 1;
            foreach ($top_students as $student): 
                $achievements = explode(',', $student['achievements']);
                $achievements = array_unique(array_filter($achievements));
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card student-card position-relative">
                        <div class="rank-badge">
                            <?php echo $rank++; ?>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo htmlspecialchars($student['profile_image']); ?>" 
                                 alt="Profile" 
                                 class="profile-image mb-3">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                            </h5>
                            <p class="text-muted mb-2">
                                <?php echo htmlspecialchars($student['department']); ?> | 
                                ID: <?php echo htmlspecialchars($student['student_id']); ?>
                            </p>
                            <div class="d-flex justify-content-center gap-3 mb-3">
                                <div class="text-center">
                                    <h4 class="mb-0"><?php echo $student['nomination_count']; ?></h4>
                                    <small class="text-muted">Nominations</small>
                                </div>
                                <div class="text-center">
                                    <h4 class="mb-0"><?php echo $project_stats[$student['id']] ?? 0; ?></h4>
                                    <small class="text-muted">Projects</small>
                                </div>
                            </div>
                            <div class="achievements">
                                <?php foreach ($achievements as $achievement): ?>
                                    <span class="badge bg-success achievement-badge">
                                        <?php echo htmlspecialchars($achievement); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <p class="text-muted">
                Rankings are based on nominations, project submissions, and overall achievements.
                <br>Updated in real-time as new nominations and projects are added.
            </p>
        </div>
    </div>

    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="text-muted mb-0">InnoLearn - Showcasing Student Excellence</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
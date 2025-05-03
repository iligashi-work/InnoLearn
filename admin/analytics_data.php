<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$admin_id = $_SESSION['admin_id'];

// Fetch all statistics
$stats = [];

// Department stats
$stmt = $pdo->prepare("
    SELECT department, COUNT(*) as count
    FROM students
    WHERE admin_id = ?
    GROUP BY department
    ORDER BY count DESC
");
$stmt->execute([$admin_id]);
$stats['department_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Project stats
$stmt = $pdo->prepare("
    SELECT p.category, COUNT(*) as count
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY p.category
    ORDER BY count DESC
");
$stmt->execute([$admin_id]);
$stats['project_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nomination stats
$stmt = $pdo->prepare("
    SELECT n.category, COUNT(*) as count
    FROM nominations n
    JOIN students s ON n.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY n.category
    ORDER BY count DESC
");
$stmt->execute([$admin_id]);
$stats['nomination_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly projects
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(p.submission_date, '%Y-%m') as month,
           COUNT(*) as count
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute([$admin_id]);
$stats['monthly_projects'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Monthly nominations
$stmt = $pdo->prepare("
    SELECT DATE_FORMAT(n.nomination_date, '%Y-%m') as month,
           COUNT(*) as count
    FROM nominations n
    JOIN students s ON n.student_id = s.id
    WHERE s.admin_id = ?
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");
$stmt->execute([$admin_id]);
$stats['monthly_nominations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($stats); 
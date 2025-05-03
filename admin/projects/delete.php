<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get admin ID
$admin_id = $_SESSION['admin_id'];

// Get project ID from URL
$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify that the project belongs to the admin's students
$stmt = $pdo->prepare("
    SELECT p.id 
    FROM projects p
    JOIN students s ON p.student_id = s.id
    WHERE p.id = ? AND s.admin_id = ?
");
$stmt->execute([$project_id, $admin_id]);

if (!$stmt->fetch()) {
    header('Location: list.php?error=1');
    exit();
}

// Delete the project
$stmt = $pdo->prepare("
    DELETE FROM projects 
    WHERE id = ? AND student_id IN (
        SELECT id FROM students WHERE admin_id = ?
    )
");

if ($stmt->execute([$project_id, $admin_id])) {
    header('Location: list.php?success=1');
} else {
    header('Location: list.php?error=2');
}
exit(); 
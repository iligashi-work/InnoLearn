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

// Get student ID from URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if student exists and belongs to the admin
$stmt = $pdo->prepare("
    SELECT id FROM students 
    WHERE id = ? AND admin_id = ?
");
$stmt->execute([$student_id, $admin_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: list.php?error=1');
    exit();
}

// Delete student and their associated data
try {
    $pdo->beginTransaction();

    // Delete student's nominations
    $pdo->prepare("
        DELETE FROM nominations 
        WHERE student_id = ?
    ")->execute([$student_id]);

    // Delete student's projects
    $pdo->prepare("
        DELETE FROM projects 
        WHERE student_id = ?
    ")->execute([$student_id]);

    // Delete student
    $pdo->prepare("
        DELETE FROM students 
        WHERE id = ? AND admin_id = ?
    ")->execute([$student_id, $admin_id]);

    $pdo->commit();
    header('Location: list.php?success=1');
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: list.php?error=2');
    exit();
} 
<?php
session_start();
require_once '../../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../../login.php');
    exit();
}

// Check if nomination ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid nomination ID";
    header('Location: list.php');
    exit();
}

$nomination_id = $_GET['id'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // First, check if the nomination exists and belongs to the admin's students
    $stmt = $pdo->prepare("
        SELECT n.id 
        FROM nominations n
        JOIN students s ON n.student_id = s.id
        WHERE n.id = ? AND s.admin_id = ?
    ");
    $stmt->execute([$nomination_id, $_SESSION['admin_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Nomination not found or you don't have permission to delete it");
    }

    // Delete the nomination
    $stmt = $pdo->prepare("DELETE FROM nominations WHERE id = ?");
    $stmt->execute([$nomination_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Nomination deleted successfully";
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting nomination: " . $e->getMessage();
}

// Redirect back to nominations list
header('Location: list.php');
exit(); 
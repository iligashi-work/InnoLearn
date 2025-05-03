<?php
require_once '../config/database.php';

try {
    // Hash the password
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the admin password
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
    $result = $stmt->execute([$hashed_password, 'admin']);
    
    if ($result) {
        echo "Password updated successfully! You can now login with:<br>";
        echo "Username: admin<br>";
        echo "Password: admin123";
    } else {
        echo "Failed to update password.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
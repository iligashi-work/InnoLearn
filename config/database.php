<?php
$host = 'localhost';
$dbname = 'innolearn_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test the connection
    $test = $pdo->query("SELECT 1");
    if ($test) {
        // Uncomment the next line to verify connection
        // echo "Database connection successful!";
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?> 
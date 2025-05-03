<?php
$host = 'localhost';
$dbname = 'innolearn_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $test = $pdo->query("SELECT 1");
    if ($test) {
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
?> 
<?php
session_start();
require_once 'config/database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $student_id = $_POST['student_id'];
    $email = $_POST['email'];

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    try{
        echo "Attempting login with:<br>";
        echo "Student ID: " . htmlspecialchars($student_id) . "<br>";
        echo "Email: " . htmlspecialchars($email) . "<br>";

        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? AND email = ? ");
        $stmt->execute([$student_id, $email]);

        echo "Query executed.<br>";

        $student = $stmt->fetch();
        
        // Debug: Print the result
        echo "Query result:<br>";
        var_dump($student);
        
        if ($student) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_number'] = $student['student_id'];
            $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
            $_SESSION['student_email'] = $student['email'];
            $_SESSION['student_department'] = $student['department'];
            header('Location: student_dashboard.php');
            exit();
        } else {
            $error_message = "Invalid student ID or email";
            
            // Debug: Check database contents
            echo "<br>Checking database contents:<br>";
            $check_stmt = $pdo->query("SELECT student_id, email FROM students");
            $all_students = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "All students in database:<br>";
            var_dump($all_students);
        }
    } catch (PDOException $e){
        echo "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Login - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
</body>
</html>
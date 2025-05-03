<?php

function sendNominationEmail($student_email, $student_name, $category, $reason) {
    $to = $student_email;
    $subject = "Congratulations! You've Been Nominated for " . $category;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f8f9fa; }
            .footer { text-align: center; padding: 20px; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Congratulations, $student_name!</h1>
            </div>
            <div class='content'>
                <p>We are pleased to inform you that you have been nominated for excellence in <strong>$category</strong>.</p>
                
                <h3>Nomination Details:</h3>
                <p>$reason</p>
                
                <p>This recognition reflects your outstanding contributions and achievements. Keep up the excellent work!</p>
                
                <p>You can view your nomination and other achievements on the InnoLearn platform.</p>
            </div>
            <div class='footer'>
                <p>This is an automated message from InnoLearn - Student Excellence Management System</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: InnoLearn <noreply@InnoLearn.edu>' . "\r\n";
    
    return mail($to, $subject, $message, $headers);
} 
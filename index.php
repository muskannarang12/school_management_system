<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // Redirect based on the role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['role'] == 'teacher') {
        header("Location: teacher_dashboard.php");
    } elseif ($_SESSION['role'] == 'student') {
        header("Location: student_dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:  #0056b3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            
        }
        .container {
            background-color: #fff;
            padding: 40px;
            border: 10px solid black;
            box-shadow: 0 2px 10px rgba(1, 0, 0, 0.1);
            text-align: center;
        }
        .container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .container a {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .container a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to School Management System</h1>
        <a href="login.php">Login</a>
        <a href="registration.php">Register</a>
    </div>
</body>
</html>

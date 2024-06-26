<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit;
}

include 'db.php'; // Include database connection

// Fetch data
$studentCount = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$teacherCount = $conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'];
$classCount = $conn->query("SELECT COUNT(*) AS count FROM classes")->fetch_assoc()['count'];

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .dashboard {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 80%;
            max-width: 600px;
            text-align: center;
        }
        .dashboard h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .dashboard p {
            font-size: 18px;
            margin: 10px 0;
        }
        .dashboard a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .dashboard a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Admin Dashboard</h1>
        <p>Total Students: <?php echo $studentCount; ?></p>
        <p>Total Teachers: <?php echo $teacherCount; ?></p>
        <p>Total Classes: <?php echo $classCount; ?></p>
        <a href="out.php">Logout</a>
    </div>
</body>
</html>

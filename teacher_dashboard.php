
<?php
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.html");
    exit;
}

include 'db.php'; // Include database connection

// Fetch classes managed by the teacher
$teacherId = $_SESSION['user_id'];
$classesQuery = $conn->query("SELECT id, name FROM classes WHERE teacher_id = $teacherId");

$classes = [];
while ($class = $classesQuery->fetch_assoc()) {
    $classId = $class['id'];
    $studentsQuery = $conn->query("SELECT first_name, last_name FROM students WHERE class_id = $classId");
    $students = [];
    while ($student = $studentsQuery->fetch_assoc()) {
        $students[] = $student;
    }
    $classes[] = ['class' => $class, 'students' => $students];
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
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
        }
        .dashboard h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }
        .class {
            margin-bottom: 20px;
        }
        .class h2 {
            font-size: 20px;
            color: #007bff;
        }
        .students {
            margin-left: 20px;
        }
        .students p {
            margin: 5px 0;
        }
        .dashboard a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .dashboard a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Teacher Dashboard</h1>
        <?php foreach ($classes as $classData): ?>
            <div class="class">
                <h2><?php echo $classData['class']['name']; ?></h2>
                <div class="students">
                    <?php foreach ($classData['students'] as $student): ?>
                        <p><?php echo $student['first_name'] . " " . $student['last_name']; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <a href="out.php">Logout</a>
    </div>
</body>
</html>

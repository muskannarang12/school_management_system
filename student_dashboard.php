<?php
session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.html");
    exit;
}

include 'db.php'; // Include database connection

// Fetch student's class
$studentId = $_SESSION['user_id'];
$classQuery = $conn->prepare("SELECT c.name FROM classes c INNER JOIN students s ON c.id = s.class_id WHERE s.id = ?");
$classQuery->bind_param("i", $studentId);
$classQuery->execute();
$classResult = $classQuery->get_result();
$class = $classResult->fetch_assoc();

// Check if class is found
if (!$class) {
    echo "Class not found for student ID: $studentId";
    exit;
}

// Fetch attendance records
$attendanceQuery = $conn->prepare("SELECT date, status FROM attendance WHERE student_id = ?");
$attendanceQuery->bind_param("i", $studentId);
$attendanceQuery->execute();
$attendanceResult = $attendanceQuery->get_result();

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
        .class, .attendance {
            margin-bottom: 20px;
        }
        .class h2, .attendance h2 {
            font-size: 20px;
            color: #007bff;
        }
        .attendance table {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance th, .attendance td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .attendance th {
            background-color: #f2f2f2;
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
        <h1>Student Dashboard</h1>
        <div class="class">
            <h2>Class</h2>
            <p><?php echo htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="attendance">
            <h2>Attendance</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php while ($attendance = $attendanceResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($attendance['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($attendance['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <a href="out.php">Logout</a>
    </div>
</body>
</html>

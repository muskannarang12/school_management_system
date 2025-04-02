<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect based on role
if ($_SESSION['role'] == 'admin') {
    header("Location: admin_dashboard.php");
    exit;
} elseif ($_SESSION['role'] == 'teacher') {
    header("Location: teacher_dashboard.php");
    exit;
}

include 'db.php';

// Get student information
$student_query = $conn->prepare("
    SELECT s.*, c.name as class_name, u.username 
    FROM students s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE u.id = ?
");
$student_query->bind_param("i", $_SESSION['user_id']);
$student_query->execute();
$student_result = $student_query->get_result();

if ($student_result->num_rows == 0) {
    header("Location: login.php");
    exit;
}

$student = $student_result->fetch_assoc();
$student_id = $student['id'];

// Get student grades with proper date handling
$grades = $conn->query("
    SELECT g.*, c.name as class_name 
    FROM grades g
    JOIN classes c ON g.class_id = c.id
    WHERE g.student_id = $student_id
    ORDER BY g.created_at DESC
");

// Get student attendance (simplified without class association)
$attendance = $conn->query("
    SELECT a.* 
    FROM attendance a
    WHERE a.student_id = $student_id
    ORDER BY a.date DESC
");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .card-title {
            margin: 0;
            font-size: 1.2rem;
            color: #4361ee;
        }
        .badge-attendance {
            background-color: #4bb543;
            color: white;
        }
        .badge-absent {
            background-color: #dc3545;
            color: white;
        }
        .badge-late {
            background-color: #ffc107;
            color: #212529;
        }
        .table th {
            border-top: none;
        }
        .student-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="student-info">
            <h2>Welcome, <?php echo htmlspecialchars($student['first_name']); ?></h2>
            <p>Class: <?php echo htmlspecialchars($student['class_name'] ?? 'Not assigned'); ?></p>
        </div>

        <!-- Grades Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">My Grades</h3>
            </div>
            
            <?php if ($grades->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Grade</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($grade = $grades->fetch_assoc()): 
                                // Determine which date field to use
                                $date = isset($grade['date']) ? $grade['date'] : 
                                       (isset($grade['created_at']) ? $grade['created_at'] : 'N/A');
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($grade['grade'] >= 70) ? 'success' : (($grade['grade'] >= 50) ? 'warning' : 'danger'); ?>">
                                        <?php echo htmlspecialchars($grade['grade']); ?>%
                                    </span>
                                </td>
                                <td><?php echo $date != 'N/A' ? date('M j, Y', strtotime($date)) : $date; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No grades recorded yet</div>
            <?php endif; ?>
        </div>

        <!-- Attendance Card -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">My Attendance</h3>
            </div>
            
            <?php if ($attendance->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($record = $attendance->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                <td>
                                    <span class="badge <?php echo ($record['status'] == 'present') ? 'badge-attendance' : (($record['status'] == 'late') ? 'badge-late' : 'badge-absent'); ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No attendance records yet</div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-3">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Fetch counts
$studentCount = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$teacherCount = $conn->query("SELECT COUNT(*) AS count FROM teachers")->fetch_assoc()['count'];
$classCount = $conn->query("SELECT COUNT(*) AS count FROM classes")->fetch_assoc()['count'];

// Fetch lists with user account information
$teachers = $conn->query("
    SELECT t.*, u.username as user_account 
    FROM teachers t 
    LEFT JOIN users u ON t.user_id = u.id
");

$students = $conn->query("
    SELECT s.*, c.name as class_name, u.username as login_username 
    FROM students s 
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN users u ON s.user_id = u.id
");

$classes = $conn->query("SELECT c.*, t.first_name as teacher_first, t.last_name as teacher_last FROM classes c LEFT JOIN teachers t ON c.teacher_id = t.id");

// Get unassigned teacher accounts (users with teacher role but no teacher profile)
$unassigned_teacher_accounts = $conn->query("
    SELECT u.id, u.username 
    FROM users u 
    LEFT JOIN teachers t ON u.id = t.user_id 
    WHERE t.id IS NULL AND u.role = 'teacher'
");

// Corrected grades query
$grades = $conn->query("SELECT g.*, s.first_name as student_first, s.last_name as student_last, c.name as class_name FROM grades g JOIN students s ON g.student_id = s.id JOIN classes c ON g.class_id = c.id ORDER BY g.created_at DESC LIMIT 20");

$attendance = $conn->query("SELECT a.*, s.first_name as student_first, s.last_name as student_last, c.name as class_name FROM attendance a JOIN students s ON a.student_id = s.id JOIN classes c ON s.class_id = c.id ORDER BY a.date DESC LIMIT 20");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #0056b3;
            color: white;
            padding: 15px 20px;
            text-align: center;
            position: relative;
        }
        .nav {
            background-color: #333;
            padding: 10px;
            text-align: center;
            justify-content : center;
            align-items : center;
            
        }
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            display: inline-block;
        }
        .nav a:hover {
            background-color: #0056b3;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .stat-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 15px;
            text-align: center;
            min-width: 200px;
            flex: 1;
        }
        .logout-btn {
            color: white;
            text-decoration: none;
            position: absolute;
            right: 20px;
            top: 40px;
            Font-weight : bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .user-account {
            font-size: 0.8em;
            color: #666;
        }
        .unlinked {
            color: #dc3545;
            font-weight: bold;
        }
        .info-box {
            background-color: #e7f3fe;
            border-left: 6px solid #2196F3;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .login-credentials {
            font-size: 0.8em;
            color: #28a745;
            font-weight: bold;
        }
        .no-credentials {
            font-size: 0.8em;
            color: #dc3545;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>School Management System</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="nav">
        <a href="#" onclick="showTab('dashboard')">Dashboard</a>
        <a href="#" onclick="showTab('teachers')">Manage Teachers</a>
        <a href="#" onclick="showTab('students')">Manage Students</a>
        <a href="#" onclick="showTab('classes')">Manage Classes</a>
        <a href="#" onclick="showTab('grades')">View Grades</a>
        <a href="#" onclick="showTab('attendance')">View Attendance</a>
    </div>

    <div class="container">
        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <p><?php echo $studentCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Teachers</h3>
                    <p><?php echo $teacherCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Classes</h3>
                    <p><?php echo $classCount; ?></p>
                </div>
            </div>
            
            <!-- Teacher Account Information -->
            <div class="info-box">
                <strong>Teacher Account Policy:</strong> Before adding a teacher profile, the teacher must first have a user account with the 'teacher' role. 
                Create teacher accounts through the user management system first, then you can create their profiles here.
            </div>
            
            <!-- Unassigned Teacher Accounts -->
            <?php if ($unassigned_teacher_accounts->num_rows > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Unassigned Teacher Accounts</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $unassigned_teacher_accounts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <a href="add_teacher.php?user_id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Create Teacher Profile</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="info-box">
                No unassigned teacher accounts found. To add a new teacher, first create a user account with the 'teacher' role.
            </div>
            <?php endif; ?>
        </div>

        <!-- Teachers Tab -->
        <div id="teachers" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2>Manage Teachers</h2>
                    <a href="add_teacher.php" class="btn btn-primary">Add Teacher Profile</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>User Account</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($teacher = $teachers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $teacher['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                            </td>
                            <td>
                                <?php if ($teacher['user_account']): ?>
                                    <span class="user-account"><?php echo htmlspecialchars($teacher['user_account']); ?></span>
                                <?php else: ?>
                                    <span class="unlinked">Not linked</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                            <td>
                                <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                <?php if (!$teacher['user_account']): ?>
                                    <a href="link_teacher.php?id=<?php echo $teacher['id']; ?>" class="btn btn-sm" style="background-color: #28a745; color: white;">Link Account</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Students Tab -->
        <div id="students" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2>Manage Students</h2>
                    <a href="add_student.php" class="btn btn-primary">Add Student</a>
                </div>
                <?php 
                $classes->data_seek(0);
                while($class = $classes->fetch_assoc()): 
                ?>
                <h3>Class <?php echo $class['name']; ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Login Credentials</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $students->data_seek(0);
                        while($student = $students->fetch_assoc()): 
                            if($student['class_id'] == $class['id']):
                        ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></td>
                            <td>
                                <?php if ($student['login_username']): ?>
                                    <span class="login-credentials">Username: <?php echo htmlspecialchars($student['login_username']); ?></span>
                                <?php else: ?>
                                    <span class="no-credentials">No login credentials</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['phone']; ?></td>
                            <td>
                                <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                <?php if (!$student['login_username']): ?>
                                    <a href="assign_cred.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm" style="background-color: #28a745; color: white;">Assign Credentials</a>
                                <?php else: ?>
                                    <a href="reset_password.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm" style="background-color: #ffc107; color: black;">Reset Password</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            endif;
                        endwhile; 
                        ?>
                    </tbody>
                </table>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Classes Tab -->
        <div id="classes" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2>Manage Classes</h2>
                    <a href="add_class.php" class="btn btn-primary">Add Class</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Class Name</th>
                            <th>Teacher</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $classes->data_seek(0);
                        while($class = $classes->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $class['id']; ?></td>
                            <td><?php echo $class['name']; ?></td>
                            <td>
                                <?php 
                                if($class['teacher_first']) {
                                    echo $class['teacher_first'] . ' ' . $class['teacher_last'];
                                } else {
                                    echo 'Not assigned';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="edit_class.php?id=<?php echo $class['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_class.php?id=<?php echo $class['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Grades Tab -->
        <div id="grades" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2>Recent Grades</h2>
                    <a href="all_grades.php" class="btn btn-primary">View All Grades</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Grade</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($grade = $grades->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($grade['student_first'] . ' ' . $grade['student_last']); ?></td>
                            <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                            <td>
                                <?php 
                                // Use created_at if available, otherwise show "N/A"
                                $date = !empty($grade['created_at']) ? $grade['created_at'] : (isset($grade['updated_at']) ? $grade['updated_at'] : null);
                                echo $date ? date('d/m/Y', strtotime($date)) : 'N/A';
                                ?>
                            </td>
                            <td>
                                <a href="edit_grade.php?id=<?php echo $grade['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_grade.php?id=<?php echo $grade['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Attendance Tab -->
        <div id="attendance" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2>Recent Attendance</h2>
                    <a href="all_attendance.php" class="btn btn-primary">View All Attendance</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($record = $attendance->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $record['student_first'] . ' ' . $record['student_last']; ?></td>
                            <td><?php echo $record['class_name']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($record['date'])); ?></td>
                            <td><?php echo ucfirst($record['status']); ?></td>
                            <td>
                                <a href="edit_attendance.php?id=<?php echo $record['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_attendance.php?id=<?php echo $record['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Update URL without reloading
            history.pushState(null, null, '#' + tabId);
        }
        
        // Check URL hash on page load
        window.addEventListener('load', function() {
            if(window.location.hash) {
                const tabId = window.location.hash.substring(1);
                showTab(tabId);
            }
        });
    </script>
</body>
</html>
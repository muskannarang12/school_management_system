<?php
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id'])) {
    header("Location:login.php");
    exit;
}

include 'db.php';

// Get teacher ID based on logged-in user
$teacher_id = null;
$teacher_query = $conn->prepare("SELECT id FROM teachers WHERE user_id = ?");
$teacher_query->bind_param("i", $_SESSION['user_id']);
$teacher_query->execute();
$teacher_result = $teacher_query->get_result();

if ($teacher_result->num_rows > 0) {
    $teacher = $teacher_result->fetch_assoc();
    $teacher_id = $teacher['id'];
} else {
    header("Location:login.php");
    exit;
}

// Get classes assigned to this teacher
$classes = $conn->query("SELECT id, name FROM classes WHERE teacher_id = $teacher_id");

// Handle viewing students for a specific class
$class_students = null;
if (isset($_GET['view_students'])) {
    $class_id = intval($_GET['view_students']);
    $class_students = $conn->query("
        SELECT s.id, s.first_name, s.last_name 
        FROM students s 
        WHERE s.class_id = $class_id
        ORDER BY s.last_name, s.first_name
    ");
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mark_attendance'])) {
        $class_id = $_POST['class_id'];
        $date = $_POST['date'];
        
        if (isset($_POST['attendance']) && is_array($_POST['attendance'])) {
            foreach ($_POST['attendance'] as $student_id => $status) {
                $student_id = intval($student_id);
                $status = $conn->real_escape_string($status);
                
                $check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
                $check->bind_param("is", $student_id, $date);
                $check->execute();
                $check_result = $check->get_result();
                
                if ($check_result->num_rows > 0) {
                    $update = $conn->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND date = ?");
                    $update->bind_param("sis", $status, $student_id, $date);
                    $update->execute();
                } else {
                    $insert = $conn->prepare("INSERT INTO attendance (student_id, date, status, recorded_at, recorded_by) VALUES (?, ?, ?, NOW(), ?)");
                    $insert->bind_param("issi", $student_id, $date, $status, $teacher_id);
                    $insert->execute();
                }
            }
            
            $_SESSION['message'] = "Attendance marked successfully!";
        }
    } elseif (isset($_POST['submit_grade'])) {
       
        $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        $grade = isset($_POST['grade']) ? $conn->real_escape_string($_POST['grade']) : '';
        
        if ($student_id > 0 && $class_id > 0 && !empty($grade)) {
           
            $check = $conn->prepare("SELECT id FROM grades WHERE student_id = ? AND class_id = ?");
            $check->bind_param("ii", $student_id, $class_id);
            $check->execute();
            $check_result = $check->get_result();
            
            if ($check_result->num_rows > 0) {
                
                $update = $conn->prepare("UPDATE grades SET grade = ?, updated_at = NOW() WHERE student_id = ? AND class_id = ?");
                $update->bind_param("sii", $grade, $student_id, $class_id);
                $update->execute();
            } else {
                // Insert new grade 
                $insert = $conn->prepare("INSERT INTO grades (student_id, class_id, grade, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $insert->bind_param("iis", $student_id, $class_id, $grade);
                $insert->execute();
            }
            
            $_SESSION['message'] = "Grade submitted successfully!";
        } else {
            $_SESSION['message'] = "Error: Missing required grade information!";
        }
    }
    
    header("Location: teacher_dashboard.php");
    exit;
}

// Get recent attendance records for display
$recent_attendance = $conn->query("
    SELECT a.*, s.id as student_id, s.first_name, s.last_name, c.name as class_name 
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN classes c ON s.class_id = c.id
    WHERE c.teacher_id = $teacher_id
    ORDER BY a.date DESC, a.recorded_at DESC
    LIMIT 10
");

// Get recent grades for display 
$recent_grades = $conn->query("
    SELECT g.*, s.id as student_id, s.first_name, s.last_name, c.name as class_name 
    FROM grades g
    JOIN students s ON g.student_id = s.id
    JOIN classes c ON g.class_id = c.id
    WHERE c.teacher_id = $teacher_id
    ORDER BY g.created_at DESC
    LIMIT 10
");

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
        .header a{
            top: 40px;
            font-weight: bold;
        }
        .nav {
            background-color: #333;
            padding: 10px;
            text-align: center;
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
        .btn-success {
            background-color: #28a745;
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
        .logout-btn {
            color: white;
            text-decoration: none;
            position: absolute;
            right: 20px;
            top: 15px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="date"], input[type="email"], input[type="tel"], 
        select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .attendance-present {
            color: green;
            font-weight: bold;
        }
        .attendance-absent {
            color: red;
            font-weight: bold;
        }
        .flex-container {
            display: flex;
            gap: 20px;
        }
        .flex-item {
            flex: 1;
        }
        .back-btn {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="header">
        <h2>Teacher Dashboard</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="nav">
        <a href="#" onclick="showTab('dashboard')">Dashboard</a>
        <a href="#" onclick="showTab('attendance')">Attendance</a>
        <a href="#" onclick="showTab('grades')">Grades</a>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- View Students for a Class -->
        <?php if (isset($class_students)): ?>
            <div class="card">
                <a href="teacher_dashboard.php" class="btn btn-primary back-btn">Back to Dashboard</a>
                <div class="card-header">
                    <h2>Students in Class</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($student = $class_students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
        <!-- Dashboard Tab -->
        <div id="dashboard" class="tab-content active">
            <div class="flex-container">
                <div class="flex-item">
                    <div class="card">
                        <div class="card-header">
                            <h2>My Classes</h2>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($class = $classes->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($class['name']); ?></td>
                                    <td>
                                        <a href="?view_students=<?php echo $class['id']; ?>" class="btn btn-primary btn-sm">View Students</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex-item">
                    <div class="card">
                        <div class="card-header">
                            <h2>Recent Attendance</h2>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($record = $recent_attendance->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $record['student_id']; ?></td>
                                    <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($record['class_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($record['date'])); ?></td>
                                    <td class="attendance-<?php echo $record['status']; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Recent Grades</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Grade</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($grade = $recent_grades->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $grade['student_id']; ?></td>
                            <td><?php echo htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['class_name']); ?></td>
                            <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($grade['created_at'])); ?></td>
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
                    <h2>Mark Attendance</h2>
                </div>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select name="class_id" id="class_id" required>
                            <option value="">Select a class</option>
                            <?php 
                            $classes->data_seek(0);
                            while($class = $classes->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" name="date" id="date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div id="students_container">
                        <p>Please select a class first</p>
                    </div>
                    
                    <button type="submit" name="mark_attendance" class="btn btn-success">Submit Attendance</button>
                </form>
            </div>
        </div>

        <!-- Grades Tab -->
        <div id="grades" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2>Submit Grades</h2>
                </div>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="grade_class_id">Class</label>
                        <select name="class_id" id="grade_class_id" required>
                            <option value="">Select a class</option>
                            <?php 
                            $classes->data_seek(0);
                            while($class = $classes->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="student_id">Student</label>
                        <select name="student_id" id="student_id" required disabled>
                            <option value="">Select a student</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="grade">Grade</label>
                        <input type="text" name="grade" id="grade" required placeholder="Enter grade (e.g., A, B+, 85)">
                    </div>
                    
                    <button type="submit" name="submit_grade" class="btn btn-success">Submit Grade</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            history.pushState(null, null, '#' + tabId);
        }
        
        window.addEventListener('load', function() {
            if(window.location.hash) {
                const tabId = window.location.hash.substring(1);
                showTab(tabId);
            }
        });
        
        document.getElementById('class_id').addEventListener('change', function() {
            const classId = this.value;
            if (!classId) return;
            
            fetch('get_students.php?class_id=' + classId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('students_container').innerHTML = data;
                });
        });
        
        document.getElementById('grade_class_id').addEventListener('change', function() {
            const classId = this.value;
            const studentSelect = document.getElementById('student_id');
            
            if (!classId) {
                studentSelect.disabled = true;
                studentSelect.innerHTML = '<option value="">Select a student</option>';
                return;
            }
            
            fetch('get_students.php?class_id=' + classId + '&select=1')
                .then(response => response.text())
                .then(data => {
                    studentSelect.innerHTML = data;
                    studentSelect.disabled = false;
                });
        });
    </script>
</body>
</html>
<?php
include 'db.php'; // Include database connection

// CRUD operations
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle add attendance
if ($action == 'mark') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Insert attendance record into database
        $student_id = $_POST['student_id'];
        $date = $_POST['date'];
        $status = $_POST['status'];

        $sql = "INSERT INTO attendance (student_id, date, status) VALUES ('$student_id', '$date', '$status')";

        if ($conn->query($sql) === TRUE) {
            header("Location: attendance.html");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Mark attendance form
        echo '<h2>Mark Attendance</h2>';
        echo '<form action="attendance.php?action=mark" method="POST">';
        echo 'Student ID: <input type="text" name="student_id"><br>';
        echo 'Date: <input type="date" name="date"><br>';
        echo 'Status: <select name="status">';
        echo '<option value="present">Present</option>';
        echo '<option value="absent">Absent</option>';
        echo '</select><br>';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    }
}

// View all attendance records
$sql = "SELECT * FROM attendance";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['student_id'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
}

// Close connection
$conn->close();
?>

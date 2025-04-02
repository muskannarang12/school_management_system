<!-- <?php
include 'db.php'; // Include database connection

// CRUD operations
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle assign grade
if ($action == 'assign') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Insert grade record into database
        $student_id = $_POST['student_id'];
        $class_id = $_POST['class_id'];
        $grade = $_POST['grade'];

        $sql = "INSERT INTO grades (student_id, class_id, grade) VALUES ('$student_id', '$class_id', '$grade')";

        if ($conn->query($sql) === TRUE) {
            header("Location: grades.html");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Assign grade form
        echo '<h2>Assign New Grade</h2>';
        echo '<form action="grades.php?action=assign" method="POST">';
        echo 'Student ID: <input type="text" name="student_id"><br>';
        echo 'Class ID: <input type="text" name="class_id"><br>';
        echo 'Grade: <input type="text" name="grade"><br>';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    }
}

// View all grades
$sql = "SELECT * FROM grades";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['student_id'] . "</td>";
        echo "<td>" . $row['class_id'] . "</td>";
        echo "<td>" . $row['grade'] . "</td>";
        echo "</tr>";
    }
}

// Close connection
$conn->close();
?> -->

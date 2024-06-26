<?php
include 'db.php'; // Include database connection

// CRUD operations
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle add, edit, delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action == 'add') {
        // Add new class
        $name = $_POST['name'];
        $teacher_id = $_POST['teacher_id'];
        $sql = "INSERT INTO classes (name, teacher_id) VALUES ('$name', '$teacher_id')";
        if ($conn->query($sql) === TRUE) {
            header("Location: classes.html");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif ($action == 'edit') {
        // Update existing class
        $id = $_POST['id'];
        $name = $_POST['name'];
        $teacher_id = $_POST['teacher_id'];
        $sql = "UPDATE classes SET name='$name', teacher_id='$teacher_id' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            header("Location: classes.html");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} elseif ($action == 'delete') {
    // Delete class
    $id = $_GET['id'];
    $sql = "DELETE FROM classes WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: classes.html");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle form display
if ($action == 'add') {
    echo '<h2>Add New Class</h2>';
    echo '<form action="classes.php?action=add" method="POST">';
    echo 'Name: <input type="text" name="name"><br>';
    echo 'Teacher ID: <input type="text" name="teacher_id"><br>';
    echo '<input type="submit" value="Submit">';
    echo '</form>';
} elseif ($action == 'edit') {
    $id = $_GET['id'];
    $sql = "SELECT * FROM classes WHERE id=$id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo '<h2>Edit Class</h2>';
        echo '<form action="classes.php?action=edit" method="POST">';
        echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
        echo 'Name: <input type="text" name="name" value="' . $row['name'] . '"><br>';
        echo 'Teacher ID: <input type="text" name="teacher_id" value="' . $row['teacher_id'] . '"><br>';
        echo '<input type="submit" value="Update">';
        echo '</form>';
    }
}

// Display all classes
$sql = "SELECT * FROM classes";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['teacher_id'] . "</td>";
        echo "<td>";
        echo "<a href='classes.php?action=edit&id=" . $row['id'] . "'>Edit</a> | ";
        echo "<a href='classes.php?action=delete&id=" . $row['id'] . "'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
}

// Close connection
$conn->close();
?>

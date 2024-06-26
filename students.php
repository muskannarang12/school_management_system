<?php
include 'db.php'; // Include database connection

// CRUD operations
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Add new student
if ($action == 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Insert student into database
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $dob = $_POST['dob'];
        $class_id = $_POST['class_id'];

        $sql = "INSERT INTO students (first_name, last_name, email, phone, address, dob, class_id) VALUES ('$first_name', '$last_name', '$email', '$phone', '$address', '$dob', '$class_id')";

        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Insert form
        echo '<h2>Add New Student</h2>';
        echo '<form action="students.php?action=add" method="POST">';
        echo 'First Name: <input type="text" name="first_name"><br>';
        echo 'Last Name: <input type="text" name="last_name"><br>';
        echo 'Email: <input type="email" name="email"><br>';
        echo 'Phone: <input type="text" name="phone"><br>';
        echo 'Address: <textarea name="address"></textarea><br>';
        echo 'Date of Birth: <input type="date" name="dob"><br>';
        echo 'Class ID: <input type="text" name="class_id"><br>';
        echo '<input type="submit" value="Submit">';
        echo '</form>';
    }
}

// View all students
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['first_name'] . "</td>";
        echo "<td>" . $row['last_name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['phone'] . "</td>";
        echo "<td>" . $row['address'] . "</td>";
        echo "<td>" . $row['dob'] . "</td>";
        echo "<td>" . $row['class_id'] . "</td>";
        echo "<td>";
        echo "<a href='students.php?action=edit&id=" . $row['id'] . "'>Edit</a> | ";
        echo "<a href='students.php?action=delete&id=" . $row['id'] . "'>Delete</a>";
        echo "</td>";
        echo "</tr>";
    }
}

// Close connection
$conn->close();
?>

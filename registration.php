<?php
include 'db.php'; // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Check if username already exists
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username already exists.'); window.location.href='registration.html';</script>";
    } else {
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful.'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Error: Could not register user.'); window.location.href='registration.html';</script>";
        }

        $stmt->close();
    }

    $checkUser->close();
}

// Close connection
$conn->close();
?>

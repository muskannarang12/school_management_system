<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

$error = '';
$success = '';

// Get student ID from URL
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Get student details with user account
$student = $conn->query("
    SELECT s.*, u.id as user_id, u.username 
    FROM students s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.id = $student_id
")->fetch_assoc();

if (!$student) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($password)) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $student['user_id']);
        
        if ($update_stmt->execute()) {
            $success = "Password reset successfully for student: " . htmlspecialchars($student['username']);
            $_SESSION['message'] = $success;
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Error resetting password: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Student Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background-color: #fff;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background-color: #e9ecef;
            border-radius: 3px;
        }
        .password-strength-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s, background-color 0.3s;
        }
    </style>
</head>
<body>
 
    
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Reset Student Password</h2>
            <p>Student: <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></p>
            <p>Username: <strong><?php echo htmlspecialchars($student['username']); ?></strong></p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <small id="passwordHelp" class="text-muted">Minimum 8 characters</small>
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <small id="confirmHelp" class="text-muted"></small>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="admin_dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) strength += 1;
            
            let width = (strength / 5) * 100;
            let color = '#dc3545'; // red
            
            if (strength >= 3) color = '#ffc107'; // yellow
            if (strength >= 5) color = '#28a745'; // green
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
        });
        
        // Password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const confirmHelp = document.getElementById('confirmHelp');
            
            if (confirm === password && password.length >= 8) {
                confirmHelp.textContent = 'Passwords match!';
                confirmHelp.style.color = '#28a745';
            } else if (password.length < 8) {
                confirmHelp.textContent = 'Password must be at least 8 characters';
                confirmHelp.style.color = '#dc3545';
            } else {
                confirmHelp.textContent = 'Passwords do not match';
                confirmHelp.style.color = '#dc3545';
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

$error = '';
$success = '';

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;


$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();

if (!$student) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
   
    if (empty($username) || empty($password)) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username already exists";
        } else {
          
            $conn->begin_transaction();
            
            try {
              
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $user_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
                $user_stmt->bind_param("ss", $username, $hashed_password);
                $user_stmt->execute();
                $user_id = $conn->insert_id;
                
                
                $update_stmt = $conn->prepare("UPDATE students SET user_id = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $user_id, $student_id);
                $update_stmt->execute();
                
              
                $conn->commit();
                
                $success = "Login credentials assigned successfully! Username: $username";
                $_SESSION['message'] = $success;
                header("Location: admin_dashboard.php");
                exit;
            } catch (Exception $e) {
            
                $conn->rollback();
                $error = "Error assigning credentials: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Login Credentials</title>
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
            <h2 class="mb-4">Assign Login Credentials</h2>
            <p>Student: <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong></p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username *</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password *</label>
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
                    <button type="submit" class="btn btn-primary">Assign Credentials</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
  
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
        
       
        document.addEventListener('DOMContentLoaded', function() {
            const email = "<?php echo $student['email']; ?>";
            if (email) {
                const emailParts = email.split('@');
                if (emailParts.length > 0) {
                    const emailUsername = emailParts[0].toLowerCase();
                    document.getElementById('username').value = emailUsername;
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
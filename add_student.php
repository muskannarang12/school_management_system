<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Student details
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $dob = $_POST['dob'];
    $class_id = intval($_POST['class_id']);
    
    // Account credentials
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        // Check if username already exists (removed email check)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username already exists";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // 1. Create user account (removed email from this query)
                $user_stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
                $user_stmt->bind_param("ss", $username, $hashed_password);
                $user_stmt->execute();
                $user_id = $conn->insert_id;
                
                // 2. Create student profile
                $student_stmt = $conn->prepare("INSERT INTO students 
                    (first_name, last_name, email, phone, address, dob, class_id, user_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $student_stmt->bind_param("ssssssii", 
                    $first_name, $last_name, $email, 
                    $phone, $address, $dob, $class_id, $user_id);
                $student_stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $success = "Student added successfully! Login credentials: Username: $username";
                $_SESSION['message'] = $success;
                header("Location: admin_dashboard.php");
                exit;
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                $error = "Error adding student: " . $e->getMessage();
            }
        }
    }
}

// Get classes for dropdown
$classes = $conn->query("SELECT id, name FROM classes ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
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
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
   
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4">Add New Student</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="studentForm">
                <div class="form-section">
                    <h5 class="mb-3">Personal Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dob" name="dob">
                        </div>
                        <div class="col-md-6">
                            <label for="class_id" class="form-label">Class *</label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Select a class</option>
                                <?php while($class = $classes->fetch_assoc()): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h5 class="mb-3">Login Credentials</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <small class="text-muted">This will be used to log in to the system</small>
                        </div>
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
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="admin_dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Student</button>
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
        
        // Form validation
        document.getElementById('studentForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            
            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                return false;
            }
            
            return true;
        });

        // Generate username suggestion based on name and email
        document.getElementById('first_name').addEventListener('blur', generateUsername);
        document.getElementById('last_name').addEventListener('blur', generateUsername);
        document.getElementById('email').addEventListener('blur', generateUsername);

        function generateUsername() {
            const firstName = document.getElementById('first_name').value.toLowerCase();
            const lastName = document.getElementById('last_name').value.toLowerCase();
            const email = document.getElementById('email').value;
            
            if (firstName && lastName) {
                // Generate username from first letter of first name + last name
                let username = firstName.charAt(0) + lastName;
                username = username.replace(/[^a-z0-9]/g, ''); // Remove special characters
                
                // If email is provided, try to extract username part
                if (email) {
                    const emailParts = email.split('@');
                    if (emailParts.length > 0) {
                        const emailUsername = emailParts[0].toLowerCase();
                        document.getElementById('username').value = emailUsername;
                        return;
                    }
                }
                
                document.getElementById('username').value = username;
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
<?php
include 'db.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } elseif (strlen($username) < 4) {
        $error = 'Username must be at least 4 characters';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } elseif (!in_array($role, ['admin', 'teacher', 'student'])) {
        $error = 'Invalid role selected';
    } else {
        // Check if username already exists
        $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);

            if ($stmt->execute()) {
                // Registration successful - redirect to login
                header("Location: login.php?registration=success");
                exit();
            } else {
                $error = 'Error: Could not register user';
            }

            $stmt->close();
        }
        $checkUser->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --error-color: #ef233c;
            --background-color: #f8f9fa;
            --card-color: #ffffff;
            --text-color: #2b2d42;
            --border-color: #e9ecef;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--text-color);
        }
        
        .auth-container {
            background-color: var(--card-color);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            width: 380px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .auth-container:hover {
            transform: translateY(-5px);
        }
        
        .auth-container h1 {
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .error-message {
            color: var(--error-color);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .form-group {
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .auth-container input[type="text"], 
        .auth-container input[type="password"],
        .auth-container select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            background-color: white;
        }
        
        .auth-container input[type="text"]:focus, 
        .auth-container input[type="password"]:focus,
        .auth-container select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        }
        
        .auth-container select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1em;
        }
        
        .auth-container input[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 0.5rem;
        }
        
        .auth-container input[type="submit"]:hover {
            background-color: var(--primary-hover);
        }
        
        .auth-footer {
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
            color: var(--primary-hover);
        }
        
        .password-hint {
            font-size: 0.8rem;
            color: var(--secondary-color);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Create Account</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form class="auth-form" action="registration.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                <div class="password-hint">Minimum 8 characters</div>
            </div>
            
            <div class="form-group">
                <label for="role">Account Type</label>
                <select id="role" name="role" required>
                    <option value="" disabled <?php echo !isset($_POST['role']) ? 'selected' : ''; ?>>Select your role</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                    <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                    <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                </select>
            </div>
            
            <input type="submit" value="Sign Up">
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>
<?php
// Close connection at the end
$conn->close();
?>

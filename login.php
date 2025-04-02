<?php
session_start();
include 'db.php';

// Display success message if registration was successful
if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
    $success = 'Registration successful! Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        header("Location: login.php?error=emptyfields&username=".urlencode($username));
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $sql = "SELECT u.*, t.id as teacher_id 
            FROM users u 
            LEFT JOIN teachers t ON u.id = t.user_id 
            WHERE u.username = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        header("Location: login.php?error=sqlerror");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            // Store teacher ID if user is a teacher
            if ($row['role'] == 'teacher' && isset($row['teacher_id'])) {
                $_SESSION['teacher_id'] = $row['teacher_id'];
            }

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Redirect based on role
            switch ($row['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'teacher':
                    // Check if teacher profile exists
                    if (isset($row['teacher_id'])) {
                        header("Location: teacher_dashboard.php");
                    } else {
                        // Redirect to profile completion if no teacher profile
                        $_SESSION['needs_profile'] = true;
                        header("Location: teacher_dashboard.php");
                    }
                    break;
                case 'student':
                    header("Location: student_dashboard.php");
                    break;
                default:
                    header("Location: login.php?error=unauthorized");
            }
            exit();
        } else {
            header("Location: login.php?error=invalidcredentials&username=".urlencode($username));
            exit();
        }
    } else {
        header("Location: login.php?error=invalidcredentials&username=".urlencode($username));
        exit();
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --error-color: #ef233c;
            --success-color: #4bb543;
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
        
        .success-message {
            color: var(--success-color);
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
        .auth-container input[type="password"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }
        
        .auth-container input[type="text"]:focus, 
        .auth-container input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
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
    </style>
</head>
<body>
    <div class="auth-container">
        <h1>Welcome Back</h1>
        
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php
        // Display error messages if they exist
        if (isset($_GET['error'])) {
            $error_messages = [
                'emptyfields' => 'Please fill in all fields',
                'invalidcredentials' => 'Invalid username or password',
                'sqlerror' => 'Database error occurred',
                'unauthorized' => 'Unauthorized access'
            ];
            
            if (array_key_exists($_GET['error'], $error_messages)) {
                echo '<div class="error-message">' . $error_messages[$_GET['error']] . '</div>';
            }
        }
        ?>
        
        <form class="auth-form" action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required
                       value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <input type="submit" value="Sign In">
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="registration.php">Create one</a>
        </div>
    </div>
</body>
</html>
<?php
// Close connection at the end
$conn->close();
?>
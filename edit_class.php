<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';

$message = '';
$class = null;

if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php#classes");
    exit;
}

$id = $_GET['id'];
$teachers = $conn->query("SELECT id, first_name, last_name FROM teachers");

// Fetch class details
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$class = $result->fetch_assoc();
$stmt->close();

if (!$class) {
    header("Location: admin_dashboard.php#classes");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $teacher_id = $_POST['teacher_id'];
    
    $stmt = $conn->prepare("UPDATE classes SET name = ?, teacher_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $name, $teacher_id, $id);
    
    if ($stmt->execute()) {
        $message = "Class updated successfully!";
        header("Location: admin_dashboard.php#classes");
        exit;
    } else {
        $message = "Error updating class: " . $conn->error;
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Edit Class</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form action="edit_class.php?id=<?php echo $id; ?>" method="post">
            <div class="form-group">
                <label for="name">Class Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($class['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="teacher_id">Teacher:</label>
                <select id="teacher_id" name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <?php 
                    $teachers->data_seek(0); // Reset pointer to beginning
                    while ($teacher = $teachers->fetch_assoc()): ?>
                        <option value="<?php echo $teacher['id']; ?>" <?php echo $teacher['id'] == $class['teacher_id'] ? 'selected' : ''; ?>>
                            <?php echo $teacher['first_name'] . ' ' . $teacher['last_name']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn">Update Class</button>
            <a href="admin_dashboard.php#classes" class="btn" style="background-color: #6c757d;">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php
include 'db.php';

if (isset($_GET['class_id'])) {
    $class_id = intval($_GET['class_id']);
    $students = $conn->query("SELECT id, first_name, last_name FROM students WHERE class_id = $class_id ORDER BY last_name, first_name");
    
    if (isset($_GET['select'])) {
        // Return options for select dropdown
        echo '<option value="">Select a student</option>';
        while($student = $students->fetch_assoc()) {
            echo '<option value="' . $student['id'] . '">' 
                . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) 
                . '</option>';
        }
    } else {
        // Return attendance table rows
        if ($students->num_rows > 0) {
            echo '<table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
            
            while($student = $students->fetch_assoc()) {
                echo '<tr>
                    <td>' . htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']) . '</td>
                    <td>
                        <select name="attendance[' . $student['id'] . ']" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                        </select>
                    </td>
                </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<p>No students found in this class.</p>';
        }
    }
}
?>
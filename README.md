A web-based School Management System built using PHP, JavaScript, HTML, and CSS. This system allows administrators, teachers, and students to manage various school-related tasks efficiently.
Features
Admin Dashboard:

Add, edit, delete, and view teachers and students.
Add, edit, delete, and view classes.
View grades of students.
View attendance records.
Teacher Dashboard:

Add, edit, delete, and view grades for students in their classes.
View attendance records for students in their classes.
Student Dashboard:

View personal information.
View grades.
View attendance records.
User Authentication:

Separate logins for admin, teachers, and students.
Secure password storage using hashing.
Registration and login pages.
Project Structure
Database:

MySQL database with tables for users, students, teachers, classes, grades, and attendance.
Backend:

PHP scripts for CRUD operations and user authentication.
Separate scripts for adding, editing, and deleting records.
Frontend:

HTML for structure.
CSS for styling.
JavaScript for client-side interactions.
Database Schema
users: Stores user information for authentication.

id (INT, Primary Key, Auto Increment)
username (VARCHAR(50), Unique)
password (VARCHAR(255))
role (ENUM: 'admin', 'teacher', 'student')
created_at (TIMESTAMP, Default CURRENT_TIMESTAMP)
students: Stores student information.

id (INT, Primary Key, Auto Increment)
first_name (VARCHAR(50))
last_name (VARCHAR(50))
email (VARCHAR(100), Unique)
phone (VARCHAR(15))
address (TEXT)
dob (DATE)
class_id (INT, Foreign Key referencing classes.id)
teachers: Stores teacher information.

id (INT, Primary Key, Auto Increment)
first_name (VARCHAR(50))
last_name (VARCHAR(50))
email (VARCHAR(100), Unique)
phone (VARCHAR(15))
address (TEXT)
dob (DATE)
classes: Stores class information.

id (INT, Primary Key, Auto Increment)
name (VARCHAR(50))
teacher_id (INT, Foreign Key referencing teachers.id)
grades: Stores grades assigned to students.

id (INT, Primary Key, Auto Increment)
student_id (INT, Foreign Key referencing students.id)
class_id (INT, Foreign Key referencing classes.id)
grade (VARCHAR(2))
attendance: Stores attendance records.

id (INT, Primary Key, Auto Increment)
student_id (INT, Foreign Key referencing students.id)
date (DATE)
status (ENUM: 'present', 'absent')
Clone the repository to your local machine:

bash
git clone https://github.com/muskannarang12/school_management_system.git
Move into the project directory:

bash
Copy code
cd school-management-system
Set up the database:

Import the provided SQL file to set up the database schema and initial data.
Configure the database connection:

Update the db.php file with your database credentials.
Run the project:

Start your web server (e.g., XAMPP, WAMP).
Navigate to the project directory in your web browser.
Usage
Access the login page via login.html.
Admin, teachers, and students can log in with their credentials.
Admins can manage teachers, students, classes, grades, and attendance from the admin dashboard.
Teachers can manage grades and view attendance from the teacher dashboard.
Students can view their grades and attendance from the student dashboard.

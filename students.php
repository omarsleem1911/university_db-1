// Back-end CRUD functions, >>Omar's job<<

<?php
require_once 'conn.php';

// Initialize variables
$name = $email = $phone = $address = $dob = $gender = $enrollment_date = $graduation_date = $status = $major = '';
$error = '';
$success = '';

// CRUD Operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create Student
    if (isset($_POST['add_student'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $enrollment_date = $_POST['enrollment_date'];
        $graduation_date = $_POST['graduation_date'];
        $status = $_POST['status'];
        $major = $_POST['major'];

        if (empty($name) || empty($email) || empty($major)) {
            $error = 'Name, email and major are required!';
        } else {
            $stmt = $conn->prepare("INSERT INTO student (first_name, last_name, email, phone_number, address, date_of_birth, gender, enrollment_date, expected_graduation, status, major_department_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Split name into first and last
            $name_parts = explode(' ', $name, 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
            
            $stmt->bind_param("sssssssssss", $first_name, $last_name, $email, $phone, $address, $dob, $gender, $enrollment_date, $graduation_date, $status, $major);
            
            if ($stmt->execute()) {
                $success = 'Student added successfully!';
                // Clear form
                $name = $email = $phone = $address = $dob = $gender = $enrollment_date = $graduation_date = $status = $major = '';
            } else {
                $error = 'Error adding student: ' . $conn->error;
            }
            $stmt->close();
        }
    }

    // Update Student
    if (isset($_POST['update_student'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $enrollment_date = $_POST['enrollment_date'];
        $graduation_date = $_POST['graduation_date'];
        $status = $_POST['status'];
        $major = $_POST['major'];

        if (empty($name) || empty($email) || empty($major)) {
            $error = 'Name, email and major are required!';
        } else {
            // Split name into first and last
            $name_parts = explode(' ', $name, 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
            
            $stmt = $conn->prepare("UPDATE student SET first_name=?, last_name=?, email=?, phone_number=?, address=?, date_of_birth=?, gender=?, enrollment_date=?, expected_graduation=?, status=?, major_department_id=? WHERE student_id=?");
            $stmt->bind_param("sssssssssssi", $first_name, $last_name, $email, $phone, $address, $dob, $gender, $enrollment_date, $graduation_date, $status, $major, $id);
            
            if ($stmt->execute()) {
                $success = 'Student updated successfully!';
            } else {
                $error = 'Error updating student: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Delete Student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM student WHERE student_id=?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = 'Student deleted successfully!';
    } else {
        $error = 'Error deleting student: ' . $conn->error;
    }
    $stmt->close();
}

// Get Student for editing
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM student WHERE student_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if ($student) {
        $name = $student['first_name'] . ' ' . $student['last_name'];
        $email = $student['email'];
        $phone = $student['phone_number'];
        $address = $student['address'];
        $dob = $student['date_of_birth'];
        $gender = $student['gender'];
        $enrollment_date = $student['enrollment_date'];
        $graduation_date = $student['expected_graduation'];
        $status = $student['status'];
        $major = $student['major_department_id'];
    }
}

// Get all students
$students = [];
$stmt = $conn->prepare("SELECT s.*, d.department_name FROM student s JOIN department d ON s.major_department_id = d.department_id ORDER BY s.last_name, s.first_name");
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get all departments for dropdown
$departments = [];
$stmt = $conn->prepare("SELECT * FROM department ORDER BY department_name");
$stmt->execute();
$result = $stmt->get_result();
$departments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>



// Front-end stuff, >>Amr's job<<

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        header h1 {
            text-align: center;
        }
        
        .nav-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #34495e;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .btn-danger {
            background-color: #e74c3c;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-success {
            background-color: #2ecc71;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-links a {
            color: #3498db;
            text-decoration: none;
            margin-right: 10px;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .search-container input {
            padding: 10px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Student Management System</h1>
            <div class="nav-links">
                <a href="index.html">Home</a>
                <a href="students.php">Students</a>
                <a href="professors.php">Professors</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2><?php echo isset($_GET['edit']) ? 'Edit Student' : 'Add New Student'; ?></h2>
            <form method="POST" action="">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="id" value="<?php echo $_GET['edit']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="major">Major Department</label>
                        <select id="major" name="major" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['department_id']; ?>" <?php echo ($major == $dept['department_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="enrollment_date">Enrollment Date</label>
                        <input type="date" id="enrollment_date" name="enrollment_date" value="<?php echo htmlspecialchars($enrollment_date); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="graduation_date">Expected Graduation</label>
                        <input type="date" id="graduation_date" name="graduation_date" value="<?php echo htmlspecialchars($graduation_date); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Active" <?php echo ($status == 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="On Leave" <?php echo ($status == 'On Leave') ? 'selected' : ''; ?>>On Leave</option>
                        <option value="Graduated" <?php echo ($status == 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                        <option value="Withdrawn" <?php echo ($status == 'Withdrawn') ? 'selected' : ''; ?>>Withdrawn</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <?php if (isset($_GET['edit'])): ?>
                        <button type="submit" name="update_student" class="btn btn-success">Update Student</button>
                        <a href="students.php" class="btn">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_student" class="btn">Add Student</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="search-container">
            <input type="text" id="search" placeholder="Search students...">
        </div>
        
        <h2>Student List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Major</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $student['student_id']; ?></td>
                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['department_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['status']); ?></td>
                        <td class="action-links">
                            <a href="students.php?edit=<?php echo $student['student_id']; ?>">Edit</a>
                            <a href="students.php?delete=<?php echo $student['student_id']; ?>" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>

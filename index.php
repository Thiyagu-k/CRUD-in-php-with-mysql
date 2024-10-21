<?php
// Database connection
$servername = "localhost";
$username = "root";  // your MySQL username
$password = "";  // your MySQL password
$dbname = "employee_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert or update employee data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $profile = $_POST['profile'];

    if ($id) {
        // Update employee
        $sql = "UPDATE employees SET name='$name', email='$email', phone='$phone', department='$department', profile='$profile' WHERE id='$id'";
    } else {
        // Insert new employee
        $sql = "INSERT INTO employees (name, email, phone, department, profile) 
                VALUES ('$name', '$email', '$phone', '$department', '$profile')";
    }

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    exit();
}

// Delete employee data
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $del_vars);
    $id = $del_vars['id'];

    $sql = "DELETE FROM employees WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    exit();
}

// Fetch employees data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM employees";
    $result = $conn->query($sql);

    $employees = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    }
    echo json_encode($employees);
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee CRUD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        form, .employee-list, .employee-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .employee {
            background-color: white;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            cursor: pointer;
        }
        .employee:hover {
            background-color: #f1f1f1;
        }
        .employee-actions button {
            margin-right: 10px;
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .employee-actions button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <h2>Add/Update Employee</h2>
    <form id="employeeForm">
        <input type="hidden" id="employeeId" name="id">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" required>

        <label for="department">Department</label>
        <input type="text" id="department" name="department">

        <label for="profile">Profile</label>
        <textarea id="profile" name="profile" rows="4"></textarea>

        <button type="submit">Submit</button>
    </form>

    <div class="employee-list">
        <h3>Employee List</h3>
        <div id="employeeData"></div>
    </div>

    <div class="employee-details" id="employeeDetails" style="display:none;">
        <h3>Employee Details</h3>
        <p id="detailsContent"></p>
    </div>

    <script>
        document.getElementById('employeeForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Employee saved successfully');
                    loadEmployees();
                    document.getElementById('employeeForm').reset();
                    document.getElementById('employeeId').value = '';
                } else {
                    alert('Error saving employee');
                }
            });
        });

        function loadEmployees() {
            fetch('index.php')
            .then(response => response.json())
            .then(data => {
                const employeeData = document.getElementById('employeeData');
                employeeData.innerHTML = '';
                data.forEach(employee => {
                    const div = document.createElement('div');
                    div.classList.add('employee');
                    div.innerHTML = `
                        <div>
                            <strong>${employee.name}</strong> (${employee.email})
                        </div>
                        <div class="employee-actions">
                            <button onclick="editEmployee(${employee.id})">Edit</button>
                            <button onclick="deleteEmployee(${employee.id})">Delete</button>
                        </div>
                    `;
                    div.onclick = function () {
                        showEmployeeDetails(employee);
                    };
                    employeeData.appendChild(div);
                });
            });
        }

        function editEmployee(id) {
            fetch('index.php')
            .then(response => response.json())
            .then(data => {
                const employee = data.find(emp => emp.id == id);
                document.getElementById('employeeId').value = employee.id;
                document.getElementById('name').value = employee.name;
                document.getElementById('email').value = employee.email;
                document.getElementById('phone').value = employee.phone;
                document.getElementById('department').value = employee.department;
                document.getElementById('profile').value = employee.profile;
            });
        }

        function deleteEmployee(id) {
            if (confirm('Are you sure you want to delete this employee?')) {
                fetch('index.php', {
                    method: 'DELETE',
                    body: new URLSearchParams({ id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Employee deleted successfully');
                        loadEmployees();
                    } else {
                        alert('Error deleting employee');
                    }
                });
            }
        }

        function showEmployeeDetails(employee) {
            const detailsContent = `
                <strong>Name:</strong> ${employee.name} <br>
                <strong>Email:</strong> ${employee.email} <br>
                <strong>Phone:</strong> ${employee.phone} <br>
                <strong>Department:</strong> ${employee.department} <br>
                <strong>Profile:</strong> ${employee.profile}
            `;
            document.getElementById('detailsContent').innerHTML = detailsContent;
            document.getElementById('employeeDetails').style.display = 'block';
        }

        loadEmployees();
    </script>
</body>
</html>


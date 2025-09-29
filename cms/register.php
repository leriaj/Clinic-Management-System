<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "cms";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$checkAdminQuery = "SELECT * FROM users WHERE role = 'Admin'";
$AdminResult = $conn->query($checkAdminQuery);
$AdminExists = false;
if ($AdminResult->num_rows > 0) {
    $AdminExists = true;
}

$role = null;
$AdminPassword = null;
$adminPasswordError = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $Ufirstname = trim(mysqli_real_escape_string($conn, $_POST['Ufirstname']));
    $Ulastname = trim(mysqli_real_escape_string($conn, $_POST['Ulastname']));
    $username = trim(mysqli_real_escape_string($conn, $_POST['Uusername']));
    $password = trim(mysqli_real_escape_string($conn, $_POST['password']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $contact = trim(mysqli_real_escape_string($conn, $_POST['contact']));
    $question = trim(mysqli_real_escape_string($conn, $_POST['security_question']));
    $answer = trim(mysqli_real_escape_string($conn, $_POST['security_answer']));
    $role = $_POST['role'];
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $gender = isset($_POST['gender']) ? $_POST['gender'] : null;
    $AdminPassword = isset($_POST['adminPassword']) ? $_POST['adminPassword'] : null;

    $birthDateTime = new DateTime($birthdate);
    $currentDateTime = new DateTime();
    $age = $birthDateTime->diff($currentDateTime)->y;

    if (
        !$Ufirstname || !$Ulastname || !$username || !$password || !$email ||
        !$contact || !$question || !$answer || !$role || !$birthdate || !$gender
    ) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit();
    }

    if ($AdminExists && $role !== "Admin") {
        if (!$AdminPassword) {
            echo "<script>alert('Admin password is required.'); window.history.back();</script>";
            exit();
        }
        $stmt = $conn->prepare("SELECT password FROM users WHERE role = 'Admin' LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($AdminPasswordHash);
        $stmt->fetch();
        $stmt->close();
        if (!password_verify($AdminPassword, $AdminPasswordHash)) {
            echo "<script>alert('Admin password is incorrect.'); window.history.back();</script>";
            exit();
        }
    }

    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

    $check_query = "SELECT * FROM users WHERE Uusername = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Username or Email already exists.'); window.history.back();</script>";
        exit();
    }

    $insert_query = "INSERT INTO users (Ufirstname, Ulastname, Uusername, password, email, contact, security_question, security_answer, role, birthdate, age, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssssssssssss", $Ufirstname, $Ulastname, $username, $hashed_pass, $email, $contact, $question, $answer, $role, $birthdate, $age, $gender);

    if ($stmt->execute()) {
        echo "<script>alert('Account created successfully.'); window.location.href = 'login.php';</script>";
    } else {
        echo "<script>alert('Error creating account.'); window.history.back();</script>";
    }

    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/lr.css">
    
    <link rel="icon" href="assets/img/logo.png">
    <title>Register FORM</title>
    <STYLE>
        select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 16px;
            border: 2px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        select::-ms-expand {
            display: none;
        }
        option {
            padding: 10px;
            font-size: 16px;
        }
        .select-wrapper {
            position: relative;
            width: 100%;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
            text-align: center;
        }
        .overlay-content {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
        }
        .overlay button {
            padding: 10px;
            background-color: #75f15f;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            font-size: 16px;
            margin-top: 10px;
        }
        .overlay button:hover {
            background-color: #75f15f;
        }
        .overlay-error {
            color: #ff4d4d;
            margin-bottom: 10px;
        }
    </STYLE>
</head>
<body>
    <?php if ($AdminExists): ?>
    <div class="overlay" id="overlay" style="display: flex;">
        <div class="overlay-content">
            <p>Admin password required to register as Doctor or Nurse.</p>
            <div id="admin-error" class="overlay-error"></div>
            <input type="password" id="adminPasswordOverlay" placeholder="Enter admin password">
            <button type="button" onclick="verifyAdminOverlay()">Continue</button>
        </div>
    </div>
    <script>
        function verifyAdminOverlay() {
            var adminPassword = document.getElementById('adminPasswordOverlay').value;
            if (!adminPassword) {
                document.getElementById('admin-error').textContent = "Please enter admin password.";
                return;
            }
            document.getElementById('admin-error').textContent = "";
            document.getElementById('overlay').style.display = 'none';
            document.querySelector('option[value="Doctor"]').disabled = false;
            document.querySelector('option[value="Nurse"]').disabled = false;
            document.querySelector('option[value="Admin"]').remove();
            document.getElementById('adminPasswordField').value = adminPassword;
        }
        window.onload = function() {
            document.querySelector('option[value="Doctor"]').disabled = true;
            document.querySelector('option[value="Nurse"]').disabled = true;
            document.getElementById('adminPasswordField').value = "";
        }
    </script>
    <?php endif; ?>
    <div class="container">
        <h1>Registration Form</h1>
        <form action="register.php" method="post">
            <div class="input-group">
                <label for="Fastname">
                    <i class="fa-solid fa-user"></i> Firstname
                </label>
                <input type="text" id="Ufirstname" placeholder="Enter firstname" name="Ufirstname" required>
            </div>
            <div class="input-group">
                <label for="Ulastname">
                    <i class="fa-solid fa-user"></i> Lastname
                </label>
                <input type="text" id="Ulastname" placeholder="Enter Lastname" name="Ulastname" required>
            </div>
            <div class="input-group">
                <label for="role">
                    <i class="fa-solid fa-user-tag"></i> Role
                </label>
                <select class="select" id="role" name="role" required>
                    <option value="" disabled selected>Roles</option>
                    <option value="Admin" <?php if($AdminExists) echo 'style="display:none;"'; ?>>Admin</option>
                    <option value="Doctor" <?php if(!$AdminExists) echo 'disabled'; ?>>Doctor</option>
                    <option value="Nurse" <?php if(!$AdminExists) echo 'disabled'; ?>>Nurse</option>
                </select>
            </div>
            <input type="hidden" id="adminPasswordField" name="adminPassword">
            <div class="input-group">
                <label for="Uusername">
                    <i class="fa-solid fa-user"></i> Username
                </label>
                <input type="text" id="Uusername" placeholder="Enter Username" name="Uusername" required>
            </div>
            <div class="input-group">
                <label for="password">
                    <i class="fa-solid fa-lock"></i> Password
                </label>
                <input type="password" id="password" placeholder="Enter Password" name="password" required>
            </div>
            <div class="input-group">
                <label for="birthdate">
                    <i class="fa-solid fa-calendar"></i> Birthdate
                </label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>
            <div class="input-group">
                <label for="gender">
                    <i class="fa-solid fa-venus-mars"></i> Gender
                </label>
                <select class="select" id="gender" name="gender" required>
                    <option value="" disabled selected>Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="input-group">
                <label for="email">
                    <i class="fa-solid fa-envelope"></i> Email
                </label>
                <input type="email" id="email" placeholder="Enter Email" name="email" required>
            </div>
            <div class="input-group">
                <label for="contact">
                    <i class="fa-solid fa-phone"></i> Contact
                </label>
                <input type="tel" id="contact" placeholder="Enter Contact (09912345678)" name="contact" required>
            </div>
            <div class="input-group">
                <label for="security_question">
                    <i class="fa-solid fa-question"></i> Security Question
                </label>
                <select class="select" id="security_question" name="security_question" required>
                    <option value="" disabled selected>Select Security Question</option>
                    <option value="What is your pet’s name?">What is your pet’s name?</option>
                    <option value="What is your mother’s maiden name?">What is your mother’s maiden name?</option>
                    <option value="What was your first school?">What was your first school?</option>
                    <option value="What is your favorite book?">What is your favorite book?</option>
                </select>
            </div>
            <div class="input-group">
                <label for="security_answer">
                    <i class="fa-solid fa-lock"></i> Security Answer
                </label>
                <input type="text" id="security_answer" name="security_answer" placeholder="Answer" required>
            </div>
            <div class="button">
                <button type="submit">Register</button>
            </div>
            <p>
                Already have an Account? <a href="login.php">Sign in</a>
            </p>
        </form>
    </div>
</body>
</html>
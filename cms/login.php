<?php
session_start();
$lockout_time = isset($_GET['lockout']) ? intval($_GET['lockout']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "cms";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($lockout_time > 0) {
        echo "<script>alert('You are locked out. Please try again in $lockout_time seconds.');</script>";
    }
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
    }

    if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
        $remaining_time = $_SESSION['lockout_time'] - time();
        header("Location: login.php?lockout=$remaining_time");
        exit();
    }

    $Uusername = $_POST['Uusername'];
    $password_input = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE Uusername = ?");
    $stmt->bind_param("s", $Uusername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($_SESSION['failed_attempts'] >= 3) {
    $_SESSION['lockout_time'] = time() + 10; 
    header("Location: login.php?lockout=10");
    exit();
}

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];
        $id = $row['id'];
        $role = $row['role'];

        if (password_verify($password_input, $stored_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['Uusername'] = $Uusername;
            $_SESSION['role'] = $role;
            $_SESSION['failed_attempts'] = 0;

            if ($role == 'Doctor') {
                header("Location: dashboard/dd.php");
            } else if ($role == 'Admin') {
                header("Location: dashboard/admin/admin.php");
            } else {
                header("Location: dashboard/pd.php");
            }
            exit();
        } else {
            $_SESSION['failed_attempts']++;
            if ($_SESSION['failed_attempts'] >= 3) {
                header("Location: login.php?lockout=10");
            } else {
                echo "<script>alert('Invalid password. Please try again.');</script>";
            }
        }
    } else {
        echo "<script>alert('Username does not exist. Please register.');</script>";
    }



    $stmt->close();
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
    <title>LOGIN FORM</title>
    <style>
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            font-size: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
    </style>
</head>
<body>

    <?php if ($lockout_time > 0): ?>
        <div class="overlay" id="lockoutOverlay">
            You are locked out. Please try again in <pre> </pre> <span id="remainingTime"><?php echo $lockout_time; ?></span> <pre> </pre>seconds.
        </div>
    <?php endif; ?>

    <br><br><br><br><br>
    <div class="container">
        <h1>Login Form</h1>
        <form action="" method="post">
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

            <br>

            <div class="button">
                <button type="submit">Login</button>
            </div>

            <p>
                Did you forget your password <a href="forgot_password.php" style="color:black;"><br>Reset Password </a>
            </p>
            <br>
            <p>
                Don't have an account? <a href="register.php" style="color:black; "><br>Create an Account</a>
            </p>
        </form>
    </div>
    
    <script src="assets/js/lockout.js"></script>
</body>
</html>

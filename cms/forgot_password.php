<?php
session_start();
$message = '';
$security_question = '';
$show_question = false;
$input = $_POST['input'] ?? ''; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $db_username = "root";
    $db_password = "";
    $dbname = "cms";

    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $security_answer = $_POST['security_answer'] ?? null;
    $new_password = $_POST['new_password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;

    if ($security_answer === null && $new_password === null) {
        $stmt = $conn->prepare("SELECT Uusername, security_question FROM users WHERE Uusername = ? OR email = ?");
        $stmt->bind_param("ss", $input, $input);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($username, $security_question);
            $stmt->fetch();
            $show_question = true;
        } else {
            echo "<script>alert('User not found.'); window.location.href = 'login.php';</script>";
            exit;
        }
        $stmt->close();
    }

    elseif ($security_answer && $new_password && $confirm_password) {
        if ($new_password !== $confirm_password) {
            echo "<script>alert('Passwords do not match.'); window.location.href = 'login.php';</script>";
            exit;
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("SELECT username FROM users WHERE (username = ? OR email = ?) AND security_answer = ?");
            $stmt->bind_param("sss", $input, $input, $security_answer);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $username = $row['username'];

                $update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update->bind_param("ss", $hashed_password, $username);
                $update->execute();

                echo "<script>alert('Password reset successfully!'); window.location.href = 'login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Invalid security answer.'); window.location.href = 'login.php';</script>";
                exit;
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/lr.css">
    <link rel="stylesheet" href="assets/css/forgot_password.css">
    <link rel="icon" href="assets/img/logo.png">
</head>
<body>
<div class="form-container">
    
    <form method="post" action="">
        <h2>Forgot Password</h2>
        <label>Username or Email:</label>
        <input type="text" name="input" value="<?= htmlspecialchars($input) ?>" required><br>

        <?php if ($show_question): ?>
            <label>Security Question:</label>
            <p><strong><?= htmlspecialchars($security_question) ?></strong></p>

            <label>Security Answer:</label>
            <input type="text" name="security_answer" required><br>

            <label>New Password:</label>
            <input type="password" name="new_password" required><br>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required><br>
        <?php endif; ?>

        <button type="submit"><?= $show_question ? 'Reset Password' : 'Next' ?></button>
        <a href="login.php"><button type="button">Cancel</button></a>
    </form>
</div>
</body>
</html>

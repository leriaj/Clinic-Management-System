<?php
session_start();

date_default_timezone_set('Asia/Manila');
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good morning!";
} elseif ($hour < 18) {
    $greeting = "Good afternoon!";
} else {
    $greeting = "Good evening!";
}

$conn = new mysqli("localhost", "root", "", "cms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href = '../../login.php';</script>";
    exit();
}

$doctors = [];
$doctorQuery = "SELECT id, Uusername, Ufirstname, Ulastname, email FROM users WHERE role = 'Doctor'";
$doctorResult = $conn->query($doctorQuery);
if ($doctorResult && $doctorResult->num_rows > 0) {
    while ($row = $doctorResult->fetch_assoc()) {
        $doctors[] = $row;
    }
}

$nurses = [];
$nurseQuery = "SELECT id, Uusername, Ufirstname, Ulastname, email FROM users WHERE role = 'Nurse'";
$nurseResult = $conn->query($nurseQuery);
if ($nurseResult && $nurseResult->num_rows > 0) {
    while ($row = $nurseResult->fetch_assoc()) {
        $nurses[] = $row;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QuickCare | Appointment</title>
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link rel="icon" href="../../assets/img/logo.png">

    <style>
        .table-container {
            display: flex;
            justify-content: center;
            gap: 50px;
        }

        th, tr{
            width: 200px;
        }
        
    </style>
</head>
<body>
    <header>
        <nav>
            <img src="../../assets/img/ogol.png" style="width: 120px; height: 55px; margin-left: 20px;" alt="Logo">
        </nav>
    </header>
                
    <main>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 900px; margin: 0 auto;">
                <div>
                    <span style="font-size:16px;"><?php echo $greeting; ?></span> <span style="font-size:18px;"><?php echo $_SESSION['Uusername']; ?> (<?php echo $_SESSION['role']; ?>)</span>
                </div>

                <div>
                    <a href="../../assets/php/signout.php" style="color:black; font-size: 15px; font-family: Comic Sans MS;"><u>Sign Out</u></a>
                </div>
            </div>
            <br>
            <div class="table-container">
                <table border="1" cellpadding="10" style="border-collapse:collapse; min-width:500px;">
                    <tr>
                        <th colspan="5" style="background:#e0e0e0; text-align:center;">Doctors</th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                    </tr>
                    <?php foreach ($doctors as $doc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['id']); ?></td>
                        <td><?php echo htmlspecialchars($doc['Uusername']); ?></td>
                        <td><?php echo htmlspecialchars($doc['Ufirstname']); ?></td>
                        <td><?php echo htmlspecialchars($doc['Ulastname']); ?></td>
                        <td><?php echo htmlspecialchars($doc['email']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <table border="1" cellpadding="8" style="border-collapse:collapse; min-width:300px;">
                    <tr>
                        <th colspan="5" style="background:#e0e0e0; text-align:center;">Nurses</th>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                    </tr>
                    <?php foreach ($nurses as $nurse): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nurse['id']); ?></td>
                        <td><?php echo htmlspecialchars($nurse['Uusername']); ?></td>
                        <td><?php echo htmlspecialchars($nurse['Ufirstname']); ?></td>
                        <td><?php echo htmlspecialchars($nurse['Ulastname']); ?></td>
                        <td><?php echo htmlspecialchars($nurse['email']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <br>
    </main>

</body>
</html>
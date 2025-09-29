<?php 

session_start();

if (!isset($_SESSION['Uusername']) || empty($_SESSION['Uusername'])) {
    echo "<script>alert('You must be logged in to access this page.'); window.location.href = '../login.php';</script>";
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Doctor') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href = 'index.php';</script>";
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<script>alert('Connection failed: " . $conn->connect_error . "');</script>";
    exit();
}

$Ufirstname = '';
$Ulastname = '';
$Uusername = '';
$first_name = $last_name = $age = $birthday = $contact_name = $contact_number = $reason = $medicine = '';
$patient_id = '';  

if (isset($_SESSION['Uusername'])) {
  $loggedInUsername = $_SESSION['Uusername'];
  $sql = "SELECT Ufirstname, Ulastname, Uusername FROM users WHERE Uusername = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $loggedInUsername);
  $stmt->execute();
  $stmt->bind_result($Ufirstname, $Ulastname, $Uusername);
  $stmt->fetch();
  $stmt->close();
}

if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $searchValue = $patient_id;

    $searchQuery = "SELECT patient_id, first_name, last_name, age, birthday, contact_name, contact_number, reason, medicine, billing_amount FROM patients WHERE patient_id = ?";
    $stmt = $conn->prepare($searchQuery);
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($patient_id, $first_name, $last_name, $age, $birthday, $contact_name, $contact_number, $reason, $medicine, $billing_amount);
        $stmt->fetch();
    }
    $stmt->close();
}   

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Profile</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .c1 {
      text-align: center;
      margin-top: 2%;
    }
    h1 {
      color: black;
    }
    .container {
      width: 80%;
      margin: auto;
    }
    label {
      display: block;
      margin-top: 10px;
    }
    input, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
    }
    .container-buttons {
      margin-top: 20px;
      text-align: center;
    }
    button {
      margin: 5px;
      padding: 10px 20px;
    }
    hr {
        border: 2px solid black;
        margin-top: 2%;
        width: 5%;
    }

  </style>
</head>
<body>
  <header>
        <nav>
            <a class="logo" href="#"><img src="../assets/img/ogol.png" style="width: 120px; height: 80px; margin-left: 20px;" alt="Logo"></a>
            <div class="mobile-menu">
              <div class="line1"></div>
              <div class="line2"></div>
              <div class="line3"></div>
            </div>
            <ul class="nav-list">
                <li><a href="dd.php" class="nav-link">Home</a></li>
                <li><a href="dd.php#announcement" class="nav-link">Announcements</a></li>
                <li><a href="ddpatientslist.php" class="nav-link">Patients List</a></li>
                <li><a href="pprof.php" class="nav-link">Patient's Profile</a></li>
                <li><a href="inventory.php" class="nav-link">Inventory</a></li>
                <li><a href="billing.php" class="nav-link">Payment</a></li>
                <li><a href="reports.php" class="nav-link">Reports</a></li>
                <li><a href="../assets/php/signout.php" class="nav-link">Signout</a></li>
            </ul>
        </nav>
    </header>

        <main>
            <div class="c1">
                <h1>Patient's Information</h1>

                <div class="container">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">

                    <label>Patient ID</label>
                    <input type="text" name="patient_id" id="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>" readonly>

                    <label>Given Name</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" <?php echo empty($first_name) ?  :  'readonly'; ?>>

                    <label>Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" <?php echo empty($last_name) ? : 'readonly'; ?>>

                    <label>Age</label>
                    <input type="text" name="age" id="age" value="<?php echo htmlspecialchars($age); ?>" <?php echo empty($age) ? : 'readonly'; ?>>

                    <label>Birthday</label>
                    <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($birthday); ?>" <?php echo empty($birthday) ? : 'readonly'; ?>>
                    
                    <center><hr></center>
                    <label>Parent/Guardian Full Name</label>
                    <input type="text" name="contact_name" id="contact_name" value="<?php echo htmlspecialchars($contact_name); ?>" <?php echo empty($contact_name) ? : 'readonly'; ?>>

                    <label>Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" <?php echo empty($contact_number) ? : 'readonly'; ?>>

                    <center><hr></center>
                    <label>Reason for Check-up</label>
                    <textarea name="reason" <?php echo empty($reason) ? : 'readonly'; ?>><?php echo htmlspecialchars($reason); ?></textarea>

                    <label>Possible Medicine</label>
                    <textarea name="medicine" <?php echo empty($medicine) ? : 'readonly'; ?>><?php echo htmlspecialchars($medicine); ?></textarea>
                    
                    <center><hr></center>
                    <label>Billing_amount</label>
                    <textarea name="billing_amount" <?php echo empty($billing_amount) ? : 'readonly'; ?>><?php echo htmlspecialchars($billing_amount); ?></textarea>

                    <center><hr></center>
                    <label>User Full Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($Ufirstname . ' ' . $Ulastname); ?>" readonly>

                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($Uusername); ?>" readonly>

                    <div class="form-buttons">
                        <a href="ddpatientslist.php"><button type="submit">FINISH</button></a>
                        <button type="button" onclick="window.print();">PRINT</button>
                    </div>
                </div>
            </div>
        </main>

        <script src="../assets/js/mobile-navbar.js"></script>
    </body>
</html>

<?php 

session_start();

if (!isset($_SESSION['Uusername']) || empty($_SESSION['Uusername'])) {
    echo "<script>alert('You must be logged in to access this page.'); window.location.href = '../login.php';</script>";
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Nurse') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href = '../login.php';</script>";
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

$searchValue = ''; 

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchValue = $_GET['search'];

    $searchQuery = "SELECT patient_id, first_name, last_name, age, birthday, contact_name, contact_number, reason, medicine FROM patients WHERE patient_id = ? OR first_name LIKE ?";
    $stmt = $conn->prepare($searchQuery);
    $searchValueLike = "%" . $searchValue . "%";
    $stmt->bind_param("ss", $searchValue, $searchValueLike);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($patient_id, $first_name, $last_name, $age, $birthday, $contact_name, $contact_number, $reason, $medicine);
        $stmt->fetch();
    } else {
        echo "<script>alert('Patient not found.');</script>";
    }
    $stmt->close();
}

if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $searchValue = $patient_id;

    $searchQuery = "SELECT patient_id, first_name, last_name, age, birthday, contact_name, contact_number, reason, medicine FROM patients WHERE patient_id = ?";
    $stmt = $conn->prepare($searchQuery);
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($patient_id, $first_name, $last_name, $age, $birthday, $contact_name, $contact_number, $reason, $medicine);
        $stmt->fetch();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $birthday = $_POST['birthday'];
    $contact_name = $_POST['contact_name'];
    $contact_number = $_POST['contact_number'];
    $reason = $_POST['reason'];
    $medicine = $_POST['medicine'];

    $updateSql = "UPDATE patients SET first_name = ?, last_name = ?, age = ?, birthday = ?, contact_name = ?, contact_number = ?, reason = ?, medicine = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssssssi", $first_name, $last_name, $age, $birthday, $contact_name, $contact_number, $reason, $medicine, $patient_id);

    if ($stmt->execute()) {
        echo "<script>alert('Updating patient profile is successful!'); window.location.href = 'pdpatientslist.php';</script>";
    } else {
        echo "<script>alert('Error updating patient information: " . $stmt->error . "');</script>";
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
  <link rel="stylesheet" href="../assets/css/pprof.css">
<body>
        <script>
            window.onload = function() {
                const overlay = document.getElementById("overlay");

                const isSearchSuccessful = <?php echo isset($patient_id) && !empty($patient_id) ? 'true' : 'false'; ?>;
                if (!isSearchSuccessful) {
                    overlay.style.display = "block";
                    document.body.classList.add("overlay-active");
                }
            };

            function closeOverlay() {
                const overlay = document.getElementById("overlay");
                overlay.style.display = "none";
                document.body.classList.remove("overlay-active");
            }

            <?php if (isset($patient_id) && !empty($patient_id)): ?>
                closeOverlay();
            <?php endif; ?>
        </script>
    <header>
        <nav>
            <a class="logo" href="#"><img src="../assets/img/ogol.png" style="width: 120px; height: 80px; margin-left: 20px;" alt="Logo"></a>
            <div class="mobile-menu">
              <div class="line1"></div>
              <div class="line2"></div>
              <div class="line3"></div>
            </div>
            <ul class="nav-list">
                <li><a href="pd.php" class="nav-link">Home</a></li>
                <li><a href="pd.php#announcement" class="nav-link">Announcements</a></li>
                <li><a href="pdpatientslist.php" class="nav-link">Patients List</a></li>
                <li><a href="pdpprof.php" class="nav-link">Patient's Profile</a></li>
                <li><a href="pdbilling.php" class="nav-link">Payment</a></li>
                <li><a href="../assets/php/signout.php" class="nav-link">Signout</a></li>
            </ul>
        </nav>
    </header>

        <main>
            <div class="c1">
                <h1>Patient's Information</h1>

                <div class="overlay" id="overlay">
                    <div class="overlay-content">
                        <h2>Search for Patient Information</h2>
                        <form method="get" action="pdpprof.php" id="search-form">
                            <label for="search">Search (ID or Name)</label>
                            <input type="text" id="search" name="search" placeholder="Enter ID or Name" value="<?php echo htmlspecialchars($searchValue ?? ''); ?>">
                            <button type="submit" name="search_button" onclick="closeOverlay()">Search</button>
                        </form>
                    </div>
                </div>

                <form method="post" action="pdpprof.php?patient_id=<?php echo $patient_id; ?>">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">

                    <label>Patient ID</label>
                    <input type="text" name="patient_id" id="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>" readonly>

                    <label>Given Name</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($first_name); ?>" <?php echo empty($first_name) ? : ''; ?>>

                    <label>Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($last_name); ?>" <?php echo empty($last_name) ? : ''; ?>>

                    <label>Age</label>
                    <input type="text" name="age" id="age" value="<?php echo htmlspecialchars($age); ?>" <?php echo empty($age) ? 'readonly' : ''; ?>>

                    <label>Birthday</label>
                    <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($birthday); ?>" <?php echo empty($birthday) ? : ''; ?>>
                    
                    <center><hr></center>
                    <label>Parent/Guardian Full Name</label>
                    <input type="text" name="contact_name" id="contact_name" value="<?php echo htmlspecialchars($contact_name); ?>" <?php echo empty($contact_name) ? : ''; ?>>

                    <label>Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" <?php echo empty($contact_number) ? : ''; ?>>

                    <center><hr></center>
                    <label>Reason for Check-up</label>
                    <textarea name="reason"> <?php echo empty($reason) ? : ''; ?><?php echo htmlspecialchars($reason); ?></textarea>

                    <label>Medicine</label>
                        <select name="medicine" id="medicine" style="width:100%">
                            <option value="">-- Select Medicine --</option>
                            <?php
                            $medicineQuery = "SELECT item_name FROM inventory WHERE quantity > 0";
                            $medicineResult = $conn->query($medicineQuery);
                            while ($row = $medicineResult->fetch_assoc()) {
                                $selected = (isset($medicine) && $medicine == $row['item_name']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['item_name']) . "' $selected>" . htmlspecialchars($row['item_name']) . "</option>";
                            }
                            ?>
                        </select>

                    <center><hr></center>
                    <label>User Full  Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($Ufirstname . ' ' . $Ulastname); ?>" readonly>

                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($Uusername); ?>" readonly>

                    <div class="form-buttons">
                        <button type="submit">UPDATE</button>
                        <button type="button" onclick="window.print();">PRINT</button>
                    </div>
                </form>
            </div>
        </main>

        <script src="../assets/js/mobile-navbar.js"></script>
    </body>
</html>

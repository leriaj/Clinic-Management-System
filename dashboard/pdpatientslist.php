<?php
session_start();

date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['Uusername']) || empty($_SESSION['Uusername'])) {
    header("Location: ../login.php");
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
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_GET['update_status'])) {
    $patient_id = $_GET['update_status'];
    $current_date = date('Y-m-d');

    $checkQuery = "SELECT patient_id FROM patients WHERE patient_id = '$patient_id'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult && $checkResult->num_rows > 0) {
        $updateQuery = "UPDATE patients SET status = 'Completed' WHERE patient_id = '$patient_id'";
        if ($conn->query($updateQuery) === TRUE) {
            echo "<script>alert('Status updated to Completed'); window.location.href = 'pdpatientslist.php';</script>";
        } else {
            echo "Error updating status: " . $conn->error;
        }
    } else {
        echo "<script>alert('Please complete all patient details before updating the status.'); window.location.href = 'pdpatientslist.php';</script>";
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST["first_name"];
    $middle_name = $_POST["middle_name"];
    $last_name = $_POST["last_name"];
    $birthday = $_POST["birthday"];
    $gender = $_POST["gender"];

    $dob = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($dob)->y;

    $contact_name = $_POST["contact_name"];
    $contact_number = $_POST["contact_number"];
    $address = $_POST["address"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $reason = $_POST["reason"];
    $completed_time = date('Y-m-d H:i:s');

    $sql = "INSERT INTO patients (first_name, middle_name, last_name, birthday, age, gender, contact_name, contact_number, address, reason, date, time) 
        VALUES ('$first_name', '$middle_name', '$last_name', '$birthday', '$age', '$gender', '$contact_name', '$contact_number', '$address', '$reason', '$date', '$time')";
        
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Patient registered successfully!'); window.location.href='pdpatientslist.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$totalPatientsQuery = "SELECT COUNT(*) AS total FROM patients";
$totalPatientsResult = $conn->query($totalPatientsQuery);
$totalPatients = $totalPatientsResult->fetch_assoc()['total'];

$incompletePatientsQuery = "
    SELECT COUNT(*) AS incomplete 
    FROM patients 
    WHERE first_name IS NULL OR first_name = '' 
       OR last_name IS NULL OR last_name = '' 
       OR birthday IS NULL 
       OR age IS NULL 
       OR contact_name IS NULL OR contact_name = '' 
       OR contact_number IS NULL OR contact_number = '' 
       OR address IS NULL OR address = '' 
       OR reason IS NULL OR reason = '' 
       OR medicine IS NULL OR medicine = ''
";
$incompletePatientsResult = $conn->query($incompletePatientsQuery);
$incompletePatients = $incompletePatientsResult->fetch_assoc()['incomplete'];

$completedPatientsQuery = "SELECT COUNT(*) AS completed FROM patients WHERE status = 'Completed'";
$completedPatientsResult = $conn->query($completedPatientsQuery);
$completedPatients = $completedPatientsResult->fetch_assoc()['completed'];

$today = date('Y-m-d');
$patientsTodayQuery = "SELECT COUNT(*) AS today FROM patients WHERE DATE(created_at) = '$today'";
$patientsTodayResult = $conn->query($patientsTodayQuery);
$patientsToday = $patientsTodayResult->fetch_assoc()['today'];

$searchQuery = "SELECT patient_id, first_name, last_name, age, birthday, contact_name, contact_number, reason, medicine FROM patients WHERE patient_id = ?";
$stmt = $conn->prepare($searchQuery);
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$stmt->store_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QuickCare | Doctor Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/pl.css" />
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <link rel="stylesheet" href="../assets/css/search.css" />
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
                <li><a href="pd.php" class="nav-link">Home</a></li>
                <li><a href="#announcement" class="nav-link">Announcements</a></li>
                <li><a href="pdpatientslist.php" class="nav-link">Patients List</a></li>
                <li><a href="pdpprof.php" class="nav-link">Patient's Profile</a></li>
                <li><a href="billing.php" class="nav-link">Payment</a></li>
                <li><a href="../assets/php/signout.php" class="nav-link">Signout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="hero">
            <div class="hero-content">
                <h1>Efficient care, seamless experience</h1>
                <p>Empowering healthcare with smart technology.</p>
                <button class="glow-button" onclick="openPopup()">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>Click Here!!!
                </button>
            </div>
        </section>

        <div class="popup-wrapper" id="popupWrapper" style="display: none;">
            <div class="popup" id="popupForm">
                <button class="btn-close" onclick="closePopup()">X</button>
                <form action="pdpatientslist.php" method="post">
                    <div id="step1">
                        <h2>Name of Patient</h2>
                        <div class="input-group">
                            <label>First Name:</label>
                            <input type="text" name="first_name" required>
                        </div>
                        <div class="input-group">
                            <label>Middle Name:</label>
                            <input type="text" name="middle_name">
                        </div>
                        <div class="input-group">
                            <label>Last Name:</label>
                            <input type="text" name="last_name" required>
                        </div>
                        <button type="button" class="btn-next" onclick="nextStep(2)">Next</button>
                    </div>

                    <div id="step2" style="display: none;">
                        <h2>Patient Information</h2>
                        <div class="input-group">
                            <label>Birthday:</label>
                            <input type="date" name="birthday" id="birthday" required oninput="calculateAge()">
                        </div>
                        <div class="input-group">
                            <label>Gender:</label>
                            <select name="gender" class="filter-select" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <button type="button" class="btn-next" onclick="nextStep(3)">Next</button>
                        <button type="button" class="btn-back" onclick="nextStep(1)">Back</button>
                    </div>

                    <div id="step3" style="display: none;">
                        <h2>Reason for Appointment</h2>
                        <div class="input-group">
                            <label>Reason:</label>
                            <textarea name="reason" id="reason" placeholder="Enter the reason for your appointment..." rows="4" required></textarea>
                        </div>
                        <div class="input-group">
                            <label>Date:</label>
                        <input type="date" name="date" id="date" required>
                        </div>
                        <div class="input-group">
                            <label>Time:</label>
                            <input type="time" name="time" id="time" required>
                        </div>
                        
                        <button type="button" class="btn-next" onclick="nextStep(4)">Next</button>
                        <button type="button" class="btn-back" onclick="nextStep(2)">Back</button>
                    </div>

                    <div id="step4" style="display: none;">
                        <h2>Parents/Guardian Name</h2>
                        <div class="input-group">
                            <label>Full Name:</label>
                            <input type="text" name="contact_name" required>
                        </div>
                        <div class="input-group">
                            <label>Contact Number:</label>
                            <input type="text" name="contact_number" placeholder="+63 123 456 789" required>
                        </div>
                        <button type="button" class="btn-next" onclick="nextStep(5)">Next</button>
                        <button type="button" class="btn-back" onclick="nextStep(3)">Back</button>
                    </div>

                    <div id="step5" style="display: none;">
                        <h2>Address</h2>
                        <div class="input-group">
                            <label>Complete Address:</label>
                            <input type="text" name="address" placeholder="House Number/City" required>
                        </div>
                        <button type="submit" class="btn-submit">Submit</button>
                        <button type="button" class="btn-back" onclick="nextStep(4)">Back</button>
                    </div>
                </form>
            </div>
        </div>
    

        <div class="patient-stats">
            <div class="stat-card">
                <h3>Total Patients</h3>
                <p><?php echo $totalPatients; ?></p>
            </div>
            <div class="stat-card">
                <h3>Incomplete Information Patients</h3>
                <p><?php echo $incompletePatients; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Patients Today</h3>
                <p><?php echo $patientsToday; ?></p>
            </div>
            <div class="stat-card">
                <h3>Completed Patients</h3>
                <p><?php echo $completedPatients; ?></p>
            </div>
        </div>

        <div class="c1">
            <h1>Patient List</h1>
            <div class="search-bar">
                <form method="GET" action="pdpatientslist.php">
                    <input type="text" name="search" placeholder="Search by Name, ID, or Status" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <button onclick="showPending()" class="pending">Pending</button>
            <button onclick="showCompleted()" class="completed">Completed</button>

            <?php
                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

                $sql = "SELECT patient_id, first_name, middle_name, last_name, birthday, age, gender, contact_name, contact_number, address, reason, medicine, created_at, date, time, reservation_type, status 
                        FROM patients 
                        WHERE first_name LIKE '%$search%' 
                        OR last_name LIKE '%$search%' 
                        OR middle_name LIKE '%$search%' 
                        OR reason LIKE '%$search%' 
                        OR patient_id LIKE '%$search%' 
                        OR status LIKE '%$search%' 
                        ORDER BY created_at ASC";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $pendingRows = '';
                    $completedRows = '';
                    $hasPendingResults = false;
                    $hasCompletedResults = false;
                
                    while ($row = $result->fetch_assoc()) {
                        $isComplete = (
                            !empty($row["first_name"]) &&
                            !empty($row["last_name"]) &&
                            !empty($row["birthday"]) &&
                            !empty($row["age"]) &&
                            !empty($row["contact_name"]) &&
                            !empty($row["contact_number"]) &&
                            !empty($row["address"]) &&
                            !empty($row["reason"]) &&
                            !empty($row["medicine"])
                        );

                        $actionLabel = (strtolower($row["status"]) === "pending") ? "Update Profile" : "View Profile";
                        $statusCell = "<td class='status-" . strtolower($row["status"]) . "'>";

                        if (strtolower($row["status"]) === "pending" && $isComplete) {
                            $statusCell .= "<a href='pdpatientslist.php?update_status={$row["patient_id"]}' style='color:green;text-decoration:underline;cursor:pointer;'>Pending</a>";
                        } else {
                            $statusCell .= ucfirst($row["status"]);
                        }
                        $statusCell .= "</td>";

                        $rowHtml = "<tr>
                            <td>{$row["patient_id"]}</td>
                            <td>{$row["first_name"]}</td>
                            <td>{$row["middle_name"]}</td>
                            <td>{$row["last_name"]}</td>
                            <td>{$row["birthday"]}</td>
                            <td>{$row["age"]}</td>
                            <td>{$row["gender"]}</td>
                            <td>{$row["reason"]}</td>
                            <td>{$row["medicine"]}</td>
                            <td>{$row["contact_name"]}</td>
                            <td>{$row["contact_number"]}</td>
                            <td>{$row["address"]}</td>
                            <td>" . (!empty($row["reservation_type"]) ? $row["reservation_type"] : "Walk-In") . "</td>
                            <td>{$row["created_at"]}</td>
                            <td>{$row["date"]}</td>
                            <td>{$row["time"]}</td>
                            $statusCell
                            <td><a href='pdpprof.php?patient_id={$row["patient_id"]}' class='update-btn'>{$actionLabel}</a></td>
                        </tr>";

                        if (strtolower($row["status"]) === "pending") {
                            $pendingRows .= $rowHtml;
                            $hasPendingResults = true;
                        } else {
                            $completedRows .= $rowHtml;
                            $hasCompletedResults = true;
                        }
                    }
                

                    $pendingTableStyle = $hasPendingResults ? "display: table;" : "display: none;";
                    $completedTableStyle = $hasCompletedResults ? "display: table;" : "display: none;";
                
                    echo "<table id='pending-table' class='pending-table' style='$pendingTableStyle'>
                        <tr><th>ID</th><th>Given Name</th><th>Middle Name</th><th>Last Name</th><th>Birthday</th><th>Age</th><th>Gender</th><th>Reason</th><th>Medicine</th><th>Parents/Guardian Full Name</th><th>Contact Number</th><th>Address</th><th>Reservation Type</th><th>Created At</th><th>Date</th><th>Time</th><th>Status</th><th>Action Taken</th></tr>
                        $pendingRows
                    </table>";
                
                    echo "<table id='completed-table' class='completed-table' style='$completedTableStyle'>
                        <tr><th>ID</th><th>Given Name</th><th>Middle Name</th><th>Last Name</th><th>Birthday</th><th>Age</th><th>Gender</th><th>Reason</th><th>Medicine</th><th>Parents/Guardian Full Name</th><th>Contact Number</th><th>Address</th><th>Reservation Type</th><th>Created At</th><th>Date</th><th>Time</th><th>Status</th><th>Action Taken</th></tr>
                        $completedRows
                    </table>";
                } else {
                    echo "<div style='text-align: center; margin-top: 50px;'>
                            <h2 style='color: #ff4d4d; font-size: 28px; font-weight: bold;'>No Patients Found</h2>
                            <p style='color: #666; font-size: 20px;'>Please check back later or add new patient records to the system.</p>
                          </div>";
                }
                ?>
        </div>
    </main>

    <script src="assets/js/animation.js"></script>
    <script>
        function showPending() {
            const pendingTable = document.getElementById('pending-table');
            const completedTable = document.getElementById('completed-table');

            if (pendingTable) pendingTable.style.display = 'table';
            if (completedTable) completedTable.style.display = 'none';
        }

        function showCompleted() {
            const pendingTable = document.getElementById('pending-table');
            const completedTable = document.getElementById('completed-table');

            if (completedTable) completedTable.style.display = 'table';
            if (pendingTable) pendingTable.style.display = 'none';
        }

        function openPopup() {
            document.getElementById('popupWrapper').style.display = 'flex';
        }
        function closePopup() {
            document.getElementById('popupWrapper').style.display = 'none';
        }

        function nextStep(step) {
            for (let i = 1; i <= 5; i++) {
                document.getElementById('step' + i).style.display = 'none';
            }
            document.getElementById('step' + step).style.display = 'block';
        }

        function calculateAge() {
            var birthday = document.getElementById('birthday').value;
            var dob = new Date(birthday);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            document.getElementById('age').value = age;
        }


        window.onload = function () {
            const now = new Date();
            

            const dateField = document.getElementById('date');
            dateField.value = now.toISOString().split('T')[0]; 


            const timeField = document.getElementById('time');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            timeField.value = `${hours}:${minutes}`;
        };
    </script>
    </main>
</body>
</html>
<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cms";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

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

    $sql = "INSERT INTO patients (first_name, middle_name, last_name, birthday, age, gender, contact_name, contact_number, address, reason, date, time) 
        VALUES ('$first_name', '$middle_name', '$last_name', '$birthday', '$age', '$gender', '$contact_name', '$contact_number', '$address', '$reason', '$date', '$time')";
        
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Patient registered successfully!'); window.location.href='appointment.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>QuickCare | Appointment</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="../assets/css/main.css" />
    <link rel="icon" href="../assets/img/logo.png">
</head>
<body>
    <header>
        <nav>
            <a class="logo" href="#"><img src="../assets/img/ogol.png" style="width: 120px; height: 80px; margin-left: 20px;" alt="Logo"></a>
            <ul class="nav-list">
              <li><a href="index.php" class="nav-link">Home</a></li>
              <li><a href="index.php#aboutus" class="nav-link">About Us</a></li>
              <li><a href="index.php#conta" class="nav-link">Contacts</a></li>
              <li><a href="index.php#faqs" class="nav-link">FAQs</a></li>
              <li><a href="index.php#feedback" class="nav-link">Feedback</a></li>
              <li><a href="index.php#team" class="nav-link">Team</a></li>
          </ul>
        </nav>
    </header>

    <main id="main-content">
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
                <form action="ddpatientslist.php" method="post">
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
                            <label>Reservation Type:</label>
                            <select name="gender" class="filter-select" required>
                                <option value="Male">Appointment</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Date:</label>
                            <input type="date" name="date" id="date" required onchange="updateReason()">
                        </div>
                        <div class="input-group">
                            <label>Time:</label>
                            <input type="time" name="time" id="time" required onchange="updateReason()">
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

        <section class="stats">
            <div class="container">
                <div class="stat-item">
                    <h3 id="doctors-count">0</h3>
                    <p>Doctors at Work</p>
                </div>
                <div class="stat-item">
                    <h3 id="nurses-count">0</h3>
                    <p>Nurses at Work</p>
                </div>
                <div class="stat-item">
                    <h3 id="patients-count">0</h3>
                    <p>Number of Patients</p>
                </div>
                
                <div class="stat-item">
                    <h4 id="working-days">Monday - Saturday</h4>
                    <h5 id="working-hours">8:00AM - 6:00PM</h5>
                    <p>Working Hours</p>
                </div>
            </div>
        </section>
    </main>
    
    <script>
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
    </script>
    <script src="../assets/js/animation.js"></script>
    <script src="../assets/js/mobile-navbar.js"></script>
    <script src="../assets/js/patient_count.js"></script>
    
</body>
</html>
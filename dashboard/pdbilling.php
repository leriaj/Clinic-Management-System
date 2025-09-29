<?php
session_start();

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

$patient = null;

if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];
    $sql = "SELECT * FROM patients WHERE patient_id = '$patient_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    } else {
        echo "<script>alert('Patient not found.'); window.location.href = 'billing.php';</script>";
        exit();
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
        $search = $conn->real_escape_string($_POST['search']);
        $sql = "SELECT * FROM patients WHERE patient_id = '$search' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' ORDER BY created_at DESC LIMIT 1";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $patient = $result->fetch_assoc();
        } else {
            echo "<script>alert('No patient found with the given ID or name.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickCare | Billing</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/billing.css">
    <link rel="stylesheet" href="../assets/css/medicine.css">
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
                <li><a href="billing.php" class="nav-link">Payment</a></li>
                <li><a href="../assets/php/signout.php" class="nav-link">Signout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <?php if (!$patient): ?>
            <div class="search-container">
                <h2>Search for a Patient</h2>
                <form method="POST" action="billing.php">
                    <input type="text" name="search" placeholder="Enter patient ID or name" required>
                    <button type="submit">Search</button>
                </form>
            </div>
        <?php else: ?>
            <div class="medicine-container">
                <h1><?php echo $patient['first_name'] . " " . $patient['last_name']; ?></h1>
                <p><strong>Reason for Appointment:</strong> <?php echo $patient['reason']; ?></p>
                <p><strong>Contact Number:</strong> <?php echo $patient['contact_number']; ?></p>
                <p><strong>Address:</strong> <?php echo $patient['address']; ?></p>
            </div>

            <div class="medicine-container">
                <h2>Medicine Purchase</h2>
                <form id="medicine-form">
                    <div class="input-group">
                        <label for="medicine">Select Medicine:</label>
                        <select name="medicine" id="medicine" onchange="updateMedicinePrice()" required>
                            <option value="">-- Select Medicine --</option>
                            <?php
                            $medicineQuery = "SELECT * FROM inventory WHERE quantity > 0";
                            $medicineResult = $conn->query($medicineQuery);
                            while ($medicine = $medicineResult->fetch_assoc()) {
                                echo "<option value='{$medicine['item_id']}' data-price='{$medicine['unit_price']}' data-stock='{$medicine['quantity']}'>
                                        {$medicine['item_name']} (Stock: {$medicine['quantity']})
                                      </option>";
                            }
                            ?>
                        </select>
                        <div id="medicine-error" class="error"></div>
                    </div>
                    <div class="input-group">
                        <label for="quantity">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" min="1" value="1" onchange="updateTotalPrice()" required>
                    </div>
                    <div class="input-group">
                        <label for="medicine-total">Total Price:</label>
                        <input type="text" id="medicine-total" value="₱0" readonly>
                    </div>
                </form>
            </div>

            <div class="medicine-container">
                <form action="../assets/php/process_billing.php" method="post">
                    <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
                    <input type="hidden" name="selected_medicines" id="selected-medicines-data">
                    <div class="input-group">
                        <label for="amount">Billing Amount:</label>
                        <input type="number" name="amount" id="amount" value="350" readonly>
                    </div>
                    <div class="input-group">
                        <label for="payment_status">Payment Status:</label>
                        <select name="payment_status" id="payment_status" required>
                            <option value="Paid">Paid</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit" onclick="prepareMedicineData()">Submit Billing</button>
                </form>
            </div>
        <?php endif; ?>
    </main>

    <script>
        let selectedMedicines = [];
        let baseBillingAmount = 350;

        function updateMedicinePrice() {
            const medicineSelect = document.getElementById('medicine');
            const selectedOption = medicineSelect.options[medicineSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
            const quantityInput = document.getElementById('quantity');
            const errorDiv = document.getElementById('medicine-error');

            errorDiv.textContent = '';

            if (quantityInput.value > stock) {
                errorDiv.textContent = 'Invalid selection or quantity exceeds stock.';
                quantityInput.value = stock;
            }

            updateTotalPrice();
        }

        function updateTotalPrice() {
            const medicineSelect = document.getElementById('medicine');
            const selectedOption = medicineSelect.options[medicineSelect.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const quantity = parseInt(document.getElementById('quantity').value) || 0;

            const totalPrice = price * quantity;
            document.getElementById('medicine-total').value = `₱${totalPrice.toFixed(2)}`;
            document.getElementById('amount').value = (baseBillingAmount + totalPrice).toFixed(2);
        }

        function prepareMedicineData() {
            const medicineSelect = document.getElementById('medicine');
            const selectedOption = medicineSelect.options[medicineSelect.selectedIndex];
            const medicineId = selectedOption.value;
            const quantity = parseInt(document.getElementById('quantity').value);

            if (medicineId && quantity > 0) {
                selectedMedicines.push({ id: medicineId, quantity: quantity });
            }

            const medicinesData = JSON.stringify(selectedMedicines);
            document.getElementById('selected-medicines-data').value = medicinesData;
        }
    </script>
</body>
</html>
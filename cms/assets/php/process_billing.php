<?php



$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $selected_medicines = json_decode($_POST['selected_medicines'], true);
    $payment_status = $_POST['payment_status'];


    if (!empty($selected_medicines)) {
    foreach ($selected_medicines as $med) {
    $item_id = $med['id'];
    $quantity = $med['quantity'];
    $sale_date = date('Y-m-d');

    $itemNameResult = $conn->query("SELECT item_name FROM inventory WHERE item_id = '$item_id'");
    $itemNameRow = $itemNameResult->fetch_assoc();
    $item_name = $itemNameRow['item_name'];

    $conn->query("INSERT INTO sales (item_name, quantity, sale_date) VALUES ('$item_name', '$quantity', '$sale_date')");
    }

    foreach ($selected_medicines as $medicine) {
        $medicine_id = $medicine['id'];
        $quantity = $medicine['quantity'];

        $updateStockQuery = "UPDATE inventory SET quantity = quantity - ? WHERE item_id = ? AND quantity >= ?";
        $stmt = $conn->prepare($updateStockQuery);
        $stmt->bind_param("iii", $quantity, $medicine_id, $quantity);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            echo "<script>alert('Failed to deduct stock for medicine ID: $medicine_id. Not enough stock.');</script>";
            exit();
        }

        $checkQty = $conn->query("SELECT item_name, quantity FROM inventory WHERE item_id = '$medicine_id'");
        $row = $checkQty->fetch_assoc();
        if (strtolower($row['item_name']) === 'paracetamol' && $row['quantity'] < 50) {

            $message = "Stock for Paracetamol is low: only " . $row['quantity'] . " left!";
            $conn->query("INSERT INTO notifications (user_id, message) VALUES (0, '$message')");
        }

    }

    $billingQuery = "INSERT INTO billing (patient_id, payment_status, created_at) VALUES (?, ?, NOW())
                     ON DUPLICATE KEY UPDATE payment_status = ?";
    $stmt = $conn->prepare($billingQuery);
    $stmt->bind_param("iss", $patient_id, $payment_status, $payment_status);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Transaction successful. Payment status updated.'); window.location.href = '../../dashboard/billing.php';</script>";
    } else {
        echo "<script>alert('Failed to process billing.');</script>";
    }
    }
}
?>
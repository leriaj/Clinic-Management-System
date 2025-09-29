<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$medicine = $_GET['medicine'] ?? '';
if (empty($medicine)) {
    http_response_code(400);
    echo json_encode(['error' => 'No medicine specified']);
    exit();
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last3Days = date('Y-m-d', strtotime('-3 days'));
$lastWeek = date('Y-m-d', strtotime('-7 days'));
$lastMonth = date('Y-m-d', strtotime('-30 days'));

$query = "SELECT 
    SUM(CASE WHEN DATE(updated_at) = ? THEN quantity ELSE 0 END) AS today,
    SUM(CASE WHEN DATE(updated_at) = ? THEN quantity ELSE 0 END) AS yesterday,
    SUM(CASE WHEN DATE(updated_at) BETWEEN ? AND ? THEN quantity ELSE 0 END) AS last3Days,
    SUM(CASE WHEN DATE(updated_at) BETWEEN ? AND ? THEN quantity ELSE 0 END) AS lastWeek,
    SUM(CASE WHEN DATE(updated_at) BETWEEN ? AND ? THEN quantity ELSE 0 END) AS lastMonth
    FROM inventory WHERE LOWER(item_name) = LOWER(?)";

    
$stmt = $conn->prepare($query);
$stmt->bind_param(
    "sssssssss", 
    $today,
    $yesterday,
    $last3Days, $today,
    $lastWeek, $today,
    $lastMonth, $today,
    $medicine
);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $data = $result->fetch_assoc();
    if (!$data) {
        $data = [
            'today' => 0,
            'yesterday' => 0,
            'last3Days' => 0,
            'lastWeek' => 0,
            'lastMonth' => 0
        ];
    }
    echo json_encode($data);
} else {
    error_log("Query Error: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data']);
}

function notifyLowStock($conn, $item_id, $item_name, $quantity) {
    if ($quantity < 50) {
        $users_query = "SELECT id FROM users WHERE role IN ('Doctor', 'Nurse')";
        $users_result = $conn->query($users_query);

        while ($user = $users_result->fetch_assoc()) {
            $user_id = $user['id'];
            // Prevent duplicate notifications for the same item and user
            $check_query = "SELECT id FROM notifications WHERE user_id='$user_id' AND type='low_stock' AND message LIKE '%(ID: $item_id)%'";
            $check_result = $conn->query($check_query);
            if ($check_result->num_rows == 0) {
                $escaped_item_name = $conn->real_escape_string($item_name);
                $message = "The stock for item '$escaped_item_name' (ID: $item_id) is low. Only $quantity left in inventory.";
                $escaped_message = $conn->real_escape_string($message);
                $conn->query("INSERT INTO notifications (user_id, message, type, created_at) VALUES ('$user_id', '$escaped_message', 'low_stock', NOW())");
            }
        }
    }
}
?>
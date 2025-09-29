<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT item_id, item_name, quantity, unit_price, category, type_of_medicine, storage_location, oldest_expiration_date, latest_expiration_date 
            FROM inventory 
            WHERE item_name LIKE '%$search%' OR item_id LIKE '%$search%' 
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $item]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching items found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No search query provided."]);
}

$conn->close();
?>
<?php
session_start();
$conn = new mysqli("localhost", "root", "", "cms");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notification_id");
    echo "Notification marked as read.";
}

?>


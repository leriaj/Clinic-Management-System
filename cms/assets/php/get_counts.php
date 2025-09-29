<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cms";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

$patient_count_query = "SELECT COUNT(*) AS patient_count FROM patients";
$patient_result = $conn->query($patient_count_query);

if (!$patient_result) {
    echo json_encode(["error" => "Patient query failed: " . $conn->error]);
    exit();
}

$patient_count = $patient_result->fetch_assoc()["patient_count"] ?? 0;

$doctor_count_query = "SELECT COUNT(*) AS doctor_count FROM users WHERE role = 'Doctor'";
$doctor_result = $conn->query($doctor_count_query);

if (!$doctor_result) {
    echo json_encode(["error" => "Doctor query faile    d: " . $conn->error]);
    exit();
}

$doctor_count = $doctor_result->fetch_assoc()["doctor_count"] ?? 0;

$nurse_count_query = "SELECT COUNT(*) AS nurse_count FROM users WHERE role = 'Nurse'";
$nurse_result = $conn->query($nurse_count_query);

if (!$nurse_result) {
    echo json_encode(["error" => "Nurse query failed: " . $conn->error]);
    exit();
}

$nurse_count = $nurse_result->fetch_assoc()["nurse_count"] ?? 0;

echo json_encode([
    "patient_count" => $patient_count,
    "doctor_count" => $doctor_count,
    "nurse_count" => $nurse_count
]);

$conn->close();
?>
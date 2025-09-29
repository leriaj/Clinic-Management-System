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
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $pendingRows = '';
    $completedRows = '';

    while ($row = $result->fetch_assoc()) {
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
            <td class='status-" . strtolower($row["status"]) . "'>" . ucfirst($row["status"]) . "</td>
            <td><a href='completed.php?patient_id={$row["patient_id"]}' class='update-btn'>View Profile</a></td>
        </tr>";

        if (strtolower($row["status"]) === "pending") {
            $pendingRows .= $rowHtml;
        } else {
            $completedRows .= $rowHtml;
        }
    }

    echo "<table id='pending-table' class='pending-table' style='" . (empty($search) || strtolower($search) === "pending" ? "display: table;" : "display: none;") . "'>
        <tr><th>ID</th><th>Given Name</th><th>Middle Name</th><th>Last Name</th><th>Birthday</th><th>Age</th><th>Gender</th><th>Reason</th><th>Medicine</th><th>Parents/Guardian Full Name</th><th>Contact Number</th><th>Address</th><th>Reservation Type</th><th>Created At</th><th>Date</th><th>Time</th><th>Status</th><th>Action Taken</th></tr>
        $pendingRows
    </table>";

    echo "<table id='completed-table' class='completed-table' style='" . (strtolower($search) === "completed" ? "display: table;" : "display: none;") . "'>
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
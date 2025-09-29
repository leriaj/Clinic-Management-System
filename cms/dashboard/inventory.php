<?php

session_start();

if (!isset($_SESSION['Uusername']) || empty($_SESSION['Uusername'])) {
    echo "<script>alert('You must be logged in to access this page.'); window.location.href = '../login.php';</script>";
    exit();
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Doctor') {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href = 'index.php';</script>";
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

function notifyLowStock($conn, $item_id, $item_name, $quantity) {
    if ($quantity < 50) {
        $users_query = "SELECT id FROM users";
        $users_result = $conn->query($users_query);

        if ($users_result && $users_result->num_rows > 0) {
            while ($user = $users_result->fetch_assoc()) {
                $user_id = $user['id'];

                $escaped_item_name = $conn->real_escape_string($item_name);
                $message = "The stock for item '$escaped_item_name' (ID: $item_id) is low. Only $quantity left in inventory.";
                $escaped_message = $conn->real_escape_string($message);

                $notification_query = "INSERT INTO notifications (user_id, message, type) 
                                       VALUES ('$user_id', '$escaped_message', 'low_stock')";
                if (!$conn->query($notification_query)) {
                    error_log("Error inserting notification: " . $conn->error);
                }
            }
        } else {
            error_log("No users found to notify.");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $brand_name = $_POST['brand_name'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $category = $_POST['category'];
    $type_of_medicine = $_POST['type_of_medicine'];

    $new_expiration_date = date('Y-m-d', strtotime('+5 years'));

    $check_query = "SELECT * FROM inventory WHERE item_name = '$item_name'";
    $check_result = $conn->query($check_query);

    if ($check_result->num_rows > 0) {
        $item = $check_result->fetch_assoc();
        $current_quantity = $item['quantity'];
        $current_oldest = $item['oldest_expiration_date'];
        $current_latest = $item['latest_expiration_date'];

        $updated_quantity = $current_quantity + $quantity;
        $updated_latest = max($current_latest, $new_expiration_date);

        $updated_oldest = $current_oldest;
        if (strtotime($current_oldest) < time()) {
            $updated_oldest = $new_expiration_date;
        }

        $medicine_count_query = "SELECT COUNT(DISTINCT item_name) AS medicine_count FROM inventory WHERE storage_location LIKE 'Shelf%'";
        $medicine_count_result = $conn->query($medicine_count_query);
        $medicine_count = $medicine_count_result->fetch_assoc()['medicine_count'];
        $shelf_number = ceil(($medicine_count + 1) / 5);
        $storage_location = "Shelf " . chr(64 + $shelf_number);

        $update_query = "UPDATE inventory 
                         SET quantity = $updated_quantity, 
                             oldest_expiration_date = '$updated_oldest', 
                             latest_expiration_date = '$updated_latest', 
                             storage_location = '$storage_location' 
                         WHERE item_name = '$item_name'";
        if ($conn->query($update_query)) {
            echo "<script>alert('Quantity and expiration dates updated successfully.'); window.location.href = 'inventory.php';</script>";
        } else {
            echo "<script>alert('Error updating item: " . $conn->error . "');</script>";
        }
    } else {
        $medicine_count_query = "SELECT COUNT(DISTINCT item_name) AS medicine_count FROM inventory WHERE storage_location LIKE 'Shelf%'";
        $medicine_count_result = $conn->query($medicine_count_query);
        $medicine_count = $medicine_count_result->fetch_assoc()['medicine_count'];
        $shelf_number = ceil(($medicine_count + 1) / 5);
        $storage_location = "Shelf " . chr(64 + $shelf_number);

        $add_query = "INSERT INTO inventory (brand_name, item_name, quantity, unit_price, category, type_of_medicine, storage_location, oldest_expiration_date, latest_expiration_date) 
                      VALUES ('$brand_name','$item_name', '$quantity', '$unit_price', '$category', '$type_of_medicine', '$storage_location', '$new_expiration_date', '$new_expiration_date')";
        if ($conn->query($add_query)) {
            echo "<script>alert('Item added successfully.'); window.location.href = 'inventory.php';</script>";
        } else {
            echo "<script>alert('Error adding item: " . $conn->error . "');</script>";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $item_id = $_POST['item_id'];
    $remove_quantity = $_POST['remove_quantity'];

    $fetch_query = "SELECT quantity, item_name FROM inventory WHERE item_id = '$item_id'";
    $fetch_result = $conn->query($fetch_query);

    if ($fetch_result->num_rows > 0) {
        $item = $fetch_result->fetch_assoc();
        $current_quantity = $item['quantity'];
        $item_name = $item['item_name'];

        $new_quantity = $current_quantity - $remove_quantity;

        if ($new_quantity > 0) {

            $update_query = "UPDATE inventory SET quantity = '$new_quantity' WHERE item_id = '$item_id'";
            if ($conn->query($update_query)) {
                notifyLowStock($conn, $item_id, $item_name, $new_quantity);
                echo "<script>alert('Quantity updated successfully.'); window.location.href = 'inventory.php';</script>";
            } else {
                echo "<script>alert('Error updating quantity: " . $conn->error . "');</script>";
            }
        } elseif ($new_quantity <= 0) {
            $delete_query = "DELETE FROM inventory WHERE item_id = '$item_id'";
            if ($conn->query($delete_query)) {
                echo "<script>alert('Item removed from inventory.'); window.location.href = 'inventory.php';</script>";
            } else {
                echo "<script>alert('Error removing item: " . $conn->error . "');</script>";
            }
        }
    } else {
        echo "<script>alert('Item not found.');</script>";
    }
}

$search = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "SELECT item_id, brand_name, item_name, quantity, unit_price, category, 
                   oldest_expiration_date, 
                   latest_expiration_date, 
                   type_of_medicine, storage_location 
            FROM inventory 
            WHERE item_name LIKE '%$search%' 
               OR category LIKE '%$search%' 
               OR type_of_medicine LIKE '%$search%' 
               OR storage_location LIKE '%$search%' 
               OR item_id LIKE '%$search%'";
} else {
   $sql = "SELECT item_id, brand_name, item_name, quantity, unit_price, category, 
            oldest_expiration_date, 
            latest_expiration_date, 
            type_of_medicine, storage_location 
         FROM inventory 
        WHERE item_name LIKE '%$search%' OR category LIKE '%$search%' OR type_of_medicine LIKE '%$search%' 
        GROUP BY item_id, brand_name, item_name, quantity, unit_price, category, type_of_medicine, storage_location, oldest_expiration_date, latest_expiration_date";

}

$result = $conn->query($sql);

if (!$result) {
    echo "<script>alert('Error executing query: " . $conn->error . "');</script>";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/inv.css">
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
                <li><a href="pprof.php" class="nav-link">Patient's Profile</a></li>`
                <li><a href="inventory.php" class="nav-link">Inventory</a></li>
                <li><a href="billing.php" class="nav-link">Payment</a></li>
                <li><a href="reports.php" class="nav-link">Reports</a></li>
                <li><a href="../assets/php/signout.php" class="nav-link">Signout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Inventory</h1>
        <div class="search-bar">
            <form method="GET" action="inventory.php">
                <input type="text" name="search" placeholder="Search by Name, Item ID, Category, Type of Medicine, or Storage Location" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div style="text-align: center;">
            <button id="open-add-item" class="overlay-button">Add Item</button>
            <button id="open-remove-item" class="overlay-button">Remove Item</button>
        </div>

        <div id="add-item-overlay" class="overlay">
            <div class="overlay-content">
                <button id="close-add-item" class="close-overlay">X</button>
                <h2>Add Item</h2>
                <form method="POST" action="inventory.php">
                    <label for="search_item">Search Item:</label><br>
                    <input type="text" id="search_item" name="search_item" placeholder="Search by Item ID or Name"><br><br>

                    <label for="item_name">Item Name:</label><br>
                    <input type="text" id="item_name" name="item_name" placeholder="Item Name" required><br><br>

                    <label for="quantity">Quantity:</label><br>
                    <input type="number" id="quantity" name="quantity" placeholder="Quantity" required><br><br>

                    <label for="unit_price">Unit Price:</label><br>
                    <input type="number" id="unit_price" step="0.01" name="unit_price" placeholder="Unit Price" required><br><br>

                    <label for="category">Category:</label><br>
                    <input type="text" id="category" name="category" placeholder="Category" required><br><br>

                    <label for="type_of_medicine">Type of Medicine:</label><br>
                    <select id="type_of_medicine" name="type_of_medicine" required>
                        <?php
                        $typeQuery = "SELECT DISTINCT type_of_medicine FROM inventory";
                        $typeResult = $conn->query($typeQuery);

                        if ($typeResult->num_rows > 0) {
                            while ($type = $typeResult->fetch_assoc()) {
                                echo "<option value='{$type['type_of_medicine']}'>{$type['type_of_medicine']}</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No types available</option>";
                        }
                        ?>
                    </select><br><br>

                    <label for="storage_location">Storage Location:</label><br>
                    <select id="storage_location" name="storage_location" required>
                        <?php
                        $locationQuery = "SELECT DISTINCT storage_location FROM inventory";
                        $locationResult = $conn->query($locationQuery);

                        if ($locationResult->num_rows > 0) {
                            while ($location = $locationResult->fetch_assoc()) {
                                echo "<option value='{$location['storage_location']}'>{$location['storage_location']}</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No storage locations available</option>";
                        }
                        ?>
                    </select><br><br>

                    <label for="expiration_date">Expiration Date:</label><br>
                    <input type="date" id="expiration_date" name="expiration_date" placeholder="Expiration Date" readonly><br><br>

                    <button type="submit" name="add_item">Add Item</button>
                </form>
            </div>
        </div>

        <div id="remove-item-overlay" class="overlay">
            <div class="overlay-content">
                <button id="close-remove-item" class="close-overlay">X</button>
                <h2>Remove Item</h2>
                <form method="POST" action="inventory.php">
                    <input type="number" name="item_id" placeholder="Item ID" required>
                    <input type="number" name="remove_quantity" placeholder="Quantity to Remove" required>
                    <button type="submit" name="remove_item">Remove Item</button>
                </form>
            </div>
        </div>

        <div id="remove-item-overlay" class="overlay">
            <div class="overlay-content">
                <button id="close-remove-item" class="close-overlay">Close</button>
                <h2>Remove Item</h2>
                <form method="POST" action="inventory.php">
                    <input type="number" name="item_id" placeholder="Item ID" required>
                    <input type="number" name="remove_quantity" placeholder="Quantity to Remove" required>
                    <button type="submit" name="remove_item">Remove Item</button>
                </form>
            </div>
        </div>

        <div class="inventory-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Item ID</th>
                        <th>Brand Name</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Category</th>
                        <th>Type of Medicine</th>
                        <th>Storage Location</th>
                        <th>Oldest Expiration Date</th>
                        <th>Latest Expiration Date</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['unit_price']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo htmlspecialchars($row['type_of_medicine']); ?></td>
                            <td><?php echo htmlspecialchars($row['storage_location']); ?></td>
                            <td><?php echo htmlspecialchars($row['oldest_expiration_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['latest_expiration_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: red;">No inventory data available.</p>
            <?php endif; ?>
        </div>
    </main>
    <script src="../assets/js/search.js"></script>
    <script src="../assets/js/mobile-navbar.js"></script>
    <script src="../assets/js/inv.js"></script>
</body>
</html>
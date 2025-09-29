
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


if (!empty($lowStockItems)) {
    echo '<div style="background:#ffcccc;color:#a00;padding:15px;margin:20px;border-radius:8px;">
        <strong>Low Stock Alert!</strong><br>';
    foreach ($lowStockItems as $item) {
        echo htmlspecialchars($item['item_name']) . ': <b>' . $item['quantity'] . '</b><br>';
    }
    echo '</div>';
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last3Days = date('Y-m-d', strtotime('-3 days'));
$lastWeek = date('Y-m-d', strtotime('-7 days'));
$lastMonth = date('Y-m-d', strtotime('-30 days'));

$stats = [
    'money' => [
        'today' => $conn->query("SELECT SUM(billing_amount) AS total FROM patients WHERE DATE(created_at) = '$today'")->fetch_assoc()['total'] ?? 0,
        'yesterday' => $conn->query("SELECT SUM(billing_amount) AS total FROM patients WHERE DATE(created_at) = '$yesterday'")->fetch_assoc()['total'] ?? 0,
        'last3Days' => $conn->query("SELECT SUM(billing_amount) AS total FROM patients WHERE DATE(created_at) BETWEEN '$last3Days' AND '$today'")->fetch_assoc()['total'] ?? 0,
        'lastWeek' => $conn->query("SELECT SUM(billing_amount) AS total FROM patients WHERE DATE(created_at) BETWEEN '$lastWeek' AND '$today'")->fetch_assoc()['total'] ?? 0,
        'lastMonth' => $conn->query("SELECT SUM(billing_amount) AS total FROM patients WHERE DATE(created_at) BETWEEN '$lastMonth' AND '$today'")->fetch_assoc()['total'] ?? 0,
    ],
    'patients' => [
        'today' => $conn->query("SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at) = '$today'")->fetch_assoc()['total'],
        'yesterday' => $conn->query("SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at) = '$yesterday'")->fetch_assoc()['total'],
        'last3Days' => $conn->query("SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at) BETWEEN '$last3Days' AND '$today'")->fetch_assoc()['total'],
        'lastWeek' => $conn->query("SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at) BETWEEN '$lastWeek' AND '$today'")->fetch_assoc()['total'],
        'lastMonth' => $conn->query("SELECT COUNT(*) AS total FROM patients WHERE DATE(created_at) BETWEEN '$lastMonth' AND '$today'")->fetch_assoc()['total'],
    ],
    'stocks' => [
        'today' => $conn->query("SELECT SUM(quantity) AS total FROM sales WHERE sale_date = '$today'")->fetch_assoc()['total'] ?? 0,
        'yesterday' => $conn->query("SELECT SUM(quantity) AS total FROM sales WHERE sale_date = '$yesterday'")->fetch_assoc()['total'] ?? 0,
        'last3Days' => $conn->query("SELECT SUM(quantity) AS total FROM sales WHERE sale_date BETWEEN '$last3Days' AND '$today'")->fetch_assoc()['total'] ?? 0,
        'lastWeek' => $conn->query("SELECT SUM(quantity) AS total FROM sales WHERE sale_date BETWEEN '$lastWeek' AND '$today'")->fetch_assoc()['total'] ?? 0,
        'lastMonth' => $conn->query("SELECT SUM(quantity) AS total FROM sales WHERE sale_date BETWEEN '$lastMonth' AND '$today'")->fetch_assoc()['total'] ?? 0,
    ],
];

$totalMoney = $conn->query("SELECT SUM(billing_amount) AS total FROM patients")->fetch_assoc()['total'] ?? 0;
$totalPatients = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'] ?? 0;
$totalStocks = $conn->query("SELECT SUM(quantity) AS total FROM sales")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .stat-cards {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            width: 30%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .stat-card h3 {
            margin-bottom: 10px;
            font-size: 20px;
            color: #333;
        }
        .stat-card p {
            font-size: 18px;
            font-weight: bold;
            color: #555;
        }
        .chart-container {
            width: 80%;
            margin: 20px auto;
        }
        canvas {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
        }
    </style>
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
        <div class="stat-cards">
            <div class="stat-card" onclick="updateChart('money')">
                <h3>Money</h3>
                <p>Total: ₱<?php echo number_format($totalMoney, 2); ?></p>
            </div>
            <div class="stat-card" onclick="updateChart('patients')">
                <h3>Patients</h3>
                <p>Total: <?php echo $totalPatients; ?></p>
            </div>
            <div class="stat-card">
                <h3>Sold Stocks</h3>
                <select id="medicineSelect" class="searchable-dropdown" onchange="updateMedicineChart()">
                    <option value="all">All Medicines</option>
                    <?php
                    $medicineQuery = "SELECT DISTINCT item_name FROM inventory";
                    $medicineResult = $conn->query($medicineQuery);
                    while ($medicine = $medicineResult->fetch_assoc()) {
                        echo "<option value='{$medicine['item_name']}'>{$medicine['item_name']}</option>";
                    }
                    ?>
                </select>
                <p>Total: <?php echo $totalStocks; ?></p>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="reportChart"></canvas>
        </div>
        <script>
            const stats = <?php echo json_encode($stats); ?>;
            const ctx = document.getElementById('reportChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Today', 'Yesterday', 'Last 3 Days', 'Last Week', 'Last Month'],
                    datasets: [{
                        label: 'Sold Stocks',
                        data: [
                            stats.stocks.today,
                            stats.stocks.yesterday,
                            stats.stocks.last3Days,
                            stats.stocks.lastWeek,
                            stats.stocks.lastMonth
                        ],
                        backgroundColor: '#78f15f',
                        borderColor: '#78f78f',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            function updateChart(type) {
                const labels = ['Today', 'Yesterday', 'Last 3 Days', 'Last Week', 'Last Month'];
                const data = [
                    stats[type].today,
                    stats[type].yesterday,
                    stats[type].last3Days,
                    stats[type].lastWeek,
                    stats[type].lastMonth
                ];
                chart.data.labels = labels;
                chart.data.datasets[0].label = type === 'stocks' ? 'Sold Stocks' : type.charAt(0).toUpperCase() + type.slice(1);
                chart.data.datasets[0].data = data;
                chart.update();
            }

            function updateMedicineChart() {
                const selectedMedicine = document.getElementById('medicineSelect').value;
                if (selectedMedicine === 'all') {
                    updateChart('stocks');
                } else {
                    fetch(`../assets/php/get_medicine_stock.php?medicine=${encodeURIComponent(selectedMedicine)}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            chart.data.labels = ['Today', 'Yesterday', 'Last 3 Days', 'Last Week', 'Last Month'];
                            chart.data.datasets[0].label = `Sold Stocks (${selectedMedicine})`;
                            chart.data.datasets[0].data = [
                                data.today || 0,
                                data.yesterday || 0,
                                data.last3Days || 0,
                                data.lastWeek || 0,
                                data.lastMonth || 0
                            ];
                            chart.update();
                        })
                        .catch(error => {
                            console.error('Error fetching medicine stock data:', error);
                            alert('Failed to fetch data for the selected medicine.');
                        });
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                $('.searchable-dropdown').select2({
                    placeholder: "Select a medicine",
                    allowClear: true
                });
                document.querySelector('.stat-card:nth-child(3)').addEventListener('click', function () {
                    updateChart('stocks');
                });
            });
        </script>
    </main>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    // Redirect to login page if the admin is not logged in
    header("Location: loginadmin.php");
    exit();
}

// Database connection
$servername = 'sql310.byethost15.com';
$username ='b15_38084301';
$password = 'FL?R5fkgEt@GAMs';
$dbname = 'b15_38084301_hawratrading';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin's name from the database using the session `adminID`
$adminName = "Admin"; // Default name
$adminId = $_SESSION['adminID'];
$sql = "SELECT Name FROM login_admin WHERE AdminID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminId); // Assuming AdminID is a string
$stmt->execute();
$stmt->bind_result($name);
if ($stmt->fetch()) {
    $adminName = $name;
}
$stmt->close();

// Fetch user engagement data (unique IPs)
$sql_users = "
    SELECT
        (SELECT COUNT(*) FROM members) AS registered_users,
        (SELECT COUNT(DISTINCT IPAddress) FROM publicusers) AS public_users
    FROM DUAL";
$result_users = $conn->query($sql_users);

if ($result_users->num_rows > 0) {
    $row_users = $result_users->fetch_assoc();
    $registered_users = $row_users['registered_users'] ?? 0;
    $public_users = $row_users['public_users'] ?? 0;
} else {
    $registered_users = 0;
    $public_users = 0;
}


// Fetch monthly subscriptions
$sql_subscriptions = "SELECT DATE_FORMAT(subscription_date, '%Y-%m') AS subscription_month, COUNT(*) AS new_subscriptions FROM members WHERE member_subscriptionplan = 'active' GROUP BY subscription_month ORDER BY subscription_month ASC";
$result_subscriptions = $conn->query($sql_subscriptions);
$months = [];
$new_subscriptions = [];

if ($result_subscriptions->num_rows > 0) {
    while ($row = $result_subscriptions->fetch_assoc()) {
        $months[] = $row['subscription_month'];
        $new_subscriptions[] = $row['new_subscriptions'];
    }
}

// Convert PHP arrays to JSON for JavaScript
$months_json = json_encode($months);
$new_subscriptions_json = json_encode($new_subscriptions);


// Get the current year and month
$currentYear = date('Y');
$currentMonth = date('m');

// Modify the SQL query to filter by the current month
$sql_products = "SELECT product_name, SUM(product_sold) AS total_sold
                 FROM products
                 WHERE YEAR(updated_at) = $currentYear AND MONTH(updated_at) = $currentMonth
                 GROUP BY product_name
                 ORDER BY total_sold DESC";
$result_products = $conn->query($sql_products);

$product_names = [];
$product_sales = [];

if ($result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $product_names[] = $row['product_name'];
        $product_sales[] = $row['total_sold'];
    }
}

// Convert PHP arrays to JSON for JavaScript
$product_names_json = json_encode($product_names);
$product_sales_json = json_encode($product_sales);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #F4D7E6;
        }

        /* Main Navigation Styling */
        .main-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }

        .nav-left h1 {
            font-size: 1.5rem;
        }

        .nav-center input[type="search"] {
            width: 300px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .nav-right a {
            margin-left: 20px;
            color: white;
            text-decoration: none;
        }

        .nav-right a:hover {
            text-decoration: underline;
        }

        /* Sub Navigation Styling */
        .sub-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 10px 0;
            background-color: #000;
        }

        .sub-nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .sub-nav a:hover {
            text-decoration: underline;
        }

        /* Main Content Layout */
        .container {
            display: flex;
            margin: 20px auto;
            max-width: 1200px;
            gap: 30px;
        }

        /* Sidebar */
        .sidebar {
            width: 25%;
            background-color: #E8C4D7;
            padding: 20px;
            border-radius: 40px;
        }
        .sidebar h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar button {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            background-color: #D3BECF;
            font-size: 18px;
            cursor: pointer;
        }
        .sidebar button:hover {
            background-color: #C5A4BA;
        }

        /* Header Styling */
        header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
        }

        /* Main Dashboard */
        .dashboard {
            flex-grow: 1;
        }
        .dashboard .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }
        .card {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            flex: 1;
            text-align: center;
            position: relative;
        }
        .chart {
            max-width: 200px; /* Adjusted size */
            max-height: 200px; /* Adjusted size */
            margin: 0 auto;    /* Center alignment */
        }
        h4 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .linechart {
            width: 100%;
            height: 400%;
        }

        .ratings-container {
            text-align: left;
            font-size: 16px;
            margin-top: 10px;
        }

        .rating-item {
            margin-bottom: 10px;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .rating-item:last-child {
            border-bottom: none;
        }

    </style>
</head>
<body>

    <header>
        <nav class="main-nav">
            <div class="nav-left">
                <h1>Hawra Trading</h1>
            </div>
            <div class="nav-center">
                <input type="search" placeholder="Search">
            </div>
            <div class="nav-right">
                <a href="loginadmin.php">Log Out</a>
                <a href="profileadmin.php">Profile</a>
            </div>
        </nav>
        <div class="sub-nav">
            <a href="index.php">Home</a>
            <a href="categories.php">Categories</a>
            <a href="messages.php">Messages</a>
        </div>
    </header>


    <!-- Main Content -->
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h3>
            <button onclick="location.href='memberdetails.php'">Member Details</button>
            <button onclick="location.href='product.php'">Product Details</button>
            <button onclick="location.href='ServiceDetails.php'">Service Details</button>
            <button onclick="location.href='TransactionReport.php'">Transaction Report</button>
        </div>

        <!-- Main Dashboard -->
        <div class="dashboard">
            <!-- Row 1 -->
            <div class="row">
                <div class="card">
                    <h4>User Engagement</h4>
                    <canvas id="userEngagementChart" class="chart"></canvas>
                </div>
                <div class="card">
                    <h4>New Subscriptions</h4>
                    <canvas id="subscriptionsChart"></canvas>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="row">
                <div class="card">
                    <h4>Customer Ratings</h4>
                    <div id="ratingsContainer" class="ratings-container">
                    </div>
                </div>
                <div class="card">
                    <h4>Monthly Profit</h4>
                    <?php include 'monthlyprofit.php'?>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="row">
                <div class="card">
                    <h4>Most Chosen Choices</h4>
                    <canvas id="mostChosenChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('userEngagementChart').getContext('2d');
        const userEngagementChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Registered Members', 'Public Users'],
                datasets: [{
                    label: 'User Engagement',
                    data: [<?php echo $registered_users; ?>, <?php echo $public_users; ?>],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    </script>

<script>
    const months = <?php echo $months_json; ?>;
    const subscriptions = <?php echo $new_subscriptions_json; ?>;

    console.log("Months JSON: ", months);
    console.log("New Subscriptions JSON: ", subscriptions);

    if (!months.length || !subscriptions.length) {
        console.error("No subscription data available for rendering the chart.");
    } else {
        const ctx = document.getElementById('subscriptionsChart').getContext('2d');
        const subscriptionsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: months, // Months from PHP
                datasets: [{
                    label: 'New Subscriptions',
                    data: subscriptions, // Subscription counts from PHP
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly New Subscriptions'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>


    <script>
        // Function to fetch and display customer ratings
        function fetchRatings() {
            $.ajax({
                url: 'Ratings.php', // Backend endpoint
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const container = $('#ratingsContainer');
                    container.empty(); // Clear previous content

                    if (data.length > 0) {
                        data.forEach(function(rating) {
                            container.append(`
                                <div class="rating-item">
                                    <strong>${rating.MemberName}</strong>: ${rating.CustomerRating}
                                </div>
                            `);
                        });
                    } else {
                        container.append('<p>No customer ratings available.</p>');
                    }
                },
                error: function() {
                    alert('Error fetching customer ratings.');
                }
            });
        }

        // Fetch ratings every 5 seconds
        setInterval(fetchRatings, 5000);

        // Initial fetch on page load
        $(document).ready(function() {
            fetchRatings();
        });
    </script>

    <script>
        const productNames = <?php echo $product_names_json; ?>;
        const productSales = <?php echo $product_sales_json; ?>;

        const ctxMostChosen = document.getElementById('mostChosenChart').getContext('2d');
        const mostChosenChart = new Chart(ctxMostChosen, {
            type: 'bar',
            data: {
                labels: productNames, // Product names from PHP
                datasets: [{
                    label: 'Units Sold',
                    data: productSales, // Sales data from PHP
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(231, 76, 60, 0.2)',
                        'rgba(155, 89, 182, 0.2)',
                        'rgba(52, 73, 94, 0.2)',
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(155, 89, 182, 1)',
                        'rgba(52, 73, 94, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Top Products by Sales'
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
        

</body>
</html>
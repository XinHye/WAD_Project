<?php 
session_start();

// Database connection
$servername = "sql310.byethost15.com ";
$username = "b15_38084301"; // Replace with your DB username
$password = "FL?R5fkgEt@GAMs";     // Replace with your DB password
$dbname = "b15_38084301_hawratrading"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details
$sql = "SELECT OrderID, MemberName, OrderDate, OrderTotal, PaymentMade FROM member_details ORDER BY OrderDate ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #F4D7E6;
        }

        /* Header Styling */
        header {
            background-color: #000;
            color: white;
            padding: 10px 20px;
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

        /* Table Styling */
        .table-container {
            margin: 40px auto;
            width: 80%;
            padding: 10px;
            border: 2px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #E6E6E6;
        }

        th, td {
            border: 2px solid black;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #d1d1d1;
        }

        /* Back Button */
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #f05959;
            color: black;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background-color: #900C3F;
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
                    <a href="profile.php">Profile</a>
                </div>
            </nav>
            <div class="sub-nav">
                <a href="homepage.php">Home</a>
                <a href="categories.php">Categories</a>
                <a href="messages.php">Messages</a>
            </div>
        </header>

    <!-- Product Table -->
    <div class="table-container">
        <h1 style="text-align: center;">Member Details</h1>
        <table>
            <tr>
                <th>Order ID</th>
                <th>Member Name</th>
                <th>Order Date</th>
                <th>Order Total</th>
                <th>Payment Made</th>
                <th>Payment Status</th>
                <th>Fulfillment Status</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Compute Payment Status
                    if ($row['PaymentMade'] == $row['OrderTotal']) {
                        $paymentStatus = "Paid";
                        $fulfillmentStatus = "Fulfilled";
                    } elseif ($row['PaymentMade'] < $row['OrderTotal'] && $row['PaymentMade'] > 0) {
                        $paymentStatus = "Pending";
                        $fulfillmentStatus = "Unfulfilled";
                    } elseif ($row['PaymentMade'] == 0.00) {
                        $paymentStatus = "Unpaid";
                        $fulfillmentStatus = "Unfulfilled";
                    } else {
                        $paymentStatus = "Unknown";
                        $fulfillmentStatus = "Unknown";
                    }

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['OrderID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['MemberName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['OrderDate']) . "</td>";
                    echo "<td>RM " . htmlspecialchars(number_format($row['OrderTotal'], 2)) . "</td>";
                    echo "<td>RM " . htmlspecialchars(number_format($row['PaymentMade'], 2)) . "</td>";
                    echo "<td>" . htmlspecialchars($paymentStatus) . "</td>";
                    echo "<td>" . htmlspecialchars($fulfillmentStatus) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No registered members available</td></tr>";
            }
            $conn->close();
            ?>
        </table>
        <a href="Admin.php" class="back-btn">Back</a>
    </div>

</body>
</html>

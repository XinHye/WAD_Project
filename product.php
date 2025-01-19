<?php 
session_start();

// Database connection
$servername = 'sql310.byethost15.com ';
$username = 'b15_38084301';
$password = 'FL?R5fkgEt@GAMs';
$dbname = 'b15_38084301_hawratrading';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details
$sql = "SELECT product_name, product_quantity, product_sold, product_price FROM products";
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

        /* Edit Button */
        .edit-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #D0D0D0;
            color: black;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .edit-btn:hover {
            background-color: #aaa;
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
        <h1 style="text-align: center;">Product Details</h1>
        <table>
            <tr>
                <th>Products</th>
                <th>Quantity available</th>
                <th>Quantity sold</th>
                <th>Price per unit(RM)</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['product_quantity']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['product_sold']) . "</td>";
                    echo "<td>" . htmlspecialchars(number_format($row['product_price'], 2)) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No products available</td></tr>";
            }
            $conn->close();
            ?>
        </table>
        <a href="ProductAction.php" class="edit-btn">Edit</a>
        <a href="Admin.php" class="back-btn">Back</a>
    </div>

</body>
</html>
<?php
// Start session
session_start();

// Enable error reporting for debugging
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

// Database configuration
$host = 'sql310.byethost15.com'; // Update with your correct host
$dbname = 'b15_38084301_hawratrading'; // Your database name
$username = 'b15_38084301'; // Database username
$password = 'FL?R5fkgEt@GAMs'; // Database password

try {
    // Connect to MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to get the user's IP address
function getUserIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Store user IP address in the database
$userIp = getUserIpAddress();
try {
    // Prepare the SQL query
    $stmt = $pdo->prepare("INSERT INTO publicusers (IPAddress, accessed_at) VALUES (:IPAddress, NOW())");
    $stmt->bindParam(':IPAddress', $userIp);

    // Execute the query
    $stmt->execute();
} catch (PDOException $e) {
    // Log the error message if the query fails
    error_log("Failed to log user IP: " . $e->getMessage());
    die("An error occurred while logging your IP address. Please try again later.");
}

// Include the navbar
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawra Trading</title>
    <style>
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
        #trademark {
            font-weight: italic;
            font-size: 45px;
            text-align: center;
            margin-top: 10px;
        }
        #banner {
            height: 25px;
            background-color: #5c5c5c;
            width: 100%;
            margin: 10px auto;
        }
        #text {
            text-align: center;
            color: white;
            font-size: 20px;
        }
        .ratings-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            height: 200px;
        }
        .instagram-image {
            width: 200px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <main>
        <!-- Main Dashboard -->
        <div class="dashboard">
            <div class="row">
                <div class="card">
                    <h1>Welcome to Hawra Trading</h1>
                    <h1 id="trademark">You Order, We Deliver.</h1>
                </div>
            </div>
            <div id="banner">
                <p id="text">ALL CLEANING SERVICES ARE NOW 20% OFF</p>
            </div>
            <div class="row">
                <div class="card">
                    <h4>Check us out!</h4>
                    <div id="ratingsContainer" class="ratings-container">
                        <img src="img/1.jpg" alt="Instagram post 1" class="instagram-image">
                        <img src="img/2.jpg" alt="Instagram post 2" class="instagram-image">
                        <img src="img/3.jpg" alt="Instagram post 3" class="instagram-image">
                    </div>
                </div>
            </div>
            <div class="card">
                <h4>Our Services offered!</h4>
                <p>Gas Delivery</p>
                <p>Cleaning Services</p>
            </div>
        </div>
    </main>

    <footer>
    <div class="contact-us">
        <img src="/img/phoneicon.png" alt="Phone" id="phone-icon">
        <a href="contact.php" id="contact-text">Contact Us</a>
    </div>
</footer>
</body>
</html>


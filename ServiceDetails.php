<?php 
session_start();

// Database connection
$servername = "sql310.byethost15.com";
$username = "b15_38084301"; // Replace with your DB username
$password = "FL?R5fkgEt@GAMs";     // Replace with your DB password
$dbname = "b15_38084301_hawratrading"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Additional Service</title>
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
        
        /* Service Section */
        .service-container {
            text-align: center;
            padding: 30px 10px;
        }

        .service-container h1 {
            font-size: 40px;
            font-weight: bold;
        }

        .service-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .service {
            width: 30%;
            background-color: #fff;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .service img {
            width: 300px; /* Set fixed width */
            height: 300px; /* Set fixed height */
            object-fit: cover; /* Ensures images fit within the size */
            border-radius: 10px;
        }

        .service button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #D0D0D0;
            color: black;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .service button:hover {
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

        /* Center Back Button */
        .back-container {
            margin-top: 40px;
            text-align: center;
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
                <a href="Admin.php">Home</a>
                <a href="categories.php">Categories</a>
                <a href="messages.php">Messages</a>
            </div>
        </header>

    <!-- Service Details --> 
    <div class="service-container">
        <h1>Additional Service</h1>
        <div class="service-options">
            <!-- Set Up Service -->
            <div class="service">
                <img src="img/4.jpg" alt="Set Up">
                <p>Select:</p>
                <button type="button" class="setup-btn" onclick="window.location.href='setupservice.php'" >Set Up</button>
            </div>

            <!-- Cleaning Service -->
            <div class="service">
                <img src="img/6.jpg" alt="Cleaning">
                <p>Select:</p>
                <button type="button" class="cleaning-btn" onclick="window.location.href='cleaningservice.php'" >Cleaning</button>
            </div>

            <!-- Safety Check Service -->
            <div class="service">
                <img src="img/5.jpg" alt="Safety Check">
                <p>Select:</p>
                <button type="button" class="safety-btn" onclick="window.location.href='safetyservice.php'" >Safety Check</button>
            </div>
        </div>

        <!-- Back Button -->
        <div class="back-container">
            <a href="Admin.php" class="back-btn">Back</a>
        </div>

    </div>
</body>
</html>
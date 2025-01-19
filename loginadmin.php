<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect('sql310.byethost15.com', 'b15_38084301', 'FL?R5fkgEt@GAMs', 'b15_38084301_hawratrading') or die('Unable to connect');

if (isset($_POST['login'])) {
    $Username = mysqli_real_escape_string($conn, $_POST['Username']); // Prevent SQL injection
    $Password = mysqli_real_escape_string($conn, $_POST['Password']);

    // Secure query using prepared statements
    $stmt = $conn->prepare("SELECT * FROM login_admin WHERE Username = ? AND Password = ?");
    $stmt->bind_param("ss", $Username, $Password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION["adminID"] = $row['AdminID']; // Use AdminID to track logged-in admin
        $_SESSION["Username"] = $row['Username']; // Optional, if needed
        header("Location:Admin.php");
        exit();
    } else {
        echo '<script type="text/javascript">';
        echo 'alert("Invalid username or password.");';
        echo 'window.location.href="loginadmin.php";';
        echo '</script>';
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Login</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #F4D7E6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: #FFF;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }
        .login-container h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .login-container h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 30px;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #CCC;
            border-radius: 5px;
            font-size: 14px;
        }
        .login-container input[type="submit"] {
            width: 90%;
            padding: 10px;
            background-color: #FF7BA5;
            margin-top: 20px;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .login-container input[type="submit"]:hover {
            background-color: #E96A91;
        }
        .forgot-password {
            margin-top: 20px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .logo {
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 150px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="img/logo.gif" alt="Hawra Trading Logo">
        </div>
        <h3>You Order, We Deliver.</h3>
        <h2>Administrator Log In</h2>
        
        <form action="loginadmin.php" method="post">
            <input type="text" name="Username" placeholder="User Code" required>
            <input type="password" name="Password" placeholder="Password" required>
            <input type="submit" name="login" value="Log in">
        </form>
    </div>
</body>
</html>
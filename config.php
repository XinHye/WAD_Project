<?php
$servername = "sql310.byetcluster.com";  // The server
$username = "b15_38084301";              // The database username
$password = "FL?R5fkgEt@GAMs";           // The database password
$dbname = "b15_38084301_hawratrading";   // The database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
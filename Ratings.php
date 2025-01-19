<?php
// Database connection
$servername = "sql310.byethost15.com";
$username = "b15_38084301";
$password = "FL?R5fkgEt@GAMs";
$dbname = "b15_38084301_hawratrading";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch customer ratings where PaymentMade = OrderTotal
$sql = "SELECT MemberName, CustomerRating 
        FROM member_details 
        WHERE PaymentMade = OrderTotal AND CustomerRating IS NOT NULL";
$result = $conn->query($sql);

$ratings = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }
}

// Return the ratings as JSON
echo json_encode($ratings);
$conn->close();
?>

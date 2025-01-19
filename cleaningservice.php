<?php 
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "sql310.byethost15.com ";
$username = "b15_38084301"; // Replace with your DB username
$password = "FL?R5fkgEt@GAMs ";     // Replace with your DB password
$dbname = "b15_38084301_hawratrading"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$_SESSION['ServiceID'] = 'S02';

// Retrieve ServiceID from the session
if (!isset($_SESSION['ServiceID'])) {
    die("Service ID not found in session.");
}
$serviceID = $_SESSION['ServiceID'];

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the form submission to update service details
    $serviceName = $_POST['ServiceName'];
    $servicePrice = $_POST['ServicePrice'];
    $serviceDescription = $_POST['ServiceDescription'];

    $updateSql = "UPDATE service_details SET ServiceName = ?, ServicePrice = ?, ServiceDescription = ? WHERE ServiceID = ?";
    $updateStmt = $conn->prepare($updateSql);

    if ($updateStmt) {
        $updateStmt->bind_param("sdss", $serviceName, $servicePrice, $serviceDescription, $serviceID);
        $updateStmt->execute();
        $updateStmt->close();

        $successMessage = "Service details updated successfully!";
    } else {
        die("Error preparing update statement: " . $conn->error);
    }
}

$service = null;
// Fetch one row from the service_details table
$sql = "SELECT ServiceID, ServiceName, ServicePrice, ServiceDescription FROM service_details WHERE ServiceID = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("s", $serviceID); // Bind as a string (use 's' for string-based IDs)
    $stmt->execute();
    $result = $stmt->get_result();
    $service = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Service Details</title>
    <style>
       body {
            font-family: Arial, sans-serif;
            background-color: #F4D7E6;
            margin: 0;
            padding: 20px;
        }
        .service-container {
            max-width: 750px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .service-container img {
            width: 100%;
            max-width: 350px;
            height: auto;
            object-fit: cover;
            border-radius: 10px;
            display: block;
            margin: 0 auto 20px;
        }
        .success-message {
            color: #28a745;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }
        .service-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            width: 100%;
            max-width: 600px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .form-group label {
            width: 30%;
            font-weight: bold;
            text-align: left;
        }
        .form-group input, .form-group textarea {
            width: 60%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group textarea {
            resize: none;
        }
        .button-group {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .save-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .save-btn:hover {
            background-color: #218838;
        }
        .back-btn {
            background-color: #f05959;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .back-btn:hover {
            background-color: #c82333;
        }
    </style>
    <script>
        function confirmUpdate(event) {
            if (!confirm("Are you sure you want to update the service details?")) {
                event.preventDefault(); // Prevent form submission if user cancels
            }
        }
    </script>
</head>
<body>
    <div class="service-container">
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($service): ?>
            <img src="cleaning.png" alt="Service Image">
            <form method="POST" onsubmit="confirmUpdate(event)">
                <div class="form-group">
                    <label for="ServiceName">Service Name:</label>
                    <input type="text" id="ServiceName" name="ServiceName" value="<?php echo htmlspecialchars($service['ServiceName']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="ServicePrice">Price (RM):</label>
                    <input type="number" id="ServicePrice" name="ServicePrice" step="0.10" value="<?php echo htmlspecialchars($service['ServicePrice']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="ServiceDescription">Description:</label>
                    <textarea id="ServiceDescription" name="ServiceDescription" rows="4" required><?php echo htmlspecialchars($service['ServiceDescription']); ?></textarea>
                </div>
                <div class="button-group">
                    <button type="submit" class="save-btn">Save</button>
                    <a href="ServiceDetails.php" class="back-btn">Back</a>
                </div>
            </form>
        <?php else: ?>
            <p>No service found for the given ID.</p>
            <a href="ServiceDetails.php" class="back-btn">Back</a>
        <?php endif; ?>
    </div>
</body>
</html>

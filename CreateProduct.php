<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect('sql310.byethost15.com', 'b15_38084301', 'FL?R5fkgEt@GAMs', 'b15_38084301_hawratrading') or die ('Unable to connect');

$message = ""; // Variable to store messages

if (isset($_POST['save'])) {
    // Get input values
    $ProductName = mysqli_real_escape_string($conn, $_POST['pname']);
    $QuantityAvailable = isset($_POST['qavailable']) ? (int)$_POST['qavailable'] : 0;
    $ProductPrice = isset($_POST['pprice']) ? (float)$_POST['pprice'] : 0.0;

    // Default QuantitySold to 0
    $QuantitySold = 0;

    // Validate required fields
    if (!empty($ProductName) && $ProductPrice > 0) {
        // Insert product into the database
        $sql = "INSERT INTO products (product_name, product_quantity, product_sold, product_price) 
                VALUES ('$ProductName', $QuantityAvailable, $QuantitySold, $ProductPrice)";

        if (mysqli_query($conn, $sql)) {
            $message = "<p style='color:green;'>New product added successfully!</p>";
        } else {
            $message = "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Please provide a valid product name and price.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .create-container {
            padding: 30px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
            position: relative;
        }
        .message {
            position: absolute;
            top: -40px;
            left: 0;
            width: 100%;
            text-align: center;
        }
        .create-container input[type="text"],
        .create-container input[type="number"] {
            width: 90%; /* Adjusted for better alignment */
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #CCC;
            border-radius: 5px;
            font-size: 14px;
        }
        .create-container button,
        .create-container a {
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: black;
            margin: 5px;
        }
        .save-btn {
            background-color: #5cb85c;
        }
        .save-btn:hover {
            background-color: #4cae4c;
        }
        .back-btn {
            background-color: #f05959;
        }
        .back-btn:hover {
            background-color: #900C3F;
        }
    </style>
</head>
<body>
    <div class="create-container">
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <h1>New Product</h1>
        <form action="" method="post">
            <div>
                <label for="pname">Product Name</label><br>
                <input type="text" name="pname" id="pname">
            </div>
            <div>
                <label for="qavailable">Quantity Available</label><br>
                <input type="number" name="qavailable" id="qavailable" min="0" max="500" step="1">
            </div>
            <div>
                <label for="pprice">Product Price Per Unit (RM)</label><br>
                <input type="text" name="pprice" id="pprice">
            </div>
            <div>
                <button type="submit" name="save" class="save-btn">Save</button>
                <a href="ProductAction.php" class="back-btn">Back</a>
            </div>
        </form>
    </div>
</body>
</html>
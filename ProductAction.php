<?php 
session_start();

// Database connection
// Database connection
$servername = 'sql310.byethost15.com';
$username = 'b15_38084301';
$password = 'FL?R5fkgEt@GAMs';
$dbname = 'b15_38084301_hawratrading';

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Success message variable
$successMessage = "";

// Handle delete action
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $sql = "DELETE FROM products WHERE product_id = $delete_id";
    $conn->query($sql);
}

// Handle save/update action
if (isset($_POST['save'])) {
    foreach ($_POST['product_id'] as $index => $id) {
        $quantity = $_POST['quantity'][$index];
        $price = $_POST['price'][$index];
        $sql = "UPDATE products SET product_quantity = $quantity, product_price = $price WHERE product_id = $id";
        $conn->query($sql);
    }
    $successMessage = "The product successfully updated!";
}

// Fetch product details
$sql = "SELECT product_id, product_name, product_quantity, product_sold, product_price FROM products";
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
        .table-container {
            margin: 40px auto;
            width: 80%;
            text-align: center;
        }
        .success-message {
            color: green;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .delete-btn {
            color: red;
            cursor: pointer;
            font-size: 30px;
            font-weight: bold;
        }
        .save-btn, .create-btn {
            margin-top: 20px;
            padding: 8px 12px;
            background-color: #5cb85c;
            color: black;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }
        .save-btn:hover, .create-btn:hover {
            background-color: #4cae4c;
        }
        input[type="number"], input[type="range"] {
            width: 60px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #f05959;
            color: black;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
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
        <h1>Product Details</h1>
        
        <!-- Success Message -->
        <?php if ($successMessage): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form id="productForm" method="POST">
            <table>
                <tr>
                    <th></th>
                    <th>Products</th>
                    <th>Quantity Available</th>
                    <th>Quantity Sold</th>
                    <th>Price Per Unit (RM)</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>
                                <button type='button' class='delete-btn' onclick='confirmDelete(" . $row['product_id'] . ")'>-</button>
                              </td>";
                        echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                        echo "<td>
                                <input type='number' step='1.0' name='quantity[]' min='0' max='100' value='" . $row['product_quantity'] . "'>
                              </td>";
                        echo "<td>" . htmlspecialchars($row['product_sold']) . "</td>";
                        echo "<td>
                                <input type='number' step='0.10' min='0' max='100' name='price[]' value='" . $row['product_price'] . "'>
                              </td>";
                        echo "<input type='hidden' name='product_id[]' value='" . $row['product_id'] . "'>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No products available</td></tr>";
                }
                ?>
            </table>
            <button type="submit" name="save" class="save-btn">Save</button>
            <button type="button" class="create-btn" onclick="window.location.href='CreateProduct.php'">Create</button>
            <a href="product.php" class="back-btn">Back</a>
        </form>
    </div>

    <script>
        // Function to confirm deletion
        function confirmDelete(productId) {
            const confirmed = confirm("Are you sure you want to delete this product?");
            if (confirmed) {
                const form = document.getElementById('productForm');
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'delete_id';
                hiddenInput.value = productId;
                form.appendChild(hiddenInput);
                form.submit();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>

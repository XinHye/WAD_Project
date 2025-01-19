<?php
session_start();
include 'config.php';
include 'navsearch.php';

// Fetch products from the database
$sql = "SELECT product_id, product_name, product_price, product_quantity FROM products";
$result = $conn->query($sql);

// Check if the user is logged in
$is_logged_in = isset($_SESSION['member_id']);
$member_id = $is_logged_in ? $_SESSION['member_id'] : null;

// Handle search query
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
}

// Fetch products from the database
$sql = "SELECT product_id, product_name, product_price, product_quantity FROM products";
if (!empty($search_query)) {
    $sql .= " WHERE product_name LIKE ?";
}
$stmt = $conn->prepare($sql);
if (!empty($search_query)) {
    $search_param = '%' . $search_query . '%';
    $stmt->bind_param("s", $search_param);
}
$stmt->execute();
$result = $stmt->get_result();

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['message'] = "Invalid quantity!";
    } else {
        // Check if the requested quantity is available in stock
        $stmt = $conn->prepare("SELECT product_quantity, product_name, product_price FROM products WHERE product_id = ?");
        $stmt->bind_param("s", $product_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($product_quantity, $product_name, $product_price);
        $stmt->fetch();
        $stmt->close();

        if ($quantity > $product_quantity) {
            $_SESSION['message'] = "Not enough stock available!";
        } else {
            if ($is_logged_in) {
                // For logged-in users, add the product to their cart in the database
                $stmt = $conn->prepare("SELECT * FROM carts WHERE member_id = ? AND product_id = ?");
                $stmt->bind_param("is", $member_id, $product_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Product is already in the cart, so update the quantity
                    $stmt = $conn->prepare("UPDATE carts SET quantity = quantity + ? WHERE member_id = ? AND product_id = ?");
                    $stmt->bind_param("iis", $quantity, $member_id, $product_id);
                    $stmt->execute();
                    $_SESSION['message'] = "Product quantity updated in your cart!";
                } else {
                    // Product is not in the cart, so add it
                    $stmt = $conn->prepare("INSERT INTO carts (member_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->bind_param("isi", $member_id, $product_id, $quantity);
                    $stmt->execute();
                    $_SESSION['message'] = "Product added to your cart!";
                }

                $stmt->close();
            } else {
                // For public users, store the cart items in session
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                // Check if the product is already in the session cart
                $product_exists = false;
                foreach ($_SESSION['cart'] as &$cart_item) {
                    if ($cart_item['product_id'] == $product_id) {
                        $cart_item['quantity'] += $quantity;
                        $product_exists = true;
                        break;
                    }
                }

                // If not already in the cart, add it
                if (!$product_exists) {
                    $_SESSION['cart'][] = [
                        'product_id' => $product_id,
                        'quantity' => $quantity,
                        'product_name' => $product_name,
                        'product_price' => $product_price
                    ];
                }

                $_SESSION['message'] = "Product added to your cart!";
            }
        }
    }

    // Refresh the page to display the message
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Display Cart for Public Users
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total_price = 0;
foreach ($cart_items as $cart_item) {
    $total_price += $cart_item['product_price'] * $cart_item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawra Trading</title>
    <script src="dec-incquantity.js"></script>
</head>
<style> 
.list-product {
    display: flex;
    gap: 10px;
    justify-content: space-between;
    padding: 20px;
    flex-wrap: wrap;
}

.product {
    width: 80%;
    max-width: 500px;
    margin: auto;
    padding: 20px;
    background-color: white;
    margin-top: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

p {
    font-weight: bold;
}

.item {
    display: flex;
    gap: 20px;
    align-items: center;
    margin: 20px 0;
}

.item-image {
    max-width: 200px;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.quantity-control input::-webkit-outer-spin-button,
.quantity-control input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* For non-WebKit browsers (optional) */
.quantity-control input[type="number"] {
    appearance: textfield;
}

.quantity-control button {
    padding: 5px 10px;
    font-size: 16px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
}

.quantity-control input {
    width: 50px;
    text-align: center;
}

.optional-selection {
    margin: 10px 0;
}

.size-buttons {
    display: flex;
    gap: 10px;
    margin: 10px 0;
}

#size-buttons:hover {
    background-color: lightgray;
}

.size-option {
    padding: 10px 20px;
    font-size: 14px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.add-to-cart {
    width: 200px; /* Set a fixed width */
    max-width: 100%; /* Ensure it doesn't exceed container width */
    text-align: center;
    padding: 15px;
    font-size: 16px;
    cursor: pointer;
    border-radius: 8px;
    border: none;
    background-color: #28a745;
    color: white;
    margin-top: 20px;
}

.add-to-cart button:hover {
    background-color: lightgray;
}

@media (max-width: 600px) {
    .add-to-cart {
        width: 100%; /* Full width on smaller screens */
    }
}

.quantity-control button:hover, .size-option:hover, #add-to-cart:hover {
    background-color: #0056b3;
}

.alert {
    position: fixed;
    top: 20px; /* Start at the middle-top */
    left: 50%; /* Center horizontally */
    transform: translateX(-50%); /* Adjust to center perfectly */
    background-color: lightgreen; 
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    font-family: Arial, sans-serif;
    font-size: 16px;
    z-index: 1000;
    opacity: 1;
    transition: opacity 0.5s ease-out, top 0.5s ease-in-out;
}
</style>
<body>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert">
        <p><?= htmlspecialchars($_SESSION['message']) ?></p>
    </div>
    <?php unset($_SESSION['message']); // Clear the message after displaying ?>
<?php endif; ?>

<script>
    window.onload = function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.top = '0px';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        }
    };
</script>

<!-- Product List -->
<div class="list-product">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product">
                <div class="item">
                    <img 
                        src="/img/<?= strtolower(str_replace(' ', '', $row['product_name'])) ?>gas.jpeg" 
                        alt="<?= $row['product_name'] ?> Gas Cylinder" 
                        class="item-image">
                    <div class="item-details">
                        <h2 class="name"><?= $row['product_name'] ?></h2>
                        <p>Price: RM <?= number_format($row['product_price'], 2) ?></p>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                            <div class="quantity-control">
                                <button type="button" class="decbtn" onclick="decreaseQuantity(this)">-</button>
                                <input 
                                    type="number"
                                    class="quantity"  
                                    name="quantity"
                                    value="1" 
                                    min="1" 
                                    max="<?= $row['product_quantity'] ?>"
                                >
                                <button type="button" class="incbtn" onclick="increaseQuantity(this)">+</button>
                            </div>
                            <button type="submit" name="add_to_cart" class="add-to-cart">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No products available.</p>
    <?php endif; ?>
</div>

</body>
</html>

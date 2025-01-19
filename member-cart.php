<?php
session_start();
include('config.php');
include('navbar.php');

// Determine if the user is logged in
$loggedIn = isset($_SESSION['member_id']);
$member_id = $loggedIn ? $_SESSION['member_id'] : null;

// Initialize session cart for public users
if (!$loggedIn && !isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];

    if ($quantity > 0) {
        if ($loggedIn) {
            // Update quantity in database for logged-in users
            $stmt = $conn->prepare("UPDATE carts SET quantity = ? WHERE cart_id = ? AND member_id = ?");
            $stmt->bind_param("iii", $quantity, $cart_id, $member_id);
            $stmt->execute();
        } else {
            // Update quantity in session cart for public users
            if (isset($_SESSION['cart'][$cart_id])) {
                $_SESSION['cart'][$cart_id]['quantity'] = $quantity;
            }
        }
        $_SESSION['message'] = "Cart updated successfully!";
    } else {
        $_SESSION['message'] = "Quantity must be greater than 0!";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle product removal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product'])) {
    $cart_id = $_POST['cart_id'];

    if ($loggedIn) {
        // Remove product from database cart for logged-in users
        $stmt = $conn->prepare("DELETE FROM carts WHERE cart_id = ? AND member_id = ?");
        $stmt->bind_param("ii", $cart_id, $member_id);
        $stmt->execute();
    } else {
        // Remove product from session cart for public users
        if (isset($_SESSION['cart'][$cart_id])) {
            unset($_SESSION['cart'][$cart_id]);
        }
    }
    $_SESSION['message'] = "Product removed from cart!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle additional service toggle
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_service'])) {
    $service = $_POST['toggle_service']; // Access the service being toggled

    // Initialize the session variable if not already done
    if (!isset($_SESSION['selected_services'])) {
        $_SESSION['selected_services'] = [];
    }

    // Toggle the service in the session
    if (in_array($service, $_SESSION['selected_services'])) {
        // Remove the service if it's already selected
        $_SESSION['selected_services'] = array_filter($_SESSION['selected_services'], function($item) use ($service) {
            return $item !== $service;
        });
    } else {
        // Add the service if it's not already selected
        $_SESSION['selected_services'][] = $service;
    }
}

// Clear the selected services if no services are selected
if (!isset($_SESSION['selected_services']) || empty($_SESSION['selected_services'])) {
    unset($_SESSION['selected_services']); // Remove session data if no services are selected
}

// Fetch cart items
$cart_items = [];
$total_price = 0;

if ($loggedIn) {
    // Fetch cart items from database for logged-in users
    $sql = "SELECT c.cart_id, p.product_name, p.product_price, c.quantity 
            FROM carts c 
            INNER JOIN products p ON c.product_id = p.product_id 
            WHERE c.member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['product_price'] * $row['quantity'];
        $cart_items[] = $row;
        $total_price += $row['subtotal'];
    }
} else {
    // Fetch cart items from session cart for public users
    foreach ($_SESSION['cart'] as $cart_id => $item) {
        $item['subtotal'] = $item['product_price'] * $item['quantity'];
        $cart_items[] = array_merge(['cart_id' => $cart_id], $item); // Add cart_id to the item array
        $total_price += $item['subtotal'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style> .cart-container {
    width: 80%;
    margin: auto;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    margin-bottom: 20px;
}

.cart-items {
    display: flex;
    justify-content: space-between;
    border-bottom: 2px solid #ddd;
    padding: 10px 0;
}

.cart-items:last-child {
    border-bottom: none;
}
.total {
    text-align: right;
    margin-top: 10px;
    font-size: 18px;
    font-weight: bold;
}

.checkout {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
}

.cart-items #item-list button {
    padding: 5px 10px;
    margin: 0 5px;
    cursor: pointer;
}

.cart-items #item-list input {
    width: 50px;
    text-align: center;
}

.update-form, .remove-form {
    display: inline-block;
    margin: 0;
}

.update-form input[type="number"] {
    width: 50px;
    padding: 5px;
}

.update-form button, .remove-btn {
    padding: 5px 10px;
    background-color: #f44336;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 5px;
}

.update-form button {
    background-color: #b44e70
}

.update-form button:hover, .remove-btn:hover {
    opacity: 0.8;
}

.alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
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
.alert.show {
    display: block;
}

.checkout-btn {
    display: block;
    width: 95%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    font-size: 18px;
    text-align: center;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    margin-top: 20px;
    text-decoration: none;
}

.checkout-btn:hover {
    background-color: #45a049;
}

.service-buttons button {
    padding: 10px;
    background-color:rgb(21, 153, 247);
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    margin: 5px;
}

.service-buttons button.selected {
    background-color: rgb(14, 117, 190);
}

.service-buttons button:hover {
    opacity: 0.8;
} 

.back-btn-container {
    margin-top: 20px;
    width: 100%;
    text-align: center;
}

.back-btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    text-align: center;
}

.back-btn:hover {
    background-color: #45a049;
}
</style>
</head>
<body>

<!-- Alert Message -->
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert show" id="alert-message">
        <p><?= htmlspecialchars($_SESSION['message']) ?></p>
    </div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>
    <?php if (!empty($cart_items)): ?>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td>RM <?= number_format($item['product_price'], 2) ?></td>
                        <td>
                            <form method="POST" class="update-form">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1">
                                <button type="submit" name="update_quantity">Update</button>
                            </form>
                        </td>
                        <td>RM <?= number_format($item['subtotal'], 2) ?></td>
                        <td>
                            <form method="POST" class="remove-form">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <button type="submit" name="remove_product" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
         <!-- Additional Services Section -->
        <h3>Additional Services</h3>
        <div class="service-buttons">
            <form method="POST">
                <button type="submit" name="toggle_service" value="set-up" class="<?= in_array('set-up', $_SESSION['selected_services'] ?? []) ? 'selected' : '' ?>">Set Up (RM 50.00)</button>
                <button type="submit" name="toggle_service" value="cleaning" class="<?= in_array('cleaning', $_SESSION['selected_services'] ?? []) ? 'selected' : '' ?>">Cleaning (RM 15.00)</button>
                <button type="submit" name="toggle_service" value="safety-check" class="<?= in_array('safety-check', $_SESSION['selected_services'] ?? []) ? 'selected' : '' ?>">Safety Check (RM 10.00)</button>
            </form>
        </div>

        <!-- Calculate total including selected services -->
        <?php
        $service_cost = 0;
        if (isset($_SESSION['selected_services'])) {
            foreach ($_SESSION['selected_services'] as $selected_service) {
                if ($selected_service == 'set-up') {
                    $service_cost += 50.00;
                } elseif ($selected_service == 'cleaning') {
                    $service_cost += 15.00;
                } elseif ($selected_service == 'safety-check') {
                    $service_cost += 10.00;
                }
            }
        }

        $total_price += $service_cost;
        ?>

        <div class="cart-total">
            <h3>Total: RM <?= number_format($total_price, 2) ?></h3>
        </div>

        <!-- Checkout Button -->
        <a href="<?= $loggedIn ? 'checkout.php' : 'registration.php' ?>" class="checkout-btn">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty!</p>
    <?php endif; ?>
</div>

<!-- Back Button to 'add-to-cart.php' -->
        <div class="back-btn-container">
            <a href="add-to-cart.php" class="back-btn">Back to Shopping</a>
        </div>

<script>
    // Alert message fade out
    window.onload = function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 3000);
        }
    };
</script>
</body>
</html>

<?php
session_start();
include("config.php");
include("functions.php");
include("navbar.php");

if (!isset($_GET['order_id'])) {
    echo "Invalid order ID.";
    exit();
}

$orderId = $_GET['order_id'];  // Fetch order_id from URL

// Fetch order details using order_id
$orderStmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$orderStmt->bind_param("s", $orderId);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows === 0) {
    echo "Order not found.";
    exit();
}

$order = $orderResult->fetch_assoc();  // Fetch order data

// Fetch the transaction_id from the transactions table using order_id
$transactionStmt = $conn->prepare("SELECT transaction_id, transaction_type FROM transactions WHERE order_id = ?");
$transactionStmt->bind_param("s", $orderId);
$transactionStmt->execute();
$transactionResult = $transactionStmt->get_result();

if ($transactionResult->num_rows === 0) {
    echo "Transaction not found for this order.";
    exit();
}

$transaction = $transactionResult->fetch_assoc();  // Fetch transaction data
$transactionId = $transaction['transaction_id'];  // Get transaction_id from transactions table
$transactionType = $transaction['transaction_type'];  // Get the payment method (card or fpx)

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // Redirect to login if the user is not logged in
    exit();
}

// Ensure services array is always set
if (!isset($_SESSION['selected_services'])) {
    $_SESSION['selected_services'] = [];
}

$pick_up = isset($_SESSION['pick_up']) ? $_SESSION['pick_up'] : 'online'; 
$schedule_type = isset($_SESSION['schedule_type']) ? $_SESSION['schedule_type'] : 'express'; 

//store the data in schedule_date session
$schedule_date = isset($_SESSION['schedule_date']) ? $_SESSION['schedule_date'] : 'Not selected'; // Fetch schedule_date from session

$member_id = $_SESSION['member_id'];

// Retrieve session data
$checkout_summary = $_SESSION['checkout_summary'] ?? [];

$cart_items = getCartItems($conn, $member_id);

// Fetch user address
$member_address = getMemberAddress($conn, $member_id);

// Extract checkout summary details
$subtotal = $checkout_summary['subtotal'] ?? 0;
$delivery_fee = $checkout_summary['delivery_fee'] ?? 0;
$redeem_discount = $checkout_summary['redeem_discount'] ?? 0;
$voucher_discount = $checkout_summary['voucher_discount'] ?? 0;
$total = $checkout_summary['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
</head>
<style> 
/* Overall container for the receipt */
.receipt-container {
    width: 100%;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

/* Header */
.receipt-container h1 {
    text-align: center;
    color: #333;
    font-size: 32px;
    margin-bottom: 10px;
}

/* Section Titles */
h3 {
    font-size: 24px;
    color: #333;
    border-bottom: 2px solid #ddd;
    padding-bottom: 5px;
    margin-bottom: 15px;
}

/* Order Details */
h4 {
    font-size: 20px;
    color: #333;
    margin-bottom: 10px;
}

/* Product and Service List */
ul {
    list-style-type: none;
    padding-left: 0;
}

ul li {
    font-size: 16px;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
    color: #555;
}

ul li:last-child {
    border-bottom: none;
}

/* Totals section */
.totals {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 2px solid #ddd;
}

.totals p {
    font-size: 18px;
    color: #333;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between; /* Align label and value */
}

.totals p span {
    font-weight: bold;
}

/* Buttons */
.action-buttons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

button {
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-right: 10px;
}

button:hover {
    background-color: #218838;
}

button:focus {
    outline: none;
}

/* Delivery Address */
h3 + p {
    font-size: 16px;
    color: #555;
    line-height: 1.5;
    margin-bottom: 20px;
    text-align: left;
}

/* Print Styles */
@media print {
    .action-buttons, button {
        display: none;
    }

    .receipt-container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
    }

    .receipt-container h1,
    .receipt-container h3,
    .receipt-container p {
        font-size: 18px;
    }

    .totals p, ul li {
        font-size: 16px;
    }

    .receipt-container {
        page-break-inside: avoid;
    }
}
</style> 
<body>
    <div class="receipt-container">
        <h1>Receipt</h1>
        <p>Transaction ID: <?php echo htmlspecialchars($transactionId); ?></p> <!-- Transaction ID fetched from the database -->
        <p>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></p>
        <p>Order Date: <?php echo htmlspecialchars($order['order_date']); ?></p>
        <p>Pick Up Option: <strong><?= htmlspecialchars($pick_up) ?></strong></p>
        <p>Schedule type: <strong><?= htmlspecialchars($schedule_type) ?></strong></p>
        <p>Schedule Date: <strong><?= htmlspecialchars($schedule_date) ?></strong></p>
        <h3>Order Summary</h3>
        
        <h4>Products</h4>
        <ul>
            <?php
            if (!empty($cart_items)) {
                foreach ($cart_items as $item) {
                    echo "<li>" . htmlspecialchars($item['product_name']) . " (RM " . 
                    number_format($item['product_price'], 2) . " x " . 
                    intval($item['quantity']) . " ) = RM " . 
                    number_format($item['product_price'] * $item['quantity'], 2) . "</li>";
                }
            } else {
                echo "<li>No products selected.</li>";
            }
            ?>
        </ul>

        <h4>Selected Services</h4>
        <ul>
            <?php
                if (count($_SESSION['selected_services']) > 0) {
                    foreach ($_SESSION['selected_services'] as $service) {
                        if ($service == 'set-up') {
                            echo "<li>Set-up Service - RM 50</li>";
                        } elseif ($service == 'cleaning') {
                            echo "<li>Cleaning Service - RM 15</li>";
                        } elseif ($service == 'safety-check') {
                            echo "<li>Safety Check Service - RM 10</li>";
                        }
                    }
                } else {
                        echo "<li>No services selected.</li>";
                }
            ?>
        </ul>

        <div class="totals">
            <p>Subtotal: <span>RM <?= number_format($subtotal, 2); ?></span></p>
            <p>Delivery Fee: <span>RM <?= number_format($delivery_fee, 2); ?></span></p>
            <p>Redeem Discount: <span>- RM <?= number_format($redeem_discount, 2); ?></span></p>
            <p>Voucher Discount: <span>- RM <?= number_format($voucher_discount, 2); ?></span></p>
            <p>Total: <strong>RM <?= number_format($total, 2); ?></strong></p>
        </div>

        <h3>Delivery Address</h3>
            <p1>
                <?php
                if ($member_address) {
                    echo "{$member_address['member_address']}, {$member_address['member_city']}, {$member_address['member_state']}";
                } else {
                    echo "No address found for this member.";
                }
                ?>
            </p1>

        <h3>Payment Method</h3>
        <p1>Paid via <?= ucfirst(htmlspecialchars($transactionType)); ?></p1> <!-- Dynamically display the payment method -->

        <div class="action-buttons">
            <button onclick="window.print()">Print Receipt</button>
            <form id="continue-shopping-form" method="post" action="emailcard.php" style="display:inline;">
                <input type="hidden" name="send_email" value="1">
                <button type="submit" class="continue-btn">Continue Shopping</button>
            </form>
        </div>
    </div>

<script>
    document.getElementById('continue-shopping-form').addEventListener('submit', function(event) {
        event.preventDefault();
        var form = event.target;
        var submitButton = form.querySelector('button[type="submit"]');
        
        // Disable the button to prevent double submission
        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.text())
        .then(data => {
            // If email is sent (regardless of the response), proceed to categories page
            // Allow a short delay for the email to be processed
            setTimeout(() => {
                window.location.href = 'categories.php';
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            // Even if there's an error in the response, if the email was sent, we can still redirect
            window.location.href = 'categories.php';
        });
    });
</script>
</body>
</html>

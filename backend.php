<?php
session_start();
include("config.php");

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
$memberId = $_SESSION['member_id'];
//new added
$pick_up = isset($_SESSION['pick_up']) ? $_SESSION['pick_up'] : 'online'; 
$schedule_type = isset($_SESSION['schedule_type']) ? $_SESSION['schedule_type'] : 'express'; 


// Retrieve checkout summary
$checkoutSummary = $_SESSION['checkout_summary'];
$subtotal = $checkoutSummary['subtotal'];
$deliveryFee = $checkoutSummary['delivery_fee'];
$redeemDiscount = $checkoutSummary['redeem_discount'];
$voucherDiscount = $checkoutSummary['voucher_discount'];
$total = $checkoutSummary['total'];

// Fetch cart details for the member
$cartStmt = $conn->prepare("
SELECT p.product_id, p.product_name, p.product_price, c.quantity 
FROM carts c
JOIN products p ON c.product_id = p.product_id
WHERE c.member_id = ?
");

$cartStmt->bind_param("i", $memberId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result();

if ($cartResult->num_rows === 0) {
echo json_encode(['error' => 'No items in the cart.']);
exit();
}

// Format cart data and prepare for updating products
$purchasedItems = [];
while ($row = $cartResult->fetch_assoc()) {
$purchasedItems[] = [
    'product_id' => $row['product_id'],
    'product_name' => $row['product_name'],
    'quantity' => $row['quantity'],
    'product_price'=> $row['product_price'],
    'total_price' => $row['product_price'] * $row['quantity']
];
}

// Insert into orders table
$orderId = uniqid('order_', false); // Generate a unique order ID
$orderDate = date('Y-m-d H:i:s'); // Current date and time
$totalAmount = $total; // From checkout summary

$orderStmt = $conn->prepare("
    INSERT INTO orders (order_id, order_date, member_id, total_amount, pick_up, schedule_type)
    VALUES (?, ?, ?, ?, ?, ?);
");
$orderStmt->bind_param("sssdss", $orderId, $orderDate, $memberId, $totalAmount, $pick_up, $schedule_type);
$orderStmt->execute();

// Loop through purchased items and insert into order_items table
foreach ($purchasedItems as $item) {
    $itemDescription = $item['product_name'];
    $itemType = 'product'; // For now, assuming all items are products
    $quantity = $item['quantity'];
    $price = $item['product_price']; // Assuming price is available
    $totalPrice = $price * $quantity;

    // Prepare the SQL statement
    $orderItemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, item_description, item_type, quantity, price, total_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    // Bind the parameters to the SQL statement
    // order_id: string (VARCHAR(36)), item_description: string, item_type: string, quantity: integer, price: decimal, total_price: decimal
    $orderItemStmt->bind_param("sssiid", $orderId, $itemDescription, $itemType, $quantity, $price, $totalPrice);
    $orderItemStmt->execute();
}

$selected_services = $_SESSION['selected_services'];
if (isset($selected_services) && !empty($selected_services)) {
    foreach ($selected_services as $service) {
        $servicePrices = [
            'set-up' => 70.00,
            'cleaning' => 30.00,
            'safety-check' => 50.00
        ];
        if (array_key_exists($service, $servicePrices)) {
            $itemDescription = $service; // Service name or description
            $itemType = 'service'; // Indicating this is a service
            $quantity = 1; // Services are typically single quantity
            $price = $servicePrices[$service]; // Set the price of the service (or retrieve from session if available)
            $totalPrice = $price * $quantity; // For services, total price is often just price * quantity
        
            // Prepare the SQL statement for inserting services into order_items
            $orderItemStmt = $conn->prepare("
                INSERT INTO order_items (order_id, item_description, item_type, quantity, price, total_price)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
        
            // Bind the parameters and execute
            $orderItemStmt->bind_param("sssiid", $orderId, $itemDescription, $itemType, $quantity, $price, $totalPrice);
            $orderItemStmt->execute();
        }
    }
}
$transactionAmount = $total; // This should come from your payment flow
$transactionType = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'card'; // Default to 'card' if nothing is selected
//$transactionType = 'card'; // Assuming 'card' as the payment method type; you can adjust based on your payment method
$transactionDate = date('Y-m-d H:i:s'); // Current date and time
$memberId = $_SESSION['member_id']; // Get member ID from session

// Generate a unique transaction ID (UUID or other method)
$transactionId = uniqid('txn_', false); 

// Prepare the SQL statement to insert into the transactions table
$transactionStmt = $conn->prepare("
    INSERT INTO transactions (transaction_id, transaction_date, transaction_amount, member_id, order_id, transaction_type)
        VALUES (?, ?, ?, ?, ?, ?)
");

// Bind parameters to the SQL statement
$transactionStmt->bind_param("ssdiss", $transactionId, $transactionDate, $transactionAmount, $memberId, $orderId, $transactionType);
$transactionStmt->execute();

$_SESSION['transaction_id'] = $transactionId;

// Loop through the purchased items and update product quantities and sold count
foreach ($purchasedItems as $item) {
    $productId = $item['product_id'];
    $quantityPurchased = $item['quantity'];

    // Update product_quantity and product_sold in the products table
    $updateStmt = $conn->prepare("
        UPDATE products
        SET product_quantity = product_quantity - ?, product_sold = product_sold + ?
        WHERE product_id = ?
    ");
    $updateStmt->bind_param("iis", $quantityPurchased, $quantityPurchased, $productId);
    $updateStmt->execute();
}


$pointsPerRM = 1; // Define how many RM equals 1 point
$earnedPoints = floor($subtotal / $pointsPerRM);

if ($earnedPoints > 0) {
    $updatePointsStmt = $conn->prepare("
        UPDATE members 
        SET member_availableredeempoints = member_availableredeempoints + ?
        WHERE member_id = ?
    ");
    $updatePointsStmt->bind_param("ii", $earnedPoints, $memberId);
    $updatePointsStmt->execute();
}

header("Location: receipt.php?order_id=" . $orderId);
exit();

?>
<?php
session_start();
include("config.php");
include("navbar.php");
include("functions.php");

// Redirect to login page if user is not logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member_id'];

// Retrieve user address and reward points
$address_query = "SELECT member_address FROM members WHERE member_id = ?";
$stmt = $conn->prepare($address_query);
if (!$stmt) {
    die("Failed to prepare address query: " . $conn->error);
}
$stmt->bind_param("i", $member_id);
$stmt->execute();
$address_result = $stmt->get_result();
$member_data = $address_result->fetch_assoc();
$member_address = htmlspecialchars($member_data['member_address'] ?? 'No address available');

// Fetch total reward points for the user
$reward_query = "SELECT member_availableredeempoints AS total_points FROM members WHERE member_id = ?";
$stmt = $conn->prepare($reward_query);
if (!$stmt) {
    die("Failed to prepare reward query: " . $conn->error);
}
$stmt->bind_param("i", $member_id);
$stmt->execute();
$reward_result = $stmt->get_result();
$total_reward_points = $reward_result->fetch_assoc()['total_points'] ?? 0;

// Updated SQL query to fetch purchase history with pick_up type
$purchase_query = "
    SELECT o.order_id, o.order_date, o.total_amount, o.pick_up, o.schedule_type,
           oi.item_description, oi.item_type, oi.quantity, oi.price, 
           oi.total_price, t.transaction_date, t.transaction_amount, 
           t.transaction_type
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN transactions t ON o.order_id = t.order_id
    WHERE o.member_id = ?
    ORDER BY o.order_date DESC, oi.item_type, oi.item_description
";

$stmt = $conn->prepare($purchase_query);
if (!$stmt) {
    die("Failed to prepare purchase query: " . $conn->error);
}
$stmt->bind_param("i", $member_id);
$stmt->execute();
$purchase_history = $stmt->get_result();

if (isset($_POST['pick_up'])) {
    $_SESSION['pick_up'] = $_POST['pick_up'];
}

if (isset($_POST['schedule_type'])) {
    $_SESSION['schedule_type'] = $_POST['schedule_type'];
}

// Group purchases by pick_up type
$online_orders = [];
$retail_orders = [];
while ($row = $purchase_history->fetch_assoc()) {
    if ($row['pick_up'] === 'online') {
        $online_orders[] = $row;
    } else {
        $retail_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
</head>
<style>
/* Main Container */
.container {
    display: flex;
    gap: 30px;
    margin: 20px auto;
    max-width: 1000px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

/* Account Info Styling */
.account-info {
    flex: 1;
    padding-right: 20px;
    border-right: 1px solid #ddd;
}

.account-info h2 {
    font-size: 1.8rem;
    margin-bottom: 20px;
    font-weight: bold;
}

.reward-points {
    background-color: #e6f7ff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.reward-points h3 {
    font-size: 2rem;
    color: #0073e6;
    margin: 0;
}

.account-info nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.account-info nav ul li {
    margin: 15px 0;
}

.account-info nav ul li a {
    text-decoration: none;
    color: #0073e6;
    font-size: 1.1rem;
}

.account-info nav ul li a.active {
    font-weight: bold;
    color: #005bb5;
}

/* Contact Button Styling */
.contact-us {
    margin-top: 30px;
    padding: 12px 25px;
    background-color: #0073e6;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.1rem;
    transition: background-color 0.3s ease;
}

.contact-us:hover {
    background-color: #005bb5;
}

/* Purchase History Styling */
.purchase-history {
    flex: 2;
    display: flex;
    flex-direction: column; 
    gap: 15px; /* Adds space between items */
}

.purchase-item {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 100%; /* Ensures items do not shrink and take full available width */
    box-sizing: border-box; /* Prevents shrinking by accounting for padding */
}

.purchase-item p {
    margin: 5px 0;
}

.expand-order {
    background: #e91e63;
    color: white;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 1rem;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.expand-order:hover {
    background: #c2185b;
}

        </style>
<body>

<div class="container">
    <div class="account-info">
        <h2>Your Account</h2>
        <div class="reward-points">
            <p>Reward Points</p>
            <h3><?php echo htmlspecialchars($total_reward_points); ?> pts</h3>
        </div>
        <nav>
            <ul>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="purchase_history.php" class="active">Purchase History</a></li>
                <li><a href="redeem_voucher.php">My Rewards Page</a></li>
            </ul>
        </nav>
    </div>

    <!-- Online Orders Section -->
    <div class="purchase-history">
        <h2>Online Purchase</h2>
        <div class="purchase-section">
            <?php if (!empty($online_orders)): ?>
                <?php 
                $current_order_id = null;
                foreach ($online_orders as $purchase): 
                ?>
                    <?php if ($current_order_id !== $purchase['order_id']): ?>
                        <?php if ($current_order_id !== null): ?>
                            <form method="GET" action="checkout.php">
                                <input type="hidden" name="reorder" value="true">
                                <input type="hidden" name="order_id" value="<?php echo $current_order_id; ?>">
                                <button type="submit" class="expand-order">Reorder</button>
                            </form>
                            </div>
                        <?php endif; ?>
                        
                        <div class="purchase-item">
                            <h4>Order #: <?php echo htmlspecialchars($purchase['order_id']); ?></h4>
                            <p>Date: <?php echo htmlspecialchars($purchase['order_date']); ?></p>
                            <p>Total Amount: RM <?php echo htmlspecialchars($purchase['total_amount']); ?></p>
                            <div class="order-items">
                        <?php 
                        $current_order_id = $purchase['order_id'];
                    endif; 
                    ?>
                <?php endforeach; ?>
                <?php if ($current_order_id !== null): ?>
                    <form method="GET" action="checkout.php">
                        <input type="hidden" name="reorder" value="true">
                        <input type="hidden" name="order_id" value="<?php echo $current_order_id; ?>">
                        <button type="submit" class="expand-order">Reorder</button>
                    </form>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No online orders found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Retail Orders Section -->
    <div class="purchase-history">
        <h2>Retail Store</h2>
        <div class="purchase-section">
            <?php if (!empty($retail_orders)): ?>
                <?php 
                $current_order_id = null;
                foreach ($retail_orders as $purchase): 
                ?>
                    <?php if ($current_order_id !== $purchase['order_id']): ?>
                        <?php if ($current_order_id !== null): ?>
                            <form method="GET" action="checkout.php">
                                <input type="hidden" name="reorder" value="true">
                                <input type="hidden" name="order_id" value="<?php echo $current_order_id; ?>">
                                <button type="submit" class="expand-order">Reorder</button>
                            </form>
                            </div>
                        <?php endif; ?>
                        
                        <div class="purchase-item">
                            <h4>Order #: <?php echo htmlspecialchars($purchase['order_id']); ?></h4>
                            <p>Date: <?php echo htmlspecialchars($purchase['order_date']); ?></p>
                            <p>Total Amount: RM <?php echo htmlspecialchars($purchase['total_amount']); ?></p>
                            <div class="order-items">
                        <?php 
                        $current_order_id = $purchase['order_id'];
                    endif; 
                    ?>
                <?php endforeach; ?>
                <?php if ($current_order_id !== null): ?>
                    <form method="GET" action="checkout.php">
                        <input type="hidden" name="reorder" value="true">
                        <input type="hidden" name="order_id" value="<?php echo $current_order_id; ?>">
                        <button type="submit" class="expand-order">Reorder</button>
                    </form>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>No retail store orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

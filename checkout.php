<?php
session_start();
include("config.php");
include("functions.php");
include("navbar.php"); 

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize session variables
if (!isset($_SESSION['selected_services'])) {
    $_SESSION['selected_services'] = [];
}

if (isset($_POST['schedule_type'])) {
    $_SESSION['schedule_type'] = $_POST['schedule_type'];
}

if (isset($_POST['pick_up'])) {
    $_SESSION['pick_up'] = $_POST['pick_up'];
}


//store the data in schedule_date session
if (isset($_POST['schedule_date']) && !empty($_POST['schedule_date'])) {
    $_SESSION['schedule_date'] = $_POST['schedule_date'];
} else {
    unset($_SESSION['schedule_date']); // Clear the date if none is selected
}


// Voucher handling
$voucher_discount = 0;
if (isset($_SESSION['selected_voucher']) && !empty($_SESSION['selected_voucher']['redeemed_vouchers'])) {
    $voucher_discount = $_SESSION['selected_voucher']['redeemed_vouchers'];
} else {
    $voucher_discount = 0; // No voucher applied or no discount
    unset($_SESSION['selected_voucher']); // Ensure no invalid voucher is stored
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_voucher'])) {
    // Remove the voucher from the session
    unset($_SESSION['selected_voucher']);
    // Reset the voucher discount to 0
    $voucher_discount = 0;

    // Redirect to the same page to reflect the changes
    header("Location: checkout.php");
    exit();
}

// Set default values
$pick_up = isset($_POST['pick_up']) ? $_POST['pick_up'] : 'online';
$schedule_type = isset($_POST['schedule_type']) ? $_POST['schedule_type'] : 'express';
$_SESSION['pick_up'] = $pick_up;
$_SESSION['schedule_type'] = $schedule_type;

// Get member information
$member_id = (int)$_SESSION['member_id'];
$member_fname = isset($_SESSION['member_fname']) ? htmlspecialchars($_SESSION['member_fname'], ENT_QUOTES, 'UTF-8') : '';
$member_lname = isset($_SESSION['member_lname']) ? htmlspecialchars($_SESSION['member_lname'], ENT_QUOTES, 'UTF-8') : '';
$member_name = trim("$member_fname $member_lname");

// Security measure
session_regenerate_id(true);

// Fetch necessary data
$cart_items = getCartItems($conn, $member_id);
$member_address = getMemberAddress($conn, $member_id);
$subscription_status = getSubscriptionStatus($conn, $member_id);
$is_subscribed = (isset($subscription_status['member_subscriptionplan']) && $subscription_status['member_subscriptionplan'] === 'Active');

// Constants
$free_delivery_threshold = 200.00;
$sarawak_fee = 25.00;
$other_states_fee = 45.00;
$other_states_discounted_fee = 35.00;

// Get available redeem points and handle redeem points submission
$available_redeem_points = $member_address['member_availableredeempoints'];

// Check if redeem points have been applied
if (isset($_POST['redeem_points']) && $_POST['redeem_points'] == '1') {
    // Convert points to RM (100 points = RM 1)
    $redeem_discount = floor($available_redeem_points / 100);
    
    // Store the redeem discount in session
    $_SESSION['redeem_discount'] = $redeem_discount;
} else {
    $_SESSION['redeem_discount'] = 0; // Reset if no redeem points
}

// Recalculate totals with updated redeem discount
list($subtotal, $delivery_fee) = calculateTotalPrice(
    $cart_items, 
    $_SESSION['selected_services'], 
    $free_delivery_threshold, 
    $member_address['member_state'], 
    $sarawak_fee, 
    $other_states_fee, 
    $other_states_discounted_fee
);

// Recalculate the total (including redeem discount)
$total = $subtotal + $delivery_fee - $voucher_discount - $_SESSION['redeem_discount'];

// Store updated checkout summary
$_SESSION['checkout_summary'] = [
    'subtotal' => $subtotal,
    'delivery_fee' => $delivery_fee,
    'redeem_discount' => $_SESSION['redeem_discount'],
    'voucher_discount' => $voucher_discount,
    'total' => $total,
];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['redeem_points']) && $_POST['redeem_points'] == '1') {
        // Calculate the points to redeem
        $points_to_redeem = floor($available_redeem_points / 100);

        // Check if the user has enough points to redeem
        if ($available_redeem_points >= 100) {
            // Update points in the database
            $new_points = $available_redeem_points - ($points_to_redeem * 100);
            $query = "UPDATE members SET member_availableredeempoints = ? WHERE member_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $new_points, $member_id);

            if ($stmt->execute()) {
                echo "Points redeemed successfully!";
            } else {
                echo "Error redeeming points.";
            }
        } else {
            echo "Not enough points to redeem.";
        }
    }
}

// Insert order details into the delivery_details table
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now'])) {
    // Assuming you already have the $total, $schedule_type, and $pick_up info.
    $order_id = uniqid(); // Or retrieve from your order table logic
    $delivery_address = htmlspecialchars("{$member_address['member_address']}");
    $member_id = (int)$_SESSION['member_id'];  // From session
    $schedule_type = $_SESSION['schedule_type'];
    $pick_up = $_SESSION['pick_up'];
    $schedule_date = isset($_SESSION['schedule_date']) ? $_SESSION['schedule_date'] : NULL;  // Handle schedule date if exists

    // Create a new delivery record
    $query = "INSERT INTO delivery_details (order_id, delivery_address, member_id, schedule_type, pick_up, schedule_date) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisss", $order_id, $delivery_address, $member_id, $schedule_type, $pick_up, $schedule_date);

    if ($stmt->execute()) {
        echo "Delivery details saved successfully!";
    } else {
        echo "Error saving delivery details.";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="card.css">
</head>
<style> 
.checkout-container {
    width: 80%;
    margin: auto;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.checkout-container h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.order-summary {
    margin-bottom: 30px;
}

.order-summary h3 {
    font-size: 18px;
    color: #555;
    margin-bottom: 10px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 5px;
}

.delivery-address h4 {
    font-size: 18px;
    color: #555;
    margin-bottom: 10px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 5px;
}

ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

ul li {
    font-size: 16px;
    margin-bottom: 10px;
    color: #666;
    display: flex;
    justify-content: space-between;
}

ul li span {
    font-weight: bold;
}

p {
    font-size: 16px;
    color: #444;
    margin-bottom: 10px;
}

.payment-methods, .action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.payment-methods button, .action-buttons button {
    flex: 1;
    padding: 15px;
    margin: 5px;
    background-color: #4CAF50;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
    transition: all 0.3s ease-in-out;
}

.payment-methods button:hover, .action-buttons button:hover {
    background-color: #3e8e41;
}

.order-summary .subtotal, .order-summary .total {
    font-weight: bold;
    color: #333;
}

.order-summary .total {
    font-size: 18px;
    border-top: 2px solid #ddd;
    margin-top: 15px;
    padding-top: 10px;
}

.order-summary .totals {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-top: 20px;
}

.totals p {
    margin: 0;
    font-size: 16px;
    color: #444;
    text-align: right;
    line-height: 1.6;
    width: 200px; /* Ensure all elements have the same width */
    display: flex;
    justify-content: space-between;
}

.totals .total {
    font-size: 18px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 2px solid #ddd;
    width: 200px; /* Match the width of other elements */
    display: flex;
    justify-content: space-between;
    font-weight: bold;
}

.redeem-points {
    font-size: 14px; /* Smaller font size */
    color: #666; /* Lighter color to make it visually subtle */
    padding-top: 10px;
    padding-bottom: 10px;
}

.redeem-points label {
    font-weight: normal; /* Reduce font weight */
}

.redeem-points p {
    font-size: 12px; /* Smaller size for the discount amount */
    margin-top: 5px; /* Separate it slightly from the label */
    color: #888; /* Even lighter color for the discount */
}
.alert {
    font-size: 12px; /* Smaller font size */
    color: #f44336; /* Red color for alert */
    font-weight: normal; /* Make the text weight normal */
    margin-top: 10px;
}

.alert a {
    font-size: 14px; /* A bit larger size for the link */
    color: #007BFF; /* Link color */
    text-decoration: none; /* Remove underline */
}

.alert a:hover {
    text-decoration: underline; /* Underline on hover */
}
</style> 
<body>

    <div class="checkout-container">
        <h1>Checkout</h1>
        <div class="delivery-address">
            <h4>Delivery Address</h4>
            <p>
                <?php
                if ($member_address) {
                    echo htmlspecialchars("{$member_address['member_address']}, {$member_address['member_city']}, {$member_address['member_state']}");
                } else {
                    echo "No address found for this member.";
                }
                ?>
            </p>
        </div>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <h4>Products</h4>
            <ul>
                <?php
                if ($cart_items) {
                    $cart_items->data_seek(0);
                    while ($row = $cart_items->fetch_assoc()) {
                        echo "<li>" . htmlspecialchars($row['product_name']) . " (RM " . 
                        number_format($row['product_price'], 2) . " x " . 
                        $row['quantity'] . " ) = RM " . 
                        number_format($row['product_price'] * $row['quantity'], 2) . "</li>";
                    }
                }
                ?>
            </ul>

            <h4>Services</h4>
            <ul>
                <?php
                if (count($_SESSION['selected_services']) > 0) {
                    foreach ($_SESSION['selected_services'] as $service) {
                        switch ($service) {
                            case 'set-up':
                                echo "<li>Set-up Service - RM 50</li>";
                                break;
                            case 'cleaning':
                                echo "<li>Cleaning Service - RM 15</li>";
                                break;
                            case 'safety-check':
                                echo "<li>Safety Check Service - RM 10</li>";
                                break;
                        }
                    }
                } else {
                    echo "<li>No services selected.</li>";
                }
                ?>
            </ul>

            <div class="totals">
                <p>Subtotal: <span>RM <?php echo number_format($subtotal, 2); ?></span></p>
                <p>Delivery Fee: <span>RM <?php echo number_format($delivery_fee, 2); ?></span></p>
                
                                <!-- Combined Form for Redeem Points, Schedule Type, and Pick-up Options -->
                <form method="POST">

                    <!-- Voucher Section -->
                    <div class="voucher-section">
                        <?php if (isset($_SESSION['selected_voucher']) && $voucher_discount > 0): ?>
                        <div class="active-voucher">
                            <p>Voucher Applied: RM <?php echo number_format($voucher_discount, 2); ?></p>
                            <button type="submit" name="cancel_voucher" class="cancel-voucher-btn">Cancel Voucher</button>
                        </div>
                        <?php else: ?>
                            <a href="redeem_voucher.php" class="voucher-link">Click here to see your available vouchers</a>
                        <?php endif; ?>
                    </div>

                    <!-- Redeem Points Section -->
                    <div class="redeem-points">
                        <?php if ($available_redeem_points >= 100): ?>
                        <label>
                            <input type="checkbox" name="redeem_points" value="1" 
                            <?php echo (isset($_POST['redeem_points']) && $_POST['redeem_points'] == '1') ? 'checked' : ''; ?>>
                            Points: <?php echo number_format($available_redeem_points); ?><br>
                            Use Redeem Points: RM <?php echo floor($available_redeem_points / 100); ?><br>
                        </label>
                        <?php else: ?>
                            <p>Points: <?php echo number_format($available_redeem_points); ?></p>
                            <p class="alert">Not enough points to redeem. You need at least 100 points to use them.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Schedule and Pick-up Options -->
                    <div class="delivery-options">
                        <div>
                            <label for="schedule_type">Please choose your schedule type:</label><br>
                                <input type="radio" id="express" name="schedule_type" value="express" 
                            <?php echo (isset($_SESSION['schedule_type']) && $_SESSION['schedule_type'] === 'express') ? 'checked' : ''; ?>>
                            <label for="express">Express</label>
                                <input type="radio" id="schedule" name="schedule_type" value="schedule" 
                            <?php echo (isset($_SESSION['schedule_type']) && $_SESSION['schedule_type'] === 'schedule') ? 'checked' : ''; ?>>
                            <label for="schedule">Scheduled</label>
                        </div>

                        <!--new added for schdule date-->

                        <div id="schedule-date-container" style="display: none;">
                            <label for="schedule_date">Select a date:</label><br>
                            <input type="date" id="schedule_date" name="schedule_date" value="<?php echo isset($_SESSION['schedule_date']) ? $_SESSION['schedule_date'] : ''; ?>">
                        </div>

                        <script>
                            // Show or hide the date input based on the selected schedule type
                            const scheduleTypeInputs = document.querySelectorAll('input[name="schedule_type"]');
                            const scheduleDateContainer = document.getElementById('schedule-date-container');

                            scheduleTypeInputs.forEach(input => {
                                input.addEventListener('change', () => {
                                    if (input.value === 'schedule') {
                                        scheduleDateContainer.style.display = 'block';
                                    } else {
                                        scheduleDateContainer.style.display = 'none';
                                    }
                                });
                            });

                            // Initial check to display the date input if "Scheduled" is selected
                            if (document.getElementById('schedule').checked) {
                                scheduleDateContainer.style.display = 'block';
                            }
                        </script>

                        <div>
                            <label for="pick_up">Please choose your pick-up option:</label><br>
                                <input type="radio" id="online" name="pick_up" value="online" 
                            <?php echo (isset($_SESSION['pick_up']) && $_SESSION['pick_up'] === 'online') ? 'checked' : ''; ?>>
                            <label for="online">Online</label>
                                <input type="radio" id="retail" name="pick_up" value="retail" 
                            <?php echo (isset($_SESSION['pick_up']) && $_SESSION['pick_up'] === 'retail') ? 'checked' : ''; ?>>
                            <label for="retail">Retail</label>
                        </div>
                    </div>

                    <!-- Single Apply Button -->
                    <button type="submit" class="apply-button">Apply</button>
                </form>

            <!-- Display Discounts and Total -->
            <p class="total">Total: <span id="total-amount">RM <?php echo number_format($total, 2); ?></span></p>
        </div>

            <!-- Payment Methods -->
            <div class="payment-methods">
                <h3>Payment Method</h3>
                <form method="POST" action="backend.php">
                    <div>
                        <input type="radio" id="card" name="payment_method" value="card" 
                            <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'card') ? 'checked' : ''; ?>>
                        <label for="card">Pay Now with Card</label>
                    </div>
                    <div>
                        <input type="radio" id="fpx" name="payment_method" value="fpx" 
                            <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'fpx') ? 'checked' : ''; ?>>
                        <label for="fpx">Pay Now with FPX</label>
                    </div>
                    <button type="submit" name="pay_now" class="pay-now-btn">Pay Now</button>
                </form>
            </div>
        </div>

    <div class="action-buttons">
        <button type="button" class="cancel-btn" onclick="window.location.href='member-cart.php'">Cancel</button>
    </div>  

    <div class="contact-us">
        <img src="/img/phoneicon.png" alt="Phone" id="phone-icon">
        <a href="contact.php" id="contact-text">Contact Us</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>

<?php
session_start();
include("config.php");
include("navbar.php");

// Check if the user is logged in
$is_logged_in = isset($_SESSION['member_id']);
$first_name = "Guest";
$success_message = '';
$customer = null;
$reward_points = 0;

if (!$is_logged_in) {
    // User is not logged in, prompt for registration
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hawra Trading - Register</title>
        <link rel="stylesheet" href="/hawra_trading/CSS/public_registration.css">
    </head>
    <body>
        <div class="register-container">
            <h1>Welcome to Hawra Trading</h1>
            <p>You do not have an account with us. Create your account now to enjoy our services!</p>
            <a href="/hawra_trading/PHP/registration.php">
                <button type="button">Create Account</button>
            </a>
        </div>
    </body>
    </html>';
    exit;
} else {
    // Fetch member ID from session
    $member_id = $_SESSION['member_id'];

    // Fetch user data
    $user_query = "SELECT * FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $first_name = $customer['member_fname'];
    }

    // Fetch reward points
    $reward_points = $customer['member_availableredeempoints'] ?? 0; // Default to 0 if NULL
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        


/* Main Section */
main {
    display: flex;
    justify-content: center;
    padding: 20px;
    background-color: #f8e9f1;
}

.account-section {
    display: flex;
    gap: 20px;
    width: 80%;
    max-width: 1200px;
}

/* Left Sidebar */
.left-bar {
    flex: 1;
    background-color: #f8e9f1;
    padding: 20px;
    border: 1px solid #f8e9f1;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.left-bar h2 {
    font-size: 20px;
    margin-bottom: 10px;
}

.left-bar p {
    margin-bottom: 10px;
    font-weight: bold;
}

.left-bar ul {
    list-style-type: none;
}

.left-bar ul li {
    margin-bottom: 10px;
}

.left-bar ul li a {
    text-decoration: none;
    color: #007bff;
    transition: color 0.3s;
}

.left-bar ul li a:hover {
    color: #0056b3;
}

/* Right Content */
.right-bar {
    flex: 2;
    background-color: #f8e9f1;
    padding: 20px;
    border: 1px solid #f8e9f1;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.right-bar h2 {
    font-size: 22px;
    margin-bottom: 20px;
}

.right-bar p {
    margin-bottom: 10px;
    font-size: 16px;
}

.right-bar strong {
    color: #007bff;
}

/* Responsive Design */
@media (max-width: 768px) {
    .account-section {
        flex-direction: column;
    }

    .left-bar, .right-bar {
        flex: unset;
        width: 100%;
    }
}
footer {
    position: fixed;
    bottom: 0;
    left: 0;
    padding: 20px;
    color: #fff;
    width: auto;
}
        </style>
</head>
<body>

<main>
    <div class="account-section">
        <div class="left-bar">
            <h2>Your Account</h2>
            <p>Reward Points: <strong><?= htmlspecialchars($reward_points); ?> pts</strong></p>
            <ul>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="purchase_history.php">Purchase History</a></li>
                <li><a href="redeem_voucher.php">My Rewards Page</a></li>
                <li><a href="Newsletter.php">Subscribe Newsletter</a></li>
            </ul>
        </div>
        <div class="right-bar">
            <h2>Welcome, <?= htmlspecialchars($first_name); ?></h2>
            <p>Email: <?= $customer ? htmlspecialchars($customer['member_email']) : "Not available"; ?></p>
            <p>Phone: <?= $customer ? htmlspecialchars($customer['member_contactno']) : "Not available"; ?></p>
            <p>Address: <?= $customer ? htmlspecialchars($customer['member_address']) : "Not available"; ?></p>
            <p>City: <?= $customer ? htmlspecialchars($customer['member_city']) : "Not available"; ?></p>
            <p>State: <?= $customer ? htmlspecialchars($customer['member_state']) : "Not available"; ?></p>
        </div>
    </div>
</main>

<footer>
    <div class="contact-us">
        <img src="/img/phoneicon.png" alt="Phone" id="phone-icon">
        <a href="contact.php" id="contact-text">Contact Us</a>
    </div>
</footer>
</body>
</html>

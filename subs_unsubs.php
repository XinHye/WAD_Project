<?php
include 'config.php'; 
include 'navbar.php'; 
session_start();
$errors = [];
$success_message = '';


// Check if user is logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member_id']; // Use 'member_id' directly from session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['subscribe'])) {
        // Subscribe logic: Update subscription status to 'Active' and set subscription_date
        $stmt = $conn->prepare(
            "UPDATE members SET member_subscriptionplan = 'Active', subscription_date = NOW(), member_availableredeempoints = member_availableredeempoints + 100 WHERE member_id = ? "
        );
        $stmt->bind_param("i", $member_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success_message = "Your subscription has been activated! You have received 100 loyalty points.";
        } else {
            $errors[] = "Failed to activate your subscription. Please try again.";
        }
        $stmt->close();
    } elseif (isset($_POST['unsubscribe'])) {
        // Unsubscribe logic: Update subscription status to 'Inactive', set subscription_date to NULL and unsubscription_date
        $stmt = $conn->prepare(
            "UPDATE members SET member_subscriptionplan = 'Inactive', subscription_date = NULL, unsubscription_date = NOW() WHERE member_id = ?"
        );
        $stmt->bind_param("i", $member_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success_message = "Your subscription has been canceled.";
        } else {
            $errors[] = "Failed to cancel your subscription. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Management</title>
</head>
<style> 
/* Container styling */
.container {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    text-align: center;
}

.container h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #4caf50;
}

.container .errors {
    background-color: #ffdddd;
    color: #d8000c;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.container .success {
    background-color: #ddffdd;
    color: #4caf50;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
}

/* Button styling */
button {
    padding: 10px 20px;
    font-size: 16px;
    margin: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.subscribe-btn {
    background-color: #4caf50;
    color: white;
}

.subscribe-btn:hover {
    background-color: #45a049;
}

.unsubscribe-btn {
    background-color: #f44336;
    color: white;
}

.unsubscribe-btn:hover {
    background-color: #d32f2f;
}

/* Responsive styling */
@media (max-width: 768px) {
    .container {
        width: 90%;
        padding: 15px;
    }

    header .main-nav h1 {
        font-size: 20px;
    }

    header .main-nav .nav-right a {
        font-size: 14px;
    }

    button {
        font-size: 14px;
        padding: 8px 15px;
    }
}
</style> 
<body>
<div class="container">
    <h2>Manage Your Subscription</h2>
    <?php if (!empty($errors)) : ?>
        <div class="errors">
            <?php foreach ($errors as $error) : ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message) : ?>
        <div class="success">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <h3>Become our member to collect your loyalty points on every purchase or cancel your subscription.</h3>
    <form method="POST">
        <p>What are the benefits?</p>
        <ul>
            <li>Receive exclusive offers.</li>
            <li>Rewards for loyalty.</li>
        </ul>
        <button type="submit" name="subscribe" class="subscribe-btn">Subscribe</button>
        <button type="submit" name="unsubscribe" class="unsubscribe-btn">Unsubscribe</button>
    </form>
</div>

<div class="contact-us">
    <img src="/img/phoneicon.png" alt="Phone" id="phone-icon">
    <a href="contact.php" id="contact-text">Contact Us</a>
</div>

<?php if ($success_message): ?>
    <script type="text/javascript">
        alert("<?php echo addslashes($success_message); ?>");
    </script>
<?php elseif (!empty($errors)): ?>
    <script type="text/javascript">
        alert("<?php echo addslashes(implode(", ", $errors)); ?>");
    </script>
<?php endif; ?>

</body>
</html>

<?php
session_start();
include("config.php");
include("navbar.php");


$member_id = $_SESSION['member_id'];

// Fetch user reward points
$query = "SELECT member_availableredeempoints FROM members WHERE member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $member_availableredeempoints = $row['member_availableredeempoints'];
} else {
    $member_availableredeempoints = 0; // Set reward points to 0 if no query results are found
}
$stmt->close();

// Initialize variables
$errors = [];  // Initialize the errors array
$show_popup = false;  // Initialize the popup variable

// Handle newsletter form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_fname = isset($_POST['member_fname']) ? trim($_POST['member_fname']) : '';
    $member_lname = isset($_POST['member_lname']) ? trim($_POST['member_lname']) : '';
    $member_email = isset($_POST['member_email']) ? trim($_POST['member_email']) : '';

    // Validate form inputs
    if (empty($member_fname) || empty($member_lname) || empty($member_email)) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($member_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } else {
        $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (member_id, member_fname, member_lname, member_email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $member_id, $member_fname, $member_lname, $member_email);

        if ($stmt->execute()) {
            $success_message = 'Thank you for subscribing to our newsletter!';
            $show_popup = true; // Show success popup
        } else {
            $errors[] = 'Failed to subscribe. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
    <style>
        
/* Main Content Styling */
main {
    display: flex;
    flex-wrap: wrap;
    padding: 20px;
    justify-content: center;
}

.account-section {
    display: flex;
    width: 100%;
    max-width: 1200px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.left-bar {
    background-color: #f9f1ff;
    padding: 20px;
    flex: 1;
}

.left-bar h2 {
    font-size: 20px;
    margin-bottom: 10px;
}

.left-bar p {
    font-size: 16px;
    margin: 5px 0;
}

.left-bar ul {
    list-style: none;
    padding: 0;
}

.left-bar ul li {
    margin: 10px 0;
}

.left-bar ul li a {
    text-decoration: none;
    color: #007bff;
    font-size: 14px;
}

.right-bar {
    flex: 2;
    padding: 20px;
}

.right-bar h2 {
    font-size: 22px;
    margin-bottom: 10px;
}

.right-bar p {
    font-size: 16px;
    margin: 10px 0;
}

form {
    display: flex;
    flex-direction: column;
}

form input {
    margin: 10px 0;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form button {
    padding: 10px;
    background-color: #007bff;
    color: #fff;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

form button:hover {
    background-color: #0056b3;
}

.success-message {
    color: green;
    margin-bottom: 10px;
}

.error-messages {
    color: red;
    margin-bottom: 10px;
    list-style: none;
    padding: 0;
}

.error-messages li {
    margin: 5px 0;
}

/* Footer Styling */
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
            <p>Reward Points: <strong><?= htmlspecialchars($member_availableredeempoints); ?> pts</strong></p>
            <ul>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="purchase_history.php">Purchase History</a></li>
                <li><a href="redeem_voucher.php">Your Rewards</a></li>
            </ul>
        </div>
        <div class="right-bar">
            <h2>Be The First to Get The News!</h2>
            <p>Subscribe to get special offers, recent announcements & be the first to know about new promotions!</p>
            
            <!-- Display Error Messages -->
            <?php if ($errors): ?>
                <ul class="error-messages" style="color: red;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <form action="newsletter.php" method="POST">
                <input type="text" name="member_fname" placeholder="Your First Name" required>
                <input type="text" name="member_lname" placeholder="Your Last Name" required>
                <input type="email" name="member_email" placeholder="Your Email" required>
                <button type="submit">Join</button>
            </form>
        </div>
    </div>
</main>

<footer>
    <a href="contact.php">Contact Us</a>
</footer>

<!-- Popup Success Message -->
<?php if ($show_popup): ?>
    <script type="text/javascript">
        alert(<?= json_encode($success_message); ?>);
        window.location.href = 'index.php'; // Redirect after successful subscription
    </script>
<?php endif; ?>

</body>
</html>

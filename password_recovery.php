<?php
session_start();
$errors = [];
$success_message = '';

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'hawra_trading';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email exists in the members table
        $stmt = $conn->prepare("SELECT member_id FROM members WHERE member_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the new password in the members table
            $updateStmt = $conn->prepare("UPDATE members SET member_password = ? WHERE member_email = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $email);

            if ($updateStmt->execute()) {
                // Send the email
                $to = $email;
                $subject = 'Password Recovery for Hawra Trading';
                $msg = "
                    <html>
                    <head>
                        <title>Password Recovery</title>
                    </head>
                    <body>
                        <p>Dear user,</p>
                        <p>Your new password is: <strong>$newPassword</strong></p>
                        <p>Please change it after logging in.</p>
                        <p>Best regards,<br>Hawra Trading Team</p>
                    </body>
                    </html>";

                // Email headers
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Hawra Trading <hawratrading@gmail.com>\r\n";
                $headers .= "Reply-To: hawratrading@gmail.com\r\n";
                $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                $headers .= "Bcc: hawratrading@gmail.com\r\n"; // For debugging

                if (mail($to, $subject, $msg, $headers)) {
                    $success_message = "An email with your new password has been sent to $email.";
                } else {
                    $errors[] = "Failed to send the email. Please try again later.";
                }
            } else {
                $errors[] = "Failed to update the password in the database.";
            }

            $updateStmt->close();
        } else {
            $errors[] = "No account found with that email address.";
        }

        $stmt->close();
    } else {
        $errors[] = "Invalid email address. Please enter a valid email.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery</title>
    <link rel="stylesheet" href="/hawra_trading/CSS/passwordrecovery.css">
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
            <a href="logout.php">Sign Out</a>
            <a href="member-cart.php">Cart</a>
            <a href="profile.php">Profile</a>
        </div>
    </nav>
    <div class="sub-nav">
        <a href="index.php">Home</a>
        <a href="categories.php">Categories</a>
        <a href="messages.php">Messages</a>
    </div>
</header>

<div class="password-recovery-container">
    <h1>Password Forgotten?</h1>
    <p>If you've forgotten your password, enter your email address below and we'll send you an email message containing your new password.</p>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Enter your E-mail address here..." required>
        <button type="submit">Submit</button>
    </form>
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

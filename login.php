<?php
session_start();
$errors = [];

include("config.php");
include("functions.php");

// Redirect to categories if user is already logged in
if (isset($_SESSION['member_id'])) {
    header("Location: categories.php?member_id=" . $_SESSION['member_id']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $errors[] = "Both fields are required";
    } else {
        // Check user credentials
        $query = "SELECT * FROM members WHERE member_email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['member_password'])) {
                // Set session variables and redirect to profile
                $_SESSION['member_id'] = $row['member_id'];
                $_SESSION['member_fname'] = $row['member_fname'];
                $_SESSION['member_lname'] = $row['member_lname'];
                $_SESSION['member_email'] = $row['member_email'];
                $_SESSION['member_subscriptionplan'] = $row['member_subscriptionplan'];
                header("Location: categories.php?member_id=" . $_SESSION['member_id']);
                exit;
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "No account found with this email";
        }
    }
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawra Trading - Login</title>
    <link rel="stylesheet" href="style1.css">
</head>
<style> 
/* Main Content Styling */
.content-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px; /* Spacing between slogan/image and login box */
    margin-top: 60px;
    flex-wrap: wrap;
}

.content {
    text-align: center;
    max-width: 400px;
    margin-right: auto;
    margin-left: auto;

}

.content img {
    width: 350%; /* Make image responsive */
    max-width: 350px;
    margin-top: 20px auto 0;
    display: block;
}

.content p {
    font-size: 45px;
    font-weight: bold;
    margin: 0;
    line-height: 1.2;
    text-align: justify;
}

/* Login Form Styling */
.login-form {
    background-color: #e4c2d4; /* Soft pink for form background */
    flex: 1;
    margin: 0;
    margin-right: 5%; /* Slightly move the form to the left */
    margin-left: auto; /* Auto-adjust right margin */
    padding: 60px;
    max-width: 600px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    text-align: left;
    font-weight: bold;
}

.login-form h2 {
    font-size: 35px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
}

.login-form label {
    font-weight: bold;
    display: block;
    margin: 10px 0 5px;
}

.login-form input {
    width: 100%;
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.password-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.show-password-label {
    font-size: 0.8em;
    cursor: pointer;
}

button {
    width: 100%;
    background-color: green; /* Green for button */
    color: white;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}

button:hover {
    background-color: #444; /* Dark gray for hover */
}

/* Register link styling */
.register-link {
    text-align: center;
    font-size: 1rem;
    margin-top: 10px;
}

.register-link a:hover {
    text-decoration: underline;
}

.admin-login {
    text-align: center;
    font-size: 1rem;
    margin-top: 10px;
}

.admin-login a:hover {
    text-decoration: underline;
}

/* Error Messages */
.error-messages {
    background-color: #ffcccc; /* Light red for error box */
    color: #cc0000; /* Dark red text */
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}

.error {
    font-size: 0.9em;
}

/* Footer Styling */
footer {
    background-color: #000; /* Black background */
    color: white;
    text-align: center;
    padding: 10px;
    position: fixed;
    bottom: 0;
    width: 100%;
}

footer a {
    color: white;
    text-decoration: none;
}

footer a:hover {
    text-decoration: underline;
}

/* Media Queries for Consistency */
@media screen and (max-width: 1300px) {
    .content-container {
        flex-direction: column; /* Stack content vertically */
        margin-top: 20px;
    }

    .content p {
        font-size: 20px; /* Slightly smaller font for mobile */
        text-align: center;
    }

    .content img {
        max-width: 150px; /* Adjust image size */
        margin: auto 0;
    }

    .login-form {
        max-width: 550px; /* Keep size manageable */
        margin: auto 0;
        padding: 20px;
        align-items: center;
    }

    .login-form h2 {
        font-size: 30px; /* Adjust header size */
    }

    .sub-nav, .nav-right {
        flex-wrap: nowrap; /* Avoid items wrapping */
        overflow-x: auto; /* Enable horizontal scrolling if needed */
    }

    .nav-center input[type="search"] {
        width: 250px; /* Reduce search bar size */
    }
</style>
<body>
    <main>

    <div class="content-container">
        <div class="content">
            <p>You Order,</p>
            <p>We Deliver.</p>
            <img src="img/logo.gif" alt="Hawra Trading Logo">
        </div>

        <div class="login-form">
            <h2>Login</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">

                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                    <label class="show-password-label" style="display: flex; justify-content: center; align-items: center;">
                        <input type="checkbox" onclick="togglePasswordVisibility()"> Show Password
                    </label>
                </div>

                <button type="submit">Login</button>
            </form>
            <p class="register-link">Don't have an account? <a href="registration.php">Register</a>.</p>
            <p class="admin-login">Register as <a href="loginadmin.php">Administrator</a>.</p>
        </div>
    </main>
    <footer>
        <a href="contact.php">Contact Us</a>
    </footer>

    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>
</body>
</html>
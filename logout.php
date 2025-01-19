<?php
session_start();
if (isset($_SESSION['member_id'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();
}

include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
</head>
<style> 
/* Main container styling */
main {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    flex-direction: column;
}

/* Logout Confirmation Styling */
.logout-container {
    background-color: #e4c2d4; /* Soft pink for container background */
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.logout-container h2 {
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
}

.logout-container p {
    font-size: 1rem;
    margin-bottom: 20px;
}

.logout-container a {
    display: inline-block;
    background-color: green;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-top: 10px;
    align-items: center; 
}

.logout-container a:hover {
    background-color: #444;
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
    .nav-center input[type="search"] {
        width: 250px; /* Reduce search bar size */
    }

    .sub-nav, .nav-right {
        flex-wrap: nowrap; /* Avoid items wrapping */
        overflow-x: auto; /* Enable horizontal scrolling if needed */
    }

    .logout-container {
        margin-top: 140px; /* Adjust for smaller screens */
    }
}
</style> 
<body>
    <main>
        <div class="logout-container">
            <h2>You have been logged out!</h2>
            <p>Thank you for visiting Hawra Trading. Come back soon.</p>
            <a href="login.php">Login Again</a>
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
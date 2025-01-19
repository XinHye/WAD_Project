<?php
session_start();
?>

<style> 
/* General Reset */
body, h1, h2, p, a, input, img, select, button {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body Styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f8e9f1; /* Light pink background */
    color: #333;
}

/* Header Styling */
header {
    background-color: #000; /* Black background */
    color: white;
    padding: 10px 20px;
}
/* Main Navigation Styling */
.main-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.nav-left h1 {
    font-size: 1.5rem;
}

.nav-center input[type="search"] {
    width: 300px;
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.nav-right a {
    margin-left: 20px;
    color: white;
    text-decoration: none;
}

.nav-right a:hover {
    text-decoration: underline;
}

/* Sub Navigation Styling */
.sub-nav {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 10px 0;
    background-color: #000; /* Black background */
    flex-wrap: wrap;
}

.sub-nav a {
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.sub-nav a:hover {
    text-decoration: underline;
}

.contact-us {
    position: fixed;
    bottom: 20px;
    left: 20px;
    display: flex;
    align-items: center;
    background-color: white;
    padding: 10px;
    border-radius: 50px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

#phone-icon {
    width: 35px;
    cursor: pointer;
    transition: transform 0.2s ease; /* Add hover animation */
}

#contact-text {
    display: none; /* Initially hidden */
    font-size: 15px;
    color: black;
    margin-left: 10px;
    text-decoration: none;
}

.contact-us:hover #contact-text {
    display: inline-block; /* Show text when hovering over the container */ 
}

#contact-text:hover {
    text-decoration: underline;
    color: blue;
}
</style> 
<!-- Navbar -->
<header>
    <nav class="main-nav">
        <div class="nav-left">
            <h1>Hawra Trading</h1>
        </div>
        <div class="nav-center">
            <!-- Search form -->
            <form method="GET" action="add-to-cart.php">
                <input type="search" name="search" placeholder="Search">
                <button type="submit">Search</button>
            </form>
        </div> 
        <!-- registered user --> 
        <?php if (isset($_SESSION['member_id'])): ?> 
        <div class="nav-right">
            <a href="logout.php">Sign out</a>
            <a href="member-cart.php">Cart</a>
            <a href="profile.php">Profile</a>
        </div>
        <?php else: ?> 
         <div class="nav-right">
            <a href="login.php">Sign in</a>
            <a href="member-cart.php">Cart</a>
            <a href="login.php">Profile</a>
        </div>
        <?php endif; ?>
    </nav>
    <div class="sub-nav">
        <a href="index.php">Home</a>
        <a href="categories.php">Categories</a>
        <a href="messages.php">Messages</a>
    </div>
</header>

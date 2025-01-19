<?php
session_start();
// Check if user is logged in

include 'config.php'; 
include 'navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hawra Trading</title>
    <!-- Use relative paths for external files -->
    <link rel="stylesheet" href="style.css">
</head>
<style> 
#categories-image {
    display: flex; /* Use Flexbox for alignment */
    justify-content: center; /* Center items horizontally */
    flex-wrap: wrap; /* Allow images to wrap to the next line on smaller screens */
    gap: 50px; /* Add space between images */
    padding: 110px; /* Add padding around the container */
    text-align: center; /* Center the content */
}

#categories-image a img {
    flex: 1 1 calc(33.33% - 20px); /* Three images per row */
    max-width: 300px;
    width: 100%;/* Images can scale down with the container */
    height: auto; /* Maintain aspect ratio */
    border-radius: 10px; /* Optional rounded corners */
    transition: transform 0.3s; /* Add a hover effect */
}

#categories-image a img:hover {
    transform: scale(1.2); /* Slight zoom effect on hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional shadow on hover */
}
</style> 
<body>
        <div id="categories-image">
            <a href="add-to-cart.php"><img src="img/1.jpg" alt="cooking-gas-delivery"></a>
            <a href="additionalservices.php"><img src="img/3.jpg" alt="subscribe_unsubscribe"></a>
            <a href="subs_unsubs.php"><img src="img/2.jpg" alt="additional-service"></a> 
        </div>
    <footer>
        <div class="contact-us">
            <img src="img/phoneicon.png" alt="Phone" id="phone-icon">
            <a href="contact.php" id="contact-text">Contact Us</a>
        </div>
    </footer>
        
</body>
</html>

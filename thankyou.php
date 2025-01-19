<?php 
include 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
</head>
<style> 
        /* Thank You Confirmation Styling */
        .thankyou-container {
            justify-content: center; 
            background-color: #e4c2d4; /* Soft pink for container background */
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin-top: 100px; /* Offset for fixed header */
            margin-left: 500px; 
        }

        .thankyou-container h2 {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
            text-align: center; 
        }

        .thankyou-container p {
            font-size: 1rem;
            margin-bottom: 20px;
            text-align: center; 
        }

        .thankyou-container a {
            display: inline-block;
            background-color: green;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
            margin-left: 100px;  
            text-align: center; 
            align-items: center; 
        }

        .thankyou-container a:hover {
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

            .thankyou-container {
                margin-top: 140px; /* Adjust for smaller screens */
            }
        }
    </style>
<body>
    <main>
        <div class="thankyou-container">
            <h2>Thank You for Your Order!</h2>
            <p>Your order has been successfully placed. We appreciate your trust in Hawra Trading.</p>
            <p>We hope you enjoy your purchase. If you have any questions or need further assistance, feel free to contact us.</p>
            <a href="add-to-cart.php" class="back-to-home-btn">Back to Shopping!</a>
        </div>
    </main>
   <footer>
        <div class="contact-us">
            <img src="img/phoneicon.png" alt="Phone" id="phone-icon">
            <a href="contact.php" id="contact-text">Contact Us</a>
        </div>
    </footer>
</body>
</html>

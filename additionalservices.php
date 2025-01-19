<?php
        session_start();
        include 'config.php';
        include 'navbar.php';
        ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Details</title>
    <style>

        h1 {
            margin: 20px 0;
            text-align: center; 
            align-items: center;
        }

        .image-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .image-container img {
            width: 200px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .text-box {
            margin-top: 20px;
            width: 80%;
            max-width: 600px;
            align-items: center;
        }

        .service-info {
            margin-top: 20px;
            width: 80%;
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-left: auto; /* Centering the service-info box */
            margin-right: auto;
        }

        .service-info h2 {
            margin: 0;
            font-size: 20px;
        }

        .service-info p {
            margin: 5px 0;
        }

        .service-info .price {
            font-weight: bold;
            color: #b44e70;
        }
    </style>
</head>
<body>
    <h1>Service Details</h1>
    <div class="image-container">
        <img src="img/4.jpg" alt="Image 1">
        <img src="img/5.jpg" alt="Image 2">
        <img src="img/6.jpg" alt="Image 3">
    </div>

    <div class="service-info">
    <?php 
    // Fetch data from the service_details table
        $sql = "SELECT * FROM service_details";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output data for each row
            while($row = $result->fetch_assoc()) {
                echo "<h2>" . $row["ServiceName"] . "</h2>";
                echo "<p class='price'>Price: RM" . $row["ServicePrice"] . "</p>";
                echo "<p>" . $row["ServiceDescription"] . "</p><hr>";
            }
        } else {
            echo "No services available.";
        }

        $conn->close();
        ?>
        
    </div>
    <footer>
        <div class="contact-us">
            <img src="img/phoneicon.png" alt="Phone" id="phone-icon">
            <a href="contact.php" id="contact-text">Contact Us</a>
        </div>
    </footer>
</body>
</html>

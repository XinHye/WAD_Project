<?php 
include 'config.php';
include 'navbar.php'; 
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking</title>
    <link rel="stylesheet" href="style1.css">
</head>
<style> 
.del-container {
    max-width: 600px;
    margin: 10px auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

header h1 {
    font-size: 1.8rem;
    text-align: center; 
}

main {
    padding: 20px;
}

.tracking-info, .delivery-details {
    margin-bottom: 20px;
    text-align: center;
}

p {
    margin-bottom: 10px;
}

.progress-bar-container {
    background: #e0e0e0;
    border-radius: 8px;
    height: 10px;
    width: 100%;
    margin: 10px 0;
    overflow: hidden;
}

.progress-bar {
    background: #f90c92;
    height: 100%;
    width: 0%;
    transition: width 0.4s ease;
}

/* Responsive Design */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    header h1 {
        font-size: 1.5rem;
        text-align: center;
    }
}

#start-delivering {
    font-size: 1rem;
    color: #555;
    margin-top: 10px;
}
</style>

<body>

<div class="del-container">
    <header>
        <h1>Track Your Order</h1>
    </header>
    <main>
        <!-- Search Form -->
        <form id="search-form">
            <label for="order-id">Enter Order ID:</label><br>
            <input type="text" id="order-id" name="order-id" required><br><br>
            <button type="submit">Track Order</button>
        </form>

        <div class="tracking-info" style="display: none;" id="tracking-info">
            <h2>Order Status</h2>
            <p id="order-status">Loading...</p>
            <div class="progress-bar-container">
                <div id="progress-bar" class="progress-bar"></div>
            </div>
            <p id="delivery-status"></p>
        </div>

        <div class="delivery-details" style="display: none;" id="delivery-details">
            <h3>Delivery Details</h3>
            <p id="start-delivering" style="display: none;"><strong>Start Delivering On:</strong> <span id="scheduled-date"></span></p>
            <p><strong>Delivery Address:</strong> <span id="delivery-address">Loading...</span></p>
            <p><strong>Schedule Type:</strong> <span id="schedule-type">Loading...</span></p>
            <p><strong>Delivery Status:</strong> <span id="delivery-status-detail">Loading...</span></p>
        </div>
    </main>
</div>

<script>
    document.getElementById("search-form").addEventListener("submit", function (event) {
        event.preventDefault(); // Prevent form submission
        const orderId = document.getElementById("order-id").value.trim();

        if (orderId) {
            fetchOrderDetails(orderId);
        } else {
            alert("Please enter a valid order ID.");
        }
    });

    function fetchOrderDetails(orderId) {
        const apiUrl = `getOrderDetails.php?order_id=${orderId}`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    displayError(data.error);
                } else {
                    updateUI(data);
                }
            })
            .catch(() => displayError("Failed to load order details.")); 
    }

    function updateUI(data) {
        document.getElementById("tracking-info").style.display = 'block';
        document.getElementById("delivery-details").style.display = 'block';

        document.getElementById("order-status").innerText = data.order_status;
        document.getElementById("progress-bar").style.width = `${data.progress}%`;
        document.getElementById("delivery-status").innerText = data.delivery_status;
        document.getElementById("delivery-address").innerText = data.delivery_address;
        document.getElementById("schedule-type").innerText = data.schedule_type;
        document.getElementById("delivery-status-detail").innerText = data.delivery_status;

        if (data.schedule_type === 'Scheduled' && data.delivery_status.includes('Start Delivering')) {
            const scheduledDate = data.scheduled_date;
            document.getElementById("start-delivering").style.display = 'block';
            document.getElementById("scheduled-date").innerText = scheduledDate;
        } else {
            document.getElementById("start-delivering").style.display = 'none';
        }
    }

    function displayError(message) {
        document.getElementById("tracking-info").style.display = 'none';
        document.getElementById("delivery-details").style.display = 'none';
        alert(message);
    }
</script>

</body>
</html>

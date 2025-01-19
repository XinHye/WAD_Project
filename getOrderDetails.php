<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Allow CORS (optional, but needed if the frontend is on a different domain)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'config.php';

// Connect to the database
$pdo = pdo_connect_mysql();

// Get the order ID from the query string
$order_id = $_GET['order_id'] ?? '';

// Validate the input
if (!$order_id) {
    echo json_encode(['error' => 'Order ID is required']);
    exit;
}

try {
    // Query the database for the delivery details
    $query = $pdo->prepare('SELECT * FROM delivery_details WHERE order_id = :order_id');
    $query->execute(['order_id' => $order_id]);
    $delivery = $query->fetch(PDO::FETCH_ASSOC);

    // If no delivery details are found, return an error
    if (!$delivery) {
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    // Prepare the response data
    $response = [
        'order_status' => '',
        'progress' => 0,
        'delivery_status' => '',
        'delivery_address' => $delivery['delivery_address'],
        'schedule_type' => ucfirst($delivery['schedule_type']),
        'scheduled_date' => $delivery['scheduled_date'] ?? null,
    ];

    // Determine order status and progress based on `delivery_status`
    switch ($delivery['delivery_status']) {
        case 'order_created':
            $response['order_status'] = 'Order Created';
            $response['progress'] = 10;
            $response['delivery_status'] = 'Your order has been created!';
            break;
        case 'in_delivery':
            $response['order_status'] = 'In Delivery';
            $response['progress'] = 50;
            $response['delivery_status'] = 'Your order is on the way!';
            break;
        case 'delivered':
            $response['order_status'] = 'Delivered';
            $response['progress'] = 100;
            $response['delivery_status'] = 'Your order has been delivered!';
            break;
        case 'not_yet_delivered':
            $response['order_status'] = 'Not Yet Delivered';
            $response['progress'] = 0;
            $response['delivery_status'] = 'Start Delivering on: ' . date('d M Y', strtotime($delivery['scheduled_date']));
            break;
        default:
            $response['order_status'] = 'Unknown Status';
            $response['progress'] = 0;
            $response['delivery_status'] = 'We are unable to track your order at the moment.';
            break;
    }

    // Return the response in JSON format
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database query failed']);
    error_log($e->getMessage()); // Log the error for debugging
}
?>

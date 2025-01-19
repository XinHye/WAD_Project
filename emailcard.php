<?php
session_start();
include("config.php");
include("functions.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_email'])) {
    if (!isset($_SESSION['member_id'])) {
        echo "error: User not logged in";
        exit();
    }

    $member_id = $_SESSION['member_id'];
    
    // Get user email
    $sql = "SELECT member_email FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $member_email = $row['member_email'];
    $stmt->close();

    if ($member_email) {
        // Retrieve necessary data
        $cart_items = getCartItems($conn, $member_id);
        $member_address = getMemberAddress($conn, $member_id);
        $checkout_summary = $_SESSION['checkout_summary'] ?? [];
        $payment_intent = $_POST['payment_intent'] ?? 'N/A';
        $order_id = $_SESSION['order_id'] ?? 'N/A';
        $order_date = $_SESSION['order_date'] ?? 'N/A';
    
        // Extract checkout summary details
        $subtotal = $checkout_summary['subtotal'] ?? 0;
        $delivery_fee = $checkout_summary['delivery_fee'] ?? 0;
        $redeem_discount = $checkout_summary['redeem_discount'] ?? 0;
        $voucher_discount = $checkout_summary['voucher_discount'] ?? 0;
        $total = $checkout_summary['total'] ?? 0;

        // Email setup
        $to = $member_email; // User's email address
        $subject = "Your Purchase Receipt - Hawra Trading";
        
        // Modified headers
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Hawra Trading <hawratrading@gmail.com>\r\n";
        $headers .= "Reply-To: hawratrading@gmail.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // For debugging - send a copy to hawratrading@gmail.com
        $headers .= "Bcc: hawratrading@gmail.com\r\n";

        // Create email body
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; }
                .header { background: #f5f5f5; padding: 20px; }
                .content { padding: 20px; }
                .total { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Purchase Receipt</h1>
                    <p>Payment ID: {$payment_intent}</p>
                    <p>Order ID: " . htmlspecialchars($order_id) . "</p>
                    <p>Order Date: " . htmlspecialchars($order_date) . "</p>
                </div>
                <div class='content'>
                    <h3>Order Summary</h3>
                    <h4>Products</h4>
                    <ul>";
        
        if (!empty($cart_items)) {
            foreach ($cart_items as $item) {
                $message .= "<li>" . htmlspecialchars($item['product_name']) . 
                           " x" . intval($item['quantity']) . 
                           " - RM " . number_format($item['product_price'], 2) . "</li>";
            }
        }

        $message .= "</ul>
                    <h4>Selected Services</h4>
                    <ul>";

        if (isset($_SESSION['selected_services']) && count($_SESSION['selected_services']) > 0) {
            foreach ($_SESSION['selected_services'] as $service) {
                switch ($service) {
                    case 'set-up':
                        $message .= "<li>Set-up Service - RM 70</li>";
                        break;
                    case 'cleaning':
                        $message .= "<li>Cleaning Service - RM 30</li>";
                        break;
                    case 'safety-check':
                        $message .= "<li>Safety Check Service - RM 50</li>";
                        break;
                }
            }
        }

        $message .= "</ul>
                    <div class='total'>
                        <p>Subtotal: RM " . number_format($subtotal, 2) . "</p>
                        <p>Delivery Fee: RM " . number_format($delivery_fee, 2) . "</p>
                        <p>Redeem Discount: - RM " . number_format($redeem_discount, 2) . "</p>
                        <p>Voucher Discount: - RM " . number_format($voucher_discount, 2) . "</p>
                        <p>Total: RM " . number_format($total, 2) . "</p>
                    </div>
                    <div class='delivery'>
                        <h3>Delivery Address</h3>
                        <p>" . htmlspecialchars($member_address['member_address']) . ", " .
                               htmlspecialchars($member_address['member_city']) . ", " .
                               htmlspecialchars($member_address['member_state']) . "</p>
                    </div>
                    <div class='payment'>
                        <h3>Payment Method</h3>
                        <p>Paid via Card</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";

        // Add debug logging
        error_log("Attempting to send email to: " . $member_email);

        // Send email
        if (mail($to, $subject, $message, $headers)) {
            // Clear cart after successful email
            $clear_cart_sql = "DELETE FROM cart WHERE member_id = ?";
            $stmt = $conn->prepare($clear_cart_sql);
            $stmt->bind_param("i", $member_id);
            $stmt->execute();
            $stmt->close();
            
            // Clear relevant session variables
            unset($_SESSION['checkout_summary']);
            unset($_SESSION['selected_services']);
            
            error_log("Email sent successfully to: " . $member_email);
            echo "success";
        } else {
            error_log("Failed to send email to: " . $member_email);
            echo "error: Failed to send email";
        }
    } else {
        echo "error: Email not found";
    }
} else {
    echo "error: Invalid request";
}
?>
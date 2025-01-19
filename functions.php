<?php
function getCartItems($conn, $member_id) {
    $sql = "SELECT c.cart_id, p.product_name, p.product_price, c.quantity 
            FROM carts c 
            INNER JOIN products p ON c.product_id = p.product_id 
            WHERE c.member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getMemberAddress($conn, $member_id) {
    $sql = "SELECT member_address, member_city, member_state, member_availableredeempoints 
            FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getSubscriptionStatus($conn, $member_id) {
    $query = "SELECT member_subscriptionplan FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to display selected services and their prices
function display_selected_services() {
    // Ensure the session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Ensure services array is always set
    if (!isset($_SESSION['selected_services'])) {
        $_SESSION['selected_services'] = [];
    }

    // Fetch and display selected services with their prices
    if (is_array($_SESSION['selected_services']) && count($_SESSION['selected_services']) > 0) {
        foreach ($_SESSION['selected_services'] as $service) {
            if ($service == 'set-up') {
                echo "<li>Set-up Service - RM 50</li>";
            } elseif ($service == 'cleaning') {
                echo "<li>Cleaning Service - RM 15</li>";
            } elseif ($service == 'safety-check') {
                echo "<li>Safety Check Service - RM 10</li>";
            }
        }
    } else {
        echo "<li>No services selected.</li>";
    }
}

function calculateTotalPrice($cart_items, $selected_services, $free_delivery_threshold, $member_state, $sarawak_fee, $other_states_fee, $other_states_discounted_fee) {
    $total_price = 0;
    while ($row = $cart_items->fetch_assoc()) {
        // Make sure the price and quantity are valid numbers
        $total_price += (float)$row['product_price'] * (int)$row['quantity'];
    }

    $service_cost = 0;
    foreach ($selected_services as $service) {
        if ($service == 'set-up') {
            $service_cost += 50;
        } elseif ($service == 'cleaning') {
            $service_cost += 15;
        } elseif ($service == 'safety-check') {
            $service_cost += 10;
        }
    }

    $subtotal = $total_price + $service_cost;

    if ($subtotal >= $free_delivery_threshold) {
        if ($member_state === "Sarawak") {
            $delivery_fee = 0.00; // Free delivery for Sarawak
        } else {
            $delivery_fee = $other_states_discounted_fee;
        }
    } else {
        $delivery_fee = ($member_state === "Sarawak") ? $sarawak_fee : $other_states_fee;
    }

    return [$subtotal, $delivery_fee];
}
?>

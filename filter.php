<?php
    require 'config.php';
    
    // Ensure the connection is open
    if (!isset($conn) || $conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $filter = $_POST['filter'] ?? 'all';
    $month = $_POST['month'] ?? '';

    // Base query to fetch all records
    $sql = "SELECT * FROM transactions";
    if ($filter === 'all') {
        $sql .= " ORDER BY transaction_date DESC";
    } elseif ($filter === 'daily') {
        $sql .= " WHERE DATE(transaction_date) = CURDATE() ORDER BY transaction_date DESC";
    } elseif ($filter === 'weekly') {
        $sql .= " WHERE WEEK(transaction_date, 1) = WEEK(CURDATE(), 1) ORDER BY transaction_date DESC";
    } elseif ($filter === 'monthly' && !empty($month)) {
        $sql .= " WHERE MONTH(transaction_date) = ? ORDER BY transaction_date DESC";
    } elseif ($filter === 'custom') {
        $sql .= " " . $queryCondition . " ORDER BY transaction_date DESC";
    } else {
        $sql .= " ORDER BY transaction_date DESC";
    }

    $transactionStmt = $conn->prepare($sql);

    if ($filter === 'monthly' && !empty($month)) {
        $transactionStmt->bind_param("i", $month);
    }

    $transactionStmt->execute();
    $result = $transactionStmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>Transaction ID</th><th>Order ID</th><th>Member ID</th><th>Amount (RM)</th><th>Type</th><th>Date</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['transaction_id']}</td>";
            echo "<td>{$row['order_id']}</td>";
            echo "<td>{$row['member_id']}</td>";            
            echo "<td>{$row['transaction_amount']}</td>";
            echo "<td>{$row['transaction_type']}</td>";
            echo "<td>" . date('d M, Y', strtotime($row['transaction_date'])) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No transactions found for the selected filter.</td></tr>";
    }

    $transactionStmt->close();
?>
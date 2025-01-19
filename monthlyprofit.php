<?php
    include 'config.php';

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch monthly transaction summary
    $query = "
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') AS month,
            COUNT(transaction_id) AS total_transactions,
            SUM(transaction_amount) AS total_income
        FROM transactions
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month DESC;
    ";

    $result = $conn->query($query);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Report</title>
        <style>
        .month {
            font-size: 15px;
            font-weight: bold; 
            color:rgb(85, 85, 85);
        }
        /* Monthly Profit Card Styling */
        .profit {
            color: green; /* Text color */
            font-size: 38px; 
            font-weight: bold; 
        }
        </style>
</head>

<body>
    <?php if ($result->num_rows > 0): ?>
        <div class="month">
            <?php 
                $row = $result->fetch_assoc(); 
                echo "-- " . date('F Y', strtotime($row['month'])) . " --";
            ?>
        </div>
        <div class="profit">
            </br>
            <?php
                echo "+ RM " . number_format($row['total_income'], 2); 
            ?>
        </div>
    <?php else: ?>
        <tr>
            <td colspan="3">No data available</td>
        </tr>
    <?php endif; ?>
<body>
</html>

<?php
// Close the connection
$conn->close();
?>
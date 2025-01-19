<?php
session_start();
include("config.php");
include("navbar.php");
include("functions.php");


$vouchers_query = "SELECT * FROM vouchers";
$vouchers_result = $conn->query($vouchers_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_voucher'])) {
    $voucher_id = $_POST['voucher_id'];
    $redeemed_vouchers = (float) $_POST['redeemed_vouchers'];
    $_SESSION['selected_voucher'] = [
        'voucher_id' => $voucher_id,
        'redeemed_vouchers' => $redeemed_vouchers,
    ];

    header("Location: checkout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redeem Voucher</title>
    <style>
.rewards-container {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
}
.rewards-header {
    font-size: 18px;
    color: #333;
    margin-bottom: 10px;
}
.rewards-count {
    color: #666;
    font-size: 14px;
    margin-left: 5px;
}
.rewards-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.reward-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #e7d0dc;
    padding: 15px;
    border-radius: 8px;
}
.reward-info {
    color: #333;
    font-size: 14px;
}
.redeem-button {
    background-color: lightgray;
    border: 1px solid #ddd;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    text-transform: uppercase;
    font-size: 12px;
}
.redeem-button:hover {
    background-color: #f8f8f8;
}
        </style>
</head>


<body>
    <div class="rewards-container">
        <div class="rewards-header">
            MY REWARDS PAGE
            <span class="rewards-count">(<?= $vouchers_result->num_rows ?>)</span>
        </div>
        <div class="rewards-list">
            <?php if ($vouchers_result && $vouchers_result->num_rows > 0): ?>
                <?php while ($voucher = $vouchers_result->fetch_assoc()): ?>
                    <div class="reward-item">
                        <div class="reward-info">
                            RM<?= number_format($voucher['discount_value'], 2); ?> off<br>
                            on orders over RM100  T&C applied.
                        </div>
                        <form method="post">
                            <input type="hidden" name="voucher_id" value="<?= $voucher['voucher_id']; ?>">
                            <input type="hidden" name="redeemed_vouchers" value="<?= $voucher['discount_value']; ?>">
                            <button type="submit" name="select_voucher" class="redeem-button">Redeem</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No rewards available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

<?php
$conn->close();
?>

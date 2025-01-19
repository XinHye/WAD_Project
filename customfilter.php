<?php
    require 'config.php';
    date_default_timezone_set('Asia/Kuala_Lumpur');
    // Ensure the connection is open
    if (!isset($conn) || $conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initial SQL to fetch all records
    $sql = "SELECT * FROM transactions";
    $result = $conn->query($sql);
    $post_at = "";
    $post_at_to_date = "";

    $queryCondition = "";
    if (!empty($_POST["search"]["post_at"])) {			
        $post_at = $_POST["search"]["post_at"];
        list($fid, $fim, $fiy) = explode("-", $post_at);
        
        $post_at_todate = date('Y-m-d');
        if (!empty($_POST["search"]["post_at_to_date"])) {
            $post_at_to_date = $_POST["search"]["post_at_to_date"];
            list($tid, $tim, $tiy) = explode("-", $_POST["search"]["post_at_to_date"]);
            $post_at_todate = "$tiy-$tim-$tid";
        }
        
        // Query condition to select from the correct table (transaction_table)
        $post_at_todate = date('Y-m-d', strtotime($post_at_todate . ' +1 day'));
        $queryCondition .= "WHERE transaction_date BETWEEN '$fiy-$fim-$fid' AND '" . $post_at_todate . "'";
    }
?>
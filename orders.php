<?php
include "db.php";
if(!isset($_SESSION['uid'])){ header("Location: login.php"); exit(); }
$uid = $_SESSION['uid'];
?>
<link rel="stylesheet" href="css/style.css">
<div class="header">
    <h2>🕒 Your Order Details</h2>
    <a href="shop.php">Back to Store</a>
</div>

<div style="padding:40px; max-width:1000px; margin:auto;">
    <p>Order history for: <b><?= $_SESSION['uname'] ?></b></p>
    
    <table border="1" style="width:100%; border-collapse:collapse; background:white; text-align:center;">
        <tr style="background:#1e3a8a; color:white;">
            <th style="padding:12px;">Order ID</th>
            <th>Date & Time</th> <!-- NEW COLUMN -->
            <th>Product Name</th>
            <th>Qty</th>
            <th>Total Paid</th>
        </tr>
        <?php
        // We added 'ORDER BY created_at DESC' to show newest orders first
        $res = $conn->query("SELECT o.*, p.name FROM orders o JOIN products p ON o.pid = p.pid WHERE o.uid = $uid ORDER BY o.created_at DESC");
        
        if($res->num_rows > 0) {
            while($row = $res->fetch_assoc()){
                // Format the date nicely
                $orderDate = date("d M Y, h:i A", strtotime($row['created_at']));
                
                echo "<tr>
                        <td style='padding:10px;'>#{$row['oid']}</td>
                        <td style='color:#666; font-size:13px;'>$orderDate</td>
                        <td>{$row['name']}</td>
                        <td>{$row['quantity']}</td>
                        <td>Rs {$row['total']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No orders found in your timeline.</td></tr>";
        }
        ?>
    </table>

    <?php
    // Grand Total calculation from DB
    $q_total = "SELECT SUM(total) AS grand_total FROM orders WHERE uid = $uid";
    $res_total = $conn->query($q_total);
    $data = $res_total->fetch_assoc();
    $db_grand_total = $data['grand_total'] ?? 0;
    ?>

    <div style="margin-top:20px; padding:20px; background:#f0f9ff; border:2px solid #1e3a8a; text-align:right;">
        <strong style="font-size:22px;">
            Total: <span style="color:#dc2626;">Rs <?= $db_grand_total ?></span>
        </strong>
    </div>
</div>
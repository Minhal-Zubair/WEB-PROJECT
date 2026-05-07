<?php
include "db.php";
$uid = $_SESSION['uid'];

// Fetch everything currently in this user's cart
$res = $conn->query("SELECT cart.*, products.price FROM cart JOIN products ON cart.pid = products.pid WHERE uid=$uid");

if($res->num_rows > 0) {
    while($row = $res->fetch_assoc()){
        // Calculate the permanent total for this line item
        $line_total = $row['price'] * $row['quantity'];
        $pid = $row['pid'];
        $qty = $row['quantity'];
        
        // SAVE TO ORDERS TABLE (Storing the final total in DB)
        $conn->query("INSERT INTO orders (uid, pid, quantity, total) VALUES ($uid, $pid, $qty, $line_total)");
    }
    // Wipe cart after successful storage
    $conn->query("DELETE FROM cart WHERE uid=$uid");
}

header("Location: orders.php");
?>
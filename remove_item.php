<?php
include "db.php";

$uid=$_SESSION['uid'];
$pid=$_GET['pid'];

$conn->query("DELETE FROM cart
WHERE uid=$uid AND pid=$pid");

header("Location:shop.php");
?>
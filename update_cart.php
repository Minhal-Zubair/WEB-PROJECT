<?php
include "db.php";

$uid=$_SESSION['uid'];
$pid=$_POST['pid'];
$q=$_POST['qty'];

$conn->query("UPDATE cart
SET quantity=$q
WHERE uid=$uid AND pid=$pid");

header("Location:shop.php");
?>
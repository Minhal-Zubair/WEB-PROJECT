<?php
include "db.php";

$uid=$_SESSION['uid'];
$pid=$_POST['pid'];

//check if product is already in cart
$check=$conn->query("SELECT * FROM cart
WHERE uid=$uid AND pid=$pid");

//if already exist in cart
if($check->num_rows>0)
$conn->query("UPDATE cart SET quantity=quantity+1
WHERE uid=$uid AND pid=$pid");
else
//if not exist add new entry to cart
$conn->query("INSERT INTO cart(uid,pid,quantity)
VALUES($uid,$pid,1)");

header("Location:shop.php");
?>
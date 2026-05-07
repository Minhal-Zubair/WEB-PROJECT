<div class="cart">

<h3>Your Cart</h3>

<?php
$uid=$_SESSION['uid'];

$q="SELECT cart.*,products.name,products.price
FROM cart
JOIN products ON cart.pid=products.pid
WHERE uid=$uid";

$res=$conn->query($q);

$total=0;

while($row=$res->fetch_assoc()){

$sub=$row['price']*$row['quantity'];
$total+=$sub;
?>

<div class="cart-item">

<b><?=$row['name']?></b>

<form action="update_cart.php" method="post">
<input type="hidden" name="pid" value="<?=$row['pid']?>">
<input type="number" name="qty"
value="<?=$row['quantity']?>" min="1">
<button class="btn">Update</button>
</form>

<p>Subtotal Rs <?=$sub?></p>

<a href="remove_item.php?pid=<?=$row['pid']?>">Remove</a>

</div>

<?php } ?>

<div class="total">
Total: Rs <?=$total?>
</div>

<a class="checkout" href="checkout.php">Checkout</a>

</div>
</div>
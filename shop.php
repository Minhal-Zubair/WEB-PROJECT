<?php
include "db.php";
//verify session
if(!isset($_SESSION['uid'])){ header("Location: login.php"); exit(); }

// Trigger alert if just logged in
if(isset($_SESSION['login_success'])) {
    echo "<script>alert('" . $_SESSION['login_success'] . "');</script>";
    unset($_SESSION['login_success']);
}
?>
<link rel="stylesheet" href="css/style.css">

<div class="header">
    <h2>🛒 Online Store</h2>
    <div>
        <!-- SESSION VERIFICATION BADGE -->
        <span style="background:#ffd700; color:#000; padding:5px 10px; border-radius:15px; font-weight:bold; margin-right:10px; font-size:12px;">
            Verified: <?= $_SESSION['uname'] ?> (ID: <?= $_SESSION['uid'] ?>)
        </span>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="products">
    <?php
    $cat = $conn->query("SELECT DISTINCT category FROM products"); // get all unique categories from products table
    //loop through each category
    while($c = $cat->fetch_assoc()){ 
        echo "<div class='category'>".$c['category']."</div>"; //display category name as section header
        $p = $conn->query("SELECT * FROM products WHERE category='".$c['category']."'"); // Fetch products belonging to current category
        //loop though each product in this category
        while($row = $p->fetch_assoc()){
    ?>
        <div class="card">
            <img src="<?=$row['image']?>" alt="product">
            <!-- product image loaded from database -->
            <h4><?=$row['name']?></h4>
            <!-- product name -->
            <p class="price">Rs <?=$row['price']?></p>
            <!-- product price -->
            <form action="add_to_cart.php" method="post">
                <input type="hidden" name="pid" value="<?=$row['pid']?>">
                <!-- hidden field to pass product id to add_to_cart.php -->
                <button class="btn">Add To Cart</button>
            </form>
        </div>
    <?php } } ?>
    </div>

    <!-- CART PANEL (REAL-TIME DISPLAY) -->
    <div class="cart">
        <h3>🧾 Your Cart</h3>
        <?php
        $uid = $_SESSION['uid'];
        $res = $conn->query("SELECT cart.*, products.name, products.price FROM cart JOIN products ON cart.pid = products.pid WHERE cart.uid = $uid"); // Fetch cart items for the current user
        $total = 0;
        while($row = $res->fetch_assoc()){
            $sub = $row['price'] * $row['quantity'];
            $total += $sub;
        ?>

        <div class="cart-item">
    <b><?=$row['name']?></b>
    <form action="update_cart.php" method="post" style="display:flex; gap:5px; align-items:center; margin:5px 0;">
        <input type="hidden" name="pid" value="<?=$row['pid']?>">
        Qty: <input type="number" name="qty" value="<?=$row['quantity']?>" min="1" style="width:50px;">
        <button class="btn" style="padding:2px 8px; font-size:11px;">Update</button>
    </form>
    <p>Subtotal: Rs <?=$sub?></p>
    <a href="remove_item.php?pid=<?=$row['pid']?>" style="color:red; font-size:12px;">❌ Remove Item</a>
</div>
        <?php } ?>
        <div class="total">Grand Total: Rs <?=$total?></div>
        <?php if($total > 0) { echo '<a class="checkout" href="checkout.php">✅ Checkout Now</a>'; } ?>
    </div>
</div>

<!-- SESSION DEBUG INFO -->
<div style="position:fixed; bottom:5px; left:5px; font-size:10px; color:#666;">
    SESS_ID: <?= session_id() ?> | UID: <?= $_SESSION['uid'] ?>
</div>
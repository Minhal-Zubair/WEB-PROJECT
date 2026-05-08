<?php
include "db.php";
$message = ""; // store error message

//only run if login form is submitted
if(isset($_POST['login'])) {
    $u = $_POST['username']; //store username entered by user in variable $u
    $p = $_POST['password']; //store password entered by user in variable $p

    $q = "SELECT * FROM users WHERE username='$u'";
    $res = $conn->query($q); //store result from db

    if($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if ((int) ($row['is_active'] ?? 1) !== 1) {
            $message = "Your account is blocked. Please contact admin.";
        } elseif ($row['password'] === $p) {
            //save user id and name into the session
            $_SESSION['uid']   = $row['userid']; 
            $_SESSION['uname'] = $row['username'];
            
            //Flag to show alert on shop.php
            $_SESSION['login_success'] = "Welcome " . $row['username'] . "!";
            
            header("Location: shop.php");
            exit();
        } else {
            $message = "Invalid Username or Password";
        }
    } else {
        $message = "Invalid Username or Password";
    }
}
?>
<link rel="stylesheet" href="css/style.css">
<div style="width:320px;margin:auto;margin-top:120px;background:white;padding:25px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.2);">
    <h3 style="text-align:center;">User Login</h3>
    <?php if($message!="") echo "<p style='color:red;text-align:center;'>$message</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required style="width:100%;padding:8px;"><br><br>
        <input type="password" name="password" placeholder="Password" required style="width:100%;padding:8px;"><br><br>
        <button class="btn" name="login" style="width:100%;">Login</button>
    </form>
    <div style="text-align:center;margin-top:14px;">
        <a href="admin_login.php" style="color:#1d4ed8;text-decoration:none;font-size:14px;">Admin login</a>
    </div>
</div>
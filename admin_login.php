<?php
include "db.php";
include "admin_auth.php";

$message = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $admin = authenticate_admin($conn, $username, $password);

    if ($admin) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['username'];

        header("Location: admin_dashboard.php");
        exit();
    }

    $message = "Invalid admin username or password";
}
?>
<link rel="stylesheet" href="css/style.css">
<div style="max-width:380px;margin:90px auto;background:rgba(255,255,255,0.95);padding:28px;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.18);">
    <h2 style="text-align:center;margin-top:0;color:#0f172a;">Admin Login</h2>
    <p style="text-align:center;color:#475569;margin-top:-6px;">Sign in to manage the store</p>
    <?php if ($message !== "") echo "<p style='color:#dc2626;text-align:center;font-weight:600;'>$message</p>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Admin username" required style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;box-sizing:border-box;"><br><br>
        <input type="password" name="password" placeholder="Password" required style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;box-sizing:border-box;"><br><br>
        <button class="btn" name="login" style="width:100%;">Login</button>
    </form>
    <div style="text-align:center;margin-top:14px;">
        <a href="login.php" style="color:#1d4ed8;text-decoration:none;font-size:14px;">Back to user login</a>
    </div>
</div>

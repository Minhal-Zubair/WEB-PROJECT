<?php
include "db.php";
include "admin_auth.php";
require_admin_login();

$totalUsers = 0;
$totalProducts = 0;
$totalOrders = 0;
$totalRevenue = 0;

$usersResult = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($usersResult) {
    $totalUsers = (int) ($usersResult->fetch_assoc()['total'] ?? 0);
}

$productsResult = $conn->query("SELECT COUNT(*) AS total FROM products");
if ($productsResult) {
    $totalProducts = (int) ($productsResult->fetch_assoc()['total'] ?? 0);
}

$ordersResult = $conn->query("SELECT COUNT(*) AS total, COALESCE(SUM(total), 0) AS revenue FROM orders");
if ($ordersResult) {
    $ordersRow = $ordersResult->fetch_assoc();
    $totalOrders = (int) ($ordersRow['total'] ?? 0);
    $totalRevenue = (int) ($ordersRow['revenue'] ?? 0);
}

$recentOrders = $conn->query("SELECT o.oid, o.created_at, o.quantity, o.total, u.username, p.name AS product_name FROM orders o LEFT JOIN users u ON u.userid = o.uid LEFT JOIN products p ON p.pid = o.pid ORDER BY o.created_at DESC LIMIT 5");
?>
<link rel="stylesheet" href="css/style.css">
<div class="header">
    <h2>Admin Dashboard</h2>
    <div>
        <span style="margin-right:12px;font-size:13px;font-weight:600;">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div style="padding:25px;background:linear-gradient(120deg,#eef2f7,#e3ecf5);min-height:100vh;">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-bottom:28px;">
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #2563eb;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Total Users</div>
            <div style="font-size:36px;font-weight:700;color:#0f172a;margin-top:8px;"><?= $totalUsers ?></div>
        </div>
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #2563eb;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Total Products</div>
            <div style="font-size:36px;font-weight:700;color:#0f172a;margin-top:8px;"><?= $totalProducts ?></div>
        </div>
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #2563eb;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Total Orders</div>
            <div style="font-size:36px;font-weight:700;color:#0f172a;margin-top:8px;"><?= $totalOrders ?></div>
        </div>
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #16a34a;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Revenue</div>
            <div style="font-size:36px;font-weight:700;color:#16a34a;margin-top:8px;">Rs <?= $totalRevenue ?></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:18px;align-items:start;margin-bottom:20px;">
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);overflow:auto;">
            <h3 style="margin-top:0;color:#0f172a;">Recent Orders</h3>
            <table style="width:100%;border-collapse:collapse;min-width:600px;font-size:13px;">
                <thead>
                    <tr style="background:linear-gradient(90deg,#0f172a,#1e3a8a);color:white;font-weight:600;">
                        <th style="padding:10px;text-align:left;">Order ID</th>
                        <th style="padding:10px;text-align:left;">Customer</th>
                        <th style="padding:10px;text-align:left;">Product</th>
                        <th style="padding:10px;text-align:left;">Qty</th>
                        <th style="padding:10px;text-align:left;">Total</th>
                        <th style="padding:10px;text-align:left;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                        <?php while ($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;font-weight:600;">#<?= htmlspecialchars($row['oid']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;text-align:center;"><?= htmlspecialchars($row['quantity']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#16a34a;font-weight:600;">Rs <?= htmlspecialchars($row['total']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;"><?= htmlspecialchars($row['created_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding:14px;border-top:1px solid #e2e8f0;color:#64748b;text-align:center;">No orders found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0;color:#0f172a;">Admin Tasks</h3>
            <div style="display:grid;gap:12px;">
                <a class="btn" href="manage_products.php" style="display:block;text-align:center;text-decoration:none;">Manage Products</a>
                <a class="btn" href="manage_orders.php" style="display:block;text-align:center;text-decoration:none;">Manage Orders</a>
                <a class="btn" href="manage_users.php" style="display:block;text-align:center;text-decoration:none;">Manage Users</a>
                <a class="btn" href="reports.php" style="display:block;text-align:center;text-decoration:none;">Reports & Analytics</a>
            </div>
            <p style="margin-top:16px;color:#64748b;font-size:12px;line-height:1.5;">Manage your store from here. Click any button to access management features.</p>
        </div>
    </div>
</div>

<?php
include "db.php";
include "admin_auth.php";
require_admin_login();

$message = "";

// Handle Order Status Actions
if (isset($_POST['action'])) {
    $oid = intval($_POST['oid']);
    $action = $_POST['action'];

    $statusMap = [
        'confirm' => 'confirmed',
        'ship' => 'shipped',
        'deliver' => 'delivered',
        'cancel' => 'cancelled'
    ];

    if (isset($statusMap[$action])) {
        $newStatus = $statusMap[$action];
        $conn->query("UPDATE orders SET order_status='$newStatus' WHERE oid=$oid");
        $actionText = ucfirst($action);
        $message = "Order has been marked as $newStatus!";
    }
}

// Fetch all orders with customer and product details
$ordersResult = $conn->query("SELECT o.*, u.username, p.name AS product_name FROM orders o LEFT JOIN users u ON o.uid = u.userid LEFT JOIN products p ON o.pid = p.pid ORDER BY o.created_at DESC");
?>
<link rel="stylesheet" href="css/style.css">
<div class="header">
    <h2>Manage Orders</h2>
    <div>
        <span style="margin-right:12px;font-size:13px;font-weight:600;">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div style="padding:25px;background:linear-gradient(120deg,#eef2f7,#e3ecf5);min-height:100vh;">
    <?php if ($message): ?>
        <div style="background:#dbeafe;border:1px solid #1d4ed8;color:#1d4ed8;padding:12px;border-radius:8px;margin-bottom:20px;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);overflow-x:auto;">
        <h3 style="margin-top:0;color:#0f172a;">All Orders</h3>
        <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:1000px;">
            <thead>
                <tr style="background:linear-gradient(90deg,#0f172a,#1e3a8a);color:white;font-weight:600;">
                    <th style="padding:10px;text-align:left;">Order ID</th>
                    <th style="padding:10px;text-align:left;">Customer</th>
                    <th style="padding:10px;text-align:left;">Product</th>
                    <th style="padding:10px;text-align:left;">Qty</th>
                    <th style="padding:10px;text-align:left;">Total (Rs)</th>
                    <th style="padding:10px;text-align:left;">Date</th>
                    <th style="padding:10px;text-align:left;">Status</th>
                    <th style="padding:10px;text-align:left;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                    <?php while ($row = $ordersResult->fetch_assoc()): ?>
                        <?php 
                        $status = $row['order_status'] ?? 'pending';
                        $statusColor = [
                            'pending' => '#f59e0b',
                            'confirmed' => '#3b82f6',
                            'shipped' => '#8b5cf6',
                            'delivered' => '#10b981',
                            'cancelled' => '#ef4444'
                        ][$status] ?? '#6b7280';
                        ?>
                        <tr>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;font-weight:600;">#<?= htmlspecialchars($row['oid']) ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['product_name'] ?? 'Unknown') ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;text-align:center;"><?= htmlspecialchars($row['quantity']) ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#16a34a;font-weight:600;">Rs <?= htmlspecialchars($row['total']) ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;"><?= htmlspecialchars(substr($row['created_at'], 0, 10)) ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;">
                                <span style="background:<?= $statusColor ?>;color:white;padding:5px 10px;border-radius:6px;font-weight:600;font-size:12px;">
                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                </span>
                            </td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;">
                                <form method="post" style="display:flex;gap:4px;flex-wrap:wrap;">
                                    <input type="hidden" name="oid" value="<?= $row['oid'] ?>">
                                    
                                    <?php if ($status === 'pending'): ?>
                                        <button class="btn" name="action" value="confirm" type="submit" style="padding:5px 10px;font-size:11px;">Confirm</button>
                                    <?php endif; ?>
                                    
                                    <?php if ($status === 'confirmed'): ?>
                                        <button class="btn" name="action" value="ship" type="submit" style="padding:5px 10px;font-size:11px;">Ship</button>
                                    <?php endif; ?>
                                    
                                    <?php if ($status === 'shipped'): ?>
                                        <button class="btn" name="action" value="deliver" type="submit" style="padding:5px 10px;font-size:11px;">Deliver</button>
                                    <?php endif; ?>
                                    
                                    <?php if ($status !== 'delivered' && $status !== 'cancelled'): ?>
                                        <button class="btn" name="action" value="cancel" type="submit" style="padding:5px 10px;font-size:11px;background:linear-gradient(45deg,#dc2626,#ef4444);" onclick="return confirm('Cancel this order?')">Cancel</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="padding:14px;border-top:1px solid #e2e8f0;color:#64748b;text-align:center;">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        <a href="admin_dashboard.php" style="color:#2563eb;text-decoration:none;font-weight:600;">← Back to Dashboard</a>
    </div>
</div>

<?php
include "db.php";
include "admin_auth.php";
require_admin_login();

$fromDate = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
$toDate = $_GET['to'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = date('Y-m-d');
}
if ($fromDate > $toDate) {
    $tmp = $fromDate;
    $fromDate = $toDate;
    $toDate = $tmp;
}

$fromDateTime = $fromDate . ' 00:00:00';
$toDateTime = $toDate . ' 23:59:59';

$summary = [
    'total_orders' => 0,
    'revenue' => 0,
    'avg_order_value' => 0,
    'customers' => 0
];

$summaryStmt = $conn->prepare("SELECT COUNT(*) AS total_orders, COALESCE(SUM(total),0) AS revenue, COALESCE(AVG(total),0) AS avg_order_value, COUNT(DISTINCT uid) AS customers FROM orders WHERE created_at BETWEEN ? AND ?");
if ($summaryStmt) {
    $summaryStmt->bind_param('ss', $fromDateTime, $toDateTime);
    $summaryStmt->execute();
    $result = $summaryStmt->get_result();
    if ($result && $result->num_rows > 0) {
        $summary = $result->fetch_assoc();
    }
    $summaryStmt->close();
}

$statusData = [];
$statusStmt = $conn->prepare("SELECT COALESCE(order_status, 'pending') AS status, COUNT(*) AS total FROM orders WHERE created_at BETWEEN ? AND ? GROUP BY COALESCE(order_status, 'pending')");
if ($statusStmt) {
    $statusStmt->bind_param('ss', $fromDateTime, $toDateTime);
    $statusStmt->execute();
    $result = $statusStmt->get_result();
    while ($result && $row = $result->fetch_assoc()) {
        $statusData[$row['status']] = (int) $row['total'];
    }
    $statusStmt->close();
}

$topProducts = [];
$topProductsStmt = $conn->prepare("SELECT COALESCE(p.name, 'Unknown Product') AS product_name, SUM(o.quantity) AS qty_sold, SUM(o.total) AS revenue FROM orders o LEFT JOIN products p ON p.pid = o.pid WHERE o.created_at BETWEEN ? AND ? GROUP BY o.pid, p.name ORDER BY qty_sold DESC, revenue DESC LIMIT 5");
if ($topProductsStmt) {
    $topProductsStmt->bind_param('ss', $fromDateTime, $toDateTime);
    $topProductsStmt->execute();
    $result = $topProductsStmt->get_result();
    while ($result && $row = $result->fetch_assoc()) {
        $topProducts[] = $row;
    }
    $topProductsStmt->close();
}

$topCustomers = [];
$topCustomersStmt = $conn->prepare("SELECT COALESCE(u.username, 'Unknown User') AS username, COUNT(*) AS orders_count, SUM(o.total) AS spend FROM orders o LEFT JOIN users u ON u.userid = o.uid WHERE o.created_at BETWEEN ? AND ? GROUP BY o.uid, u.username ORDER BY spend DESC LIMIT 5");
if ($topCustomersStmt) {
    $topCustomersStmt->bind_param('ss', $fromDateTime, $toDateTime);
    $topCustomersStmt->execute();
    $result = $topCustomersStmt->get_result();
    while ($result && $row = $result->fetch_assoc()) {
        $topCustomers[] = $row;
    }
    $topCustomersStmt->close();
}

$dailyData = [];
$dailyStmt = $conn->prepare("SELECT DATE(created_at) AS order_day, COUNT(*) AS orders_count, SUM(total) AS revenue FROM orders WHERE created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY order_day DESC LIMIT 10");
if ($dailyStmt) {
    $dailyStmt->bind_param('ss', $fromDateTime, $toDateTime);
    $dailyStmt->execute();
    $result = $dailyStmt->get_result();
    while ($result && $row = $result->fetch_assoc()) {
        $dailyData[] = $row;
    }
    $dailyStmt->close();
}

$maxDailyRevenue = 0;
foreach ($dailyData as $row) {
    $maxDailyRevenue = max($maxDailyRevenue, (int) $row['revenue']);
}

$statusOrder = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
$statusColors = [
    'pending' => '#f59e0b',
    'confirmed' => '#2563eb',
    'shipped' => '#7c3aed',
    'delivered' => '#16a34a',
    'cancelled' => '#dc2626'
];
?>
<link rel="stylesheet" href="css/style.css">
<div class="header">
    <h2>Reports & Analytics</h2>
    <div>
        <span style="margin-right:12px;font-size:13px;font-weight:600;">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        <a href="admin_logout.php">Logout</a>
    </div>
</div>

<div style="padding:25px;background:linear-gradient(120deg,#eef2f7,#e3ecf5);min-height:100vh;">
    <form method="get" style="background:rgba(255,255,255,0.9);padding:16px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;align-items:end;">
        <div>
            <label style="display:block;color:#0f172a;font-weight:600;font-size:12px;margin-bottom:6px;">From Date</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>" style="width:100%;padding:9px;border:1px solid #cbd5e1;border-radius:8px;box-sizing:border-box;">
        </div>
        <div>
            <label style="display:block;color:#0f172a;font-weight:600;font-size:12px;margin-bottom:6px;">To Date</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>" style="width:100%;padding:9px;border:1px solid #cbd5e1;border-radius:8px;box-sizing:border-box;">
        </div>
        <button class="btn" type="submit" style="padding:10px 14px;">Apply Filter</button>
        <a href="reports.php" class="btn" style="display:block;text-align:center;text-decoration:none;padding:10px 14px;background:linear-gradient(45deg,#64748b,#78716c);">Reset</a>
    </form>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px;margin-top:18px;margin-bottom:22px;">
        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #2563eb;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Orders</div>
            <div style="font-size:34px;font-weight:700;color:#0f172a;margin-top:8px;"><?= (int) $summary['total_orders'] ?></div>
        </div>
        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #16a34a;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Revenue</div>
            <div style="font-size:34px;font-weight:700;color:#16a34a;margin-top:8px;">Rs <?= (int) $summary['revenue'] ?></div>
        </div>
        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #0ea5e9;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Avg Order Value</div>
            <div style="font-size:34px;font-weight:700;color:#0f172a;margin-top:8px;">Rs <?= (int) round((float) $summary['avg_order_value']) ?></div>
        </div>
        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);border-left:5px solid #8b5cf6;">
            <div style="color:#64748b;font-size:12px;text-transform:uppercase;font-weight:600;">Active Customers</div>
            <div style="font-size:34px;font-weight:700;color:#0f172a;margin-top:8px;"><?= (int) $summary['customers'] ?></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:18px;align-items:start;margin-bottom:18px;">
        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);overflow:auto;">
            <h3 style="margin-top:0;color:#0f172a;">Top Selling Products</h3>
            <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:420px;">
                <thead>
                    <tr style="background:linear-gradient(90deg,#0f172a,#1e3a8a);color:white;">
                        <th style="padding:10px;text-align:left;">Product</th>
                        <th style="padding:10px;text-align:left;">Units Sold</th>
                        <th style="padding:10px;text-align:left;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topProducts) > 0): ?>
                        <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($product['product_name']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= (int) $product['qty_sold'] ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#16a34a;font-weight:600;">Rs <?= (int) $product['revenue'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="padding:12px;border-top:1px solid #e2e8f0;color:#64748b;text-align:center;">No product sales in selected period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0;color:#0f172a;">Order Status Breakdown</h3>
            <div style="display:grid;gap:10px;">
                <?php foreach ($statusOrder as $status): ?>
                    <?php $count = $statusData[$status] ?? 0; ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:9px 10px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;">
                        <span style="display:inline-flex;align-items:center;gap:8px;color:#0f172a;font-weight:600;text-transform:capitalize;font-size:13px;">
                            <span style="width:10px;height:10px;border-radius:999px;background:<?= $statusColors[$status] ?>;"></span>
                            <?= htmlspecialchars($status) ?>
                        </span>
                        <span style="font-weight:700;color:#0f172a;"><?= (int) $count ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:18px;align-items:start;">
        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);">
            <h3 style="margin-top:0;color:#0f172a;">Daily Revenue Trend</h3>
            <?php if (count($dailyData) > 0): ?>
                <div style="display:grid;gap:10px;">
                    <?php foreach ($dailyData as $day): ?>
                        <?php
                        $revenue = (int) $day['revenue'];
                        $percent = $maxDailyRevenue > 0 ? (int) round(($revenue / $maxDailyRevenue) * 100) : 0;
                        ?>
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:#334155;margin-bottom:4px;">
                                <span><?= htmlspecialchars($day['order_day']) ?> (<?= (int) $day['orders_count'] ?> orders)</span>
                                <span style="font-weight:700;color:#16a34a;">Rs <?= $revenue ?></span>
                            </div>
                            <div style="height:10px;background:#e2e8f0;border-radius:999px;overflow:hidden;">
                                <div style="height:10px;background:linear-gradient(45deg,#2563eb,#22c55e);width:<?= $percent ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color:#64748b;margin:0;">No daily trend data in selected period.</p>
            <?php endif; ?>
        </div>

        <div style="background:rgba(255,255,255,0.9);padding:18px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);overflow:auto;">
            <h3 style="margin-top:0;color:#0f172a;">Top Customers</h3>
            <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:300px;">
                <thead>
                    <tr style="background:linear-gradient(90deg,#0f172a,#1e3a8a);color:white;">
                        <th style="padding:10px;text-align:left;">Customer</th>
                        <th style="padding:10px;text-align:left;">Orders</th>
                        <th style="padding:10px;text-align:left;">Spend</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($topCustomers) > 0): ?>
                        <?php foreach ($topCustomers as $customer): ?>
                            <tr>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($customer['username']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= (int) $customer['orders_count'] ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#16a34a;font-weight:600;">Rs <?= (int) $customer['spend'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="padding:12px;border-top:1px solid #e2e8f0;color:#64748b;text-align:center;">No customer activity found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:20px;">
        <a href="admin_dashboard.php" style="color:#2563eb;text-decoration:none;font-weight:600;">← Back to Dashboard</a>
    </div>
</div>

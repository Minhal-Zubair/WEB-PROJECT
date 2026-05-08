<?php
include "db.php";
include "admin_auth.php";
require_admin_login();

$message = "";

// Handle Block/Unblock User
if (isset($_POST['toggle_block'])) {
    $uid = intval($_POST['uid']);
    $isActive = intval($_POST['is_active']);
    $newStatus = $isActive === 1 ? 0 : 1;
    $conn->query("UPDATE users SET is_active=$newStatus WHERE userid=$uid");
    $message = $newStatus === 0 ? "User blocked successfully!" : "User unblocked successfully!";
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE userid=$uid");
    $message = "User deleted successfully!";
}

// Fetch all users
$usersResult = $conn->query("SELECT * FROM users ORDER BY userid DESC");
?>
<link rel="stylesheet" href="css/style.css">
<div class="header">
    <h2>Manage Users</h2>
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
        <h3 style="margin-top:0;color:#0f172a;">All Users</h3>
        <table style="width:100%;border-collapse:collapse;font-size:13px;min-width:700px;">
            <thead>
                <tr style="background:linear-gradient(90deg,#0f172a,#1e3a8a);color:white;font-weight:600;">
                    <th style="padding:10px;text-align:left;">User ID</th>
                    <th style="padding:10px;text-align:left;">Username</th>
                    <th style="padding:10px;text-align:left;">Email</th>
                    <th style="padding:10px;text-align:left;">Status</th>
                    <th style="padding:10px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                    <?php while ($row = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;font-weight:600;">#<?= htmlspecialchars($row['userid']) ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['username']) ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;">
                                <?php 
                                $isActive = (int) ($row['is_active'] ?? 1) === 1;
                                $statusText = $isActive ? 'Active' : 'Blocked';
                                $statusColor = $isActive ? '#16a34a' : '#dc2626';
                                ?>
                                <span style="color:<?= $statusColor ?>;font-weight:600;"><?= $statusText ?></span>
                            </td>
                            <td style="padding:10px;border-top:1px solid #e2e8f0;text-align:center;">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="uid" value="<?= $row['userid'] ?>">
                                    <input type="hidden" name="is_active" value="<?= $row['is_active'] ?? 1 ?>">
                                    <button class="btn" name="toggle_block" type="submit" style="padding:5px 10px;font-size:12px;background:<?= $isActive ? 'linear-gradient(45deg,#dc2626,#ef4444)' : 'linear-gradient(45deg,#16a34a,#22c55e)' ?>;margin-right:6px;">
                                        <?= $isActive ? 'Block' : 'Unblock' ?>
                                    </button>
                                </form>
                                <a href="?delete=<?= $row['userid'] ?>" onclick="return confirm('Are you sure?')" style="color:#dc2626;text-decoration:none;font-weight:600;font-size:12px;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="padding:14px;border-top:1px solid #e2e8f0;color:#64748b;text-align:center;">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px;">
        <a href="admin_dashboard.php" style="color:#2563eb;text-decoration:none;font-weight:600;">← Back to Dashboard</a>
    </div>
</div>

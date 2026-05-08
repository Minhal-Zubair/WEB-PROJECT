<?php
include "db.php";
include "admin_auth.php";
require_admin_login();

$message = "";
$action = $_GET['action'] ?? null;
$editProduct = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE pid = $pid");
    $message = "Product deleted successfully!";
}

// Handle Add/Edit
if (isset($_POST['save_product'])) {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $category = $conn->real_escape_string($_POST['category'] ?? '');
    $image = $conn->real_escape_string($_POST['image'] ?? '');
    $pid = intval($_POST['pid'] ?? 0);

    if (!empty($name) && $price > 0 && !empty($category) && !empty($image)) {
        if ($pid > 0) {
            // Edit existing product
            $conn->query("UPDATE products SET name='$name', price=$price, category='$category', image='$image' WHERE pid=$pid");
            $message = "Product updated successfully!";
        } else {
            // Add new product
            $conn->query("INSERT INTO products (name, price, category, image) VALUES ('$name', $price, '$category', '$image')");
            $message = "Product added successfully!";
        }
    } else {
        $message = "All fields are required!";
    }
}

// Fetch product for editing
if ($action === 'edit' && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    $result = $conn->query("SELECT * FROM products WHERE pid = $pid LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $editProduct = $result->fetch_assoc();
    }
}

// Fetch all products
$productsResult = $conn->query("SELECT * FROM products ORDER BY pid DESC");
?>
<link rel="stylesheet" href="css/style.css">
<div class="header">
    <h2>Manage Products</h2>
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

    <div style="display:grid;grid-template-columns:1fr 350px;gap:20px;">
        <!-- Products Table -->
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);overflow-x:auto;">
            <h3 style="margin-top:0;color:#0f172a;">All Products</h3>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:linear-gradient(90deg,#0f172a,#1e3a8a);color:white;font-weight:600;">
                        <th style="padding:10px;text-align:left;">Product ID</th>
                        <th style="padding:10px;text-align:left;">Name</th>
                        <th style="padding:10px;text-align:left;">Category</th>
                        <th style="padding:10px;text-align:left;">Price (Rs)</th>
                        <th style="padding:10px;text-align:left;">Image</th>
                        <th style="padding:10px;text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($productsResult && $productsResult->num_rows > 0): ?>
                        <?php while ($row = $productsResult->fetch_assoc()): ?>
                            <tr>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;font-weight:600;">#<?= htmlspecialchars($row['pid']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['name']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#0f172a;"><?= htmlspecialchars($row['category']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#16a34a;font-weight:600;">Rs <?= htmlspecialchars($row['price']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;color:#64748b;font-size:12px;"><?= htmlspecialchars($row['image']) ?></td>
                                <td style="padding:10px;border-top:1px solid #e2e8f0;text-align:center;">
                                    <a href="?action=edit&pid=<?= $row['pid'] ?>" style="color:#2563eb;text-decoration:none;margin-right:8px;font-weight:600;">Edit</a>
                                    <a href="?delete=<?= $row['pid'] ?>" onclick="return confirm('Are you sure?')" style="color:#dc2626;text-decoration:none;font-weight:600;">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="padding:14px;border-top:1px solid #e2e8f0;color:#64748b;text-align:center;">No products found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add/Edit Product Form -->
        <div style="background:rgba(255,255,255,0.9);padding:20px;border-radius:12px;box-shadow:0 5px 20px rgba(0,0,0,0.1);height:fit-content;">
            <h3 style="margin-top:0;color:#0f172a;"><?= $editProduct ? 'Edit Product' : 'Add New Product' ?></h3>
            <form method="post" style="display:grid;gap:12px;">
                <?php if ($editProduct): ?>
                    <input type="hidden" name="pid" value="<?= $editProduct['pid'] ?>">
                <?php endif; ?>

                <div>
                    <label style="display:block;color:#0f172a;font-weight:600;margin-bottom:4px;font-size:12px;">Product Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;font-size:13px;">
                </div>

                <div>
                    <label style="display:block;color:#0f172a;font-weight:600;margin-bottom:4px;font-size:12px;">Price (Rs)</label>
                    <input type="number" name="price" value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;font-size:13px;">
                </div>

                <div>
                    <label style="display:block;color:#0f172a;font-weight:600;margin-bottom:4px;font-size:12px;">Category</label>
                    <select name="category" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;font-size:13px;">
                        <option value="">Select Category</option>
                        <option value="Shoes" <?= ($editProduct['category'] ?? '') === 'Shoes' ? 'selected' : '' ?>>Shoes</option>
                        <option value="Cloth" <?= ($editProduct['category'] ?? '') === 'Cloth' ? 'selected' : '' ?>>Cloth</option>
                        <option value="Accessory" <?= ($editProduct['category'] ?? '') === 'Accessory' ? 'selected' : '' ?>>Accessory</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;color:#0f172a;font-weight:600;margin-bottom:4px;font-size:12px;">Image Path</label>
                    <input type="text" name="image" value="<?= htmlspecialchars($editProduct['image'] ?? '') ?>" placeholder="images/product.jpg" required style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:6px;box-sizing:border-box;font-size:13px;">
                    <small style="color:#64748b;display:block;margin-top:4px;">e.g., images/shoe1.jpg</small>
                </div>

                <button class="btn" name="save_product" type="submit" style="width:100%;padding:10px;margin-top:8px;">
                    <?= $editProduct ? 'Update Product' : 'Add Product' ?>
                </button>

                <?php if ($editProduct): ?>
                    <a href="manage_products.php" class="btn" style="width:100%;padding:10px;text-align:center;text-decoration:none;display:block;background:linear-gradient(45deg,#64748b,#78716c);">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div style="margin-top:20px;">
        <a href="admin_dashboard.php" style="color:#2563eb;text-decoration:none;font-weight:600;">← Back to Dashboard</a>
    </div>
</div>

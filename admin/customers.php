
<?php
require_once '../includes/functions.php';
// Check if user is logged in and is an admin
require_once '../config/database.php';

// Fetch all customers
$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$customers = $stmt->fetchAll();

$page_title = "Customers";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="admin-body">

    <aside class="admin-sidebar">
        <nav class="admin-menu">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="faqs.php"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
            </ul>
        </nav>
    </aside>

    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-content">
                <h1>Customers</h1>
                <p><a href="dashboard.php">&larr; Back to Dashboard</a></p>
                <div class="admin-form" style="margin-top: 30px;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="4">No customers found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><?php echo $customer['id']; ?></td>
                                        <td><?php echo htmlspecialchars($customer['name'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($customer['created_at'] ?? ''); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
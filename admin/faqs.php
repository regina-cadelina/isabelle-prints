<?php
require_once '../config/database.php';

// Create FAQs table if it doesn't exist (optional safety)
$pdo->exec("
    CREATE TABLE IF NOT EXISTS faqs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Handle new FAQ submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question']) && !empty($_POST['answer'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
        $stmt->execute([$_POST['question'], $_POST['answer']]);
        header("Location: faqs.php"); // Redirect to prevent form resubmission
        exit;
    } catch (PDOException $e) {
        die("Error adding FAQ: " . $e->getMessage());
    }
}

// Fetch all FAQs
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY id DESC");
    $faqs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching FAQs: " . $e->getMessage());
}

$page_title = "Manage FAQs";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <div class="admin-user">
                <span>&nbsp Welcome, <?php echo htmlspecialchars($current_user['first_name']); ?></span>
                <a href="../pages/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                    <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="faqs.php"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-content">
                <h1>FAQs</h1>

                <!-- Form to add a new FAQ -->
                <form method="post" class="admin-form" style="margin-bottom:30px;">
                    <h2>Add New FAQ</h2>
                    <label>Question:<br>
                        <input type="text" name="question" required style="width:100%;">
                    </label><br><br>
                    <label>Answer:<br>
                        <textarea name="answer" required style="width:100%;height:80px;"></textarea>
                    </label><br><br>
                    <button type="submit" class="btn-small">Add FAQ</button>
                </form>

                <!-- Display all FAQs -->
                <h2>All FAQs</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th>Answer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($faqs) > 0): ?>
                            <?php foreach ($faqs as $faq): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($faq['id']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($faq['question'])); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No FAQs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

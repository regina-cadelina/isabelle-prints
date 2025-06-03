<?php
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_user = $stmt->fetch();
}

// Handle delete FAQ
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: faqs.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error deleting FAQ: " . $e->getMessage();
    }
}

// Handle edit FAQ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE faqs SET question = ?, answer = ? WHERE id = ?");
        $stmt->execute([$_POST['question'], $_POST['answer'], $_POST['edit_id']]);
        header("Location: faqs.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating FAQ: " . $e->getMessage();
    }
}

// Handle new FAQ submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question']) && !empty($_POST['answer']) && !isset($_POST['edit_id'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
        $stmt->execute([$_POST['question'], $_POST['answer']]);
        header("Location: faqs.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error adding FAQ: " . $e->getMessage();
    }
}

// Get FAQ for editing
$edit_faq = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_faq = $stmt->fetch();
}

// Fetch all FAQs
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY id DESC");
    $faqs = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching FAQs: " . $e->getMessage();
    $faqs = [];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">
                <h2><i class="fas fa-cog"></i> Admin Panel</h2>
            </div>
            <div class="admin-user">
                <span>&nbsp Welcome, <?php echo htmlspecialchars($current_user['first_name'] ?? 'Admin'); ?></span>
                <a href="../pages/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>
    
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="products.php"><i class="fas fa-box"></i> Manage Products</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                    <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                    <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                    <li><a href="faqs.php" class="active"><i class="fas fa-question-circle"></i> Manage FAQs</a></li>
                    <li><a href="../index.php"><i class="fas fa-globe"></i> View Website</a></li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-content">
                <h1>Manage FAQs</h1>
                
                <?php if (isset($error)): ?>
                    <div class="admin-alert error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Form to add/edit FAQ -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <?php echo $edit_faq ? 'Edit FAQ' : 'Add New FAQ'; ?>
                    </div>
                    <div class="admin-card-body">
                        <form method="post" class="admin-form">
                            <?php if ($edit_faq): ?>
                                <input type="hidden" name="edit_id" value="<?php echo $edit_faq['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="question">Question:</label>
                                <input type="text" id="question" name="question" class="form-control" 
                                       value="<?php echo htmlspecialchars($edit_faq['question'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="answer">Answer:</label>
                                <textarea id="answer" name="answer" class="form-control" rows="4" required><?php echo htmlspecialchars($edit_faq['answer'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn-primary">
                                    <?php echo $edit_faq ? 'Update FAQ' : 'Add FAQ'; ?>
                                </button>
                                <?php if ($edit_faq): ?>
                                    <a href="faqs.php" class="btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Display all FAQs -->
                <div class="admin-card" style="margin-top: 30px;">
                    <div class="admin-card-header">
                        All FAQs
                        <a href="../pages/faqs.php" target="_blank" class="view-all-btn" style="float: right;">
                            <i class="fas fa-external-link-alt"></i> View User FAQ Page
                        </a>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Question</th>
                                        <th>Answer</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($faqs) > 0): ?>
                                        <?php foreach ($faqs as $faq): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($faq['id']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($faq['question'], 0, 50)) . (strlen($faq['question']) > 50 ? '...' : ''); ?></td>
                                                <td><?php echo htmlspecialchars(substr($faq['answer'], 0, 100)) . (strlen($faq['answer']) > 100 ? '...' : ''); ?></td>
                                                <td>
                                                    <a href="faqs.php?edit=<?php echo $faq['id']; ?>" class="btn-small">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="faqs.php?delete=<?php echo $faq['id']; ?>" 
                                                       class="btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this FAQ?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4">No FAQs found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
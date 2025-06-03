<?php
require_once '../config/database.php';

// Handle new FAQ submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question']) && !empty($_POST['answer'])) {
    $stmt = $pdo->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
    $stmt->execute([$_POST['question'], $_POST['answer']]);
}

// Fetch all FAQs
$stmt = $pdo->query("SELECT * FROM faqs ORDER BY id DESC");
$faqs = $stmt->fetchAll();

$page_title = "Manage FAQs";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-container">
        <main class="admin-main">
            <div class="admin-content">
                <h1>FAQs</h1>
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
                        <?php foreach ($faqs as $faq): ?>
                        <tr>
                            <td><?php echo $faq['id']; ?></td>
                            <td><?php echo htmlspecialchars($faq['question']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>

CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL
);
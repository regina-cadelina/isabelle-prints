<?php
$pageTitle = "Frequently Asked Questions";
require_once '../config/database.php';

// Fetch all FAQs from database
try {
    $stmt = $pdo->query("SELECT * FROM faqs ORDER BY id ASC");
    $faqs = $stmt->fetchAll();
} catch (PDOException $e) {
    $faqs = []; // Empty array if there's an error
}

include '../includes/header.php';
?>

<main class="faqs-page">
    <div class="container">
        <div class="page-header">
            <h1>Frequently Asked Questions</h1>
        </div>

        <div class="faqs-content">
            <?php if (count($faqs) > 0): ?>
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="faq-item">
                        <div class="faq-question">
                            <h3><?php echo htmlspecialchars($faq['question']); ?></h3>
                            <span class="faq-toggle">+</span>
                        </div>
                        <div class="faq-answer">
                            <p><?php echo nl2br(htmlspecialchars($faq['answer'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-faqs">
                    <h3>No FAQs Available</h3>
                    <p>We're currently updating our FAQ section. Please check back soon or contact us directly for any questions.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const toggle = item.querySelector('.faq-toggle');

        question.addEventListener('click', function() {
            const isActive = item.classList.contains('active');

            // Close all items
            faqItems.forEach(other => {
                other.classList.remove('active');
                const otherToggle = other.querySelector('.faq-toggle');
                if (otherToggle) otherToggle.textContent = '+';
            });

            // Open the clicked item
            if (!isActive) {
                item.classList.add('active');
                toggle.textContent = '-';
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>

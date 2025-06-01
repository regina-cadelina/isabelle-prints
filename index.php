<?php
$pageTitle = "Premium Printing Solutions";
include 'includes/header.php';

// Get categories for the homepage
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>PREMIUM PRINTING<br>SOLUTIONS</h1>
                <p>Personalized designs for all your printing needs</p>
                <a href="/isabelle-prints/pages/products.php" class="btn btn-primary">SHOP NOW</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2>OUR CATEGORIES</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <a href="/isabelle-prints/pages/products.php?category=<?php echo $category['slug']; ?>">
                            <div class="category-image">
                                <i class="fas fa-image"></i>
                            </div>
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
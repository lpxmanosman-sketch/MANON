<?php
/**
 * Categories Showcase Page - MANON
 */
$pageTitle = 'تشكيلة المجموعات والتصنيفات | منون';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch categories with product counts
$sql = "
    SELECT c.*, COUNT(p.id) AS products_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
    WHERE c.is_active = 1 
    GROUP BY c.id 
    ORDER BY c.sort_order ASC, c.id ASC
";
$categories = $db->query($sql)->fetchAll();
?>

<div class="py-5 text-center" style="background: linear-gradient(135deg, #F8F3EC 0%, #EFE5D7 100%); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <span class="section-tag">OUR EXCLUSIVE LINES</span>
        <h1 class="display-5 fw-bold text-dark mb-2">تصنيفات ومجموعات منون</h1>
        <p class="text-muted fs-6 max-w-600 mx-auto">
            تصفحي تشكيلاتنا المتنوعة من العبايات الكلاسيكية والفاخرة والعملية المصممة بعناية فائقة لتناسب كل مناسبة وأوقاتك اليومية.
        </p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($categories as $cat): ?>
            <div class="col-lg-4 col-md-6">
                <div class="category-card position-relative overflow-hidden shadow-sm" style="height: 380px;">
                    <img src="<?= e($cat['image'] ?: 'assets/images/cat1.jpg') ?>" alt="<?= e($cat['name']) ?>">
                    <div class="category-overlay"></div>
                    <div class="category-info p-4 w-100">
                        <span class="badge bg-warning text-dark mb-2"><?= $cat['products_count'] ?> عبايات متوفرة</span>
                        <h2 class="category-name text-white fs-3 mb-2"><?= e($cat['name']) ?></h2>
                        <p class="text-light opacity-75 small mb-3"><?= e($cat['description']) ?></p>
                        <a href="shop.php?category=<?= e($cat['slug']) ?>" class="btn btn-sm btn-manon-gold">
                            <span>استعراض المجموعة</span>
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

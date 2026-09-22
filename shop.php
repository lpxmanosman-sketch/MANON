<?php
/**
 * Shop / Products Catalog - MANON
 */
$pageTitle = 'متجر العبايات الفاخرة | تشكيلة منون الحصرية';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Input parameters
$categorySlug = $_GET['category'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$filterType = $_GET['filter'] ?? '';
$sort = $_GET['sort'] ?? 'latest';
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 5000;

// Fetch active categories for sidebar
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();

// Build Query
$sql = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1
";
$params = [];

if (!empty($categorySlug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $categorySlug;
}

if (!empty($searchQuery)) {
    $sql .= " AND (p.name LIKE ? OR p.short_desc LIKE ? OR p.description LIKE ? OR p.fabric LIKE ?)";
    $term = "%{$searchQuery}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($filterType === 'new') {
    $sql .= " AND p.is_new = 1";
} elseif ($filterType === 'featured') {
    $sql .= " AND p.is_featured = 1";
}

if ($minPrice > 0) {
    $sql .= " AND (CASE WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 THEN p.sale_price ELSE p.price END) >= ?";
    $params[] = $minPrice;
}

if ($maxPrice < 5000) {
    $sql .= " AND (CASE WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 THEN p.sale_price ELSE p.price END) <= ?";
    $params[] = $maxPrice;
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY (CASE WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 THEN p.sale_price ELSE p.price END) ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY (CASE WHEN p.sale_price IS NOT NULL AND p.sale_price > 0 THEN p.sale_price ELSE p.price END) DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.views_count DESC, p.id DESC";
        break;
    case 'latest':
    default:
        $sql .= " ORDER BY p.id DESC";
        break;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$totalFound = count($products);

// Current category name for heading
$currentCatName = 'جميع العبايات';
if (!empty($categorySlug)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $categorySlug) {
            $currentCatName = $cat['name'];
            break;
        }
    }
}
?>

<div class="py-4" style="background: #F4EFEB; border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="shop.php">المتجر</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($currentCatName) ?></li>
            </ol>
        </nav>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
                <h1 class="h2 fw-bold text-dark mb-1"><?= e($currentCatName) ?></h1>
                <p class="text-muted small mb-0">تم العثور على <?= $totalFound ?> عباية فاخرة</p>
            </div>
            <?php if (!empty($searchQuery)): ?>
                <div class="mt-2 mt-md-0">
                    <span class="badge bg-dark px-3 py-2">نتائج البحث عن: "<?= e($searchQuery) ?>" <a href="shop.php" class="text-white ms-2 text-decoration-none">&times;</a></span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
                <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-funnel me-2 text-warning"></i> تصنيفات العبايات</h5>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2">
                        <a href="shop.php" class="d-flex justify-content-between align-items-center text-decoration-none <?= empty($categorySlug) && empty($filterType) ? 'fw-bold text-dark' : 'text-muted' ?>">
                            <span>الكل</span>
                            <i class="bi bi-chevron-left small"></i>
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                        <li class="mb-2">
                            <a href="shop.php?category=<?= e($cat['slug']) ?>" class="d-flex justify-content-between align-items-center text-decoration-none <?= ($categorySlug === $cat['slug']) ? 'fw-bold text-dark' : 'text-muted' ?>">
                                <span><?= e($cat['name']) ?></span>
                                <i class="bi bi-chevron-left small"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-tags me-2 text-warning"></i> مجموعات خاصة</h5>
                <ul class="list-unstyled mb-4">
                    <li class="mb-2">
                        <a href="shop.php?filter=new" class="d-flex justify-content-between align-items-center text-decoration-none <?= ($filterType === 'new') ? 'fw-bold text-dark' : 'text-muted' ?>">
                            <span>وصل حديثاً</span>
                            <span class="badge bg-danger rounded-pill">جديد</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="shop.php?filter=featured" class="d-flex justify-content-between align-items-center text-decoration-none <?= ($filterType === 'featured') ? 'fw-bold text-dark' : 'text-muted' ?>">
                            <span>الأكثر طلباً</span>
                            <span class="badge bg-warning text-dark rounded-pill">مميز</span>
                        </a>
                    </li>
                </ul>

                <h5 class="fw-bold mb-3 pb-2 border-bottom"><i class="bi bi-cash-stack me-2 text-warning"></i> نطاق السعر</h5>
                <form action="shop.php" method="GET">
                    <?php if (!empty($categorySlug)): ?><input type="hidden" name="category" value="<?= e($categorySlug) ?>"><?php endif; ?>
                    <?php if (!empty($filterType)): ?><input type="hidden" name="filter" value="<?= e($filterType) ?>"><?php endif; ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-muted mb-1">من (ج.م)</label>
                            <input type="number" name="min_price" class="form-control form-control-sm" value="<?= $minPrice ?>" min="0" step="50">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-1">إلى (ج.م)</label>
                            <input type="number" name="max_price" class="form-control form-control-sm" value="<?= $maxPrice ?>" min="0" step="50">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm w-100">تطبيق التصفية</button>
                </form>
            </div>

            <!-- WhatsApp Help Banner in Sidebar -->
            <div class="mt-4 p-4 rounded-4 text-center text-white" style="background: linear-gradient(135deg, #1C1917 0%, #2A2623 100%);">
                <i class="bi bi-whatsapp fs-1 text-success mb-2 d-inline-block"></i>
                <h6 class="fw-bold">محتارة في اختيار المقاس؟</h6>
                <p class="small opacity-75 mb-3">فريق مصممات منون جاهز لمساعدتك عبر الواتساب فوراً</p>
                <a href="https://wa.me/<?= WHATSAPP_PHONE ?>?text=<?= rawurlencode('مرحباً منون، أحتاج مساعدة في اختيار المقاس المناسب للعباية') ?>" target="_blank" class="btn btn-sm btn-whatsapp-order w-100">
                    تحدثي معنا
                </a>
            </div>
        </div>

        <!-- Products Grid Area -->
        <div class="col-lg-9">
            <!-- Top Controls Bar -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center bg-white p-3 rounded-4 shadow-sm border border-secondary-subtle mb-4 gap-3">
                <div class="text-muted small">
                    عرض <strong><?= count($products) ?></strong> من أصل <strong><?= $totalFound ?></strong> عباية
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-nowrap small text-muted">ترتيب حسب:</label>
                    <select class="form-select form-select-sm" onchange="location = this.value;" style="min-width: 170px;">
                        <?php
                        $baseParams = $_GET;
                        $makeSortUrl = function($s) use ($baseParams) {
                            $baseParams['sort'] = $s;
                            return 'shop.php?' . http_build_query($baseParams);
                        };
                        ?>
                        <option value="<?= $makeSortUrl('latest') ?>" <?= ($sort === 'latest') ? 'selected' : '' ?>>الأحدث أولاً</option>
                        <option value="<?= $makeSortUrl('price_asc') ?>" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>السعر: من الأقل للأعلى</option>
                        <option value="<?= $makeSortUrl('price_desc') ?>" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>السعر: من الأعلى للأقل</option>
                        <option value="<?= $makeSortUrl('popular') ?>" <?= ($sort === 'popular') ? 'selected' : '' ?>>الأكثر شعبية</option>
                    </select>
                </div>
            </div>

            <!-- Product Cards Grid -->
            <?php if (empty($products)): ?>
                <div class="bg-white p-5 rounded-4 text-center border border-secondary-subtle my-4">
                    <i class="bi bi-bag-x text-muted" style="font-size: 3.5rem;"></i>
                    <h4 class="fw-bold mt-3 mb-2">عذراً، لم نجد نتائج مطابقة</h4>
                    <p class="text-muted mb-4">جربي تعديل معايير البحث أو تصفحي كامل تشكيلتنا الفاخرة</p>
                    <a href="shop.php" class="btn btn-manon-primary">تصفح كافة العبايات</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $prod): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="product-card">
                                <div class="product-thumb">
                                    <a href="product.php?id=<?= $prod['id'] ?>">
                                        <img src="<?= e($prod['main_image']) ?>" alt="<?= e($prod['name']) ?>" loading="lazy">
                                    </a>
                                    <?php if ($prod['sale_price']): ?>
                                        <span class="product-badge sale">خصم</span>
                                    <?php elseif ($prod['is_new']): ?>
                                        <span class="product-badge new">جديد</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-content">
                                    <span class="product-category"><?= e($prod['category_name']) ?></span>
                                    <h3 class="product-title">
                                        <a href="product.php?id=<?= $prod['id'] ?>"><?= e($prod['name']) ?></a>
                                    </h3>
                                    <div class="product-price-box">
                                        <?php if ($prod['sale_price']): ?>
                                            <span class="product-price text-danger"><?= formatPrice($prod['sale_price']) ?></span>
                                            <span class="product-old-price"><?= formatPrice($prod['price']) ?></span>
                                        <?php else: ?>
                                            <span class="product-price"><?= formatPrice($prod['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-card-actions">
                                        <button type="button" class="btn-add-cart btn-add-cart-ajax" data-id="<?= $prod['id'] ?>">
                                            <i class="bi bi-bag-plus"></i> أضف للسلة
                                        </button>
                                        <a href="product.php?id=<?= $prod['id'] ?>" class="btn-view-details" title="تفاصيل العباية">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

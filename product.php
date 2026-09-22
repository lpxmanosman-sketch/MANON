<?php
/**
 * Product Details Page - MANON
 */
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDB();

$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? AND p.is_active = 1 
    LIMIT 1
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container py-5 text-center'><h3>عذراً، المنتج غير موجود أو تم إيقافه</h3><a href='shop.php' class='btn btn-dark mt-3'>العودة للمتجر</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
$db->prepare("UPDATE products SET views_count = views_count + 1 WHERE id = ?")->execute([$id]);

// Fetch product gallery images
$galleryStmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
$galleryStmt->execute([$id]);
$galleryImages = $galleryStmt->fetchAll();

// Fetch Related Products (same category, excluding current)
$relStmt = $db->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1 
    ORDER BY p.id DESC LIMIT 4
");
$relStmt->execute([$product['category_id'], $id]);
$relatedProducts = $relStmt->fetchAll();

$sizes = array_map('trim', explode(',', $product['sizes']));
$availableColors = getActiveColors();
$defaultColor = !empty($availableColors) ? $availableColors[0]['name'] : 'أسود';
$currentPrice = ($product['sale_price'] !== null && $product['sale_price'] > 0) ? $product['sale_price'] : $product['price'];

// WhatsApp direct order URL for single product
$singleWaMsg = "مرحباً MANON، أريد طلب هذه العباية:\n- " . $product['name'] . "\n- اللون: " . $defaultColor . "\n- المقاس: " . ($sizes[0] ?? '54') . "\n- السعر: " . formatPrice($currentPrice) . "\n- الرابط: " . BASE_URL . "product.php?id=" . $product['id'];
$singleWaUrl = "https://wa.me/" . WHATSAPP_PHONE . "?text=" . rawurlencode($singleWaMsg);
?>

<div class="py-3 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="shop.php">المتجر</a></li>
                <li class="breadcrumb-item"><a href="shop.php?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- Product Images Gallery -->
        <div class="col-lg-6">
            <div class="product-gallery-wrap">
                <!-- Main Large Image -->
                <div class="bg-white rounded-4 overflow-hidden border border-secondary-subtle p-2 mb-3 shadow-sm text-center">
                    <img id="mainGalleryImg" src="<?= e($product['main_image']) ?>" alt="<?= e($product['name']) ?>" class="img-fluid rounded-3 w-100" style="max-height: 560px; object-fit: cover;">
                </div>

                <!-- Thumbnails Carousel/Row -->
                <div class="d-flex gap-2 overflow-auto pb-2">
                    <div class="gallery-thumb active border rounded-3 p-1 cursor-pointer" data-full="<?= e($product['main_image']) ?>" style="width: 80px; height: 80px; flex-shrink: 0; cursor: pointer;">
                        <img src="<?= e($product['main_image']) ?>" alt="thumb" class="w-100 h-100 object-fit-cover rounded-2">
                    </div>
                    <?php foreach ($galleryImages as $gImg): ?>
                        <div class="gallery-thumb border rounded-3 p-1 cursor-pointer" data-full="<?= e($gImg['image_path']) ?>" style="width: 80px; height: 80px; flex-shrink: 0; cursor: pointer;">
                            <img src="<?= e($gImg['image_path']) ?>" alt="thumb" class="w-100 h-100 object-fit-cover rounded-2">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Product Information & Actions -->
        <div class="col-lg-6">
            <div class="product-details-content ps-lg-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-dark px-3 py-2"><?= e($product['category_name']) ?></span>
                    <?php if ($product['sku']): ?>
                        <span class="text-muted small">كود العباية: <?= e($product['sku']) ?></span>
                    <?php endif; ?>
                    <?php if ($product['is_new']): ?>
                        <span class="badge bg-danger">كولكشن جديد</span>
                    <?php endif; ?>
                </div>

                <h1 class="h2 fw-bold text-dark mb-3"><?= e($product['name']) ?></h1>

                <!-- Price Box -->
                <div class="d-flex align-items-center gap-3 my-3 p-3 rounded-3" style="background: #F8F4EE;">
                    <div class="fs-2 fw-bold text-dark"><?= formatPrice($currentPrice) ?></div>
                    <?php if ($product['sale_price']): ?>
                        <div class="text-muted text-decoration-line-through fs-5"><?= formatPrice($product['price']) ?></div>
                        <span class="badge bg-danger ms-auto">توفير <?= formatPrice($product['price'] - $product['sale_price']) ?></span>
                    <?php endif; ?>
                </div>

                <p class="text-muted fs-6 mb-4"><?= nl2br(e($product['short_desc'] ?: $product['description'])) ?></p>

                <!-- Product Specifications -->
                <div class="row g-2 mb-4 p-3 bg-white rounded-3 border">
                    <div class="col-6 small">
                        <span class="text-muted">الخامة:</span>
                        <strong class="text-dark d-block"><?= e($product['fabric'] ?: 'حرير كريب كوري ملكي') ?></strong>
                    </div>
                    <div class="col-6 small">
                        <span class="text-muted">اللون:</span>
                        <strong class="text-dark d-block"><?= e($product['color'] ?: 'أسود فاحم ملكي') ?></strong>
                    </div>
                    <div class="col-6 small mt-2">
                        <span class="text-muted">حالة المخزون:</span>
                        <strong class="text-success d-block"><i class="bi bi-check-circle-fill"></i> متوفر وجاهز للشحن</strong>
                    </div>
                    <div class="col-6 small mt-2">
                        <span class="text-muted">الملحقات:</span>
                        <strong class="text-dark d-block">طرحة فاخرة مجانية</strong>
                    </div>
                </div>

                <!-- Selection Form -->
                <form id="addToCartForm" class="mb-4">
                    <!-- Color Swatches Selector -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="fw-bold small text-dark">اختاري اللون:</label>
                            <span id="selectedColorName" class="badge bg-dark px-3 py-1"><?= e($defaultColor) ?></span>
                        </div>
                        <div class="color-swatch-wrap">
                            <?php foreach ($availableColors as $cIdx => $c): ?>
                                <label class="color-swatch-item" title="<?= e($c['name']) ?>">
                                    <input type="radio" name="product_color_radio" value="<?= e($c['name']) ?>" <?= ($cIdx === 0) ? 'checked' : '' ?> class="d-none color-radio" onchange="document.getElementById('selectedColorName').textContent = this.value; document.getElementById('productColorInput').value = this.value; updateSingleWaLink();">
                                    <span class="color-swatch-circle" style="background-color: <?= e($c['hex_code']) ?>; <?= (strtolower($c['hex_code']) === '#ffffff') ? 'border-color: #ccc;' : '' ?>"></span>
                                    <span class="color-label"><?= e($c['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="productColorInput" value="<?= e($defaultColor) ?>">
                    </div>

                    <!-- Size Selector -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="fw-bold small text-dark">اختاري المقاس المناسب:</label>
                            <a href="#sizeModal" data-bs-toggle="modal" class="small text-decoration-underline text-muted"><i class="bi bi-rulers me-1"></i> دليل المقاسات</a>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <select id="productSizeSelect" class="form-select form-select-lg border-secondary-subtle fw-semibold" onchange="updateSingleWaLink();">
                                <?php foreach ($sizes as $idx => $s): ?>
                                    <option value="<?= e($s) ?>" <?= ($idx === 1 || count($sizes) === 1) ? 'selected' : '' ?>>مقاس <?= e($s) ?> (الأنسب لطول <?= 150 + ((int)$s - 50) * 2 ?> سم تقريباً)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Quantity & Buttons -->
                    <div class="row g-3 align-items-center mb-4">
                        <div class="col-sm-4">
                            <div class="input-group qty-control-wrap">
                                <button type="button" class="btn btn-outline-secondary qty-btn-minus"><i class="bi bi-dash"></i></button>
                                <input type="number" id="productQtyInput" class="form-control text-center fw-bold qty-input" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                                <button type="button" class="btn btn-outline-secondary qty-btn-plus"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="col-sm-8">
                            <button type="button" class="btn btn-manon-primary w-100 py-3 btn-add-cart-ajax" data-id="<?= $product['id'] ?>">
                                <i class="bi bi-bag-plus fs-5"></i> أضيفي إلى السلة
                            </button>
                        </div>
                    </div>

                    <!-- Direct WhatsApp Order Button -->
                    <div class="mb-4">
                        <a href="<?= $singleWaUrl ?>" id="directSingleWaBtn" target="_blank" class="btn btn-whatsapp-order w-100 py-3">
                            <i class="bi bi-whatsapp fs-4"></i> اطلبي هذه العباية مباشرة عبر واتساب
                        </a>
                        <div class="text-center small text-muted mt-2">
                            <i class="bi bi-shield-check text-success"></i> طلب فوري وسريع بدون تعقيد عبر رقمنا الرسمي
                        </div>
                    </div>
                </form>

                <script>
                function updateSingleWaLink() {
                    const colorEl = document.getElementById('productColorInput');
                    const sizeEl = document.getElementById('productSizeSelect');
                    const color = colorEl ? colorEl.value : 'أسود';
                    const size = sizeEl ? sizeEl.value : '54';
                    const msg = "مرحباً MANON، أريد طلب هذه العباية:\n- <?= addslashes(e($product['name'])) ?>\n- اللون: " + color + "\n- المقاس: " + size + "\n- السعر: <?= formatPrice($currentPrice) ?>\n- الرابط: <?= BASE_URL ?>product.php?id=<?= $product['id'] ?>";
                    const btn = document.getElementById('directSingleWaBtn');
                    if (btn) {
                        btn.href = "https://wa.me/<?= WHATSAPP_PHONE ?>?text=" + encodeURIComponent(msg);
                    }
                }
                </script>

                <!-- Value Props -->
                <div class="border-top pt-4">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <i class="bi bi-truck text-warning fs-4 d-block mb-1"></i>
                            <span class="small fw-semibold d-block">شحن مجاني</span>
                            <span class="text-muted" style="font-size: 0.75rem;">لكافة المحافظات</span>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-box-seam text-warning fs-4 d-block mb-1"></i>
                            <span class="small fw-semibold d-block">تغليف ملكي</span>
                            <span class="text-muted" style="font-size: 0.75rem;">معطر بالبخور</span>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-arrow-repeat text-warning fs-4 d-block mb-1"></i>
                            <span class="small fw-semibold d-block">معاينة عند الاستلام</span>
                            <span class="text-muted" style="font-size: 0.75rem;">حق فحص العباية</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Long Description & Details Tabs -->
    <div class="row mt-5 pt-4">
        <div class="col-12">
            <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                <h4 class="fw-bold mb-4 pb-2 border-bottom">تفاصيل العباية والعناية بها</h4>
                <div class="lh-lg text-muted fs-6">
                    <?= nl2br(e($product['description'])) ?>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <h5 class="fw-bold mb-3">إرشادات الغسيل والعناية بعبايات منون:</h5>
                    <ul class="text-muted small lh-lg">
                        <li>يُفضل الغسيل اليدوي بالماء البارد باستخدام شامبو مخصص للعبايات السوداء.</li>
                        <li>تجنبي استخدام المبيضات أو مساحيق الغسيل العادية للحفاظ على لمعة وسواد القماش.</li>
                        <li>يُفضل الكي بالبخار على درجة حرارة متوسطة، أو الكي من الجهة الداخلية للعباية.</li>
                        <li>يُفضل التجفيف في الظل بعيداً عن أشعة الشمس المباشرة.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-5 pt-5">
            <div class="section-header text-start mb-4">
                <span class="section-tag">YOU MAY ALSO LOVE</span>
                <h3 class="section-title">عبايات ذات صلة تناسب ذوقك</h3>
            </div>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="product-card">
                            <div class="product-thumb">
                                <a href="product.php?id=<?= $rel['id'] ?>">
                                    <img src="<?= e($rel['main_image']) ?>" alt="<?= e($rel['name']) ?>" loading="lazy">
                                </a>
                            </div>
                            <div class="product-content">
                                <span class="product-category"><?= e($rel['category_name']) ?></span>
                                <h4 class="product-title">
                                    <a href="product.php?id=<?= $rel['id'] ?>"><?= e($rel['name']) ?></a>
                                </h4>
                                <div class="product-price-box">
                                    <span class="product-price"><?= formatPrice($rel['sale_price'] ?: $rel['price']) ?></span>
                                </div>
                                <div class="product-card-actions">
                                    <a href="product.php?id=<?= $rel['id'] ?>" class="btn btn-manon-outline w-100 btn-sm">عرض التفاصيل</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Size Chart Modal -->
<div class="modal fade" id="sizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 p-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">جدول مقاسات عبايات منون</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">المقاس يُحدد عادة بناءً على طول القامة من الكتف للقدم:</p>
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>المقاس</th>
                                <th>طول القامة التقريبي</th>
                                <th>عرض الصدر (إنش)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>52</strong></td><td>150 - 155 سم</td><td>21</td></tr>
                            <tr><td><strong>54</strong></td><td>156 - 160 سم</td><td>22</td></tr>
                            <tr><td><strong>56</strong></td><td>161 - 165 سم</td><td>23</td></tr>
                            <tr><td><strong>58</strong></td><td>166 - 170 سم</td><td>24</td></tr>
                            <tr><td><strong>60</strong></td><td>171 - 175 سم</td><td>25</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-light border small text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i> إذا كنتِ ترتدين كعباً عالياً باستمرار، ننصحك باختيار مقاس أكبر بدرجة واحدة.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Shopping Cart Page - MANON
 */
$pageTitle = 'سلة المشتريات | منون';
require_once __DIR__ . '/includes/header.php';

$cart = getCart();
$subtotal = getCartSubtotal();
?>

<div class="py-4 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="shop.php">المتجر</a></li>
                <li class="breadcrumb-item active" aria-current="page">سلة المشتريات</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-0">سلة التسوق الملكية</h1>
    </div>
</div>

<div class="container py-5">
    <?php if (empty($cart)): ?>
        <div class="bg-white p-5 rounded-4 text-center border border-secondary-subtle shadow-sm my-4">
            <div class="mb-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
            </div>
            <h3 class="fw-bold mb-2">سلة المشتريات فارغة حالياً</h3>
            <p class="text-muted mb-4">لم تقومي بإضافة أي عباية إلى سلتك حتى الآن. استكشفي أحدث تصاميمنا الفاخرة!</p>
            <a href="shop.php" class="btn btn-manon-primary btn-lg">
                <i class="bi bi-shop me-2"></i> تصفحي متجر منون
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <h5 class="fw-bold mb-0">المنتجات في السلة (<?= count($cart) ?>)</h5>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="clearCartBtn">
                            <i class="bi bi-trash3 me-1"></i> إفراغ السلة بالكامل
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>العباية</th>
                                    <th>اللون والمقاس</th>
                                    <th>السعر</th>
                                    <th class="text-center" style="width: 140px;">الكمية</th>
                                    <th>الإجمالي</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $key => $item): 
                                    $itemTotal = $item['price'] * $item['quantity'];
                                    $itemColor = $item['color'] ?? 'أسود';
                                ?>
                                    <tr class="cart-row" data-key="<?= e($key) ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="rounded-3 border object-fit-cover" style="width: 65px; height: 85px;">
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-dark">
                                                        <a href="product.php?id=<?= $item['product_id'] ?>" class="text-decoration-none text-dark"><?= e($item['name']) ?></a>
                                                    </h6>
                                                    <span class="text-muted small">كود: #<?= $item['product_id'] ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-dark px-2 py-1 small">اللون: <?= e($itemColor) ?></span>
                                                <span class="badge bg-light text-dark border px-2 py-1 small">المقاس: <?= e($item['size']) ?></span>
                                            </div>
                                        </td>
                                        <td class="fw-semibold">
                                            <?= formatPrice($item['price']) ?>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm qty-control-wrap">
                                                <button type="button" class="btn btn-outline-secondary cart-qty-minus"><i class="bi bi-dash"></i></button>
                                                <input type="number" class="form-control text-center fw-bold cart-item-qty" value="<?= $item['quantity'] ?>" min="1" max="20" data-key="<?= e($key) ?>">
                                                <button type="button" class="btn btn-outline-secondary cart-qty-plus"><i class="bi bi-plus"></i></button>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-dark item-total-val">
                                            <?= formatPrice($itemTotal) ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-link text-danger p-0 remove-item-btn" data-key="<?= e($key) ?>" title="حذف من السلة">
                                                <i class="bi bi-x-circle-fill fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="shop.php" class="btn btn-manon-outline">
                        <i class="bi bi-arrow-right me-2"></i> متابعة التسوق
                    </a>
                </div>
            </div>

            <!-- Order Summary & Checkout CTA -->
            <div class="col-lg-4">
                <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">ملخص الطلب</h5>
                    
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>المجموع الفرعي:</span>
                        <strong class="text-dark" id="summarySubtotal"><?= formatPrice($subtotal) ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>تكلفة الشحن والتوصيل:</span>
                        <strong class="text-success">مجاناً (عرض خاص)</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-4 pt-3 border-top fs-5 fw-bold">
                        <span>المجموع الكلي:</span>
                        <span class="text-dark" id="summaryTotal"><?= formatPrice($subtotal) ?></span>
                    </div>

                    <a href="checkout.php" class="btn btn-manon-primary w-100 py-3 mb-3">
                        <i class="bi bi-credit-card-2-front me-2"></i> إتمام الطلب الآن
                    </a>

                    <div class="p-3 rounded-3 text-center small text-muted" style="background: #F9F6F0;">
                        <i class="bi bi-shield-check text-success fs-5 d-block mb-1"></i>
                        <span>تأكيد سريع للطلب مع خيار الإرسال المباشر عبر واتساب</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Quantity change handler
    function updateItemQuantity(key, newQty) {
        if (newQty < 1) return;
        const fd = new FormData();
        fd.append('action', 'update');
        fd.append('cart_key', key);
        fd.append('quantity', newQty);

        fetch('api/cart.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }

    document.querySelectorAll('.cart-qty-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('tr').querySelector('.cart-item-qty');
            const key = input.dataset.key;
            const newQty = parseInt(input.value || 1) + 1;
            updateItemQuantity(key, newQty);
        });
    });

    document.querySelectorAll('.cart-qty-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.closest('tr').querySelector('.cart-item-qty');
            const key = input.dataset.key;
            const newQty = parseInt(input.value || 1) - 1;
            if (newQty >= 1) {
                updateItemQuantity(key, newQty);
            }
        });
    });

    document.querySelectorAll('.cart-item-qty').forEach(input => {
        input.addEventListener('change', function() {
            const key = this.dataset.key;
            const newQty = parseInt(this.value || 1);
            if (newQty >= 1) {
                updateItemQuantity(key, newQty);
            }
        });
    });

    // Remove single item
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('هل أنتِ متأكدة من رغبتك في إزالة هذه العباية من السلة؟')) {
                const key = this.dataset.key;
                const fd = new FormData();
                fd.append('action', 'remove');
                fd.append('cart_key', key);

                fetch('api/cart.php', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(data => {
                        location.reload();
                    });
            }
        });
    });

    // Clear whole cart
    const clearBtn = document.getElementById('clearCartBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (confirm('هل أنتِ متأكدة من إفراغ سلة المشتريات بالكامل؟')) {
                const fd = new FormData();
                fd.append('action', 'clear');
                fetch('api/cart.php', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(() => location.reload());
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

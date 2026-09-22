<?php
/**
 * Checkout Page - MANON (إتمام الطلب)
 */
$pageTitle = 'إتمام الطلب | MANON';
require_once __DIR__ . '/includes/header.php';

$cart = getCart();
$subtotal = getCartSubtotal();

if (empty($cart)) {
    header('Location: ' . BASE_URL . 'cart.php');
    exit;
}

$db = getDB();
$electronicPaymentEnabled = getSetting('electronic_payment_enabled', '0') == '1';
$electronicPaymentNote = getSetting('electronic_payment_note', 'الدفع الإلكتروني متاح عند التواصل مع خدمة العملاء.');

// Check for applied coupon in session
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
$discountAmount = 0.00;
$discountCode = null;

if ($appliedCoupon) {
    // Validate again in DB
    $val = validateDiscountCode($appliedCoupon['code'], $subtotal);
    if ($val['valid']) {
        $discountCode = $val['code'];
        $discountAmount = $val['discount_amount'];
    } else {
        unset($_SESSION['applied_coupon']);
        $appliedCoupon = null;
    }
}

$finalTotal = max(0, $subtotal - $discountAmount);

$errors = [];
$customerName = '';
$phone = '';
$city = '';
$address = '';
$paymentMethod = 'cod';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'انتهت صلاحية الجلسة، يرجى إعادة المحاولة.';
    }

    $customerName = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $paymentMethod = ($_POST['payment_method'] ?? 'cod') === 'electronic' ? 'electronic' : 'cod';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($customerName)) {
        $errors[] = 'يرجى إدخال الاسم.';
    }
    if (empty($phone)) {
        $errors[] = 'يرجى إدخال رقم الهاتف للتواصل.';
    }
    if (empty($city)) {
        $errors[] = 'يرجى تحديد المحافظة.';
    }
    if (empty($address)) {
        $errors[] = 'يرجى كتابة العنوان بالتفصيل.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $orderNumber = 'MANON-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
            $shippingCost = 0.00;

            // 1. Insert into orders
            $stmt = $db->prepare("
                INSERT INTO orders (
                    order_number, customer_name, phone, whatsapp, city, address, 
                    notes, discount_code, discount_amount, payment_method, 
                    subtotal, shipping_cost, total, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')
            ");
            $stmt->execute([
                $orderNumber,
                $customerName,
                $phone,
                $phone,
                $city,
                $address,
                $notes,
                $discountCode,
                $discountAmount,
                $paymentMethod,
                $subtotal,
                $shippingCost,
                $finalTotal
            ]);
            $orderId = $db->lastInsertId();

            // 2. Insert into order_items
            $itemStmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, selected_size, selected_color, price, quantity, total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $itemsForWa = [];
            foreach ($cart as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $itemColor = !empty($item['color']) ? $item['color'] : 'أسود';
                $itemSize = !empty($item['size']) ? $item['size'] : '54';

                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $itemSize,
                    $itemColor,
                    $item['price'],
                    $item['quantity'],
                    $itemTotal
                ]);

                $itemsForWa[] = [
                    'product_name'   => $item['name'],
                    'selected_size'  => $itemSize,
                    'selected_color' => $itemColor,
                    'price'          => $item['price'],
                    'quantity'       => $item['quantity']
                ];
            }

            // 3. Mark discount code as used if applicable
            if (!empty($discountCode)) {
                $db->prepare("UPDATE discount_codes SET is_used = 1, used_at = NOW(), order_id = ? WHERE code = ?")
                   ->execute([$orderId, $discountCode]);
            }

            $db->commit();

            // 4. Generate structured WhatsApp message
            $orderData = [
                'order_number'  => '#' . $orderNumber,
                'customer_name' => $customerName,
                'phone'         => $phone,
                'city'          => $city,
                'address'       => $address,
                'notes'         => $notes
            ];
            $waUrl = buildWhatsAppUrl($orderData, $itemsForWa, $finalTotal, $discountAmount, $paymentMethod);

            // 5. Clear cart and session coupon
            clearCart();
            unset($_SESSION['applied_coupon']);

            $_SESSION['last_order_id'] = $orderId;
            $_SESSION['last_order_number'] = $orderNumber;
            $_SESSION['last_wa_url'] = $waUrl;

            header('Location: ' . BASE_URL . 'order-success.php?id=' . $orderId);
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'حدث خطأ أثناء حفظ الطلب: ' . $e->getMessage();
        }
    }
}
?>

<div class="py-4 bg-light border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="index.php">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="cart.php">السلة</a></li>
                <li class="breadcrumb-item active" aria-current="page">إتمام الطلب</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold text-dark mb-0">إتمام الطلب</h1>
    </div>
</div>

<div class="container py-5">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="checkout.php" method="POST" id="checkoutForm">
        <?= csrfField() ?>
        <div class="row g-4">
            <!-- Left: Customer Details & Payment -->
            <div class="col-lg-7">
                <!-- 1. Customer Details -->
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-secondary-subtle mb-4">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">
                        <i class="bi bi-person-lines-fill me-2 text-warning"></i> بياناتك
                    </h5>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control form-control-lg" placeholder="الاسم بالكامل" value="<?= e($customerName) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="tel" name="phone" class="form-control form-control-lg" placeholder="رقم الهاتف للتواصل وتأكيد الشحن" value="<?= e($phone) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">المحافظة <span class="text-danger">*</span></label>
                        <select name="city" class="form-select form-select-lg" required>
                            <option value="" disabled <?= empty($city) ? 'selected' : '' ?>>اختاري المحافظة...</option>
                            <option value="القاهرة" <?= ($city === 'القاهرة') ? 'selected' : '' ?>>القاهرة</option>
                            <option value="الجيزة" <?= ($city === 'الجيزة') ? 'selected' : '' ?>>الجيزة</option>
                            <option value="الإسكندرية" <?= ($city === 'الإسكندرية') ? 'selected' : '' ?>>الإسكندرية</option>
                            <option value="الشرقية" <?= ($city === 'الشرقية') ? 'selected' : '' ?>>الشرقية</option>
                            <option value="الدقهلية (المنصورة)" <?= ($city === 'الدقهلية (المنصورة)') ? 'selected' : '' ?>>الدقهلية (المنصورة)</option>
                            <option value="الغربية (طنطا)" <?= ($city === 'الغربية (طنطا)') ? 'selected' : '' ?>>الغربية (طنطا)</option>
                            <option value="بورسعيد" <?= ($city === 'بورسعيد') ? 'selected' : '' ?>>بورسعيد</option>
                            <option value="السويس" <?= ($city === 'السويس') ? 'selected' : '' ?>>السويس</option>
                            <option value="البحيرة" <?= ($city === 'البحيرة') ? 'selected' : '' ?>>البحيرة</option>
                            <option value="كفر الشيخ" <?= ($city === 'كفر الشيخ') ? 'selected' : '' ?>>كفر الشيخ</option>
                            <option value="المنوفية" <?= ($city === 'المنوفية') ? 'selected' : '' ?>>المنوفية</option>
                            <option value="القليوبية" <?= ($city === 'القليوبية') ? 'selected' : '' ?>>القليوبية</option>
                            <option value="الفيوم" <?= ($city === 'الفيوم') ? 'selected' : '' ?>>الفيوم</option>
                            <option value="بني سويف" <?= ($city === 'بني سويف') ? 'selected' : '' ?>>بني سويف</option>
                            <option value="المنيا" <?= ($city === 'المنيا') ? 'selected' : '' ?>>المنيا</option>
                            <option value="أسيوط" <?= ($city === 'أسيوط') ? 'selected' : '' ?>>أسيوط</option>
                            <option value="سوهاج" <?= ($city === 'سوهاج') ? 'selected' : '' ?>>سوهاج</option>
                            <option value="قنا" <?= ($city === 'قنا') ? 'selected' : '' ?>>قنا</option>
                            <option value="الأقصر" <?= ($city === 'الأقصر') ? 'selected' : '' ?>>الأقصر</option>
                            <option value="أسوان" <?= ($city === 'أسوان') ? 'selected' : '' ?>>أسوان</option>
                            <option value="البحر الأحمر" <?= ($city === 'البحر الأحمر') ? 'selected' : '' ?>>البحر الأحمر</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">العنوان بالتفصيل <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="3" placeholder="المنطقة، الشارع، رقم العقار، رقم الشقة..." required><?= e($address) ?></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-dark">ملاحظات إضافية (اختياري)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="أي تعليمات تودين إبلاغ فريق التوصيل بها..."></textarea>
                    </div>
                </div>

                <!-- 2. Payment Method -->
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-secondary-subtle">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">
                        <i class="bi bi-credit-card me-2 text-warning"></i> طريقة الدفع
                    </h5>

                    <!-- Cash on Delivery -->
                    <div class="form-check p-3 border rounded-3 mb-3 bg-light d-flex align-items-center gap-3">
                        <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="payCod" value="cod" checked>
                        <label class="form-check-label w-100 cursor-pointer" for="payCod">
                            <strong class="d-block text-dark">○ الدفع عند الاستلام</strong>
                            <span class="text-muted small">الدفع نقداً عند استلام ومعاينة العباية مع مندوب التوصيل</span>
                        </label>
                    </div>

                    <!-- Electronic Payment (Configurable) -->
                    <?php if ($electronicPaymentEnabled): ?>
                        <div class="form-check p-3 border rounded-3 bg-light d-flex align-items-center gap-3">
                            <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" id="payElectronic" value="electronic">
                            <label class="form-check-label w-100 cursor-pointer" for="payElectronic">
                                <strong class="d-block text-dark">○ الدفع الإلكتروني</strong>
                                <span class="text-muted small"><?= e($electronicPaymentNote) ?></span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="col-lg-5">
                <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-secondary-subtle position-sticky" style="top: 90px;">
                    <h5 class="fw-bold mb-4 pb-2 border-bottom">
                        <i class="bi bi-bag-check me-2 text-warning"></i> ملخص الطلب (<?= count($cart) ?>)
                    </h5>

                    <!-- Products List with Image & Color -->
                    <div class="order-items-scroll mb-4" style="max-height: 260px; overflow-y: auto;">
                        <?php foreach ($cart as $item): 
                            $itemTotal = $item['price'] * $item['quantity'];
                            $itemColor = !empty($item['color']) ? $item['color'] : 'أسود';
                        ?>
                            <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                <img src="<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" class="rounded-3 border object-fit-cover" style="width: 55px; height: 72px;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 small fw-bold text-dark"><?= e($item['name']) ?></h6>
                                    <div class="text-muted small mt-1">
                                        <span class="badge bg-dark px-2 py-0">اللون: <?= e($itemColor) ?></span>
                                        <span class="badge bg-light text-dark border px-2 py-0">مقاس: <?= e($item['size']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="small text-muted">الكمية: <?= $item['quantity'] ?></span>
                                        <strong class="text-dark small"><?= formatPrice($itemTotal) ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Discount Coupon Form -->
                    <div class="mb-4 p-3 rounded-3" style="background: #F8F5EE; border: 1px dashed #C5A880;">
                        <label class="form-label small fw-bold text-dark mb-1">هل لديكِ كود خصم (من عجلة الحظ)؟</label>
                        <div class="input-group">
                            <input type="text" id="couponCodeInput" class="form-control text-uppercase" placeholder="MANON10-XXXX" value="<?= e($discountCode ?? '') ?>">
                            <button type="button" id="applyCouponBtn" class="btn btn-dark">تطبيق</button>
                        </div>
                        <div id="couponMsgBox" class="small mt-2 <?= $discountAmount > 0 ? 'text-success fw-bold' : '' ?>">
                            <?php if ($discountAmount > 0): ?>
                                ✓ تم تطبيق كود الخصم (<?= $appliedCoupon['percent'] ?>%) بنجاح
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Price Calculations -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>السعر قبل الخصم:</span>
                            <strong class="text-dark" id="displaySubtotal"><?= formatPrice($subtotal) ?></strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2 text-muted">
                            <span>الشحن والتوصيل:</span>
                            <strong class="text-success">مجاناً</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2 text-danger <?= $discountAmount > 0 ? '' : 'd-none' ?>" id="discountRow">
                            <span>الخصم:</span>
                            <strong id="displayDiscount">- <?= formatPrice($discountAmount) ?></strong>
                        </div>

                        <div class="d-flex justify-content-between pt-3 border-top fs-4 fw-bold">
                            <span class="text-dark">الإجمالي:</span>
                            <span class="text-dark" id="displayFinalTotal"><?= formatPrice($finalTotal) ?></span>
                        </div>
                    </div>

                    <!-- Large Confirm Order Button -->
                    <button type="submit" class="btn btn-manon-primary w-100 py-3 fs-5 shadow-lg mb-3">
                        <i class="bi bi-check-circle fs-4 me-2"></i> تأكيد الطلب
                    </button>

                    <div class="text-center small text-muted">
                        <i class="bi bi-shield-check text-success"></i> سيتم تأكيد طلبكِ وحفظه فوراً في النظام مع خيار المتابعة عبر واتساب
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const couponBtn = document.getElementById('applyCouponBtn');
    const couponInput = document.getElementById('couponCodeInput');
    const couponMsgBox = document.getElementById('couponMsgBox');
    const discountRow = document.getElementById('discountRow');
    const displayDiscount = document.getElementById('displayDiscount');
    const displayFinalTotal = document.getElementById('displayFinalTotal');

    if (couponBtn) {
        couponBtn.addEventListener('click', () => {
            const code = couponInput.value.trim();
            if (!code) {
                couponMsgBox.className = 'small mt-2 text-danger';
                couponMsgBox.textContent = 'يرجى كتابة كود الخصم أولاً';
                return;
            }

            couponBtn.disabled = true;
            couponBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            const fd = new FormData();
            fd.append('action', 'apply_coupon');
            fd.append('coupon_code', code);

            fetch('api/cart.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    couponBtn.disabled = false;
                    couponBtn.innerHTML = 'تطبيق';

                    if (res.success) {
                        couponMsgBox.className = 'small mt-2 text-success fw-bold';
                        couponMsgBox.textContent = res.message;
                        discountRow.classList.remove('d-none');
                        displayDiscount.textContent = '- ' + res.discountFormatted;
                        displayFinalTotal.textContent = res.totalAfterFormatted;
                    } else {
                        couponMsgBox.className = 'small mt-2 text-danger';
                        couponMsgBox.textContent = res.message;
                    }
                })
                .catch(() => {
                    couponBtn.disabled = false;
                    couponBtn.innerHTML = 'تطبيق';
                    couponMsgBox.className = 'small mt-2 text-danger';
                    couponMsgBox.textContent = 'تعذر الاتصال بالخادم';
                });
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

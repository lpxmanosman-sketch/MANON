<?php
/**
 * Admin - Order Details View
 */
$adminTitle = 'تفاصيل الطلب الكاملة';
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = getDB();

// Handle status change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $newStatus = $_POST['status'] ?? 'new';
        $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        setFlash('success', 'تم تحديث حالة الطلب بنجاح.');
    }
    header('Location: order-details.php?id=' . $id);
    exit;
}

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'الطلب غير موجود.');
    header('Location: orders.php');
    exit;
}

// Fetch ordered items with product images if available
$itemsStmt = $db->prepare("
    SELECT oi.*, p.main_image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

// Prepare WhatsApp message to reply to customer
$cleanPhone = preg_replace('/\D/', '', $order['phone']);
if (strpos($cleanPhone, '20') !== 0 && strlen($cleanPhone) === 11 && strpos($cleanPhone, '01') === 0) {
    $cleanPhone = '2' . $cleanPhone;
}
$replyMsg = "مرحباً أستاذة {$order['customer_name']}، معكِ خدمة عملاء متجر منون بخصوص طلبك رقم {$order['order_number']}.";
$waCustomerUrl = "https://wa.me/{$cleanPhone}?text=" . rawurlencode($replyMsg);
?>

<div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <a href="orders.php" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="bi bi-arrow-right me-1"></i> العودة لقائمة الطلبات
        </a>
        <h4 class="fw-bold text-dark mb-0">
            تفاصيل الطلب: <span class="text-primary">#<?= e($order['order_number']) ?></span>
        </h4>
        <span class="text-muted small">بتاريخ: <?= date('Y/m/d h:i A', strtotime($order['created_at'])) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $waCustomerUrl ?>" target="_blank" class="btn btn-whatsapp-order">
            <i class="bi bi-whatsapp"></i> مراسلة العميلة عبر واتساب
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Order Items List -->
    <div class="col-lg-8">
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
            <h5 class="fw-bold mb-3 pb-2 border-bottom">المنتجات المطلوبة (<?= count($items) ?>)</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 70px;">العباية</th>
                            <th>اسم الموديل</th>
                            <th>اللون والمقاس</th>
                            <th>سعر القطعة</th>
                            <th>الكمية</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?= BASE_URL . e($item['main_image'] ?: 'assets/images/abaya1.jpg') ?>" class="rounded-3 border object-fit-cover" style="width: 55px; height: 70px;">
                                </td>
                                <td>
                                    <strong class="text-dark"><?= e($item['product_name']) ?></strong>
                                    <?php if ($item['product_id']): ?>
                                        <div class="small text-muted"><a href="<?= BASE_URL ?>product.php?id=<?= $item['product_id'] ?>" target="_blank">معاينة العباية <i class="bi bi-box-arrow-up-right"></i></a></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1"><?= e($item['selected_color'] ?: 'افتراضي') ?></span>
                                    <span class="badge bg-secondary px-2 py-1"><?= e($item['selected_size'] ?: 'قياسي') ?></span>
                                </td>
                                <td><?= formatPrice($item['price']) ?></td>
                                <td><span class="fw-bold">× <?= $item['quantity'] ?></span></td>
                                <td><strong class="text-dark"><?= formatPrice($item['total']) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">المجموع الفرعي:</td>
                            <td class="fw-bold"><?= formatPrice($order['subtotal']) ?></td>
                        </tr>
                        <?php if (!empty($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold text-danger">خصم كود (<?= e($order['discount_code']) ?>):</td>
                            <td class="text-danger fw-bold">-<?= formatPrice($order['discount_amount']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="5" class="text-end fw-semibold">تكلفة الشحن والتوصيل:</td>
                            <td class="text-success fw-bold">مجاناً</td>
                        </tr>
                        <tr class="table-light fs-5">
                            <td colspan="5" class="text-end fw-bold">إجمالي المبلغ المستحق:</td>
                            <td class="fw-bold text-dark"><?= formatPrice($order['total']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if (!empty($order['notes'])): ?>
            <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
                <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-chat-left-text me-2 text-warning"></i> ملاحظات العميلة على الطلب:</h6>
                <p class="text-muted mb-0 bg-light p-3 rounded-3"><?= nl2br(e($order['notes'])) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Customer & Order Status Sidebar -->
    <div class="col-lg-4">
        <!-- Update Status Card -->
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle mb-4">
            <h5 class="fw-bold mb-3 pb-2 border-bottom">حالة الطلب الحالية</h5>
            <div class="mb-3 text-center">
                <?= getStatusBadge($order['status']) ?>
            </div>

            <form action="order-details.php?id=<?= $id ?>" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_order_status">
                <label class="form-label small fw-bold">تحديث الحالة إلى:</label>
                <select name="status" class="form-select mb-3">
                    <option value="new" <?= ($order['status'] === 'new') ? 'selected' : '' ?>>جديد (New)</option>
                    <option value="confirmed" <?= ($order['status'] === 'confirmed') ? 'selected' : '' ?>>مؤكد (Confirmed)</option>
                    <option value="preparing" <?= ($order['status'] === 'preparing') ? 'selected' : '' ?>>قيد التجهيز (Preparing)</option>
                    <option value="delivered" <?= ($order['status'] === 'delivered') ? 'selected' : '' ?>>تم التوصيل (Delivered)</option>
                    <option value="cancelled" <?= ($order['status'] === 'cancelled') ? 'selected' : '' ?>>ملغي (Cancelled)</option>
                </select>
                <button type="submit" class="btn btn-dark w-100">تحديث الحالة</button>
            </form>
        </div>

        <!-- Customer Information Card -->
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
            <h5 class="fw-bold mb-3 pb-2 border-bottom">بيانات العميلة</h5>

            <div class="mb-3">
                <span class="text-muted small d-block">الاسم الكامل:</span>
                <strong class="text-dark fs-6"><?= e($order['customer_name']) ?></strong>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">رقم الهاتف:</span>
                <strong class="text-dark dir-ltr d-inline-block"><?= e($order['phone']) ?></strong>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">رقم الواتساب:</span>
                <strong class="text-dark dir-ltr d-inline-block"><?= e($order['whatsapp']) ?></strong>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">المحافظة / المدينة:</span>
                <strong class="text-dark"><?= e($order['city']) ?></strong>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">طريقة الدفع:</span>
                <?php if (($order['payment_method'] ?? 'cod') === 'electronic'): ?>
                    <span class="badge bg-info text-dark py-2 px-3"><i class="bi bi-credit-card me-1"></i> دفع إلكتروني (بطاقة / محفظة)</span>
                <?php else: ?>
                    <span class="badge bg-light text-dark border py-2 px-3"><i class="bi bi-cash-stack me-1"></i> الدفع عند الاستلام (COD)</span>
                <?php endif; ?>
            </div>

            <div class="mb-0">
                <span class="text-muted small d-block">العنوان بالتفصيل:</span>
                <strong class="text-dark small lh-base"><?= nl2br(e($order['address'])) ?></strong>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

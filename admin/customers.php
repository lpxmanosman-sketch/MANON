<?php
/**
 * Admin - Customers Directory
 */
$adminTitle = 'سجل العميلات وبيانات الطلب';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Group unique customers by phone
$sql = "
    SELECT 
        customer_name, 
        phone, 
        whatsapp, 
        city, 
        address,
        COUNT(id) as total_orders, 
        SUM(total) as total_spent, 
        MAX(created_at) as last_order_date
    FROM orders 
    GROUP BY phone 
    ORDER BY total_orders DESC, last_order_date DESC
";
$customers = $db->query($sql)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">سجل العميلات</h4>
        <p class="text-muted small mb-0">بيانات العميلات وقيمة مشترياتهن وتاريخ آخر طلب</p>
    </div>
</div>

<div class="bg-white rounded-4 shadow-sm border border-secondary-subtle overflow-hidden">
    <?php if (empty($customers)): ?>
        <div class="p-5 text-center text-muted">
            <i class="bi bi-people fs-1 d-block mb-2"></i>
            لا توجد بيانات عميلات مسجلة بعد.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>اسم العميلة</th>
                        <th>رقم الهاتف / واتساب</th>
                        <th>المحافظة</th>
                        <th>عدد الطلبات</th>
                        <th>إجمالي المشتريات</th>
                        <th>تاريخ آخر طلب</th>
                        <th class="text-center" style="width: 130px;">تواصل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): 
                        $phoneNum = preg_replace('/\D/', '', $c['phone']);
                        if (strpos($phoneNum, '20') !== 0 && strlen($phoneNum) === 11 && strpos($phoneNum, '01') === 0) {
                            $phoneNum = '2' . $phoneNum;
                        }
                    ?>
                        <tr>
                            <td>
                                <strong class="text-dark"><?= e($c['customer_name']) ?></strong>
                            </td>
                            <td>
                                <div class="dir-ltr text-end fw-semibold"><?= e($c['phone']) ?></div>
                            </td>
                            <td><?= e($c['city']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 py-2"><?= $c['total_orders'] ?> طلب</span>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= formatPrice($c['total_spent']) ?>
                            </td>
                            <td class="small text-muted">
                                <?= date('Y/m/d', strtotime($c['last_order_date'])) ?>
                            </td>
                            <td class="text-center">
                                <a href="https://wa.me/<?= $phoneNum ?>" target="_blank" class="btn btn-sm btn-success" title="محادثة واتساب">
                                    <i class="bi bi-whatsapp"></i> واتساب
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

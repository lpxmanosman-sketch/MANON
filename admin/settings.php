<?php
/**
 * Admin - Website Settings
 */
$adminTitle = 'إعدادات المتجر وبيانات الاتصال';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$errors = [];

// Handle General Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'خطأ في التحقق من الجلسة.';
    } else {
        $fields = [
            'site_name', 'site_slogan', 'whatsapp_number', 'phone_number', 
            'email', 'currency', 'address', 'announcement', 'hero_title', 'hero_subtitle'
        ];

        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($fields as $key) {
            if (isset($_POST[$key])) {
                $stmt->execute([$key, trim($_POST[$key])]);
            }
        }

        setFlash('success', 'تم حفظ إعدادات المتجر بنجاح!');
        header('Location: settings.php');
        exit;
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'خطأ في التحقق من الجلسة.';
    } else {
        $currPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        $adminId = $_SESSION['admin_user']['id'];
        $userStmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $userStmt->execute([$adminId]);
        $hash = $userStmt->fetchColumn();

        if (!password_verify($currPass, $hash)) {
            $errors[] = 'كلمة المرور الحالية غير صحيحة.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'كلمة المرور الجديدة يجب ألا تقل عن 6 أحرف.';
        } elseif ($newPass !== $confPass) {
            $errors[] = 'تأكيد كلمة المرور غير متطابق.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $adminId]);
            setFlash('success', 'تم تغيير كلمة المرور بنجاح!');
            header('Location: settings.php');
            exit;
        }
    }
}

// Fetch current settings
$settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="mb-4">
    <h4 class="fw-bold text-dark mb-1">إعدادات المتجر العامة</h4>
    <p class="text-muted small mb-0">التحكم في بيانات التواصل ورقم الواتساب الرسمي والنصوص التعريفية</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger rounded-4 shadow-sm mb-4">
        <ul class="mb-0">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Store Info Form -->
    <div class="col-lg-8">
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border border-secondary-subtle">
            <h5 class="fw-bold mb-4 pb-2 border-bottom">بيانات المتجر والواتساب</h5>
            <form action="settings.php" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_settings">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">اسم العلامة التجارية</label>
                        <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? 'MANON | منون') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">شعار البراند (Slogan)</label>
                        <input type="text" name="site_slogan" class="form-control" value="<?= e($settings['site_slogan'] ?? 'عبايتك فخامة تليق بك') ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">رقم الواتساب للطلبات المباشرة <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-whatsapp text-success"></i></span>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?= e($settings['whatsapp_number'] ?? '+20 12 73572887') ?>" required>
                        </div>
                        <div class="form-text small">هذا الرقم يُستخدم لاستقبال رسائل الطلبات المنسقة عبر wa.me</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">رقم الهاتف للاتصال</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="phone_number" class="form-control" value="<?= e($settings['phone_number'] ?? '+20 12 73572887') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">البريد الإلكتروني للعملاء</label>
                        <input type="email" name="email" class="form-control" value="<?= e($settings['email'] ?? 'info@manon-fashion.com') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">رمز العملة الافتراضية</label>
                        <input type="text" name="currency" class="form-control" value="<?= e($settings['currency'] ?? 'ج.م') ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">العنوان ومقر البراند</label>
                    <input type="text" name="address" class="form-control" value="<?= e($settings['address'] ?? 'القاهرة - مصر') ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">شريط الإعلانات أعلى الموقع (Announcement Bar)</label>
                    <textarea name="announcement" class="form-control" rows="2"><?= e($settings['announcement'] ?? 'شحن مجاني على كافة الطلبات هذا الأسبوع | تصميمات استثنائية لأناقة تدوم') ?></textarea>
                </div>

                <button type="submit" class="btn btn-dark px-4 py-2">
                    <i class="bi bi-check2 me-1"></i> حفظ جميع الإعدادات
                </button>
            </form>
        </div>
    </div>

    <!-- Password Change Card -->
    <div class="col-lg-4">
        <div class="bg-white p-4 rounded-4 shadow-sm border border-secondary-subtle">
            <h5 class="fw-bold mb-3 pb-2 border-bottom">تغيير كلمة المرور</h5>
            <form action="settings.php" method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="change_password">

                <div class="mb-3">
                    <label class="form-label small fw-bold">كلمة المرور الحالية</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">كلمة المرور الجديدة</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-outline-dark w-100">
                    تحديث كلمة المرور
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

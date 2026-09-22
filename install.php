<?php
/**
 * MANON Database Auto-Installer
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=UTF-8');

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$message = '';
$success = false;

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    $sqlFile = __DIR__ . '/database/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File database/database.sql not found!");
    }

    $sql = file_get_contents($sqlFile);
    
    // Execute multiple statements
    $pdo->exec($sql);
    
    // Update admin password with fresh hash
    $pdo->exec("USE `manon_db`");
    $freshHash = password_hash('admin123456', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `email` = 'admin@manon.com'");
    $stmt->execute([$freshHash]);

    $success = true;
    $message = "تم تثبيت قاعدة البيانات manon_db بنجاح وتمت تهيئة البيانات الافتراضية وحساب المدير!";
} catch (PDOException $e) {
    $message = "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
} catch (Exception $e) {
    $message = "خطأ: " . $e->getMessage();
}

if (php_sapi_name() === 'cli') {
    echo ($success ? "SUCCESS: " : "ERROR: ") . $message . "\n";
    exit($success ? 0 : 1);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تثبيت متجر منون | MANON Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #FAF7F2; padding: 50px 20px; color: #18181B; }
        .setup-card { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 35px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .logo-img { max-height: 80px; margin-bottom: 20px; }
        .btn-gold { background: #C5A880; color: #fff; font-weight: 600; border: none; padding: 12px 25px; border-radius: 8px; }
        .btn-gold:hover { background: #b09165; color: #fff; }
    </style>
</head>
<body>
    <div class="setup-card text-center">
        <img src="assets/images/logo.jpg" alt="MANON" class="logo-img">
        <h3 class="fw-bold mb-3">تهيئة متجر منون | MANON</h3>
        
        <?php if ($success): ?>
            <div class="alert alert-success py-3 mb-4">
                <strong>تهانينا!</strong> <?= $message ?>
            </div>
            <div class="bg-light p-3 rounded mb-4 text-start text-end">
                <p class="mb-1"><strong>رابط المتجر:</strong> <a href="index.php">الصفحة الرئيسية للمتجر</a></p>
                <p class="mb-1"><strong>رابط لوحة التحكم:</strong> <a href="admin/login.php">لوحة تحكم الإدارة</a></p>
                <p class="mb-1"><strong>البريد الإلكتروني للمدير:</strong> <code>admin@manon.com</code></p>
                <p class="mb-0"><strong>كلمة المرور الافتراضية:</strong> <code>admin123456</code></p>
            </div>
            <a href="index.php" class="btn btn-gold w-100">الانتقال للمتجر الآن</a>
        <?php else: ?>
            <div class="alert alert-danger py-3 mb-4">
                <?= $message ?>
            </div>
            <a href="install.php" class="btn btn-gold">إعادة المحاولة</a>
        <?php endif; ?>
    </div>
</body>
</html>

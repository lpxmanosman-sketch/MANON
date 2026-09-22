<?php
/**
 * Admin Login Page - MANON
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'انتهت صلاحية الجلسة، يرجى إعادة المحاولة.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $error = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session id to prevent fixation
                session_regenerate_id(true);

                $_SESSION['admin_user'] = [
                    'id'    => $user['id'],
                    'name'  => $user['name'],
                    'email' => $user['email'],
                    'role'  => $user['role']
                ];

                header('Location: ' . BASE_URL . 'admin/dashboard.php');
                exit;
            } else {
                $error = 'بيانات الدخول غير صحيحة. يرجى التحقق وإعادة المحاولة.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المشرف | منون</title>
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #1C1917 0%, #292420 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 40px;
            max-width: 440px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(197, 168, 128, 0.3);
        }
        .login-logo {
            max-height: 75px;
            margin-bottom: 20px;
        }
        .btn-gold {
            background: #C5A880;
            color: #fff;
            font-weight: 700;
            border: none;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            background: #a8895e;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <img src="<?= BASE_URL ?>assets/images/logo.jpg" alt="MANON" class="login-logo rounded-2 p-1 border">
    <h3 class="fw-bold text-dark mb-1">لوحة تحكم منون</h3>
    <p class="text-muted small mb-4">أدخلي بيانات المشرف للدخول وإدارة المتجر</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-start small py-2 px-3 rounded-3 mb-4">
            <i class="bi bi-exclamation-triangle me-1"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="text-start">
        <?= csrfField() ?>
        <div class="mb-3">
            <label class="form-label small fw-bold text-dark">البريد الإلكتروني</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="admin@manon.com" value="admin@manon.com" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold text-dark">كلمة المرور</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" value="admin123456" required>
            </div>
        </div>

        <button type="submit" class="btn btn-gold w-100 py-2 fs-6">
            <i class="bi bi-box-arrow-in-left me-2"></i> تسجيل الدخول
        </button>

        <div class="text-center mt-4">
            <a href="<?= BASE_URL ?>index.php" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-right me-1"></i> العودة للمتجر الرئيسي
            </a>
        </div>
    </form>
</div>

</body>
</html>

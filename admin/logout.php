<?php
/**
 * Admin Logout
 */
require_once __DIR__ . '/../includes/config.php';

unset($_SESSION['admin_user']);
session_destroy();

header('Location: ' . BASE_URL . 'admin/login.php');
exit;

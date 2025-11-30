<?php
// FILE KIỂM TRA ĐĂNG NHẬP ADMIN
// Bắt đầu session nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database
require_once __DIR__ . '/dbadmin.php';

// Khởi tạo database
$db = new dbadmin();

// Kiểm tra đăng nhập
if (!$db->isLoggedIn()) {
    // Chưa đăng nhập hoặc không phải admin/editor → Chuyển về trang login
    header('Location: login.php');
    exit();
}
?>
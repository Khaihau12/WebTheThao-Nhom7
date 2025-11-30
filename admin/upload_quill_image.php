<?php
session_start();
require_once 'dbadmin.php';

$db = new dbadmin();

// Kiểm tra đăng nhập
if (!$db->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Bạn cần đăng nhập']);
    exit();
}

// Kiểm tra có file upload không
if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Không có file']);
    exit();
}

$file = $_FILES['image'];

// Kiểm tra lỗi
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Lỗi upload']);
    exit();
}

// Kiểm tra loại file
$allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Chỉ chấp nhận JPG, PNG, GIF']);
    exit();
}

// Kiểm tra kích thước (5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'File quá lớn (max 5MB)']);
    exit();
}

// Tạo tên file
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'content_' . uniqid() . '_' . time() . '.' . $ext;

// Upload
$upload_dir = dirname(__DIR__) . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$upload_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Trả về URL
    echo json_encode(['url' => '/web21-10/uploads/' . $filename]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể lưu file']);
}
?>

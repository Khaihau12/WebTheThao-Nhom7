<?php
session_start(); // Phải có session_start() để lưu thông tin đăng nhập
require 'dbadmin.php';

$dbAdmin = new dbadmin();

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    } else {
        $username = '';
    }
    
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    } else {
        $password = '';
    }

    $user = $dbAdmin->login($username, $password);
    if ($user) {
        $success = 'Đăng nhập thành công! Chuyển trang...';
        header('refresh:1;url=index.php');
    } else {
        $err = 'Sai username hoặc mật khẩu';
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đăng Nhập</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<h1>Đăng Nhập Hệ Thống</h1>
<?php if ($err) { ?><p class="error"><?=$err?></p><?php } ?>
<?php if ($success) { ?><p class="success"><?=$success?></p><?php } ?>
<form method="post">
    <label>Username</label>
    <input type="text" name="username" required>
    
    <label>Password</label>
    <input type="password" name="password" required>
    
    <button type="submit">Đăng Nhập</button>
</form>
</div>
</body>
</html>
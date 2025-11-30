<?php
session_start();
require_once 'dbuser.php';
$db = new dbuser();

// Biến thông báo
$message = '';
$message_type = '';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    } else {
        $username = '';
    }
    
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    } else {
        $email = '';
    }
    
    if (isset($_POST['display_name'])) {
        $display_name = trim($_POST['display_name']);
    } else {
        $display_name = '';
    }
    
    if (isset($_POST['password'])) {
        $password = trim($_POST['password']);
    } else {
        $password = '';
    }
    
    if (isset($_POST['confirm_password'])) {
        $confirm_password = trim($_POST['confirm_password']);
    } else {
        $confirm_password = '';
    }
    
    // kiểm tra đơn giản: Chỉ check mật khẩu nhập lại có khớp không
    if ($password !== $confirm_password) {
        $message = 'Mật khẩu nhập lại không khớp!';
        $message_type = 'error';
    } else {
        // Gọi function đăng ký (function sẽ tự validate các trường bắt buộc)
        $result = $db->dangKy($username, $password, $email, $display_name);
        
        if ($result['success']) {
            $message = 'Đăng ký thành công! Chuyển đến trang đăng nhập sau 2 giây...';
            $message_type = 'success';
            header('refresh:2;url=loginuser.php');
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }
}

// Lấy danh mục cho menu
$danhMuc = $db->layTatCaChuyenMuc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Tin Tức 24H</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .register-container h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-register:hover {
            background-color: #229954;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
        }
        
        .form-footer a {
            color: #3498db;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .message.error {
            background-color: #ffe6e6;
            color: #e74c3c;
            border: 1px solid #e74c3c;
        }
        
        .message.success {
            background-color: #e6ffe6;
            color: #27ae60;
            border: 1px solid #27ae60;
        }
    </style>
</head>
<body>
    
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                
                <div class="header-top">
                    
                    <div class="site-logo">
                        <a href="index.php">
                            <h1>📰 24H</h1>
                            <span>Tin Tức Thể Thao</span>
                        </a>
                    </div>
                    
                    
                    <div class="header-actions">
                        <form action="index.php" method="get" class="search-form">
                            <input type="text" name="q" placeholder="Tìm kiếm...">
                            <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                        <div class="user-links">
                            <a href="loginuser.php"><i class="fa fa-user"></i> Đăng nhập</a>
                        </div>
                    </div>
                </div>
                
                
                <nav class="main-navigation">
                    <ul>
                        <li><a href="index.php"><i class="fa fa-home"></i> Trang Chủ</a></li>
                        <?php foreach($danhMuc as $dm) { ?>
                        <li><a href="category.php?slug=<?php echo $dm['slug']; ?>"><?php echo $dm['name']; ?></a></li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    
    <main>
        <div class="container">
            <div class="register-container">
                <h2> Đăng ký tài khoản</h2>
                
                <?php if ($message) { ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php } ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập <span style="color:red;">*</span></label>
                        <input type="text" id="username" name="username" 
                               placeholder="Nhập tên đăng nhập (không dấu, không khoảng trắng)" 
                               value="<?php if(isset($_POST['username'])) { echo $_POST['username']; } ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span style="color:red;">*</span></label>
                        <input type="email" id="email" name="email" 
                               placeholder="Nhập email (vd: user@gmail.com)" 
                               value="<?php if(isset($_POST['email'])) { echo $_POST['email']; } ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_name">Tên hiển thị</label>
                        <input type="text" id="display_name" name="display_name" 
                               placeholder="Nhập tên hiển thị (có thể để trống)" 
                               value="<?php if(isset($_POST['display_name'])) { echo $_POST['display_name']; } ?>">
                        <small style="color:#999;font-size:12px;">Nếu để trống, hệ thống sẽ dùng tên đăng nhập</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mật khẩu <span style="color:red;">*</span></label>
                        <input type="password" id="password" name="password" 
                               placeholder="Nhập mật khẩu (ít nhất 6 ký tự)" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Nhập lại mật khẩu <span style="color:red;">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Nhập lại mật khẩu" required>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <i class="fa fa-user-plus"></i> Đăng ký
                    </button>
                </form>
                
                <div class="form-footer">
                    <p>Đã có tài khoản? <a href="loginuser.php"> Đăng nhập ngay</a></p>
                    <p><a href="index.php">← Quay lại trang chủ</a></p>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer style="margin-top:40px;padding:20px 0;border-top:1px solid #eee;color:#666;font-size:13px">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center">
            <div>© 2025 Web Thể Thao - Tất cả vì người đọc.</div>
            <div style="opacity:.6">
                <a href="admin/login.php" title="Đăng nhập quản trị" style="color:#666;text-decoration:none">Quản trị</a>
            </div>
        </div>
    </footer>
</body>
</html>

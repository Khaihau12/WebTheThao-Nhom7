<?php
// Kiểm tra đăng nhập
require_once 'check_login.php';

$message = "";

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $parent_id = $_POST['parent_id'];
    
    // Thêm chuyên mục (slug tự động tạo từ name)
    $result = $db->themChuyenMuc($name, $parent_id);
    
    if ($result === true) {
        $message = "Thêm chuyên mục thành công!";
    } else {
        // Nếu result là string thì đó là thông báo lỗi
        $message = is_string($result) ? $result : "Lỗi khi thêm chuyên mục!";
    }
}

// Lấy danh sách chuyên mục để chọn parent
$categories = $db->layDanhSachChuyenMuc();
?>

<div class="content-header">
    <h2>Thêm Chuyên Mục Mới</h2>
</div>

<div class="content-body">    <?php if ($message) { ?>
        <?php
        if (strpos($message, 'thành công') !== false) {
            $messageClass = 'success';
        } else {
            $messageClass = 'error';
        }
        ?>
        <p class="<?= $messageClass ?>">
            <?= $message ?>
        </p>
    <?php } ?>
    
    <form method="POST" action="">
        <label>Tên chuyên mục:</label>
        <input type="text" name="name" required>
        
        <label>Chuyên mục cha (để trống nếu là chuyên mục gốc):</label>
        <select name="parent_id">
            <option value="">-- Không có --</option>
            <?php foreach ($categories as $cat) { ?>
                <option value="<?= $cat['category_id'] ?>"><?= $cat['name'] ?></option>
            <?php } ?>
        </select>
        
        <button type="submit" class="btn btn-success">Đã Thêm Chuyên Mục</button>
        <a href="?page=categories" class="btn">← Quay lại danh sách</a>
    </form>
</div>

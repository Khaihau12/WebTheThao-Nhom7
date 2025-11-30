<?php
// Kiểm tra đăng nhập
require_once 'check_login.php';

$message = "";
$message_type = "";

// Lấy SLUG từ URL
if (isset($_GET['slug'])) {
    $category_slug = $_GET['slug'];
} else {
    $category_slug = "";
}

// Kiểm tra SLUG hợp lệ
if (empty($category_slug)) {
    $message = "Slug chuyên mục không hợp lệ!";
    $message_type = "error";
} else {
    // Lấy thông tin chuyên mục hiện tại
    $category = $db->layChuyenMucTheoSlug($category_slug);
    
    if (!$category) {
        $message = "Không tìm thấy chuyên mục!";
        $message_type = "error";
    }
}

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($category)) {
    $name = trim($_POST['name']);
    
    // Xử lý parent_id - cho phép null
    if (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') {
        $parent_id = (int)$_POST['parent_id'];
    } else {
        $parent_id = null;
    }
    
    // Sửa chuyên mục (slug tự động tạo từ name mới)
    $result = $db->suaChuyenMuc($category_slug, $name, $parent_id);
    
    if ($result === true) {
        $message = " Chỉnh sửa chuyên mục thành công!";
        $message_type = "success";
        
        // Gọi phương thức createSlug để tạo slug mới từ name
        $new_slug = $db->createSlug($name);
        
        // Lấy lại thông tin sau khi sửa
        $category = $db->layChuyenMucTheoSlug($new_slug);
        if ($category) {
            $category_slug = $new_slug;
        }
    } else {
        // Nếu result là string thì đó là thông báo lỗi
        $message = is_string($result) ? $result : "Lỗi khi chỉnh sửa chuyên mục!";
    }
}

// Lấy danh sách chuyên mục để chọn parent (trừ chính nó)
$categories = $db->layDanhSachChuyenMuc();
?>

<div class="content-header">
    <h2> Chỉnh Sửa Chuyên Mục</h2>
</div>

<div class="content-body">
    <?php if ($message) { ?>
        <?php
        if ($message_type == 'success') {
            $messageClass = 'success';
        } else {
            $messageClass = 'error';
        }
        ?>
        <p class="<?= $messageClass ?>">
            <?= $message ?>
        </p>
    <?php } ?>
    
    <?php if (isset($category)) { ?>
    <form method="POST" action="?page=edit-category&slug=<?= $category_slug ?>">
        <label>Tên chuyên mục:</label>
        <input type="text" name="name" value="<?= $category['name'] ?>" required>
        <small>Slug sẽ tự động tạo từ tên chuyên mục</small>
        
        <label>Chuyên mục cha (để trống nếu là chuyên mục gốc):</label>
        <select name="parent_id">
            <option value="">-- Không có --</option>
            <?php foreach ($categories as $cat) { ?>
                <?php if ($cat['slug'] != $category_slug) { // Không cho chọn chính nó ?>
                    <?php
                    if ($category['parent_id'] == $cat['category_id']) {
                        $selected = 'selected';
                    } else {
                        $selected = '';
                    }
                    ?>
                    <option value="<?= $cat['category_id'] ?>" <?= $selected ?>>
                        <?= $cat['name'] ?>
                    </option>
                <?php } ?>
            <?php } ?>
        </select>
        
        <button type="submit" class="btn btn-success">✓ Lưu Thay Đổi</button>
        <a href="?page=categories" class="btn">← Quay lại danh sách</a>
    </form>
    <?php } else { ?>
        <p>Không thể chỉnh sửa chuyên mục này.</p>
        <a href="?page=categories" class="btn">← Quay lại danh sách</a>
    <?php } ?>
</div>

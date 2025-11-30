<?php
// TRANG ADMIN - CHỈNH SỬA BÀI VIẾT
// Khởi tạo database
$conn = $db->getConnection();

// Lấy slug từ URL
if (isset($_GET['slug'])) {
    $article_slug = $_GET['slug'];
} else {
    $article_slug = '';
}

if (empty($article_slug)) {
    die('Slug bài báo không hợp lệ');
}

// Lấy thông tin bài báo bằng phương thức
$article = $db->layBaiVietTheoSlug($article_slug);
if (!$article) {
    die('Không tìm thấy bài báo');
}

// Biến lưu thông báo
$message = '';
$message_type = '';

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = $article['image_url']; // Giữ ảnh cũ
    
    // Xử lý upload ảnh mới (nếu có)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $message = 'Chỉ chấp nhận file ảnh JPG, PNG, GIF!';
            $message_type = 'error';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $message = 'File ảnh quá lớn! Tối đa 5MB.';
            $message_type = 'error';
        } else {
            // Tạo tên file 
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $upload_path = dirname(__DIR__) . '/uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = '/web21-10/uploads/' . $filename;
                
                // Xóa ảnh cũ nếu có
                if (!empty($article['image_url']) && file_exists(dirname(__DIR__) . $article['image_url'])) {
                    @unlink(dirname(__DIR__) . $article['image_url']);
                }
            } else {
                $message = 'Lỗi khi upload ảnh!';
                $message_type = 'error';
            }
        }
    }
    
    // Nếu không có lỗi upload ảnh, tiếp tục cập nhật
    if ($message_type !== 'error') {
        if (isset($_POST['category_id'])) {
            $category_id = (int)$_POST['category_id'];
        } else {
            $category_id = 0;
        }
        
        if (isset($_POST['title'])) {
            $title = trim($_POST['title']);
        } else {
            $title = '';
        }
        
        if (isset($_POST['summary'])) {
            $summary = trim($_POST['summary']);
        } else {
            $summary = '';
        }
        
        if (isset($_POST['content'])) {
            $content = trim($_POST['content']);
        } else {
            $content = '';
        }
        
        if (isset($_POST['is_featured'])) {
            $is_featured = 1;
        } else {
            $is_featured = 0;
        }

        // kiểm tra
        if ($category_id <= 0) {
            $message = 'Vui lòng chọn chuyên mục!';
            $message_type = 'error';
        } elseif ($title === '') {
            $message = 'Vui lòng nhập tiêu đề!';
            $message_type = 'error';
        } else {
            // Chuẩn bị dữ liệu
            $data = [
                'category_id' => $category_id,
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'image_url' => $image_url,
                'is_featured' => $is_featured
            ];
            
            // Gọi phương thức suaBaiViet
            if ($db->suaBaiViet($article_slug, $data)) {
                $message = 'Cập nhật bài viết thành công!';
                $message_type = 'success';
                
                // Lấy slug mới từ title
                $new_slug = $db->createSlug($title);
                
                // Lấy lại thông tin sau khi sửa
                $article = $db->layBaiVietTheoSlug($new_slug);
                if ($article) {
                    $article_slug = $new_slug;
                }
            } else {
                $message = 'Lỗi khi cập nhật bài viết!';
                $message_type = 'error';
            }
        }
    }
}

// Lấy danh sách chuyên mục
$categories = $db->layDanhSachChuyenMuc();
?>

<div class="content-header">
    <h2> Chỉnh Sửa Bài Viết</h2>
    <p><i>Cập nhật bài viết: <?= $article['title']; ?></i></p>
</div>

<div class="content-body">
    <?php if ($message) { ?>
        <div class="<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php } ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        
        
        <label>Chuyên mục <span style="color:red;">*</span></label>
        <select name="category_id" required>
            <option value="">-- Chọn chuyên mục --</option>
            <?php foreach ($categories as $cat) { ?>
                <?php
                if ($article['category_id'] == $cat['category_id']) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
                ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php echo $selected; ?>>
                    <?php echo $cat['name']; ?>
                </option>
            <?php } ?>
        </select>
        
        
        <label>Tiêu đề <span style="color:red;">*</span></label>
        <input type="text" name="title" placeholder="Nhập tiêu đề bài viết..." 
               value="<?php echo $article['title']; ?>" required>
        <p style="font-size:12px; color:#999;">💡 Slug sẽ tự động tạo từ tiêu đề</p>
        
        
        <label>Tóm tắt</label>
        <textarea name="summary" rows="3" 
                  placeholder="Nhập tóm tắt ngắn gọn về bài viết..."><?php echo $article['summary']; ?></textarea>
        
        
        <label>Nội dung chi tiết</label>
        <textarea id="content" name="content" rows="8"
                  placeholder="Nhập nội dung đầy đủ của bài viết..."><?php echo $article['content']; ?></textarea>
        
        
        
        <label>Ảnh đại diện</label>
        <?php if (!empty($article['image_url'])) { ?>
            <div style="margin-bottom: 10px;">
                <img src="<?php echo $article['image_url']; ?>" 
                     alt="Current image" 
                     style="max-width: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                <p style="font-size:12px; color:#666;">Ảnh hiện tại</p>
            </div>
        <?php } ?>
        <input type="file" name="image" id="image_upload" accept="image/jpeg,image/png,image/gif,image/jpg" 
               onchange="document.getElementById('preview_img').src = window.URL.createObjectURL(this.files[0]); document.getElementById('preview_img').style.display = 'block';">
        <p style="font-size:12px; color:#999;">Chọn ảnh mới để thay thế (JPG, PNG, GIF - tối đa 5MB)</p>
        
        
        <img id="preview_img" src="" alt="" style="display:none; max-width: 300px; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">
        
        
        <label>
            <?php
            if ($article['is_featured']) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            ?>
            <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $checked; ?>>
            Đánh dấu là tin nổi bật
        </label>
        
        
        <button type="submit" class="btn btn-success">✓ Cập Nhật Bài Viết</button>
        <a href="?page=articles" class="btn">← Quay lại danh sách</a>
    </form>
</div>


<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>

var contentTextarea = document.querySelector('#content');
var quillDiv = document.createElement('div');
quillDiv.id = 'quill-editor';
quillDiv.innerHTML = contentTextarea.value;
contentTextarea.style.display = 'none';
contentTextarea.parentNode.insertBefore(quillDiv, contentTextarea.nextSibling);

// Khởi tạo Quill Editor
var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'align': '' }, { 'align': 'center' }, { 'align': 'right' }, { 'align': 'justify' }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            ['link', 'image'],
            [{ 'color': [] }, { 'background': [] }],
            ['clean']
        ]
    },
    placeholder: 'Nhập nội dung bài viết...'
});

// Upload ảnh lên server 
quill.getModule('toolbar').addHandler('image', function() {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();
    
    input.onchange = function() {
        var file = input.files[0];
        if (file) {
            var formData = new FormData();
            formData.append('image', file);
            
            fetch('upload_quill_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.url) {
                    var range = quill.getSelection();
                    quill.insertEmbed(range.index, 'image', result.url);
                } else {
                    alert('Lỗi upload: ' + (result.error || 'Không rõ'));
                }
            })
            .catch(error => {
                alert('Lỗi upload ảnh!');
                console.error(error);
            });
        }
    };
});

// Đồng bộ nội dung với textarea khi submit
var form = contentTextarea.closest('form');
form.onsubmit = function() {
    contentTextarea.value = quill.root.innerHTML;
};
</script>

<style>
#quill-editor {
    min-height: 450px;
    background: white;
}
.ql-toolbar {
    background: #f5f5f5;
    border: 1px solid #ddd !important;
    border-radius: 4px 4px 0 0;
}
.ql-container {
    border: 1px solid #ddd !important;
    border-radius: 0 0 4px 4px;
    font-size: 14px;
}

.ql-editor ol {
    padding-left: 1.5em;
}
.ql-editor ul {
    padding-left: 1.5em;
}

.ql-editor {
    font-family: 'Times New Roman', Times, serif;
}
</style>

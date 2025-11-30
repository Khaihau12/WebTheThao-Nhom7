<?php


// Kiểm tra đăng nhập
require_once 'check_login.php';

require_once 'dbadmin.php';

// Khởi tạo database
$db = new dbadmin();

// Biến lưu thông báo
$message = '';
$message_type = '';

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_url = '';
    
    // Xử lý upload ảnh
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
            } else {
                $message = 'Lỗi khi upload ảnh!';
                $message_type = 'error';
            }
        }
    }
    
    // Nếu không có lỗi upload ảnh, tiếp tục thêm bài viết
    if ($message_type !== 'error') {
        // Lấy dữ liệu từ form
        if (isset($_POST['category_id'])) {
            $category_id = $_POST['category_id'];
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
        
        if (!empty($_POST['author_id'])) {
            $author_id = (int)$_POST['author_id'];
        } else {
            $author_id = null;
        }
        
        if (isset($_POST['is_featured'])) {
            $is_featured = 1;
        } else {
            $is_featured = 0;
        }
        
        $data = [
            'category_id' => $category_id,
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'image_url' => $image_url,
            'author_id' => $author_id,
            'is_featured' => $is_featured
        ];
        
        // Kiểm tra dữ liệu
        if (empty($data['category_id'])) {
            $message = 'Vui lòng chọn chuyên mục!';
            $message_type = 'error';
        } elseif (empty($data['title'])) {
            $message = 'Vui lòng nhập tiêu đề!';
            $message_type = 'error';
        } else {
            // Thêm bài viết
            $result = $db->themBaiViet($data);
            
            if ($result) {
                $message = ' Thêm bài viết thành công!<br>ID bài viết: <strong>' . $result . '</strong><br>Có thể tiếp tục thêm bài viết mới bên dưới.';
                $message_type = 'success';
                
                // Reset form để thêm bài mới
                $_POST = [];
            } else {
                $message = 'Lỗi khi thêm bài viết! Có thể slug đã tồn tại.';
                $message_type = 'error';
            }
        }
    }
}

// Lấy danh sách chuyên mục
$categories = $db->layDanhSachChuyenMuc();
?>

<div class="content-header">
    <h2> Thêm Bài Viết Mới</h2>
</div>

<div class="content-body">    <?php if ($message) { ?>
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
                if (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) {
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
               value="<?php if(isset($_POST['title'])) { echo $_POST['title']; } ?>" required>
        <p style="font-size:12px; color:#999;">Slug sẽ tự động tạo từ tiêu đề</p>
        
        
        <label>Tóm tắt</label>
        <textarea name="summary" rows="3" 
                  placeholder="Nhập tóm tắt ngắn gọn về bài viết..."><?php if(isset($_POST['summary'])) { echo $_POST['summary']; } ?></textarea>
        
        
        <label>Nội dung chi tiết</label>
        <textarea id="content" name="content" rows="8"
                  placeholder="Nhập nội dung đầy đủ của bài viết..."><?php if(isset($_POST['content'])) { echo $_POST['content']; } ?></textarea>

        
        
        <label>Ảnh đại diện</label>
        <input type="file" name="image" id="image_upload" accept="image/jpeg,image/png,image/gif,image/jpg" 
               onchange="document.getElementById('preview_img').src = window.URL.createObjectURL(this.files[0]); document.getElementById('preview_img').style.display = 'block';">
        <p style="font-size:12px; color:#999;">Chọn ảnh JPG, PNG, GIF (tối đa 5MB)</p>
        
        
        <img id="preview_img" src="" alt="" style="display:none; max-width: 300px; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">
        
        
        <label>
            <?php
            if (isset($_POST['is_featured'])) {
                $checked = 'checked';
            } else {
                $checked = '';
            }
            ?>
            <input type="checkbox" id="is_featured" name="is_featured" value="1" <?php echo $checked; ?>>
            Đánh dấu là tin nổi bật
        </label>
        
        
        <button type="submit" class="btn btn-success">✓ Thêm Bài Viết</button>
        <button type="reset" class="btn">↻ Làm mới</button>
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

// Xử lý upload ảnh
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

// Khi submit form, cập nhật nội dung từ Quill vào 
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
<style>
.ck-editor__editable { min-height: 450px; }

.ck-editor__editable ol {
    list-style-type: decimal;
    padding-left: 40px;
}
.ck-editor__editable ol li {
    list-style-type: decimal;
}
.ck-editor__editable ul {
    list-style-type: disc;
    padding-left: 40px;
}

.ck-editor__editable .text-left { text-align: left; }
.ck-editor__editable .text-center { text-align: center; }
.ck-editor__editable .text-right { text-align: right; }
.ck-editor__editable .text-justify { text-align: justify; }
</style>

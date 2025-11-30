<?php
class dbadmin {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "webthethao";
    protected $conn;
    
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        
        if ($this->conn->connect_error) {
            die("Kết nối thất bại: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
    }
    
    
    public function getConnection() {
        return $this->conn;
    }
    

    // CHỨC NĂNG: XÁC THỰC VÀ QUẢN LÝ PHIÊN ĐĂNG NHẬP

    public function login($username, $password) {
        // Mã hóa password bằng MD5
        $password_md5 = md5($password);
        
        // Lấy tất cả tài khoản từ database
        $sql = "SELECT * FROM users";
        $result = $this->conn->query($sql);
        
        // Duyệt qua từng tài khoản
        while ($row = $result->fetch_assoc()) {
            // Nếu tìm thấy username và password mã hóa khớp VÀ role phải là admin hoặc editor
            if ($row['username'] == $username && $row['password'] == $password_md5) {
                if ($row['role'] == 'admin' || $row['role'] == 'editor') {
                    // Lưu thông tin vào session
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $row['user_id'];
                    $_SESSION['admin_username'] = $row['username'];
                    $_SESSION['admin_display_name'] = $row['display_name'];
                    $_SESSION['admin_role'] = $row['role'];
                    
                    return true; // Đăng nhập thành công
                }
            }
        }
        
        return false; // Không tìm thấy tài khoản khớp hoặc không phải admin/editor
    }
    

    public function logout() {
        session_destroy();
        return true;
    }


    public function isLoggedIn() {
        // Kiểm tra có session admin_logged_in và role phải là admin hoặc editor
        if (isset($_SESSION['admin_logged_in']) && isset($_SESSION['admin_role'])) {
            if ($_SESSION['admin_role'] == 'admin' || $_SESSION['admin_role'] == 'editor') {
                return true;
            }
        }
        return false;
    }
    

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'display_name' => $_SESSION['admin_display_name'],
                'role' => $_SESSION['admin_role']
            ];
        }
        return null;
    }
    

    // CHỨC NĂNG: QUẢN LÝ CHUYÊN MỤC (CATEGORIES)

    public function layDanhSachChuyenMuc() {
        $sql = "SELECT * FROM categories ORDER BY category_id DESC";
        $result = $this->conn->query($sql);
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        
        return $categories;
    }
    

    public function themChuyenMuc($name, $parent_id) {
        // Tự động tạo slug từ name
        $slug = $this->createSlug($name);
        
        // Kiểm tra slug đã tồn tại chưa
        $check_sql = "SELECT category_id FROM categories WHERE slug = '$slug'";
        $check_result = $this->conn->query($check_sql);
        if ($check_result->num_rows > 0) {
            return "Chuyên mục này đã được tạo rồi!";
        }
        
        // Nếu parent_id rỗng thì set null
        $parent_id_value = empty($parent_id) ? "NULL" : $parent_id;
        
        // Thêm vào database
        $sql = "INSERT INTO categories (name, slug, parent_id) VALUES ('$name', '$slug', $parent_id_value)";
        $result = $this->conn->query($sql);
        
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    

    public function layChuyenMucTheoSlug($category_slug) {
        $sql = "SELECT * FROM categories WHERE slug = '$category_slug'";
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    

    public function suaChuyenMuc($old_slug, $name, $parent_id) {
        // Tự động tạo slug mới từ name mới
        $new_slug = $this->createSlug($name);
        
        // Kiểm tra nếu slug mới khác slug cũ thì kiểm tra trùng
        if ($new_slug !== $old_slug) {
            $check_sql = "SELECT category_id FROM categories WHERE slug = '$new_slug'";
            $check_result = $this->conn->query($check_sql);
            if ($check_result->num_rows > 0) {
                return "Chuyên mục này đã được tạo rồi!";
            }
        }
        
        // Nếu parent_id rỗng thì set null
        $parent_id_value = empty($parent_id) ? "NULL" : $parent_id;
        
        // Cập nhật vào database
        $sql = "UPDATE categories SET name = '$name', slug = '$new_slug', parent_id = $parent_id_value WHERE slug = '$old_slug'";
        $result = $this->conn->query($sql);
        
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    

    public function deleteBySlug($category_slug) {
        $sql = "DELETE FROM categories WHERE slug = '$category_slug'";
        $result = $this->conn->query($sql);
        
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    

    public function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }

    // CHỨC NĂNG: QUẢN LÝ BÀI VIẾT (ARTICLES)

    public function layBaiVietMoiNhat($limit = 10) {
        $sql = "SELECT a.article_id, a.title, a.created_at, c.name as category_name 
                FROM articles a 
                LEFT JOIN categories c ON a.category_id = c.category_id 
                ORDER BY a.created_at DESC 
                LIMIT $limit";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return false;
        }
        
        $danhSach = array();
        
        while ($row = $result->fetch_assoc()) {
            $danhSach[] = $row;
        }
        
        return $danhSach;
    }
    
    // CHỨC NĂNG : PHƯƠNG THỨC Hiễn thị tin (dùng trong demo_danh_sach_loai_tin.php và ứng dụng ở các trang khác )

    public function hienThiDanhSachLoaiTin() {
        $sql = "SELECT category_id, name, slug, parent_id FROM categories ORDER BY category_id ASC";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return false;
        }
        
        $danhSach = array();
        
        while ($row = $result->fetch_assoc()) {
            $danhSach[] = $row;
        }
        
        return $danhSach;
    }
    

    public function hienThiDanhSachLoaiTinTheoParent($parent_id = null) {
        if ($parent_id === null) {
            // Lấy các loại tin gốc (không có parent)
            $sql = "SELECT category_id, name, slug, parent_id FROM categories WHERE parent_id IS NULL ORDER BY category_id ASC";
        } else {
            // Lấy các loại tin con theo parent_id
            $sql = "SELECT category_id, name, slug, parent_id FROM categories WHERE parent_id = $parent_id ORDER BY category_id ASC";
        }
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return false;
        }
        
        $danhSach = array();
        
        while ($row = $result->fetch_assoc()) {
            $danhSach[] = $row;
        }
        
        return $danhSach;
    }
    

    public function layThongTinLoaiTin($category_slug) {
        $sql = "SELECT category_id, name, slug, parent_id FROM categories WHERE slug = '$category_slug'";
        
        $result = $this->conn->query($sql);
        
        if (!$result) {
            return false;
        }
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    

    public function demSoLuongLoaiTin() {
        $sql = "SELECT COUNT(*) as total FROM categories";
        
        $result = $this->conn->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'];
        }
        
        return 0;
    }
    

    // CHỨC NĂNG: QUẢN LÝ BÀI VIẾT (ARTICLES)

    public function themBaiViet($data) {
        // kiểm tra dữ liệu đầu vào
        if (empty($data['category_id']) || empty($data['title'])) {
            return false;
        }
        
        // Chuẩn bị dữ liệu
        $category_id = (int)$data['category_id'];
        $title = $data['title'];
        
        // Tự động tạo slug từ title
        $slug = $this->createSlug($title);
        
        if (isset($data['summary'])) {
            $summary = $data['summary'];
        } else {
            $summary = '';
        }
        
        if (isset($data['content'])) {
            $content = $data['content'];
        } else {
            $content = '';
        }
        
        if (isset($data['image_url'])) {
            $image_url = $data['image_url'];
        } else {
            $image_url = '';
        }
        
        if (isset($data['author_id'])) {
            $author_id = (int)$data['author_id'];
        } else {
            $author_id = 0;
        }
        
        if (isset($data['is_featured'])) {
            $is_featured = (int)$data['is_featured'];
        } else {
            $is_featured = 0;
        }
        
        // Thêm vào database
        $sql = "INSERT INTO articles (category_id, title, slug, summary, content, image_url, author_id, is_featured, created_at) 
                VALUES ($category_id, '$title', '$slug', '$summary', '$content', '$image_url', $author_id, $is_featured, NOW())";
        
        // Thực thi
        if ($this->conn->query($sql)) {
            return $this->conn->insert_id;
        } else {
            return false;
        }
    }
    

    public function layBaiVietTheoSlug($article_slug) {
        $sql = "SELECT * FROM articles WHERE slug = '$article_slug'";
        $result = $this->conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    public function suaBaiViet($old_slug, $data) {
        // kiểm tra dữ liệu
        if (empty($data['category_id']) || empty($data['title'])) {
            return false;
        }
        
        $category_id = (int)$data['category_id'];
        $title = $data['title'];
        
        // Tự động tạo slug mới từ title
        $new_slug = $this->createSlug($title);
        
        $summary = isset($data['summary']) ? $data['summary'] : '';
        $content = isset($data['content']) ? $data['content'] : '';
        $image_url = isset($data['image_url']) ? $data['image_url'] : '';
        $is_featured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;
        
        // Cập nhật bài viết
        $sql = "UPDATE articles SET 
                category_id = $category_id, 
                title = '$title', 
                slug = '$new_slug', 
                summary = '$summary', 
                content = '$content', 
                image_url = '$image_url', 
                is_featured = $is_featured 
                WHERE slug = '$old_slug'";
        
        $result = $this->conn->query($sql);
        
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
    

    public function xoaBaiVietTheoSlug($article_slug) {
        $sql = "DELETE FROM articles WHERE slug = '$article_slug'";
        $result = $this->conn->query($sql);
        
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    // ĐÓNG KẾT NỐI


    public function dongKetNoi() {
        if ($this->conn && !$this->conn->connect_errno) {
            $this->conn->close();
            $this->conn = null; // Đặt về null sau khi đóng để tránh đóng lại
        }
    }
    

    public function __destruct() {
        $this->dongKetNoi();
    }
    
}
?>

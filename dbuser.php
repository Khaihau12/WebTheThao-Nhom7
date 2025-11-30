<?php
class dbuser {
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
    
 
    // CHỨC NĂNG: XEM CHUYÊN MỤC (CATEGORIES)

    public function layTatCaChuyenMuc() {
        $sql = "SELECT category_id, name, slug, parent_id FROM categories ORDER BY name ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

    public function layCategoryGoc($limit = 4) {
        $sql = "SELECT category_id, name, slug, parent_id 
                FROM categories 
                WHERE parent_id IS NULL OR parent_id = 0
                ORDER BY category_id ASC
                LIMIT $limit";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

    public function layCategoryTheoSlug($category_slug) {
        $sql = "SELECT category_id, name, slug, parent_id FROM categories WHERE slug = '$category_slug'";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    

    // CHỨC NĂNG: XEM & ĐỌC BÀI VIẾT (ARTICLES)

    public function layBaiVietMoiNhat($limit = 10) {
        $sql = "SELECT a.article_id, a.category_id, a.title, a.slug, 
                a.summary, a.content, a.image_url, a.author_id, 
                a.is_featured, a.created_at, 
                c.name as category_name, c.slug as category_slug 
                FROM articles AS a
                JOIN categories AS c ON a.category_id = c.category_id 
                ORDER BY a.created_at DESC 
                LIMIT $limit";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

    public function layBaiVietNoiBat($limit = 1) {
        $sql = "SELECT a.article_id, a.category_id, a.title, a.slug, 
                a.summary, a.content, a.image_url, a.author_id, 
                a.is_featured, a.created_at, 
                c.name as category_name, c.slug as category_slug 
                FROM articles AS a
                JOIN categories AS c ON a.category_id = c.category_id 
                WHERE a.is_featured = 1
                ORDER BY a.created_at DESC 
                LIMIT $limit";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    

    public function layBaiVietTheoCategory($category_slug, $limit = 10) {
        $sql = "SELECT a.article_id, a.category_id, a.title, a.slug, 
                a.summary, a.content, a.image_url, a.author_id, 
                a.is_featured, a.created_at, 
                c.name as category_name, c.slug as category_slug 
                FROM articles AS a
                JOIN categories AS c ON a.category_id = c.category_id 
                WHERE c.slug = '$category_slug'
                ORDER BY a.created_at DESC 
                LIMIT $limit";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function layChiTietBaiVietTheoSlug($article_slug) {
        $sql = "SELECT a.article_id, a.category_id, a.title, a.slug, 
                a.summary, a.content, a.image_url, a.author_id, 
                a.is_featured, a.created_at, 
                c.name as category_name, c.slug as category_slug
                FROM articles AS a
                JOIN categories AS c ON a.category_id = c.category_id 
                WHERE a.slug = '$article_slug'";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }


    // CHỨC NĂNG: XÁC THỰC VÀ QUẢN LÝ PHIÊN ĐĂNG NHẬP

    public function dangKy($username, $password, $email, $display_name = '') {
        // 1. Kiểm tra các trường bắt buộc không được để trống
        if (empty($username) || empty($password) || empty($email)) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!'];
        }
        
        // 2. Kiểm tra username đã tồn tại chưa
        $sql = "SELECT username FROM users WHERE username = '$username'";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại!'];
        }
        
        // 3. Kiểm tra email đã tồn tại chưa
        $sql = "SELECT email FROM users WHERE email = '$email'";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email đã được sử dụng!'];
        }
        
        // 4. Nếu không có display_name thì dùng username
        if (empty($display_name)) {
            $display_name = $username;
        }
        
        // 5. Mã hóa password bằng MD5 và lưu vào database
        $password_md5 = md5($password);
        
        $sql = "INSERT INTO users (username, password, email, display_name, role, created_at) 
                VALUES ('$username', '$password_md5', '$email', '$display_name', 'user', NOW())";
        
        if ($this->conn->query($sql)) {
            return ['success' => true, 'message' => 'Đăng ký thành công!'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi tạo tài khoản!'];
        }
    }
    

    public function login($username, $password) {
        // Mã hóa password bằng MD5
        $password_md5 = md5($password);
        
        // Lấy tất cả tài khoản từ database
        $sql = "SELECT * FROM users";
        $result = $this->conn->query($sql);
        
        // Duyệt qua từng tài khoản
        while ($row = $result->fetch_assoc()) {
            // Nếu tìm thấy username và password khớp
            if ($row['username'] == $username && $row['password'] == $password_md5) {
                // Lưu thông tin vào session
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['display_name'] = $row['display_name'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['email'] = $row['email'];
                
                return true;
            }
        }
        
        return false;
    }
    

    public function logout() {
        session_destroy();
        return true;
    }


    // Kiểm tra đã đăng nhập hay chưa

    public function isLoggedIn() {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            return true;
        }
        return false;
    }
    

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'display_name' => $_SESSION['display_name'],
                'role' => $_SESSION['role'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }


    public function doiMatKhau($user_id, $mat_khau_cu, $mat_khau_moi) {
        // 1. Lấy password hiện tại
        $sql = "SELECT password FROM users WHERE user_id = $user_id";
        $result = $this->conn->query($sql);
        $user = $result->fetch_assoc();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy người dùng!'];
        }
        
        // 2. So sánh mật khẩu cũ (mã hóa MD5)
        $mat_khau_cu_md5 = md5($mat_khau_cu);
        if ($user['password'] !== $mat_khau_cu_md5) {
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!'];
        }
        
        // 3. Cập nhật mật khẩu mới (mã hóa MD5)
        $mat_khau_moi_md5 = md5($mat_khau_moi);
        $sql = "UPDATE users SET password = '$mat_khau_moi_md5' WHERE user_id = $user_id";
        
        if ($this->conn->query($sql)) {
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công!'];
        } else {
            return ['success' => false, 'message' => 'Lỗi khi đổi mật khẩu!'];
        }
    }

 
    // CHỨC NĂNG: TƯƠNG TÁC BÀI VIẾT (Like, Save, View)

    public function daThichBaiViet($user_id, $article_id) {
        $sql = "SELECT * FROM article_likes WHERE user_id = $user_id AND article_id = $article_id";
        $result = $this->conn->query($sql);
        return $result->num_rows > 0;
    }


    public function daLuuBaiViet($user_id, $article_id) {
        $sql = "SELECT * FROM article_saves WHERE user_id = $user_id AND article_id = $article_id";
        $result = $this->conn->query($sql);
        return $result->num_rows > 0;
    }


    public function toggleThichBaiViet($user_id, $article_id) {
        if ($this->daThichBaiViet($user_id, $article_id)) {
            // Đã thích -> Bỏ thích
            $sql = "DELETE FROM article_likes WHERE user_id = $user_id AND article_id = $article_id";
            $this->conn->query($sql);
            return false;
        } else {
            // Chưa thích -> Thêm thích
            $sql = "INSERT INTO article_likes (user_id, article_id) VALUES ($user_id, $article_id)";
            $this->conn->query($sql);
            return true;
        }
    }


    public function toggleLuuBaiViet($user_id, $article_id) {
        if ($this->daLuuBaiViet($user_id, $article_id)) {
            // Đã lưu -> Bỏ lưu
            $sql = "DELETE FROM article_saves WHERE user_id = $user_id AND article_id = $article_id";
            $this->conn->query($sql);
            return false;
        } else {
            // Chưa lưu -> Thêm lưu
            $sql = "INSERT INTO article_saves (user_id, article_id) VALUES ($user_id, $article_id)";
            $this->conn->query($sql);
            return true;
        }
    }


    public function demLuotThich($article_id) {
        $sql = "SELECT COUNT(*) as total FROM article_likes WHERE article_id = $article_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function demLuotLuu($article_id) {
        $sql = "SELECT COUNT(*) as total FROM article_saves WHERE article_id = $article_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function luuLuotXem($user_id, $article_id) {
        // Kiểm tra đã xem chưa
        $sql = "SELECT * FROM article_views WHERE user_id = $user_id AND article_id = $article_id";
        $result = $this->conn->query($sql);
        
        if ($result->num_rows == 0) {
            // Chưa xem -> Thêm mới
            $sql = "INSERT INTO article_views (user_id, article_id, viewed_at) VALUES ($user_id, $article_id, NOW())";
            $this->conn->query($sql);
            return true;
        } else {
            // Đã xem -> Cập nhật thời gian
            $sql = "UPDATE article_views SET viewed_at = NOW() WHERE user_id = $user_id AND article_id = $article_id";
            $this->conn->query($sql);
            return false;
        }
    }
    

    public function demLuotXemBaiViet($article_id) {
        $sql = "SELECT COUNT(*) as total FROM article_views WHERE article_id = $article_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    // CHỨC NĂNG: BÌNH LUẬN BÀI VIẾT (COMMENTS)

    public function themBinhLuan($article_id, $user_id, $content) {
        $sql = "INSERT INTO comments (article_id, user_id, content, created_at) 
                VALUES ($article_id, $user_id, '$content', NOW())";
        return $this->conn->query($sql);
    }


    //Lấy danh sách bình luận của bài viết

    public function layBinhLuan($article_id) {
        $sql = "SELECT c.*, u.username, u.display_name 
                FROM comments AS c
                JOIN users AS u ON c.user_id = u.user_id
                WHERE c.article_id = $article_id
                ORDER BY c.created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    public function demBinhLuan($article_id) {
        $sql = "SELECT COUNT(*) as total FROM comments WHERE article_id = $article_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function xoaBinhLuan($comment_id, $user_id) {
        $sql = "DELETE FROM comments WHERE comment_id = $comment_id AND user_id = $user_id";
        return $this->conn->query($sql);
    }


    public function suaBinhLuan($comment_id, $user_id, $content) {
        $sql = "UPDATE comments SET content = '$content' WHERE comment_id = $comment_id AND user_id = $user_id";
        return $this->conn->query($sql);
    }


    public function layMotBinhLuan($comment_id) {
        $sql = "SELECT * FROM comments WHERE comment_id = $comment_id";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }


    // CHỨC NĂNG: THỐNG KÊ USER (Dashboard cá nhân)

    public function demBaiDaDoc($user_id) {
        $sql = "SELECT COUNT(*) as total FROM article_views WHERE user_id = $user_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    

    public function demBaiYeuThich($user_id) {
        $sql = "SELECT COUNT(*) as total FROM article_likes WHERE user_id = $user_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    

    public function demBaiDaLuu($user_id) {
        $sql = "SELECT COUNT(*) as total FROM article_saves WHERE user_id = $user_id";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    

    public function layBaiDaDoc($user_id, $limit = 10) {
        $sql = "SELECT a.*, c.name as category_name, v.viewed_at 
                FROM article_views AS v
                JOIN articles AS a ON v.article_id = a.article_id
                LEFT JOIN categories AS c ON a.category_id = c.category_id
                WHERE v.user_id = $user_id
                ORDER BY v.viewed_at DESC
                LIMIT $limit";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

    public function layBaiYeuThich($user_id, $limit = 10) {
        $sql = "SELECT a.*, c.name as category_name, l.created_at as liked_at
                FROM article_likes AS l
                JOIN articles AS a ON l.article_id = a.article_id
                LEFT JOIN categories AS c ON a.category_id = c.category_id
                WHERE l.user_id = $user_id
                ORDER BY l.created_at DESC
                LIMIT $limit";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

}
?>

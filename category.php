<?php
session_start();
require_once 'dbuser.php';
$db = new dbuser();


if (isset($_GET['slug'])) {
    $category_slug = $_GET['slug'];
} else {
    $category_slug = '';
}


$category = $db->layCategoryTheoSlug($category_slug);


if (!$category) {
    header('Location: index.php');
    exit;
}


$tinTuc = $db->layBaiVietTheoCategory($category_slug, 20);


$isLoggedIn = $db->isLoggedIn();
if ($isLoggedIn) {
    $currentUser = $db->getCurrentUser();
} else {
    $currentUser = null;
}


$danhMuc = $db->layTatCaChuyenMuc();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $category['name']; ?> - Tin Tức 24H</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                            <?php if($isLoggedIn) { ?>
                                <a href="indexuser.php"><i class="fa fa-user"></i> <?php echo $currentUser['display_name']; ?></a>
                            <?php } else { ?>
                                <a href="loginuser.php"><i class="fa fa-user"></i> Đăng nhập</a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                
                <nav class="main-navigation">
                    <ul>
                        <li><a href="index.php"><i class="fa fa-home"></i> Trang Chủ</a></li>
                        <?php foreach($danhMuc as $dm) { ?>
                        <?php
                        if ($dm['slug'] == $category_slug) {
                            $class_active = 'class="active"';
                        } else {
                            $class_active = '';
                        }
                        ?>
                        <li><a href="category.php?slug=<?php echo $dm['slug']; ?>" <?php echo $class_active; ?>><?php echo $dm['name']; ?></a></li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main>
    <div class="container" style="padding: 30px 0;">
        <h1 class="page-title">
             <?php echo strtoupper($category['name']); ?>
        </h1>

        <div class="category-article-list">
            <?php if (count($tinTuc) > 0) { ?>
                <?php foreach($tinTuc as $tin) { ?>
                
                <article class="list-news-item d-flex">
                    <a href="article.php?slug=<?php echo $tin['slug']; ?>" class="list-news-img">
                        <?php if($tin['image_url']) { ?>
                            <img src="<?php echo $tin['image_url']; ?>" alt="<?php echo $tin['title']; ?>" class="img-fluid">
                        <?php } else { ?>
                            <img src="https://via.placeholder.com/220x140/3498db/ffffff?text=No+Image" alt="No image" class="img-fluid">
                        <?php } ?>
                    </a>
                    <div class="list-news-info">
                        <h3 class="list-news-title">
                            <a href="article.php?slug=<?php echo $tin['slug']; ?>" class="fw-bold color-main hover-color-24h">
                                <?php echo $tin['title']; ?>
                            </a>
                        </h3>
                        <div class="meta" style="font-size: 13px; color: #999; margin: 5px 0;">
                            <i class="fa fa-calendar"></i> <?php echo date('d/m/Y', strtotime($tin['created_at'])); ?>
                        </div>
                        <p style="font-size: 15px; color: #555; margin-top: 8px;">
                            <?php echo substr($tin['summary'], 0, 200); ?>...
                        </p>
                    </div>
                </article>
                <?php } ?>
            <?php } else { ?>
                <div style="text-align: center; padding: 50px 0; color: #999;">
                    <i class="fa fa-inbox" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p style="font-size: 18px;">Chưa có bài viết nào trong chuyên mục này</p>
                </div>
            <?php } ?>
        </div>
    </div>
    </main>

    
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

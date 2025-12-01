<?php
session_start();
require_once 'dbuser.php';
$db = new dbuser();


$tinNoiBat = $db->layBaiVietNoiBat(1); // Lấy 1 bài nổi bật nhất
$tinMoiNhat = $db->layBaiVietMoiNhat(10); // Lấy 10 tin mới nhất
$tinTheThao = $db->layBaiVietTheoCategory('the-thao', 4); // Lấy 4 tin thể thao
$tinSidebar = $db->layBaiVietMoiNhat(5); // Lấy 5 tin cho sidebar
$danhMuc = $db->layTatCaChuyenMuc(); // Lấy tất cả danh mục
$categoryGoc = $db->layCategoryGoc(4); // Lấy 4 category cha để hiển thị menu

// Kiểm tra đăng nhập
$isLoggedIn = $db->isLoggedIn();
if ($isLoggedIn) {
    $currentUser = $db->getCurrentUser();
} else {
    $currentUser = null;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức bóng đá, thể thao, giải trí | Đọc tin tức 24h mới nhất</title>
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
                        <li><a href="index.php" class="active"><i class="fa fa-home"></i> Trang Chủ</a></li>
                        <?php foreach($danhMuc as $dm) { ?>
                        <li><a href="category.php?slug=<?php echo $dm['slug']; ?>"><?php echo $dm['name']; ?></a></li>
                        <?php } ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>


    
    <main>
    <div class="container main-content-24h" style="padding-top: 20px;">
        <div class="row d-flex">
            <div class="col-8 main-column main-column-pad">
                
                
                <section class="hightl-24h-block d-flex">
                    
                    <?php if($tinNoiBat) { ?>
                    <div class="hightl-24h-big hightl-24h-big--col">
                        <a href="article.php?slug=<?php echo $tinNoiBat['slug']; ?>">
                            <img src="<?php echo $tinNoiBat['image_url']; ?>" alt="<?php echo $tinNoiBat['title']; ?>" class="img-fluid hightl-img-big">
                        </a>
                        <h2 class="hightl-title-big">
                            <a href="article.php?slug=<?php echo $tinNoiBat['slug']; ?>" class="fw-bold color-main hover-color-24h">
                                <?php echo $tinNoiBat['title']; ?>
                            </a>
                        </h2>
                        <p class="hightl-summary"><?php echo substr($tinNoiBat['summary'], 0, 150); ?>...</p>
                    </div>
                    <?php } ?>
                    
                    
                    <div class="hightl-24h-list" style="flex: 1; padding-left: 20px;">
                        <?php 
                        $tinNho = array_slice($tinMoiNhat, 1, 3); // Lấy 3 tin tiếp theo
                        foreach($tinNho as $tin) { 
                        ?>
                        <article class="hightl-24h-items" style="margin-bottom: 15px;">
                            <span class="hightl-24h-items-cate d-block mar-b-5">
                                <a href="category.php?slug=<?php echo $tin['category_slug']; ?>" class="color-24h">
                                    <?php echo $tin['category_name']; ?>
                                </a>
                            </span>
                            <h3>
                                <a href="article.php?slug=<?php echo $tin['slug']; ?>" class="d-block fw-medium color-main hover-color-24h">
                                    <?php echo $tin['title']; ?>
                                </a>
                            </h3>
                        </article>
                        <?php } ?>
                    </div>
                </section>

                
                

                <hr style="margin: 30px 0; border: 0; border-top: 5px solid #eee;">

                
                <section class="cate-news-24h-r mar-t-40">
                    <div class="box-t d-flex align-items-center mar-b-15">
                        <h2 class="fw-bold text-uppercase color-green-custom">TIN MỚI NHẤT</h2>
                    </div>
                    
                    
                    <?php foreach($tinMoiNhat as $tin) { ?>
                    <div class="article-card">
                        <img src="<?php echo $tin['image_url']; ?>" alt="<?php echo $tin['title']; ?>">
                        <div class="article-content">
                            <h3><?php echo $tin['title']; ?></h3>
                            <div class="meta">
                                <span> <?php echo date('d/m/Y', strtotime($tin['created_at'])); ?></span> |
                                <span> <?php echo $tin['category_name']; ?></span>
                            </div>
                            <p><?php echo substr($tin['summary'], 0, 150); ?>...</p>
                            <a href="article.php?slug=<?php echo $tin['slug']; ?>" class="read-more">Đọc tiếp →</a>
                        </div>
                    </div>
                    <?php } ?>
                </section>

            </div>

            <aside class="sidebar-column col-4">
                <div class="latest-news-block">
                    <header class="latest-news-tit">
                        <h2 class="fw-bold text-uppercase color-green-custom"> TIN MỚI NHẤT</h2>
                    </header>
                    <div class="latest-news-list">
                        <?php foreach($tinSidebar as $tin) { ?>
                        <div class="sidebar-article">
                            <h4 style="font-size: 11px; color: #888; text-transform: uppercase;">
                                <?php echo $tin['category_name']; ?>
                            </h4>
                            <p>
                                <a href="article.php?slug=<?php echo $tin['slug']; ?>" class="color-main hover-color-24h">
                                    <?php echo $tin['title']; ?>
                                </a>
                            </p>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </aside>
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

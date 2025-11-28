<?php
session_start() ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Nền tảng mua bán các loại đồ cũ trực tuyến</title>
    <link rel="stylesheet" href="/sm-demo/src/css/style.css">
</head>
    
<body>
    <div class="browser-bar">
        <div class="browser-nav">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <span>SM - Nền tảng mua bán các loại đồ cũ trực tuyến</span>
        <span style="margin-left: auto;">https://www.sm.vn</span>
    </div>

    <!-- Promotional banner -->
    <div class="promo-banner">
        <p>Nền tảng mua đồ cũ vì một trái đất xanh hơn!</p>
        <p>Cam kết hoàn tiền 100% nếu sản phẩm không đúng mô tả!</p>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <a href="/sm-demo/src/index.php" class="logo">
                <div class="logo-icon"><img src="/sm-demo/src/img/logo.jpg" alt="logo"></div>
                
            </a>
            
            <div class="category-dropdown">
                <button class="category-btn">Tất cả danh mục</button>
                <div class="category-menu">
                    <a href="#">Sách</a>
                    <a href="#">Xe ô tô</a>
                    <a href="#">Làm đẹp</a>
                    <a href="#">Thời trang nữ</a>
                    <a href="#">Thời trang nam</a>
                    <a href="#">Đồ cho mẹ và bé</a>
                    <a href="#">Đồ chơi</a>
                    <a href="#">Đồ gia dụng</a>
                    <a href="#">Thiết bị điện tử</a>
                </div>
            </div>

            <div class="search-container">
                <input type="text" class="search-bar" placeholder=" Tìm kiếm">
                <span class="search-icon">&#x1F50E;&#xFE0E;</span>
            </div>

            <div class="user-actions">
                <a href="#" class="bell-icon"><img class="bell-icon-img" src="https://cdn-icons-png.flaticon.com/128/3602/3602145.png" alt="bell"></a>
                <span>|</span>
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'NguoiBan'): ?>

                    <ul class="menu">
                        <li><a href="#!"><?php echo $_SESSION['fullname']; ?></a>
                            <ul class="sub-menu">
                                <li><a href="/sm-demo/src/seller/add-product.php">Thêm sản phẩm</a></li>
                                <li><a href="">Tất cả sản phẩm</a></li>
                                <li><a href="">Đơn bán</a></li>
                                <li><a href="">Doanh thu</a></li>
                                <li><a href="">Hồ sơ shop</a></li>
                            </ul>   
                        </li>
                    </ul>

                    <a href="/sm-demo/src/seller/add-product.php" class = "btn-sell">Đăng bán</a>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <span><u><?php echo $_SESSION['fullname']; ?></u></span>
                    <span>|</span>
                    <a href="logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>    
                <a href="/sm-demo/src/login.php"><u>Đăng nhập</u></a>
                <span>|</span>
                <a href="/sm-demo/src/register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
                <span>|</span>
                <a href="#" class="cart-icon"><img class="cart-icon-img" src="https://cdn-icons-png.flaticon.com/128/1170/1170678.png" alt="cart"></a>
            </div>
        </div>

        <div class="nav-categories">
            <!-- create list category list -->
            <a href="list-product.php">Sách</a>
            <a href="#">Đồ cho nam</a>
            <a href="#">Đồ cho nữ</a>
            <a href="#">Đồ cho mẹ và bé</a>
            <a href="#">Đồ gia dụng</a>
            <a href="#">Đồ chơi</a>
        </div>
    </header>
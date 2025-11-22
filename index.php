<?php
session_start() ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Nền tảng mua bán các loại đồ cũ trực tuyến</title>
    
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <!-- Browser bar simulation -->
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
            <a href="index.php" class="logo">
                <div class="logo-icon"><img src="./img/logo.jpg" alt="logo"></div>
                
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span><u><?php echo $_SESSION['fullname']; ?></u></span>
                    <span>|</span>
                    <a href="logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>    
                <a href="/baitaplvn_sm/src/login.php"><u>Đăng nhập</u></a>
                <span>|</span>
                <a href="/baitaplvn_sm/src/register.php"><u>Đăng ký</u></a>
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

    <!-- Main content -->
    <main class="main-content">
        <!-- Featured Products Section -->
        <section>
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="#" class="view-all-link">xem tất cả</a>
            </div>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-image"></div>
                    <div class="product-info">
                        <div class="product-name">Quạt mini cầm tay</div>
                        <div class="product-price">29.999đ</div>
                        <div class="product-actions">
                            <button class="btn-add-cart">thêm vào</button>
                            <button class="btn-buy-now">mua ngay</button>
                        </div>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-image"></div>
                    <div class="product-info">
                        <div class="product-name">Sách lịch sử thế giới</div>
                        <div class="product-price">39.999đ</div>
                        <div class="product-actions">
                            <button class="btn-add-cart">thêm vào</button>
                            <button class="btn-buy-now">mua ngay</button>
                        </div>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-image"></div>
                    <div class="product-info">
                        <div class="product-name">Áo phông trẻ em</div>
                        <div class="product-price">37.999đ</div>
                        <div class="product-actions">
                            <button class="btn-add-cart">thêm vào</button>
                            <button class="btn-buy-now">mua ngay</button>
                        </div>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-image"></div>
                    <div class="product-info">
                        <div class="product-name">Vật lý đại cương</div>
                        <div class="product-price">77.999đ</div>
                        <div class="product-actions">
                            <button class="btn-add-cart">thêm vào</button>
                            <button class="btn-buy-now">mua ngay</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Store Reviews Section -->
        <section>
            <div class="section-header">
                <h2 class="section-title">Đánh giá các cửa hàng gần đây</h2>
            </div>
            <div class="stores-grid">
                <div class="store-card">
                    <div class="store-image"></div>
                </div>
                <div class="store-card">
                    <div class="store-image"></div>
                </div>
                <div class="store-card">
                    <div class="store-image"></div>
                </div>
                <div class="store-card">
                    <div class="store-image"></div>
                </div>
                <div class="store-card">
                    <div class="store-image"></div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <div class="logo" style="margin-bottom: 15px;">
                    <div class="logo-icon"><img src="./img/logo.jpg" alt="logo"></div>
                    <span style="color: white;">SM</span>
                </div>
                <p>SM - Nền tảng mua và bán đồ cũ uy tín và có đảm bảo tại Việt Nam.</p>
            </div>
            <div class="footer-column">
                <h3>Hỗ trợ khách hàng</h3>
                <a href="#">Trung tâm trợ giúp</a>
                <a href="#">An toàn mua bán</a>
                <a href="#">Liên hệ hỗ trợ</a>
            </div>
            <div class="footer-column">
                <h3>Về SM</h3>
                <a href="#">Giới thiệu</a>
                <a href="#">Quy chế hoạt động sàn</a>
                <a href="#">Chính sách bảo mật</a>
                <a href="#">Giải quyết tranh chấp</a>
                <a href="#">Tuyển dụng</a>
                <a href="#">Truyền thông</a>
            </div>
            <div class="footer-column">
                <h3>Liên kết</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/15047/15047435.png" alt="facebook"></a>
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/145/145807.png" alt="in"></a>
                    <a href="#" class="social-icon"><img src="https://cdn-icons-png.flaticon.com/128/15707/15707749.png" alt="instagram"></a>
                </div>
                <p style="margin-top: 20px;">Email: abc@gmail.com</p>
                <p>CSKH: 19003003 (1.000đ/phút)</p>
                <p>Địa chỉ: Tầng 18, Toà nhà abc, 6 Trần Hưng Đạo, Phường Quy Nhơn Tỉnh Gia Lai, Việt Nam</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 SM. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>
</body>
</html>

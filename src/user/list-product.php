 <!DOCTYPE html>
 <html lang="vi">
 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Danh mục Sách</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="list-product.css">
 </head>
 <body>
     <div class="browser-bar">
         <div class="browser-nav">
             <span></span><span></span><span></span>
         </div>
         <span>https://www.sm.vn/list-product</span>
     </div>
 
     <div class="promo-banner">
         <p>Nền tảng mua đồ cũ vì một trái đất xanh hơn!</p>
         <p>Cam kết hoàn tiền 100% nếu sản phẩm không đúng mô tả!</p>
     </div>
 
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <a href="index.php" class="logo">
                <div class="logo-icon"><img src="logo.jpg" alt="logo"></div>
                
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
                <a href="login.php"><u>Đăng nhập</u></a>
                <span>|</span>
                <a href="#"><u>Đăng ký</u></a>
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
 
     <div class="main-content">
         <div class="breadcrumb">
            <u> <a href="index.php">Trang chủ </a></u> <span> > </span> <u> <a href="list-product.php">Danh mục sản phẩm </a></u> <span> > </span> <u> <a href="list-product.html">Sách </a></u>
         </div>
     </div>
 
     <section class="category-page">
         <aside>
             <div class="filter-card">
                 <h4>Tìm trên SM</h4>
                 <input class="filter-input" placeholder="Từ khóa...">
             </div>
             <div class="filter-card">
                 <h4>Ưu đãi</h4>
                 <div class="filter-list">
                     <label><input type="checkbox"> Freeship</label>
                     <label><input type="checkbox"> Giảm giá ≥ 50%</label>
                 </div>
             </div>
             <div class="filter-card">
                 <h4>Danh mục</h4>
                 <select class="filter-select">
                     <option>Sách</option>
                     <option>Sách văn học</option>
                     <option>Sách thiếu nhi</option>
                     <option>Sách kỹ năng</option>
                     <option>Sách giáo khoa</option>
                 </select>
             </div>
             <div class="filter-card">
                 <h4>Giá</h4>
                 <div class="filter-list">
                     <label><input type="checkbox"> Dưới 100.000đ</label>
                     <label><input type="checkbox"> Từ 100.000 - 200.000đ</label>
                     <label><input type="checkbox"> Trên 200.000đ</label>
                 </div>
             </div>
             <div class="filter-card">
                 <h4>Nơi bán</h4>
                 <div class="filter-list">
                     <label><input type="checkbox"> TP.HCM</label>
                     <label><input type="checkbox"> Hà Nội</label>
                     <label><input type="checkbox"> Đà Nẵng</label>
                 </div>
             </div>
             <div class="filter-card">
                 <h4>Tình trạng</h4>
                 <div class="filter-list">
                     <label><input type="checkbox"> Mới</label>
                     <label><input type="checkbox"> Như mới</label>
                     <label><input type="checkbox"> Tốt</label>
                 </div>
             </div>
         </aside>
 
         <div>
             <div class="category-header">
                 <div class="category-title">Bán sách (có 1.838 kết quả)</div>
                 <div class="sorter">
                     Lọc theo:
                     <select>
                         <option>Mặc định</option>
                         <option>Giá tăng dần</option>
                         <option>Giá giảm dần</option>
                         <option>Mới nhất</option>
                     </select>
                 </div>
             </div>
             <div class="category-grid">
                 <!-- 12 product placeholders -->
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Sách toán học</div><div class="product-price">29.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Sách lịch sử thế giới</div><div class="product-price">39.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Áo phông trẻ em</div><div class="product-price">37.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Doremon tập 29</div><div class="product-price">77.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Truyện Kiều</div><div class="product-price">99.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Địa lý Việt Nam</div><div class="product-price">59.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Triết học Mác - Lênin</div><div class="product-price">37.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Hóa học đại cương</div><div class="product-price">79.999đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Hacker TOEIC</div><div class="product-price">129.000đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Tiếng Nhật căn bản</div><div class="product-price">89.000đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">IELTS Reading</div><div class="product-price">179.000đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
                 <div class="product-card"><div class="product-image"></div><div class="product-info"><div class="product-name">Toán cao cấp</div><div class="product-price">77.000đ</div><div class="product-actions"><button class="btn-add-cart">thêm vào</button><button class="btn-buy-now">mua ngay</button></div></div></div>
             </div>
 
             <div class="pagination">
                 <button>&lt;&lt;</button>
                 <button>&lt;</button>
                 <button>1</button>
                 <button>2</button>
                 <button>3</button>
                 <button>4</button>
                 <button>5</button>
                 <button>&gt;</button>
                 <button>&gt;&gt;</button>
             </div>
         </div>
     </section>
 
     <footer class="footer">
         <div class="footer-content">
             <div class="footer-column">
                 <div class="logo" style="margin-bottom: 15px;">
                     <div class="logo-icon"><img src="logo.jpg" alt="logo"></div>
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
 


<?php
session_start();

// Kết nối database
require_once('../database/db.php');

// Lấy ID người bán từ URL
$seller_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($seller_id <= 0) {
    header('Location: index.php');
    exit();
}

// Lấy thông tin shop/người bán
$sql_seller = "SELECT ID_NguoiDung, HoTen, SoDienThoai, Email, DiaChiGiaoHangMacDinh 
               FROM nguoidung 
               WHERE ID_NguoiDung = $seller_id";
$result_seller = mysqli_query($conn, $sql_seller);
$seller = mysqli_fetch_assoc($result_seller);

if (!$seller) {
    header('Location: index.php');
    exit();
}

// Đếm tổng số sản phẩm của shop
$sql_count = "SELECT COUNT(*) as total FROM sanpham WHERE ID_NguoiBan = $seller_id AND TrangThaiDangBan = 'DangBan'";
$result_count = mysqli_query($conn, $sql_count);
$count_data = mysqli_fetch_assoc($result_count);
$total_products = $count_data['total'];

// Pagination
$products_per_page = 20;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;
$total_pages = ceil($total_products / $products_per_page);

// Lấy sản phẩm của shop
$sql_products = "SELECT sp.*, ha.URL_HinhAnh 
                 FROM sanpham sp 
                 LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
                 WHERE sp.ID_NguoiBan = $seller_id AND sp.TrangThaiDangBan = 'DangBan'
                 GROUP BY sp.ID_SanPham 
                 ORDER BY sp.NgayTao DESC
                 LIMIT $offset, $products_per_page";
$result_products = mysqli_query($conn, $sql_products);
$products = [];
while ($row = mysqli_fetch_assoc($result_products)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop <?php echo htmlspecialchars($seller['HoTen']); ?> - SM</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .shop-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .shop-info-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .shop-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .shop-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            flex-shrink: 0;
        }

        .shop-details {
            flex: 1;
        }

        .shop-name {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .shop-stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .stat-label {
            font-size: 13px;
            color: #999;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #ff6b6b;
        }

        .shop-contact {
            margin-top: 20px;
            padding-top: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }

        .contact-item span {
            font-weight: 500;
        }

        .products-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff6b6b;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .product-card {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            background: white;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 220px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 14px;
            margin-bottom: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.4;
            min-height: 40px;
            color: #333;
        }

        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: #ff6b6b;
        }

        .product-meta-info {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px 0;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #ff6b6b;
            color: white;
            border-color: #ff6b6b;
        }

        .pagination .active {
            background: #ff6b6b;
            color: white;
            border-color: #ff6b6b;
        }

        .no-products {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-products-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .shop-header {
                flex-direction: column;
                text-align: center;
            }

            .shop-stats {
                justify-content: center;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }
        }
    </style>
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
                    <span><u><a href="buyer/edit-profile.php"><?php echo $_SESSION['fullname']; ?></a></u></span>
                    <span>|</span>
                    <a href="logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>    
                <a href="/baitaplvn_sm/src/login.php"><u>Đăng nhập</u></a>
                <span>|</span>
                <a href="/baitaplvn_sm/src/register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
                <span>|</span>
                <a href="user/cart.php" class="cart-icon"><img class="cart-icon-img" src="https://cdn-icons-png.flaticon.com/128/1170/1170678.png" alt="cart"></a>
            </div>
        </div>

        <div class="nav-categories">
            <a href="index.php">Trang chủ</a>
            <a href="#">Sách</a>
            <a href="#">Đồ cho nam</a>
            <a href="#">Đồ cho nữ</a>
            <a href="#">Đồ cho mẹ và bé</a>
            <a href="#">Đồ gia dụng</a>
            <a href="#">Đồ chơi</a>
        </div>
    </header>

    <!-- Main content -->
    <div class="shop-container">
        <!-- Shop Info Card -->
        <div class="shop-info-card">
            <div class="shop-header">
                <div class="shop-avatar">
                    👤
                </div>
                <div class="shop-details">
                    <h1 class="shop-name"><?php echo htmlspecialchars($seller['HoTen']); ?></h1>
                    <div class="shop-stats">
                        <div class="stat-item">
                            <span class="stat-label">Sản phẩm</span>
                            <span class="stat-value"><?php echo $total_products; ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Đang bán</span>
                            <span class="stat-value"><?php echo $total_products; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="shop-contact">
                <?php if (!empty($seller['SoDienThoai'])): ?>
                <div class="contact-item">
                    📞 <span>Số điện thoại:</span> <?php echo htmlspecialchars($seller['SoDienThoai']); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($seller['Email'])): ?>
                <div class="contact-item">
                    ✉️ <span>Email:</span> <?php echo htmlspecialchars($seller['Email']); ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($seller['DiaChi'])): ?>
                <div class="contact-item">
                    📍 <span>Địa chỉ:</span> <?php echo htmlspecialchars($seller['DiaChi']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Section -->
        <div class="products-section">
            <h2 class="section-title">Sản phẩm của shop (<?php echo $total_products; ?>)</h2>
            
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <a href="product-detail.php?id=<?php echo $product['ID_SanPham']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($product['URL_HinhAnh'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['URL_HinhAnh']); ?>" alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>">
                                    <?php else: ?>
                                        <span style="color: #ccc; font-size: 48px;">📦</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($product['TenSanPham']); ?></div>
                                    <div class="product-price"><?php echo number_format($product['Gia'], 0, ',', '.'); ?>đ</div>
                                    <div class="product-meta-info">
                                        <?php echo htmlspecialchars($product['TinhTrang']); ?>
                                        <?php if ($product['SoLuong'] > 0): ?>
                                            • Còn <?php echo $product['SoLuong']; ?> sản phẩm
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($current_page > 1): ?>
                        <a href="?id=<?php echo $seller_id; ?>&page=<?php echo $current_page - 1; ?>">« Trước</a>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<a href="?id=' . $seller_id . '&page=1">1</a>';
                        if ($start_page > 2) {
                            echo '<span>...</span>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        if ($i == $current_page) {
                            echo '<span class="active">' . $i . '</span>';
                        } else {
                            echo '<a href="?id=' . $seller_id . '&page=' . $i . '">' . $i . '</a>';
                        }
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<span>...</span>';
                        }
                        echo '<a href="?id=' . $seller_id . '&page=' . $total_pages . '">' . $total_pages . '</a>';
                    }
                    ?>

                    <?php if ($current_page < $total_pages): ?>
                        <a href="?id=<?php echo $seller_id; ?>&page=<?php echo $current_page + 1; ?>">Sau »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-products">
                    <div class="no-products-icon">📦</div>
                    <p>Shop hiện chưa có sản phẩm nào đang bán</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

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

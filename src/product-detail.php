<?php
session_start();
require_once __DIR__ . '/../database/db.php';

// Kết nối database
require_once('../database/db.php');

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit();
}

// Lấy thông tin sản phẩm
$sql = "SELECT sp.*, dm.TenDanhMuc, nd.HoTen as TenNguoiBan, nd.SoDienThoai 
        FROM sanpham sp 
        LEFT JOIN danhmuc dm ON sp.ID_DanhMuc = dm.ID_DanhMuc 
        LEFT JOIN nguoidung nd ON sp.ID_NguoiBan = nd.ID_NguoiDung 
        WHERE sp.ID_SanPham = $product_id AND sp.TrangThaiDangBan = 'DangBan'";

$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);
// Lấy danh mục
$sql_categories = "SELECT * FROM danhmuc ORDER BY TenDanhMuc";
$result_categories = mysqli_query($conn, $sql_categories);
$categories = [];
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
}
if (!$product) {
    header('Location: index.php');
    exit();
}

// Lấy tất cả hình ảnh của sản phẩm
$sql_images = "SELECT URL_HinhAnh FROM hinhanhsanpham WHERE ID_SanPham = $product_id";
$result_images = mysqli_query($conn, $sql_images);
$images = [];
while ($row = mysqli_fetch_assoc($result_images)) {
    $images[] = $row['URL_HinhAnh'];
}

// Lấy sản phẩm tương tự
$sql_similar = "SELECT sp.*, ha.URL_HinhAnh 
                FROM sanpham sp 
                LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
                WHERE sp.ID_DanhMuc = ? AND sp.ID_SanPham != ? AND sp.TrangThaiDangBan = 'DangBan'
                GROUP BY sp.ID_SanPham 
                LIMIT 5";
$stmt_similar = mysqli_prepare($conn, $sql_similar);
mysqli_stmt_bind_param($stmt_similar, "ii", $product['ID_DanhMuc'], $product_id);
mysqli_stmt_execute($stmt_similar);
$result_similar = mysqli_stmt_get_result($stmt_similar);
$similar_products = [];
while ($row = mysqli_fetch_assoc($result_similar)) {
    $similar_products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['TenSanPham']); ?> - SM</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .product-detail-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .product-detail-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .product-images {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f8f8;
        }

        .main-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .thumbnail-images {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border: 2px solid #e5e5e5;
            border-radius: 4px;
            cursor: pointer;
            overflow: hidden;
            flex-shrink: 0;
            transition: border-color 0.3s;
        }

        .thumbnail:hover, .thumbnail.active {
            border-color: #ff6b6b;
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .product-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .product-price {
            font-size: 32px;
            font-weight: bold;
            color: #ff6b6b;
        }

        .product-meta {
            display: flex;
            gap: 20px;
            padding: 15px 0;
            border-top: 1px solid #e5e5e5;
            border-bottom: 1px solid #e5e5e5;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .meta-label {
            font-size: 12px;
            color: #999;
        }

        .meta-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .product-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid #e5e5e5;
            border-radius: 4px;
            overflow: hidden;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #f5f5f5;
            cursor: pointer;
            font-size: 18px;
        }

        .quantity-btn:hover {
            background: #e5e5e5;
        }

        .quantity-input {
            width: 50px;
            height: 32px;
            border: none;
            text-align: center;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .btn-primary {
            flex: 1;
            padding: 12px 24px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #ff5252;
        }

        .btn-secondary {
            flex: 1;
            padding: 12px 24px;
            background: white;
            color: #ff6b6b;
            border: 2px solid #ff6b6b;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: #fff5f5;
        }

        .product-description-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff6b6b;
        }

        .description-content {
            line-height: 1.6;
            color: #555;
        }

        .seller-info-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .seller-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .seller-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #999;
        }

        .seller-details {
            flex: 1;
        }

        .seller-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .seller-phone {
            font-size: 14px;
            color: #666;
        }

        .similar-products-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .product-card {
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            padding: 12px;
        }

        .product-name {
            font-size: 14px;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-card .product-price {
            font-size: 16px;
            font-weight: bold;
            color: #ff6b6b;
        }
         .btn-view-shop {
            padding: 10px 24px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-block;
        }

        .btn-view-shop:hover {
            background: #ff5252;
        }

        @media (max-width: 768px) {
            .product-detail-main {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
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
    <?php include 'header.php'; ?>
    <!-- Main content -->
    <div class="product-detail-container">
        <!-- Product Detail Main Section -->
        <div class="product-detail-main">
            <!-- Left: Product Images -->
            <div class="product-images">
                <div class="main-image" id="mainImage">
                    <?php if (!empty($images)): ?>
                        <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>">
                    <?php else: ?>
                        <span style="color: #999;">Không có hình ảnh</span>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-images">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" onclick="changeImage('<?php echo htmlspecialchars($image); ?>', this)">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Product Info -->
            <div class="product-info-section">
                <h1 class="product-title"><?php echo htmlspecialchars($product['TenSanPham']); ?></h1>
                <div class="product-price"><?php echo number_format($product['Gia'], 0, ',', '.'); ?>đ</div>

                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Tình trạng</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['TinhTrang']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Số lượng còn</span>
                        <span class="meta-value"><?php echo $product['SoLuong']; ?></span>
                    </div>
                    <?php if (!empty($product['KichThuoc'])): ?>
                    <div class="meta-item">
                        <span class="meta-label">Kích thước</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['KichThuoc']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($product['TenDanhMuc'])): ?>
                    <div class="meta-item">
                        <span class="meta-label">Danh mục</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['TenDanhMuc']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
 <?php if (isset($product['SoLuong']) && $product['SoLuong'] >= 1): ?>
                    <div class="product-quantity">
                        <span>Số lượng:</span>
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="decreaseQuantity()">-</button>
                            <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['SoLuong']; ?>">
                            <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-secondary" onclick="addToCartDetail()">Thêm vào giỏ</button>
                        <button class="btn-primary" ><a href="user/checkout.php?buy_now=1&product_id=<?php echo $product_id; ?>">Mua ngay</a></button>
                    </div>
                <?php else: ?>
                    <div class="product-quantity">
                        <span style="color: #ff6b6b; font-weight: bold; font-size: 18px;">⚠ Sản phẩm đã hết hàng</span>
                    </div>

                    <div class="action-buttons">
                        <button class="btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;">Hết hàng</button>
                        <button class="btn-primary" disabled style="opacity: 0.5; cursor: not-allowed;">Không khả dụng</button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['DiaChiLayHang'])): ?>
                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">Địa chỉ lấy hàng</span>
                        <span class="meta-value"><?php echo htmlspecialchars($product['DiaChiLayHang']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Description Section -->
        <div class="product-description-section">
            <h2 class="section-title">Mô tả sản phẩm</h2>
            <div class="description-content">
                <?php echo !empty($product['MoTa']) ? nl2br(htmlspecialchars($product['MoTa'])) : 'Chưa có mô tả cho sản phẩm này.'; ?>
            </div>
        </div>

        <!-- Seller Info Section -->
        <div class="seller-info-section">
            <h2 class="section-title">Thông tin người bán</h2>
            <div class="seller-info">
                <div class="seller-avatar">
                    👤
                </div>
                <div class="seller-details">
                    <div class="seller-name"><?php echo htmlspecialchars($product['TenNguoiBan']); ?></div>
                    <?php if (!empty($product['SoDienThoai'])): ?>
                    <div class="seller-phone">📞 <?php echo htmlspecialchars($product['SoDienThoai']); ?></div>
                    <?php endif; ?>
                </div>
                 <a href="shop-detail.php?id=<?php echo $product['ID_NguoiBan']; ?>" class="btn-view-shop">
                    Xem Shop
                </a>
            </div>
        </div>

        <!-- Similar Products Section -->
        <?php if (!empty($similar_products)): ?>
        <div class="similar-products-section">
            <h2 class="section-title">Sản phẩm tương tự</h2>
            <div class="products-grid">
                <?php foreach ($similar_products as $similar): ?>
                    <a href="product-detail.php?id=<?php echo $similar['ID_SanPham']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-card">
                            <div class="product-image">
                                <?php if (!empty($similar['URL_HinhAnh'])): ?>
                                    <img src="<?php echo htmlspecialchars($similar['URL_HinhAnh']); ?>" alt="<?php echo htmlspecialchars($similar['TenSanPham']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($similar['TenSanPham']); ?></div>
                                <div class="product-price"><?php echo number_format($similar['Gia'], 0, ',', '.'); ?>đ</div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
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

    <script>
        function changeImage(imageUrl, thumbnail) {
            document.getElementById('mainImage').querySelector('img').src = imageUrl;
            
            // Remove active class from all thumbnails
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            thumbnail.classList.add('active');
        }

        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
            }
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const min = parseInt(input.min);
            const current = parseInt(input.value);
            if (current > min) {
                input.value = current - 1;
            }
        }

        function addToCartDetail() {
            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $product_id; ?>;
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('action', 'add');
            formData.append('ajax', '1');

            fetch('add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (confirm('✓ ' + data.message + '\nSố lượng sản phẩm trong giỏ: ' + data.cart_count + '\n\nBạn có muốn chuyển đến giỏ hàng?')) {
                        window.location.href = 'user/cart.php';
                    }
                } else {
                    alert('⚠ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            });
        }

        function buyNow() {
            const quantity = document.getElementById('quantity').value;
            const productId = <?php echo $product_id; ?>;
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('action', 'add');
            formData.append('ajax', '1');

            fetch('add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'user/cart.php';
                } else {
                    alert('⚠ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }
    </script>
</body>
</html>

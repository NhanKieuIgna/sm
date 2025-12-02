<?php
session_start();

// Kết nối database
require_once('../database/db.php');

// Lấy sản phẩm nổi bật (giới hạn 8 sản phẩm)
$sql = "SELECT sp.*, img.URL_HinhAnh 
        FROM sanpham sp 
        LEFT JOIN hinhanhsanpham img ON sp.ID_SanPham = img.ID_SanPham 
        WHERE sp.TrangThaiDangBan = 'DangBan' 
        GROUP BY sp.ID_SanPham 
        ORDER BY sp.NgayTao DESC 
        LIMIT 8";
$result = mysqli_query($conn, $sql);
$featured_products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $featured_products[] = $row;
    }
}

// Lấy danh mục
$sql_categories = "SELECT * FROM danhmuc ORDER BY TenDanhMuc";
$result_categories = mysqli_query($conn, $sql_categories);
$categories = [];
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
}
// Lấy đánh giá cửa hàng gần đây
$sql_reviews = "SELECT dg.SoSao, dg.NhanXet AS NoiDung, dg.NgayDanhGia, 
                       nd_nguoidanhgia.HoTen AS TenNguoiDanhGia,
                       nd_nguoiduocdanhgia.HoTen AS TenNguoiBan,
                       nd_nguoiduocdanhgia.ID_NguoiDung AS ID_NguoiBan,
                       sp.TenSanPham,
                       sp.ID_SanPham,
                       img.URL_HinhAnh
                FROM danhgia_nhanxet dg
                JOIN nguoidung nd_nguoidanhgia ON dg.ID_NguoiDanhGia = nd_nguoidanhgia.ID_NguoiDung
                JOIN nguoidung nd_nguoiduocdanhgia ON dg.ID_NguoiDuocDanhGia = nd_nguoiduocdanhgia.ID_NguoiDung
                LEFT JOIN danhsachdonhang dh ON dg.ID_DonHang = dh.ID_DonHang
                LEFT JOIN chitietdonhang ctdh ON dh.ID_DonHang = ctdh.ID_DonHang
                LEFT JOIN sanpham sp ON ctdh.ID_SanPham = sp.ID_SanPham
                LEFT JOIN hinhanhsanpham img ON sp.ID_SanPham = img.ID_SanPham
                ORDER BY dg.NgayDanhGia DESC
                LIMIT 5";
                // Thực thi truy vấn
$result_reviews = mysqli_query($conn, $sql_reviews);
$recent_reviews = [];
if ($result_reviews) {
    while ($row = mysqli_fetch_assoc($result_reviews)) {
        $recent_reviews[] = $row;
    }
}
?>

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
    <?php include 'header.php'; ?>

    <!-- Main content -->
    <main class="main-content">
        <!-- Featured Products Section -->
        <section>
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <a href="user/list-product.php" class="view-all-link">xem tất cả</a>
            </div>
            <div class="products-grid">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?php echo $product['ID_SanPham']; ?>" style="text-decoration: none; color: inherit;">
                                <div class="product-image">
                                    <?php if (!empty($product['URL_HinhAnh'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['URL_HinhAnh']); ?>" alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($product['TenSanPham']); ?></div>
                                    <div class="product-price"><?php echo number_format($product['Gia'], 0, ',', '.'); ?>đ</div>
                                    <div class="product-actions" onclick="event.stopPropagation()" style="display: flex; justify-content: center; gap: 10px;">
                                        <form method="POST" action="add-to-cart.php" style="display: inline;">
                                            <input type="hidden" name="product_id" value="<?php echo $product['ID_SanPham']; ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                            <button type="submit" class="btn-add-cart">thêm vào</button>
                                        </form>
                                        <a href="product-detail.php?id=<?php echo $product['ID_SanPham']; ?>">
                                            <button class="btn-buy-now">mua ngay</button>
                                        </a>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Chưa có sản phẩm nổi bật</p>
                <?php endif; ?>
            </div>
        </section>

        
        <!-- Recent Store Reviews Section -->
        <section>
            <div class="section-header">
                <h2 class="section-title">Đánh giá các cửa hàng gần đây</h2>
            </div>
            <div class="reviews-grid">
                <?php if (!empty($recent_reviews)): ?>
                    <?php foreach ($recent_reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div>
                                    <div class="reviewer-name"><?php echo htmlspecialchars($review['TenNguoiDanhGia']); ?></div>
                                    <div class="review-date"><?php echo date('d/m/Y', strtotime($review['NgayDanhGia'])); ?></div>
                                </div>
                                <div class="review-rating">
                                    <?php 
                                    $rating = intval($review['SoSao']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $rating) ? '★' : '☆';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php if (!empty($review['TenSanPham'])): ?>
                                <a href="product-detail.php?id=<?php echo $review['ID_SanPham']; ?>" style="text-decoration: none; color: inherit;">
                                    <div class="review-product">
                                        <?php if (!empty($review['URL_HinhAnh'])): ?>
                                            <img src="<?php echo htmlspecialchars($review['URL_HinhAnh']); ?>" alt="<?php echo htmlspecialchars($review['TenSanPham']); ?>" class="review-product-image">
                                        <?php endif; ?>
                                        <div class="review-product-info">
                                            <div class="product-name"><?php echo htmlspecialchars($review['TenSanPham']); ?></div>
                                            <div class="seller-name">Người bán: <?php echo htmlspecialchars($review['TenNguoiBan']); ?></div>
                                        </div>
                                    </div>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($review['NoiDung'])): ?>
                                <div class="review-content">
                                    <?php echo htmlspecialchars($review['NoiDung']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">Chưa có đánh giá nào</p>
                <?php endif; ?>
            </div>
            <style>
                .reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.review-card {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f0f0f0;
}

.reviewer-name {
    font-weight: 600;
    color: #333;
    font-size: 15px;
}

.review-date {
    font-size: 13px;
    color: #999;
    margin-top: 3px;
}

.review-rating {
    color: #ffc107;
    font-size: 16px;
    letter-spacing: 2px;
}

.review-product {
    display: flex;
    gap: 12px;
    margin-bottom: 15px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 6px;
    transition: background 0.3s;
}

.review-product:hover {
    background: #f0f0f0;
}

.review-product-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}

.review-product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.review-product-info .product-name {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
}

.review-product-info .seller-name {
    font-size: 13px;
    color: #666;
}

.review-content {
    font-size: 14px;
    line-height: 1.6;
    color: #555;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
}

.stores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 20px;
    justify-content: center;
    align-items: center;
}

.store-card {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.store-image {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #2d8659;
    position: relative;
}

.store-image::after {
    /* ::after là pseudo-element tức là phần tử ảo được tạo ra bởi CSS */
    /*  content: 'X'; */
    position: absolute;
}
            </style>
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
    <script>

        function addToCart(productId) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('action', 'add');
            formData.append('ajax', '1');

            fetch('add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ ' + data.message + '\nSố lượng sản phẩm trong giỏ: ' + data.cart_count);
                } else {
                    alert('⚠ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
            });
        }

        // Search autocomplete functionality
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();

                if (query.length < 2) {
                    searchSuggestions.style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`search-suggestions.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.products.length > 0) {
                                let html = '<div style="padding: 10px;">';
                                data.products.forEach(product => {
                                    html += `
                                        <a href="product-detail.php?id=${product.id}" style="display: flex; align-items: center; padding: 8px; text-decoration: none; color: #333; border-bottom: 1px solid #f0f0f0;">
                                            <img src="${product.image || 'placeholder.jpg'}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 500;">${product.name}</div>
                                                <div style="color: #ff6b6b; font-size: 14px;">${product.price}</div>
                                            </div>
                                        </a>
                                    `;
                                });
                                html += '</div>';
                                searchSuggestions.innerHTML = html;
                                searchSuggestions.style.display = 'block';
                            } else {
                                searchSuggestions.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                        });
                }, 300);
            });

            // Close suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                    searchSuggestions.style.display = 'none';
                }
            });
        }

        // Toggle categories
        const toggleBtn = document.getElementById('toggleCategories');
        if (toggleBtn) {
            let isExpanded = false;
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const extraCategories = document.querySelectorAll('.extra-category');
                isExpanded = !isExpanded;
                
                extraCategories.forEach(cat => {
                    cat.style.display = isExpanded ? 'inline-block' : 'none';
                });
                
                this.textContent = isExpanded ? 'Thu gọn ▲' : 'Tất cả danh mục ▼';
            });
        }
    </script>
</body>
</html>

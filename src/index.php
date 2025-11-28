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

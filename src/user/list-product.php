<?php
session_start();

// Kết nối database
require_once('../../database/db.php');

// Lấy category ID từ URL
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Lấy thông tin danh mục hiện tại
$category_name = 'Tất cả sản phẩm';
if ($category_id > 0) {
    $sql_cat = "SELECT TenDanhMuc FROM danhmuc WHERE ID_DanhMuc = $category_id";
    $result_cat = mysqli_query($conn, $sql_cat);
    if ($row_cat = mysqli_fetch_assoc($result_cat)) {
        $category_name = $row_cat['TenDanhMuc'];
    }
}

// Lấy danh sách tất cả danh mục cho menu và filter
$sql_categories = "SELECT * FROM danhmuc ORDER BY TenDanhMuc";
$result_categories = mysqli_query($conn, $sql_categories);
$categories = [];
if ($result_categories) {
    while ($row = mysqli_fetch_assoc($result_categories)) {
        $categories[] = $row;
    }
}

// Lấy từ khóa tìm kiếm từ URL
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Cấu hình phân trang
$products_per_page = 8;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;

// Xây dựng câu truy vấn sản phẩm
$where_conditions = ["sp.TrangThaiDangBan = 'DangBan'"];

if ($category_id > 0) {
    $where_conditions[] = "sp.ID_DanhMuc = $category_id";
}

if (!empty($search_query)) {
    $search_term = '%' . mysqli_real_escape_string($conn, $search_query) . '%';
    $where_conditions[] = "sp.TenSanPham LIKE '$search_term'";
    $category_name = 'Kết quả tìm kiếm: "' . htmlspecialchars($search_query) . '"';
}

$where_clause = implode(' AND ', $where_conditions);

// Đếm tổng số sản phẩm để phân trang
$sql_count = "SELECT COUNT(DISTINCT sp.ID_SanPham) as total
              FROM sanpham sp 
              WHERE $where_clause";
$result_count = mysqli_query($conn, $sql_count);
$total_products = 0;
if ($result_count) {
    $row_count = mysqli_fetch_assoc($result_count);
    $total_products = $row_count['total'];
}

$total_pages = ceil($total_products / $products_per_page);

// Lấy danh sách sản phẩm cho trang hiện tại
$sql = "SELECT sp.*, ha.URL_HinhAnh 
        FROM sanpham sp 
        LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
        WHERE $where_clause
        GROUP BY sp.ID_SanPham 
        ORDER BY sp.NgayTao DESC
        LIMIT $products_per_page OFFSET $offset";

$result = mysqli_query($conn, $sql);
$products = [];
$cities = []; // Danh sách thành phố cho filter

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
        
        // Lấy tên thành phố từ DiaChiLayHang (giả sử định dạng: Số nhà, Phường, Quận, Thành phố)
        if (!empty($row['DiaChiLayHang'])) {
            $address_parts = explode(',', $row['DiaChiLayHang']);
            
            $city = trim(end($address_parts));
            if (!empty($city) && !in_array($city, $cities)) {
                $cities[] = $city;
            }
        }
    }
}

// Hàm tạo URL phân trang giữ nguyên tham số
function build_pagination_url($page) {
    global $category_id, $search_query;
    $params = [];
    if ($category_id > 0) $params['category'] = $category_id;
    if (!empty($search_query)) $params['search'] = $search_query;
    $params['page'] = $page;
    return 'list-product.php?' . http_build_query($params);
}

sort($cities);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - <?php echo htmlspecialchars($category_name); ?></title>
    <link rel="stylesheet" href="../css/style.css">
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
            <a href="../index.php" class="logo">
                <div class="logo-icon"><img src="../img/logo.jpg" alt="logo"></div>
            </a>
            
            <div class="category-dropdown">
                <button class="category-btn">Tất cả danh mục</button>
                <div class="category-menu">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <a href="list-product.php?category=<?php echo $cat['ID_DanhMuc']; ?>"><?php echo htmlspecialchars($cat['TenDanhMuc']); ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="#">Chưa có danh mục</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="search-container">
                <form action="list-product.php" method="GET" style="display: flex; align-items: center; width: 100%;">
                    <input type="text" name="search" class="search-bar" placeholder=" Tìm kiếm" value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
                    <button type="submit" style="background: none; border: none; cursor: pointer;">
                        <span class="search-icon">&#x1F50E;&#xFE0E;</span>
                    </button>
                </form>
            </div>

            <div class="user-actions">
                <a href="#" class="bell-icon"><img class="bell-icon-img" src="https://cdn-icons-png.flaticon.com/128/3602/3602145.png" alt="bell"></a>
                <span>|</span>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span><u><a href="../buyer/edit-profile.php"><?php echo $_SESSION['fullname']; ?></a></u></span>
                    <span>|</span>
                    <a href="../logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>    
                    <a href="../login.php"><u>Đăng nhập</u></a>
                    <span>|</span>
                    <a href="../register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
                <span>|</span>
                <a href="cart.php" class="cart-icon"><img class="cart-icon-img" src="https://cdn-icons-png.flaticon.com/128/1170/1170678.png" alt="cart"></a>
            </div>
        </div>

        <div class="nav-categories">
            <?php 
            $displayed_categories_nav = array_slice($categories, 0, 6);
            $remaining_categories_nav = array_slice($categories, 6);
            
            if (!empty($displayed_categories_nav)): 
                foreach ($displayed_categories_nav as $cat): 
            ?>
                <a href="list-product.php?category=<?php echo $cat['ID_DanhMuc']; ?>"><?php echo htmlspecialchars($cat['TenDanhMuc']); ?></a>
            <?php 
                endforeach;
                
                if (!empty($remaining_categories_nav)): 
                    foreach ($remaining_categories_nav as $cat): 
            ?>
                <a href="list-product.php?category=<?php echo $cat['ID_DanhMuc']; ?>" class="extra-category" style="display: none;"><?php echo htmlspecialchars($cat['TenDanhMuc']); ?></a>
            <?php 
                    endforeach;
                endif;
                
                if (!empty($remaining_categories_nav)): 
            ?>
                <a href="#" id="toggleCategories" style="color: #ff6b6b; font-weight: 500;">Tất cả danh mục ▼</a>
            <?php 
                endif;
            else: 
            ?>
                <a href="#">Chưa có danh mục</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-content">
        <div class="breadcrumb">
            <u><a href="../index.php">Trang chủ</a></u> <span> > </span> <u><a href="list-product.php">Danh mục sản phẩm</a></u> <?php if ($category_id > 0): ?><span> > </span> <strong><?php echo htmlspecialchars($category_name); ?></strong><?php endif; ?>
        </div>
    </div>

    <section class="category-page">
        <aside>
            <div class="filter-card">
                <h4>Tìm trên SM</h4>
                <input class="filter-input" placeholder="Từ khóa..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                <select class="filter-select" onchange="window.location.href='list-product.php?category='+this.value">
                    <option value="0">Tất cả danh mục</option>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['ID_DanhMuc']; ?>" <?php echo ($category_id == $cat['ID_DanhMuc']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['TenDanhMuc']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
                    <?php if (!empty($cities)): ?>
                        <?php foreach ($cities as $city): ?>
                            <label><input type="checkbox"> <?php echo htmlspecialchars($city); ?></label>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <label><input type="checkbox"> Chưa có dữ liệu</label>
                    <?php endif; ?>
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
                <div class="category-title"><?php echo htmlspecialchars($category_name); ?> (có <?php echo $total_products; ?> kết quả)</div>
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
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="../product-detail.php?id=<?php echo $product['ID_SanPham']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-image">
                                <?php if (!empty($product['URL_HinhAnh'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['URL_HinhAnh']); ?>" alt="<?php echo htmlspecialchars($product['TenSanPham']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($product['TenSanPham']); ?></div>
                                <div class="product-price"><?php echo number_format($product['Gia'], 0, ',', '.'); ?>đ</div>
                                <div class="product-actions" onclick="event.stopPropagation()" style="display: flex; justify-content: center; gap: 10px;">
                                    <form method="POST" action="../add-to-cart.php" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['ID_SanPham']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                        <button type="submit" class="btn-add-cart">thêm vào</button>
                                    </form>
                                    <a href="../product-detail.php?id=<?php echo $product['ID_SanPham']; ?>">
                                        <button class="btn-buy-now">mua ngay</button>
                                    </a>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Chưa có sản phẩm nào.</p>
                <?php endif; ?>
            </div>

            <div class="pagination">
                <?php if ($total_pages > 1): ?>
                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo build_pagination_url(1); ?>"><button>&lt;&lt;</button></a>
                        <a href="<?php echo build_pagination_url($current_page - 1); ?>"><button>&lt;</button></a>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <?php if ($i == $current_page): ?>
                            <button style="background: #ff6b6b; color: white;"><?php echo $i; ?></button>
                        <?php else: ?>
                            <a href="<?php echo build_pagination_url($i); ?>"><button><?php echo $i; ?></button></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo build_pagination_url($current_page + 1); ?>"><button>&gt;</button></a>
                        <a href="<?php echo build_pagination_url($total_pages); ?>"><button>&gt;&gt;</button></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <div class="logo" style="margin-bottom: 15px;">
                    <div class="logo-icon"><img src="../img/logo.jpg" alt="logo"></div>
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

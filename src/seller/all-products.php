<?php 
    require_once 'header.php'; 
    
    $page = 'all-products';

    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href='/../../login.php';</script>";
        exit();
    }
    $user_id = $_SESSION['user_id'];

    $where_clause = "WHERE sp.ID_NguoiBan = '$user_id'"; 

    if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
        $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
        $where_clause .= " AND (sp.TenSanPham LIKE '%$keyword%' OR sp.MoTa LIKE '%$keyword%')";
    }

    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $cat_id = mysqli_real_escape_string($conn, $_GET['category']);
        $where_clause .= " AND sp.ID_DanhMuc = '$cat_id'";
    }

    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $date_search = mysqli_real_escape_string($conn, $_GET['date']);
        $where_clause .= " AND DATE(sp.NgayTao) = '$date_search'";
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status_search = mysqli_real_escape_string($conn, $_GET['status']);
        $where_clause .= " AND sp.TrangThaiDangBan = '$status_search'";
    }

    $sql = "SELECT sp.*, ha.URL_HinhAnh 
            FROM sanpham sp 
            LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham 
            $where_clause
            GROUP BY sp.ID_SanPham
            ORDER BY sp.NgayTao DESC";

    $result = mysqli_query($conn, $sql);
    $total_products = mysqli_num_rows($result);
?>


<div style="display: flex; min-height: 100vh;">
    
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content-area">
        <h1 class="page-title">Tất cả sản phẩm</h1>

        <form class="filter-bar" method="GET" action="">
            
            <input type="text" name="keyword" class="form-control-custom input-keyword" 
                   placeholder="Nhập tên SP..." 
                   value="<?php if(isset($_GET['keyword'])) echo htmlspecialchars($_GET['keyword']); ?>">
            
            <select name="category" class="form-control-custom input-category">
                <option value="">-- Danh mục --</option>
                <?php 
                    $sql_dm = "SELECT * FROM danhmuc ORDER BY TenDanhMuc ASC"; 
                    $result_dm = mysqli_query($conn, $sql_dm);
                    if ($result_dm) {
                        while ($row_dm = mysqli_fetch_assoc($result_dm)) {
                            $selected = (isset($_GET['category']) && $_GET['category'] == $row_dm['ID_DanhMuc']) ? 'selected' : '';
                            echo '<option value="' . $row_dm['ID_DanhMuc'] . '" ' . $selected . '>' . $row_dm['TenDanhMuc'] . '</option>';
                        }
                    }
                ?>
            </select>

            <input type="date" name="date" class="form-control-custom input-date" 
                   value="<?php if(isset($_GET['date'])) echo $_GET['date']; ?>">

            <select name="status" class="form-control-custom input-status">
                <option value="">-- Trạng thái --</option>
                <?php
                    $status_options = [
                        'ChoDuyet' => 'Chờ duyệt',
                        'DangBan'  => 'Đang bán',
                        'DaBan'    => 'Đã bán',
                        'BiGoBo'   => 'Bị gỡ bỏ'
                    ];

                    foreach ($status_options as $db_val => $show_val) {
                        $selected = (isset($_GET['status']) && $_GET['status'] == $db_val) ? 'selected' : '';
                        echo "<option value='$db_val' $selected>$show_val</option>";
                    }
                ?>
            </select>

            <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
        
        </form>

        <p class="product-count-label">Số lượng sản phẩm: <?php echo $total_products; ?></p>

        <div class="product-list-container">
            <?php if ($total_products > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <div class="prod-img-box">
                            <?php 
                                $img_url = $row['URL_HinhAnh'];
                                $real_path = '../' . $img_url;
                                if (!empty($real_path)) {
                                    echo '<img src="'.$real_path.'" alt="Ảnh SP">';
                                } else {
                                    echo '<span class="no-image-placeholder">×</span>';
                                }
                            ?>
                        </div>

                        <div class="prod-info">
                            <p class="prod-name"><?php echo htmlspecialchars($row['TenSanPham']); ?></p>
                            <p>Số lượng: <strong><?php echo $row['SoLuong']; ?></strong></p>
                            <p>Giá: <span class="price-highlight"><?php echo number_format($row['Gia'], 0, ',', '.'); ?>đ</span></p>
                            
                            <p>Trạng thái: 
                                <?php 
                                    $st = $row['TrangThaiDangBan'];
                                    $color = 'black';
                                    $text = $st;

                                    switch ($st) {
                                        case 'DangBan': $color = '#28a745'; $text = 'Đang bán'; break;
                                        case 'ChoDuyet': $color = '#ffc107'; $text = 'Chờ duyệt'; break;
                                        case 'DaBan': $color = '#17a2b8'; $text = 'Đã bán'; break;
                                        case 'BiGoBo': $color = '#dc3545'; $text = 'Bị gỡ bỏ'; break;
                                    }
                                ?>
                                <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                    <?php echo $text; ?>
                                </span>
                            </p>
                            
                            <p style="font-size: 12px; color: #888;">
                                Ngày đăng: <?php echo date('d/m/Y H:i', strtotime($row['NgayTao'])); ?>
                            </p>
                        </div>

                        <div class="prod-actions">
                            <a href="../product-detail.php?id=<?php echo $row['ID_SanPham']; ?>" class="btn-action">
                                <i class="fa-duotone fa-solid fa-eye"></i> Xem
                            </a>
                            
                            <a href="edit-product.php?id=<?php echo $row['ID_SanPham']; ?>" class="btn-action">
                                <i class="fa-sharp fa-solid fa-pen-to-square"></i> Chỉnh sửa
                            </a>
                            
                            <a href="delete-product.php?id=<?php echo $row['ID_SanPham']; ?>" class="btn-action btn-delete" 
                               onclick="return confirm('Bạn muốn xóa sản phẩm này?');">
                               <i class="fa-solid fa-trash-can"></i> Xóa
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #666;">
                    Không tìm thấy sản phẩm nào phù hợp.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
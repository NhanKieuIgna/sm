<?php 
     require_once __DIR__ . '/../../database/db.php';
    require_once 'header.php'; 
    $page = 'order-list'; 

    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href='../login.php';</script>"; exit();
    }
    $user_id = $_SESSION['user_id'];

    $current_status = isset($_GET['status']) ? $_GET['status'] : 'all';
    
    // Đếm số lượng
    $count_sql = "SELECT TrangThaiDonHang, COUNT(*) as sl FROM danhsachdonhang WHERE ID_NguoiBan = '$user_id' GROUP BY TrangThaiDonHang";
    $count_res = mysqli_query($conn, $count_sql);
    $counts = [];
    $total_all = 0;
    while($row = mysqli_fetch_assoc($count_res)){
        $counts[$row['TrangThaiDonHang']] = $row['sl'];
        $total_all += $row['sl'];
    }

    $tabs = [
        'all'             => ['label' => 'Tất cả',          'db_val' => '',              'count' => $total_all],
        'cho-xac-nhan'    => ['label' => 'Chờ xác nhận',    'db_val' => 'ChoXacNhan',    'count' => $counts['ChoXacNhan'] ?? 0],
        'cho-giao-hang'   => ['label' => 'Chờ giao hàng',   'db_val' => 'ChoGiaoHang',   'count' => $counts['ChoGiaoHang'] ?? 0],
        'dang-van-chuyen' => ['label' => 'Đang vận chuyển', 'db_val' => 'DangVanChuyen', 'count' => $counts['DangVanChuyen'] ?? 0],
        'khieu-nai'       => ['label' => 'Khiếu nại',       'db_val' => 'KhieuNai',      'count' => $counts['KhieuNai'] ?? 0],
        'da-huy'          => ['label' => 'Đã hủy',          'db_val' => 'DaHuy',         'count' => $counts['DaHuy'] ?? 0],
        'hoan-thanh'      => ['label' => 'Hoàn thành',      'db_val' => 'HoanThanh',     'count' => $counts['HoanThanh'] ?? 0]
    ];

    $where_sql = "WHERE dh.ID_NguoiBan = '$user_id'";
    if ($current_status != 'all') {
        $db_status = $tabs[$current_status]['db_val'];
        $where_sql .= " AND dh.TrangThaiDonHang = '$db_status'";
    }
    
    if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
        $kw = mysqli_real_escape_string($conn, $_GET['keyword']);
        $where_sql .= " AND (dh.ID_DonHang LIKE '%$kw%' OR nm.TenDangNhap LIKE '%$kw%')";
    }
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        $date = mysqli_real_escape_string($conn, $_GET['date']);
        $where_sql .= " AND DATE(dh.NgayDatHang) = '$date'";
    }

    $sql_orders = "SELECT dh.*, nm.TenDangNhap as TenNguoiMua 
                   FROM danhsachdonhang dh
                   JOIN nguoidung nm ON dh.ID_NguoiMua = nm.ID_NguoiDung
                   $where_sql
                   ORDER BY dh.NgayDatHang DESC";
    $result_orders = mysqli_query($conn, $sql_orders);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style> body { background-color: #f5f5f5; font-family: Arial, sans-serif; } </style>

<div style="display: flex; min-height: 100vh;">
    <?php include 'sidebar.php'; ?>
    <div style="flex: 1; padding: 0;">
        <h2 style="padding: 20px 20px 0; font-size: 20px;">Đơn bán</h2>

        <div class="status-tabs">
            <?php foreach ($tabs as $key => $tab): ?>
                <a href="order-list.php?status=<?php echo $key; ?>" 
                   class="tab-item <?php if($current_status == $key) echo 'active'; ?>">
                   <?php echo $tab['label']; ?>
                   <?php if($tab['count'] > 0): ?>
                       <span class="badge-count"><?php echo $tab['count']; ?></span>
                   <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="padding: 0 15px;">
            <form action="" method="GET" class="filter-section">
                <input type="hidden" name="status" value="<?php echo $current_status; ?>">
                <div class="search-input-group">
                    <input type="text" name="keyword" class="input-control" placeholder="Nhập từ khóa ở đây" value="<?php if(isset($_GET['keyword'])) echo $_GET['keyword']; ?>">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </div>
                <div class="search-input-group" style="max-width: 200px;">
                    <input type="text" name="date" class="input-control" placeholder="Ngày đặt hàng" onfocus="(this.type='date')" onblur="(this.type='text')" value="<?php if(isset($_GET['date'])) echo $_GET['date']; ?>">
                    <i class="fa-regular fa-calendar search-icon"></i>
                </div>
                <div style="flex: 1; text-align: right;">
                    <button type="submit" class="btn-search-blue" style="float: right;"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>
        </div>

        <div style="padding: 20px 15px;">
            <?php if (mysqli_num_rows($result_orders) > 0): ?>
                <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
                    <div class="order-card">
                        <div class="oc-header">
                            <div class="oc-user">
                                <?php echo htmlspecialchars($order['TenNguoiMua']); ?>
                                <span style="font-weight:normal; font-size:13px; color:#777; margin-left:10px;">
                                    <?php echo date('d-m-Y', strtotime($order['NgayDatHang'])); ?>
                                </span>
                            </div>
                            <div style="color: #f57224; text-transform: uppercase; font-size: 13px;">
                                <?php echo $order['TrangThaiDonHang']; ?>
                            </div>
                        </div>

                        <div class="oc-body">
                            <?php 
                                $id_dh = $order['ID_DonHang'];
                                $sql_detail = "SELECT ct.SoLuongMua, ct.GiaTaiThoiDiemDat, sp.TenSanPham, sp.MauSac, ha.URL_HinhAnh FROM chitietdonhang ct JOIN sanpham sp ON ct.ID_SanPham = sp.ID_SanPham LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham WHERE ct.ID_DonHang = '$id_dh' GROUP BY sp.ID_SanPham"; 
                                $res_detail = mysqli_query($conn, $sql_detail);
                                $tong_tien_san_pham = 0;
                                while ($item = mysqli_fetch_assoc($res_detail)):
                                    $tong_tien_san_pham += ($item['SoLuongMua'] * $item['GiaTaiThoiDiemDat']);
                                    $img_url = $item['URL_HinhAnh'];
                                    if(!empty($img_url) && strpos($img_url, '../') !== 0) $img_url = '../' . $img_url;
                            ?>
                                <a href="order-detail.php?id=<?php echo $order['ID_DonHang']; ?>" style="text-decoration:none; color:inherit;">
                                    <div class="oc-product-item">
                                        <div style="width:80px; height:80px;">
                                            <img src="<?php echo !empty($img_url) ? $img_url : 'https://placehold.co/80x80?text=No+Img'; ?>" class="oc-img" onerror="this.src='https://placehold.co/80x80?text=No+Img'">
                                        </div>
                                        <div style="flex:1;">
                                            <div style="font-weight:500;"><?php echo $item['TenSanPham']; ?></div>
                                            <div style="color:#888; font-size:13px;">Phân loại: <?php echo $item['MauSac']; ?></div>
                                            <div style="font-size:13px;">x<?php echo $item['SoLuongMua']; ?></div>
                                        </div>
                                        <div style="font-weight:bold; color:#f57224;"><?php echo number_format($item['GiaTaiThoiDiemDat'], 0, ',', '.'); ?>đ</div>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>

                        <div class="oc-footer">
                            <div style="margin-bottom: 10px; font-size: 14px;">
                                Tổng tiền hàng: <span style="color:#f57224; font-size:18px; font-weight:bold;">
                                    <?php echo number_format($tong_tien_san_pham, 0, ',', '.'); ?>đ
                                </span>
                            </div>
                            
                            <?php if ($order['TrangThaiDonHang'] == 'ChoXacNhan'): ?>
                                <button onclick="openRejectModal(<?php echo $order['ID_DonHang']; ?>)" class="btn-white">Từ chối bán</button>
                                <button onclick="openConfirmModal(<?php echo $order['ID_DonHang']; ?>)" class="btn-orange">Xác nhận bán</button>
                            
                            <?php elseif ($order['TrangThaiDonHang'] == 'ChoGiaoHang'): ?>
                                <button class="btn-white" disabled style="background:#f9f9f9; color:#999; border-color:#eee; cursor: not-allowed;">
                                    <i class="fa-solid fa-box-open"></i> Đang chờ Shipper lấy hàng...
                                </button>

                            <?php elseif ($order['TrangThaiDonHang'] == 'DangVanChuyen'): ?>
                                <button class="btn-white" disabled style="color:#2196F3; border-color:#2196F3; background:#e3f2fd; cursor: default;">
                                    <i class="fa-solid fa-truck-fast"></i> Shipper đang giao hàng...
                                </button>

                            <?php elseif ($order['TrangThaiDonHang'] == 'DaHuy'): ?>
                                <span style="color:red;">Đơn hàng đã hủy</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align:center; margin-top:50px; color:#999;">Không có đơn hàng nào.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="confirmModal" class="custom-modal">
    <div class="modal-content">
        <h3>Xác nhận đơn hàng</h3>
        <p style="color:#666; margin:15px 0 25px;">Chuyển sang trạng thái "Chờ giao hàng"?</p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <button onclick="closeModal('confirmModal')" class="btn-white">Hủy</button>
            <a id="link-confirm" href="#" class="btn-orange">Đồng ý</a>
        </div>
    </div>
</div>

<div id="rejectModal" class="custom-modal">
    <div class="modal-content">
        <h3 style="color:#d32f2f;">Từ chối đơn hàng</h3>
        <p style="color:#666; margin:15px 0 25px;">Bạn có chắc muốn hủy đơn hàng này?</p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <button onclick="closeModal('rejectModal')" class="btn-white">Quay lại</button>
            <a id="link-reject" href="#" class="btn-orange" style="background:#d32f2f; border-color:#d32f2f;">Xác nhận hủy</a>
        </div>
    </div>
</div>

<script>
    function openConfirmModal(id) { document.getElementById('confirmModal').style.display='block'; document.getElementById('link-confirm').href='order-action.php?action=confirm&id='+id; }
    function openRejectModal(id) { document.getElementById('rejectModal').style.display='block'; document.getElementById('link-reject').href='order-action.php?action=reject&id='+id; }
    function closeModal(id) { document.getElementById(id).style.display='none'; }
    window.onclick = function(e) { if(e.target.className==='custom-modal') e.target.style.display="none"; }
</script>
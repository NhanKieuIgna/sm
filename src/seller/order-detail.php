<?php 
    require_once 'header.php'; 
    $page = "order-list.php";
    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href='login.php';</script>"; exit();
    }
    $user_id = $_SESSION['user_id'];

    // 1. LẤY ID TỪ URL
    if (!isset($_GET['id'])) {
        echo "<script>alert('Không tìm thấy đơn hàng!'); window.location.href='order-list.php';</script>"; exit();
    }
    $id_donhang = mysqli_real_escape_string($conn, $_GET['id']);

    // 2. QUERY THÔNG TIN ĐƠN HÀNG + NGƯỜI MUA + NGƯỜI BÁN
    $sql_order = "SELECT dh.*, 
                          nm.TenDangNhap, nm.SoDienThoai, nm.DiaChiGiaoHangMacDinh,
                          nb.HoTen AS TenShop, nb.SoDienThoai AS SDTShop, nb.DiaChiGiaoHangMacDinh AS DiaChiShop
                  FROM danhsachdonhang dh
                  JOIN nguoidung nm ON dh.ID_NguoiMua = nm.ID_NguoiDung
                  JOIN nguoidung nb ON dh.ID_NguoiBan = nb.ID_NguoiDung
                  WHERE dh.ID_DonHang = '$id_donhang' AND dh.ID_NguoiBan = '$user_id'";
    
    $res_order = mysqli_query($conn, $sql_order);
    
    if (mysqli_num_rows($res_order) == 0) {
        echo "Không tìm thấy đơn hàng hoặc bạn không có quyền xem."; exit();
    }
    $order = mysqli_fetch_assoc($res_order);

    $sql_calc_total = "SELECT SUM(SoLuongMua * GiaTaiThoiDiemDat) as TongTienHang 
                       FROM chitietdonhang 
                       WHERE ID_DonHang = '$id_donhang'";
    $res_calc = mysqli_query($conn, $sql_calc_total);
    $row_calc = mysqli_fetch_assoc($res_calc);
    
    $tong_don = $row_calc['TongTienHang'] ? $row_calc['TongTienHang'] : 0;
    $phi_co_dinh = 9900; 
    $thue = $tong_don * 0.015;
    $thuc_nhan = $tong_don - $thue;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { background-color: #f5f5f5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; }
</style>

<div style="display: flex; min-height: 100vh;">
    <?php include 'sidebar.php'; ?>
    
    <div style="flex: 1; padding: 20px;">
        
        <div class="breadcrumb">
            <a href="order-list.php">Đơn bán</a> 
            <i class="fa-solid fa-chevron-right" style="font-size: 12px; color: #999;"></i>
            <span>Chi tiết đơn hàng</span>
        </div>

        <div class="detail-box" style="border-radius: 4px;">
            <div class="status-banner">
                <div class="banner-title">
                    <?php 
                        $status_map = [
                            'ChoXacNhan' => 'Chờ xác nhận',
                            'ChoGiaoHang' => 'Chờ giao hàng',
                            'DangVanChuyen' => 'Đang vận chuyển',
                            'HoanThanh' => 'Đã hoàn thành',
                            'DaHuy' => 'Đã hủy'
                        ];
                        echo isset($status_map[$order['TrangThaiDonHang']]) ? $status_map[$order['TrangThaiDonHang']] : $order['TrangThaiDonHang'];
                    ?>
                </div>
                <div class="banner-desc">Vui lòng đóng gói sản phẩm và đợi nhân viên giao hàng đến lấy hàng</div>
                
                <div class="banner-steps">
                    <span class="<?php echo ($order['TrangThaiDonHang'] == 'ChoXacNhan') ? 'active' : ''; ?>">Đã xác nhận</span> 
                    <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                    <span class="step-item <?php echo ($order['TrangThaiDonHang'] == 'DangVanChuyen') ? 'active' : ''; ?>">Vận chuyển</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
                    <span class="<?php echo ($order['TrangThaiDonHang'] == 'HoanThanh') ? 'active' : ''; ?>">Hoàn thành</span>
                </div>

                <div class="truck-icon">
                    <i class="fa-solid fa-truck"></i>
                </div>
            </div>

            <div class="info-bar">
                <div>
                    <i class="fa-solid fa-store" style="margin-right: 5px;"></i> 
                    <strong><?php echo $_SESSION['user_name'] ?? 'Shop của tôi'; ?></strong>
                    <span style="margin: 0 10px; color: #ddd;">|</span>
                    Mã đơn hàng: <?php echo $order['ID_DonHang']; ?> <i class="fa-regular fa-copy" style="cursor: pointer;"></i>
                </div>
                <div><?php echo date('d-m-Y H:i', strtotime($order['NgayDatHang'])); ?></div>
            </div>

            <div class="content-padding split-layout">
                <div class="col-left">
                    <?php 
                        $sql_prod = "SELECT ct.*, sp.TenSanPham, sp.MauSac, sp.KichThuoc, ha.URL_HinhAnh 
                                     FROM chitietdonhang ct 
                                     JOIN sanpham sp ON ct.ID_SanPham = sp.ID_SanPham
                                     LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham
                                     WHERE ct.ID_DonHang = '$id_donhang' GROUP BY sp.ID_SanPham";
                        $res_prod = mysqli_query($conn, $sql_prod);
                        while($prod = mysqli_fetch_assoc($res_prod)):
                    ?>
                    <div class="prod-item">
                        <?php if(!empty($prod['URL_HinhAnh'])): ?>
                            <img src="<?php echo $prod['URL_HinhAnh']; ?>" class="prod-img">
                        <?php else: ?>
                            <div style="width:80px; height:80px; background:#eee; display:flex; align-items:center; justify-content:center;">No Img</div>
                        <?php endif; ?>
                        <div>
                            <div class="prod-name"><?php echo $prod['TenSanPham']; ?></div>
                            <div class="prod-attr">Phân loại: <?php echo $prod['MauSac']; ?>, Kích cỡ: <?php echo $prod['KichThuoc']; ?></div>
                            <div class="prod-attr">Số lượng: x<?php echo $prod['SoLuongMua']; ?></div>
                            <div style="margin-top: 5px;">Giá: <strong><?php echo number_format($prod['GiaTaiThoiDiemDat'], 0, ',', '.'); ?>đ</strong></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <div style="margin-top: 20px; font-size: 13px; color: #666;">
                        <p>Đơn vị vận chuyển: <strong>Lazada Express</strong> (Mặc định)</p>
                        <p>Phí vận chuyển: 0đ</p>
                    </div>
                </div>
                <div class="col-right">
                    <h4 style="margin: 0 0 10px; font-size: 15px;">Địa chỉ giao hàng:</h4>
                    <div style="font-size: 14px; line-height: 1.5;">
                        <strong><?php echo $order['TenDangNhap']; ?></strong><br>
                        <?php echo $order['SoDienThoai']; ?><br>
                        
                        <span style="color: #666;">
                            <?php echo $order['DiaChiGiaoHangMacDinh']; ?>
                        </span>
                        
                    </div>
                    <a href="#" style="font-size: 13px; color: #218838; text-decoration: none; display: block; margin-top: 10px;">
                        <i class="fa-regular fa-comments"></i> Liên hệ người mua
                    </a>
                </div>
            </div>

            <div class="alert-green">
                <strong><i class="fa-solid fa-circle-check"></i> Lưu ý khi chuẩn bị hàng cho bạn:</strong>
                <ul style="margin: 5px 0 0 20px; padding: 0;">
                    <li>Đóng gói và ghi thông tin mã vận đơn (Tạm sinh: JNT<?php echo rand(100000,999999); ?>VNA) trên kiện hàng.</li>
                    <li>Quay video/chụp ảnh kiện hàng để được hỗ trợ giải quyết khi có vấn đề.</li>
                </ul>
            </div>

            <div class="finance-section">
                <div class="fs-col-left">
                    <h4 style="margin: 0 0 15px; font-size: 15px;">Thông tin vận chuyển:</h4>
                    <div class="timeline-item">
                        <div class="time-text"><?php echo date('d-m-Y H:i', strtotime($order['NgayDatHang'])); ?></div>
                        <div class="status-text">Người mua đã đặt hàng</div>
                    </div>
                    <div class="timeline-item" style="border-left: 2px dashed #ddd;">
                        <div class="time-text">---</div>
                        <div class="status-text" style="color: #999;">Đang chờ lấy hàng...</div>
                    </div>
                    <h4 style="margin: 20px 0 10px; font-size: 15px;">Địa chỉ lấy hàng (Của Shop):</h4>

                    <div style="font-size: 13px; color: #555;">
                        <span style="font-weight: bold; color: #333;">
                        <?php echo $order['TenShop']; ?>
                        </span>
                        <br>

                        <?php echo $order['SDTShop']; ?>
                        <br>

                        <span style="color: #666;">
                        <?php echo $order['DiaChiShop']; ?>
                            </span>
                    </div>
                </div>
                <div class="fs-col-right">
                    <h4 style="margin: 0 0 15px; font-size: 15px;">Bạn sẽ được nhận (khi hoàn thành):</h4>
                    <div class="finance-row"><span>Giá sản phẩm</span><span><?php echo number_format($tong_don, 0, ',', '.'); ?> vnđ</span></div>
                    <div class="finance-row"><span>(-) Phí vận chuyển</span><span>0 vnđ</span></div>
                    <div class="finance-row"><span>(-) Thuế danh mục hàng hóa (1.5%)</span><span><?php echo number_format($thue, 0, ',', '.'); ?> vnđ</span></div>
                    <div class="finance-row finance-final">
                        <span>Bạn sẽ được nhận</span>
                        <span><?php echo number_format($thuc_nhan, 0, ',', '.'); ?> vnđ</span>
                    </div>
                    <div style="text-align: right; margin-top: 5px; font-size: 12px; color: #00bfa5;">
                        và (điểm 02) <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
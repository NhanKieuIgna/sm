<?php 
    require_once 'header.php'; 
    $page = 'sales-summary'; 

    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href='login.php';</script>"; exit();
    }
    $user_id = $_SESSION['user_id'];

    $current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview'; 
    $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
    $to_date   = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

    $finance = [
        'tong_doanh_thu' => 0,
        'tien_thuc_nhan' => 0,
        'tien_mat'       => 0
    ];
    
    $orders_count = [
        'tong_don' => 0,
        'cho_xu_ly' => 0,     
        'hoan_thanh' => 0,    
        'da_huy' => 0,
        'thanh_cong' => 0
    ];

    if ($current_tab == 'overview') {
        $sql_finance = "SELECT 
                            SUM(TongDoanhThu) as TotalRevenue,
                            SUM(TienThucNhan) as NetRevenue,
                            SUM(GiaTriMat_HuyHoan) as LostRevenue
                        FROM thongkedoanhthu
                        WHERE ID_NguoiBan = '$user_id'
                          AND LoaiThoiGian = 'Ngay'
                          AND GiaTriThoiGian BETWEEN '$from_date' AND '$to_date'";
        
        $res_finance = mysqli_query($conn, $sql_finance);
        if ($row_fin = mysqli_fetch_assoc($res_finance)) {
            $finance['tong_doanh_thu'] = $row_fin['TotalRevenue'] ?? 0;
            $finance['tien_thuc_nhan'] = $row_fin['NetRevenue'] ?? 0;
            $finance['tien_mat']       = $row_fin['LostRevenue'] ?? 0;
        }

        $sql_count = "SELECT TrangThaiDonHang, COUNT(*) as sl 
                      FROM danhsachdonhang 
                      WHERE ID_NguoiBan = '$user_id' 
                        AND DATE(NgayDatHang) BETWEEN '$from_date' AND '$to_date'
                      GROUP BY TrangThaiDonHang";
        
        $res_count = mysqli_query($conn, $sql_count);
        while($row = mysqli_fetch_assoc($res_count)){
            $st = $row['TrangThaiDonHang'];
            $sl = $row['sl'];
            $orders_count['tong_don'] += $sl;

            if ($st == 'HoanThanh') {
                $orders_count['hoan_thanh'] += $sl;
                $orders_count['thanh_cong'] += $sl;
            } elseif ($st == 'DaHuy') {
                $orders_count['da_huy'] += $sl;
            } else {
                $orders_count['cho_xu_ly'] += $sl;
            }
        }
    }

    $bestsellers = [];
    if ($current_tab == 'bestseller') {
        $sql_best = "SELECT sp.TenSanPham, sp.ID_SanPham, ha.URL_HinhAnh, 
                            SUM(ct.SoLuongMua) as TongSoLuong, 
                            SUM(ct.SoLuongMua * ct.GiaTaiThoiDiemDat) as TongTien
                     FROM chitietdonhang ct
                     JOIN danhsachdonhang dh ON ct.ID_DonHang = dh.ID_DonHang
                     JOIN sanpham sp ON ct.ID_SanPham = sp.ID_SanPham
                     LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham
                     WHERE dh.ID_NguoiBan = '$user_id' 
                       AND dh.TrangThaiDonHang = 'HoanThanh' 
                       AND DATE(dh.NgayDatHang) BETWEEN '$from_date' AND '$to_date'
                     GROUP BY sp.ID_SanPham
                     ORDER BY TongSoLuong DESC
                     LIMIT 10";
        $res_best = mysqli_query($conn, $sql_best);
        if($res_best){
            while($row = mysqli_fetch_assoc($res_best)){
                $bestsellers[] = $row;
            }
        }
    }
?>

<style>
    body { background-color: #f5f5f5; font-family: Arial, sans-serif; }
</style>

<div style="display: flex; min-height: 100vh;">
    <?php include 'sidebar.php'; ?>
    
    <div style="flex: 1; padding: 20px;">
        <h2 style="margin-bottom: 20px;">Thống kê doanh thu</h2>

        <div class="summary-header">
            <div class="report-tabs">
                <a href="?tab=overview&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" 
                   class="tab-link <?php if($current_tab == 'overview') echo 'active'; ?>">
                   Báo cáo tổng quan
                </a>
                <a href="?tab=bestseller&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" 
                   class="tab-link <?php if($current_tab == 'bestseller') echo 'active'; ?>">
                   Báo cáo hàng bán chạy
                </a>
            </div>

            <form action="" method="GET" class="filter-form">
                <input type="hidden" name="tab" value="<?php echo $current_tab; ?>">
                <label>Từ ngày:</label>
                <input type="date" name="from_date" class="form-control-date" value="<?php echo $from_date; ?>">
                <label>Đến ngày:</label>
                <input type="date" name="to_date" class="form-control-date" value="<?php echo $to_date; ?>">
                <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> Tìm kiếm</button>
            </form>
        </div>

        <div class="stats-container">
            
            <?php if($current_tab == 'overview'): ?>
                
                <div class="section-title">Tổng Quan Tài Chính</div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-label">Doanh thu (Tổng)</div>
                        <div class="stat-value money"><?php echo number_format($finance['tong_doanh_thu'], 0, ',', '.'); ?> ₫</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Doanh thu thực nhận</div>
                        <div class="stat-value" style="color:#28a745;"><?php echo number_format($finance['tien_thuc_nhan'], 0, ',', '.'); ?> ₫</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Tiền mất (Do hủy/Hoàn)</div>
                        <div class="stat-value" style="color:#dc3545;"><?php echo number_format($finance['tien_mat'], 0, ',', '.'); ?> ₫</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Tổng đơn hàng</div>
                        <div class="stat-value"><?php echo $orders_count['tong_don']; ?></div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed #ddd; margin-bottom: 30px;">

                <div class="section-title">Chi Tiết Đơn Hàng</div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-label">Chưa thanh toán (Đang XL)</div>
                        <div class="stat-value"><?php echo $orders_count['cho_xu_ly']; ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Đã thanh toán (Hoàn thành)</div>
                        <div class="stat-value"><?php echo $orders_count['hoan_thanh']; ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Số đơn bị hủy</div>
                        <div class="stat-value" style="color:#dc3545;"><?php echo $orders_count['da_huy']; ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Số đơn thành công</div>
                        <div class="stat-value" style="color:#28a745;"><?php echo $orders_count['thanh_cong']; ?></div>
                    </div>
                </div>

            <?php elseif($current_tab == 'bestseller'): ?>
                
                <div class="section-title">Top 10 Sản phẩm bán chạy nhất</div>
                <?php if(count($bestsellers) > 0): ?>
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th style="text-align:center;">Số lượng bán</th>
                                <th style="text-align:right;">Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; foreach($bestsellers as $bs): ?>
                            <tr>
                                <td>
                                    <div class="prod-cell">
                                        <span class="rank-num <?php if($rank==1) echo 'top-1'; ?>">#<?php echo $rank++; ?></span>
                                        <?php if(!empty($bs['URL_HinhAnh'])): ?>
                                            <img src="<?php echo $bs['URL_HinhAnh']; ?>" style="width:50px; height:50px; object-fit:cover; border-radius:4px; border:1px solid #eee;">
                                        <?php endif; ?>
                                        <strong><?php echo $bs['TenSanPham']; ?></strong>
                                    </div>
                                </td>
                                <td style="text-align:center; font-weight:bold;"><?php echo $bs['TongSoLuong']; ?></td>
                                <td style="text-align:right; color:#f57224;"><?php echo number_format($bs['TongTien'], 0, ',', '.'); ?>đ</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align:center; padding: 40px; color: #999;">Chưa có sản phẩm nào bán được.</div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>
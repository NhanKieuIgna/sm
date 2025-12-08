<?php
session_start();
require_once '../../database/db.php'; 

if (isset($_GET['action']) && isset($_GET['id']) && isset($_SESSION['user_id'])) {
    
    $id_donhang = $_GET['id'];
    $action = $_GET['action'];
    $user_id = $_SESSION['user_id']; 

    $new_status = '';
    $redirect_tab = 'all';

    if ($action == 'confirm') {
        $new_status = 'ChoGiaoHang';
        $redirect_tab = 'cho-giao-hang';
        
    } elseif ($action == 'reject') {
        $new_status = 'DaHuy';
        $redirect_tab = 'da-huy';
        
    } elseif ($action == 'complete') { 
        $new_status = 'HoanThanh';
        $redirect_tab = 'hoan-thanh';
    }

if ($new_status != '') {   
        $sql_calc = "SELECT SUM(SoLuongMua * GiaTaiThoiDiemDat) as TongTienHang 
                     FROM chitietdonhang 
                     WHERE ID_DonHang = '$id_donhang'";
                     
        $res_calc = mysqli_query($conn, $sql_calc);
        $row_calc = mysqli_fetch_assoc($res_calc);
        
        $total_money = $row_calc['TongTienHang'] ? $row_calc['TongTienHang'] : 0;

        $sql_update = "UPDATE danhsachdonhang 
                       SET TrangThaiDonHang = '$new_status' 
                       WHERE ID_DonHang = '$id_donhang' AND ID_NguoiBan = '$user_id'";
        
        if (mysqli_query($conn, $sql_update)) {
            updateRevenueStats($conn, $user_id, $new_status, $total_money);
            
            echo "<script>
                    alert('Cập nhật trạng thái thành công!'); 
                    window.location.href='order-list.php?status=$redirect_tab';
                  </script>";
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }
    }
}

function updateRevenueStats($conn, $user_id, $status, $amount) {
    $today = date('Y-m-d'); 
    
    $fee_percent = 0.015; 
    $net_income = $amount - ($amount * $fee_percent);

    if ($status == 'HoanThanh') {
        $sql_stats = "INSERT INTO thongkedoanhthu 
                        (ID_NguoiBan, LoaiThoiGian, GiaTriThoiGian, TongDoanhThu, TienThucNhan, GiaTriMat_HuyHoan)
                      VALUES 
                        ('$user_id', 'Ngay', '$today', '$amount', '$net_income', 0)
                      ON DUPLICATE KEY UPDATE 
                        TongDoanhThu = TongDoanhThu + VALUES(TongDoanhThu),
                        TienThucNhan = TienThucNhan + VALUES(TienThucNhan)";
        
        mysqli_query($conn, $sql_stats);
    }
    elseif ($status == 'DaHuy') {
        $sql_stats = "INSERT INTO thongkedoanhthu 
                        (ID_NguoiBan, LoaiThoiGian, GiaTriThoiGian, TongDoanhThu, TienThucNhan, GiaTriMat_HuyHoan)
                      VALUES 
                        ('$user_id', 'Ngay', '$today', 0, 0, '$amount')
                      ON DUPLICATE KEY UPDATE 
                        GiaTriMat_HuyHoan = GiaTriMat_HuyHoan + VALUES(GiaTriMat_HuyHoan)";
        
        mysqli_query($conn, $sql_stats);
    }
}
?>
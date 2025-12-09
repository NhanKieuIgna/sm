<?php
session_start();
require_once '../../database/db.php'; 
require_once __DIR__ . '/../../database/db.php';
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
    $sql_get_items = "SELECT ID_SanPham, SoLuongMua FROM chitietdonhang WHERE ID_DonHang = '$id_donhang'";
    $res_items = mysqli_query($conn, $sql_get_items);

    if ($res_items) {
        while ($item = mysqli_fetch_assoc($res_items)) {
            $id_sp = $item['ID_SanPham'];
            $sl_tra = $item['SoLuongMua'];

            $sql_restore = "UPDATE sanpham SET SoLuong = SoLuong + $sl_tra WHERE ID_SanPham = '$id_sp'";
            mysqli_query($conn, $sql_restore);
        }
    }
    } elseif ($action == 'complete') { 
        $new_status = 'HoanThanh';
        $redirect_tab = 'hoan-thanh';
    }

    if ($new_status != '') {
        
        $sql_update = "UPDATE danhsachdonhang 
                       SET TrangThaiDonHang = '$new_status' 
                       WHERE ID_DonHang = '$id_donhang' AND ID_NguoiBan = '$user_id'";
        
        if (mysqli_query($conn, $sql_update)) {
            
            echo "<script>
                    alert('Cập nhật trạng thái thành công!'); 
                    window.location.href='order-list.php?status=$redirect_tab';
                  </script>";
        } else {
            echo "Lỗi SQL: " . mysqli_error($conn);
        }
    }
}
?>
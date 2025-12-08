<?php
    require_once 'header.php';

    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>";
        exit();
    }

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_sp = mysqli_real_escape_string($conn, $_GET['id']);
        $user_id = $_SESSION['user_id'];

        $check_sql = "SELECT ID_SanPham FROM sanpham WHERE ID_SanPham = '$id_sp' AND ID_NguoiBan = '$user_id'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $sql_get_img = "SELECT URL_HinhAnh FROM hinhanhsanpham WHERE ID_SanPham = '$id_sp'";
            $res_img = mysqli_query($conn, $sql_get_img);
            
            while ($row_img = mysqli_fetch_assoc($res_img)) {
                $file_path = $row_img['URL_HinhAnh'];
                if (file_exists($file_path)) {
                    unlink($file_path); 
                }
            }

            // Lệnh SQL để xóa sản phẩm
            $sql_del_img = "DELETE FROM hinhanhsanpham WHERE ID_SanPham = '$id_sp'";
            mysqli_query($conn, $sql_del_img);

            $sql_del_sp = "DELETE FROM sanpham WHERE ID_SanPham = '$id_sp' AND ID_NguoiBan = '$user_id'";

            // Thông báo kết quả xóa thành công
            if (mysqli_query($conn, $sql_del_sp)) {
                echo "<script>
                        alert('Đã xóa sản phẩm thành công!');
                        window.location.href = 'all-products.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Lỗi Database: " . mysqli_error($conn) . "');
                        window.location.href = 'all-products.php';
                      </script>";
            }

        } else {
            echo "<script>
                    alert('Cảnh báo: Bạn không có quyền xóa sản phẩm này!');
                    window.location.href = 'all-products.php';
                  </script>";
        }

    } else {
        header("Location: all-products.php");
        exit();
    }
?>
<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "secondhand_market";
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header("Location: delivery_index.php");
    exit(); 
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql_order = "
        SELECT
            dh.*,
            nm.HoTen AS TenNguoiMua,
            nb.HoTen AS TenNguoiBan,
            ngh.HoTen AS TenNguoiGiaoHang
        FROM danhsachdonhang dh
        JOIN nguoidung nm ON dh.ID_NguoiMua = nm.ID_NguoiDung
        JOIN nguoidung nb ON dh.ID_NguoiBan = nb.ID_NguoiDung
        LEFT JOIN nguoidung ngh ON dh.ID_NguoiGiaoHang = ngh.ID_NguoiDung
        WHERE dh.ID_DonHang = :id
    ";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bindParam(':id', $order_id, PDO::PARAM_INT);
    $stmt_order->execute();
    $order = $stmt_order->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Lỗi: Đơn hàng không tồn tại.");
    }
   
    if ($order['PhuongThucThanhToan'] === 'COD') {
        $tong_tien_can_thu = $order['SoTienCanThu_COD'];
    } else {
        $tong_tien_can_thu = 0; 
    }
    $sql_items = "
        SELECT
            ct.*,
            sp.TenSanPham,
            ha.URL_HinhAnh
        FROM chitietdonhang ct
        JOIN sanpham sp ON ct.ID_SanPham = sp.ID_SanPham
        LEFT JOIN hinhanhsanpham ha ON sp.ID_SanPham = ha.ID_SanPham
        WHERE ct.ID_DonHang = :id
        GROUP BY ct.ID_ChiTiet
    ";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bindParam(':id', $order_id, PDO::PARAM_INT);
    $stmt_items->execute();
    $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    $sql_history = "
        SELECT *
        FROM lichsutrangthai
        WHERE ID_DonHang = :id
        ORDER BY ThoiGianCapNhat DESC
    ";
    $stmt_history = $conn->prepare($sql_history);
    $stmt_history->bindParam(':id', $order_id, PDO::PARAM_INT);
    $stmt_history->execute();
    $history = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Lỗi truy vấn CSDL: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng #<?php echo htmlspecialchars($order['ID_DonHang']); ?></title>
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #17a2b8;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --background-light: #f8f9fa;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px;
            background-color: var(--background-light); 
        }
        .container { 
            max-width: 1000px; 
            margin: auto; 
            background: #fff; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        }
        h1 { 
            color: var(--primary-color); 
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }
        h2 { 
            color: #333; 
            border-bottom: 2px solid var(--primary-color); 
            padding-bottom: 5px; 
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        .order-summary, .item-list, .shipping-info, .history { 
            margin-bottom: 30px; 
            padding: 20px; 
            border: 1px solid #e9ecef; 
            border-radius: 8px; 
            background-color: #ffffff;
            transition: box-shadow 0.3s;
        }
        .order-summary:hover, .item-list:hover, .shipping-info:hover, .history:hover {
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.1);
        }
        
        /* Badge Status */
        .status-badge { 
            display: inline-block; 
            padding: 6px 12px; 
            border-radius: 20px; 
            font-weight: bold; 
            color: white; 
            font-size: 0.9em;
            text-transform: uppercase;
        }
        .status-ChoXacNhan { background-color: var(--warning-color); } 
        .status-DangVanChuyen { background-color: var(--secondary-color); } 
        .status-HoanThanh, .status-DaGiao { background-color: var(--primary-color); } 
        .status-DaHuy, .status-KhieuNai { background-color: var(--danger-color); } 
        .status-default { background-color: #6c757d; } 

        /* Table Styling */
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        th, td { 
            padding: 12px 15px; 
            border: 1px solid #f0f0f0; 
            text-align: left; 
        }
        th { 
            background-color: #e9f7ef;
            color: #333;
            font-weight: 600;
        }
        .item-row img { 
            width: 50px; 
            height: 50px; 
            object-fit: cover; 
            margin-right: 10px; 
            border-radius: 6px; 
        }
        .total-row td { 
            font-weight: bold; 
            background-color: #f0fcf3; 
            border-top: 2px solid var(--primary-color);
        }
        .total-cod td {
            background-color: #d4edda; 
            color: #155724;
            font-size: 1.1em;
            border-top: 2px solid var(--primary-color);
        }
        .total-cod strong {
            font-size: 1.2em;
            color: var(--danger-color); 
        }
        .history ul {
            list-style: none;
            padding: 0;
        }
        .history li {
            padding: 8px 0;
            border-bottom: 1px dotted #ccc;
        }
        .history li:last-child {
            border-bottom: none;
        }
        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        a:hover {
            color: #1e7e34;
            text-decoration: underline;
        }
        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px; 
            background-color: #6c757d; 
            color: white !important; 
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s, box-shadow 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-back:hover {
            background-color: #5a6268;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Chi Tiết Đơn Hàng #<?php echo htmlspecialchars($order['ID_DonHang']); ?></h1>
    
    <div class="order-summary">
        <h2>Tóm Tắt Đơn Hàng</h2>
        <p><strong>Mã Đơn Hàng:</strong> #<?php echo htmlspecialchars($order['ID_DonHang']); ?></p>
        <p><strong>Ngày Đặt Hàng:</strong> <?php echo date('H:i:s d-m-Y', strtotime($order['NgayDatHang'])); ?></p>
        <p><strong>Người Bán:</strong> <?php echo htmlspecialchars($order['TenNguoiBan']); ?></p>
        <p><strong>Người Mua:</strong> <?php echo htmlspecialchars($order['TenNguoiMua']); ?></p>
        <p><strong>Trạng Thái:</strong>
            <span class="status-badge status-<?php echo htmlspecialchars($order['TrangThaiDonHang']); ?>">
                <?php echo htmlspecialchars($order['TrangThaiDonHang']); ?>
            </span>
        </p>
    </div>
    
    <div class="item-list">
        <h2>Sản Phẩm Đã Mua (<?php echo count($order_items); ?> món)</h2>
        <table>
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Giá Đơn Vị</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr class="item-row">
                        <td>
                            <img src="<?php echo '../' ?><?php echo htmlspecialchars($item['URL_HinhAnh'] ?? 'placeholder.jpg'); ?>" alt="Ảnh sản phẩm">
                        </td>
                        <td><?php echo htmlspecialchars($item['TenSanPham']); ?></td>
                        <td>₫<?php echo number_format($item['GiaTaiThoiDiemDat'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($item['SoLuongMua']); ?></td>
                        <td>₫<?php echo number_format($item['GiaTaiThoiDiemDat'] * $item['SoLuongMua'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
                
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;">Tổng Giá Trị Sản Phẩm</td>
                    <td>₫<?php echo number_format($order['TongGiaTriDonHang'], 0, ',', '.'); ?></td>
                </tr>
                
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;">Phí Giao Hàng</td>
                    <td>₫<?php echo number_format($tong_tien_can_thu, 0, ',', '.'); ?></td>
                </tr>
                
                <?php if ($order['PhuongThucThanhToan'] === 'COD'): ?>
                <tr class="total-cod">
                    <td colspan="4" style="text-align: right;">**Tổng Số Tiền Cần Thu (COD)**</td>
                    <td><strong>₫<?php echo number_format($tong_tien_can_thu, 0, ',', '.'); ?></strong></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="shipping-info">
        <h2>Thông Tin Giao Hàng</h2>
        <p><strong>Địa Chỉ Giao Hàng:</strong> <?php echo nl2br(htmlspecialchars($order['DiaChiGiaoHang'])); ?></p>
        <p><strong>Phương Thức Vận Chuyển:</strong> <?php echo htmlspecialchars($order['PhuongThucVanChuyen'] ?? 'Chưa xác định'); ?></p>
        <p><strong>Phương Thức Thanh Toán:</strong> <?php echo htmlspecialchars($order['PhuongThucThanhToan'] ?? 'Chưa xác định'); ?></p>
        <p><strong>Người Giao Hàng:</strong> <?php echo htmlspecialchars($order['TenNguoiGiaoHang'] ?? 'Chưa được gán'); ?></p>
        <?php if (!empty($order['LyDoHuy']) && $order['TrangThaiDonHang'] == 'DaHuy'): ?>
            <p style="color: var(--danger-color);"><strong>Lý Do Hủy:</strong> <?php echo htmlspecialchars($order['LyDoHuy']); ?></p>
        <?php endif; ?>
    </div>

    <div class="history">
        <h2>Lịch Sử Trạng Thái</h2>
        <?php if (count($history) > 0): ?>
            <ul>
                <?php foreach ($history as $log): ?>
                    <li>
                        [<?php echo date('H:i:s d-m-Y', strtotime($log['ThoiGianCapNhat'])); ?>] -
                        **<?php echo htmlspecialchars($log['TrangThaiMoi']); ?>**
                        <?php if (!empty($log['GhiChu'])): ?>
                            (Ghi chú: <?php echo htmlspecialchars($log['GhiChu']); ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Chưa có lịch sử trạng thái nào.</p>
        <?php endif; ?>
    </div>

    <p style="text-align: center;">
        <a href="my_donhang.php" class="btn-back"> Quay lại trang Đơn hàng</a>
    </p>

</div>

</body>
</html>
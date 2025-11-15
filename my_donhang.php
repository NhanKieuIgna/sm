<?php
session_start();

// Dữ liệu đơn hàng giả lập (thường lấy từ CSDL)
$orders = [
    [
        'order_id' => 'DH001',
        'date' => '2025-11-15',
        'status' => 'Đã giao',
        'customer_name' => 'Ngô Quốc Trung',
        'phone' => '0912345678',
        'address' => '123 Nguyễn Huệ, Quy Nhơn, Bình Định',
        'image' => 'uploads/1699999999_delivery1.jpg'
    ],
    [
        'order_id' => 'DH002',
        'date' => '2025-11-14',
        'status' => 'Chưa giao',
        'customer_name' => 'Trần Văn A',
        'phone' => '0987654321',
        'address' => '456 Lê Lợi, Quy Nhơn, Bình Định',
        'image' => ''
    ],
    [
        'order_id' => 'DH003',
        'date' => '2025-11-13',
        'status' => 'Đã giao',
        'customer_name' => 'Lê Thị B',
        'phone' => '0911222333',
        'address' => '789 Hùng Vương, Quy Nhơn, Bình Định',
        'image' => 'uploads/1699999999_delivery3.jpg'
    ],
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của bạn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            height: 600px;
            overflow-y: auto; /* Thanh cuộn dọc */
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .order {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .order:last-child {
            border-bottom: none;
        }
        .order img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .order-info {
            flex-grow: 1;
        }
        .order-info p {
            margin: 5px 0;
        }
        .status {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 5px;
            color: #fff;
            display: inline-block;
        }
        .Đã\ giao { background-color: #28a745; }
        .Chưa\ giao { background-color: #ffc107; color: #000; }
        .Giao\ thất\ bại { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Đơn hàng của bạn</h2>

        <?php if(count($orders) == 0): ?>
            <p>Bạn chưa có đơn hàng nào.</p>
        <?php else: ?>
            <?php foreach($orders as $order): ?>
                <div class="order">
                    <?php if($order['image'] && file_exists($order['image'])): ?>
                        <img src="<?php echo $order['image']; ?>" alt="Ảnh xác nhận">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/100?text=Chưa+ảnh" alt="Chưa có ảnh">
                    <?php endif; ?>

                    <div class="order-info">
                        <p><strong>Mã đơn hàng:</strong> <?php echo $order['order_id']; ?></p>
                        <p><strong>Ngày đặt:</strong> <?php echo $order['date']; ?></p>
                        <p><strong>Tên khách hàng:</strong> <?php echo $order['customer_name']; ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo $order['phone']; ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo $order['address']; ?></p>
                        <p class="status <?php echo str_replace(' ', '\\ ', $order['status']); ?>">
                            <?php echo $order['status']; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

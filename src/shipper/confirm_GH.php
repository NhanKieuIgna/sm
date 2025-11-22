<?php
session_start();

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    $delivery_status = $_POST['delivery_status'] ?? '';
    $upload_dir = "uploads/";

    // Tạo thư mục uploads nếu chưa có
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_path = '';
    if (isset($_FILES['delivery_image']) && $_FILES['delivery_image']['error'] == 0) {
        $file_tmp = $_FILES['delivery_image']['tmp_name'];
        $file_name = basename($_FILES['delivery_image']['name']);
        $target_file = $upload_dir . time() . "_" . $file_name;

        // Kiểm tra định dạng file ảnh
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_types)) {
            if (move_uploaded_file($file_tmp, $target_file)) {
                $image_path = $target_file;
                $message = "Xác nhận giao hàng thành công!";
            } else {
                $message = "Lỗi khi tải lên ảnh.";
            }
        } else {
            $message = "Chỉ cho phép file ảnh: jpg, jpeg, png, gif.";
        }
    } else {
        $message = "Bạn chưa chọn ảnh.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận giao hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type=text], select, input[type=file] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xác nhận giao hàng</h2>

        <?php if(isset($message)): ?>
            <div class="message <?php echo isset($image_path) ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label for="order_id">Mã đơn hàng:</label>
            <input type="text" id="order_id" name="order_id" required>

            <label for="delivery_status">Trạng thái giao hàng:</label>
            <select id="delivery_status" name="delivery_status" required>
                <option value="delivered">Đã giao</option>
                <option value="failed">Giao thất bại</option>
            </select>

            <label for="delivery_image">Ảnh xác nhận giao hàng:</label>
            <input type="file" id="delivery_image" name="delivery_image" accept="image/*" required>

            <button type="submit">Xác nhận</button>
        </form>

        <?php if(isset($image_path) && $image_path != ''): ?>
            <h3>Ảnh đã tải lên:</h3>
            <img src="<?php echo $image_path; ?>" style="width:100%; border-radius:5px;">
        <?php endif; ?>
    </div>
</body>
</html>

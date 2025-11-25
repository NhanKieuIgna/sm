<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "secondhand_market";

$message = null;
$message_class = '';
$image_path = '';
$order_id = isset($_GET['id']) ? (string)$_GET['id'] : null;
if (!$order_id && !isset($_POST['order_id'])) {
    die("Lỗi: Không tìm thấy Mã Đơn Hàng.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'] ?? $order_id;
    $delivery_status = $_POST['delivery_status'] ?? '';
    $upload_success = false;
    $upload_dir = "sm/uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (isset($_FILES['delivery_image']) && $_FILES['delivery_image']['error'] == 0) {
        $file_tmp = $_FILES['delivery_image']['tmp_name'];
        $file_name = basename($_FILES['delivery_image']['name']);
        
        $target_file = $upload_dir . time() . "_" . uniqid() . "_" . $file_name; 
        
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_types)) {
            if (move_uploaded_file($file_tmp, $target_file)) {
                $image_path = $target_file;
                $upload_success = true;
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Cập nhật: Thêm cột ThoiGianHoanThanh = NOW()
                    $sql_update = "UPDATE danhsachdonhang SET TrangThaiDonHang = :status, AnhXacNhanGiaoHang = :image_path, ThoiGianHoanThanh = NOW() WHERE ID_DonHang = :id";
                    $stmt = $conn->prepare($sql_update);
                    
                    $stmt->bindParam(':status', $delivery_status);
                    $stmt->bindParam(':image_path', $image_path);
                    $stmt->bindParam(':id', $order_id, PDO::PARAM_STR); 
                    
                    $stmt->execute();
                    
                    $message = "Xác nhận giao hàng **(#" . htmlspecialchars($order_id) . ")** thành công! Trạng thái: " . htmlspecialchars($delivery_status);
                    $message_class = 'success';
                
                } catch (PDOException $e) {
                    $message = "Lỗi CSDL khi cập nhật đơn hàng: " . $e->getMessage();
                    $message_class = 'error';
                    if (file_exists($image_path)) {
                        unlink($image_path);
                        $image_path = '';
                    }
                }

            } else {
                $message = "Lỗi không xác định khi tải lên ảnh.";
                $message_class = 'error';
            }
        } else {
            $message = "Chỉ cho phép file ảnh: jpg, jpeg, png, gif.";
            $message_class = 'error';
        }
    } else {
        $message = "Bạn chưa chọn ảnh hoặc có lỗi trong quá trình tải lên.";
        $message_class = 'error';
    }
} 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
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
            color: #28a745;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type=text], select, input[type=file] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
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
            transition: background-color 0.3s;
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
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Xác nhận giao đơn hàng có MDH <?php echo htmlspecialchars($order_id); ?></h2> 

        <?php if($message): ?>
            <div class="message <?php echo htmlspecialchars($message_class); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            
            <label for="delivery_status">Trạng thái giao hàng:</label>
            <select id="delivery_status" name="delivery_status" required>
                <option value="DaGiao">Đã giao thành công</option>
                <option value="GiaoThatBai">Giao thất bại</option>
            </select>

            <label for="delivery_image">Ảnh xác nhận giao hàng:</label>
            <input type="file" id="delivery_image" name="delivery_image" accept="image/*" required>

            <button type="submit">Cập nhật trạng thái & Tải ảnh</button>
        </form>       
    <p style="text-align: center;">
        <a href="my_donhang.php" class="btn-back"> Quay lại trang Đơn hàng</a>
    </p>
    </div>
</body>
</html>


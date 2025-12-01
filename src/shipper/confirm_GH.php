<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "secondhand_market";

$message = null;
$message_class = '';
$order_id = isset($_GET['id']) ? (string)$_GET['id'] : null;

// Kiểm tra MDH
if (!$order_id && !isset($_POST['order_id'])) {
    die("Lỗi: Không tìm thấy Mã Đơn Hàng.");
}

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'] ?? $order_id;
    $delivery_status = $_POST['delivery_status'] ?? '';
    $failure_reason = $_POST['failure_reason'] ?? '';

    $image_path = ''; 
    $upload_success = false;
    $proceed_to_db_update = false;

    $is_delivered_successfully = ($delivery_status === 'DaGiao');
    $is_delivery_failed = ($delivery_status === 'GiaoThatBai');
    
    $db_status = $delivery_status; 

    // --- Xử lý tải lên ảnh cho trường hợp 'DaGiao' ---
    if ($is_delivered_successfully) {
        // SỬA ĐƯỜNG DẪN LƯU ẢNH TẠI ĐÂY: "../uploads/shipper_documents/"
        $upload_dir = "../uploads/shipper_documents/";
        
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
                    // Lưu đường dẫn tương đối để truy vấn từ CSDL
                    // Lưu ý: CSDL nên lưu đường dẫn tương đối (vd: uploads/shipper_documents/...) 
                    // hoặc chỉ tên file. Tôi sẽ lưu đường dẫn $target_file: ../uploads/shipper_documents/filename
                    $image_path = $target_file; 
                    $upload_success = true;
                    $proceed_to_db_update = true;
                    $db_status = 'DaGiao'; 
                } else {
                    $message = "Lỗi không xác định khi tải lên ảnh.";
                    $message_class = 'error';
                }
            } else {
                $message = "Chỉ cho phép file ảnh: jpg, jpeg, png, gif.";
                $message_class = 'error';
            }
        } else {
            $message = "Bạn chưa chọn ảnh xác nhận giao hàng cho trạng thái **Đã giao thành công**.";
            $message_class = 'error';
        }
    } 
    // --- Xử lý cho trường hợp 'GiaoThatBai' -> Chuyển thành 'DaHuy' ---
    elseif ($is_delivery_failed) {
        if (!empty(trim($failure_reason))) {
            $db_status = 'DaHuy'; // Đặt trạng thái DB là DaHuy
            $image_path = NULL; // Không có ảnh xác nhận
            $proceed_to_db_update = true;
        } else {
            $message = "Bạn phải cung cấp **Lý do thất bại** khi chọn trạng thái Giao thất bại.";
            $message_class = 'error';
        }
    } else {
        $message = "Trạng thái giao hàng không hợp lệ.";
        $message_class = 'error';
    }


    // --- Cập nhật CSDL ---
    if ($proceed_to_db_update) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // CÂU TRUY VẤN MỚI: KHÔNG CÓ ID_NguoiGiaoHang trong câu lệnh UPDATE
            $sql_update = "UPDATE danhsachdonhang 
                           SET TrangThaiDonHang = :status, 
                               AnhXacNhanGiaoHang = :image_path, 
                               LyDoHuy = :reason,
                               ThoiGianHoanThanh = NOW() 
                           WHERE ID_DonHang = :id";
            
            $stmt = $conn->prepare($sql_update);
            
            $stmt->bindParam(':status', $db_status);
            $stmt->bindParam(':id', $order_id, PDO::PARAM_STR); 
            
            // Khai báo biến NULL để ràng buộc chính xác kiểu dữ liệu NULL
            $null_value = null;

            if ($is_delivered_successfully) {
                // Thành công: lưu ảnh, LyDoHuy = NULL
                $stmt->bindParam(':image_path', $image_path);
                $stmt->bindParam(':reason', $null_value, PDO::PARAM_NULL);
            } elseif ($is_delivery_failed) {
                // Thất bại/Hủy: LyDoHuy = lý do, AnhXacNhanGiaoHang = NULL
                $stmt->bindParam(':image_path', $null_value, PDO::PARAM_NULL);
                $stmt->bindParam(':reason', $failure_reason); 
            }
            
            $stmt->execute();
            
            $status_display = ($is_delivered_successfully) ? 'Đã giao thành công' : 'Đã hủy do giao thất bại';
            $message = "Cập nhật đơn hàng **(#" . htmlspecialchars($order_id) . ")** thành công! Trạng thái: **" . $status_display . "**";
            if ($is_delivery_failed) {
                 $message .= "<br>Lý do thất bại đã được lưu vào cột **LyDoHuy**.";
            } 
            $message .= "<br>Thông tin Người giao hàng (`ID_NguoiGiaoHang`) được **giữ nguyên**.";
            $message_class = 'success';
        
        } catch (PDOException $e) {
            $message = "Lỗi CSDL khi cập nhật đơn hàng: " . $e->getMessage();
            $message_class = 'error';
            if ($is_delivered_successfully && $upload_success && file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }
} 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Xác nhận giao hàng</title>
    <style>
        /* CSS giữ nguyên */
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
        input[type=text], select, input[type=file], textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
        }
        textarea {
            resize: vertical;
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
        .hidden { display: none; }
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
            <select id="delivery_status" name="delivery_status" required onchange="toggleFields()">
                <option value="DaGiao" selected>Đã giao thành công</option>
                <option value="GiaoThatBai">Giao thất bại</option>
            </select>

            <div id="image_field">
                <label for="delivery_image">Ảnh xác nhận giao hàng:</label>
                <input type="file" id="delivery_image" name="delivery_image" accept="image/*" required>
            </div>
            
            <div id="reason_field" class="hidden">
                <label for="failure_reason">Lý do thất bại (Sẽ lưu vào LyDoHuy):</label>
                <textarea id="failure_reason" name="failure_reason" rows="3" maxlength="255"></textarea>
            </div>

            <button type="submit">Cập nhật trạng thái</button>
        </form> 
        
        <p style="text-align: center;">
            <a href="my_donhang.php" class="btn-back"> Quay lại trang Đơn hàng</a>
        </p>
    </div>

    <script>
        function toggleFields() {
            const status = document.getElementById('delivery_status').value;
            const imageField = document.getElementById('image_field');
            const imageInput = document.getElementById('delivery_image');
            const reasonField = document.getElementById('reason_field');
            const reasonInput = document.getElementById('failure_reason');

            if (status === 'DaGiao') {
                imageField.classList.remove('hidden');
                imageInput.setAttribute('required', 'required'); 
                
                reasonField.classList.add('hidden');
                reasonInput.removeAttribute('required'); 
            } else if (status === 'GiaoThatBai') {
                imageField.classList.add('hidden');
                imageInput.removeAttribute('required'); 
                imageInput.value = ''; 

                reasonField.classList.remove('hidden');
                reasonInput.setAttribute('required', 'required'); 
            }
        }

        document.addEventListener('DOMContentLoaded', toggleFields);
    </script>
</body>
</html>

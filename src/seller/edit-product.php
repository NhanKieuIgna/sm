<?php
    require_once 'header.php'; 
    $page = 'all-product'; 

    if (isset($_GET['id'])) {
        $id_sp = mysqli_real_escape_string($conn, $_GET['id']);
        if(isset($_SESSION['user_id'])){
            $user_id = $_SESSION['user_id'];
        } else {
             echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>"; exit();
        }

        $sql_get = "SELECT * FROM sanpham WHERE ID_SanPham = '$id_sp' AND ID_NguoiBan = '$user_id'";
        $result_get = mysqli_query($conn, $sql_get);

        if (mysqli_num_rows($result_get) > 0) {
            $product = mysqli_fetch_assoc($result_get);
        } else {
            echo "<script>alert('Sản phẩm không tồn tại hoặc bạn không có quyền sửa!'); window.location.href='all-product.php';</script>";
            exit();
        }
    } else {
        header("Location: all-product.php");
        exit();
    }

    if(isset($_POST['update-product'])) {
        $ten_sp      = mysqli_real_escape_string($conn, $_POST['name']);
        $id_danhmuc  = $_POST['category'];
        $soluong     = $_POST['quantity'];
        $gia         = $_POST['price']; 
        $tinhtrang   = $_POST['condition'];
        $mota        = mysqli_real_escape_string($conn, $_POST['description']);
        $mausac      = mysqli_real_escape_string($conn, $_POST['color']);
        $thuonghieu  = mysqli_real_escape_string($conn, $_POST['brand']);
        $kichthuoc   = mysqli_real_escape_string($conn, $_POST['size']);
        $diachi      = mysqli_real_escape_string($conn, $_POST['pickup_address']);

        if (!is_numeric($id_danhmuc)) {
            echo "<script>alert('Vui lòng chọn danh mục hợp lệ!'); history.back();</script>"; exit();
        }
        // Câu lệnh SQL cập nhật thông tin sản phẩm
        $sql_update = "UPDATE sanpham SET 
                        TenSanPham = '$ten_sp',
                        ID_DanhMuc = '$id_danhmuc',
                        SoLuong = '$soluong',
                        Gia = '$gia',
                        TinhTrang = '$tinhtrang',
                        MoTa = '$mota',
                        MauSac = '$mausac',
                        ThuongHieu = '$thuonghieu',
                        KichThuoc = '$kichthuoc',
                        DiaChiLayHang = '$diachi'
                       WHERE ID_SanPham = '$id_sp' AND ID_NguoiBan = '$user_id'";

        // Thêm ảnh mới
        if (mysqli_query($conn, $sql_update)) {
            $target_dir = "../img/";
            $files = $_FILES['product_images'];

            if (isset($files['name']) && is_array($files['name'])) {
                for ($i = 0; $i < count($files['name']); $i++) {
                    if (!empty($files['name'][$i]) && $files['error'][$i] == 0) {
                        
                        $filename = time() . "_" . $i . "_" . basename($files['name'][$i]);
                        $target_file = $target_dir . $filename;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                            $db_url = "../img/" . $filename;
                            $sql_img = "INSERT INTO hinhanhsanpham (ID_SanPham, URL_HinhAnh) VALUES ('$id_sp', '$db_url')";
                            mysqli_query($conn, $sql_img);
                        }
                    }
                }
            }

            echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='all-product.php';</script>";
        } else {
            echo "<script>alert('Lỗi SQL: " . mysqli_error($conn) . "');</script>";
        }
    }
?>


<div style="display: flex; min-height: 100vh; background-color: #f5f5f5;">
    <?php include 'sidebar.php'; ?>

    <div class="main-content" style="flex: 1; padding: 30px;">
        <h2 style="margin-bottom: 20px;">Cập nhật thông tin sản phẩm</h2>
        
        <div class="edit-container">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="form-row-split">
                    
                    <div class="form-col-half">
                        <h4 style="margin-bottom: 15px; color: #00bfa5; border-bottom: 2px solid #eee; padding-bottom: 10px;">Thông tin cơ bản</h4>

                        <div class="form-group">
                            <label class="form-label">Tên sản phẩm <span style="color:red">*</span></label>
                            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['TenSanPham']); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Danh mục <span style="color:red">*</span></label>
                            <select name="category" class="form-control" required>
                                <option value="">-- Chọn danh mục --</option>
                                <?php 
                                    $sql_dm = "SELECT * FROM danhmuc ORDER BY TenDanhMuc ASC";
                                    $res_dm = mysqli_query($conn, $sql_dm);
                                    while($row_dm = mysqli_fetch_assoc($res_dm)){
                                        $selected = ($row_dm['ID_DanhMuc'] == $product['ID_DanhMuc']) ? 'selected' : '';
                                        echo "<option value='".$row_dm['ID_DanhMuc']."' $selected>".$row_dm['TenDanhMuc']."</option>";
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="form-row-mini">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Giá (VNĐ) <span style="color:red">*</span></label>
                                <input type="number" name="price" class="form-control" required value="<?php echo $product['Gia']; ?>">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Số lượng <span style="color:red">*</span></label>
                                <input type="number" name="quantity" class="form-control" required value="<?php echo $product['SoLuong']; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tình trạng <span style="color:red">*</span></label>
                            <select name="condition" class="form-control">
                                <option value="Moi" <?php if($product['TinhTrang']=='Moi') echo 'selected'; ?>>Mới</option>
                                <option value="NhuMoi" <?php if($product['TinhTrang']=='NhuMoi') echo 'selected'; ?>>Như mới</option>
                                <option value="Tot" <?php if($product['TinhTrang']=='Tot') echo 'selected'; ?>>Tốt</option>
                                <option value="TrungBinh" <?php if($product['TinhTrang']=='TrungBinh') echo 'selected'; ?>>Trung bình</option>
                                <option value="Kem" <?php if($product['TinhTrang']=='Kem') echo 'selected'; ?>>Kém</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mô tả chi tiết <span style="color:red">*</span></label>
                            <textarea name="description" class="form-control" rows="8" required><?php echo htmlspecialchars($product['MoTa']); ?></textarea>
                        </div>
                    </div>
                    <div class="form-col-half">
                        <h4 style="margin-bottom: 15px; color: #00bfa5; border-bottom: 2px solid #eee; padding-bottom: 10px;">Thông tin khác</h4>

                        <div class="form-group">
                            <label class="form-label">Địa chỉ lấy hàng <span style="color:red">*</span></label>
                            <input type="text" name="pickup_address" class="form-control" required value="<?php echo htmlspecialchars($product['DiaChiLayHang']); ?>">
                        </div>

                        <div class="form-row-mini">
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Màu sắc <span style="color:red">*</span></label>
                                <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($product['MauSac']); ?>">
                            </div>
                            <div class="form-group" style="flex:1">
                                <label class="form-label">Kích thước <span style="color:red">*</span></label>
                                <input type="text" name="size" class="form-control" value="<?php echo htmlspecialchars($product['KichThuoc']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Thương hiệu  <span style="color:red">*</span></label>
                            <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['ThuongHieu']); ?>">
                        </div>

                        <div class="upload-box">
                            <label class="form-label" style="cursor: pointer;">
                                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 30px; color: #00bfa5;"></i><br>
                                Tải thêm ảnh mới
                                <input type="file" name="product_images[]" multiple class="form-control" style="margin-top: 10px;">
                            </label>
                            <p style="font-size: 12px; color: #888; margin-top: 5px;">(Ảnh cũ vẫn giữ nguyên, chỉ tải lên nếu muốn thêm)</p>
                            
                            <div class="preview-images">
                                <?php
                                    $sql_img = "SELECT * FROM hinhanhsanpham WHERE ID_SanPham = '$id_sp'";
                                    $res_img = mysqli_query($conn, $sql_img);
                                    if(mysqli_num_rows($res_img) > 0){
                                        while($img = mysqli_fetch_assoc($res_img)){
                                            echo '<img src="'.$img['URL_HinhAnh'].'" alt="Ảnh cũ">';
                                        }
                                    } else {
                                        echo '<span style="font-size:12px; color:#999;">Chưa có ảnh nào</span>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    </div> 
                <div class="form-actions">
                    <a href="all-product.php" class="btn-cancel">Hủy bỏ</a>
                    <button type="submit" name="update-product" class="btn-submit">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
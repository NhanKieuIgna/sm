<?php
    require_once 'header.php'; 
    require_once __DIR__ . '/../../database/db.php';
    if(isset($_POST['a-product'])) {
        if (empty($_POST['category'])) {
            echo "<script>alert('Vui lòng chọn danh mục!'); window.history.back();</script>";
            exit();
        }
        $id_nguoiban = $_SESSION['user_id'];
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

        // Bắt buộc phải có ít nhất 1 ảnh
        if (empty($_FILES['product_images']['name'][0])) {
            echo "<script>alert('Vui lòng chọn ít nhất một ảnh cho sản phẩm!'); window.history.back();</script>";
            exit();
        }

        // Báo lỗi nếu trường nào bị bỏ trống
        if (empty($id_danhmuc) || empty($ten_sp) || empty($gia) || empty($soluong) || 
            empty($mota) || empty($mausac) || empty($thuonghieu) || 
            empty($kichthuoc) || empty($diachi)) {
            
            echo "<script>alert('Vui lòng nhập đầy đủ tất cả các thông tin bắt buộc!'); window.history.back();</script>";
            exit(); 
        }

        
        $sql = "INSERT INTO sanpham (ID_NguoiBan, TenSanPham, MoTa, Gia, TinhTrang, SoLuong, DiaChiLayHang, KichThuoc, ID_DanhMuc, TrangThaiDangBan, MauSac, ThuongHieu, NgayTao) 
                VALUES ('$id_nguoiban', '$ten_sp', '$mota', '$gia', '$tinhtrang', '$soluong', '$diachi', '$kichthuoc', '$id_danhmuc', 'ChoDuyet', '$mausac', '$thuonghieu', NOW())";

 if (mysqli_query($conn, $sql)) {
            $id_sp = mysqli_insert_id($conn); // Lấy ID vừa tạo

            $target_dir = "../img/";
            // Kiểm tra và tạo thư mục nếu chưa có(tránh lỗi nếu lỡ tay xóa thư mục)
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $files = $_FILES['product_images'];

            // Duyệt qua từng file
            if (isset($files['name']) && is_array($files['name'])) {
                for ($i = 0; $i < count($files['name']); $i++) {
                    
                    // Kiểm tra có tên file và không có lỗi upload
                    if (!empty($files['name'][$i]) && $files['error'][$i] == 0) {
                        
                        // Đặt tên file: time_sốthứtự_tênfile (thêm $i để tránh trùng nếu up nhiều ảnh cùng lúc)
                        $filename = time() . "_" . $i . "_" . basename($files['name'][$i]);
                        $target_file = $target_dir . $filename;
                        
                        // Kiểm tra chỉ khi di chuyển file thành công mới lưu vào DB
                        if (move_uploaded_file($files['tmp_name'][$i], $target_file)) {
                            
                            // Lưu đường dẫn vào DB
                            $db_url = "../img/" . $filename;
                            
                            // Insert vào bảng hinhanhsanpham
                            $sql_img = "INSERT INTO hinhanhsanpham (ID_SanPham, URL_HinhAnh) VALUES ('$id_sp', '$db_url')";
                            mysqli_query($conn, $sql_img);
                        }
                    }
                }
            }

            echo "<script>alert('Đăng bán thành công!'); window.location.href='add-product.php';</script>";
        } else {
            echo "<script>alert('Lỗi SQL: " . mysqli_error($conn) . "');</script>";
        }
    }
?>

    <div class="title">
    <h2>Đăng bán sản phẩm</h2>
    <h3>Chắc chắn rằng bạn mô tả sản phẩm thật chính xác và đáng tin cậy!</h3>
    </div>
    <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST" enctype="multipart/form-data">
    

    <div class="form-section media-upload-section">
        <label for="product-images" class="required">Ảnh</label>
            <div class="upload-wrapper"> 
                <input type="file" id="product-images" name="product_images[]" multiple accept="image/*" style="display: none;">
        
            <div id="image-gallery-container" class="upload-gallery-container empty-center">
            <label for="product-images" class="upload-dropzone add-more-dropzone">
                <i class="fa-solid fa-camera-retro camera-icon"></i>
                <p>Tải lên hình ảnh</p>
            </label>
        </div>
    </div>
</div>

    <div class="form-grid">
        <div class="col-left">
            <div class="form-group row-group">
                <div class="input-half">
                    <label for="category" class="required">Danh mục</label>
                    <select id="category" name="category">
                        <option value="">Danh mục</option>
                        <?php 
                        if (!empty($categories)) {
                            foreach ($categories as $cat) {
                                echo '<option value="'.$cat['ID_DanhMuc'].'">'.$cat['TenDanhMuc'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="input-half quantity-control">
                    <label for="quantity" class="required">Số lượng</label>
                    <div class="quantity-input-group">
                        <button type="button" class="btn-qty">-</button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1">
                        <button type="button" class="btn-qty">+</button>
                    </div>
                </div>
            </div>

            <div class="form-group row-group">
                <div class="input-half">
                    <label for="product-name" class="required">Tên sản phẩm</label>
                    <input type="text" id="product-name" name="name">
                </div>
                <div class="input-half">
                    <label for="price" class="required">Giá bán</label>
                    <input type="number" id="price" name="price" value="20000" min="20000">
                </div>
            </div>
            
            <div class="form-group">
                <label for="condition" class="required">Tình trạng</label>
                <select id="condition" name="condition">
                    <option>Mới</option>
                    <option>Như mới</option>
                    <option>Tốt</option>
                    <option>Trung bình</option>
                    <option>Kém</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="description" class="required">Mô tả</label>
                <textarea id="description" name="description" rows="8"></textarea>
            </div>
        </div>

        <div class="col-right">
            <h3>Thông tin khác</h3>
            
            <div class="form-group">
                <label for="color" class="required">Màu sắc</label>
                <input type="text" id="color" name="color">
            </div>
            <div class="form-group">
                <label for="brand" class="required">Thương hiệu</label>
                <input type="text" id="brand" name="brand">
            </div>
            <div class="form-group">
                <label for="size" class="required">Kích cỡ</label>
                <input type="text" id="size" name="size">
            </div>

            <h3 style="margin-top: 20px;">Vận chuyển</h3>
            <div class="form-group">
                <label for="pickup-address" class="required">Địa chỉ lấy hàng</label>
                <input type="text" id="pickup-address" name="pickup_address">
            </div>
        </div>
    </div>

    <button type="submit" class="btn-submit" name="a-product">Đăng bán</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('product-images');
    const imageGalleryContainer = document.getElementById('image-gallery-container');
    const addMoreDropzone = document.querySelector('.add-more-dropzone');
    
    // Tạo thùng chứa ảo để chứa các file
    const dataTransfer = new DataTransfer();

    function updateContainerState() {
        const currentImages = imageGalleryContainer.querySelectorAll('.uploaded-image-item').length;
        if (currentImages === 0) {
            imageGalleryContainer.classList.add('empty-center');
        } else {
            imageGalleryContainer.classList.remove('empty-center');
        }
    }

    function handleFileSelection(event) {
        const files = event.target.files;
        if (files.length === 0) return;

        for (const file of files) {
            if (file.type.startsWith('image/')) {
                // Thêm file ảnh vào thùng chứa ảo
                dataTransfer.items.add(file);

                // Tạo ảnh xem trước
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Truyền thêm đối tượng 'file' vào để sau này biết mà xóa
                    const imageItem = createPreviewElement(e.target.result, file.name, file);
                    imageGalleryContainer.insertBefore(imageItem, addMoreDropzone);
                    updateContainerState();
                };
                reader.readAsDataURL(file);
            }
        }


        fileInput.files = dataTransfer.files;
    }
    
    // Thêm tham số 'fileObj' để biết chính xác file nào cần xóa
    function createPreviewElement(src, fileName, fileObj) {
        const imageItem = document.createElement('div');
        imageItem.classList.add('uploaded-image-item');
        
        const img = document.createElement('img');
        img.src = src;
        img.alt = fileName;

        const removeBtn = document.createElement('button');
        removeBtn.classList.add('remove-image-btn');
        removeBtn.innerHTML = '&times;'; 
        removeBtn.type = "button"; // Quan trọng: chặn submit form

        removeBtn.addEventListener('click', function() {
            // Xóa giao diện
            imageItem.remove(); 
            updateContainerState();

            // Tạo một thùng mới
            const newDataTransfer = new DataTransfer();
            
            // Duyệt qua thùng cũ, giữ lại những file KHÔNG phải là file đang xóa
            for (let i = 0; i < dataTransfer.files.length; i++) {
                const file = dataTransfer.files[i];
                if (file !== fileObj) {
                    newDataTransfer.items.add(file);
                }
            }
            
            // Cập nhật lại thùng chứa chính và Input
            dataTransfer.items.clear();
            for (let i = 0; i < newDataTransfer.files.length; i++) {
                dataTransfer.items.add(newDataTransfer.files[i]);
            }
            fileInput.files = dataTransfer.files;
        });

        imageItem.appendChild(img);
        imageItem.appendChild(removeBtn);
        return imageItem;
    }

    if (fileInput && imageGalleryContainer && addMoreDropzone) {
        fileInput.addEventListener('change', handleFileSelection);
        updateContainerState(); 
    }
});
</script>

<?php
    require 'footer.php';
?>

<?php
    require 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm</title>
    <link rel="stylesheet" href="/sm-demo/src/css/add-product.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="title">
    <h2>Đăng bán sản phẩm</h2>
    <h3>Chắc chắn rằng bạn mô tả sản phẩm thật chính xác và đáng tin cậy!</h3>
    </div>
    <form action="process_add_product.php" method="POST" enctype="multipart/form-data">
    

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
                        <option>Danh mục</option>
                        <option>Quần áo</option>
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
                    <input type="text" id="price" name="price" value="20.000 VNĐ">
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

    <button type="submit" class="btn-submit">Đăng bán</button>
</form>
<?php
    require 'footer.php';
?>

<script>
 document.addEventListener('DOMContentLoaded', function() {
    // 1. Lấy các phần tử cần thiết
    const fileInput = document.getElementById('product-images');
    const imageGalleryContainer = document.getElementById('image-gallery-container');
    const addMoreDropzone = document.querySelector('.add-more-dropzone');
    
    // Khởi tạo biến đếm để theo dõi số lượng ảnh đã tải
    let imageItemCount = 0; 
    
    // Hàm cập nhật trạng thái container (căn giữa/xếp gọn)
    function updateContainerState() {
        // Đếm số lượng ảnh đã được chèn vào container (ngoại trừ dropzone)
        const currentImages = imageGalleryContainer.querySelectorAll('.uploaded-image-item').length;
        imageItemCount = currentImages;

        if (imageItemCount === 0) {
            // Khi không có ảnh: Căn giữa
            imageGalleryContainer.classList.add('empty-center');
        } else {
            // Khi đã có ảnh: Xếp gọn sang trái
            imageGalleryContainer.classList.remove('empty-center');
        }
    }

    // Hàm xử lý tải lên (như cũ, nhưng gọi updateContainerState)
    function handleFileSelection(event) {
        const files = event.target.files;
        if (files.length === 0) {
            // Nếu người dùng đóng hộp thoại mà không chọn file, vẫn cập nhật trạng thái
            updateContainerState(); 
            return;
        }

        for (const file of files) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageItem = createPreviewElement(e.target.result, file.name);
                    imageGalleryContainer.insertBefore(imageItem, addMoreDropzone);
                    updateContainerState(); // Cập nhật ngay sau khi ảnh được thêm
                };
                reader.readAsDataURL(file);
            }
        }
        event.target.value = ''; // Reset input
    }
    
    // Hàm tạo phần tử xem trước và nút xóa (phần quan trọng nhất)
    function createPreviewElement(src, fileName) {
        const imageItem = document.createElement('div');
        imageItem.classList.add('uploaded-image-item');
        
        const img = document.createElement('img');
        img.src = src;
        img.alt = fileName;

        const removeBtn = document.createElement('button');
        removeBtn.classList.add('remove-image-btn');
        removeBtn.innerHTML = '&times;'; 

        removeBtn.addEventListener('click', function() {
            imageItem.remove(); // Xóa ảnh
            updateContainerState(); // Cập nhật trạng thái sau khi xóa
        });

        imageItem.appendChild(img);
        imageItem.appendChild(removeBtn);
        return imageItem;
    }

    // Gán sự kiện cho input và dropzone
    if (fileInput && imageGalleryContainer && addMoreDropzone) {
        fileInput.addEventListener('change', handleFileSelection);

        // Khởi tạo trạng thái ban đầu (nên là center)
        updateContainerState(); 
    }
});
</script>
</body>
</html>

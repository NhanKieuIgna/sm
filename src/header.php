    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <a href="index.php" class="logo">
                <div class="logo-icon"><img src="./img/logo.jpg" alt="logo"></div>
                
            </a>
            
            <div class="category-dropdown">
                <button class="category-btn">Tất cả danh mục</button>
                <div class="category-menu">
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <a href="user/list-product.php?category=<?php echo $category['ID_DanhMuc']; ?>"><?php echo htmlspecialchars($category['TenDanhMuc']); ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a href="#">Chưa có danh mục</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="search-container">
                <form action="user/list-product.php" method="GET" style="display: flex; align-items: center; width: 100%;">
                    <input type="text" name="search" class="search-bar" placeholder=" Tìm kiếm" id="searchInput" autocomplete="off">
                    <button type="submit" style="background: none; border: none; cursor: pointer;">
                        <span class="search-icon">&#x1F50E;&#xFE0E;</span>
                    </button>
                    <div id="searchSuggestions" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                </form>
            </div>

            <div class="user-actions">
                <a href="#" class="bell-icon"><img class="bell-icon-img" src="https://cdn-icons-png.flaticon.com/128/3602/3602145.png" alt="bell"></a>
                <span>|</span>
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'NguoiBan'): ?>

                    <ul class="menu">
                        <li><a href="#!"><?php echo $_SESSION['fullname']; ?></a>
                            <ul class="sub-menu">
                                <li><a href="seller/add-product.php">Thêm sản phẩm</a></li>
                                <li><a href="">Tất cả sản phẩm</a></li>
                                <li><a href="">Đơn bán</a></li>
                                <li><a href="">Doanh thu</a></li>
                                <li><a href="">Hồ sơ shop</a></li>
                                <li><a href="user/edit-profile.php">Chỉnh sửa hồ sơ</a></li>
                            </ul>   
                        </li>
                    </ul>
                      <span>|</span>
                    <a href="logout.php"><u>Đăng Xuất</u></a>
                    
                   
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <ul class="menu">
                        <li><a href="#!"><?php echo $_SESSION['fullname']; ?></a>
                            <ul class="sub-menu">
                                <li><a href="user/edit-profile.php">Chỉnh sửa hồ sơ</a></li>
                                <li><a href="user/my-orders.php">Đơn hàng của tôi</a></li>
                            </ul>   
                        </li>
                    </ul>

                   
                    <span>|</span>
                    <a href="logout.php"><u>Đăng Xuất</u></a>
                <?php else: ?>    
                <a href="login.php"><u>Đăng nhập</u></a>
                <span>|</span>
                <a href="register.php"><u>Đăng ký</u></a>
                <?php endif; ?>
                <span>|</span>
                <a href="user/cart.php" class="cart-icon"><img class="cart-icon-img" src="https://cdn-icons-png.flaticon.com/128/1170/1170678.png" alt="cart"></a>
            </div>
        </div>

        <div class="nav-categories">
        
            <?php 
            $displayed_categories = array_slice($categories, 0, 6); // 6 danh mục đầu tiên
            $remaining_categories = array_slice($categories, 6); // Các danh mục còn lại
            
            if (!empty($displayed_categories)): 
                foreach ($displayed_categories as $category): 
            ?>
                <a href="user/list-product.php?category=<?php echo $category['ID_DanhMuc']; ?>"><?php echo htmlspecialchars($category['TenDanhMuc']); ?></a>
            <?php 
                endforeach;
                
                // Hiển thị các danh mục còn lại (ẩn ban đầu)
                if (!empty($remaining_categories)): 
                    foreach ($remaining_categories as $category): 
            ?>
                <a href="user/list-product.php?category=<?php echo $category['ID_DanhMuc']; ?>" class="extra-category" style="display: none;"><?php echo htmlspecialchars($category['TenDanhMuc']); ?></a>
            <?php 
                    endforeach;
                endif;
                
                // Nút "Tất cả danh mục" nếu có nhiều hơn 6 danh mục
                if (!empty($remaining_categories)): 
            ?>
                <a href="#" id="toggleCategories" style="color: #ff6b6b; font-weight: 500;">Tất cả danh mục ▼</a>
            <?php 
                endif;
            else: 
            ?>
                <a href="#">Chưa có danh mục</a>
            <?php endif; ?>
        </div>
    </header>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .sidebar {
        width: 250px;
        background: #fff;
        min-height: 100vh;
        border-right: 1px solid #e0e0e0;
        padding: 20px 10px;
        flex-shrink: 0; 
    }
    .menu-title {
        font-size: 13px;
        color: #38b744ff;
        margin: 10px 15px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: #333;
        text-decoration: none;
        font-size: 15px;
        border-radius: 8px;
        margin-bottom: 5px;
        transition: 0.3s;
    }
    .menu-item i {
        width: 30px; 
        font-size: 18px;
        color: #555;
    }
    .menu-item:hover {
        background-color: #f0f0f0;
    }

    .menu-item.active {
        background-color: #e3fdfd; 
        color: #00bfa5; 
        font-weight: bold;
    }
    .menu-item.active i {
        color: #00bfa5;
    }
</style>

<div class="sidebar">
    <div style="padding: 0 15px 20px; border-bottom: 1px solid #eee; margin-bottom: 15px; font-size: 15px;">
        <strong>Menu Quản Lý</strong>
    </div>

    <div class="menu-title">Sản phẩm</div>
    
    <a href="all-products.php" class="menu-item <?php if($page == 'all-products') echo 'active'; ?>">
        <i class="fa-solid fa-box-open"></i> Tất cả sản phẩm
    </a>

    <div class="menu-title">Bán hàng</div>

    <a href="order-list.php" class="menu-item <?php if($page == 'order-list') echo 'active'; ?>">
        <i class="fa-solid fa-clipboard-list"></i> Danh sách đơn hàng
    </a>

    <div class="menu-title">Thống kê</div>

    <a href="sales-summary.php" class="menu-item <?php if($page == 'sales-summary') echo 'active'; ?>">
        <i class="fa-solid fa-chart-pie"></i> Tổng kết doanh thu
    </a>

    <div class="menu-title">Hệ thống</div>

    <a href="shop-profile.php" class="menu-item <?php if($page == 'shop-profile') echo 'active'; ?>">
        <i class="fa-solid fa-store"></i> Hồ sơ Shop
    </a>
</div>
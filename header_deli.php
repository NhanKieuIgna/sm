<?php
session_start();
?>
<header>
    <div class="header-container">
        <div class="logo">
            <a href="dashboard.php">
                <img src="sm/logo.jpg" alt="Logo" class="logo-img">
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="delivery_schedule.php">Lịch giao hàng</a></li>
                <li><a href="profile.php">Hồ sơ</a></li>
                <li><a href="logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </div>
</header>

<style>
    header {
        background-color: #007bff;
        color: #fff;
        padding: 15px 0;
        margin-bottom: 4rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .header-container {
        width: 90%;
        margin: auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .logo-img {
        height: 50px; /* điều chỉnh kích thước ảnh */
        width: auto;
    }
    nav ul {
        list-style: none;
        display: flex;
        margin: 0;
        padding: 0;
    }
    nav ul li {
        margin-left: 20px;
    }
    nav ul li a {
        color: #fff;
        text-decoration: none;
        font-weight: 500;
    }
    nav ul li a:hover {
        text-decoration: underline;
    }
</style>

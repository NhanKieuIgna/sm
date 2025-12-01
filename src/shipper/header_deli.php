<header>
    
</header>
<div class="header-container">
        <div class="logo" style="display: flex;">
            <a href="delivery_index.php">
                <img src="../shipper/logo.jpg" alt="Logo" class="logo-img">
            </a>
            <h2 style="padding-left: 5px;">Delivery-Secondhand Market</h2>
        </div>
        <nav>
            <ul>
                <li><a href="delivery_index.php">Trang chủ</a></li>
                <li><a href="hoso.php">Hồ sơ</a></li>
                <li><a href="../logout.php">Đăng xuất</a></li>
            </ul>
        </nav>
    </div>
<style>

.header-container {
    position: sticky; 
    top: 0; 
    z-index: 1000; 
    background-color: white; 
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
    border-radius: 10px;
    padding: 1.5rem;
    width: 100%;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}
    .logo-img {
        height: 50px;
        width: auto;
    }
    .logo h2 {
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7); 
    }
    nav ul {
        list-style: none;
        display: flex;
        margin-right: 5rem;
        padding: 0;
    }
    nav ul li {
        margin-left: 20px;

    }
    nav ul li a {
        color: #040202ff;
        text-decoration: none;
        font-weight: 500;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
    }
    nav ul li a:hover {
        text-decoration: underline;
    }
</style>
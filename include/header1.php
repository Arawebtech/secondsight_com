<?php
// Add this at the top if not present
if (session_status() === PHP_SESSION_NONE) session_start();
$is_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['user_name']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
?>
<div class="navbar-area">

    <div class="mobile-nav">
        <a href="index.php" class="logo">
            <img src="assets/img/n-logo.png" class="main-logo" style="max-width: 206%; height: 60px;" alt="Logo" loading="lazy">
            <img src="assets/img/logoh.png" class="white-logo" alt="Logo" loading="lazy">
        </a>
    </div>

    <div class="main-nav justify-content-end">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-lg">
                <a class="navbar-brand" href="index.php">
                    <img src="assets/img/n-logo.png" class="main-logo" alt="Logo" style="height: 69px;" loading="lazy">
                    <img src="assets/img/logoh.png" class="white-logo" style="max-width: 82%;" alt="Logo" loading="lazy">
                </a>
                <div class="collapse navbar-collapse mean-menu" style="margin-left:60px;">
                    <ul class="navbar-nav m-auto">
                        <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="about.php" class="nav-link">About us</a></li>
                        <?php if (!$is_logged_in): ?>
                            <li class="nav-item"><a href="courses.php" class="nav-link">Courses</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a href="blog.php" class="nav-link">Blog</a></li>
                        <li class="nav-item"><a href="testimonial.php" class="nav-link">Testimonial</a></li>
                        <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                        <!--<li class="nav-item"><a href="apply.php" class="nav-link">Apply Now</a></li>-->

                        <div class="others-option">
                        <!-- Cart Icon -->
                        <div class="cart-icon">
                            <a href="javascript:void(0);" onclick="toggleCartDropdown()" class="cart-toggle">
                                <i class="flaticon-shopping-cart"></i>
                                <span id="cart-count"><?= count($_SESSION['cart']) ?></span>
                            </a>
                    
                            <!-- Dropdown Cart Items -->
                            <div id="cart-dropdown" class="cart-dropdown">
                                <?php if (!empty($courses)): ?>
                                    <ul class="cart-items">
                                        <?php foreach ($courses as $course): ?>
                                            <li class="cart-item">
                                                <div class="item-info">
                                                    <h6 class="cart-item-title"><?= htmlspecialchars($course['s_name']) ?></h6>
                                                    <span class="cart-item-price">₹<?= htmlspecialchars($course['price']) ?></span>
                                                    <small class="cart-item-quantity">Qty: <?= $_SESSION['quantities'][$course['id']] ?></small>
                                                </div>
                                                <a href="?remove_id=<?= $course['id'] ?>" class="remove-btn">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="cart-summary">
                                        <p class="cart-total">
                                            Total: <strong>₹<span id="dropdown-total"><?= number_format($total_price, 2) ?></span></strong>
                                        </p>
                                        <a href="cart.php" class="btn btn-sm w-100">View Cart</a>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-cart text-center">
                                        <i class="fas fa-shopping-basket fa-2x text-muted mb-2"></i>
                                        <p class="mb-1">No items in your cart</p>
                                        <small>Total: ₹0.00</small>
                                        <a href="courses.php" class="btn btn-outline-warning btn-sm mt-2 w-100">Browse Courses</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    
                        <!-- User Profile Section -->
                        <?php if ($is_logged_in): ?>
                            <div class="profile-dropdown">
                                <a href="/profile.php" title="Profile" class="profile-icon">
                                    <i class="fas fa-user-circle"></i>
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="custom-logins-btn default-btn me-2">Login</a>
                            <a href="register.php" class="custom-logins-btn default-btn">Register</a>
                        <?php endif; ?>
                    </div>


                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" media="print" onload="this.media='all'">



    <div class="others-option-for-responsive">
        <div class="container">
            <div class="dot-menu">
                <div class="inner">
                   <!-- Mobile Cart Icon -->
<div class="mobile-cart-dropdown">
    <a href="cart.php">
        <i class="flaticon-shopping-cart"></i>
        <span id="cart-count"><?= count($_SESSION['cart']) ?></span>
    </a>
</div>

                </div>


                <div class="register hide-mobile" style="margin-top: -17px;margin-left: -122px;">
    <?php if ($is_logged_in): ?>
        <a href="/profile.php" class="default-btn" style="font-size: 10px; padding: 10px 8px;">
            <i class="fas fa-user-circle"></i>
        </a>
    <?php else: ?>
        <a href="login.php" class="default-btn" style="font-size: 10px; padding: 2px 2px;">Login</a>
        <a href="register.php" class="default-btn" style="font-size: 10px; padding: 2px 2px;">Register</a>
    <?php endif; ?>
</div>


            </div>

        </div>

    </div>
</div>


<style>

.cart-toggle {
    text-decoration: none;
    color: #333;
    position: relative;
    font-size: 1.3rem;
}
#cart-count {
    background: #dc3545;
    color: #fff;
    font-size: 9px;
    padding: 1px 5px;
    border-radius: 50%;
    position: absolute;
    top: -6px;
    right: -10px;
}

/* Dropdown */
.cart-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 45px;
    width: 320px;
    background: #fff;
    border-radius: 8px;
    border-color: var(--main-color);
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    overflow: hidden;
    z-index: 1000;
}

/* Items */
.cart-items {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 250px;
    overflow-y: auto;
}
.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 12px;
    border-bottom: 1px solid #eee;
}
.cart-item:last-child {
    border-bottom: none;
}
.item-info {
    flex: 1;
    margin-right: 10px;
}
.cart-item-title {
    font-size: 14px;
    margin: 0 0 4px;
    font-weight: 600;
}
.cart-item-price {
    font-size: 13px;
    color: #333;
}
.cart-item-quantity {
    font-size: 12px;
    color: #777;
}
.remove-btn {
    color: #dc3545;
    font-size: 14px;
    text-decoration: none;
}
.remove-btn:hover {
    color: #a71d2a;
}

/* Summary */
.cart-summary {
    padding: 12px;
    background: #f9f9f9;
    text-align: right;
}
.cart-summary .cart-total {
    margin-bottom: 10px;
    font-size: 15px;
}

/* Empty State */
.empty-cart {
    padding: 20px;
}

</style>
<!-- Styles for Cart Dropdown -->
<style>
    @media (max-width: 768px) {
        .others-option-for-responsive {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            /* Spacing between cart and register/login */
            flex-direction: row;
        }

    }
</style>
<style>
    @media only screen and (max-width: 767px) {
    .cart-dropdown, #mobile-cart-dropdown {
        display: none;
        position: absolute;
        top: 50px;
        left: 0;
        width: 100% !important;
        background-color: #fff;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        border-radius: 0;
        z-index: 999;
        padding: 10px 15px;
    }

    .cart-dropdown ul,
    #mobile-cart-dropdown ul {
        padding: 0;
        margin: 0;
    }

    .cart-dropdown li,
    #mobile-cart-dropdown li {
        display: flex;
        flex-direction: column;
        border-bottom: 1px solid #eee;
        padding: 10px 0;
    }

    .cart-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .cart-item-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .cart-item-price,
    .cart-item-quantity {
        font-size: 14px;
        color: #444;
        margin-bottom: 4px;
    }

    .remove-btn {
        margin-top: 5px;
        color: #d9534f;
        font-size: 13px;
        align-self: flex-end;
    }

    .cart-summary {
        text-align: right;
        margin-top: 10px;
    }

    .cart-summary .btn {
        display: inline-block;
        background: var(--main-color);
        color:#fff;
        padding: 8px 16px;
        text-decoration: none;
        font-size: 14px;
        border-radius: 5px;
    }

    .cart-summary p {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
    }
}

    @media (max-width: 768px) {
    .hide-mobile {
        display: none !important;
    }
}

    .cart-icon {
        position: relative;
        cursor: pointer;
    }

    .cart-icon a {
        text-decoration: none;
        color: #000;
    }

    /*.cart-icon #cart-count {*/
    /*    background-color: #dc3545;*/
    /*    color: white;*/
    /*    padding: 1px 5px;*/
    /*    border-radius: 50%;*/
    /*    position: absolute;*/
    /*    top: -10px;*/
    /*    right: -10px;*/
    /*    font-size: 10px;*/
    /*}*/

    .cart-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 45px;
        width: 300px;
        background-color: #fff;
        border: 1px solid var(--main-color);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        z-index: 999;
    }

    .cart-dropdown ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .cart-dropdown li {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-item-title {
        font-size: 14px;
        margin: 0;
    }

    .cart-item-price {
        font-size: 14px;
        color: #333;
    }

    .remove-btn {
        background-color: transparent;
        color: red;
        border: none;
        cursor: pointer;
        font-size: 12px;
        padding: 0;
    }

    .cart-summary {
        padding: 10px;
        text-align: right;
    }

    .cart-summary p {
        margin: 0 0 10px;
    }

    .cart-summary .btn {
        display: inline-block;
        background: var(--main-color);
        color:#fff;
        padding: 5px 15px;
        text-decoration: none;
        border-radius: 5px;
    }

    .register {
        position: relative;
        display: flex;
        align-items: center;
        margin-left: 24px;
    }

 


    .profile-dropdown {
        position: relative;
        display: inline-block;
    }



    .profile-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        font-size: 2rem;
        color: #fff;
        background: linear-gradient(135deg, #1976d2 60%, #42a5f5 100%);
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(25, 118, 210, 0.10);
        transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
        border: 2px solid #fff;
    }
    .profile-icon:hover {
        transform: scale(1.08);
        background: linear-gradient(135deg, #1565c0 60%, #1976d2 100%);
        box-shadow: 0 4px 16px rgba(25, 118, 210, 0.18);
    }

    /* Dropdown Styles */
    .dropdown-toggle {
        display: none;
        /* Keep checkbox hidden */
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        border-radius: 8px;
        /* Rounded corners */
        min-width: 160px;
        margin-left: -60px;
        /* Adjust for proper alignment */
        opacity: 0;
        /* Hidden initially for fade-in effect */
        visibility: hidden;
        /* Hidden by default */
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    /* Show dropdown when toggle is checked */
    .dropdown-toggle:checked+.profile-icon+.dropdown-content {
        display: block;
        /* Show dropdown */
        opacity: 1;
        /* Fade-in effect */
        visibility: visible;
        /* Make it visible */
    }

    .dropdown-content a {
        display: block;
        padding: 12px 16px;
        color: #007bff;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s;
    }

    /* Link Hover Effect */
    .dropdown-content a:hover {
        background-color: #f1f1f1;
        color: #0056b3;
    }

    .logout-btn {
        background-color: #ffb607;
        /* Logout button color */
        color: #fff;
        border-radius: 5px;
        text-align: center;
        /* Center text */
    }

    .logout-btn:hover {
        background-color: #c82333;
        /* Darker red on hover */
    }


    /* Additional styles as needed */


    /* Styles for Mobile Cart Dropdown */
    .mobile-cart-dropdown {
        position: relative;
        cursor: pointer;
    }

    #mobile-cart-dropdown {
        display: none;
        /* Initially hidden */
        position: absolute;
        right: 0;
        top: 30px;
        width: 300px;
        /* Adjust as necessary */
        background-color: #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        border-radius: 5px;
        z-index: 999;
    }

    #mobile-cart-dropdown ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
</style>

<!-- JavaScript to Toggle Dropdown -->
<script>

    document.querySelector('.profile-icon').addEventListener('click', function () {
        const dropdown = document.querySelector('.dropdown-content');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });




    function toggleCartDropdown() {
        const dropdown = document.getElementById("cart-dropdown");
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    // Hide dropdown if clicked outside
    document.addEventListener("click", function (event) {
        const dropdown = document.getElementById("cart-dropdown");
        const target = event.target.closest(".cart-icon");
        if (!target) {
            dropdown.style.display = "none";
        }
    });


    function toggleMobileCartDropdown() {
        const dropdown = document.getElementById('mobile-cart-dropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';

        // Optionally, hide the desktop cart dropdown if it is open
        const desktopDropdown = document.getElementById("cart-dropdown");
        if (desktopDropdown) {
            desktopDropdown.style.display = 'none';
        }
    }
    document.addEventListener("click", function (event) {
    if (window.innerWidth <= 767) {
        const dropdown = document.getElementById("mobile-cart-dropdown");
        const target = event.target.closest(".mobile-cart-dropdown");
        if (!target) dropdown.style.display = "none";
    }
});

    // Hide dropdown if clicked outside
    document.addEventListener("click", function (event) {
        const dropdown = document.getElementById("mobile-cart-dropdown");
        const target = event.target.closest(".mobile-cart-dropdown");
        if (!target) {
            dropdown.style.display = "none";
        }
    });

</script>

<style>
    @media only screen and (max-width: 767px) {
    .cart-icon {
        display: none !important;
    }
}

@media only screen and (max-width: 767px) {
    .navbar-area .main-nav .navbar .navbar-brand img.main-logo {
        height: 40px !important;
        max-width: 120px !important;
    }
    .navbar-area .main-nav .navbar .navbar-brand img.white-logo {
        height: 40px !important;
        max-width: 100px !important;
    }
    .navbar-area .main-nav .navbar .navbar-brand {
        padding: 0 !important;
    }
    .navbar-area .main-nav .navbar-nav {
        flex-direction: column !important;
        align-items: flex-start !important;
        width: 100% !important;
    }
    .navbar-area .main-nav .navbar-nav .nav-item {
        width: 100% !important;
        margin-bottom: 4px;
    }
    .navbar-area .main-nav .navbar-nav .nav-link {
        width: 100% !important;
        font-size: 14px !important;
        padding: 6px 0 !important;
    }
    .others-option-for-responsive, .others-option {
        /*width: 90% !important;*/
        display: flex !important;
        /* flex-direction: column !important; */
        align-items: stretch !important;
        gap: 4px!important;
        align-self: center !important;
    }
    .cart-icon, .register {
        width: 6% !important;
        margin: 0 !important;
    }
    .cart-dropdown, .mobile-cart-dropdown {
        width: 100% !important;
        left: 0 !important;
        right: 0 !important;
    }
    .default-btn {
        width: 85% !important;
        margin-bottom: 8px;
        align-items: center;
        padding: 6px 10px !important; /* Less vertical padding */
        font-size: 14px !important;
        height: auto !important;
    }
}
</style>
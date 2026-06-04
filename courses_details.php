<?php
// session_start();
// include('include/cart_logic.php');
// include('admin/include/db_config.php');
// $user_id = $_SESSION['user_id'];

session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');

// Check login
$is_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['user_name']);
$user_id      = $is_logged_in ? $_SESSION['user_id'] : null;
$user_name    = $is_logged_in ? $_SESSION['user_name'] : '';

?>


<?php
$blog_url = isset($_GET['courses_url']) ? $_GET['courses_url'] : '';
$query = $conn->prepare("SELECT * FROM courses  WHERE url = ?");
$query->bind_param('s', $blog_url);
$query->execute();
$result_product = $query->get_result();

if ($result_product->num_rows > 0) {
    $row = $result_product->fetch_assoc();
    $s_name = htmlspecialchars($row['s_name']);
    $s_title = htmlspecialchars($row['s_title']);
    $url = htmlspecialchars($row['url']);
    $rating = htmlspecialchars($row['rating']);
    $price = htmlspecialchars($row['price']);
    $gst_percentage   = floatval($row['gst_percentage']);
    $gst_amount       = floatval($row['gst_amount']);
    $duration = htmlspecialchars($row['duration']);
    $short_description = $row['short_description'];
    $validity = $row['validity'];
    $duration_time = $row['duration_time'];

    $description = htmlspecialchars($row['description']);
    $thumbnail_image = htmlspecialchars($row['thumbnail_image']);
    $s_schema = htmlspecialchars($row['s_schema']);
    $meta_keyword = htmlspecialchars($row['meta_keyword']);

    $instructor_name = htmlspecialchars($row['instructor_name']);
    $inst_about = htmlspecialchars($row['inst_about']);

    // $bannerImagePath_inst = $base_url . "/assets/img/instructors/{$row['inst_img']}";
$bannerImagePath_inst = "/assets/img/instructors/" . rawurlencode($row['inst_img']);


    // $bannerImagePath = $base_url . "/assets/img/course-img/{$row['banner_image']}";
    $bannerImagePath = "/assets/img/course-img/" . rawurlencode($row['banner_image']);

} else {
    echo "course not found.";
    exit;
}

?>
<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');
?>
<style>
    body {
        background-color: #F5F5F5;
    }

    .single-course {
        background-color: #fff;
        background: linear-gradient(133.21deg, #F7F7F7 -2.44%, #F9F9F9 135.62%);
        box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #FFFFFF,
            2px 2px 10px rgba(255, 255, 255, 0.25),
            -4px -4px 20px rgba(255, 255, 255, 0.8),
            1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        box-sizing: border-box;
    }

    .single-course img {
        height: 280px;
    }

    .course-content {
        height: 245px;
        position: relative;
    }

    .course-content .btn-course-view {
        position: absolute;
        bottom: 10px;
        left: 10px;
    }

    /*.single-course .course-content .rating li a {*/
    /*    top: -3px;*/
    /*}*/

    .single-course .course-content .price {
        top: -55px;
        font-size: 17px;
    }

    /*.single-course .course-content .price span {*/
    /*    line-height: 55px;*/
    /*}*/

    .single-course .course-content .price del {
        color: #312B23;
        font-size: 15px;
        font-weight: 400;
        line-height: 0;
    }

    .single-course .course-content p {
        border-bottom: none;
        margin-bottom: 0;
        max-height: 90px;
    }

    .single-course .course-content .tag {
        max-height: unset;
    }

    @media only screen and (max-width: 767px) {
        .single-course .course-content .price {
            top: -55px;
            font-size: 16px;
            width: 80px;
            height: 40px;
            line-height: 43px;
        }

        .single-course img {
            width: 100% !important;
            height: auto !important;
            object-fit: cover;
        }

        /*.single-course .course-content .price span {*/
        /*    line-height: 45px;*/
        /*}*/

        .single-course .course-content .price del {
            font-size: 12px;
            line-height: 0;
        }

        .single-course-area .course-rating img {
            max-width: 112px;
        }

        .default-btn {
            padding: 15px;
        }

        .footer-logo-mob {
            max-width: 206%;
            height: 75px;
        }
    }

    @media only screen and (min-width: 768px) and (max-width: 1190px) {
        .single-course .course-content .price {
            top: -39px;
            font-size: 16px;
            width: 80px;
            height: 40px;
            line-height: 43px;
        }


        .single-course .course-content .price del {
            font-size: 12px;
            line-height: 0;
        }
    }

    .account-wrap {
        background-color: #fff;
        background: linear-gradient(133.21deg, #f7f7f7 -2.44%, #f9f9f9 135.62%);
        box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #ffffff,
            2px 2px 10px rgba(255, 255, 255, 0.25),
            -4px -4px 20px rgba(255, 255, 255, 0.8),
            1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }
    
     @media only screen and (min-width:567px) and (max-width: 776px) {
       .single-course img {
         height: auto;
         object-fit: cover;
         
       }
    }
</style>

<body>
<div class="navbar-area">

    <div class="mobile-nav">
        <a href="<?= $base_url; ?>index.php" class="logo">
            <img src="/assets/img/n-logo.png" class="main-logo" style="max-width: 206%; height: 60px;" alt="Logo" loading="lazy">
            <img src="/assets/img/logoh.png" class="white-logo" alt="Logo" loading="lazy">
        </a>
    </div>

    <div class="main-nav">
        <div class="container-fluid">
            <nav class="navbar navbar-expand-md">
                <a class="navbar-brand" href="<?= $base_url; ?>index.php">
                    <img src="/assets/img/n-logo.png" class="main-logo" alt="Logo" style="height: 69px;" loading="lazy">
                    <img src="/assets/img/logoh.png" class="white-logo" style="max-width: 82%;" alt="Logo" loading="lazy">
                </a>
                <div class="collapse navbar-collapse mean-menu" style="margin-left:60px;">
                    <ul class="navbar-nav m-auto">
                        <li class="nav-item"><a href="<?= $base_url; ?>index.php" class="nav-link">Home</a></li>
                        <li class="nav-item"><a href="<?= $base_url; ?>about.php" class="nav-link">About us</a></li>
                        <?php if (!$is_logged_in): ?>
                            <li class="nav-item"><a href="<?= $base_url; ?>courses.php" class="nav-link">Courses</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a href="<?= $base_url; ?>blog.php" class="nav-link">Blog</a></li>
                        <li class="nav-item"><a href="<?= $base_url; ?>testimonial.php" class="nav-link">Testimonial</a></li>
                        <li class="nav-item"><a href="<?= $base_url; ?>contact.php" class="nav-link">Contact</a></li>
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
                    <a href="<?= $base_url; ?>cart.php" class="btn btn-sm w-100">View Cart</a>
                </div>
            <?php else: ?>
                <div class="empty-cart text-center">
                    <i class="fas fa-shopping-basket fa-2x text-muted mb-2"></i>
                    <p class="mb-1">No items in your cart</p>
                    <small>Total: ₹0.00</small>
                    <a href="<?= $base_url; ?>courses.php" class="btn btn-outline-warning btn-sm mt-2 w-100">Browse Courses</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Profile Section -->
    <?php if ($is_logged_in): ?>
        <div class="profile-dropdown">
            <a href="<?= $base_url; ?>profile.php" title="Profile" class="profile-icon">
                <i class="fas fa-user-circle"></i>
            </a>
        </div>
    <?php else: ?>
        <a href="<?= $base_url; ?>login.php" class="custom-logins-btn default-btn me-2">Login</a>
        <a href="<?= $base_url; ?>register.php" class="custom-logins-btn default-btn">Register</a>
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
    <a href="<?= $base_url; ?>cart.php">
        <i class="flaticon-shopping-cart"></i>
        <span id="cart-count"><?= count($_SESSION['cart']) ?></span>
    </a>
</div>

                </div>


                <div class="register hide-mobile" style="margin-top: -17px;margin-left: -122px;">
    <?php if ($is_logged_in): ?>
        <a href="<?= $base_url; ?>profile.php" class="default-btn" style="font-size: 10px; padding: 10px 8px;">
            <i class="fas fa-user-circle"></i>
        </a>
    <?php else: ?>
        <a href="<?= $base_url; ?>login.php" class="default-btn" style="font-size: 10px; padding: 2px 2px;">Login</a>
        <a href="<?= $base_url; ?>register.php" class="default-btn" style="font-size: 10px; padding: 2px 2px;">Register</a>
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
    font-size: 10px;
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
    /*    background-color: red;*/
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
        function toggleCartDropdown() {
            const dropdown = document.getElementById("cart-dropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

        // Hide dropdown if clicked outside
        document.addEventListener("click", function(event) {
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

        // Hide dropdown if clicked outside
        document.addEventListener("click", function(event) {
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




    <!--<div class="page-title-area bg-4">-->
    <!--    <div class="container">-->
    <!--        <div class="page-title-content">-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->


    <section class="single-course-area ptb-50">
        <div class="container">
            <div class="section-title" style="margin-bottom: 40px;">
                <span style="display: inline-block; font-size: 18px; font-weight: bold; margin-bottom: 10px;">COURSE DETAILS</span>
                <h2 style="font-size: 27px; color: #333; font-weight: 600; margin: 0;"><?= $s_name; ?></h2>
                <div style="width: 350px; height: 4px; background-color: var(--main-color); margin: 10px auto 0;"></div>
            </div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="single-course-content">
                        <!--<h3>-->
                        <!--    <?= $s_name; ?>-->
                        <!--</h3>-->
                        <div class="row align-items-center">
                            <div class="col-lg-6 col-sm-4">
                                <div class="course-rating">
                                    <!--<img src="<?= $bannerImagePath_inst ?>" class="rounded-circle border bg-white"  alt="Image">-->
                               <img src="<?= $bannerImagePath_inst ?>" class="rounded-circle border bg-white" alt="Image">

                                    <h4 style="line-height:55px;">
                                        <a href="<?= $base_url; ?>team/<?= $instructor_slug; ?>">
                                            <?= $instructor_name; ?>
                                        </a>
                                    </h4>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-4">
                                <div class="course-rating star pl-0">
                                    <h4>Reviews</h4>
                                    <div class="product-review">
                                        <div class="rating">
                                            <!-- loop for rating star -->
                                            <?php
                                            for ($i = 0; $i < 5; $i++) {
                                                echo '<i class="bx bxs-star"></i>';
                                            }
                                            ?>
                                        </div>
                                        <!--<a href="" class="rating-count">-->
                                        <!--    5-->
                                        <!--</a>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <img src="<?= $bannerImagePath ?>" alt="Image">
                    </div>
                    <div class="tab single-course-tab">
                        <ul class="tabs">
                            <li>
                                <a href="javascript:;">Overview</a>
                            </li>
                            <li>
                                <a href="javascript:;"> Instructor</a>
                            </li>
                        </ul>
                        <div class="tab_content">
                            <div class="tabs_item">
                                <h3>Course Description</h3>
                                <p>
                                    <?= trim($short_description); ?>
                                </p>

                            </div>
                            <div class="tabs_item">
                                <div class="instructor-content">
                                    <div class="row align-items-center">
                                        <div class="col-lg-4">
                                            <div class="advisor-img">
                                                
                                                <img src="<?= $bannerImagePath_inst ?>" alt="Image">

                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <div class="advisor-content">
                                                <a href="">
                                                    <h3><?= $instructor_name; ?></h3>
                                                </a>

                                                <p><?= $inst_about; ?></p>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="account-wrap">
                        <ul>
    <?php if($gst_amount > 0): ?>
<li>
    GST (<?= $gst_percentage ?>%)
    <small style="color:green;">(Included in Price)</small>
    <span>₹<?= number_format($gst_amount, 2) ?></span>
</li>
<?php endif; ?>
                            <li>
                                Price <span class="bold">₹
                                    <?= $price ?>
                                </span>
                            </li>
                            <!--<li>-->
                            <!--    Courses Duration <span>-->
                            <!--        <?= $duration ?>-->
                            <!--    </span>-->
                            <!--</li>-->
                            <li style="padding-bottom:45px;">
                                Courses Name <span>
                                    <?= $s_name ?>
                                </span>
                            </li>
                            <li>
                                Validity <span>
                                    <?= $validity ?> Months
                                </span>
                            </li>
                            <li>
                                course hours <span>
                                    <?= $duration_time ?>
                                </span>
                            </li>

                            <li>
                                Website: <a
                                    href="https://secondsightfoundation.com">secondsightfoundation.com</a>
                            </li>
                        </ul>
                        <?php
                        if ($price === "000") {
                        ?>
                            <a href="https://wa.me/919990242970" target="_blank" class="default-btn"><i class="fa-brands fa-whatsapp"></i> Join Now</a>

                        <?php
                        } else {
                        ?>
                            <a href="#" class="default-btn" onclick="addToCart(<?= urlencode($row['id']); ?>)">Buy Now</a>
                        <?php
                        }
                        ?>

                        <!--<div class="social-content">-->
                        <!--    <p>-->
                        <!--        Share this course-->
                        <!--        <i class="bx bxs-share-alt"></i>-->
                        <!--    </p>-->
                        <!--    <ul>-->
                        <!--        <li>-->
                        <!--            <a href="https://www.facebook.com/" target="_blank">-->
                        <!--                <i class="bx bxl-facebook"></i>-->
                        <!--            </a>-->
                        <!--        </li>-->

                        <!--        <li>-->
                        <!--            <a href="https://www.instagram.com/" target="_blank">-->
                        <!--                <i class="bx bxl-instagram"></i>-->
                        <!--            </a>-->
                        <!--        </li>-->
                        <!--        <li>-->
                        <!--            <a href="https://www.behance.com/" target="_blank">-->
                        <!--                <i class="bx bxl-behance"></i>-->
                        <!--            </a>-->
                        <!--        </li>-->
                        <!--    </ul>-->
                        <!--</div>-->
                    </div>
                    <br>


                    <!--<div class="col-lg-4">-->
                    <div class="widget-sidebar">
                        <div class="sidebar-widget popular-post">
                            <h3 class="widget-title">Latest Courses</h3>

                            <div class="post-wrap">


                                <?php
                                $query = "SELECT * FROM courses ORDER BY created_date DESC, RAND() LIMIT 4";
                                $result = mysqli_query($conn, $query);

                                while ($row1 = mysqli_fetch_assoc($result)) {
                                    // $bannerImagePath1 = $base_url . "/assets/img/course-img/{$row1['banner_image']}";
                                      $bannerImagePath1 = "/assets/img/course-img/" . rawurlencode($row1['banner_image']);

                                ?>
                                    <div class="item">
                                        <a href="<?= $base_url; ?>courses/<?= $row1['url']; ?>" class="thumb">
                                            <span class="fullimage cover bg1" role="img"
                                                style="background-image: url('<?= $bannerImagePath1; ?>');"></span>
                                        </a>


                                        <div class="info">
                                            <h4 class="title">
                                                <a href="<?= $base_url; ?>courses/<?= $row1['url']; ?>">
                                                    <h5>
                                                        <?= $row1['s_title']; ?>
                                                    </h5> <!-- Dynamic description -->
                                                </a>

                                                <span class="date">
                                                    <?= $row1['created_date']; ?>
                                                </span>
                                            </h4>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>

                            </div>
                            <!--</div>-->

                        </div>

                    </div>


                </div>
            </div>
    </section>



    <section class="courses-area-style pb-70">
        <div class="container">
            <div class="section-title">
                <h2>Related Courses</h2>
            </div>
            <div class="row">
                <?php
                $query1 = "SELECT * FROM courses where status='Active' ORDER BY RAND() LIMIT 3";
                $result1 = mysqli_query($conn, $query1);

                while ($row2 = mysqli_fetch_assoc($result1)) {
                    // $bannerImagePath1 = $base_url . "/assets/img/course-img/{$row2['banner_image']}";
                      $bannerImagePath1 = "/assets/img/course-img/" . rawurlencode($row2['banner_image']);

                ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="single-course shadow">
                            <a href="<?= $base_url; ?>courses/<?= $row2['url']; ?>">
                                <img src="<?= $bannerImagePath1; ?>" alt="Image">
                            </a>
                            <div class="course-content">
                                <span class="price">
                                    <span> ₹ <?= $row2['price']; ?></span>
                                    <!--<small> <del class="total_price"><?= $row2['duration']; ?></del></small>-->
                                </span>
                                <!--<span class="tag">Education</span>-->
                                <a href="<?= $base_url; ?>courses/<?= $row2['url']; ?>">
                                    <h3>
                                        <?= $row2['s_name']; ?>
                                    </h3>
                                </a>
                                <ul class="rating">
                                    <!-- loop for rating star -->
                                    <?php
                                    for ($i = 0; $i < $row2['rating']; $i++) {
                                        echo ' <li>
                                        <i class="bx bxs-star"></i>
                                    </li>';
                                    }
                                    ?>
                                    <!--<li>-->
                                    <!--    <a href="<?= $base_url; ?>courses/<?= $row2['url']; ?>">-->
                                    <!--        <?= $row2['rating']; ?>-->
                                    <!--    </a>-->
                                    <!--</li>-->

                                </ul>
                                <span class="tag">
                                    <?php
                                    // Trim the short description to remove any leading or trailing whitespace
                                    $shortDesc = trim($row2['description']);
                                    if (strlen($shortDesc) > 180) {
                                        echo substr($shortDesc, 0, 180) . '...';
                                    } else {
                                        echo $shortDesc;
                                    }
                                    ?>
                                </span>
                                <div style="margin-top: 15px;" class="btn-course-view">
                                    <a href="<?= $base_url; ?>courses/<?= $row2['url'] ?>" class="default-btn" style="padding: 10px 20px;">
                                        Buy Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
        </div>
    </section>

 <style>
    /* Style for the sticky footer */
    .privacy-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        /* Ensure the footer takes full width */
        background-color: #1a1a1a;
        /* Dark background color */
        color: white;
        padding: 8px 20px;
        /* Padding around the content */
        display: flex;
        justify-content: space-between;
        /* Space out text and buttons */
        align-items: center;
        /* Vertically center the content */
        z-index: 9999;
        display: none;
        /* Hidden by default, shown when the user has not accepted the policy */
    }

    .footer-content {
        display: flex;
        /* Use flex to align the content horizontally */
        justify-content: space-between;
        /* Space out text and buttons */
        width: 100%;
        /* Full width of the footer */
        max-width: 1200px;
        /* Maximum width of the content */
        padding: 0 10px;
        /* Padding to ensure content is not flush with edges */
    }

    .footer-content p {
        flex: 1;
        /* Allow text to take up remaining space */
        margin: 0;
        text-align: left;
        /* Align text to the left */
        font-size: small;
    }

    .footer-buttons {
        display: flex;
        /* Keep buttons aligned in a row */
        margin-left: 20px;
        /* Space between the text and buttons */
    }

    .footer-buttons button {
        margin: 8px 5px;
        /* Margin between the buttons */
        padding: 0px 11px;
        /* Adjusted padding to make buttons thinner */
        background-color: #4CAF50;
        /* Green background color */
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 14px;
        /* Adjusted font size */
        transition: background-color 0.3s;
    }

    .footer-buttons button:hover {
        background-color: #45a049;
        /* Darker green shade on hover */
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            /* Stack text and buttons vertically */
            align-items: center;
            /* Center content */
            padding: 41px 0;
            /* Padding adjustments */
            margin-top: -35px;
        }

        .footer-content p {
            text-align: center;
            /* Center-align text on smaller screens */
            font-size: 14px;
            /* Adjust text size */
            margin-bottom: 10px;
            /* Add space below text */
        }

        .footer-buttons {
            justify-content: center;
            /* Center the buttons */
            margin-top: -10px;
            /* Space between text and buttons */
        }

        .footer-buttons button {
            margin: 5px;
            /* Reduce margin between buttons */
            font-size: 12px;
            /* Smaller font size for mobile */
            padding: 8px 15px;
            /* Adjust button padding */
        }
        .footer-logo-mob{
            max-width: 206%;
            height: 75px;
        }
    }

    @media (max-width: 480px) {
        .footer-content p {
            font-size: 8px;
            /* Make text smaller for very small screens */
            text-align: center;
            /* Center-align text */
        }

        .footer-buttons button {
            font-size: 9px;
            /* Even smaller font size for very small screens */
            padding: 4px 9px;
            /* Adjust button padding for small screens */
        }
        .footer-widget .address .location , .font-14-mobile{
            font-size:14px;
        }
        .footer-widget .address li a{
            font-size:14px;
        }
        .copyright-custom{
            text-align:unset;
        }
        p:last-child {
            margin-bottom: 0;
            text-align: center;
        }
    }
   #location-atag:hover{
    color: #fff;
   }

@media (max-width: 767px) {
    .privacy-footer {
        flex-direction: column !important;
        align-items: center !important;
        padding: 20px 5px !important;
    }
    .footer-content {
        flex-direction: column !important;
        align-items: center !important;
        padding: 10px 0 !important;
        margin-top: 0 !important;
    }
    .footer-content p {
        text-align: center !important;
        font-size: 13px !important;
        margin-bottom: 10px !important;
    }
    .footer-buttons {
        flex-direction: column !important;
        width: 100% !important;
        margin-left: 0 !important;
        gap: 8px;
    }
    .footer-buttons button {
        width: 100% !important;
        font-size: 15px !important;
        margin: 0 0 8px 0 !important;
    }
}
</style>



<footer class="footer-top-area pt-70 pb-30">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <!-- <h3>Find Us</h3> -->
                    <ul class="address p-0" style="margin-top:-26px;">
                        <li class="location p-3 mb-0">
                            <a href="<?= $base_url; ?>index.php">
                                <img src="/assets/img/flogo.webp" style="margin-left: -30px;" class="main-logo footer-logo-mob" alt="Logo" ></a>
                        </li>
                        <li class="location p-0"> Second Sight Foundation bridges ancient wisdom with modern science,
                            exploring the mind and universe through a scientific lens.
                        </li>

                    </ul>
                    <br>


                    <ul class="social-links">
                        <li style="margin-left: 0;">
                            <a href="https://www.instagram.com/secondsightfoundationdelhi/
" target="_blank"><i class="bx bxl-instagram" style="color: white;  font-size: 20px; "></i></a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/healingsoul.in/" target="_blank"><i
                                    class="bx bxl-facebook-square" style="color: white;  font-size: 20px; "></i></a>
                        </li>
                        <li>
                            <a href="https://www.youtube.com/@secondsightfoundation" target="_blank"><i
                                    class="fab fa-youtube" style="color: white;  font-size: 20px; "></i></a>
                        </li>
                        <li>
                            <a href="https://wa.me/9716517463" target="_blank"><i class="fab fa-whatsapp"
                                    style="color: white;  font-size: 20px; "></i></a>
                        </li>

                    </ul>
                    <!-- Font Awesome CSS -->
                    <link rel="stylesheet"
                        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

                    <style>
                        /* Ensure Boxicons or Font Awesome are loaded properly */
                        .social-links {
                            display: flex;
                            /* Display icons in a row */
                            justify-content: flex-start;
                            /* Align icons to the left */
                            gap: 15px;
                            /* Space between the icons */
                            list-style-type: none;
                            /* Remove list styling */
                            padding: 0;
                            /* Remove padding */
                            margin: 0;
                            /* Remove margin */
                        }

                        .social-links li {
                            display: inline-block;
                            /* Ensure each icon is treated as a block */
                        }

                        .social-links a {
                            font-size: 30px;
                            /* Set icon size */
                            color: #212121;
                            /* Set default icon color */
                            text-decoration: none;
                            /* Remove underline from links */
                        }

                        .social-links a:hover {
                            color: #ff7043;
                            /* Change icon color on hover */
                        }
                        /*@media (max-width:600px){*/
                        /*    .locat-padding{*/
                        /*        padding-left:0;*/
                        /*    }*/
                        /*}*/
                    
                    </style>
                    <link rel="stylesheet"
                        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Quick Links</h3>
                    <ul class="link">
                        <li>
                            <a href="<?= $base_url; ?>index.php">Home</a>
                        </li>
                        <li>
                            <a href="<?= $base_url; ?>about.php">About us
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_url; ?>testimonial.php">Testimonial
                            </a>
                        </li>
                        <li>
                            <a href="<?= $base_url; ?>contact.php">Contact us</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Useful links</h3>
                    <ul class="link">
                        <li>
                            <a href="<?= $base_url; ?>courses.php">Courses</a>
                        </li>
                        <li>
                            <a href="<?= $base_url; ?>teamlist.php">Our Team</a>
                        </li>
                        <li>
                            <a href="<?= $base_url; ?>view_gallery.php">View Gallery</a>
                        </li>
                        <li>
                            <a href="<?= $base_url; ?>blog.php">Blog</a>
                        </li>
<li>
 <a href="<?= $base_url; ?>terms-and-conditons.php" target="_blank">Terms & Conditions</a></li>
              <li>  <a href="<?= $base_url; ?>privacy-policy.php" target="_blank">Privacy Policy</a></li>
              <li>  <a href="<?= $base_url; ?>refund-and-returns-policy.php" target="_blank">Refund and Returns Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Find Us</h3>
                    <ul class="address">
                       <li>
                            <i class="fa-solid fa-user fa-sm" style="top:9px;"></i>
                            <span class="text-white">Contact Name: Jatin Sharma</span>
                        </li>
                        <li class="location locat-padding" style="padding-left:2rem;">
                            <i class="bx bxs-location-plus"></i>
                          <a href="" id="location-atag">  Metro Station Tagore Garden, AE-10, Ground Floor, Tagore Garden, Near Tagore Garden Metro
                            Station Gate Number 1 Exit, New Delhi, Delhi 110027 </a>
                        </li>
                        <li>
                            <i class="bx bxs-envelope"></i>
                            <a href="mailto:gurujimanishsharma@gmail.com">gurujimanishsharma@gmail.com</span></a>
                        </li>
                        <li>
                            <i class="bx bxs-phone-call"></i>
                            <a href="tel:+91-9716517463"> +91 9716517463</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>



<footer class="footer-bottom-area">
    <div class="container">
        <div class="copyright-wrap">
            <p class="font-14-mobile copyright-custom p-2">Copyright @ Second Sight Foundation. All Right Reserved. Design & Developed By <a
                    href="https://arawebtechnologies.com/" target="_blank">AraWebTechnologies. </a></p>
              
        </div>
        
    </div>
</footer>
</body>

</html>

<script>
    function showPopup() {
        alert("Course added to the cart successfully!");
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function addToCart(courseId) {
        // Show alert message
        alert('Course added successfully!');

        // Redirect to cart.php with the course ID
        window.location.href = "../cart.php?course_id=" + courseId;
    }
</script>
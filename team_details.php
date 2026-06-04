<?php
session_start();
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];
include('include/cart_logic.php');

?>

<?php
$blog_url = isset($_GET['team_url']) ? $_GET['team_url'] : '';
$query = $conn->prepare("SELECT * FROM team  WHERE url = ?");
$query->bind_param('s', $blog_url);
$query->execute();
$result_product = $query->get_result();

if ($result_product->num_rows > 0) {
    $row = $result_product->fetch_assoc();
    $name = htmlspecialchars($row['name']);
    $about = $row['about'];
    $url = htmlspecialchars($row['url']);
    $rating = htmlspecialchars($row['rating']);
    $price = htmlspecialchars($row['price']);
    $duration = htmlspecialchars($row['duration']);
    // $short_description = htmlspecialchars($row['short_description']);

    $description = $row['description'];
    $thumbnail_image = htmlspecialchars($row['thumbnail_image']);
    $s_schema = htmlspecialchars($row['s_schema']);
    $meta_keyword = htmlspecialchars($row['meta_keyword']);

    $bannerImagePath = $base_url . "../assets/img/team/{$row['image']}";
} else {
    echo "blog not found.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="zxx">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">

  

    <link rel="stylesheet" href="../assets/css/flaticon.css">

    <link rel="stylesheet" href="../assets/css/meanmenu.min.css">

   

    <link rel="stylesheet" href="../assets/css/style.css">

    

    <link rel="stylesheet" href="../assets/css/responsive.css">
<link rel="icon" href="<?=$base_url;?>../assets/img/logo-fav.png" type="image/png">

    <title>Second sight foundation</title>

</head>

<body>
    <div class="navbar-area">

        <div class="mobile-nav">
            <a href="index.php" class="logo">
                <img src="../assets/img/n-logo.png" class="main-logo" style="max-width: 206%; height: 44px;" alt="Logo" loading="lazy">
                <img src="assets/img/logoh.png" class="white-logo" alt="Logo" loading="lazy">
            </a>
        </div>

        <div class="main-nav">
            <div class="container-fluid">
                <nav class="navbar navbar-expand-md">
                    <a class="navbar-brand" href="index.php">
                        <img src="../assets/img/n-logo.png" class="main-logo" alt="Logo" style="height: 69px;" loading="lazy">
                        <img src="../assets/img/logoh.png" class="white-logo" style="max-width: 82%;" alt="Logo" loading="lazy">
                    </a>
                    <div class="collapse navbar-collapse mean-menu">
                        <ul class="navbar-nav m-auto">
                            <li class="nav-item"><a href="../index.php" class="nav-link">Home</a></li>
                            <li class="nav-item"><a href="../about.php" class="nav-link">About Us</a></li>
                            <li class="nav-item"><a href="../courses.php" class="nav-link">Courses</a></li>
                            <li class="nav-item"><a href="../blog.php" class="nav-link">Blog</a></li>
                            <li class="nav-item"><a href="../testimonial.php" class="nav-link">Testimonial</a></li>
                            <li class="nav-item"><a href="../contact.php" class="nav-link">Contact</a></li>
                            <!--<li class="nav-item"><a href="../apply.php" class="nav-link">Apply Now</a></li>-->

                            <div class="others-option">
                                <div class="cart-icon" style="position: relative;">
                                    <a href="javascript:void(0);" onclick="toggleCartDropdown()">
                                        <i class="flaticon-shopping-cart"></i>
                                        <span id="cart-count">
                                            <?= count($_SESSION['cart']) ?>
                                        </span>
                                    </a>
                                    <!-- Dropdown Cart Items -->
                                    <div id="cart-dropdown" class="cart-dropdown">
                                        <?php if (!empty($courses)): ?>
                                            <ul>
                                                <?php foreach ($courses as $course): ?>
                                                    <li>
                                                        <div class="cart-item">
                                                            <p class="cart-item-title">
                                                                <?= htmlspecialchars($course['s_name']) ?>
                                                            </p>
                                                            <p class="cart-item-price" data-course-id="<?= $course['id'] ?>">₹
                                                                <?= htmlspecialchars($course['price']) ?>
                                                            </p>
                                                            <p class="cart-item-quantity" data-course-id="<?= $course['id'] ?>">
                                                                Qty:
                                                                <?= $_SESSION['quantities'][$course['id']] ?>
                                                            </p>
                                                        </div>
                                                        <a href="?remove_id=<?= $course['id'] ?>" class="remove-btn">Remove</a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <div class="cart-summary">
                                                <p><strong>Total: ₹<span id="dropdown-total">
                                                            <?= number_format($total_price, 2) ?>
                                                        </span></strong></p>
                                                <a href="../cart.php" class="btn">View Cart</a>
                                            </div>
                                        <?php else: ?>
                                            <ul>
                                                <li>
                                                    <div class="cart-item empty-cart">
                                                        <p class="cart-item-title">No items in your cart</p>
                                                        <p class="cart-item-price">₹0.00</p>
                                                        <p class="cart-item-quantity">Qty: 0</p>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="cart-summary">
                                                <p><strong>Total: ₹<span id="dropdown-total">0.00</span></strong>
                                                </p>
                                                <a href="../cart.php" class="btn">View Cart</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- User Profile Section -->
                                <div class="register">
                                  
                                        <a href="../login.php" class="default-btn">Login</a>
                                    
                                </div>

                            </div>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <div class="others-option-for-responsive">
            <div class="container">
                <div class="dot-menu">
                    <div class="inner">
                        <div class="mobile-cart-dropdown" style="position: relative;">
                            <a href="javascript:void(0);" onclick="toggleMobileCartDropdown()">
                                <i class="flaticon-shopping-cart"></i>
                                <span id="cart-count">
                                    <?= count($_SESSION['cart']) ?>
                                </span>
                            </a>
                            <div id="mobile-cart-dropdown" class="cart-dropdown">
                                <!-- Mobile Cart items go here -->
                                <?php if (!empty($courses)): ?>
                                    <ul>
                                        <?php foreach ($courses as $course): ?>
                                            <li>
                                                <div class="cart-item">
                                                    <p class="cart-item-title">
                                                        <?= htmlspecialchars($course['s_name']) ?>
                                                    </p>
                                                    <p class="cart-item-price" data-course-id="<?= $course['id'] ?>">₹
                                                        <?= htmlspecialchars($course['price']) ?>
                                                    </p>
                                                    <p class="cart-item-quantity" data-course-id="<?= $course['id'] ?>">Qty:
                                                        <?= $_SESSION['quantities'][$course['id']] ?>
                                                    </p>
                                                </div>
                                                <a href="?remove_id=<?= $course['id'] ?>" class="remove-btn">Remove</a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="cart-summary">
                                        <p><strong>Total: ₹<span id="dropdown-total">
                                                    <?= number_format($total_price, 2) ?>
                                                </span></strong></p>
                                        <a href="cart.php" class="btn">View Cart</a>
                                    </div>
                                <?php else: ?>
                                    <ul>
                                        <li>
                                            <div class="cart-item empty-cart">
                                                <p class="cart-item-title">No items in your cart</p>
                                                <p class="cart-item-price">₹0.00</p>
                                                <p class="cart-item-quantity">Qty: 0</p>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="cart-summary">
                                        <p><strong>Total: ₹<span id="dropdown-total">0.00</span></strong></p>
                                        <a href="cart.php" class="btn">View Cart</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                     <div class="register">
                   
                    <a href="login.php" class="default-btn" style="margin-top: -17px;
    margin-left: -122px; font-size: 10px;
        padding: 10px 8px;">Login</a>
                
            </div>
                    
                </div>
            </div>
        </div>


    </div>

    <!-- Styles for Cart Dropdown -->
    <style>
        .cart-icon {
            position: relative;
            cursor: pointer;
        }

        .cart-icon a {
            text-decoration: none;
            color: #000;
        }

        .cart-icon #cart-count {
            background-color: red;
            color: white;
            padding: 2px 6px;
            border-radius: 50%;
            position: absolute;
            top: -10px;
            right: -10px;
            font-size: 12px;
        }

        .cart-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 30px;
            width: 300px;
            background-color: #fff;
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
            background-color: #28a745;
            color: white;
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
            font-size: 36px;
            color: #1d0f96;
            cursor: pointer;
            transition: transform 0.3s, color 0.3s;
        }

        .profile-icon:hover {
            transform: scale(1.1);
            color: #0056b3;
        }

        .dropdown-toggle {
            display: none;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            margin-top: 8px;
            border-radius: 5px;
            min-width: 160px;
            margin-left: -132px;
        }

        .dropdown-toggle:checked+.profile-icon+.dropdown-content {
            display: block;
        }

        .dropdown-content a {
            display: block;
            padding: 12px 16px;
            color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
            color: #0056b3;
        }

        .logout-btn {
            background-color: #ffb607;
            color: #fff;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

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
    <style>
        .img-container-tm {
            background: #fff url('../assets/img/imag/bg-tm2.webp') no-repeat center center;
            background-size: 135%;
            position: relative;
            border-radius: 50%;
            overflow: hidden;
        }

        .img-container-tm img {
            border-radius: 50%;
            position: absolute;
            bottom: -10px;
        }

        .teacher-name-head {
            display: none;
        }

        @media screen and (max-width:576px) {
            .img-container-tm {
                background: #fff url('../assets/img/bg-tm2.png') no-repeat center center;
                background-size: 135%;
                position: static;
                border-radius: 50%;

            }

            .img-container-tm img {
                border-radius: 50%;
                position: static;
                bottom: -10px;
            }

            #team-dtl-container {
                display: flex;
                flex-direction: column-reverse;
            }

            .single-course-tab .tabs {
                display: none;
            }

            .teacher-name-head {
                display: flex;
                justify-content: center;
                padding-bottom: 40px;
            }

            .teacher-name-head ul li {
                padding: 20px;
                color: #fff;
                font-size: 24px;
                font-weight: 500;
                display: block;
                border-radius: 7px;
            }
            .footer-logo-mob{
                max-width: 206%;
                height: 75px;
            }
        }
        .tabs_item ul{
            list-style:disc;
        }
    </style>
    <!-- JavaScript to Toggle Dropdown -->
    <script>
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

        // Hide dropdown if clicked outside
        document.addEventListener("click", function (event) {
            const dropdown = document.getElementById("mobile-cart-dropdown");
            const target = event.target.closest(".mobile-cart-dropdown");
            if (!target) {
                dropdown.style.display = "none";
            }
        });

    </script>
  

    <section class="single-course-area ptb-100" style="background:#000000e6;">
        <div class="container">
            <div class="teacher-name-head">
                <ul class="tabs" style=" background: -moz-linear-gradient(top, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        background: -webkit-linear-gradient(top, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        background: linear-gradient(to bottom, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);">
                    <li>
                        <a href="javascript:;" style="color:#fff;"><?= $name ?></a>
                    </li>
                </ul>
            </div>
            <div class="row" id="team-dtl-container">
                <div class="col-xl-8 col-lg-7 col-md-6 mt-4 col-12">

                    <div class="tab single-course-tab" style="height: 350px;

}">
                        <ul class="tabs" style=" background: -moz-linear-gradient(top, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        background: -webkit-linear-gradient(top, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);
        background: linear-gradient(to bottom, #FFB606 0%, #FCAC31 50%, #FAAB5C 100%);">
                            <li>
                                <a href="javascript:;" style="color:#fff;font-size: 26px;"><?= $name ?></a>
                            </li>
                        </ul>
                        <div class="tab_content">
                            <div class="tabs_item" style="color: #fff;">

                                <p style="color:#fff;">

                                    <?= $about ?>
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-5 col-md-6 col-12 img-container-tm">
                    <img src="<?= $bannerImagePath ?>" alt="" style="height: 405;
    width: 405;">
                </div>
            </div>
    </section>
    <style>
        @media (max-width: 768px) {
            .single-course-area {
                padding: 50px 20px;
                /* Adjust padding for mobile */
            }

            .col-xl-8,
            .col-lg-7,
            .col-md-6,
            .col-12 {
                width: 100%;
                /* Make columns full-width on mobile */
                margin-top: 20px;
                /* Add spacing between columns */
            }

            .tab.single-course-tab {
                height: auto;
                /* Let the height adjust automatically */
                width: 100%;
                /* Make the tab full-width on mobile */
            }

            .img-container-tm img {
                height: auto;
                /* Let the image height adjust */
                width: 100%;
                /* Make the image full-width */
                max-width: 100%;
                /* Ensure the image doesn't overflow */
                object-fit: cover;
                /* Ensure the image scales nicely */
            }
        }
    </style>

       <footer class="footer-top-area pt-70 pb-30">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <!-- <h3>Find Us</h3> -->
                    <ul class="address" style="margin-top:-26px;">
                        <li class="location">
                            <a href="index.php">
                                <img src="../assets/img/flogo.webp" class="main-logo footer-logo-mob" alt="Logo"></a>
                        </li>
                        <li class="location"> Second Sight Foundation bridges ancient wisdom with modern science,
                            exploring the mind and universe through a scientific lens.
                        </li>

                    </ul>
                    <br>


                    <ul class="social-links">
                        <li>
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
                            <a href="../index.php">Home</a>
                        </li>
                        <li>
                            <a href="../about.php">About us
                            </a>
                        </li>
                        <li>
                            <a href="../testimonial.php">Testimonial
                            </a>
                        </li>
                        <li>
                            <a href="../contact.php">Contact us</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Useful links</h3>
                    <ul class="link">
                        <li>
                            <a href="../courses.php">Courses</a>
                        </li>
                        <li>
                            <a href="../teamlist.php">Our Team</a>
                        </li>
                        <li>
                            <a href="../view_gallery.php">View Gallery</a>
                        </li>
                        <li>
                            <a href="../blog.php">Blog</a>
                        </li>
<li>
 <a href="terms-and-conditons.php" target="_blank">Terms & Conditions</a></li>
              <li>  <a href="privacy-policy.php" target="_blank">Privacy Policy</a></li>
              <li>  <a href="refund-and-returns-policy.php" target="_blank">Refund and Returns Policy</a></li>
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
                        <li class="location" style="padding-left:1rem">
                           <i class="fa-solid fa-location-dot fa-sm" style="top:9px;"></i>
                          <a href="" style="padding-left:1rem;">  Metro Station Tagore Garden, AE-10, Ground Floor, Tagore Garden, Near Tagore Garden Metro
                            Station Gate Number 1 Exit, New Delhi, Delhi 110027 </a>
                        </li>
                        <li>
                        <i class="fa-regular fa-envelope fa-sm" style="top:9px;"></i>
                            <a href="mailto:gurujimanishsharma@gmail.com">gurujimanishsharma@gmail.com</span></a>
                        </li>
                        <li>
                            <i class="fa-solid fa-phone-volume fa-sm" style="top:9px;"></i>
                            <a href="tel: 9716517463"> +91-9716517463</a>
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
            <p class="font-14-mobile">Copyright @ Second Sight Foundation. All Right Reserved. Design & Developed By <a
                    href="https://arawebtechnologies.com/" target="_blank">AraWebTechnologies. </a></p>
        </div>

    </div>
</footer>



   <script data-cfasync="false" src="../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="../assets/js/jquery.min.js" async></script>

    <script src="../assets/js/bootstrap.bundle.min.js" async></script>

    <script src="../assets/js/meanmenu.min.js" defer></script>

    <script src="../assets/js/owl.carousel.min.js" defer></script>

    <script src="../assets/js/wow.min.js" defer></script>

    <script src="../assets/js/nice-select.min.js" defer></script>

    <script src="../assets/js/magnific-popup.min.js" defer></script>

    <script src="../assets/js/jarallax.min.js" defer></script>

    <script src="../assets/js/appear.min.js" defer></script>

    <script src="../assets/js/odometer.min.js" defer></script>

    <script src="../assets/js/form-validator.min.js" defer></script>

    <script src="../assets/js/contact-form-script.js" defer></script>

    <script src="../assets/js/ajaxchimp.min.js" defer></script>

    <script src="../assets/js/custom.js" defer></script>
</body>

</html>
<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');
mysqli_query($conn, "SET NAMES 'utf8mb4'");
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$query = "SELECT * FROM courses WHERE status = 'Active' ORDER BY RAND()";

$result = mysqli_query($conn, $query);
mysqli_set_charset($conn, "utf8mb4");
?>


<!DOCTYPE html>
<html lang="zxx">
<?php include('include/head.php'); ?>
<style>
    body {
        background-color: #F5F5F5;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
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
        display: flex;
        flex-direction: column;
    }

    .single-course img {
        height: 280px;
    }

    .course-content {
        height: 280px;
        position: relative;
    }

    .course-content .btn-course-view {
        position: absolute;
        bottom: 10px;
        left: 10px;
    }

    .single-course .course-content .rating li a {
        top: -1px;
        font-size:14px;
    }

    .single-course .course-content .price {
        top: -39px;
        font-size: 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
    }

    .single-course .course-content .price span {
        line-height: 55px;
        color: #28a745;
        font-weight: bold;
    }

    .single-course .course-content .price del {
        color: #999;
        font-size: 14px;
        font-weight: 400;
        line-height: 20px;
        text-decoration: line-through;
        margin-top: -10px;
    }

    .single-course .course-content p {
        border-bottom: none;
        margin-bottom: 0;
        max-height: 90px;
    }

    .single-course .course-content .tag {
        max-height: unset;
    }

    .discount-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    @media only screen and (max-width: 767px) {
          .single-course .course-content .price {
            top: -39px;
            font-size: 16px;
            width: 80px;
            height: 50px;
            line-height: 25px;
        }

        .single-course .course-content .price span {
            line-height: 20px;
            font-size: 14px;
        }

        .single-course .course-content .price del {
            font-size: 12px;
            line-height: 15px;
        }
       .course-content .btn-course-view{
            position:relative;
            margin-left:0;
        }
       .course-content .btn-course-view a{
           
            margin-left:-9px;
        }
        .single-course .course-content {
            height:auto;
        }
        .single-course {
            box-shadow: none;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .single-course img {
            width: 100% !important;
            height: auto !important;
            object-fit: cover;
        }
        .course-content {
            height: auto !important;
            padding: 10px 5px !important;
        }
        .btn, .default-btn, button {
            width: 100% !important;
            font-size: 15px !important;
            margin-bottom: 10px;
        }
        .form-control {
            width: 100% !important;
            font-size: 15px !important;
        }
        .section-title h2, .section-title span {
            font-size: 18px !important;
        }
        .page-title-area, .courses-area-style {
            padding: 15px 0 !important;
        }
        .row, .container, .container-fluid {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        .col-lg-6, .col-lg-4, .col-md-6, .col-sm-6, .col-lg-3 {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            margin-bottom: 15px;
        }
    }

    @media only screen and (min-width: 768px) and (max-width: 1190px) {
        .single-course .course-content .price {
             top: -39px;
            font-size: 16px;
            width: 80px;
            height: 50px;
            line-height: 25px;
        }

        .single-course .course-content .price span {
            line-height: 20px;
            font-size: 14px;
        }

        .single-course .course-content .price del {
            font-size: 12px;
            line-height: 15px;
        }
    .courses-area-style{
        padding-top:75px;
    }
   }
    @media only screen and (min-width:567px) and (max-width: 776px) {
       .single-course img {
         height: auto;
         object-fit: cover;
         
       }
    }
    .price-container {
    display: flex; /* Aligns prices horizontally */
    align-items: baseline; /* Aligns text baselines for a clean look */
    gap: 10px; /* Adds space between the two price elements */
    margin-bottom: 10px; /* Space below the price container */
}

.discounted-price {
    color: #28a745; /* A striking green for discounted price */
    font-size: 1.5em; /* Larger font size for prominence */
    font-weight: bold; /* Make it bold */
}

.original-price {
    color: #ff6b6b; /* Softer red for the original price */
    font-size: 0.9em; /* Smaller than the discounted price */
    text-decoration: line-through; /* Strikethrough for the original price */
    margin-left: 5px; /* Adds a little space to the left of the original price */
}

/* Optional: Add some padding to the course content if needed */
.course-content {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
</style>

<body>
    <?php include('include/header1.php'); ?>
    <!--<div class="page-title-area bg-4">-->
    <!--    <div class="container">-->
    <!--        <div class="page-title-content">-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
    <section class="courses-area-style ptb-60">
        <div class="container">
            <div class="showing-result">
                <div class="row align-items-center">
                    <!--<div class="col-lg-6 col-md-4">-->
                    <!--    <div class="showing-result-count">-->
                            <!--<p>Showing 1-8 of 14 results</p>-->
                    <!--    </div>-->
                    <!--</div>-->
                  <div class="col-lg-3 col-md-4" style="position: relative; ">
    <!-- Search Input -->
    <input type="text" id="searchInput" 
           placeholder="Search courses..." 
           style="width: 100%; padding: 10px 40px; font-size: 16px; border: 2px solid #ccc; border-radius: 25px; outline: none; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1); transition: 0.3s;" 
           onfocus="this.style.borderColor='#4e54c8'" 
           onblur="this.style.borderColor='#ccc'" />

    <!-- Search Icon -->
    <span style="position: absolute; top: 50%; right: 25px; transform: translateY(-50%); font-size: 18px; color: #4e54c8;">
        🔍
    </span>

    <!-- Search Results Container -->
    <div id="searchResults" 
         style="position: absolute; top: 50px; left: 0; width: 100%; background: #fff; border: 1px solid #ccc; border-radius: 5px; max-height: 200px; overflow-y: auto; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1); display: none;">
        <!-- Dynamic Search Results Will Appear Here -->
    </div>
</div>


                    <!--<div class="col-lg-3 col-md-4">-->
                    <!--    <div id="searchResults"></div> -->
                    <!--</div>-->
                    
                </div>
            </div>
            <div class="row">
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    // $bannerImagePath = $base_url . "assets/img/course-img/{$row['banner_image']}";
         $bannerImagePath = "assets/img/course-img/" . rawurlencode($row['banner_image']);

                    
                    // Calculate discounted price (assuming 20% discount, you can modify this logic)
                    $originalPrice = $row['duration'];
                    $discountPercentage = isset($row['discount_percentage']) ? $row['discount_percentage'] : 0; // You can make this dynamic from database
                    // $discountedPrice = $originalPrice - ($originalPrice * $discountPercentage / 100);
                    $discountedPrice = $row['price'];
                    
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="single-course shadow">
                            <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>">
                                <img src="<?= $bannerImagePath; ?>" alt="Image">
                            </a>
                            
                            
                            
                        <div class="course-content">
                            <!-- Discount Badge -->
                            <!-- <div class="discount-badge">
                                <?= $discountPercentage; ?>% OFF
                            </div> -->
                            <div class="price-container">
                                <span class="discounted-price">₹ <?= number_format($discountedPrice, 0); ?></span>
                                <span class="original-price">₹ <?= number_format($originalPrice, 0); ?></span>
                            </div>
                            <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>">
                                <h3>
                                    <?= $row['s_name']; ?>
                                </h3>
                            </a>
                            <ul class="rating">
                                <?php
                                for ($i = 0; $i < 5; $i++) {
                                    echo '<li><i class="bx bxs-star"></i></li>';
                                }
                                ?>
                                <li>
                                    <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>">
                                        5
                                    </a>
                                </li>
                            </ul>
                            <span class="tag">
<?php
$shortDesc = trim($row['short_description']);
$pOnly = '';

libxml_use_internal_errors(true); // Suppress HTML warnings
$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="utf-8" ?>' . $shortDesc);

$paragraphs = $dom->getElementsByTagName('p');
foreach ($paragraphs as $p) {
    $pOnly .= $dom->saveHTML($p);
}
libxml_clear_errors();

// Optional: Trim and truncate if needed
$cleaned = strip_tags($pOnly, '<p>');
echo mb_strlen($cleaned, 'UTF-8') > 150
    ? mb_substr($cleaned, 0, 150, 'UTF-8') . '...'
    : $cleaned;
?>
</span>


                            <div style="margin-top: 35px; text-align: center;" class="btn-course-view">
                            <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>" class="default-btn" style="padding: 10px 20px;">
                                Know More
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
    <?php
    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>

</html>
<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    const query = this.value.trim().toLowerCase();
    const courses = document.querySelectorAll('.single-course');

    courses.forEach(course => {
        const courseName = course.querySelector('h3').textContent.toLowerCase();
        const courseDescription = course.querySelector('.tag').textContent.toLowerCase();

        if (courseName.includes(query) || courseDescription.includes(query)) {
            course.style.display = '';
        } else {
            course.style.display = 'none';
        }
    });

    if (query.length === 0) {
        courses.forEach(course => course.style.display = '');
    }
});
</script>
<script type="text/javascript">
    const baseUrl = "<?= $base_url; ?>"; // Make sure $base_url is defined and correctly passed to JS
</script>
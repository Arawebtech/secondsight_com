<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];
// $query = "select * from courses order by ASC";

$query = "select * from blog where status='Active' ORDER BY RAND()";
$result = mysqli_query($conn, $query);

?>
<!DOCTYPE html>
<html lang="zxx">
<?php include('include/head.php'); ?>
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
    .single-course .course-content{
         min-height:210px;   
        }
     #blog-search-icon{
            position: absolute; top: 50%; left: 238px; transform: translateY(-50%); font-size: 18px; color: #4e54c8;
        }
    @media screen and (max-width:700px)
    {
        /*#blog-search-icon{*/
        /*    position: absolute; top: 50%; right: 25x; transform: translateY(-50%); font-size: 18px; color: #4e54c8;*/
           
        /*}*/
        .single-course .course-content{
           min-height:auto; 
        }
    }
    @media only screen and (max-width: 767px) {
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
</style>

<body>
    <?php
    include('include/header1.php');

    ?>
    <!--<div class="page-title-area bg-5" style="background-image: url(assets/img/imag/blog-banner.webp);margin-top:0;">-->
    <!--    <div class="container">-->
    <!--        <div class="page-title-content">-->

    <!--        </div>-->
    <!--    </div>-->
    <!--</div>-->
    <section class="courses-area-style ptb-100">
        <div class="container">
            <div class="showing-result">
                <div class="row align-items-center">
    
    
      <div class="col-lg-3 col-md-4" style="position: relative; ">
    <!-- Search Input -->
    <input type="text" id="searchInput" 
           placeholder="Search blogs..." 
           style="width: 100%; padding: 10px 40px; font-size: 16px; border: 2px solid #ccc; border-radius: 25px; outline: none; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1); transition: 0.3s;" 
           onfocus="this.style.borderColor='#4e54c8'" 
           onblur="this.style.borderColor='#ccc'" />

    <!-- Search Icon -->
    <span id="blog-search-icon" style="position: absolute; top: 50%; right: 25px; transform: translateY(-50%); font-size: 18px; color: #4e54c8;left:auto;">
        🔍
    </span>

    <!-- Search Results Container -->
    <div id="searchResults" 
         style="position: absolute; top: 50px; left: 0; width: 100%; background: #fff; border: 1px solid #ccc; border-radius: 5px; max-height: 200px; overflow-y: auto; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1); display: none;">
        <!-- Dynamic Search Results Will Appear Here -->
    </div>
</div>
                    <!--<div class="col-lg-3 col-md-4">-->
                    <!--</div>-->
                </div>
            </div>
            
            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result)) {
                    // $bannerImagePath = $base_url . "/assets/img/single-blog/{$row['banner_image']}";
                    $bannerImagePath = "/assets/img/single-blog/" . rawurlencode($row['banner_image']);
                    ?>

                    <div class="col-lg-4 col-md-6">
                        <div class="single-course">
                            <a href="<?= $base_url; ?>blog/<?= $row['url']; ?>">
                                <img src="<?= $bannerImagePath; ?>" alt="Image" style="width: 390px; height: 250px;">
                            </a>
                            <div class="course-content">
                                <a href="<?= $base_url; ?>blog/<?= $row['url']; ?>">
                                    <h3>
                                        <?= $row['b_name']; ?>
                                    </h3>
                                </a>
                                <ul class="rating">
                                    <li>
                                        <i class="bx bxs-star"></i>
                                    </li>
                                    <li>
                                        <i class="bx bxs-star"></i>
                                    </li>
                                    <li>
                                        <i class="bx bxs-star"></i>
                                    </li>
                                    <li>
                                        <i class="bx bxs-star"></i>
                                    </li>
                                    <li>
                                        <i class="bx bxs-star"></i>
                                    </li>
                                    <li>
                                        <span>5</span>
                                    </li>
                                </ul>
                                <span class="tag">
                                     <?php
                                    // Trim the short description to remove any leading or trailing whitespace
                                    $shortDesc = trim($row['b_title']);
                                    if (strlen($shortDesc) > 150) {
                                        echo substr($shortDesc, 0, 150) . '...';
                                    } else {
                                        echo $shortDesc;
                                    }
                                    ?>
                                    <?= $row['$shortDesc']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class="col-lg-12 col-md-12">
                    <div class="pagination-area">

                        <span class="page-numbers current" aria-current="page">1</span>
                        <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                </div>
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
            course.style.display = 'block'; // Show matching courses
        } else {
            course.style.display = 'none'; // Hide non-matching courses
        }
    });

    // If no query is entered, show all courses
    if (query.length === 0) {
        courses.forEach(course => course.style.display = 'block');
    }
});




</script>
<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM testimonials";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');
?>

<body>

    <?php
    include('include/header1.php');
    ?>

    <div class="page-title-area bg-5">
        <div class="container">
            <div class="page-title-content">
            </div>
        </div>
    </div>

    <style>
        @media (max-width: 1024px) {
            .page-title-area.bg-5 {
                padding: 92px;
                margin-top: 0;
            }
            .feedback-area .feedback-item iframe {
                border-radius: 8px;
                overflow: hidden;
                background-color: #f4f4f4;
            }
        }
      

        /* Styling for the video embed */
        .video-embed {
        
            border: 4px solid #ffeb3b;
            border-radius: 10px;
            padding: 10px;
            margin-top: 15px;
            max-width: 400px;
            width:100%;
            height: 225px;
            overflow: hidden;
            position: relative;
           
            
        }

        .video-embed iframe {
            width: 100%;
            height: 100%;
            border-radius: 8px;
        }

        /* Feedback Item Styling */
        .feedback-item {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 10px;
            transition: transform 0.3s;
        }

        .feedback-item:hover {
            transform: translateY(-10px);
        }
        
        .feedback-title img {
            border-radius: 8px;
            margin-bottom: 15px;
        }

        /* Description Styling */
        .feedback-description {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 15px;
        }
        
           /* Styling for the Owl Carousel Navigation Arrows */
        .owl-nav {
            display: flex;
            justify-content: space-between;
            position: absolute;
            top: 50%;
            width: 100%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
        .owl-nav .owl-prev, .owl-nav .owl-next {
            background-color: #ffeb3b; /* Yellow background color */
            color: #333; /* Arrow color */
            padding: 15px; /* Increase padding to make the circle bigger */
            border-radius: 50%; /* Makes the button circular */
            pointer-events: auto;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 30px; /* Arrow icon size */
            width: 60px; /* Circular button width */
            height: 60px; /* Circular button height */
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center; /* Ensures the icon is centered */
            line-height: 0; /* Fixes any vertical alignment issues */
        }
        
        /* Styling for the icon inside the circle */
        .owl-nav .owl-prev i, .owl-nav .owl-next i {
            font-size: 30px; /* Make sure the icon is large */
        }
        
        /* Hover Effects for the arrows */
        .owl-nav .owl-prev:hover, .owl-nav .owl-next:hover {
            background-color: #333; /* Darker background on hover */
            color: #fff; /* White arrow color on hover */
        }
        
        /* Adjust positioning of the arrows */
        .owl-nav .owl-prev {
            left: 15px;
        }
        
        .owl-nav .owl-next {
            right: 15px;
        }
         @media (max-width: 700px) {
            .owl-nav .owl-prev, .owl-nav .owl-next {
           width:0;
         }
            
         }

    </style>
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">


    <section class="feedback-area f5f6fa-bg-color ptb-100">
        <div class="container">
            <div class="section-title">
                <span>TESTIMONIAL</span>
                <h2>What Our Students Say</h2>
            </div>
            <div class="courses-slider-three owl-theme owl-carousel" >
                <?php while ($row = mysqli_fetch_assoc($result)) {
                    $bannerImagePath = $base_url . "/assets/img/single-blog/{$row['banner_image']}";
                    $videoUrl = $row['youtube_video_url'];

                    // Convert YouTube URL to embed format
                    if (strpos($videoUrl, 'youtube.com/shorts/') !== false) {
                        $videoId = substr($videoUrl, strrpos($videoUrl, '/') + 1);
                        $videoUrl = "https://www.youtube.com/embed/{$videoId}";
                    } elseif (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                        parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryParams);
                        if (isset($queryParams['v'])) {
                            $videoId = $queryParams['v'];
                            $videoUrl = "https://www.youtube.com/embed/{$videoId}";
                        }
                    }
                ?>
                    <div class="feedback-item">
                        <i class="flaticon-quotation"></i>
                        <p class="feedback-description"><?php echo $row['description']; ?></p>
                        <div class="feedback-title">
                          
                            <h3><?php echo $row['t_name']; ?></h3>
                            <span><?php echo $row['short_name']; ?></span>
                            <!-- Embed Video if URL is available -->
                            <?php if (!empty($videoUrl)) { ?>
                                <div class="video-embed">
                                    <iframe src="<?php echo $videoUrl; ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <?php
    include('include/footer.php');
    include('include/footer-script.php');
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

   
    <!-- Owl Carousel Initialization Script -->
   <!-- Updated Owl Carousel Initialization Script -->
<script>
    $(document).ready(function() {
        $(".courses-slider-three").owlCarousel({
            items: 3,
            loop: true,
            margin: 20,
            nav: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 3000,
            navText: [
                '<div class="custom-owl-prev"><i class="fa fa-chevron-left"></i></div>',
                '<div class="custom-owl-next"><i class="fa fa-chevron-right"></i></div>'
            ],
            responsive: {
                0: { items: 1 },
                768: { items: 2 },
                1024: { items: 3 }
            }
        });
    });
</script>




</body>

</html>

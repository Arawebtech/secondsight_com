<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin/include/db_config.php');
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = null; // or handle unauthenticated user case
}
include('include/cart_logic.php');
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['model-submit'])) {
    // Sanitize POST data
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
    $user_subject = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

    // Database sanitization
    $safe_name_db = mysqli_real_escape_string($conn, $name);
    $safe_email_db = mysqli_real_escape_string($conn, $email);
    $safe_subject_db = mysqli_real_escape_string($conn, $user_subject);
    $safe_message_db = mysqli_real_escape_string($conn, $message);

    // Basic validation
    if (empty($name) || empty($email) || empty($user_subject) || empty($message)) {
        echo "<script>alert('Please fill in all fields.'); history.replaceState(null, null, window.location.pathname);</script>";
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); history.replaceState(null, null, window.location.pathname);</script>";
        exit;
    }

    try {
        // Prepare email
        $to = "gurujimanishsharma@gmail.com, info@arawebtechnologies.com";
        $subject = "New Query Raised From Second Sight Foundation";

        // Headers
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From: Secondsightfoundation.com <gurujimanishsharma@gmail.com>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Email Body
        $body = "
            <html>
            <body>
            <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <tr><td><strong>Name</strong></td><td>{$name}</td></tr>
                <tr><td><strong>Email</strong></td><td>{$email}</td></tr>
                <tr><td><strong>Phone</strong></td><td>{$user_subject}</td></tr>
                <tr><td><strong>Message</strong></td><td>" . nl2br($message) . "</td></tr>
            </table>
            </body>
            </html>
        ";

        // Send the email
        if (mail($to, $subject, $body, $headers)) {
            echo "<script>alert('Thank you! We will get back to you soon.');</script>";
        } else {
            echo "<script>alert('Failed to send email. Please try again later.');</script>";
        }
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        echo "<script>alert('An error occurred while sending your email. Please try again later.');</script>";
    }
}





// Mail code for enroll form


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enroll-submit'])) {
    // Database connection include here if needed

    function sanitize_input($input) {
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $input = preg_replace("/[^a-zA-Z0-9\s@.]/", "", $input); // allow only letters, numbers, spaces, @ and .
        return $input;
    }

    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $course = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

    $user_subject = 'N/A';

    if (!empty($name) && !empty($email) && !empty($phone) && !empty($course)) {
 

            // Send mail
            $to = "gurujimanishsharma@gmail.com, info@arawebtechnologies.com"; // multiple emails separated by comma
            $subject = "New Enroll Query Raised From Second Sight Foundation";

            $message = "
            <html>
            <head>
            <title>New Enroll Query</title>
            </head>
            <body>
            <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
                <tr>
                    <td><strong>Name</strong></td>
                    <td>{$name}</td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td>{$email}</td>
                </tr>
                <tr>
                    <td><strong>Mobile No.</strong></td>
                    <td>{$phone}</td>
                </tr>
                <tr>
                    <td><strong>Course</strong></td>
                    <td>{$course}</td>
                </tr>
            </table>
            </body>
            </html>
            ";

            // Headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Secondsightfoundation.com <gurujimanishsharma@gmail.com>' . "\r\n";

            // Send the mail
            if (mail($to, $subject, $message, $headers)) {
                echo "<script>alert('Thank you! We will get back to you soon.');</script>";
            } else {
                echo "<script>alert('Failed to send details. Please try again later.');</script>";
            }
       
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}

?>


<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');
?>
<link rel="preload" href="/assets/img/banner-imag/b11.webp" as="image">

<link rel="stylesheet" href="/assets/css/odometer.min.css">
<link rel="stylesheet" href="/assets/css/owl.theme.default.min.css">
<link rel="preload" href="/assets/img/banner-imag/s1.webp" as="image">

<link rel="preload" href="/assets/img/banner-imag/b11.webp" as="image">
<link rel="preload" href="/assets/img/n-logo.png" as="image">
<style amp-custom>
    .single-course,
    .single-teachers {
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
        height: 256px;
    }

    .course-content {
        height: 223px;
        position: relative;
    }
    
    .single-course .course-content .tag p{
        line-height:1.5;
    }

    .course-content .btn-course-view {
        position: absolute;
        bottom: 10px;
        margin-top:15px;
        left: 10px;
    }
    .single-course .course-content{
        padding:7px 13px;
    }

    .single-course .course-content .rating li a {
        top: 0px;
    }

    .single-course .course-content .price {
        top: -50px;
         font-size: 17px;
       display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .single-course .course-content .price span {
        line-height: 55px;

    }

    .single-course .course-content .price del {
        color: #312b23;
        font-size: 15px;
        font-weight: 400;
        line-height: 0;
    }

    .single-course .course-content p {
        border-bottom: none;
    }

    .single-news {
        width: 100%;
        overflow: hidden;
        padding: 10px;
        border-radius: 10px;
        box-sizing: border-box;
        background-color: #fff;
        background: linear-gradient(133.21deg, #F7F7F7 -2.44%, #F9F9F9 135.62%);
        box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #FFFFFF,
            2px 2px 10px rgba(255, 255, 255, 0.25),
            -4px -4px 20px rgba(255, 255, 255, 0.8),
            1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
    }

    .single-news img {
        width: 100%;
        height: 271px;
        object-fit: cover;
    }

    @media only screen and (max-width: 767px) {
        .single-course .course-content .price {
            top: -43px;
            font-size: 16px;
            width: 80px;
            height: 45px;
            line-height: 55px;
             border-radius:22px;
        }

        .single-course .course-content .price span {
            line-height: 45px;

        }

        .single-course .course-content .price del {
            font-size: 12px;
            line-height: 0;
        }
         .course-content .btn-course-view a{
             margin-left:-9px;
         }
          .single-course .course-content{
            height:auto;
        }
         .course-content .btn-course-view {
            position: relative;
            margin-top:40px;   
        }
        .margin-box{
            padding-top:1rem;
        }
        .education-content{
            margin-top:0;
        }
    }

    @media only screen and (min-width: 768px) and (max-width: 1190px) {
        .single-course .course-content .price {
            top: -45px;
            font-size: 16px;
            width: 80px;
            height: 45px;
            border-radius:22px;
            line-height: 55px;
        }

        .single-course .course-content .price span {
            line-height: 45px;

        }

        .single-course .course-content .price del {
            font-size: 12px;
            line-height: 0;
        }
    }
    @media screen and (max-width:600px)
    {
        .font-14-mobile{
            font-size:14px;
        }
        .single-course .course-content .tag, .feedback-item p{
            font-size:14px;
        }
        .single-achieve {
            text-align: center;
            margin-bottom: 5px;
            transition: var(--transition);
            min-height: 250px;
        }
       
    }
    @media only screen and (max-width: 767px) {
    .owl-nav{
        /*left: 12px;*/
        display:none;
        }
        .hw-vc-btn{
            margin-left:0;
        }
        .margin-tops{
            margin-top:0;
        }
}
</style>


<body>

    <?php
    include('include/header1.php');

    ?>
<style>

</style>

    <section class="banner-area f5f6fa-bg-color">
        <div class="container social">
            <div class="row align-items-center justify-content-between">
                <!-- Left Content -->
                <div class="col-lg-6">
                    <div class="banner-content">
                        <h1 class="wow animate__animated animate__fadeInLeft" data-wow-delay="0.5s">
                            Transform Your Life, Heal Your Soul and Find True Liberation
                        </h1>
                        <p class="wow animate__animated animate__fadeInLeft font-14-mobile" data-wow-delay="0.8s">
                            At our foundation, we scientifically explore occult concepts like the third eye, blindfold
                            training, and manifestation, revealing how thoughts and emotions shape our reality.
                        </p>
                        <a href="courses.php" class="default-btn wow animate__animated animate__fadeInLeft hm-vc-btn"
                            data-wow-delay="1s" style="margin-left:0;">
                            View Courses
                        </a>
                    </div>
                </div>

                <!-- Right Image -->
                <div class="col-lg-6">
                    <div class="banner-img wow animate__animated animate__fadeInRight" data-wow-delay="1.5s">
                        <img src="/assets/img/banner-imag/s1.webp" alt="Banner Image" loading="lazy">
                    </div>
                </div>
            </div>
            <style>
                .wow.animate__animated {
                    visibility: visible !important;
                    /* Ensure visibility after animation */
                    animation-fill-mode: both;
                    /* Keep final animation state */
                    animation-duration: 1s;
                    /* Smooth animation duration */
                }

                .banner-area .banner-content h1,
                .banner-area .banner-content p,
                .banner-area .banner-img img {
                    animation-delay: 0.5s;
                    animation-duration: 1s;
                }

                .social-wrap li {
                    display: inline-block;
                    margin-right: 10px;
                    color: #fff;
                }

                .social-wrap li.follow-us {
                    font-weight: bold;
                    margin-right: 20px;
                }
                .achieve-area {
    background-color: #fff;
    padding-top: 100px;
    padding-bottom: 0;
}

.section-title {
    text-align: center;
    margin-bottom: 50px;
}

.section-title span {
    color: #fdc134;
    font-weight: 500;
    font-size: 16px;
    display: block;
    margin-bottom: 8px;
}

.section-title h2 {
    font-size: 32px;
    font-weight: 700;
    color: #333;
}

.single-achieve {
    background: #ffffff;
    border-radius: 15px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0px 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.single-achieve:hover {
    background-color: #fdc134; /* Yellow on hover */
    transform: translateY(-8px);
    box-shadow: 0px 8px 20px rgba(0,0,0,0.15);
}

.achieve-shape img {
    width: 60px;
    height: 60px;
    background-color: #fdc134;
    padding: 12px;
    border-radius: 50%;
    box-shadow: 0px 4px 10px rgba(253,193,52,0.4);
    transition: all 0.3s ease;
}
.single-achieve:hover{
    background:#fdc134 !important;
}

.single-achieve:hover .achieve-shape img {
    background-color: #fff; /* White icon background when hovered */
    transform: rotate(8deg) scale(1.05);
}

.single-achieve h3 {
    font-size: 14px; /* Keep original */
    font-weight: 600;
    color: #333;
    margin-top: 15px;
    transition: color 0.3s ease;
}

.single-achieve p {
    font-size: 14px; /* Keep original */
    color: #555;
    margin-top: 10px;
    line-height: 1.6;
    transition: color 0.3s ease;
}

.single-achieve:hover h3,
.single-achieve:hover p {
    color: #fff; /* White text on yellow background */
}

@media (max-width: 767px) {
    .section-title h2 {
        font-size: 24px;
    }
    .single-achieve {
        padding: 20px;
    }
    .achieve-shape img {
        width: 50px;
        height: 50px;
        padding: 10px;
    }
}

            </style>

           
            <!-- Animate.css -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" media="print" onload="this.media='all'">

            <!-- WOW.js -->
         



            <ul class="social-wrap">
                <li class="follow-us">
                    Follow Us:
                </li>

                <li>
                    <a href="https://www.instagram.com/secondsightfoundationdelhi/" target="_blank">
                        <i class="bx bxl-instagram"></i>
                    </a>
                </li>
                <li>
                    <a href="https://www.facebook.com/healingsoul.in/" target="_blank">
                        <i class="bx bxl-facebook"></i>
                    </a>
                </li>
                <li>
                    <a href="https://www.youtube.com/@secondsightfoundation" target="_blank"><i class="fab fa-youtube"
                            style="font-size: 18px; "></i></a>
                </li>
            </ul>

        </div>
    </section>



    <section class="achieve-area f5f6fa-bg-color pt-100 pb-0 bg-white margin-box">
        <div class="container">
            <div class="section-title">
                <span>Leader In Spiritual Education</span>
                <h2>Achieve Your Spiritual Goals</h2>
            </div>
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="single-achieve">
                        <div class="achieve-shape shape-1">
                            <img loading="lazy" src="/assets/img/achieve-shape/1.png" alt="Image">
                        </div>
                        <h3 style="font-size: 18px;">Master Powerful Techniques</h3>
                        <p class="font-14-mobile"  style="font-size: 14px;">Learn advanced spiritual techniques such as Third Eye Activation, Shambhavi Healing, and Aura
                            Reading that will help you unlock your hidden potential.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-achieve">
                        <div class="achieve-shape shape-2">
                            <img loading="lazy" src="/assets/img/achieve-shape/2.png" alt="Image">
                        </div>
                        <h3 style="font-size: 18px;">Transform Your                         <br> Life</h3>
                        <br>
                        
                        <p class="font-14-mobile"  style="font-size: 14px;">Gain insights and healing through courses on Past Life Regression, Reiki, and Meditation,
                            helping you achieve balance and harmony in life.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-achieve">
                        <div class="achieve-shape shape-3">
                            <img loading="lazy" src="/assets/img/achieve-shape/3.png" alt="Image">
                        </div>
                        <h3 style="font-size: 18px;">Get Certified</h3>
                        <br>
                        <p class="font-14-mobile" style="font-size: 14px;">Receive internationally recognized certifications in modalities like Usui Reiki, Hypnosis,
                            and Magneto Therapy, empowering you to help others.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-achieve">
                        <div class="achieve-shape shape-4">
                            <img loading="lazy" src="/assets/img/achieve-shape/4.png" alt="Image">
                        </div>
                        <h3 style="font-size: 18px;">Empower Your Community</h3> <br>
                        <p class="font-14-mobile"  style="font-size: 14px;">Utilize your newfound skills to create a positive impact by healing and guiding others,
                            becoming a spiritual leader in your community.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="education-area-two ptb-0 " style="margin-top:100px !important;">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="education-content" style="margin-top:0;">
                        <span class="top-title">Education For All</span>
                        <h2>Transform Your Life, Heal Your Soul, and Find Liberation from<span> All Troubles</span></h2>
                        <p style="font-size: 14px; line-height: 1.6;">
                            At our foundation, we approach the mysteries of the occult with a scientific lens,
                            illuminating concepts like the third eye, blindfold training, and Akashic Records. We bridge
                            the gap between ancient wisdom and modern understanding to transform lives.
                        </p>
                        <p style="font-size: 14px; line-height: 1.6;">
                            Dive deep into past life regression therapy, Reiki's healing energy, and discover how
                            practices like Third Eye Awakening can unlock your inner potential for manifestation,
                            spiritual growth, and profound personal transformation.
                        </p>
                        <div class="row">
                            <div class="col-lg-6">
                                <ul>
                                    <li>
                                        <i class="bx bx-check"></i>
                                        Learn SHAMBHAVI healing in 1 minute
                                    </li>
                                    <li>
                                        <i class="bx bx-check"></i>
                                        Balance your Aura & Chakras instantly
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-6">
                                <ul>
                                    <li>
                                        <i class="bx bx-check"></i>
                                        Remove Black Magic, Tantra, Evil Eye issues
                                    </li>
                                    <li>
                                        <i class="bx bx-check"></i>
                                        Develop great intuition power
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <a href="courses.php" class="default-btn" style="margin-top: 18px;">
                            View All Courses
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="education-img-wrap">
                        <div class="education-img-2">
                            <img loading="lazy" src="/assets/img/banner-imag/p2.webp" alt="Image">
                        </div>
                        <div class="education-img-3">
                            <img loading="lazy" src="/assets/img/banner-imag/p1.webp" alt="Image">
                        </div>
                        <div class="education-img-4">
                            <img loading="lazy" src="/assets/img/banner-imag/p3.webp" alt="Image">
                        </div>
                        <div class="education-shape-1">
                            <img loading="lazy" src="/assets/img/banner-imag/education-shape-1.webp" alt="Image">
                        </div>
                        <div class="education-shape-2">
                            <img loading="lazy" src="/assets/img/banner-imag/education-shape-2.png" alt="Image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        .enroll-wrap {
            margin-top: -58px;
        }

        @media only screen and (max-width: 768px) {
            .education-img-wrap {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                margin-top: 20px;
            }

            .enroll-wrap {
                margin-top: 6px;
            }

            .education-img-2,
            .education-img-3,
            .education-img-4 {
                margin-bottom: 15px;
            }

            .education-img-2 img,
            .education-img-3 img,
            .education-img-4 img {
                height: auto;
            }

            .education-shape-1 img,
            .education-shape-2 img {
                height: auto;
            }

            .education-content h2 {
                font-size: 20px;
                line-height: 1.4;
            }
            .education-content{
                margin-top:45px;
            }
            .education-content p {
                font-size: 14px;
                line-height: 1.6;
            }

            .education-content ul li {
                font-size: 14px;
                margin-bottom:7px;
            }
            .education-content ul {
                
                margin-top:7px;
            }

            .default-btn {
                font-size: 14px;
                padding: 5px 15px;
            }

            .custom-mt-350 {
                margin-top: 100px;
            }
            .team-btn{
                margin-top:-22px;
                padding:10px;
            }
             .hw-vc-btn{
                margin-left:0;
            }
        }
        .single-achieve:hover{
            transform: none;
            background-color: transparent;
            box-shadow: none
        }  

        .teachers-area-three{
            margin-top:5rem!important;
        }
        .custom-hide-unhide{
            width:100%;
            height:0px;
        }

        @media screen and (max-width:500px) {
            .custom-hide-unhide{
                height:120px;
            }
            .custom-view-team{
                margin:0 80px;
            }
        }
    </style>



    <section class="education-area ebeef5-bg-color mt-100" style='padding-bottom:20px;'>
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-6">
                    <div class="education-img" style="height: 81%;">
                        <img loading="lazy" src="/assets/img/imag/guruji2.webp" alt="Image">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="education-content ptb-100">
                        <span class="top-title" style="margin-top: -51px;">Founder</span>
                        <h2>ABOUT MANISH SHARMA<span> (GURU JI)</span></h2>
                        <p class="font-14-mobile">Dr. Manish Sharma is a Reiki Grandmaster and Multimodality Healer with over 15 years of
                            experience. He specializes in various holistic healing modalities, such as Sanjeevani,
                            Kundalini, Hypnosis, Past Life Healing, Midbrain Activation, and Third Eye Activation. He is
                            also proficient in physical therapies like Yoga, Naturopathy, Acupuncture, and more.</p>

                        <ul>
                            <li><i class="bx bx-check"></i> Reiki Grandmaster and Multimodality Healer</li>
                            <li><i class="bx bx-check"></i> Expert in Midbrain and Third Eye Activation</li>
                            <li><i class="bx bx-check"></i> Proficient in Yoga, Naturopathy, Acupressure, and
                                Acupuncture</li>
                            <li><i class="bx bx-check"></i> MD in Alternative Medicine</li>


                        </ul>
                        <a href="courses.php" class="default-btn">
                            View More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <section class="d-flex courses-area-three ptb-100 jarallax" 
        data-jarallax="{&quot;speed&quot;: 0.3}">
        <div class="container">
            <div class="section-title white-title">
                <span>Our Courses</span>
                <h2>Checkout Our Best Offering Courses</h2>
            </div>
            <div class="courses-slider-three owl-theme owl-carousel">

                <?php

                $query = "SELECT * FROM courses WHERE status = 'Active' ORDER BY RAND()";

                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    // $bannerImagePath = $base_url . "/assets/img/course-img/{$row['banner_image']}";
                          $bannerImagePath = "/assets/img/course-img/" . rawurlencode($row['banner_image']);

                    ?>



                    <div class="single-course shadow">
                        <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>">
                            <img loading="lazy" src="<?= $bannerImagePath; ?>" alt="Image">
                        </a>
                        <div class="course-content">
                            <span class="price" style=" margin-bottom:20px !important;">
                                <span> ₹ <?= $row['price']; ?></span>
                              <!--  <small> <del class="total_price">₹<?= $row['duration'] ?></del></small> -->
                            </span>
                            <!--<span class="tag">Education</span>-->
                            <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>">
                                <h3>
                                    <?= $row['s_name']; ?>
                                </h3>
                            </a>
                            
                            <ul class="rating">
                                <!-- loop for rating star -->
                                <?php
                                for ($i = 0; $i < $row['rating']; $i++) {
                                    echo ' <li>
                                        <i class="bx bxs-star"></i>
                                    </li>';
                                }
                                ?>

                                <!--<li>-->
                                <!--    <a href="<?= $base_url; ?>courses/<?= $row['url']; ?>">-->
                                <!--        <?= $row['rating']; ?>-->
                                <!--    </a>-->
                                <!--</li>-->
                            </ul>
                            
                            <span class="tag font-14-mobile">
                                <?php
                                // Trim the short description to remove any leading or trailing whitespace
                                $shortDesc = trim($row['description']);
                                if (strlen($shortDesc) > 180) {
                                    echo substr($shortDesc, 0, 180) . '...';
                                } else {
                                    echo $shortDesc;
                                }
                                ?>
                            </span>
                            <div class="btn-course-view">
                                <a href="courses/<?= $row['url'] ?>" class="default-btn text-center" style="padding: 10px 20px;">
                                    Buy Now
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php

                }
                ?>


            </div>
        </div>
    </section>



    <section class="enroll-area ptb-100 bg-white"">
        <div class="container">
            <div class="row d-flex justify-content-between align-items-center">
                <div class="col-md-5">
                    <div class="enroll-wrap" style="background-color: #737d8d; padding: 1px;">
                        <form class="courses-form" style="padding: 17px 52px;" method="post" action="">
                            <span>Need Any Courses</span>
                            <h3>Enroll Now</h3>
                            <div class="form-group">
                                <input type="text" class="form-control"
                                    style="height: 40px; background-color: #9e9e9ef7;" id="Name" name="name"
                                    placeholder="Your name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control"
                                    style="height: 40px; background-color: #9e9e9ef7;" id="email" name="email"
                                    placeholder="Your email" required>
                            </div>
                            <div class="form-group">
                                <input type="tel" class="form-control"
                                    style="height: 40px; background-color: #9e9e9ef7;" id="Number" name="phone" maxlength="10" pattern="[0-9]{10}" title="Please enter 10 digits only"
                                    placeholder="Phone Number" required>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control"
                                    style="height: 40px; background-color: #9e9e9ef7;" id="message" name="message"
                                    placeholder="Courses Type" required>
                            </div>
                            <button type="submit" name="enroll-submit" class="default-btn hm-vc-btn" style="margin-left:0;">
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-7 d-flex justify-content-end">
                    <div class="enroll-img">
                        <img loading="lazy" src="/assets/img/imag/ccc.jpg" alt="Image" style="width:520px;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="custom-hide-unhide">

    </div>

    <section class="teachers-area-three ">
        <div class="container"  style="margin-top: 150px !important;">
            <div class="section-title">
                <span>OUR TEAM</span>
                <h2>Meet Our Team Members</h2>
            </div>
            <div class="row">

                <?php

                $query_team = "SELECT * FROM team where status=1 ORDER BY created_date DESC LIMIT 4";
                $result_team = mysqli_query($conn, $query_team);
                while ($row_team = mysqli_fetch_assoc($result_team)) {
                    // $bannerImagePath = $base_url . "/assets/img/team/{$row_team['image']}"; 
                      $bannerImagePath = "/assets/img/team/" . rawurlencode($row_team['image']);

                    ?>
                    
                    <div class="col-lg-3 col-sm-6">
                        <div class="single-teachers shadow">
                            <a href="team/<?= $row_team['url'] ?>">
                                <img loading="lazy" src="<?= $bannerImagePath ?>" alt="Image">
                            </a>
                            <div class="teachers-content">
                             
                                <h5 style="font-size: 17px;"><a
                                        href="team/<?= $row_team['url'] ?>"><?= $row_team['name']; ?></a></h5>
                                <span class="font-14-mobile"><?= $row_team['specialisation']; ?></span>
                                <a href="team/<?= $row_team['url'] ?>" class="default-btn hm-vc-btn"
                                    style="margin-top: 12px;padding: 6px 20px;margin-left:0;">
                                    View Profile
                                </a>
                       
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>
    </section>

    <div class='custom-view-team' style="display: flex; justify-content: center; align-items: center; margin-bottom:40px; ">
        <a href="teamlist.php" class="default-btn team-btn" style="margin-left:0;">View Team</a>
    </div>
    <style>
        @media only screen and (max-width: 768px) {

            .section-title {
                margin-right: 0;
                text-align: center;
                width: 100%;
                margin-bottom:4px;
            }

            .section-title h2 {
                font-size: 24px;
                margin: 0;
            }

            .section-title span {
                font-size: 18px;
                margin-bottom:-10px;
            }

            .feedback-item {
                margin-right: 0;
                width: 100%;
            }

            .feedback-slider {
                padding: 0 15px;
            }

            .feedback-item p {
                text-align: justify;
            }
        }

        @media only screen and (max-width: 700px) {
            .feedback-area-three {
                background-size: cover;
                background-position: top center;
            }
            .single-counter .counter-shape h2 {
                font-size:24px;
            }
            .counter-shape img{
                max-width:26%;
            }
            .counter-shape p{
               font-size:24px;
            }
            .blog-content{
                height:auto;
            }
            .pop-btn{
                margin-left:0;
            }
        }
        .blog-content{
            height:auto;
        }
        .single-achieve p{
            text-align:left;
        }
    </style>



    <section class="news-area-two pt-0 pb-10 margin-box">
        <div class="container">
            <div class="section-title">
                <span>GALLERY</span>
                <h2>Our Beautiful Moments</h2>
            </div>
            <div class="row">

                <?php

                $query = "select * from image ORDER BY created_date DESC LIMIT 3";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {

                    ?>

                    <div class="col-lg-4 col-md-6">
                        <div class="single-news">
                            <img loading="lazy" src="/assets/img/gallery/<?= $row['small_image'] ?>" alt="Image"
                                style="width:-webkit-fill-available;" loading="lazy">
                        </div>
                    </div>
                    <?php
                }
                ?>


                <div class="col-12">
                    <div class="text-center">
                        <a href="view_gallery.php" class="default-btn hw-vc-btn p-2">View Our Gallery</a>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <section class="courses-area pt-70 pb-70">
        <div class="container">
            <div class="section-title">
                <span>Popular Blogs</span>
                <h2>Read Popular Blogs for In-Depth Knowledge</h2>
            </div>
            <div class="row">
                <?php
                $query = "select * from blog order by RAND() LIMIT 6";
                $result = mysqli_query($conn, $query);
                while ($row = mysqli_fetch_assoc($result)) {

                    // $bannerImagePath = $base_url . "/assets/img/single-blog/{$row['banner_image']}";
                          $bannerImagePath = "/assets/img/single-blog/" . rawurlencode($row['banner_image']);


                    ?>

                    <div class="col-lg-4 col-md-6">
                        <div class="single-course single-blog">
                            <a href="<?= $base_url; ?>blog/<?= $row['url']; ?>">
                                <img src="<?= $bannerImagePath; ?>" alt="Image" style="height: 244px;width: 412px;"
                                    loading="lazy">
                            </a>
                            <div class="course-content blog-content">
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
                                        <a href="<?= $base_url; ?>blog/<?= $row['url']; ?>">
                                            <?= isset($row['rating']) ? $row['rating'] : '5'; ?>
                                        </a>
                                    </li>
                                </ul>
                                <span class="tag">
                                     <?php
                                    // Trim the short description to remove any leading or trailing whitespace
                                    $shortDesc = trim($row['b_title']);
                                    if (strlen($shortDesc) > 130) {
                                        echo substr($shortDesc, 0, 130) . '...';
                                    } else {
                                        echo $shortDesc;
                                    }
                                    ?>
                                </span>

                                <ul class="lessons">
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

            </div>
        </div>
    </section>


    <section class="counter-area ebeef5-bg-color pt-100 pb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="single-counter">
                        <div class="counter-shape shape-1">
                            <img src="/assets/img/counter-shape/counter-shape-1.png" alt="Image" loading="lazy">
                            <h2>
                                <span class="odometer" data-count="100">00</span>
                                <span class="target">%</span>
                            </h2>
                        </div>
                        <p style="text-align:center">Success rate</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-counter">
                        <div class="counter-shape shape-2">
                            <img src="/assets/img/counter-shape/counter-shape-2.png" alt="Image" loading="lazy">
                            <h2>
                                <span class="odometer" data-count="5253">00</span>
                            </h2>
                        </div>
                        <p style="text-align:center">Students enrolled</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-counter">
                        <div class="counter-shape shape-3">
                            <img src="/assets/img/counter-shape/counter-shape-3.png" alt="Image" loading="lazy">
                            <h2>
                                <span class="odometer" data-count="325">00</span>
                            </h2>
                        </div>
                        <p style="text-align:center">Certified Doctors</p>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="single-counter">
                        <div class="counter-shape shape-4">
                            <img src="/assets/img/counter-shape/counter-shape-4.png" alt="Image" loading="lazy">
                            <h2>
                                <span class="odometer" data-count="565">00</span>
                            </h2>
                        </div>
                        <p style="text-align:center">Complete courses</p>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <div class="video-area f5f6fa-bg-color">
        <div class="container">
            <div class="video-wrap">
                <img src="/assets/img/banner-imag/image.webp" alt="Image" loading="lazy">
                <div class="video-content">
                    <!--<a href="https://www.youtube.com/watch?v=ZL-xMdqFJZw" class="video-btn popup-youtube">-->
                        <i class="flaticon-play-button"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php
    $query = "SELECT * FROM testimonials";

    // $query = "select * from team ORDER BY RAND()";
    $result = mysqli_query($conn, $query);

    ?>

    <section class="feedback-area f5f6fa-bg-color ptb-40">
        <div class="container">
            <div class="section-title">
                <span>TESTIMONIAL</span>
                <h2>What Our Students Say</h2>
            </div>
            <div class="feedback-slider owl-theme owl-carousel">


                <?php while ($row = mysqli_fetch_assoc($result)) {
                    $bannerImagePath = $base_url . "/assets/img/single-blog/{$row['banner_image']}";
                    $videoUrl = $row['youtube_video_url'];

                    // Convert YouTube URL to embed format if it's from YouTube Shorts
                    // Convert YouTube URL to embed format if it's from YouTube Shorts
                    if (strpos($videoUrl, 'youtube.com/shorts/') !== false) {
                        // If the URL is from YouTube Shorts, extract the video ID
                        $videoId = substr($videoUrl, strrpos($videoUrl, '/') + 1);
                        $videoUrl = "https://www.youtube.com/embed/{$videoId}";
                    } elseif (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                        // If the URL is a standard YouTube video, extract the video ID
                        parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryParams);
                        if (isset($queryParams['v'])) {
                            $videoId = $queryParams['v'];
                            $videoUrl = "https://www.youtube.com/embed/{$videoId}";
                        }
                    }
                    ?>

                    <div class="feedback-item">
                        <i class="flaticon-quotation"></i>
                        <p><?php echo $row['description']; ?></p>
                        <div class="feedback-title">
                           

                            <h3><?php echo $row['t_name'] ?></h3>
                            <span><?php echo isset($row['short_name']) ? $row['short_name'] : ''; ?></span>
                            <?php if (!empty($videoUrl)) { ?>
                                <div class="video-embed">
                                    <iframe src="<?php echo $videoUrl; ?>" frameborder="0" loading="lazy" 
                                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                </div>
                            <?php } else { ?>
                                <!-- You can add fallback content here if needed -->
                            <?php } ?>
                        </div>
                    </div>

                    <?php
                }
                ?>

     

            </div>
        </div>
    </section>

    <style>
        @media (max-width: 1024px) {
            .page-title-area.bg-5 {
                padding: 92px;
                margin-top: 39px;
            }

            .feedback-area .feedback-item iframe {
                border-radius: 8px;
                /* Rounded corners for the video */
                overflow: hidden;
                background-color: #f4f4f4;
            }
        }

        /* Video embed container with yellow border */
        .video-embed {
            border: 4px solid #ffeb3b;
            /* Yellow border around the video */
            border-radius: 10px;
            /* Rounded corners for the container */
            padding: 10px;
            /* Some padding around the video */
            margin-top: 34px;
            /* Spacing from the content above */
            max-width: 400px;
            /* Reduced size of the video container */
            height: 225px;
            /* Reduced height of the video container */
            overflow: hidden;
            position: relative;
        }

        .video-embed iframe {
            width: 100%;
            /* Make the iframe fit the container */
            height: 100%;
            /* Adjust iframe height */
            border-radius: 8px;
            /* Rounded corners for the iframe */
        }

        .feedback-title img {
            border-radius: 8px;
        }
    </style>



    <?php

    include('include/footer.php');
    include('include/footer-script.php');
    ?>

    <div class="modal-newsletter-area">
        <div class="modal fade" id="exampleModal" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <i class="bx bx-x"></i>
                    </button>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-5 col-sm-5 p-0">
                                <div class="newsletter-img">
                                </div>
                            </div>
                            <div class="col-lg-7 col-sm-7 pl-0">
                                <div class="modal-newsletter-wrap">
                                    <h5>Unlock Your Full Potential</h5>
                                    <p>For more information about our courses, get in touch with us</p>
                                    <form method="post" action=""
                                        style="max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                        <div style="margin-bottom: 15px;">
                                            <input type="text" class="form-control" placeholder="Enter your Name"
                                                name="name"
                                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;" required>
                                        </div>

                                        <div style="margin-bottom: 15px;">
                                            <input type="email" class="form-control" placeholder="Enter your email"
                                                name="email"
                                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;" required>
                                        </div>

                                        <div style="margin-bottom: 15px;">
                                            <input type="tel" class="form-control" placeholder="Phone Number" name="phone"  maxlength="10" pattern="[0-9]{10}" title="Please enter 10 digits only"
                                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;" required>
                                             
                                        </div>

                                        <div style="margin-bottom: 15px;">
                                            <input type="text" class="form-control" placeholder="Message" name="message"
                                                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;" required>
                                        </div>

                                        <div id="validator-newsletter-2" class="form-result"
                                            style="margin-bottom: 15px;"></div>

                                        <div class="agree-label" style="margin-bottom: 15px;">
                                            <!-- Add terms and conditions or agreement checkbox if needed -->
                                        </div>

                                        <button type="submit" class="default-btn pop-btn" name="model-submit"
                                            style="padding: 12px 40px; background-color: #007bff; color: #fff; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; width: 100%; transition: background-color 0.3s;">
                                            Submit
                                        </button>
                                    </form>

                                    <!-- Responsive CSS for mobile view -->
                                    <style>
                                        @media (max-width: 768px) {
                                            form {
                                                padding: 15px;
                                            }

                                            input.form-control {
                                                padding: 8px;
                                                font-size: 14px;
                                            }

                                            button.default-btn {
                                                padding: 10px 20px;
                                                font-size: 16px;
                                            }
                                        }
                                    </style>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
  // Animate numbers on page load
  document.addEventListener("DOMContentLoaded", function () {
    const counters = document.querySelectorAll(".odometer");
    counters.forEach(counter => {
      const finalValue = counter.getAttribute("data-count");
      setTimeout(() => {
        counter.innerHTML = finalValue;
      }, 1000); // 1s delay for smooth animation
    });
  });
</script>
</body>


</html>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" defer></script>
 <script>
                document.addEventListener("DOMContentLoaded", function () {
                    // Initialize WOW.js to prevent multiple initializations
                    if (typeof WOW !== "undefined" && !window.wowInitialized) {
                        window.wowInitialized = true;
                        new WOW().init();
                    }
                });
            </script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(function () {
            // Select the modal element
            var modalElement = document.getElementById('exampleModal');
            // Check if the modal element exists
            if (modalElement) {
                // Initialize and show the modal using Bootstrap's Modal class
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            }
        }, 8000); // 30000 ms = 30 seconds
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let url = 'https://script.google.com/macros/s/AKfycbzNq4kELiaig2L5OBB8HgDx2ilI5oIE_KuqF70mIM5EZBTV-qpgvl0a_1RS3up76GVJ/exec';

        // सभी फॉर्म सेलेक्ट करें
        let forms = document.querySelectorAll('form');

        // हर फॉर्म पर event listener लगाएं
        forms.forEach(form => {
            form.addEventListener("submit", function (e) {
              

                let d = new FormData(form);
                fetch(url, {
                    method: "POST",
                    body: d
                })
                .then(res => res.text())
                .then(finalRes => {
                    form.reset(); // फॉर्म खाली करें
                    // window.location.href = "thankyou.php";
                })
               
            });
        });
    });
</script>

<style>
@media only screen and (max-width: 767px) {
    .single-course, .single-teachers {
        box-shadow: none;
        border-radius: 8px;
        margin-bottom: 18px;
    }
    .single-course img, .single-teachers img {
        width: 100%;
        height: auto !important;
        object-fit: cover;
    }
    .course-content, .education-content {
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
    .page-title-area, .affordable-area, .education-area-two {
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
    .education-img img, .education-img-2 img {
        width: 100% !important;
        height: auto !important;
    }
    .p-about {
        font-size: 13px !important;
    }
}
</style>

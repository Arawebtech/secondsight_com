<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}

include('include/db_config.php');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Dashboard - Second Sight Foundation</title>
    <!--<link rel="icon" type="image/png" sizes="192x192" href="../assets/images/favicon.jpg">-->
       <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            background: linear-gradient(135deg, #ffd700, #ffa500);
            /* Yellow and Orange theme */
            color: #333;
            margin: 0;
            padding: 0;
        }

        .content-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #444;
            margin-bottom: 20px;
        }

        .breadcrumb {
            background: none;
            margin-bottom: 20px;
        }

        .breadcrumb li a {
            color: #fff;
        }

        .info-box {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 10px;
            padding: 5px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        .info-box-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            color: #fff;
            font-size: 30px;
            text-align: center;
            margin-bottom: 10px;
            background: #f1c40f;
            /* Yellow */
        }

        .info-box:hover .info-box-icon {
            background: #e67e22;
            /* Darker yellow-orange */
        }

        .info-box-content {
            display: inline-block;
            vertical-align: top;
            margin-left: 15px;
            text-align: center;
        }

        .info-box-text {
            font-size: 14px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .info-box-number {
            font-size: 26px;
            color: #777;
            font-weight: 800;
        }

        .navbar {
            background-color: #ffd700;
            /* Bright Yellow */
            border: none;
        }

        .navbar .navbar-brand {
            color: #fff;
            font-size: 24px;
            font-weight: bold;
        }

        .navbar .navbar-nav>li>a {
            color: #fff;
        }

        .sidebar-menu>li>a {
            font-weight: 600;
            color: #f39c12;
            /* Darker Yellow */
        }

        .sidebar-menu>li.active>a,
        .sidebar-menu>li>a:hover {
            background-color: #f39c12;
            color: #fff;
        }

        .content-wrapper {
            background-color: #f7fafc;
            padding: 46px;
        }

        .control-sidebar-bg {
            background-color: #ffd700;
        }

        @media (max-width: 767px) {
            .info-box {
                flex-direction: column;
                padding: 10px;
            }
            .info-box-icon {
                margin: 0 auto 10px auto;
            }
            .info-box-content {
                text-align: center;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .row > [class^="col-"] {
                width: 100%;
                display: block;
                margin-bottom: 15px;
            }
        }
        @media (max-width: 480px) {
            .content-header h1 {
                font-size: 22px;
            }
            .info-box-number {
                font-size: 18px;
            }
        }
    </style>
</head>

<body class="hold-transition skin-yellow sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>
                    Dashboard - Second Sight Foundation
                    <small>Admin Panel</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Dashboard</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <!-- Box 1: Course Management -->
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-courses.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="ion ion-ios-book-outline"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $courseList = "SELECT id FROM courses";
                                    $resultCourse = mysqli_query($conn, $courseList);
                                    $courseCount = $resultCourse->num_rows;
                                    ?>
                                    <span class="info-box-text">Total Courses</span>
                                    <span class="info-box-number"><?php echo $courseCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Box 2: User Registration Details -->
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-registration.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="ion ion-ios-people-outline"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $userList = "SELECT id FROM users";
                                    $resultUser = mysqli_query($conn, $userList);
                                    $userCount = $resultUser->num_rows;
                                    ?>
                                    <span class="info-box-text">Registered Users</span>
                                    <span class="info-box-number"><?php echo $userCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-order.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="ion ion-ios-cart"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $userList = "SELECT id FROM orders";
                                    $resultUser = mysqli_query($conn, $userList);
                                    $userCount = $resultUser->num_rows;
                                    ?>
                                    <span class="info-box-text">Order Details</span>
                                    <span class="info-box-number"><?php echo $userCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    
                     <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-team.php">
                            <div class="info-box">
                               <span class="info-box-icon"><i class="ion ion-ios-people"></i></span>



                                <div class="info-box-content">
                                    <?php
                                    $userList = "SELECT id FROM team";
                                    $resultUser = mysqli_query($conn, $userList);
                                    $userCount = $resultUser->num_rows;
                                    ?>
                                    <span class="info-box-text">Team Details</span>
                                    <span class="info-box-number"><?php echo $userCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Box 3: Blog Management -->
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-blog.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fa-solid fa-blog"></i> </span>
                                <div class="info-box-content">
                                    <?php
                                    $blogList = "SELECT id FROM blog";
                                    $resultBlog = mysqli_query($conn, $blogList);
                                    $blogCount = $resultBlog->num_rows;
                                    ?>
                                    <span class="info-box-text">Total Blogs</span>
                                    <span class="info-box-number"><?php echo $blogCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-image.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fa-solid fa-image"></i> </span>
                                <div class="info-box-content">
                                    <?php
                                    $blogList = "SELECT id FROM image";
                                    $resultBlog = mysqli_query($conn, $blogList);
                                    $blogCount = $resultBlog->num_rows;
                                    ?>
                                    <span class="info-box-text">Gallery </span>
                                    <span class="info-box-number"><?php echo $blogCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-course-comment.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fa-regular fa-comment"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $blogList = "SELECT id FROM course_comment";
                                    $resultBlog = mysqli_query($conn, $blogList);
                                    $blogCount = $resultBlog->num_rows;
                                    ?>
                                    <span class="info-box-text">Course Comment</span>
                                    <span class="info-box-number"><?php echo $blogCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-lesson.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fa-solid fa-video"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $blogList = "SELECT id FROM lesson_video";
                                    $resultBlog = mysqli_query($conn, $blogList);
                                    $blogCount = $resultBlog->num_rows;
                                    ?>
                                    <span class="info-box-text">Lesson</span>
                                    <span class="info-box-number"><?php echo $blogCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-testimonial.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fas fa-pencil-alt"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $blogList = "SELECT id FROM testimonials";
                                    $resultBlog = mysqli_query($conn, $blogList);
                                    $blogCount = $resultBlog->num_rows;
                                    ?>
                                    <span class="info-box-text">Testimonials</span>
                                    <span class="info-box-number"><?php echo $blogCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-notification.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fa-solid fa-bell"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $blogList = "SELECT id FROM notifications";
                                    $resultBlog = mysqli_query($conn, $blogList);
                                    $blogCount = $resultBlog->num_rows;
                                    ?>
                                    <span class="info-box-text">Notification</span>
                                    <span class="info-box-number"><?php echo $blogCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
               <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="view-batch.php">
                            <div class="info-box">
                                <span class="info-box-icon"><i class="fa-solid fa-layer-group"></i></span>
                                <div class="info-box-content">
                                    <?php
                                    $batchList = "SELECT id FROM batch";
                                    $resultBatch = mysqli_query($conn, $batchList);
                                    $batchCount = $resultBatch->num_rows;
                                    ?>
                                    <span class="info-box-text">Batches</span>
                                    <span class="info-box-number"><?php echo $batchCount; ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <script src="bower_components/fastclick/lib/fastclick.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="dist/js/demo.js"></script>
</body>

</html>
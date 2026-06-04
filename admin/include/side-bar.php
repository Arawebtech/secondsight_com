<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">-->
    <title>Sidebar</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <aside class="main-sidebar">
        <section class="sidebar">
          

            <div style="height: calc(100vh - 60px); overflow-y: auto;">
                <ul class="sidebar-menu" data-widget="tree">
                    <li class="header">MAIN NAVIGATION</li>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="view-courses.php"><i class="fas fa-book-open"></i> <span>View Courses</span></a></li>
                    <li><a href="view-lesson.php"><i class="fa-solid fa-video"></i> <span>View Lesson</span></a></li>
                    <li><a href="view-batch.php"><i class="fa-solid fa-layer-group"></i> <span>View Batches</span></a></li>
                    <li><a href="view-course-comment.php"><i class="fa-regular fa-comment"></i> <span>Course Comment</span></a></li>
                    <li><a href="view-blog.php"><i class="fa-solid fa-blog"></i> <span>View Blogs</span></a></li>
                     <li><a href="view-image.php"><i class="fa-solid fa-image"></i> <span>View Gallery</span></a></li>
                      <li><a href="view-testimonial.php"><i class="fas fa-pencil-alt"></i> <span>View Testimonial</span></a></li>
                      
                       <li><a href="view-notification.php"><i class="fa-solid fa-bell"></i><span>Push Notification</span></a></li>
                    <li>
                        <a href="view-team.php">
                            <i class="fas fa-users"></i> 
                            <span>View Team</span>
                        </a>
                    </li>
                    <li><a href="view-registration.php"><i class="fas fa-user-friends"></i> <span>User Registrations</span></a></li>
                        <li><a href="view-order.php"><i class="fa fa-shopping-cart"></i> <span>View Orders</span></a></li>
                     
                   <li><a href="view-coupon.php"><i class="fa-solid fa-percent"></i>  <span>Coupon Code</span></a></li>
              <li><a href="view-batchcode.php"><i class="fa-solid fa-key"></i>  <span>Generate Batch Code</span></a></li>
                        <li><a href="change-password.php"><i class="fa-solid fa-key"></i> <span>Change Password</span></a></li>
                        
                         <li>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> <span>Log out</span>
                            </a>
                        </li>

                </ul>
            </div>
        </section>
    </aside>
</body>
</html>

<style>
    /* Main Sidebar Container */
    .main-sidebar {
        background-color: #ffd54f;
        color: #212121;
        width: 250px;
        height: 100vh;
        position: fixed;
        overflow-y: hidden;
        transition: width 0.3s, background-color 0.3s;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .main-sidebar:hover {
        /*background-color: #ffca28; */
    }

    /* User Panel */
    .user-panel {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: -6px;
        background-color: #20201c;
        /* Light yellow for the user panel */
        border-radius: 10px;
        transition: background-color 0.3s;
    }

    .user-panel:hover {
        /*background-color: #ffeb3b; */
    }

    .user-panel .image img {
        border-radius: 50%;
        width: 55px;
        height: 55px;
        margin-right: 15px;
        border: 2px solid #212121;
    }

    .user-panel .info p {
        margin: 0;
        font-weight: bold;
        color: #212121;
        /* Dark text */
    }

    .user-panel .info a {
        color: #43a047;
        /* Green online status */
        font-size: 13px;
        display: inline-block;
        margin-top: 3px;
    }

    /* Sidebar Menu */
    .sidebar-menu {
        list-style: none;
        padding-left: 0;
        margin-top: 10px;
    }

    .sidebar-menu>li {
        margin-bottom: 7px;
    }

    .sidebar-menu>li>a {
        color: #212121;
        font-weight: bold;
        padding: 4px 20px;
        display: flex;
        align-items: center;
        border-radius: 8px;
        text-decoration: none;
        transition: background-color 0.3s, padding-left 0.3s;
        background-color: #f39c12;
    }

    .sidebar-menu>li>a i {
        margin-right: 12px;
        color: white;
        /* Icon color */
    }

    .sidebar-menu>li>a:hover {
        background-color: #ffab00;
        /* Highlight link on hover */
        color: #fff;
        /* White text on hover */
    }

    .sidebar-menu>li.active>a {
        background-color: #ff8f00;
        color: white;
    }

    /* Sidebar Header */
    .sidebar-menu .header {
        color: #9e9e9e;
        padding: 10px 20px;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 14px;
        border-bottom: 1px solid #f5f5f5;
        margin-bottom: 15px;
    }

   
    @media (max-width: 767px) {
        .main-sidebar {
            width: 0;
            min-width: 0;
            overflow-x: hidden;
            left: 0;
            top: 0;
            height: 100vh;
            position: fixed;
            z-index: 2000;
            transition: width 0.3s;
        }
        body.sidebar-open .main-sidebar {
            width: 220px;
            min-width: 220px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }
        .sidebar-menu>li>a span {
            font-size: 14px;
        }
        .sidebar-menu>li>a i {
            margin-right: 10px;
        }
    }
    @media (max-width: 480px) {
        .main-sidebar {
            width: 0;
        }
        body.sidebar-open .main-sidebar {
            width: 180px;
            min-width: 180px;
        }
        .sidebar-menu>li>a span {
            font-size: 12px;
        }
    }
</style>
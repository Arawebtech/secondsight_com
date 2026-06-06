<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('admin/include/db_config.php');
include('include/session_validator.php');

// Validate user session
if (!validateUserSession($conn)) {
    forceLogout('login.php');
}

if (!isset($_SESSION['user_id']) & !isset($_SESSION['user_name'])) {
    header("Location: /login.php");
    exit;
}
unset($_SESSION['cart']);
unset($_SESSION['quantities']);
unset($_SESSION['order_summary']);

include('include/cart_logic.php');

$user_id = $_SESSION['user_id'];
$check_change_pass = false;
$check_profile_upd = false;

// Fetch user details
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update profile if the form is submitted
if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];

    $query_update = "UPDATE users SET name = ?, email = ?, mobile = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("sssi", $name, $email, $mobile, $user_id);
    
    if ($stmt_update->execute()) {
        echo "<script>alert('Profile updated successfully!');
        window.location.href = 'profile.php';
        </script>";
    } else {
        echo "<script>alert('Error updating profile!');</script>";
    }
}

if (isset($_POST['update_pass'])) {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if ($new_password !== $confirm_new_password) {
        echo "<script>alert('New Password and Confirm New Password do not match!'); window.location.href='profile.php';</script>";
        exit;
    }

    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_password);
    $stmt->fetch();

    if ($stored_password === $old_password) {
        if ($new_password === $confirm_password) {
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $new_password, $user_id);

            if ($update_stmt->execute()) {
                echo "<script>alert('Password changed successfully!'); window.location.href='profile.php';</script>";
            } else {
                echo "<script>alert('Failed to update password. Try again!');</script>";
            }
            $stmt->close();
            $update_stmt->close();
            $conn->close();
        } else {
            echo "<script>alert('New password and confirm password do not match.');</script>";
        }
    } else {
        echo "<script>alert('Old password is incorrect.'); window.location.href='profile.php';</script>";
    }
}

// Fetch all orders for the user
$query_orders = "
SELECT 
    orders.id AS order_id,
    orders.total_amount, 
    orders.order_status, 
    orders.payment_status, 
    orders.created_at 
FROM 
    orders 
WHERE 
   orders.user_id = ? AND 
   orders.order_status = 'confirmed'
ORDER BY 
    orders.created_at ASC;
";

$stmt_orders = $conn->prepare($query_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

$orders = [];
while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
}

$courses_by_order = [];

foreach ($orders as $order) {
    $order_id = $order['order_id'];

    $query_courses = "
    SELECT 
        courses.s_title AS course_name, 
        order_details.quantity, 
        order_details.price, 
        (order_details.quantity * order_details.price) AS subtotal 
    FROM 
        order_details 
    JOIN 
        courses ON order_details.course_id = courses.id 
    WHERE 
        order_details.order_id = ?
    ";

    $stmt_courses = $conn->prepare($query_courses);
    $stmt_courses->bind_param("i", $order_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();

    while ($course_row = $result_courses->fetch_assoc()) {
        $courses_by_order[$order_id][] = $course_row;
    }
}

$courses_by_order = [];

foreach ($orders as $order) {
    $order_id = $order['order_id'];

    $query_courses = "
    SELECT 
        courses.s_title AS course_name, 
        courses.description, 
        courses.url,
        courses.banner_image, 
        courses.validity, 
        courses.duration_time, 
        courses.instructor_name, 
        order_details.quantity, 
        order_details.price, 
        (order_details.quantity * order_details.price) AS subtotal 
    FROM 
        order_details 
    JOIN 
        courses ON order_details.course_id = courses.id 
    WHERE order_details.order_id = ?
";

    $stmt_courses = $conn->prepare($query_courses);
    $stmt_courses->bind_param("i", $order_id);
    $stmt_courses->execute();
    $result_courses = $stmt_courses->get_result();

    while ($course_row = $result_courses->fetch_assoc()) {
        $courses_by_order[$order_id][] = $course_row;
    }
}

// MODIFIED: Separate queries for purchased courses and batch access courses
// Query for PURCHASED courses only
$query_purchased = "
SELECT DISTINCT
    c.id,
    c.s_name,
    c.url,
    c.description,
    c.instructor_name,
    c.price,
    c.banner_image,
    c.validity,
    c.duration_time,
    o.id AS order_id, 
    o.created_at AS order_date,
    'purchased' AS access_type
FROM 
    orders o
JOIN 
    order_details od ON o.id = od.order_id
JOIN 
    courses c ON od.course_id = c.id
WHERE 
    o.user_id = ? AND o.order_status = 'confirmed'
ORDER BY order_date DESC
";

$stmt_purchased = $conn->prepare($query_purchased);
$stmt_purchased->bind_param("i", $user_id);
$stmt_purchased->execute();
$result_purchased = $stmt_purchased->get_result();

$purchased_courses = [];
while ($row = $result_purchased->fetch_assoc()) {
    $purchased_courses[] = $row;
}
$stmt_purchased->close();

// Query for BATCH ACCESS courses only
$query_batch = "
SELECT DISTINCT
    c.id,
    c.s_name,
    c.url,
    c.description,
    c.instructor_name,
    c.price,
    c.banner_image,
    c.validity,
    c.duration_time,
    NULL AS order_id,
    NULL AS order_date,
    'batch' AS access_type
FROM 
    user_batch_enrollments ube
JOIN 
    lesson_batch lb ON ube.batch_id = lb.batch_id
JOIN 
    lesson_video lv ON lb.lesson_id = lv.id
JOIN 
    courses c ON lv.course_id = c.id
WHERE 
    ube.user_id = ? AND ube.status = 'Active' AND lv.status = 'Active'
ORDER BY c.s_name ASC
";

$stmt_batch = $conn->prepare($query_batch);
$stmt_batch->bind_param("i", $user_id);
$stmt_batch->execute();
$result_batch = $stmt_batch->get_result();

$batch_courses = [];
while ($row = $result_batch->fetch_assoc()) {
    $batch_courses[] = $row;
}
$stmt_batch->close();

?>

    <?php
// Fetch notification count
$query_count = "SELECT COUNT(*) AS notification_count FROM notifications WHERE user_id = ? AND status = 'unread'";
$stmt_count = $conn->prepare($query_count);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result();
$count_data = $result_count->fetch_assoc();
$notification_count = $count_data['notification_count'];

// Fetch notification data
$query_data = "SELECT id, title, message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt_data = $conn->prepare($query_data);
$stmt_data->bind_param("i", $user_id);
$stmt_data->execute();
$result_data = $stmt_data->get_result();

$notifications = [];
while ($row = $result_data->fetch_assoc()) {
    $notifications[] = $row;
}
?>

        <?php
// Notifications logic for purchased courses only (since batch courses don't have purchase dates)
$current_date = date('Y-m-d');

foreach ($purchased_courses as $course) {
    $order_id = $course['order_id'];
    $purchase_date = $course['order_date'];
    $purchase_date = date('Y-m-d', strtotime($purchase_date));
    $validity_months = intval($course['validity']);
    $expiration_date = date('Y-m-d', strtotime("+$validity_months months", strtotime($purchase_date)));
    $one_month_before = date('Y-m-d', strtotime("-1 month", strtotime($expiration_date)));
    $two_days_before = date('Y-m-d', strtotime("-2 days", strtotime($expiration_date)));

    if ($current_date == $one_month_before) {
        $notification_message = "Your course '{$course['s_name']}' will expire on {$expiration_date}.";
        if (!checkNotificationExists($user_id, $notification_message, $current_date)) {
            sendNotification($user_id, $notification_message);
        }
    }

    if ($current_date == $two_days_before) {
        $notification_message = "Reminder: Your course '{$course['s_name']}' is expiring soon on {$expiration_date}.";
        if (!checkNotificationExists($user_id, $notification_message, $current_date)) {
            sendNotification($user_id, $notification_message);
        }
    }
}

function sendNotification($user_id, $message)
{
    global $conn;
    $query_notify = "INSERT INTO notifications (user_id, title, message, status, created_at) 
                     VALUES (?, 'Course Expiry Reminder', ?, 'unread', NOW())";
    $stmt_notify = $conn->prepare($query_notify);
    $stmt_notify->bind_param("is", $user_id, $message);
    $stmt_notify->execute();
}

function checkNotificationExists($user_id, $message, $date)
{
    global $conn;
    $query_check = "SELECT COUNT(*) AS count FROM notifications 
                    WHERE user_id = ? AND message = ? AND DATE(created_at) = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("iss", $user_id, $message, $date);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}
?>

            <!DOCTYPE html>
            <html lang="zxx">
            <?php include('include/head.php'); ?>

            <head>

                <!-- Your existing styles here -->
                <style>
                    /* All your existing CSS styles remain the same */
                    
                    @media screen and (min-width: 767px) {
                        .openbtn {
                            display: none;
                        }
                        .sidepanel {
                            position: relative;
                            width: 250px;
                            padding: 50px;
                            transition: none;
                        }
                        .sidepanel.open {
                            width: 250px;
                        }
                        .closebtn {
                            display: none;
                        }
                    }
                    
                    #mobile-notfi {
                        position: absolute;
                        right: 110px;
                    }
                    
                    .sidepanel a {
                        font-size: 1.1rem;
                    }
                    
                    @media screen and (max-width:767px) {
                        .main-logo {
                            display: none;
                        }
                        #mobile-notfi {
                            top: 12px;
                        }
                        .sidepanel {
                            width: 0;
                            position: fixed;
                            z-index: 9999;
                            height: auto;
                            top: 0;
                            left: 0;
                            background-color: #111;
                            overflow-x: hidden;
                            transition: 0.5s;
                            padding-top: 60px;
                        }
                        .sidepanel a {
                            text-decoration: none;
                            display: block;
                            transition: 0.3s;
                            font-size: 1.1rem;
                        }
                        .sidepanel a:hover {
                            color: #f1f1f1;
                        }
                        .sidepanel .closebtn {
                            position: absolute;
                            top: 0;
                            right: 25px;
                            font-size: 36px;
                        }
                        .openbtn {
                            margin-left: -25px;
                            font-size: 20px;
                            cursor: pointer;
                            background-color: #111;
                            color: white;
                            padding: 10px 15px;
                            border: none;
                        }
                        .openbtn:hover {
                            background-color: #444;
                        }
                        .sidepanel.open {
                            width: 250px;
                            height: 100%;
                            padding: 50px;
                        }
                        .Instructor,
                        .validity,
                        .duration {
                            margin-top: 0.45rem;
                        }
                    }
                    
                    .collapse:not(.show) {
                        display: block;
                    }
                    /* Add styles for batch access indicator */
                    
                    .batch-access-badge {
                        display: inline-block;
                        background: #1976d2;
                        color: #fff;
                        font-size: 0.9rem;
                        font-weight: 500;
                        border-radius: 12px;
                        padding: 2px 12px;
                        margin-left: 8px;
                        vertical-align: middle;
                    }
                    
                    .batch-course-container {
                        border: 2px solid #1976d2;
                        border-radius: 8px;
                    }
                    
                    .batch-course-title {
                        background: linear-gradient(135deg, #1976d2, #42a5f5);
                        color: white;
                    }
                    
                    .empty-state {
                        text-align: center;
                        padding: 40px;
                        color: #666;
                    }
                    
                    .empty-state i {
                        font-size: 48px;
                        margin-bottom: 16px;
                        color: #ccc;
                    }
                </style>

                <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
                <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
            </head>

            <body>
                <!-- Your existing header code remains the same -->
                <header class="navbar navbar-expand-lg navbar-light bg-light sticky-top" style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); padding: 10px 20px;">
                    <div class="container-fluid" style="justify-content: flex-start;">
                        <!-- Logo -->
                        <a class="navbar-brand" href="#" style="display: flex; align-items: center;">
                            <img src="/assets/img/logo-nn.png" class="main-logo" alt="Logo" style="height: 50px; margin-right: 10px;">
                        </a>
                        <button class="openbtn" onclick="openNav()" style="border-radius:5px;">☰  </button>

                        <div class="profile-image profile-desk" style="height:60px; width:60px; overflow: hidden; border-radius: 50%; border: 3px solid #d99b55;position:absolute;right:40px">
                            <img src="<?= $base_url ?>/<?= $user['profile_photo'] !== null ? $user['profile_photo'] : 'assets/img/profile/dpf.png'; ?>" alt="" style="height: 100%; width: 100%; object-fit: cover;" class="img-fluid">
                        </div>


                        <!--Navigation -->
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ml-auto" style="display: flex; align-items: center; flex-wrap: wrap;">
                                <li class="nav-item" id="mobile-notfi" style="list-style-type:none;">
                                    <a href="#" class="nav-link" id="notificationBell" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-bell"></i>
                                        <!-- Bell icon -->
                                        <span id="notificationCount" class="badge badge-danger"><?php echo $notification_count; ?></span>
                                        <!-- Notification count -->
                                    </a>

                                    <div class="dropdown-menu" aria-labelledby="notificationBell" style="min-width: 250px; position: absolute; right: 0;">
                                        <h6 class="dropdown-header">Notifications</h6>
                                        <?php if (count($notifications) > 0): ?>
                                        <?php foreach ($notifications as $notification): ?>
                                        <a class="dropdown-item" href="#" style="padding: 10px; border-bottom: 1px solid #f1f1f1; display: flex; flex-direction: column;white-space: pre-wrap;">
                                            <strong style="color: #007bff;"><?php echo $notification['title']; ?></strong>
                                            <p style="font-size: 0.9rem; color: #555;">
                                                <?php echo $notification['message']; ?>
                                            </p>
                                            <!--<small style="color: #888;"><?php echo date('Y-m-d H:i', strtotime($notification['created_at'])); ?></small>-->
                                            <small style="color: #888;">
                <?php echo date('l, F j, Y \a\t h:i A', strtotime($notification['created_at'])); ?>
            </small>
                                        </a>
                                        <?php endforeach; ?>
                                        <?php else: ?>
                                        <a class="dropdown-item" href="#" style="padding: 10px; color: #888;">No new notifications</a>
                                        <?php endif; ?>
                                    </div>
                                </li>


                            </ul>
                        </div>
                    </div>
                    <!--Alert for password update-->
                    <?php if($check_change_pass == true):  ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="position:absolute;top:25;right:30">
                        <strong>Success!</strong> Your password successfully updated.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php endif; 
         $check_change_pass = false;
         ?>

                        <!--Alert for profile update-->
                        <?php if($check_profile_upd == true):  ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="position:absolute;top:25;right:30">
                            <strong>Success!</strong> Your Profile Successfully Updated.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <?php endif;
         $check_profile_upd =false;
         ?>
                        </div>
                </header>
                <!-- Add your custom CSS below -->
                <style>
                    /* Dropdown Styles */
                    
                    .dropdown-menu {
                        max-height: 300px;
                        overflow-y: auto;
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
                    }
                    
                    .dropdown-header {
                        font-size: 1rem;
                        font-weight: bold;
                        color: #333;
                        padding: 10px;
                        border-bottom: 1px solid #f1f1f1;
                        background-color: #f8f9fa;
                    }
                    
                    .dropdown-item {
                        padding: 12px 15px;
                        color: #333;
                        text-decoration: none;
                        display: block;
                        transition: background-color 0.3s;
                    }
                    
                    .dropdown-item:hover {
                        background-color: #f1f1f1;
                    }
                    
                    .dropdown-item strong {
                        font-size: 1rem;
                        color: #007bff;
                    }
                    
                    .dropdown-item p {
                        font-size: 0.9rem;
                        color: #555;
                    }
                    
                    .dropdown-item small {
                        font-size: 0.75rem;
                        color: #888;
                    }
                    /* Notification Bell Container */
                    
                    #notificationBell {
                        position: relative;
                        display: inline-block;
                        cursor: pointer;
                        /* Make bell clickable */
                    }
                    /* Notification Bell Icon */
                    
                    #notificationBell i {
                        font-size: 1.5rem;
                        color: #4d4d4d;
                        /* Default bell icon color */
                    }
                    /* Notification Badge Circle */
                    
                    #notificationCount {
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        background-color: red;
                        color: white;
                        border-radius: 50%;
                        padding: 5px 10px;
                        font-size: 0.75rem;
                        font-weight: bold;
                        display: inline-block;
                        min-width: 20px;
                        /* Minimum size of the badge */
                        text-align: center;
                        line-height: 1;
                        transition: all 0.3s ease-in-out;
                        /* Smooth transition for updates */
                        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
                        /* Adding shadow for better visibility */
                    }
                    /* Make sure the badge is visible */
                    
                    #notificationCount {
                        visibility: visible;
                        /* Ensure visibility of the badge */
                    }
                    /* Notification Bell Hover Effect */
                    
                    #notificationBell:hover i {
                        color: #ff5733;
                        /* Color change on hover */
                    }
                    /* Notification Count Bounce Animation */
                    
                    @keyframes bounce {
                        0% {
                            transform: translateY(0);
                        }
                        50% {
                            transform: translateY(-5px);
                        }
                        100% {
                            transform: translateY(0);
                        }
                    }
                    /* Optional: Add bounce animation to notification count */
                    
                    #notificationCount {
                        animation: bounce 1s infinite alternate;
                    }
                    
                    .font-css-th th {
                        font-size: 16px;
                        font-weight: 400;
                    }
                    
                    .font-css-td td {
                        font-size: 16px;
                    }
                    
                    @media only screen and (max-width:576px) {
                        .font-css-th th {
                            font-size: 12px;
                            font-weight: 400;
                        }
                        .font-css-td td {
                            font-size: 12px;
                        }
                        .desc,
                        .validity,
                        .duration,
                        .Instructor,
                        .btn-success {
                            font-size: 14px;
                        }
                    }
                    
                    @media screen and (max-width: 700px) {
                        .dropdown-menu {
                            max-width: 200px;
                        }
                        .p {
                            font-size: 7px;
                        }
                    }
                </style>


                <div class="container-fluid d-flex">
                    <nav class="col-md-2 bg-light sidepanel" id="mySidepanel">
                        <div class="sidebar-sticky">
                            <div class="profile-image profile-desk" style="height:100px; width:100px; overflow: hidden; border-radius: 50%; border: 3px solid #d99b55;margin-left:10px">
                                <img src="<?= $user['profile_photo'] !== null ? $user['profile_photo'] : '/assets/img/profile/dpf.png'; ?>" alt="" style="height: 100%; width: 100%; object-fit: cover;" class="img-fluid">
                            </div>


                            <ul class="nav flex-column" id="nav-desktop">

                                <li class="nav-item">
                                    <a href="javascript:void(0)" class="closebtn">×</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#profile-section" data-toggle="tab">My Profile</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link active" href="#courses-section" data-toggle="tab">My Courses</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#order-section" data-toggle="tab">My Orders</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#batchcode" data-toggle="tab">Batch Access</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="my-batches.php">
                                        <i class="fas fa-graduation-cap"></i> My Batches
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link" href="#explore-courses-section" data-toggle="tab">
                    Explore Courses
                </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link text-danger" href="logout.php">
                    Logout
                </a>
                                </li>

                            </ul>

                        </div>
                    </nav>

                    <div class="content-area col-md-10" style="overflow:overlay;">
                        <h2>Welcome,
                            <?php echo htmlspecialchars($user['name']); ?>
                        </h2>
                        <div class="container-fluid">
                            <div class="row">



                                <!-- ===== RIGHT CONTENT AREA ===== -->
                                <div class="col-md-9 col-lg-10 p-4">

                                    <div class="tab-content">

                                        <!-- ================= PROFILE SECTION ================= -->
                                        <div class="tab-pane fade" id="profile-section">
                                            <h3>My Profile</h3>

                                            <div class="card p-3">
                                                <div class="form-group mb-3">
                                                    <label>Name</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label>Email</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label>Phone</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['mobile']); ?>" disabled>
                                                </div>

                                                <div class="mt-3">
                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#updateProfileModal">Update Profile</button>

                                                    <button class="btn btn-secondary" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- ================= COURSES SECTION ================= -->
                                        <div class="tab-pane fade show active" id="courses-section">
                                            <h3>My Purchased Courses</h3>

                                            <?php if (!empty($purchased_courses)): ?>
                                            <?php foreach ($purchased_courses as $course): ?>
                                            <div class="card mb-4 shadow-sm">
                                                <div class="row p-3">
                                                    <div class="col-md-4">
                                                        <img src="/assets/img/course-img/<?php echo htmlspecialchars($course['banner_image']); ?>" class="img-fluid rounded">
                                                    </div>

                                                    <div class="col-md-8">
                                                        <h5>
                                                            <?php echo htmlspecialchars($course['s_name']); ?>
                                                            <span class="badge bg-success">Purchased</span>
                                                        </h5>

                                                        <p><b>Instructor:</b>
                                                            <?php echo htmlspecialchars($course['instructor_name']); ?>
                                                        </p>
                                                        <p><b>Duration:</b>
                                                            <?php echo htmlspecialchars($course['duration_time']); ?>
                                                        </p>

                                                        <a class="btn btn-success" href="<?= $base_url . 'lesson.php?course_id=' . $course['id'] ?>">
                                            Watch Course
                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <p>No Purchased Courses Yet</p>
                                            <?php endif; ?>
                                        </div>


                                        <!-- ================= ORDER SECTION ================= -->
                                        <div class="tab-pane fade" id="order-section">
                                            <h3>My Orders</h3>

                                            <?php if (!empty($orders)): ?>
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Status</th>
                                                        <th>Total</th>
                                                        <th>Payment</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo $order['order_id']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $order['order_status']; ?>
                                                        </td>
                                                        <td>₹
                                                            <?php echo number_format($order['total_amount'],2); ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $order['payment_status']; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo $order['created_at']; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <?php else: ?>
                                            <p>No Orders Found</p>
                                            <?php endif; ?>
                                        </div>


                                        <!-- ================= BATCH ACCESS ================= -->
                                        <div class="tab-pane fade" id="batchcode">
                                            <h3>Batch Access</h3>

                                            <form>
                                                <input type="text" class="form-control mb-3 w-50" placeholder="Enter batch code">

                                                <button class="btn btn-success">Access</button>
                                            </form>
                                        </div>


                                        <!-- ================= EXPLORE COURSES ================= -->
                                        <div class="tab-pane fade" id="explore-courses-section">
                                            <h3>Explore Courses</h3>
                                            <p>Your existing explore courses code will go here exactly same.</p>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- All your existing modals remain unchanged -->
                <div class="modal fade" id="updateProfileModal" tabindex="-1" role="dialog" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                            </div>
                            <div class="modal-body">
                                <form id="updateProfileForm" method="POST" action="" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" pattern="[0-9]{10}" title="Enter 10 digit mobile number" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="profile_photo">Profile Photo (optional)</label>
                                        <input type="file" class="form-control-file" id="profile_photo" name="profile_photo" accept="image/*">
                                        <input type="hidden" id="cropped_image_data" name="cropped_image_data">
                                        <img id="profile_photo_preview" src="#" alt="Preview" style="display:none;max-width:200px;margin-top:10px;" />
                                    </div>
                                    <br>
                                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password Modal -->
                <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content custom-modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title w-100 text-center" id="changePasswordModalLabel" style="font-weight:600;">Change Password</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                            </div>
                            <div class="modal-body pt-0">
                                <form id="changePasswordForm" method="POST" action="">
                                    <div class="form-group mb-3">
                                        <label for="old_password" class="font-weight-bold">Old Password</label>
                                        <input type="password" class="form-control custom-input" id="old_password" name="old_password" required placeholder="Enter old password">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="new_password" class="font-weight-bold">New Password</label>
                                        <input type="password" class="form-control custom-input" id="new_password" name="new_password" required placeholder="Enter new password">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label for="confirm_new_password" class="font-weight-bold">Confirm New Password</label>
                                        <input type="password" class="form-control custom-input" id="confirm_new_password" name="confirm_new_password" required placeholder="Re-enter new password">
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <button type="submit" name="update_pass" class="btn btn-primary custom-btn w-100">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!--<div class="modal fade" id="cropperModal" tabindex="-1" role="dialog" aria-labelledby="cropperModalLabel" aria-hidden="true">-->
                <!--    <div class="modal-dialog modal-lg">-->
                <!--        <div class="modal-content">-->
                <!--            <div class="modal-header">-->
                <!--                <h5 class="modal-title" id="cropperModalLabel">Crop Profile Photo</h5>-->
                <!--                <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
                <!--                    <span aria-hidden="true">&times;</span>-->
                <!--                </button>-->
                <!--            </div>-->
                <!--            <div class="modal-body">-->
                <!--                <div class="img-container">-->
                <!--                    <img id="cropperImage" src="" alt="Picture to crop" style="max-width: 100%;">-->
                <!--                </div>-->
                <!--            </div>-->
                <!--            <div class="modal-footer">-->
                <!--                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>-->
                <!--                <button type="button" class="btn btn-primary" id="cropButton">Crop & Upload</button>-->
                <!--            </div>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->

                <!-- All your existing scripts -->
                <!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
                <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
                <?php
// include('include/footer.php');

?>
                    <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
                    <script src="assets/js/jquery.min.js"></script>


                    <script src="assets/js/bootstrap.bundle.min.js"></script>



                    <script src="assets/js/ajaxchimp.min.js"></script>

                    <script src="assets/js/custom.js"></script>

                    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>-->
                    <script>
                        // All page-specific vanilla JS and jQuery code
                        $(document).ready(function() {
                            // Batch code form submission
                            $("#batchcodeForm").submit(function(e) {
                                e.preventDefault();
                                $.ajax({
                                    url: "access-course-using-batchcode.php",
                                    type: "POST",
                                    data: $(this).serialize(),
                                    dataType: "json",
                                    success: function(response) {
                                        var messageDiv = $("#responseMessage");
                                        if (response.status === "success") {
                                            messageDiv.removeClass("errored").addClass("successed").css("color", "#155724").css("background-color", "#d4edda").text(response.message).fadeIn();
                                            setTimeout(function() {
                                                window.location.href = response.redirect;
                                            }, 2000);
                                        } else {
                                            messageDiv.removeClass("successed errored").addClass("successed").css("color", "#155724").css("background-color", "#d4edda").text(response.message).fadeIn();
                                        }
                                        setTimeout(function() {
                                            messageDiv.fadeOut();
                                        }, 5000);
                                    },
                                    error: function(xhr, status, error) {
                                        console.log("AJAX Error: ", error, xhr.responseText);
                                        $("#responseMessage").removeClass("successed errored").addClass("successed").css("color", "#155724").css("background-color", "#d4edda").text("Server Error! Check Console.").fadeIn();
                                    }
                                });
                            });

                            // Course search via AJAX
                            $('#courseSearchForm').off('submit');
                            $('#searchQueryInput').on('keyup', function() {
                                var searchQuery = $(this).val();
                                var resultsContainer = $('#courseResultsContainer');
                                $.ajax({
                                    url: 'search_courses.php',
                                    type: 'POST',
                                    data: {
                                        search_query: searchQuery
                                    },
                                    beforeSend: function() {
                                        resultsContainer.html('<p class="col-12 text-center">Searching...</p>');
                                    },
                                    success: function(response) {
                                        resultsContainer.html(response);
                                    },
                                    error: function() {
                                        resultsContainer.html('<p class="col-12 text-center">An error occurred while searching.</p>');
                                    }
                                });
                            });

                            // Tab activation from URL hash
                            var hash = window.location.hash;
                            if (hash) {
                                $('.nav-link[href="' + hash + '"]').tab('show');
                            }

                            // Update URL hash on tab click
                            $('.nav-link').on('click', function(e) {
                                if (history.pushState) {
                                    history.pushState(null, null, e.target.hash);
                                } else {
                                    window.location.hash = e.target.hash;
                                }
                            });

                            // Cropper initialization
                            let cropper = null;
                            const sidebarProfileImage = document.querySelector('#mySidepanel .profile-image img');
                            const cropperModal = document.getElementById('cropperModal');
                            const cropperImage = document.getElementById('cropperImage');
                            const cropButton = document.getElementById('cropButton');
                            const profilePhotoInput = document.getElementById('profile_photo');

                            function openCropperWithFile(file) {
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        cropperImage.src = e.target.result;
                                        $('#updateProfileModal').modal('hide');
                                        $(cropperModal).modal('show');
                                    };
                                    reader.readAsDataURL(file);
                                }
                            }

                            $(cropperModal).on('shown.bs.modal', function() {
                                if (cropper) {
                                    cropper.destroy();
                                }
                                cropper = new Cropper(cropperImage, {
                                    aspectRatio: 1,
                                    viewMode: 2,
                                    dragMode: 'move',
                                    autoCropArea: 1
                                });
                            });

                            if (sidebarProfileImage) {
                                sidebarProfileImage.addEventListener('click', function() {
                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = 'image/*';
                                    input.onchange = e => openCropperWithFile(e.target.files[0]);
                                    input.click();
                                });
                            }

                            if (profilePhotoInput) {
                                profilePhotoInput.addEventListener('change', e => openCropperWithFile(e.target.files[0]));
                            }

                            cropButton.addEventListener('click', function() {
                                if (!cropper) return;
                                const canvas = cropper.getCroppedCanvas({
                                    width: 300,
                                    height: 300
                                });
                                canvas.toBlob(function(blob) {
                                    const formData = new FormData();
                                    formData.append('profile_photo', blob, 'profile.jpg');
                                    cropButton.textContent = 'Uploading...';
                                    cropButton.disabled = true;
                                    fetch('update_profile_photo.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                const newImageUrl = data.filePath + '?t=' + new Date().getTime();
                                                document.querySelectorAll('.profile-image img').forEach(img => {
                                                    img.src = newImageUrl;
                                                });
                                                $(cropperModal).modal('hide');
                                            } else {
                                                alert('Upload failed: ' + data.message);
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                            alert('An error occurred during upload.');
                                        })
                                        .finally(() => {
                                            cropButton.textContent = 'Crop & Upload';
                                            cropButton.disabled = false;
                                        });
                                }, 'image/jpeg', 0.9);
                            });

                            $(cropperModal).on('hidden.bs.modal', function() {
                                if (cropper) {
                                    cropper.destroy();
                                    cropper = null;
                                }
                            });
                        });

                        // Vanilla JS functions and listeners
                        function togglePasswordVisibility(passwordFieldId, toggleIconId) {
                            var passwordField = document.getElementById(passwordFieldId);
                            var toggleIcon = document.getElementById(toggleIconId);
                            if (passwordField.type === "password") {
                                passwordField.type = "text";
                                toggleIcon.classList.remove("fa-eye-slash");
                                toggleIcon.classList.add("fa-eye");
                            } else {
                                passwordField.type = "password";
                                toggleIcon.classList.remove("fa-eye");
                                toggleIcon.classList.add("fa-eye-slash");
                            }
                        }

                        document.getElementById('notificationBell').addEventListener('click', function() {
                            var xhr = new XMLHttpRequest();
                            xhr.open("POST", "mark_notifications_seen.php", true);
                            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                            xhr.onload = function() {
                                if (xhr.status == 200) {
                                    document.getElementById('notificationCount').innerText = 0;
                                }
                            };
                            xhr.send("user_id=" + <?php echo $user_id; ?>);
                        });

                        function openNav() {
                            if (window.innerWidth <= 767) {
                                document.getElementById('mySidepanel').classList.add('open');
                            }
                        }

                        function closeNav() {
                            if (window.innerWidth <= 767) {
                                document.getElementById('mySidepanel').classList.remove('open');
                            }
                        }

                        document.querySelectorAll('#mySidepanel .nav-link').forEach(link => {
                            link.addEventListener('click', closeNav);
                        });

                        window.addEventListener("pageshow", function(event) {
                            if (event.persisted) {
                                location.reload();
                            }
                        });

                        // ADDED: Search functionality for purchased courses
                        const searchInput = document.getElementById('courseSearchInput');
                        if (searchInput) {
                            const courseItems = document.querySelectorAll('#courseList .course-item');
                            searchInput.addEventListener('input', function() {
                                const query = this.value.trim().toLowerCase();
                                courseItems.forEach(function(item) {
                                    const name = item.getAttribute('data-coursename');
                                    if (query === '' || name.startsWith(query)) {
                                        item.style.display = '';
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                            });
                        }

                        // ADDED: Search functionality for batch courses
                        const batchSearchInput = document.getElementById('batchCourseSearchInput');
                        if (batchSearchInput) {
                            const batchCourseItems = document.querySelectorAll('#batchCourseList .batch-course-item');
                            batchSearchInput.addEventListener('input', function() {
                                const query = this.value.trim().toLowerCase();
                                batchCourseItems.forEach(function(item) {
                                    const name = item.getAttribute('data-coursename');
                                    if (query === '' || name.startsWith(query)) {
                                        item.style.display = '';
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                            });
                        }
                    </script>


                    <style>
                        /* All your existing CSS styles remain the same */
                        
                        body {
                            background-color: #F5F5F5;
                        }
                        
                        .single-course {
                            background-color: #fff;
                            background: linear-gradient(133.21deg, #F7F7F7 -2.44%, #F9F9F9 135.62%);
                            box-shadow: -6px -6px 8px rgba(255, 255, 255, 0.8), -2px -1px 8px #FFFFFF, 2px 2px 10px rgba(255, 255, 255, 0.25), -4px -4px 20px rgba(255, 255, 255, 0.8), 1px 1px 5px rgba(185, 185, 185, 0.6), 4px 4px 15px rgba(0, 0, 0, 0.1);
                            border-radius: 10px;
                            box-sizing: border-box;
                        }
                        
                        .single-course img {
                            height: 280px;
                        }
                        
                        .course-content {
                            height: 256px;
                            position: relative;
                        }
                        
                        .course-content .btn-course-view {
                            position: absolute;
                            bottom: 10px;
                            left: 10px;
                        }
                        
                        .single-course .course-content .rating li a {
                            top: -1px;
                            font-size: 14px;
                        }
                        
                        .single-course .course-content .price {
                            top: -39px;
                            font-size: 17px;
                        }
                        
                        .single-course .course-content .price del {
                            color: #312B23;
                            font-size: 15px;
                            font-weight: 400;
                            line-height: 0;
                        }
                        
                        .single-course .course-content p {
                            border-bottom: none;
                            margin-bottom: 0;
                            max-height: unset;
                        }
                        
                        .single-course .course-content .tag {
                            max-height: unset;
                        }
                        
                        .sidebar {
                            background-color: #fff;
                            padding: 20px;
                            border-right: 1px solid #e9ecef;
                            height: 100vh;
                            position: sticky;
                            top: 0;
                            transition: all 0.3s;
                        }
                        
                        .sidebar h4 {
                            font-size: 18px;
                            margin-bottom: 20px;
                            font-weight: 600;
                            color: #1d0f96;
                        }
                        
                        .sidebar .nav-item {
                            margin: 10px 0;
                        }
                        
                        .nav-link {
                            color: #333;
                            text-decoration: none;
                            transition: color 0.3s;
                        }
                        
                        .nav-link:hover {
                            color: #1d0f96;
                        }
                        
                        .content-area {
                            padding: 20px;
                            flex: 1;
                        }
                        
                        .profile-details {
                            margin-bottom: 20px;
                            background: #fff;
                            padding: 15px;
                            border-radius: 8px;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        }
                        
                        .profile-details li {
                            list-style: none;
                            padding: 12px 0;
                            font-size: 16px;
                            border-bottom: 1px solid #e9ecef;
                        }
                        
                        .profile-details input {
                            margin-left: 10px;
                            padding: 5px;
                            border: 1px solid #ccc;
                            border-radius: 4px;
                            width: calc(100% - 20px);
                        }
                        
                        .profile-actions a,
                        .profile-actions button {
                            display: inline-block;
                            padding: 5px 9px;
                            color: #fff;
                            border-radius: 6px;
                            text-decoration: none;
                            margin-right: 10px;
                            transition: background-color 0.3s;
                            margin-bottom: 12px;
                        }
                        
                        .btn-primary {
                            background-color: #1d0f96;
                        }
                        
                        .btn-primary:hover {
                            opacity: 0.9;
                            background-color: #154a7d;
                        }
                        
                        .tab-content {
                            margin-top: 20px;
                        }
                        
                        .table {
                            width: 100%;
                            background-color: #fff;
                            border-radius: 8px;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        }
                        
                        .table-hover tbody tr:hover {
                            background-color: #f1f1f1;
                        }
                        
                        .thead-dark th {
                            background-color: #343a40;
                            color: #fff;
                            border-bottom: 0;
                        }
                        
                        .default-btn {
                            padding: 12px 25px;
                        }
                        
                        @media (max-width: 768px) {
                            .sidebar {
                                height: auto;
                                padding: 0;
                                border-right: none;
                                margin-top: 1px;
                            }
                            .sidebar h4 {
                                font-size: 16px;
                            }
                            .nav-link {
                                font-size: 14px;
                            }
                            .profile-details li {
                                font-size: 14px;
                            }
                            .profile-actions {
                                text-align: center;
                            }
                            .content-area {
                                padding: 10px;
                                margin-top: 1px;
                            }
                            .content-area h2 {
                                font-size: 16px;
                            }
                        }
                        
                        @media (max-width: 767px) {
                            .navbar-nav {
                                display: block;
                                text-align: center;
                            }
                            .navbar-nav .nav-item {
                                margin: 10px 0;
                            }
                            .navbar-nav .nav-item button {
                                display: block;
                                width: 100%;
                            }
                        }
                        
                        @media only screen and (max-width: 767px) {
                            .single-course .course-content .price {
                                top: -39px;
                                font-size: 16px;
                                width: 80px;
                                height: 40px;
                                line-height: 45px;
                            }
                            .single-course .course-content .price del {
                                font-size: 12px;
                                line-height: 0;
                            }
                            .course-content .btn-course-view {
                                position: relative;
                                margin-left: 0;
                            }
                            .course-content .btn-course-view a {
                                margin-left: -9px;
                            }
                            .single-course .course-content {
                                height: auto;
                            }
                        }
                        
                        @media only screen and (min-width: 768px) and (max-width: 1190px) {
                            .single-course .course-content .price {
                                top: -39px;
                                font-size: 16px;
                                width: 80px;
                                height: 40px;
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
                        /* Additional styles for notifications and other elements */
                        
                        .dropdown-menu {
                            max-height: 300px;
                            overflow-y: auto;
                            background-color: #ffffff;
                            border-radius: 8px;
                            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
                        }
                        
                        .dropdown-header {
                            font-size: 1rem;
                            font-weight: bold;
                            color: #333;
                            padding: 10px;
                            border-bottom: 1px solid #f1f1f1;
                            background-color: #f8f9fa;
                        }
                        
                        .dropdown-item {
                            padding: 12px 15px;
                            color: #333;
                            text-decoration: none;
                            display: block;
                            transition: background-color 0.3s;
                        }
                        
                        .dropdown-item:hover {
                            background-color: #f1f1f1;
                        }
                        
                        .dropdown-item strong {
                            font-size: 1rem;
                            color: #007bff;
                        }
                        
                        .dropdown-item p {
                            font-size: 0.9rem;
                            margin: 5px 0;
                            color: #666;
                        }
                        
                        .dropdown-item small {
                            font-size: 0.8rem;
                            color: #888;
                        }
                        
                        #notificationCount {
                            position: absolute;
                            top: -5px;
                            right: -5px;
                            background-color: #dc3545;
                            color: white;
                            border-radius: 50%;
                            padding: 2px 6px;
                            font-size: 0.75rem;
                            min-width: 18px;
                            text-align: center;
                        }
                        
                        .course-container {
                            border-radius: 8px;
                            transition: transform 0.2s, box-shadow 0.2s;
                        }
                        
                        .course-container:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        }
                        
                        .course-title-head {
                            font-size: 1.2rem;
                            font-weight: 600;
                            margin: 0;
                            border-radius: 8px 8px 0 0;
                        }
                        
                        .img-box img {
                            width: 100%;
                            height: 200px;
                            object-fit: cover;
                            border-radius: 8px;
                        }
                        
                        .content-box {
                            padding: 15px;
                        }
                        
                        .Instructor,
                        .validity,
                        .duration {
                            margin-bottom: 10px;
                            color: #555;
                        }
                        /* Modal customizations */
                        
                        .custom-modal-content {
                            border-radius: 15px;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                        }
                        
                        .custom-input {
                            border-radius: 8px;
                            border: 1px solid #ddd;
                            padding: 12px;
                            transition: border-color 0.3s, box-shadow 0.3s;
                        }
                        
                        .custom-input:focus {
                            border-color: #007bff;
                            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                        }
                        
                        .custom-btn {
                            border-radius: 8px;
                            padding: 12px 24px;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            transition: all 0.3s;
                        }
                        
                        .custom-btn:hover {
                            transform: translateY(-1px);
                            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
                        }
                        /* Responsive font sizes */
                        
                        .font-css-th {
                            font-size: 0.9rem;
                            font-weight: 600;
                            background-color: #f8f9fa;
                        }
                        
                        .font-css-td {
                            font-size: 0.85rem;
                            vertical-align: middle;
                        }
                        /* Price styling for course cards */
                        
                        .price-container {
                            display: flex;
                            align-items: center;
                            margin-bottom: 10px;
                        }
                        
                        .discounted-price {
                            font-size: 1.2rem;
                            font-weight: bold;
                            color: #28a745;
                            margin-right: 8px;
                        }
                        
                        .original-price {
                            font-size: 1rem;
                            text-decoration: line-through;
                            color: #6c757d;
                        }
                        
                        .discount-badge {
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            background-color: #dc3545;
                            color: white;
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-size: 0.8rem;
                            font-weight: bold;
                            z-index: 1;
                        }
                        /* Alert styles */
                        
                        .alerted {
                            padding: 10px;
                            border-radius: 5px;
                            margin: 10px 0;
                            display: none;
                        }
                        
                        .alerted.successed {
                            background-color: #d4edda;
                            border: 1px solid #c3e6cb;
                            color: #155724;
                        }
                        
                        .alerted.errored {
                            background-color: #f8d7da;
                            border: 1px solid #f5c6cb;
                            color: #721c24;
                        }
                        /* Loading states */
                        
                        .loading {
                            opacity: 0.6;
                            pointer-events: none;
                        }
                        /* Profile photo styles */
                        
                        .profile-image {
                            cursor: pointer;
                            transition: transform 0.2s;
                        }
                        
                        .profile-image:hover {
                            transform: scale(1.05);
                        }
                        
                        .img-container {
                            max-height: 400px;
                            overflow: hidden;
                        }
                        /* Course grid responsiveness */
                        
                        @media (max-width: 576px) {
                            .img-box,
                            .content-box {
                                margin-bottom: 15px;
                            }
                            .course-title-head {
                                font-size: 1rem;
                                padding: 4%;
                            }
                            .btn {
                                font-size: 0.9rem;
                                padding: 8px 16px;
                            }
                        }
                    </style>





                    <!-- Bootstrap CSS -->
                    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


                    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

                    <!-- Bootstrap JS -->
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>




            </body>

            </html>
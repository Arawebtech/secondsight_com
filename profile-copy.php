<?php
session_start();

if (!isset($_SESSION['user_id']) & !isset($_SESSION['user_name'])) { // Replace 'user_id' with your session variable for logged-in status
    // Redirect to the login page
    header("Location: /login.php"); // Replace 'login.php' with the path to your login page
    exit; // Stop further execution of the script
}

include('admin/include/db_config.php');
include('include/cart_logic.php'); 

$user_id = $_SESSION['user_id'];
$check_change_pass =false;
$check_profile_upd =false;


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
    
    // Check if a file was uploaded
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['profile_photo']['tmp_name'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = $user_id . '.' . $file_extension;
        $upload_dir = 'assets/img/profile/';
        $dest_path = $upload_dir . $new_file_name;

        // Move the uploaded file to the destination directory
        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            // Update the user's profile photo path in the database
            $query_update = "UPDATE users SET name = ?, email = ?, mobile = ?, profile_photo = ? WHERE id = ?";
            $stmt_update = $conn->prepare($query_update);
            $stmt_update->bind_param("ssssi", $name, $email, $mobile, $dest_path, $user_id);
              $_SESSION['profile_photo'] = $dest_path;
        } else {
            echo "<script>alert('Error uploading profile photo!');</script>";
            exit; // Stop further execution if the upload fails
        }
    } else {
        // If no file was uploaded, update without changing the profile photo
        $query_update = "UPDATE users SET name = ?, email = ?, mobile = ? WHERE id = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sssi", $name, $email, $mobile, $user_id);
    }

    // Execute the update query
    if ($stmt_update->execute()) {
        echo "<script>alert('Profile updated successfully!');
        window.location.href = 'profile.php';
        </script>";
        // $check_profile_upd=true;
    } else {
        echo "<script>alert('Error updating profile!');</script>";
    }
}

if (isset($_POST['update_pass'])) {
    $user_id = $_SESSION['user_id']; // Assuming user is logged in
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Fetch old password from database
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_password);
    $stmt->fetch();

    // Verify old password
    if ($stored_password === $old_password) {
        // Update with new password
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $new_password, $user_id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Password changed successfully!'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Failed to update password. Try again!');</script>";
        }
    } else {
        echo "<script>alert('Old password is incorrect.'); window.location.href='profile.php';</script>";
    }

    $stmt->close();
    $update_stmt->close();
    $conn->close();
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
    orders.user_id = ? 
ORDER BY 
    orders.created_at ASC;
";

$stmt_orders = $conn->prepare($query_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

// Store fetched orders in an array
$orders = [];
while ($row = $result_orders->fetch_assoc()) {
    $orders[] = $row;
}

// Initialize an array for courses
$courses_by_order = [];

// Fetch courses for each order
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

    // Store courses in the array
    while ($course_row = $result_courses->fetch_assoc()) {
        $courses_by_order[$order_id][] = $course_row;
    }
}

// Initialize an array for courses
$courses_by_order = [];

// Fetch courses for each order
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

    // Store courses in the array
    while ($course_row = $result_courses->fetch_assoc()) {
        $courses_by_order[$order_id][] = $course_row;
    }
}


$user_id = $_SESSION['user_id'];


$query = "
SELECT 
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
    o.created_at AS order_date
FROM 
    orders o
JOIN 
    order_details od ON o.id = od.order_id
JOIN 
    courses c ON od.course_id = c.id
WHERE 
    o.user_id = ?
";


// Prepare the statement
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id); // Bind the user ID as an integer

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Initialize an array to store the course details
$course_details = [];

// Fetch the data
while ($row = $result->fetch_assoc()) {
    $course_details[] = $row; // Store each row in the array
}

// Close the statement
$stmt->close();


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

// Initialize an array to store notifications
$notifications = [];
while ($row = $result_data->fetch_assoc()) {
    $notifications[] = $row;
}

?>
<?php
$current_date = date('Y-m-d'); 

foreach ($course_details as $course) {
    // Get order_id and created_at directly from $course_details
    $order_id = $course['order_id'];
    $purchase_date = $course['order_date']; 

    // Format the purchase_date
    $purchase_date = date('Y-m-d', strtotime($purchase_date));

    // Calculate expiration date
    $validity_months = intval($course['validity']);
    $expiration_date = date('Y-m-d', strtotime("+$validity_months months", strtotime($purchase_date)));

    // Calculate reminders
    $one_month_before = date('Y-m-d', strtotime("-1 month", strtotime($expiration_date)));
    $two_days_before = date('Y-m-d', strtotime("-2 days", strtotime($expiration_date)));

    // Notifications logic
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

// Notification Function
function sendNotification($user_id, $message) {
    global $conn; // Use the database connection
    $query_notify = "INSERT INTO notifications (user_id, title, message, status, created_at) 
                     VALUES (?, 'Course Expiry Reminder', ?, 'unread', NOW())";
    $stmt_notify = $conn->prepare($query_notify);
    $stmt_notify->bind_param("is", $user_id, $message);
    $stmt_notify->execute();
}

// Check if notification already exists
function checkNotificationExists($user_id, $message, $date) {
    global $conn;
    $query_check = "SELECT COUNT(*) AS count FROM notifications 
                    WHERE user_id = ? AND message = ? AND DATE(created_at) = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("iss", $user_id, $message, $date);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0; // Return true if notification exists
}

?>
    <!DOCTYPE html>
    <html lang="zxx">
    <?php include('include/head.php'); ?>
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <!-- Bootstrap CSS -->
    <style>
            /*for desktop*/
            @media screen and (min-width: 767px) {
                .openbtn{
                    display:none;
                }
                 .sidepanel {
                    position: relative;
                    width: 250px;
                    padding:50px;
                    transition: none; /* Disable animation for fixed state */
                }
                .sidepanel.open {
                    width: 250px;
                }
                .closebtn {
                    display: none; /* Hide close button */
                }
                
            }
            
              #mobile-notfi{
                    position:absolute;
                    right:110px;
                  
                }
                .sidepanel a {
                 
                 font-size:1.1rem;
                }
            @media screen and (max-width:767px)
            {
                .main-logo{
                    display:none;
                }
                 #mobile-notfi{
                     top:12px;
                }
                 .sidepanel  {
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
                 font-size:1.1rem;
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
                    margin-left:-25px;
                  font-size: 20px;
                  cursor: pointer;
                  background-color: #111;
                  color: white;
                  padding: 10px 15px;
                  border: none;
                }
                
                .openbtn:hover {
                  background-color:#444;
                }
                /* When open */
                .sidepanel.open {
                    width: 250px; /* Adjust as needed */
                    height:100%;
                    padding:50px;
                }
                .Instructor, .validity, .duration{
                    margin-top:0.45rem;
                }
           
            }
            .collapse:not(.show) {
                display: block;
            }
    </style>
    
    </head>
    
    
    <body>
    
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
                        <i class="fas fa-bell"></i> <!-- Bell icon -->
                        <span id="notificationCount" class="badge badge-danger"><?php echo $notification_count; ?></span> <!-- Notification count -->
                    </a>
        
                <div class="dropdown-menu" aria-labelledby="notificationBell" style="min-width: 250px; position: absolute; right: 0;">
                    <h6 class="dropdown-header">Notifications</h6>
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <a class="dropdown-item" href="#" style="padding: 10px; border-bottom: 1px solid #f1f1f1; display: flex; flex-direction: column;white-space: pre-wrap;">
                                <strong style="color: #007bff;"><?php echo $notification['title']; ?></strong>
                                <p style="font-size: 0.9rem; color: #555;"><?php echo $notification['message']; ?></p>
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
            cursor: pointer; /* Make bell clickable */
        }
    
        /* Notification Bell Icon */
        #notificationBell i {
            font-size: 1.5rem;
            color: #4d4d4d; /* Default bell icon color */
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
            min-width: 20px; /* Minimum size of the badge */
            text-align: center;
            line-height: 1;
            transition: all 0.3s ease-in-out; /* Smooth transition for updates */
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); /* Adding shadow for better visibility */
        }
    
        /* Make sure the badge is visible */
        #notificationCount {
            visibility: visible; /* Ensure visibility of the badge */
        }
    
        /* Notification Bell Hover Effect */
        #notificationBell:hover i {
            color: #ff5733; /* Color change on hover */
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
            .font-css-th th{
                font-size:16px;
                font-weight:400;
            }
            .font-css-td td{
                font-size:16px;
            }
        @media only screen and (max-width:576px)
        {
            .font-css-th th{
                font-size:12px;
                font-weight:400;
            }
            .font-css-td td{
                font-size:12px;
            }
            .desc,.validity,.duration,.Instructor,.btn-success{
                font-size:14px;
            }
        }
        
             @media screen and (max-width: 700px) {
                 .dropdown-menu{
                    max-width: 200px;
  
                 }
                 .p{
                     font-size:7px;
                 }
               
             }
    </style>

    <div class="container-fluid d-flex">
        <nav class="col-md-2 bg-light sidepanel" id="mySidepanel">
            <div class="sidebar-sticky">
              
                <!--<h4 class="sidebar-heading">Profile</h4>-->
                 
                    <div class="profile-image profile-desk" style="height:100px; width:100px; overflow: hidden; border-radius: 50%; border: 3px solid #d99b55;margin-left:10px">
                    <img src="<?= $base_url ?>/<?= $user['profile_photo'] !== null ? $user['profile_photo'] : 'assets/img/profile/dpf.png'; ?>" alt="" style="height: 100%; width: 100%; object-fit: cover;" class="img-fluid">
                </div>
                <ul class="nav flex-column" id="nav-desktop">
                     <li class="nav-item">
                           <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
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
                    <a class="nav-link" href="#explore-courses-section" data-toggle="tab">Explore New Courses</a>
                </li>
                   
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
               
            </div>
        </nav>

        <div class="content-area col-md-10" style="overflow:overlay;">
            <h2>Welcome,
                <?php echo htmlspecialchars($user['name']); ?>
            </h2>
            <div class="tab-content">
                        <div class="tab-pane fade col-md-5" id="profile-section">
                        <h3>My Profile</h3>
                        <ul class="profile-details">
                            <li>Name: <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                            </li>
                            <li>Email: <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </li>
                            <li>Phone: <input type="text" value="<?php echo htmlspecialchars($user['mobile']); ?>" disabled>
                            </li>
                        </ul>
                        
                         <li class="nav-item d-flex flex-wrap justify-content-center" style="gap: 10px; margin-top: 10px;">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#updateProfileModal" style="padding: 8px 15px; font-size: 0.9rem; display: inline-block;">Update Profile</button>
                    <button class="btn btn-secondary" data-toggle="modal" data-target="#changePasswordModal" style="padding: 8px 15px; font-size: 0.9rem; display: inline-block;">Change Password</button>
                </li>
                       
                    </div>
                        
                        <!--Order details in table-->
                        <div class="tab-pane fade" id="order-section">
                       <?php if (!empty($orders)): ?>
                            <h3>My Courses</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="font-css-th">
                                        <th>Order ID</th>
                                        <th>Order Status</th>
                                        <th>Total Amount</th>
                                        <th>Payment Status</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr class="font-css-td" >
                                        <td>
                                            <?php echo htmlspecialchars($order['order_id']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($order['order_status']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($order['payment_status']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($order['created_at']); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
        
                            <h4>Courses in Orders</h4>
                            <?php foreach ($orders as $order): ?>
                            <h5>Courses for Order ID:
                                <?php echo htmlspecialchars($order['order_id']); ?>
                            </h5>
                            <table class="table table-bordered">
                                <thead>
                                     <tr class="font-css-th">
                                        <th >Course Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($courses_by_order[$order['order_id']])): ?>
                                    <?php foreach ($courses_by_order[$order['order_id']] as $course): ?>
                                    <tr class="font-css-td">
                                        <td >
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($course['quantity']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(number_format($course['price'], 2)); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(number_format($course['subtotal'], 2)); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="4">No courses found for this order.</td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    
                                </tbody>
                            </table>
                            
                    
                       
                        <?php endforeach; ?>
                        <?php else: ?>
                            <p>You have not purchase any courses.</p>
                        <?php endif; ?>
                        </div>
                        
                        
                        <!-- Display Course Details -->
                         <div class="tab-pane fade show active" id="courses-section" style="overflow-x:hidden;">
                        <?php if (!empty($course_details)): ?>
                           <?php foreach ($course_details as $course): ?>
                            <div class="course-container border" style="background-color:#fff;margin-bottom:1rem;">
                                <h3 class="course-title-head py-1" style="background-color:#d3d3cf;padding:6%;"><?php echo htmlspecialchars($course['s_name']); ?></h3>
                                <div class="row" style="padding:2% 6%;">
                                    <div class="img-box col-lg-4 col-md-6 col-12">
                                        <img src="/assets/img/course-img/<?php echo htmlspecialchars($course['banner_image']); ?>" alt="course_img">
                                    </div>
                                    <div class="content-box col-lg-8 col-md-6 col-12 row">
                                        <!--<div class="desc"><?php echo strlen($course['description']) > 100 ? substr($course['description'], 0, 100) . '...' : $course['description']; ?></div>-->
                                        <div class="Instructor"><b>Instructor:</b> <?php echo htmlspecialchars($course['instructor_name']); ?></div>
                                        <div class="validity"><b>Validity:</b> <?php echo htmlspecialchars($course['validity']); ?> Months</div>
                                        <div class="duration"><b>Duration:</b> <?php echo htmlspecialchars($course['duration_time']); ?> </div>
                                        <div style="padding-top:-20px; margin-top: 20px;">
                                               <a class="btn btn-success float-end" 
                                               href="<?= htmlspecialchars($base_url . 'profile/' . $course['url'] . '/' . $course['id']) ?>"
                                               style="font-size:1rem;">Watch Course</a>
                                     </div>
                                    </div>
                                </div>
                        </div>
                          <?php endforeach; ?>
                          <?php else: ?>
                            <p>You have not purchase any courses.</p>
                        <?php endif; ?>
                      </div>
                        
                 
                 
                 
                 <!--<other courses></other>-->
<!--                 <div class="tab-pane fade" id="explore-courses-section">-->
<!--    <h3>Other Courses</h3>-->
<!--    <section class="courses-area-style ptb-60">-->
<!--        <div class="container">-->
          
<!--            </div>-->
<!--            <div class="row">-->

<script>
    //   function addToCart(courseId) {
      
    //     window.location.href = "../cart.php?course_id=" + courseId;
    // }
</script>

      
                        

            </div>
        </div>
        
    </div>

    <!-- Modals for updating profile and changing password -->
    <div class="modal fade" id="updateProfileModal" tabindex="-1" role="dialog"
        aria-labelledby="updateProfileModalLabel" aria-hidden="true">
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
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="mobile"
                                value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                        </div>
                        
                         <div class="form-group">
                            <label for="profile_photo">Profile Photo (optional)</label>
                            <input type="file" class="form-control-file" id="profile_photo" name="profile_photo">
                        </div>
                        
                        <br>
                        
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
  <!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog"
    aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm" method="POST" action="">
                    <!-- Old Password Field -->
                    <div class="form-group">
                        <label for="old_password">Old Password</label>
                        <input type="password" class="form-control" id="old_password" style="height: 35px; width: 52%;" name="old_password" required>
                    </div>
                    <br>
                    <!-- New Password Field -->
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" style="height: 35px; width: 52%;" name="new_password" required>
                    </div>
                    <br>
                    <button type="submit" name="update_pass" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>


   
    <!--</body>-->
    <!--</html>-->


    <!--<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php
// include('include/footer.php');

?>
<script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="assets/js/jquery.min.js"></script>


    <script src="assets/js/bootstrap.bundle.min.js"></script>



    <script src="assets/js/ajaxchimp.min.js"></script>

    <script src="assets/js/custom.js"></script>

</body>

<!-- Mirrored from templates.hibootstrap.com/eduon/default/index-3.html by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 03 Oct 2024 07:22:43 GMT -->

</html>
<style>
    .sidebar {
        background-color: #fff;
        padding: 20px;
        border-right: 1px solid #e9ecef;
        height: 100vh;
        /* Full height */
        position: sticky;
        /* Keep the sidebar in view */
        top: 0;
        /* Stick to the top */
        transition: all 0.3s;
        /* Smooth transition */
    }

    .sidebar h4 {
        font-size: 18px;
        margin-bottom: 20px;
        font-weight: 600;
        color: #1d0f96;
        /* Highlighted color */
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
        /* Take remaining space */
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
        /* Adjust width for input */
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
        /* Darker shade on hover */
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

    /* Responsive styles */
    @media (max-width: 768px) {
        .sidebar {
            height: auto;
            /* Allow sidebar to collapse */
            padding: 0;
            border-right: none;
            /* Remove right border for mobile view */
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
    
    
    /* For mobile view */
@media (max-width: 767px) {
    .navbar-nav {
        display: block;
        text-align: center;
    }

    .navbar-nav .nav-item {
        margin: 10px 0;
    }

    .navbar-nav .nav-item button {
        display: block; /* Make the buttons block-level for stacking */
        width: 100%; /* Make buttons take up full width on mobile */
    }
}

</style>

<script>
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
    // Use AJAX to update the notification status to "read"
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "mark_notifications_seen.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("user_id=" + <?php echo $user_id; ?>);

    xhr.onload = function() {
        if (xhr.status == 200) {
            // After marking as seen, update the count
            document.getElementById('notificationCount').innerText = 0; // Set the count to 0
        }
    };
});


// Open the sidebar
function openNav() {
    if (window.innerWidth <= 767) { // Only toggle for smaller screens
        const sidepanel = document.getElementById('mySidepanel');
        sidepanel.classList.add('open');
    }
}

// Close the sidebar
function closeNav() {
    if (window.innerWidth <= 767) { // Only toggle for smaller screens
        const sidepanel = document.getElementById('mySidepanel');
        sidepanel.classList.remove('open');
    }
}

// Close the sidebar when a link is clicked (only for smaller screens)
document.querySelectorAll('#mySidepanel .nav-link').forEach(link => {
    link.addEventListener('click', function () {
        closeNav();
    });
});

</script>

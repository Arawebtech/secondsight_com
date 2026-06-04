<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}

include('include/db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure user is logged in
    if (!isset($_SESSION['name'])) {
        echo "<script>alert('You must be logged in to change your password.');</script>";
        exit;
    }

    // Get data from form
    $username = $_SESSION['name'];
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if fields are filled
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo "<script>alert('All fields are required.');</script>";
        exit;
    }

    // Validate new password and confirm password
    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('New password and confirm password do not match.');</script>";
        // exit;
    }
    else
    {
         // Fetch current password from the database
        $query = "SELECT password FROM admin WHERE name = '$username'";
        $result = mysqli_query($conn, $query);
    
        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
    
            // Verify old password
            if ($oldPassword !== $admin['password']) {
                echo "<script>alert('Old password is incorrect.');</script>";
                mysqli_close($conn);
                // exit;
            }
            else
            {
                 // Update the password in the database
                $updateQuery = "UPDATE admin SET password = '$newPassword' WHERE name = '$username'";
                if (mysqli_query($conn, $updateQuery)) {
                    echo "<script>alert('Password updated successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to update password. Please try again later.');</script>";
                }
            }
           
        } else {
            echo "<script>alert('Admin not found.');</script>";
        }
    }
    // Close the database connection
    mysqli_close($conn);
}
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

     /*passsword css*/
     .containerss {
         max-width:450px;
     }
     .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="password"],input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 38px;
            cursor: pointer;
            color: #888;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body class="hold-transition skin-yellow sidebar-mini">
    <div class="wrapper">
        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h2>
                   
                    Change Password
                </h2>
              
            </section>

            <section class="content">
               <div class="containerss">
                <!--<h2>Change Password</h2>-->
            <form action="" method="POST">
                    <div class="form-group">
                        <label for="old_password">Old Password:</label>
                        <input type="password" id="old_password" name="old_password" required>
                        <span class="toggle-password" onclick="togglePassword('old_password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <span class="toggle-password" onclick="togglePassword('new_password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <button type="submit">Change Password</button>
                </form>
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
 <script>
        // Toggle password visibility
        function togglePassword(fieldId, toggleIcon) {
            const passwordField = document.getElementById(fieldId);
            const icon = toggleIcon.querySelector("i");
            const isPasswordVisible = passwordField.type === "text";

            // Toggle the input type
            passwordField.type = isPasswordVisible ? "password" : "text";

            // Toggle the icon class
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        }
    </script>
</html>
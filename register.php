<?php
session_start(); 
include('admin/include/db_config.php');
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (isset($_POST['register'])) {
    // Database connection assumed: $conn

    // Sanitize inputs
    $first_name = htmlspecialchars(strip_tags(trim($_POST['first_name'])));
    $middle_name = htmlspecialchars(strip_tags(trim($_POST['middle_name'])));
    $last_name = htmlspecialchars(strip_tags(trim($_POST['last_name'])));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mobile = htmlspecialchars(strip_tags(trim($_POST['mobile'])));
    $password = trim($_POST['password']);
    $created_date = date('Y-m-d H:i:s');

    // Basic validation - first name and last name are mandatory
    if (empty($first_name) || empty($last_name) || empty($email) || empty($mobile) || empty($password)) {
        echo "<script>
                alert('Please fill in all the required fields (First Name, Last Name, Email, Mobile, Password).');
                window.location.href = 'register.php';
              </script>";
        exit();
    }

    
    // Concatenate name fields - include middle name only if provided
    if (!empty($middle_name)) {
        $full_name = $first_name . ' ' . $middle_name;
    } else {
        $full_name = $first_name;
    }



    // Check if email already exists
    $check_email = "SELECT id, is_verify FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_email);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['is_verify'] == 0) {
            echo "<script>
                    alert('Email already exists. Please verify your email.');
                    window.location.href = 'login.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Email already exists. Please use another email.');
                    window.location.href = 'register.php';
                  </script>";
        }
        exit();
    }
    try {
        // Generate a unique token
        $token = bin2hex(random_bytes(16));

        // Insert user data into database using the concatenated full name
        $query = "INSERT INTO users (name, email, mobile, password, token, is_verify, created_date) 
                  VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssss', $full_name, $email, $mobile, $password, $token, $created_date);
        $execute_result = mysqli_stmt_execute($stmt);

        if ($execute_result) {

            // Add notification for new user registration
            $notification_message = "New user has joined: " . $full_name . " (" . $email . ")";
            $notification_query = "INSERT INTO notifications (user_id, title, message, created_at, status) VALUES (?, ?, ?, ?, ?)";
            $notification_stmt = mysqli_prepare($conn, $notification_query);
            $user_id = mysqli_insert_id($conn); // Get the ID of the newly inserted user
            $notification_title = "New User Registration";
            $notification_status = "unread";
            mysqli_stmt_bind_param($notification_stmt, 'issss', $user_id, $notification_title, $notification_message, $created_date, $notification_status);
            mysqli_stmt_execute($notification_stmt);

            // Email sending using PHP's native mail()
            $to = $email;
            $subject = "Verify Your Email - Secondsightfoundation.com";

            $verification_link = "https://www.secondsightfoundation.com/verify.php?token=$token";
            $logo_url = "https://www.secondsightfoundation.com/assets/img/n-logo.png"; // $base_url remove करके fixed कर दिया

            $message = "
            <html>
            <head>
            <title>Verify Your Email</title>
            </head>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='text-align: center; padding: 20px;'>
                    <img src='$logo_url' alt='Company Logo' width='150'>
                </div>
                <div style='padding: 20px;'>
                    <h2>Hello, $first_name!</h2>
                    <p>Thank you for registering with us. Please click the button below to verify your email:</p>
                    <p style='text-align: center;'>
                        <a href='$verification_link' style='background-color: #007BFF; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Verify Your Email</a>
                    </p>
                    <p>If the button does not work, copy and paste this link into your browser:</p>
                    <p><a href='$verification_link'>$verification_link</a></p>
                </div>
                <div style='text-align: center; margin-top: 20px; font-size: 12px; color: #666;'>
                    &copy; 2025 Secondsight. All rights reserved.
                </div>
            </body>
            </html>
            ";

            // Headers
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Secondsightfoundation <noreply@secondsightfoundation.com>" . "\r\n";

            if (mail($to, $subject, $message, $headers)) {
                echo "<script>
                        alert('Please check your email to verify your account.');
                        window.location.href = 'login.php';
                      </script>";
                exit();
            } else {
                echo "<script>alert('Failed to send verification email. Please try again later.');</script>";
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>




<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');

?>
<style>
    @media only screen and (max-width:767px) {
        #register {
            font-size: 16px;
            padding: 10px;
            margin-left: 0;
        }
      
    }
   
    #register {
        color: #fff;
        border: none;
    }

    #register:hover {
        background-color: #fff;
        color: var(--main-color);
        font-weight: 400;
        border: 1px solid var(--main-color);
        transition: var(--transition);

    }

    .required-field {
        color: red;
    }
</style>

<body style="background-color: #dfdfdf;">

    <?php
    include('include/header1.php');

    ?>



    <section class="user-area-style pb-100">
        <div class="container">
            <div class="row justify-content-center">

                <div class="col-lg-5 px-0" style="background-color: #fff;">
                    <div class="section-title text-center" style="padding: 0px;">
                        <h2>Registration</h2>
                    </div>
                    <div class="contact-form-action">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="first_name" placeholder="First Name *" required>
                                        <small class="required-field">* Required</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="middle_name" placeholder="Middle Name (Optional)">
                                        <small class="text-muted">Optional</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea class="form-control" name="last_name" placeholder="Full Address *" required></textarea>
                                        <small class="required-field">* Required</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" type="email" name="email" placeholder="Email Address *" required>
                                        <small class="required-field">* Required</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" type="tel" name="mobile" placeholder="Mobile No. *" required>
                                        <small class="required-field">* Required</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group password-field">
                                        <input id="passwordField" class="form-control" type="password" name="password" placeholder="Password *" required>
                                        <small class="required-field">* Required</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6">
                                            <button class="default-btn register ms-0" name="register" type="submit"
                                                id="register">
                                                Register Now
                                            </button>
                                        </div>
                                        <div class="col-lg-6 col-sm-6 text-right">
                                            <input id="showPassword" type="checkbox" onclick="togglePassword()">
                                            <label for="showPassword">Show password?</label>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    function togglePassword() {
                                        var passwordField = document.getElementById("passwordField");
                                        if (passwordField.type === "password") {
                                            passwordField.type = "text";
                                        } else {
                                            passwordField.type = "password";
                                        }
                                    }
                                </script>

                                <div class="col-12">
                                    <p style="text-align:left;">Have an account? <a href="login.php">Login Now!</a></p>
                                </div>
                            </div>
                        </form>
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
   
    </script>
</body>

</html>
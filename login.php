<?php
session_start();
include('admin/include/db_config.php');

// Check if user is already logged in
// if (isset($_SESSION['user_id'])) {
//     header('Location: profile.php'); 
//     exit();
// }

if (isset($_POST['login'])) {
     $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));

    $query1 = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query1);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($user['is_verify'] == 0) {
            // User has not verified their email
            echo "<script>alert('Please verify your email before logging in.');
            window.location.href = 'login.php';
            </script>";
            exit();
        }
        if ($user['is_active'] == 0) {
            // User is inactive
            echo "<script>alert('Your account is inactive. Please contact support.');
            window.location.href = 'login.php';
            </script>";
            exit();
        }

        // Verify password
        if ($password === $user['password']) {
            // Generate unique session ID
            $session_id = uniqid() . '_' . time() . '_' . rand(1000, 9999);
            
            // Update user's session ID in database
            $update_session = "UPDATE users SET user_session_id = ? WHERE id = ?";
            $stmt = $conn->prepare($update_session);
            $stmt->bind_param("si", $session_id, $user['id']);
            $stmt->execute();
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['profile_photo'] = $user['profile_photo'];
            $_SESSION['user_session_id'] = $session_id;

            // Redirect to profile page
            header('Location: profile.php');
            
            // Check if redirect parameter exists
// $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'profile.php';

// header("Location: $redirect");
// exit();
            
        } else {
            // Invalid password
            echo "<script>alert('Invalid credentials. Please try again.');
            window.location.href = 'login.php';
            </script>";
        }
    } else {
        // Invalid email
        echo "<script>alert('Invalid email. Please try again or register first.');
        window.location.href = 'login.php';
        </script>";
    }
}

?>


<!DOCTYPE html>
<html lang="zxx">

<?php
include('include/head.php');

?>

<body style="background-color: #dfdfdf;">

    <?php
    include('include/header1.php');

    ?>
    <style>
        @media screen and (max-width:600px) {
            .login-btn-mobile {
                margin-left: 0;
                padding: 10px;
            }
        }
        .user-area-style .contact-form-action form .form-group {
           margin-bottom:0;
        }
         .password-field {
            position: relative;
            width: 100%;
            margin-bottom: 30px;
            
     }
           .password-field .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #4B4B4B;
        }
         .login-btn-mobile{
            margin-top:50px;
        }
    
    </style>


    <section class="user-area-style pb-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 px-0" style="background-color: #fff;">
                    <div class="section-title text-center" style="padding: 0px;">
                        <h2>Log In</h2>
                    </div>
                    
                    <?php if (isset($_GET['session_expired'])): ?>
                    <div class="alert alert-warning text-center" style="margin: 10px;">
                        <strong>Session Expired!</strong> You have been logged out because you logged in from another device. Please log in again.
                    </div>
                    <?php endif; ?>
                    
                    <div class="contact-form-action mb-50">
                        <form method="post" action="">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <input class="form-control" type="email" name="email" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group password-field" style="top: 69%;">
                                        <input class="form-control" type="password" name="password" id="password"
                                            placeholder="Password">
                                              <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                  
                                    </div>
                                </div>
                                <!--<div class="col-12">-->
                                <!--    <div class="login-action">-->
                                <!--        <span class="log-rem">-->
                                <!--            <input id="remember" type="checkbox">-->
                                <!--            <label for="remember">Remember me!</label>-->
                                <!--        </span>-->
                                      
                                <!--    </div>-->
                                <!--</div>-->
                                <div class="col-12">
                                    <button class="default-btn login-btn-mobile" name="login" type="submit">
                                        Log In Now
                                    </button>
                                </div>
                                 <div class="col-12">
                                    <div class="login-action" style="float:left;">
                                        <span class="forgot-login">
                                            <a href="forgot_password.php">Forgot your password?</a>
                                        </span>
                                    </div>
                                </div>
                                <!--<div class="col-12">-->
                                <!--    <p  style="text-align:left;">Have an account? <a href="register.php">Registration Now!</a></p>-->
                                <!--</div>-->
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

  <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</html>
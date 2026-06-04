<?php
session_start();
include('include/cart_logic.php'); 
include('admin/include/db_config.php');
$user_id = $_SESSION['user_id'];

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<?php 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT password FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $password = $row['password'];

        // Email content
        $to = $email;
        $subject = 'User Password Recovery';
        $message = "Your password is: " . $password;
        $headers = "From: Secondsightfoundation.com <gurujimanishsharma@gmail.com>\r\n";
        $headers .= "Reply-To: gurujimanishsharma@gmail.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo "<script>alert('An email has been sent with your password. Please check your email: $email');
             window.location.href = 'login.php';
            </script>";
        } else {
            echo "<script>alert('Failed to send email. Please try again later.');</script>";
        }
    } else {
        echo "<script>alert('No user found with that email address.');</script>";
    }
}
?>




<!DOCTYPE html>
<html lang="zxx">


<?php
include('include/head.php');

?>
<body>

<?php
include('include/header.php');

?>

<style>
    @media screen and (max-width:700px)
    {
        .default-btn{
            padding:10px 16px;
           margin-left:0;
        }
    }
</style>

<!--<div class="page-title-area bg-7">-->
<!--<div class="container">-->
<!--<div class="page-title-content">-->
<!--	<h2>Recover password</h2>-->
<!--<ul>-->
<!--<li>-->
<!--<a href="index.html">-->
<!--Home-->
<!--</a>-->
<!--</li>-->
<!--<li class="active">Contact</li>-->
<!--</ul>-->
<!--</div>-->
<!--</div>-->
<!--</div>-->


		<!-- End Page Title Area -->

		<!-- Start Recover Password Area -->
		<section class="user-area-style recover-password-area ptb-100">
			<div class="container">
				<div class="contact-form-action recover">
					<div class="form-heading text-center">
						<h3 class="form-title">Reset Password!</h3>
						<p class="reset-desc">Enter the email of your account to reset the password. Then you will receive a link to email to reset the password. If you have any issue about reset password <a href="contact.php">contact us.</a></p>
					</div>

					<form method="post">
						<div class="row">
							<div class="col-12">
								<div class="form-group">
									<input class="form-control" type="email" name="email" placeholder="Enter Email Address">
								</div>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-6">
								<a class="now-log-in font-q" href="login.php">Log In in your account</a>
							</div>
							
							<div class="col-lg-6 col-md-6 col-sm-6">
								<p class="now-register">
									Not a member?
									<a class="font-q" href="login.php">Registration</a>
								</p>
							</div>

							<div class="col-12">
								<button class="default-btn btn-two" type="submit">
									Reset Password
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</section>



<?php
include('include/footer.php');
include('include/footer-script.php');
?>
</html>
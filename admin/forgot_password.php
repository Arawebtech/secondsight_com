<?php
session_start();
include('include/cart_logic.php'); 
include('include/db_config.php');
$user_id = $_SESSION['user_id'];

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<?php 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $email = mysqli_real_escape_string($conn, $email);

    $query = "SELECT password FROM admin WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $password = $row['password'];
        
        // smtp email
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gurujimanishsharma@gmail.com'; // Securely fetch from environment variables
        $mail->Password = 'dbkxuvdvjtorjguj'; // Securely fetch from environment variables
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('gurujimanishsharma@gmail.com', 'Secondsightfoundation.com');
        $mail->addAddress('gurujimanishsharma@gmail.com');
         $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        
        $mail->Subject = 'Admin Password Recovery';
        $mail->Body = "Your password is: " . $password;
        

        if ($mail->send()) {
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
include('../include/head.php');

?>
<body>

<?php
include('../include/header.php');

?>

<style>
    @media screen and (max-width:700px)
    {
        .default-btn{
            padding:10px 16px;
           margin-left:0;
        }
    }
     body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .user-area-style {
            padding: 100px 0;
            /*background-color: #ffffff;*/
            max-width:500px;
            margin:auto;
        }

        .contact-form-action {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .reset-desc {
            color: #666;
            font-size: 16px;
            margin-top: 10px;
        }

        .reset-desc a {
            color: #00aaff;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            color: #333;
            outline: none;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #00aaff;
            box-shadow: 0 0 5px rgba(0, 170, 255, 0.2);
        }

        .now-log-in {
            color: #666;
            font-size: 16px;
            text-decoration: none;
        }

        .now-register {
            text-align: center;
            font-size: 16px;
        }

        .now-register a {
            color: #00aaff;
            text-decoration: none;
        }

        .default-btn {
            background: linear-gradient(to bottom, #ffb607 0%, #d76d0a 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        .default-btn:hover {
            background-color: #0088cc;
        }

        .btn-two {
            width: 100%;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .col-12, .col-lg-6, .col-md-6, .col-sm-6 {
            width: 100%;
            margin-bottom: 15px;
        }

        @media (min-width: 768px) {
            .col-lg-6 {
                width: 48%;
            }

            .col-md-6 {
                width: 48%;
            }

            .col-sm-6 {
                width: 48%;
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
						<p class="reset-desc">Enter the email of your account to reset the password. Then you will receive a link to email to reset the password. </p>
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
// include('../include/footer.php');
include('../include/footer-script.php');
?>
</html>
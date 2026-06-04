<?php
session_start();
include('include/cart_logic.php');
include('admin/include/db_config.php');

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
function cleanTextAndNumbers($input) {
    // Remove everything except letters and numbers
    $input = trim($input);
    $input = strip_tags($input); // remove HTML tags
    $input = htmlspecialchars($input); // convert special chars
    $input = preg_replace("/[^a-zA-Z0-9\s]/", "", $input); // allow only letters, numbers, spaces
    return $input;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include('db_connection.php'); // Make sure you have your DB connection here

    // Sanitize inputs
    $email = isset($_POST['email']) ? htmlspecialchars(strip_tags(trim($_POST['email']))) : '';
    $name = isset($_POST['name']) ? cleanTextAndNumbers($_POST['name']) : '';
    $user_subject = isset($_POST['phone']) ? cleanTextAndNumbers($_POST['phone']) : '';
    $message = isset($_POST['message']) ? cleanTextAndNumbers($_POST['message']) : '';

    // Basic validation
    if (empty($name) || empty($email)) {
        echo "<script>alert('Please fill in all fields.');</script>";
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
        exit;
    }


    // Email settings
    $to = "gurujimanishsharma@gmail.com, info@arawebtechnologies.com";
    $subject = "New Query Raised From Second Sight Foundation";
    $headers = "From: Secondsightfoundation.com <gurujimanishsharma@gmail.com>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Email Body
    $body = "
        <h2>New Customer Query</h2>
        <table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>
            <tr><td><strong>Name</strong></td><td>{$name}</td></tr>
            <tr><td><strong>Email</strong></td><td>{$email}</td></tr>
            <tr><td><strong>Phone</strong></td><td>{$user_subject}</td></tr>
            <tr><td><strong>Message</strong></td><td>{$message}</td></tr>
        </table>
    ";

    // Send the email
    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Thank you! We will get back to you soon.');</script>";
    } else {
        echo "<script>alert('Failed to send email. Please try again later.');</script>";
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
    include('include/header1.php');

    ?>
    <style>
        @media screen and (max-width:700px)
        {
            .contact-info-area .single-contact-info i {
                font-size: 23px;
            }
            .btn-contact{
                padding:10px;
                font-size:14px;
            }
        }
    </style>
    <div class="page-title-area bg-6">
        <div class="container">
            <div class="page-title-content">
            </div>
        </div>
    </div>


    <section class="contact-info-area pt-100 pb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-sm-6">
                    <div class="single-contact-info">
                        <i class="flaticon-call"></i>
                        <h3>Call us</h3>
                 
                            
                            <span>Contact Name: Jatin Sharma</span>
                        
                        <a href="tel:9716517463">Phone :+91-9716517463</a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6">
                    <div class="single-contact-info">
                        <i class="flaticon-pin"></i>
                        <h3>Our location</h3>
                        <a href="">Metro Station Tagore Garden, AE-10, Ground Floor, Tagore Garden, Near Tagore Garden
                            Metro Station Gate Number 1 Exit, New Delhi, Delhi 110027</a>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 offset-sm-3 offset-lg-0">
                    <div class="single-contact-info">
                        <i class="flaticon-email"></i>
                        <h3>Email</h3>
                        <a href="mailto:gurujimanishsharma@gmail.com">gurujimanishsharma@gmail.com</span></a>

                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="main-contact-area pb-100">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="contact-wrap contact-pages mb-0">
                        <div class="contact-form">
                            <div class="section-title">
                                <h2>Drop us a message for any query</h2>
                                <p>For more information about our courses, get in touch</p>
                            </div>
                            <form method="post">
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" id="name" class="form-control" required
                                                data-error="Please enter your name">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type="email" name="email" id="email" class="form-control" required
                                                data-error="Please enter your email">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Mobile Number</label>
                                            <input type="tel" name="phone" id="phone" class="form-control" 
                                                required data-error="Please mobile number" maxlength="10"  pattern="[0-9]{10}" title="Please enter 10 digits only">
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Message</label>
                                            <textarea name="message" class="form-control" id="message" cols="30"
                                                rows="10" required data-error="Write your message"></textarea>
                                            <div class="help-block with-errors"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-md-12">
                                        <button type="submit" name="submit" class="default-btn btn-two btn-contact">
                                            Send Message
                                        </button>
                                        <div id="msgSubmit" class="h3 text-center hidden"></div>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <div class="map-area">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7003.066092481351!2d77.10386827770996!3d28.643753999999984!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d0365942aa2dd%3A0x5b2db2d0eb309968!2sTagore%20Garden!5e0!3m2!1sen!2sin!4v1730700289637!5m2!1sen!2sin"
            width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>

    </div>
    <?php

    include('include/footer.php');
    include('include/footer-script.php');
    ?>
</body>

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


</html>
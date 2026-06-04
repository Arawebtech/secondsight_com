<!-- whatsapp and call icon btn -->
<!--<section class="contact-whatsapp fixed-bottom">-->
<!--    <div class="container row">-->
<!--        <div class="whatsapp-call">-->
<!--            <a href="https://wa.me/9716517463" target="_blank">-->
<!--                <i class="fa-brands fa-whatsapp" style="font-size: 36px; color: #25D366;"></i>-->

<!--            </a>-->
<!--        </div>-->
<!--        <div class="phone-call" style="align-self:center;">-->
<!--            <a href="tel:9716517463" class="btn btn-primary">-->
<!--                <i class="fa-solid fa-phone"></i>-->
<!--            </a>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->
<!-- End icon btn code -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('admin/include/db_config.php');


$show_cookie_notice = true;
if (isset($_COOKIE['privacy_policy_accepted']) && $_COOKIE['privacy_policy_accepted'] == '1') {
    $show_cookie_notice = false;
}

// Safely handle user IP tracking - wrap in try-catch to prevent errors
try {
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // Check if user_ips table exists before querying
    $table_check = "SHOW TABLES LIKE 'user_ips'";
    $table_result = $conn->query($table_check);
    
    if ($table_result && $table_result->num_rows > 0) {
        $sql_check = "SELECT id FROM user_ips WHERE ip_address = '$user_ip' LIMIT 1";
        $result = $conn->query($sql_check);
        
        if ($result && $result->num_rows == 0) {
            $sql_insert = "INSERT INTO user_ips (ip_address, access_time) VALUES ('$user_ip', NOW())";
            $conn->query($sql_insert);
        }
    }
} catch (Exception $e) {
    // Silently ignore errors - this is not critical functionality
    // error_log("User IP tracking error: " . $e->getMessage());
}

$conn->close();
?>

<div id="privacy-footer" class="privacy-footer">
    <div class="footer-content">
        <p>We use cookies on our website to give you the most relevant experience by remembering your preferences and
            repeat visits. By clicking "Accept", you consent to the use of ALL the cookies.</p>
        <div class="footer-buttons">
            <button id="accept-btn">Accept</button>
            <button id="decline-btn">Decline</button>
        </div>
    </div>
</div>

<script>
    window.onload = function () {
        var showFooter = <?php echo $show_cookie_notice ? 'true' : 'false'; ?>;
        if (showFooter) {
            var footer = document.getElementById("privacy-footer");
            if (footer) {
                footer.style.display = "flex";
            } else {
                console.error("privacy-footer element not found");
            }
        }
    };


    document.getElementById("accept-btn").onclick = function () {
        setCookie("privacy_policy_accepted", "1", 30);
        var footer = document.getElementById("privacy-footer");
        if (footer) {
            footer.style.display = "none";
        }
    };


    document.getElementById("decline-btn").onclick = function () {
        var footer = document.getElementById("privacy-footer");
        if (footer) {
            footer.style.display = "none";
        }

    };


    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }
</script>

<style>
    /* Style for the sticky footer */
    .privacy-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        /* Ensure the footer takes full width */
        background-color: #1a1a1a;
        /* Dark background color */
        color: white;
        padding: 8px 20px;
        /* Padding around the content */
        display: flex;
        justify-content: space-between;
        /* Space out text and buttons */
        align-items: center;
        /* Vertically center the content */
        z-index: 9999;
        display: none;
        /* Hidden by default, shown when the user has not accepted the policy */
    }

    .footer-content {
        display: flex;
        /* Use flex to align the content horizontally */
        justify-content: space-between;
        /* Space out text and buttons */
        width: 100%;
        /* Full width of the footer */
        max-width: 1200px;
        /* Maximum width of the content */
        padding: 0 10px;
        /* Padding to ensure content is not flush with edges */
    }

    .footer-content p {
        flex: 1;
        /* Allow text to take up remaining space */
        margin: 0;
        text-align: left;
        /* Align text to the left */
        font-size: small;
    }

    .footer-buttons {
        display: flex;
        /* Keep buttons aligned in a row */
        margin-left: 20px;
        /* Space between the text and buttons */
    }

    .footer-buttons button {
        margin: 8px 5px;
        /* Margin between the buttons */
        padding: 0px 11px;
        /* Adjusted padding to make buttons thinner */
        background-color: #4CAF50;
        /* Green background color */
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 14px;
        /* Adjusted font size */
        transition: background-color 0.3s;
    }

    .footer-buttons button:hover {
        background-color: #45a049;
        /* Darker green shade on hover */
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            /* Stack text and buttons vertically */
            align-items: center;
            /* Center content */
            padding: 41px 0;
            /* Padding adjustments */
            margin-top: -35px;
        }

        .footer-content p {
            text-align: center;
            /* Center-align text on smaller screens */
            font-size: 14px;
            /* Adjust text size */
            margin-bottom: 10px;
            /* Add space below text */
        }

        .footer-buttons {
            justify-content: center;
            /* Center the buttons */
            margin-top: -10px;
            /* Space between text and buttons */
        }

        .footer-buttons button {
            margin: 5px;
            /* Reduce margin between buttons */
            font-size: 12px;
            /* Smaller font size for mobile */
            padding: 8px 15px;
            /* Adjust button padding */
        }
        .footer-logo-mob{
            max-width: 206%;
            height: 75px;
        }
    }

    @media (max-width: 480px) {
        .footer-content p {
            font-size: 8px;
            /* Make text smaller for very small screens */
            text-align: center;
            /* Center-align text */
        }

        .footer-buttons button {
            font-size: 9px;
            /* Even smaller font size for very small screens */
            padding: 4px 9px;
            /* Adjust button padding for small screens */
        }
        .footer-widget .address .location , .font-14-mobile{
            font-size:14px;
        }
        .footer-widget .address li a{
            font-size:14px;
        }
        .copyright-custom{
            text-align:unset;
        }
        p:last-child {
            margin-bottom: 0;
            text-align: center;
        }
    }
   #location-atag:hover{
    color: #fff;
   }

@media (max-width: 767px) {
    .privacy-footer {
        flex-direction: column !important;
        align-items: center !important;
        padding: 20px 5px !important;
    }
    .footer-content {
        flex-direction: column !important;
        align-items: center !important;
        padding: 10px 0 !important;
        margin-top: 0 !important;
    }
    .footer-content p {
        text-align: center !important;
        font-size: 13px !important;
        margin-bottom: 10px !important;
    }
    .footer-buttons {
        flex-direction: column !important;
        width: 100% !important;
        margin-left: 0 !important;
        gap: 8px;
    }
    .footer-buttons button {
        width: 100% !important;
        font-size: 15px !important;
        margin: 0 0 8px 0 !important;
    }
}
</style>



<footer class="footer-top-area pt-70 pb-30">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <!-- <h3>Find Us</h3> -->
                    <ul class="address p-0" style="margin-top:-26px;">
                        <li class="location p-3 mb-0">
                            <a href="index.php">
                                <img src="/assets/img/flogo.webp" style="margin-left: -30px;" class="main-logo footer-logo-mob" alt="Logo" ></a>
                        </li>
                        <li class="location p-0"> Second Sight Foundation bridges ancient wisdom with modern science,
                            exploring the mind and universe through a scientific lens.
                        </li>

                    </ul>
                    <br>


                    <ul class="social-links">
                        <li style="margin-left: 0;">
                            <a href="https://www.instagram.com/secondsightfoundationdelhi/
" target="_blank"><i class="bx bxl-instagram" style="color: white;  font-size: 20px; "></i></a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/healingsoul.in/" target="_blank"><i
                                    class="bx bxl-facebook-square" style="color: white;  font-size: 20px; "></i></a>
                        </li>
                        <li>
                            <a href="https://www.youtube.com/@secondsightfoundation" target="_blank"><i
                                    class="fab fa-youtube" style="color: white;  font-size: 20px; "></i></a>
                        </li>
                        <li>
                            <a href="https://wa.me/9716517463" target="_blank"><i class="fab fa-whatsapp"
                                    style="color: white;  font-size: 20px; "></i></a>
                        </li>

                    </ul>
                    <!-- Font Awesome CSS -->
                    <link rel="stylesheet"
                        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

                    <style>
                        /* Ensure Boxicons or Font Awesome are loaded properly */
                        .social-links {
                            display: flex;
                            /* Display icons in a row */
                            justify-content: flex-start;
                            /* Align icons to the left */
                            gap: 15px;
                            /* Space between the icons */
                            list-style-type: none;
                            /* Remove list styling */
                            padding: 0;
                            /* Remove padding */
                            margin: 0;
                            /* Remove margin */
                        }

                        .social-links li {
                            display: inline-block;
                            /* Ensure each icon is treated as a block */
                        }

                        .social-links a {
                            font-size: 30px;
                            /* Set icon size */
                            color: #212121;
                            /* Set default icon color */
                            text-decoration: none;
                            /* Remove underline from links */
                        }

                        .social-links a:hover {
                            color: #ff7043;
                            /* Change icon color on hover */
                        }
                        /*@media (max-width:600px){*/
                        /*    .locat-padding{*/
                        /*        padding-left:0;*/
                        /*    }*/
                        /*}*/
                    
                    </style>
                    <link rel="stylesheet"
                        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Quick Links</h3>
                    <ul class="link">
                        <li>
                            <a href="index.php">Home</a>
                        </li>
                        <li>
                            <a href="about.php">About us
                            </a>
                        </li>
                        <li>
                            <a href="testimonial.php">Testimonial
                            </a>
                        </li>
                        <li>
                            <a href="contact.php">Contact us</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Useful links</h3>
                    <ul class="link">
                        <li>
                            <a href="courses.php">Courses</a>
                        </li>
                        <li>
                            <a href="teamlist.php">Our Team</a>
                        </li>
                        <li>
                            <a href="view_gallery.php">View Gallery</a>
                        </li>
                        <li>
                            <a href="blog.php">Blog</a>
                        </li>
<li>
 <a href="terms-and-conditons.php" target="_blank">Terms & Conditions</a></li>
              <li>  <a href="privacy-policy.php" target="_blank">Privacy Policy</a></li>
              <li>  <a href="refund-and-returns-policy.php" target="_blank">Refund and Returns Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="footer-widget">
                    <h3>Find Us</h3>
                    <ul class="address">
                       <li>
                            <i class="fa-solid fa-user fa-sm" style="top:9px;"></i>
                            <span class="text-white">Contact Name: Jatin Sharma</span>
                        </li>
                        <li class="location locat-padding" style="padding-left:2rem;">
                            <i class="bx bxs-location-plus"></i>
                          <a href="" id="location-atag">  Metro Station Tagore Garden, AE-10, Ground Floor, Tagore Garden, Near Tagore Garden Metro
                            Station Gate Number 1 Exit, New Delhi, Delhi 110027 </a>
                        </li>
                        <li>
                            <i class="bx bxs-envelope"></i>
                            <a href="mailto:gurujimanishsharma@gmail.com">gurujimanishsharma@gmail.com</span></a>
                        </li>
                        <li>
                            <i class="bx bxs-phone-call"></i>
                            <a href="tel:+91-9716517463"> +91 9716517463</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>



<footer class="footer-bottom-area">
    <div class="container">
        <div class="copyright-wrap">
            <p class="font-14-mobile copyright-custom p-2">Copyright @ Second Sight Foundation. All Right Reserved. Design & Developed By <a
                    href="https://arawebtechnologies.com/" target="_blank">AraWebTechnologies. </a></p>
              
        </div>
        
    </div>
</footer>


<div class="go-top">
    <i class="bx bx-chevrons-up"></i>
    <i class="bx bx-chevrons-up"></i>
</div>

<!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '4611124362447778');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=4611124362447778&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
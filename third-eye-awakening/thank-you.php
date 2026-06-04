<?php
session_start();

// Redirect to the home page if the payment session is not set
// This prevents direct access to the thank you page
// if (!isset($_SESSION['payment_success']) || $_SESSION['payment_success'] !== true) {
//     header('Location: index.php');
//     exit;
// }

// Unset the session variable to prevent the user from refreshing and seeing the message again
unset($_SESSION['payment_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Thank You! - Second Sight Foundation</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="assets/css/bootstrap.min.css" as="style" onload="this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="assets/css/bootstrap.min.css"></noscript>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <link rel="stylesheet" href="assets/css/style.css" />
    
    <style>
        body {
            font-family: "Montserrat", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            overflow-x: hidden;
            color: #333;
        }
        .thank-you-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="thank-you-container">
    <div class="container p-4">
        <div class="card shadow-lg rounded-4 border-0 p-5 bg-white">
            <h1 class="display-4 fw-bold text-success mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </h1>
            <h2 class="fw-bold mb-3">Thank You for Your Registration!</h2>
            <p class="lead mb-4">
                Your payment was successful and your registration for the
                Third Eye Awakening Webinar is now confirmed.
            </p>
            <p class="fs-5 text-muted mb-4">
                Please check your email and WhatsApp for the session details.
            </p>
                <a href="https://chat.whatsapp.com/KbpascP3Wf7B79KbE9t7LM?mode=ems_copy_t" class="btn btn-success btn-lg mt-2 fw-bold" target="_blank">
                    Join WhatsApp Community
                </a>
            <!-- New Webinar Info Section -->
            <hr class="my-4">
            <div class="webinar-info">
                <h3 class="fw-bold text-primary mb-3">Here's Your Community Link</h3>
                <p class="fs-5">Namaste 🙏✨</p>
                <p class="fs-5">Thank you for the payment. We have Received your payment.</p>
                <p class="fs-5 mb-3">✅ Your seat for the Third Eye Awakening Webinar with Guruji Manish Sharma Ji is confirmed! 🎉</p>
                <p class="fs-5 mb-3">🌟 This is where you will learn the ancient process to clear blockages and awaken your inner vision. 👁️✨</p>
                <p class="fs-5 mb-3">📲 Join our private WhatsApp community now: 👇🏻👇🏻👇🏻</p>

                <p class="fs-6 mt-3 mb-3">Inside, you’ll get 📢 early access to updates, 📚 webinar resources, and 🪷 special guidance from Guruji.</p>
                <p class="fs-5 fw-bold text-danger">⚡ Don’t miss the start of your transformation — join now! 🚀</p>
                <p class="fs-6 text-muted mt-4">Team Second Sight Foundation.</p>
            </div>
            <!-- End of New Webinar Info Section -->

            <!--<a href="index.php" class="btn btn-primary btn-lg mt-3 fw-bold">-->
            <!--    Return to Homepage-->
            <!--</a>-->
        </div>
    </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js" defer></script>
<script src="assets/js/main.js"></script>

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
fbq('init', '2167488523749549');
fbq('track', 'PageView');
fbq('track', 'Purchase', {
        value: '299',
        currency: 'INR',
        content_ids: ['webinar_123'],
        content_type: 'product'
    });
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=2167488523749549&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

</body>
</html>

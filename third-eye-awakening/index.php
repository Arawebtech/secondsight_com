<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Razorpay Configuration
$razorpay_key_id = 'rzp_live_RFX3z8BI0cj1Jz';
$razorpay_key_secret = 'rdRyBr5Uckhtp88MlRbop6Xl';


// Test Keys 
// $razorpay_key_id = 'rzp_test_e60seRffkGZ7L7';
// $razorpay_key_secret = 'wpXPnc2qV3LSZXug9YTu0V3Y';
function createRazorpayOrder($amount, $key_id, $key_secret) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount' => $amount * 100,
        'currency' => 'INR',
        'receipt' => 'rcpt_' . time()
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($key_id . ':' . $key_secret)
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function sendEmailConfirmation($to, $name, $amount, $orderId) {
    $subject = "Your Registration for Third Eye Webinar is Confirmed!";
    $whatsappLink = "https://chat.whatsapp.com/KbpascP3Wf7B79KbE9t7LM?mode=ems_copy_t"; // <-
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin:0; padding:0; background:#f5f5f5; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; text-align: center; }
            .header h1 { margin: 0; font-size: 22px; }
            .content { padding: 25px; }
            .highlight { background: #e8f5e8; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 6px; }
            .btn { display: inline-block; background: #25D366; color: white !important; padding: 12px 20px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 15px; }
            .footer { text-align: center; padding: 15px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🧿 Third Eye Awakening Webinar</h1>
                <p>Registration Confirmed</p>
            </div>
            <div class='content'>
                <h2>Welcome, $name! 🎉</h2>
                <p>Thank you for registering for the <strong>Third Eye Awakening Webinar</strong> with Guruji Manish Sharma Ji. Your payment has been received successfully.</p>

                <div class='highlight'>
                    <p><strong>Amount Paid:</strong> ₹" . ($amount/100) . "</p>
                    <p><strong>Order ID:</strong> $orderId</p>
                    <p><strong>Date:</strong> " . date('d M Y, h:i A') . "</p>
                </div>

                <p>👉 To get important updates and the joining link, please join our official WhatsApp community:</p>
                <p style='text-align:center;'>
                    <a href='$whatsappLink' class='btn'>Join WhatsApp Group</a>
                </p>

                <p><strong>Note:</strong> This is a LIVE webinar. No recordings will be provided. Make sure to attend on time for the spiritual transmission.</p>
            </div>
            <div class='footer'>
                <p>🕉️ With gratitude,<br><strong>Second Sight Foundation Team</strong></p>
                <p><em>Decode Hidden Energies, Heal Karmas & Awaken True Intuition Through Your Third Eye.</em></p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Set content-type header for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Third Eye Webinar <noreply@secondsightfoundation.com>" . "\r\n";
    $headers .= "Reply-To: support@secondsightfoundation.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    // Log email attempt
    $logFile = __DIR__ . "/email_logs.txt";
    $logData = "=============================\n";
    $logData .= "Time: " . date("Y-m-d H:i:s") . "\n";
    $logData .= "To: " . $to . "\n";
    $logData .= "Name: " . $name . "\n";
    $logData .= "Amount: ₹" . ($amount/100) . "\n";
    $logData .= "Order ID: " . $orderId . "\n";

    // Send the email
    $emailSent = mail($to, $subject, $message, $headers);
    
    if ($emailSent) {
        $logData .= "Status: Email sent successfully\n\n";
    } else {
        $logData .= "Status: Failed to send email\n";
        $logData .= "Error: " . error_get_last()['message'] . "\n\n";
    }
    
    file_put_contents($logFile, $logData, FILE_APPEND);
    
    return $emailSent;
}

function sendWhatsAppMessage($phone, $name) {
    $logFile = __DIR__ . "/whatsapp_logs.txt"; // log file in same folder

    $payload = [
        'countryCode' => '+91',
        'phoneNumber' => $phone,
        'type' => 'Template',
        'template' => [
            'name' => 'third_eye_payment_confirm_v1',
            'languageCode' => 'en',
            'headerValues' => ['header_variable_value'],
            'bodyValues' => [$name]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.interakt.ai/v1/public/message/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic Vy1QUXMwb0NibkxfNEM3NUZrX1A2dVVydHFoY3VSck1KQ0JEQk9LY0ZVODo=',
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // Prepare log content
    $logData = "=============================\n";
    $logData .= "Time: " . date("Y-m-d H:i:s") . "\n";
    $logData .= "Phone: " . $phone . "\n";
    $logData .= "Name: " . $name . "\n";
    $logData .= "Payload: " . json_encode($payload) . "\n";
    if ($error) {
        $logData .= "cURL Error: " . $error . "\n";
    }
    $logData .= "Response: " . $response . "\n\n";

    // Write log into file
    file_put_contents($logFile, $logData, FILE_APPEND);

    return $response;
}

// Handle order creation via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    header('Content-Type: application/json');
    $amount = 299;
    $order = createRazorpayOrder($amount, $razorpay_key_id, $razorpay_key_secret);
    echo json_encode(['success' => true, 'order' => $order]);
    exit;
}

// Handle payment completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $payment_id = filter_input(INPUT_POST, 'razorpay_payment_id', FILTER_SANITIZE_STRING);
    $order_id = filter_input(INPUT_POST, 'razorpay_order_id', FILTER_SANITIZE_STRING);
    $signature = filter_input(INPUT_POST, 'razorpay_signature', FILTER_SANITIZE_STRING);
    $customer_name = filter_input(INPUT_POST, 'customer_name', FILTER_SANITIZE_STRING) ?? '';
    $customer_phone = filter_input(INPUT_POST, 'customer_phone', FILTER_SANITIZE_STRING) ?? '';
    $customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL) ?? '';
    
    // Verify signature using hash_equals for secure comparison
    $generated_signature = hash_hmac('sha256', $order_id . "|" . $payment_id, $razorpay_key_secret);
    
    if (hash_equals($generated_signature, $signature)) {
        // Payment successful - send both WhatsApp and Email notifications
        
        // Send WhatsApp message
        $whatsappResponse = sendWhatsAppMessage($customer_phone, $customer_name);
        
        // Send Email confirmation
        $emailResponse = sendEmailConfirmation($customer_email, $customer_name, 29900, $order_id); // 29900 = 299 * 100 (in paisa)
        
        // Store success information in session
        $_SESSION['payment_success'] = true;
        $_SESSION['payment_amount'] = 299;
        $_SESSION['customer_name'] = $customer_name;
        $_SESSION['customer_email'] = $customer_email;
        $_SESSION['customer_phone'] = $customer_phone;
        $_SESSION['order_id'] = $order_id;
        $_SESSION['whatsapp_sent'] = !empty($whatsappResponse);
        $_SESSION['email_sent'] = $emailResponse;
        
        // Redirect to thank you page
        header('Location: thank-you.php');
        exit;
    } else {
        // Handle failed signature verification
        // Log the failed attempt
        $errorLogFile = __DIR__ . "/payment_errors.txt";
        $errorData = "=============================\n";
        $errorData .= "Time: " . date("Y-m-d H:i:s") . "\n";
        $errorData .= "Error: Signature verification failed\n";
        $errorData .= "Order ID: " . $order_id . "\n";
        $errorData .= "Payment ID: " . $payment_id . "\n";
        $errorData .= "Expected Signature: " . $generated_signature . "\n";
        $errorData .= "Received Signature: " . $signature . "\n\n";
        file_put_contents($errorLogFile, $errorData, FILE_APPEND);
        
        header('Location: payment-failed.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
      <title>
      The Right Way to Open Your Third Eye &amp; Transform Your Life
    </title>
        <link rel="preload" href="assets/images/third-banner-mobile.webp" as="image" fetchpriority="high">
 <link
      href="assets/images/smartpages-secondsightfoundatio-3.webp"
      rel="icon"
      type="image/svg+xml"
    />
    
<link rel="preload" href="assets/css/bootstrap.min.css" as="style" onload="this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="assets/css/bootstrap.min.css"></noscript>

  <style>
    body {
      font-family: "Montserrat", sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      overflow-x: hidden;
      color: #333;
      font-display: swap;
    }
    
    /* Custom styles for the modal */
    .modal-content {
      border-radius: 1rem;
    }
    .modal-header {
      border-bottom: none;
    }
    .modal-footer {
      border-top: none;
    }
  </style>
  <link
    rel="preload"
    href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap"
    as="style"
    onload="this.onload=null;this.rel='stylesheet'"
  />
  <noscript>
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap"
    />
  </noscript>

<link rel="preload" href="assets/images/third-banner.webp" as="image" type="image/webp" />
<link rel="preload" href="assets/images/third-banner-mobile.webp" as="image" type="image/webp" media="(max-width: 768px)" />
<link rel="stylesheet" href="assets/css/style.css" />
    
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
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=2167488523749549&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->

</head>
  <body>
    <div class="top-bar bg-warning text-dark py-2">
      <div class="container">
        <div class="row align-items-center text-center text-md-start">
          <div class="col-md-8 fw-semibold mb-2 mb-md-0">
            🌟 HURRY : Limited-Time Offer Discounted Price Expires in!
          </div>
          <div class="col-md-4 fw-bold">
            <div class="timer-wrapper d-flex justify-content-center gap-2">
              <div class="text-center">
                <div class="time-box days">00</div>
                <div class="small">Days</div>
              </div>
              <div class="text-center">
                <div class="time-box hours">00</div>
                <div class="small">Hours</div>
              </div>
              <div class="text-center">
                <div class="time-box minutes">00</div>
                <div class="small">Minutes</div>
              </div>
              <div class="text-center">
                <div class="time-box seconds">00</div>
                <div class="small">Seconds</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <header class="hero">
      <div class="container">
        <h1 class="display-4 fw-bold">
          The Right Way to Open Your Third Eye &amp; Transform Your Life
        </h1>
        <p class="lead">🌌 A Movement for Those Ready to Truly Awaken...</p>
        <div class="text-center mt-5">
          <button
            type="button"
            class="btn btn-danger btn-lg px-5 py-3 fs-4 fw-bold rzp-button"
            onclick="showPaymentModal()"
            >Register Me Now @ Just ₹299</button>
          <div class="text-warning mt-2" style="font-size: 1.25rem">
            Only 09 Spots Left
          </div>
        </div>
      </div>
    </header>
    
    <section class="py-5 bg-light" id="why-guidance">
      <div class="container">
        <div class="row justify-content-center text-center mb-4">
          <div class="col-lg-10">
            <h2 class="fw-bold display-5">
              🧿 <span class="text-gradient">Why Guidance Matters</span>
            </h2>
            <p class="lead text-dark-emphasis fs-5">
              <strong>Third Eye Awakening</strong> is not a new-age fad — it's a
              <span class="text-success fw-semibold"
                >sacred spiritual technology</span
              >.
            </p>
          </div>
        </div>
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div
              class="p-4 bg-warning bg-opacity-25 rounded-4 shadow-lg border-start border-5 border-warning-subtle"
            >
              <p class="fs-5 fw-semibold mb-4 text-danger">
                But in today's world, many are unknowingly activating their
                third eye without proper guidance, leading to:
              </p>
              <ul class="list-unstyled fs-5 text-dark-emphasis">
                <li class="d-flex align-items-start mb-3">
                  ⚠️
                  <span
                    ><strong>Psychic fear</strong>, over-sensitivity, emotional
                    instability</span
                  >
                </li>
                <li class="d-flex align-items-start mb-3">
                  ⚠️
                  <span
                    ><strong>Unclear dreams</strong>, spiritual confusion,
                    ungrounded experiences</span
                  >
                </li>
                <li class="d-flex align-items-start">
                  ⚠️
                  <span
                    ><strong>Disconnection</strong> from real purpose, energy
                    fatigue, and isolation</span
                  >
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="section bg-light">
      <div class="container">
        <div class="row g-4 align-items-center">
          <div class="col-md-6">
            <div class="p-0">
              <h2 class="fs-2 text-md-start mb-3">
                ✨
                <span class="text-gradient">Third Eye Awakening Webinar</span>
              </h2>
              <p class="quote-highlight fst-italic fs-5 text-md-start mb-4">
                "Decode Hidden Energies, Heal Karmas &amp; Awaken True Intuition
                Through Your Third Eye."
              </p>
              <ul class="list-unstyled text-dark">
                <li class="d-flex align-items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success me-3 fs-5" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg><span
                    >Healing and correcting improper third eye activation</span
                  >
                </li>
                <li class="d-flex align-items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success me-3 fs-5" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg><span>Building safe, guided intuition and inner vision</span>
                </li>
                <li class="d-flex align-items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success me-3 fs-5" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg><span
                    >Empowering healers, seekers, and empaths to grow
                    spiritually with protection</span
                  >
                </li>
                <li class="d-flex align-items-start">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill text-success me-3 fs-5" viewBox="0 0 16 16">
  <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
</svg><span
                    >Creating a vibrant sangha of light spiritual growth through
                    community</span
                  >
                </li>
              </ul>
            </div>
          </div>
          <div class="col-md-6 two-col-img"></div>
        </div>
      </div>
      <div class="text-center mt-5">
        <h4 class="mb-2">🌟 Discounted Price Offer Expires in</h4>
        <div class="timer-wrapper d-flex justify-content-center gap-2 mb-3">
          <div class="text-center">
            <div class="time-box days">00</div>
            <div class="small">Days</div>
          </div>
          <div class="text-center">
            <div class="time-box hours">00</div>
            <div class="small">Hours</div>
          </div>
          <div class="text-center">
            <div class="time-box minutes">00</div>
            <div class="small">Minutes</div>
          </div>
          <div class="text-center">
            <div class="time-box seconds">00</div>
            <div class="small">Seconds</div>
          </div>
        </div>
        <button
          class="btn btn-danger btn-lg px-5 py-3 fs-4 fw-bold rzp-button"
          onclick="showPaymentModal()"
          >Register Me Now @ Just ₹299</button>
        <div class="text-danger mt-2" style="font-size: 1.25rem">
          Only 09 Spots Left
        </div>
      </div>
    </section>
    <section class="py-5 bg-light">
      <div class="container">
        <h2 class="fw-bold text-center mb-4 display-6">
          🌟
          <span class="text-gradient"
            >In this powerful, live online webinar, Guruji Manish Sharma Ji will
            reveal:</span
          >
        </h2>
        <ul class="list-group p-2">
          <li class="mb-2">
            <span
              >🔮 What is the Third Eye from spiritual and energetic
              perspectives</span
            >
          </li>
          <li class="mb-2">
            <span
              >🧘‍♂️ The correct process of Third Eye Awakening – without harm or
              imbalance</span
            >
          </li>
          <li class="mb-2">
            <span
              >⚠️ What happens when the Third Eye is forcefully or wrongly
              activated</span
            >
          </li>
          <li class="mb-2">
            <span
              >🧠 What is Unconscious Memory – and how it controls 90% of your
              life</span
            >
          </li>
          <li class="mb-2">
            <span
              >🕉️ The link between Unconscious Memory and Past Life Karmas</span
            >
          </li>
          <li class="mb-2">
            <span
              >❤️‍🩹 How Third Eye can help you heal relationships, emotions,
              karmas, and even health</span
            >
          </li>
          <li class="mb-2">
            <span
              >👁️‍🗨️ How to activate your inner vision and decode hidden
              energies</span
            >
          </li>
          <li class="mb-2">
            <span
              >🔐 Powerful tools, practices &amp; secrets usually taught in
              high-level spiritual initiations</span
            >
          </li>
        </ul>
      </div>
      <div class="text-center">
        <strong>YOUR PRICE TODAY</strong><br />
        <div class="offer-tag d-inline-block">
          <del class="text-danger">₹1499</del
          ><span class="text-dark fw-bold">₹299</span>
        </div>
      </div>
    </section>
    <section class="section bg-warning bg-opacity-25 position-relative">
      <div class="container">
        <h2 class="fw-bold mb-4 text-gradient">This Is For You If...</h2>
        <ul class="list-group mb-5">
          <li class="mb-2">
            ⚠️ You're experiencing fear, confusion or imbalance after spiritual awakening
          </li>
          <li class="mb-2">
            ⚠️ You want to grow your intuition, inner clarity, and energetic mastery
          </li>
          <li class="mb-2">
            ⚠️ You're a healer, seeker, or spiritual practitioner wanting real transformation
          </li>
          <li class="mb-2">
            ⚠️ You crave a grounded, authentic path of spiritual growth with guidance
          </li>
        </ul>
      </div>
    </section>
    <section class="section container text-center" id="register">
      <h2 class="fw-bold">🕉️ Join the Third Eye Awakening Webinar</h2>
      <button
        type="button"
        class="btn btn-danger btn-lg px-5 py-3 fs-4 fw-bold rzp-button"
        onclick="showPaymentModal()"
        >Register Me Now @ Just ₹299</button>
      <div class="text-danger mt-2" style="font-size: 1.25rem">
        Only 09 Spots Left
      </div>
    </section>
    <section class="section faq-section">
      <div class="container">
        <h2 class="fw-bold text-center mb-4">🙋 Frequently Asked Questions</h2>
        <div class="accordion" id="faqAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq1">
              <button aria-controls="collapse1" aria-expanded="true" class="accordion-button" data-bs-target="#collapse1" data-bs-toggle="collapse" type="button" > Will this webinar help me open my Third Eye? </button>
            </h2>
            <div aria-labelledby="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion" id="collapse1" >
              <div class="accordion-body"> Yes. You will learn the correct, safe and authentic method of Third Eye Awakening. Guruji will guide you through powerful knowledge and energy-based insights that will prepare your mind and energy body for awakening. </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq2">
              <button aria-controls="collapse2" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#collapse2" data-bs-toggle="collapse" type="button" > What if I already meditate or do healing work? </button>
            </h2>
            <div aria-labelledby="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion" id="collapse2" >
              <div class="accordion-body"> Perfect! This webinar will amplify your spiritual abilities, bring clarity to your practices, and may open new dimensions of healing, intuition, and past-life awareness. </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq3">
              <button aria-controls="collapse3" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#collapse3" data-bs-toggle="collapse" type="button" > What is the language of the webinar? </button>
            </h2>
            <div aria-labelledby="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion" id="collapse3" >
              <div class="accordion-body"> The webinar will be in simple Hindi with occasional English, so it is easy to understand for everyone across India. </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq4">
              <button aria-controls="collapse4" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#collapse4" data-bs-toggle="collapse" type="button" > Will I get a recording? </button>
            </h2>
            <div aria-labelledby="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion" id="collapse4" >
              <div class="accordion-body"> No recording will be provided. This is a LIVE &amp; once-in-a-lifetime spiritual transmission by Guruji Manish Sharma Ji. Only those who attend live will receive this powerful knowledge. After the session, the door closes. Your live presence is essential. </div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="faq5">
              <button aria-controls="collapse5" aria-expanded="false" class="accordion-button collapsed" data-bs-target="#collapse5" data-bs-toggle="collapse" type="button" > Will I get course details immediately after payment? </button>
            </h2>
            <div aria-labelledby="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion" id="collapse5" >
              <div class="accordion-body"> Yes, you will receive all the session details in WhatsApp community group right after successful payment and if you miss to join WhatsApp community, you will receive the link on your email. </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="section py-5 testimonial">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="fw-bold display-6">
            ✨
            <span class="text-gradient">What Our Community Says</span>
          </h2>
          <p class="lead text-dark-emphasis">
            From Skeptic to Seeker: Hear from those who have transformed
          </p>
        </div>
        <div class="row row-cols-1 row-cols-md-3 g-4">
          <div class="col">
            <div class="card h-100 shadow-sm rounded-4">
              <div class="card-body">
                <blockquote class="blockquote mb-0">
                  <p class="fst-italic">
                    "I was lost in spiritual noise. This webinar gave me a clear
                    path, helping me ground my energies and truly connect with
                    my inner self. Grateful for the real, tangible guidance."
                  </p>
                  <footer class="blockquote-footer mt-3">
                    Aman Sharma <br /><cite title="Source Title">New Delhi</cite>
                  </footer>
                </blockquote>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 shadow-sm rounded-4">
              <div class="card-body">
                <blockquote class="blockquote mb-0">
                  <p class="fst-italic">
                    "I've felt an energetic shift I can't explain. The fear I
                    had around my spiritual gifts has vanished, replaced by a
                    deep sense of peace. Thank you, Guruji!"
                  </p>
                  <footer class="blockquote-footer mt-3">
                    Priya Singh <br /><cite title="Source Title">Mumbai</cite>
                  </footer>
                </blockquote>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card h-100 shadow-sm rounded-4">
              <div class="card-body">
                <blockquote class="blockquote mb-0">
                  <p class="fst-italic">
                    "This isn't just about the Third Eye. It's about healing
                    deep karmic wounds and living with intention. I finally feel
                    in control of my life."
                  </p>
                  <footer class="blockquote-footer mt-3">
                    Ravi Kumar <br /><cite title="Source Title">Bangalore</cite>
                  </footer>
                </blockquote>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <footer class="footer bg-dark text-white py-4">
      <div class="container text-center">
        <p class="mb-0">
          © 2024 Second Sight Foundation. All Rights Reserved.
        </p>
        <p class="mb-0">
          <a href="#" class="text-white-50 text-decoration-none">Privacy Policy</a>
          |
          <a href="#" class="text-white-50 text-decoration-none">Terms of Service</a>
        </p>
      </div>
    </footer>
    
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg border-0">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title fw-bold" id="paymentModalLabel">Enter Your Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-center text-muted">Please provide your details to proceed with the payment.</p>
            <form id="payment-form">
              <div class="mb-3">
                <label for="customerName" class="form-label">Full Name</label>
                <input type="text" class="form-control rounded-pill" id="customerName" required>
              </div>
              <div class="mb-3">
                <label for="customerEmail" class="form-label">Email Address</label>
                <input type="email" class="form-control rounded-pill" id="customerEmail" required>
              </div>
              <div class="mb-3">
                <label for="customerPhone" class="form-label">Mobile Number</label>
                <input type="tel" class="form-control rounded-pill" id="customerPhone" required>
              </div>
              <div id="error-message" class="alert alert-danger d-none mt-3" role="alert"></div>
            </form>
          </div>
          <div class="modal-footer border-top-0 d-flex justify-content-center">
            <button type="button" class="btn btn-danger btn-lg px-5 py-2 fw-bold" onclick="startPayment()">
              Proceed to Payment
            </button>
          </div>
        </div>
      </div>
    </div>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
      // Countdown Timer
      function updateTimer() {
        const targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 1); // Set to next day
        const now = new Date();
        const difference = targetDate - now;

        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((difference % (1000 * 60)) / 1000);

        const formatTime = (time) => String(time).padStart(2, '0');

        document.querySelectorAll('.time-box.days').forEach(el => el.textContent = formatTime(days));
        document.querySelectorAll('.time-box.hours').forEach(el => el.textContent = formatTime(hours));
        document.querySelectorAll('.time-box.minutes').forEach(el => el.textContent = formatTime(minutes));
        document.querySelectorAll('.time-box.seconds').forEach(el => el.textContent = formatTime(seconds));
      }
      setInterval(updateTimer, 1000);
      updateTimer();

      // Show the modal
      function showPaymentModal() {
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
      }

      // Start the payment process
      function startPayment() {
        const customerName = document.getElementById('customerName').value.trim();
        const customerEmail = document.getElementById('customerEmail').value.trim();
        const customerPhone = document.getElementById('customerPhone').value.trim();
        const errorMessage = document.getElementById('error-message');
        
        // Basic validation
        if (!customerName || !customerEmail || !customerPhone) {
            errorMessage.textContent = "Please fill in all fields.";
            errorMessage.classList.remove('d-none');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(customerEmail)) {
            errorMessage.textContent = "Please enter a valid email address.";
            errorMessage.classList.remove('d-none');
            return;
        }

        // Phone validation (basic)
        const phoneRegex = /^[6-9]\d{9}$/;
        if (!phoneRegex.test(customerPhone)) {
            errorMessage.textContent = "Please enter a valid 10-digit mobile number.";
            errorMessage.classList.remove('d-none');
            return;
        }

        errorMessage.classList.add('d-none');

        const button = document.querySelector('#paymentModal .btn-danger');
        const originalButtonText = button.textContent;
        button.textContent = "Processing...";
        button.disabled = true;

        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'create_order=1'
        })
        .then(response => response.json())
        .then(data => {
            button.textContent = originalButtonText;
            button.disabled = false;
            
            if (data.success) {
                const order = data.order;
                const options = {
                    "key": "<?php echo $razorpay_key_id; ?>",
                    "amount": order.amount,
                    "currency": order.currency,
                    "name": "Second Sight Foundation",
                    "description": "Third Eye Awakening Webinar",
                    "order_id": order.id,
                    "handler": function (response) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'index.php';

                        const fields = {
                            'razorpay_payment_id': response.razorpay_payment_id,
                            'razorpay_order_id': response.razorpay_order_id,
                            'razorpay_signature': response.razorpay_signature,
                            'customer_name': customerName,
                            'customer_phone': customerPhone,
                            'customer_email': customerEmail
                        };

                        Object.keys(fields).forEach(key => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = key;
                            input.value = fields[key];
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    },
                    "prefill": {
                        "name": customerName,
                        "email": customerEmail,
                        "contact": customerPhone
                    },
                    "theme": {
                        "color": "#dc3545"
                    }
                };

                const rzp = new Razorpay(options);
                rzp.on('payment.failed', function (response) {
                    errorMessage.textContent = 'Payment failed. Please try again.';
                    errorMessage.classList.remove('d-none');
                    button.textContent = originalButtonText;
                    button.disabled = false;
                });
                rzp.open();
            } else {
                errorMessage.textContent = 'Error: Could not create order.';
                errorMessage.classList.remove('d-none');
                button.textContent = originalButtonText;
                button.disabled = false;
            }
        })
        .catch(error => {
            button.textContent = originalButtonText;
            button.disabled = false;
            errorMessage.textContent = 'Error: ' + error.message;
            errorMessage.classList.remove('d-none');
        });
      }
    </script>
  </body>
</html>
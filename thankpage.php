<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You | Second Sight</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
          <link rel="icon" href="https://www.secondsightfoundation.com/assets/img/logo-fav.png" type="image/png">

    <style>
        body {
            min-height: 100vh;
          
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', Arial, sans-serif;
            overflow-x: hidden;
        }
        .glass-box {
            background: rgba(255,255,255,0.25);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.18);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 24px;
            border: 1.5px solid rgba(255,255,255,0.25);
            padding: 56px 36px 40px 36px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            position: relative;
        }
        .thankyou-icon {
            font-size: 4.5rem;
            color: #fdc134; /* Website theme color */
            margin-bottom: 18px;
            animation: pop 0.7s cubic-bezier(.68,-0.55,.27,1.55);
        }
        @keyframes pop {
            0% { transform: scale(0.5); opacity: 0; }
            80% { transform: scale(1.15); opacity: 1; }
            100% { transform: scale(1); }
        }
        .thankyou-title {
            font-size: 2.3rem;
            font-weight: 700;
            color: #22223b;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .thankyou-message {
            color: #4a4e69;
            font-size: 1.13rem;
            margin-bottom: 30px;
        }
        .thankyou-btn {
            background: linear-gradient(135deg, #fdc134, #fcb813);
            color: #000 !important;
            border: none;
            padding: 13px 36px;
            font-size: 1.08rem;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.2s;
            text-decoration: none;
            margin-bottom: 0;
            box-shadow: 0 2px 12px rgba(253,193,52,0.13);
            display: inline-block;
        }
        .thankyou-btn:hover {
            opacity: 0.93;
            box-shadow: 0 4px 18px rgba(253,193,52,0.18);
        }
        .thankyou-btn.secondary {
            background: #fff;
            color: #fdc134 !important;
            border: 2px solid #fdc134;
            margin-top: 16px;
        }
        .order-id {
            font-size: 1.02rem;
            color: #fdc134;
            background: rgba(253,193,52,0.08);
            border-radius: 6px;
            padding: 6px 0 4px 0;
            margin-bottom: 18px;
            display: inline-block;
            letter-spacing: 0.5px;
        }
        /* Confetti */
        .confetti {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            pointer-events: none;
            z-index: 9999;
        }
    </style>
</head>
<body>
    <canvas class="confetti"></canvas>
    <div class="glass-box">
        <div class="thankyou-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="thankyou-title">Thank You!</div>
        <div class="order-id">Order ID: <b>#123456</b></div>
        <div class="thankyou-message">
            Your order has been placed <span style="color:green">successfully.</span><br>
            We appreciate your trust in us.<br>
            A confirmation email has been sent to your registered email address.
        </div>
        <a href="index.php" class="thankyou-btn"><i class="fas fa-home me-2"></i>Back to Home</a>
        <br>
        <a href="product-circle.php" class="thankyou-btn secondary"><i class="fas fa-shopping-bag me-2"></i>Continue Shopping</a>
    </div>
 
</body>
</html>



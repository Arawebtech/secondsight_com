<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limited Time Offer</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Font Awesome CDN (Include this once in your <head>) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />


   <style>
  
        .header-container {
            width: 100%;
            margin-top:-5% !important;
        }
        @media (max-width:768px) {
           .header-container {
          
            margin-top:-16% !important;
        }   
        }

        .offer-section {
            background-color: #6A0DAD;
            padding: 13px 0;
        }

        .offer-text {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
            font-weight: bold;
            font-size: 16px;
            font-family: Arial, sans-serif;
        }

        .star-icon {
            width: 20px;
            height: 20px;
            background-color: #D4A017;
            border: 2px solid #333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .countdown-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .countdown-box {
            background-color:#FFCC22;
            color: #fff;
            padding: 8px 15px;
            border-radius: 5px;
            text-align: center;
            min-width: 60px;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        }

        .countdown-number {
            font-size: 30px;
            font-weight: bold;
            display: block;
            font-family: Arial, sans-serif;
        }

        .countdown-label {
            font-size: 12px;
            color: #fff;
            margin-top: 2px;
            font-family: Arial, sans-serif;
        }

        
        @media (max-width: 768px) {
            .offer-text {
                font-size: 18px;
                text-align: center;
                justify-content: center;
            }
            
            .countdown-container {
                gap: 10px;
                justify-content: center;
            }
            
            .countdown-box {
                min-width: 70px;
                padding: 12px 15px;
            }
            
            .countdown-number {
                font-size: 24px;
            }
            
            .countdown-label {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="offer-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8 col-md-6 col-12">
                        <div class="offer-text">
                            
                            <span style="color:#F5F5DC;font-size:18px; ">🌟 Heal Yourself & Others with Durga Reiki!</span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="countdown-container">
                            <div class="countdown-box">
                                <span class="countdown-number" id="days" >00</span>
                                <div class="countdown-label">Days</div>
                            </div>
                            <div class="countdown-box">
                                <span class="countdown-number"  id="hours">00</span>
                                <div class="countdown-label"  >Hours</div>
                            </div>
                            <div class="countdown-box">
                                <span class="countdown-number" id="minutes"  >00</span>
                                <div class="countdown-label"  >Minutes</div>
                            </div>
                            <div class="countdown-box">
                                <span class="countdown-number" id="seconds">00</span>
                                <div class="countdown-label" >Seconds</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bottom-section"></div>
    </div>

    <script>
        // Set the countdown end date (you can modify this)
        const countdownDate = new Date();
        countdownDate.setDate(countdownDate.getDate() + 7); // 7 days from now

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = countdownDate.getTime() - now;

            if (distance < 0) {
                // Countdown has ended
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = days.toString().padStart(2, '0');
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        
        // Initial call
        updateCountdown();
    </script>
</body>
</html> 
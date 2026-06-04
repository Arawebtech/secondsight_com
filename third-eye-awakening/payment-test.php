<?php
session_start();

// Configuration - Replace with your actual Razorpay credentials
$razorpay_key_id = 'rzp_test_e60seRffkGZ7L7';
$razorpay_key_secret = 'wpXPnc2qV3LSZXug9YTu0V3Y';

// Function to create Razorpay order
function createRazorpayOrder($amount, $key_id, $key_secret) {
    $order_data = [
        'receipt' => 'rcpt_' . time(),
        'amount' => $amount * 100, // Amount in paise
        'currency' => 'INR'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($key_id . ':' . $key_secret)
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    return false;
}

// Function to send WhatsApp message via Interakt API
function sendWhatsAppMessage($phone, $name) {
    $interakt_data = [
        'countryCode' => '+91',
        'phoneNumber' => $phone,
        'type' => 'Template',
        'template' => [
            'name' => 'third_eye_payment_confirm_v1',
            'languageCode' => 'en',
            'headerValues' => [
                'header_variable_value'
            ],
            'bodyValues' => [
                $name
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.interakt.ai/v1/public/message/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($interakt_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic Vy1QUXMwb0NibkxfNEM3NUZrX1A2dVVydHFoY3VSck1KQ0JEQk9LY0ZVODo=',
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $http_code,
        'response' => $response,
        'error' => $curl_error,
        'success' => ($http_code >= 200 && $http_code < 300)
    ];
}
function getPaymentDetails($payment_id, $key_id, $key_secret) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/payments/' . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($key_id . ':' . $key_secret)
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code === 200) {
        return json_decode($response, true);
    }
    
    // Store error for debugging
    return ['error' => "HTTP Code: $http_code, cURL Error: $curl_error", 'response' => $response];
}

// Handle order creation via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    header('Content-Type: application/json');
    
    $amount = (int)$_POST['amount'];
    $order = createRazorpayOrder($amount, $razorpay_key_id, $razorpay_key_secret);
    
    if ($order) {
        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create order']);
    }
    exit;
}

// Handle webhook
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $payment_id = $_POST['razorpay_payment_id'];
    $order_id = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    
    // Get additional form data
    $customer_name = $_POST['customer_name'] ?? 'Not provided';
    $customer_email = $_POST['customer_email'] ?? 'Not provided';
    $customer_phone = $_POST['customer_phone'] ?? 'Not provided';
    
    // Verify signature
    $generated_signature = hash_hmac('sha256', $order_id . "|" . $payment_id, $razorpay_key_secret);
    
    if ($generated_signature === $signature) {
        // Initialize webhook data with basic info
        $webhook_data = [
            'payment_id' => $payment_id,
            'order_id' => $order_id,
            'signature' => $signature,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'timestamp' => date('Y-m-d H:i:s'),
            'status' => 'Payment Successful'
        ];
        
        // Fetch detailed payment information from Razorpay API
        $payment_details = getPaymentDetails($payment_id, $razorpay_key_id, $razorpay_key_secret);
        
        // Add API response details if available
        if ($payment_details && !isset($payment_details['error'])) {
            $webhook_data['amount_paid'] = ($payment_details['amount'] / 100) . ' INR';
            $webhook_data['currency'] = $payment_details['currency'] ?? 'INR';
            $webhook_data['method'] = $payment_details['method'] ?? 'N/A';
            $webhook_data['bank'] = $payment_details['bank'] ?? 'N/A';
            $webhook_data['wallet'] = $payment_details['wallet'] ?? 'N/A';
            $webhook_data['vpa'] = $payment_details['vpa'] ?? 'N/A';
            $webhook_data['card_id'] = $payment_details['card_id'] ?? 'N/A';
            $webhook_data['international'] = isset($payment_details['international']) ? ($payment_details['international'] ? 'Yes' : 'No') : 'N/A';
            $webhook_data['razorpay_fee'] = isset($payment_details['fee']) ? ($payment_details['fee'] / 100) . ' INR' : 'N/A';
            $webhook_data['razorpay_tax'] = isset($payment_details['tax']) ? ($payment_details['tax'] / 100) . ' INR' : 'N/A';
            $webhook_data['created_at'] = isset($payment_details['created_at']) ? date('Y-m-d H:i:s', $payment_details['created_at']) : 'N/A';
            
            // Card details if available
            if (isset($payment_details['card'])) {
                $card = $payment_details['card'];
                $webhook_data['card_network'] = $card['network'] ?? 'N/A';
                $webhook_data['card_type'] = $card['type'] ?? 'N/A';
                $webhook_data['card_last4'] = $card['last4'] ?? 'N/A';
                $webhook_data['card_issuer'] = $card['issuer'] ?? 'N/A';
            }
            
            // Acquirer data if available
            if (isset($payment_details['acquirer_data'])) {
                $webhook_data['auth_code'] = $payment_details['acquirer_data']['auth_code'] ?? 'N/A';
                $webhook_data['rrn'] = $payment_details['acquirer_data']['rrn'] ?? 'N/A';
            }
            
            // Notes if available
            if (isset($payment_details['notes']) && !empty($payment_details['notes'])) {
                $webhook_data['notes'] = json_encode($payment_details['notes']);
            }
            
            // Contact and email from payment details
            $webhook_data['razorpay_contact'] = $payment_details['contact'] ?? 'N/A';
            $webhook_data['razorpay_email'] = $payment_details['email'] ?? 'N/A';
            
        } elseif (isset($payment_details['error'])) {
            // Add error info for debugging
            $webhook_data['api_error'] = $payment_details['error'];
            $webhook_data['api_response'] = $payment_details['response'] ?? 'No response';
        } else {
            $webhook_data['api_error'] = 'Failed to fetch payment details from Razorpay API';
        }
        
        // Send WhatsApp message via Interakt API
        $whatsapp_result = sendWhatsAppMessage($customer_phone, $customer_name);
        
        // Add WhatsApp sending status to webhook data
        if ($whatsapp_result['success']) {
            $webhook_data['whatsapp_status'] = 'Message sent successfully';
            $webhook_data['whatsapp_response'] = $whatsapp_result['response'];
        } else {
            $webhook_data['whatsapp_status'] = 'Failed to send message';
            $webhook_data['whatsapp_error'] = 'HTTP Code: ' . $whatsapp_result['http_code'] . 
                                            ', Error: ' . $whatsapp_result['error'];
            $webhook_data['whatsapp_response'] = $whatsapp_result['response'];
        }
        
        $_SESSION['payment_success'] = true;
        $_SESSION['webhook_data'] = $webhook_data;
    } else {
        $_SESSION['payment_error'] = 'Payment verification failed';
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Clear session data if requested
if (isset($_GET['clear'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #2c5aa0;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .payment-form {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input[type="text"], input[type="email"], input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .pay-button {
            background: #2c5aa0;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
        }
        
        .pay-button:hover {
            background: #1e3d72;
        }
        
        .webhook-section {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .error-section {
            background: #ffeaea;
            border: 1px solid #f44336;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .webhook-data {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: monospace;
        }
        
        .clear-button {
            background: #666;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-top: 15px;
        }
        
        .clear-button:hover {
            background: #555;
        }
        
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .col {
            flex: 1;
        }
    </style>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Razorpay Payment Testing</h1>
        </div>
        
        <div class="content">
            <div class="info-box">
                <strong>Note:</strong> Make sure to replace the key_id and secret in the PHP code with your actual Razorpay test credentials.
            </div>
            
            <?php if (isset($_SESSION['payment_success'])): ?>
                <div class="webhook-section">
                    <h2>Payment Successful</h2>
                    <p>Payment has been processed successfully. Here are the webhook details:</p>
                    
                    <div class="webhook-data">
                        <?php foreach ($_SESSION['webhook_data'] as $key => $value): ?>
                            <strong><?php echo ucfirst(str_replace('_', ' ', $key)); ?>:</strong> <?php echo htmlspecialchars($value); ?><br>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="?clear=1" class="clear-button">Clear Results & Test Again</a>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['payment_error'])): ?>
                <div class="error-section">
                    <h2>Payment Error</h2>
                    <p><?php echo htmlspecialchars($_SESSION['payment_error']); ?></p>
                    <a href="?clear=1" class="clear-button">Clear & Try Again</a>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($_SESSION['payment_success'])): ?>
                <div class="payment-form">
                    <h2>Test Payment Form</h2>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="name">Customer Name</label>
                                <input type="text" id="name" value="John Doe" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="john@example.com" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" value="9876543210" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="amount">Amount (in INR)</label>
                                <input type="number" id="amount" value="100" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="pay-button" onclick="startPayment()">
                        Pay Now
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function startPayment() {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const amount = document.getElementById('amount').value;
            
            if (!name || !email || !phone || !amount) {
                alert('Please fill all fields');
                return;
            }
            
            // Disable button and show loading
            const button = document.querySelector('.pay-button');
            const originalText = button.textContent;
            button.textContent = 'Creating order...';
            button.disabled = true;
            
            // Create order first
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `create_order=1&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                button.textContent = originalText;
                button.disabled = false;
                
                if (data.success) {
                    // Start payment with actual order ID
                    const options = {
                        "key": "<?php echo $razorpay_key_id; ?>",
                        "amount": data.order.amount,
                        "currency": data.order.currency,
                        "name": "Test Payment",
                        "description": "Testing Razorpay Integration",
                        "order_id": data.order.id,
                        "handler": function (response) {
                            // Create a form to submit the payment data
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '';
                            
                            const paymentId = document.createElement('input');
                            paymentId.type = 'hidden';
                            paymentId.name = 'razorpay_payment_id';
                            paymentId.value = response.razorpay_payment_id;
                            form.appendChild(paymentId);
                            
                            const orderId = document.createElement('input');
                            orderId.type = 'hidden';
                            orderId.name = 'razorpay_order_id';
                            orderId.value = response.razorpay_order_id;
                            form.appendChild(orderId);
                            
                            const signature = document.createElement('input');
                            signature.type = 'hidden';
                            signature.name = 'razorpay_signature';
                            signature.value = response.razorpay_signature;
                            form.appendChild(signature);
                            
                            // Add customer details to form
                            const customerName = document.createElement('input');
                            customerName.type = 'hidden';
                            customerName.name = 'customer_name';
                            customerName.value = name;
                            form.appendChild(customerName);
                            
                            const customerEmail = document.createElement('input');
                            customerEmail.type = 'hidden';
                            customerEmail.name = 'customer_email';
                            customerEmail.value = email;
                            form.appendChild(customerEmail);
                            
                            const customerPhone = document.createElement('input');
                            customerPhone.type = 'hidden';
                            customerPhone.name = 'customer_phone';
                            customerPhone.value = phone;
                            form.appendChild(customerPhone);
                            
                            document.body.appendChild(form);
                            form.submit();
                        },
                        "prefill": {
                            "name": name,
                            "email": email,
                            "contact": phone
                        },
                        "theme": {
                            "color": "#2c5aa0"
                        }
                    };
                    
                    const rzp = new Razorpay(options);
                    
                    rzp.on('payment.failed', function (response) {
                        alert('Payment failed: ' + response.error.description);
                    });
                    
                    rzp.open();
                } else {
                    alert('Failed to create order: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                button.textContent = originalText;
                button.disabled = false;
                alert('Network error: ' + error.message);
            });
        }
    </script>
</body>
</html>
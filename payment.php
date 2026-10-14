<?php
session_start();
include('admin/include/db_config.php');

// Set your API Key and Auth Key here
define('API_KEY', '24da45ebc5a83205b521a7dfe54e3403');
define('AUTH_TOKEN', '42d10a7ab9586e94291dafa4219d00bd');

// Form Data received
$buyer_name = $_POST['name'];
$discountPercent = $_POST['discount-value'];
$couponCode = $_POST['coupon-code'];
$email = isset($_POST['email']) ? $_POST['email'] : '';
$user_id = $_POST['user_id'];
$amount = $_POST['amount'];
$phone = $_POST['phone'];
$paid_amount = $_POST['paid_amount'];

if (empty($email) && isset($_POST['user_id'])) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user_id) {
        $total1 = 0;

        // Calculate total from cart
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $course_id) {
                $stmt = $conn->prepare("SELECT price FROM courses WHERE id = ?");
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $course = $result->fetch_assoc();
                $stmt->close();

                if ($course) {
                    $total1 += $course['price'] * $_SESSION['quantities'][$course_id];
                }
            }
        }

        // Validate coupon code if applied
        if (!empty($couponCode) && $couponCode !== '0') {
            $c_stmt = $conn->prepare("SELECT id, code, discount, type, expiry_date, no_of_times, used_count, status FROM coupon WHERE UPPER(TRIM(code)) = UPPER(TRIM(?))");
            $c_stmt->bind_param("s", $couponCode);
            $c_stmt->execute();
            $c_res = $c_stmt->get_result();
            if ($c_row = $c_res->fetch_assoc()) {
                if (strtotime($c_row['expiry_date']) < strtotime(date('Y-m-d')) || $c_row['used_count'] >= $c_row['no_of_times'] || $c_row['status'] === 'expired' || $c_row['status'] === 'disabled') {
                    die("<script>alert('Coupon is expired or usage limit reached!'); window.location.href='checkout.php';</script>");
                }
                $couponCode = $c_row['code'];
            } else {
                die("<script>alert('Invalid coupon code!'); window.location.href='checkout.php';</script>");
            }
            $c_stmt->close();
        }

        // Insert order
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, discount_percent, couponcode, created_at) VALUES (?, ?, ?, ?, NOW())");
        $order_stmt->bind_param("idds", $user_id, $total1, $discountPercent, $couponCode);
        $order_stmt->execute();

        if ($order_stmt->affected_rows > 0) {
            $order_id = $order_stmt->insert_id;
            $_SESSION['order_id'] = $order_id;
        } else {
            die("Error: Order not inserted!");
        }
        $order_stmt->close();

        // NOTE: Coupon usage will be incremented AFTER successful payment in payment_status.php
        // This ensures the coupon is only marked as used when payment is actually completed
        // Removed coupon increment from here to prevent counting failed payments

        // Insert ordered courses
        foreach ($_SESSION['cart'] as $course_id) {
            $quantity = $_SESSION['quantities'][$course_id];
            $stmt = $conn->prepare("SELECT price FROM courses WHERE id = ?");
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $course = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($course) {
                $price = $course['price'];
                $order_detail_stmt = $conn->prepare("INSERT INTO order_details (order_id, course_id, quantity, price) VALUES (?, ?, ?, ?)");
                $order_detail_stmt->bind_param("iiid", $order_id, $course_id, $quantity, $price);
                $order_detail_stmt->execute();
                $order_detail_stmt->close();
            }
        }

        // Handle 100% Discount Case
        if (floatval($paid_amount) <= 0.01) {
            $update_status = $conn->prepare("UPDATE orders SET payment_status = 'completed', order_status = 'confirmed' WHERE id = ?");
            $update_status->bind_param("i", $order_id);
            $update_status->execute();
            $update_status->close();

            // Increment coupon usage for free order
            if (!empty($couponCode) && $couponCode !== '0') {
                $coupon_stmt = $conn->prepare("UPDATE coupon SET used_count = used_count + 1, remaining_uses = GREATEST(0, no_of_times - (used_count + 1)) WHERE UPPER(TRIM(code)) = UPPER(TRIM(?))");
                if ($coupon_stmt) {
                    $coupon_stmt->bind_param("s", $couponCode);
                    $coupon_stmt->execute();
                    $coupon_stmt->close();
                }

                $expire_stmt = $conn->prepare("UPDATE coupon SET status = 'expired' WHERE UPPER(TRIM(code)) = UPPER(TRIM(?)) AND used_count >= no_of_times");
                if ($expire_stmt) {
                    $expire_stmt->bind_param("s", $couponCode);
                    $expire_stmt->execute();
                    $expire_stmt->close();
                }
            }

            unset($_SESSION['cart']);
            unset($_SESSION['quantities']);
            unset($_SESSION['order_summary']);

            $redirect_base = defined('BASE_URL') ? rtrim(BASE_URL, '/') . '/' : './';
            header("Location: " . $redirect_base . "payment_status.php?payment_status=success&order_id=$order_id&payment_id=FREE");
            exit();
        }

        // Instamojo Payment Request
        $api_url = "https://www.instamojo.com/api/1.1/payment-requests/";
        
        // Use dynamic base URL for redirect so it works on localhost too
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        $isLocalhost = ($domainName == 'localhost' || $domainName == '127.0.0.1');
        $base_url = $isLocalhost ? $protocol . $domainName . "/araweb/secondsight_com_backup/" : $protocol . $domainName . "/";
        $redirect_url = $base_url . "payment_status.php";

        $data = [
            'purpose' => 'Course Purchase',
            'amount' => $amount,
            'currency' => 'INR',
            'buyer_name' => $buyer_name,
            'email' => $email,
            'phone' => $phone,
            'redirect_url' => $redirect_url,
            'send_email' => false,
            'send_sms' => false,
            'allow_repeated_payments' => false
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Api-Key: " . API_KEY,
            "X-Auth-Token: " . AUTH_TOKEN,
            "Content-Type: application/x-www-form-urlencoded"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if (isset($result['payment_request']['longurl'])) {
            $_SESSION['TID'] = $result['payment_request']['id'];
            header("Location: " . $result['payment_request']['longurl']);
            exit();
        } else {
            echo "Payment initiation failed: " . (is_array($result['message']) ? json_encode($result['message']) : ($result['message'] ?? "Unknown error"));
        }
    }
}
?>

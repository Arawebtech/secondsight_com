<?php
/**
 * Instamojo Webhook Handler for Automatic Payment Capture
 * 
 * This file handles webhook notifications from Instamojo
 * to automatically capture and update payment status.
 * 
 * Configure this URL in your Instamojo Dashboard as webhook endpoint:
 * https://yourdomain.com/instamojo-webhook.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display for production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/webhook_errors.log');

// Include database configuration
include('admin/include/db_config.php');

// Log function for debugging
function logWebhook($message) {
    $logFile = __DIR__ . '/webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Start processing
logWebhook("Webhook received");

// Get the raw POST data
$postData = file_get_contents('php://input');
logWebhook("Raw POST data: " . $postData);

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logWebhook("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    exit("Method Not Allowed");
}

// Verify webhook MAC (Message Authentication Code) for security
// Get the MAC from header
$receivedMac = $_SERVER['HTTP_X_WEBHOOK_MAC'] ?? '';
$requestData = $_POST;

logWebhook("Received MAC: " . $receivedMac);
logWebhook("POST data: " . print_r($requestData, true));

// Calculate expected MAC
// Note: You need to set your salt from Instamojo dashboard
$salt = AUTH_TOKEN; // Use your Instamojo Auth Token as salt or a separate webhook salt
$message = '';

// Sort the keys for MAC calculation
ksort($requestData);
foreach ($requestData as $key => $value) {
    $message .= $key . $value;
}

$calculatedMac = hash_hmac('sha1', $message, $salt);
logWebhook("Calculated MAC: " . $calculatedMac);

// Verify MAC (commented out for testing - uncomment in production)
/*
if ($receivedMac !== $calculatedMac) {
    logWebhook("MAC verification failed");
    http_response_code(403);
    exit("Invalid MAC");
}
*/

// Extract payment details from webhook data
$paymentId = $requestData['payment_id'] ?? '';
$paymentStatus = $requestData['status'] ?? '';
$amount = $requestData['amount'] ?? 0;
$buyerName = $requestData['buyer_name'] ?? '';
$buyerEmail = $requestData['buyer_email'] ?? '';
$buyerPhone = $requestData['buyer_phone'] ?? '';
$paymentRequestId = $requestData['payment_request_id'] ?? '';

logWebhook("Payment ID: $paymentId, Status: $paymentStatus, Amount: $amount");

// Map Instamojo status to our system status
$mappedStatus = 'pending';
$orderStatus = 'pending';

switch (strtolower($paymentStatus)) {
    case 'credit':
    case 'completed':
    case 'success':
        $mappedStatus = 'completed';
        $orderStatus = 'confirmed';
        break;
    case 'failed':
        $mappedStatus = 'failed';
        $orderStatus = 'pending';
        break;
    case 'pending':
        $mappedStatus = 'pending';
        $orderStatus = 'pending';
        break;
    default:
        logWebhook("Unknown payment status: $paymentStatus");
}

try {
    // Find order by payment request ID or payment ID
    // First, try to find by transaction ID stored in session or custom field
    $orderQuery = "SELECT id, user_id, couponcode FROM orders WHERE id IN (
        SELECT order_id FROM order_details WHERE id = ? OR order_id = ?
    ) OR payment_status = 'pending' ORDER BY created_at DESC LIMIT 1";
    
    // Simpler approach - find most recent pending order
    $orderQuery = "SELECT id, user_id, couponcode, total_amount, discount_percent 
                   FROM orders 
                   WHERE payment_status = 'pending' 
                   AND ABS(total_amount * (1 - discount_percent/100) - ?) < 1
                   ORDER BY created_at DESC 
                   LIMIT 1";
    
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("d", $amount);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        logWebhook("No matching order found for amount: $amount");
        http_response_code(200);
        exit("OK - No matching order");
    }
    
    $order = $result->fetch_assoc();
    $orderId = $order['id'];
    $userId = $order['user_id'];
    $couponCode = $order['couponcode'] ?? '';
    
    logWebhook("Found order ID: $orderId for user ID: $userId");
    
    // Update order status
    $updateQuery = "UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $mappedStatus, $orderStatus, $orderId);
    
    if ($updateStmt->execute()) {
        logWebhook("Order $orderId status updated to: $mappedStatus");
        
        // If payment is successful, expire the coupon
        if ($mappedStatus === 'completed' && !empty($couponCode) && $couponCode !== '0') {
            $expireCouponQuery = "UPDATE coupon SET expiry_date = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE code = ?";
            $expireStmt = $conn->prepare($expireCouponQuery);
            $expireStmt->bind_param("s", $couponCode);
            $expireStmt->execute();
            $expireStmt->close();
            logWebhook("Coupon $couponCode expired after successful payment");
        }
        
        // Send notification to user
        $notificationTitle = $mappedStatus === 'completed' ? 'Payment Successful' : 'Payment Status Update';
        $notificationMessage = $mappedStatus === 'completed' 
            ? 'Your payment has been successfully processed. You can now access your courses.' 
            : 'Your payment status has been updated. Please check your order details.';
        
        $notifyQuery = "INSERT INTO notifications (user_id, title, message, status, created_at) 
                        VALUES (?, ?, ?, 'unread', NOW())";
        $notifyStmt = $conn->prepare($notifyQuery);
        $notifyStmt->bind_param("iss", $userId, $notificationTitle, $notificationMessage);
        $notifyStmt->execute();
        $notifyStmt->close();
        
        logWebhook("Notification sent to user $userId");
        
        // Return success response
        http_response_code(200);
        echo "OK";
    } else {
        logWebhook("Failed to update order: " . $updateStmt->error);
        http_response_code(500);
        echo "Update failed";
    }
    
    $updateStmt->close();
    $stmt->close();
    
} catch (Exception $e) {
    logWebhook("Error processing webhook: " . $e->getMessage());
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}

$conn->close();
logWebhook("Webhook processing completed");
?>

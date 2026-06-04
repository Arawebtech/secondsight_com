<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include('admin/include/db_config.php');

// Get parameters from GET request
$status = $_GET['payment_status'] ?? 'failed';
$payment_id = $_GET['payment_id'] ?? 'N/A';
$order_id = $_GET['order_id'] ?? ($_SESSION['order_id'] ?? null);
$user_id = $_SESSION['user_id'] ?? null;

$success_message = "";
$order_summary = [];
$purchase_date = "";
$total_order_amount = 0; // This will store the final amount paid
$subtotal_before_discount = 0; // This will store the sum of individual item subtotals
$order_discount_percent = 0; // Initialize discount percentage from orders table

// Safety checks for missing session
if (!$order_id || !$user_id) {
    die("Session expired or invalid access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $payment_status = $_GET['payment_status'] ?? 'failed';
    $order_status = "confirmed";

    // First, check if this order already has a valid payment or if it's genuinely free
    $orderCheckQuery = "SELECT total_amount, discount_percent, payment_status, couponcode FROM orders WHERE id = ?";
    $stmtCheck = $conn->prepare($orderCheckQuery);
    $stmtCheck->bind_param("i", $order_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $orderData = $resultCheck->fetch_assoc();
    $stmtCheck->close();

    if (!$orderData) {
        die("Order not found.");
    }

    $order_total = $orderData['total_amount'];
    $order_discount_percent = $orderData['discount_percent'];
    $existing_payment_status = $orderData['payment_status'];
    $coupon_code_used = $orderData['couponcode'] ?? '';

    // Calculate if this should be a free order (100% discount or $0 total)
    $calculated_discount = ($order_total * $order_discount_percent) / 100;
    $final_amount = $order_total - $calculated_discount;
    $is_free_order = ($final_amount <= 0.01); // Consider amounts less than 1 cent as free

    // Only process as successful payment if:
    // 1. Payment status is explicitly 'success' AND payment_id is not 'FREE', OR
    // 2. It's genuinely a free order (100% discount or $0 total) AND payment_id is 'FREE'
    $valid_paid_transaction = ($payment_status === 'success' && $payment_id !== 'FREE' && $payment_id !== 'N/A');
    $valid_free_transaction = ($is_free_order && $payment_id === 'FREE');

    if ($valid_paid_transaction || $valid_free_transaction) {
        $payment_status = "completed";

        // Update order table only if not already completed
        if ($existing_payment_status !== 'completed') {
            $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ? WHERE id = ?");
            $stmt->bind_param("ssi", $order_status, $payment_status, $order_id);
            $stmt->execute();
            $stmt->close();

            // **IMPORTANT: Increment coupon usage ONLY after successful payment**
            if (!empty($coupon_code_used) && $coupon_code_used !== '0') {
                $coupon_increment_query = "UPDATE coupon SET used_count = used_count + 1, remaining_uses = remaining_uses - 1 WHERE code = ?";
                $coupon_increment_stmt = $conn->prepare($coupon_increment_query);
                $coupon_increment_stmt->bind_param("s", $coupon_code_used);
                $coupon_increment_stmt->execute();
                $coupon_increment_stmt->close();
            }

            // Mark coupon as expired after successful payment
            if (!empty($coupon_code_used) && $coupon_code_used !== '0') {
                // Mark coupon as expired after successful payment
                $expireCouponQuery = "UPDATE coupon SET expiry_date = DATE_SUB(NOW(), INTERVAL 1 DAY) WHERE code = ?";
                $stmtExpire = $conn->prepare($expireCouponQuery);
                $stmtExpire->bind_param("s", $coupon_code_used);
                $stmtExpire->execute();
                $stmtExpire->close();
            }

            // Clear the cart session
            unset($_SESSION['cart'], $_SESSION['quantities']);
        }

        // Fetch order date, total amount, and discount_percent from the 'orders' table
        $orderMainQuery = "SELECT created_at AS order_date, total_amount, discount_percent FROM orders WHERE id = ?";
        $stmtMainOrder = $conn->prepare($orderMainQuery);
        $stmtMainOrder->bind_param("i", $order_id);
        $stmtMainOrder->execute();
        $resultMainOrder = $stmtMainOrder->get_result();
        if ($rowMainOrder = $resultMainOrder->fetch_assoc()) {
            $purchase_date = date('F j, Y', strtotime($rowMainOrder['order_date'])); // Format the date
            $total_order_amount = $rowMainOrder['total_amount']; // This is the final amount paid
            $order_discount_percent = $rowMainOrder['discount_percent']; // Get discount percent
        }
        $stmtMainOrder->close();

        // Fetch order details, joining with 'courses' for s_name and 'order_details' for price, quantity, and item_subtotal
        $orderDetailsQuery = "SELECT od.id, od.course_id, od.quantity, od.price, od.subtotal AS item_subtotal, c.s_name AS course_name_from_db 
                              FROM order_details od
                              JOIN courses c ON od.course_id = c.id
                              WHERE od.order_id = ?";
        $stmt = $conn->prepare($orderDetailsQuery);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $order_detail_id = $row['id'];
            $course_id = $row['course_id'];
            $course_name = $row['course_name_from_db'];
            $course_price = $row['price'];
            $course_quantity = $row['quantity'];
            $item_subtotal_row = $row['item_subtotal']; // Subtotal for this specific order_detail item

            $subtotal_before_discount += $item_subtotal_row; // Accumulate for the overall subtotal

            // Note: Batch assignment is now handled through batch codes and enrollments
            if ($is_free_order) {
                $batch_title = "Free course access granted";
            } else {
                $batch_title = "Course purchased successfully";
            }

            // Add course details including batch title, quantity and item_subtotal to summary
            $order_summary[] = [
                'name' => $course_name,
                'price' => $course_price,
                'quantity' => $course_quantity,
                'item_subtotal' => $item_subtotal_row,
                'batch' => $batch_title
            ];
        }
        $stmt->close();

        if ($is_free_order) {
            $success_message = "🎉 Thank You for Your Interest!
            <br>Congratulations! Your free course access has been activated. You can start learning anytime!";
        } else {
            $success_message = "🎉 Thank You for Your Purchase!
            <br>Congratulations on taking the next step in your learning journey. Your access to the course is now active, and you can start anytime!";
        }
    } else {
        // Payment failed or invalid access attempt
        if ($payment_status === 'success' && $payment_id === 'FREE' && !$is_free_order) {
            $success_message = "❌ Invalid Access! This order requires payment. Please complete the payment process.";
        } else {
            $success_message = "❌ Oops! Your payment failed. Please try again.";
        }

        // Optionally, fetch order details to show what was in the failed order attempt
        $orderMainQuery = "SELECT created_at AS order_date, total_amount, discount_percent FROM orders WHERE id = ?";
        $stmtMainOrder = $conn->prepare($orderMainQuery);
        $stmtMainOrder->bind_param("i", $order_id);
        $stmtMainOrder->execute();
        $resultMainOrder = $stmtMainOrder->get_result();
        if ($rowMainOrder = $resultMainOrder->fetch_assoc()) {
            $purchase_date = date('F j, Y', strtotime($rowMainOrder['order_date']));
            $total_order_amount = $rowMainOrder['total_amount'];
            $order_discount_percent = $rowMainOrder['discount_percent'];
        }
        $stmtMainOrder->close();

        $orderDetailsQuery = "SELECT od.id, od.course_id, od.quantity, od.price, od.subtotal AS item_subtotal, c.s_name AS course_name_from_db 
                              FROM order_details od
                              JOIN courses c ON od.course_id = c.id
                              WHERE od.order_id = ?";
        $stmt = $conn->prepare($orderDetailsQuery);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $item_subtotal_row = $row['item_subtotal'];
            $subtotal_before_discount += $item_subtotal_row; // Accumulate even for failed orders for display

            $order_summary[] = [
                'name' => $row['course_name_from_db'],
                'price' => $row['price'],
                'quantity' => $row['quantity'],
                'item_subtotal' => $item_subtotal_row,
                'batch' => 'N/A (Payment Required)'
            ];
        }
        $stmt->close();
    }
}

// Calculate discount applied based on the percentage from the orders table
$discount_applied = ($subtotal_before_discount * $order_discount_percent) / 100;

// Ensure discount is not negative (in case of free purchases or specific discount logic)
if ($discount_applied < 0) {
    $discount_applied = 0;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --success-color: #059669;
            --error-color: #dc2626;
            --text-primary: #1f2937;
            --text-medium: #6b7280;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--text-primary);
            font-weight: 700;
        }

        .message-box {
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 500;
        }

        .message-box.success {
            background-color: #d1fae5;
            color: var(--success-color);
            border: 1px solid #a7f3d0;
        }

        .message-box.error {
            background-color: #fee2e2;
            color: var(--error-color);
            border: 1px solid #fca5a5;
        }

        .details-section {
            margin-bottom: 2rem;
        }

        .details-section h3 {
            margin-bottom: 1rem;
            color: var(--text-primary);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }

        .details-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .details-item:last-child {
            border-bottom: none;
        }

        .course-list {
            list-style: none;
            margin: 1rem 0;
        }

        .course-list li {
            background: var(--bg-light);
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            border-left: 4px solid var(--primary-color);
        }

        .course-name {
            font-weight: 600;
            display: block;
            margin-bottom: 0.5rem;
        }

        .course-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: var(--text-medium);
        }

        .course-price-qty {
            display: flex;
            gap: 1rem;
        }

        .total-line {
            font-weight: 600;
            font-size: 1.1rem;
            border-top: 2px solid var(--border-color);
            padding-top: 1rem;
        }

        .discount-line {
            color: var(--success-color);
            font-weight: 500;
        }

        .final-total-line {
            font-weight: 700;
            font-size: 1.2rem;
            background: var(--bg-light);
            padding: 1rem;
            border-radius: 6px;
            border: 2px solid var(--primary-color);
        }

        .actions-section {
            text-align: center;
            margin-top: 2rem;
        }

        .action-link {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .action-link:hover {
            background-color: #1d4ed8;
        }

        .action-link.retry {
            background-color: var(--error-color);
        }

        .action-link.retry:hover {
            background-color: #b91c1c;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .course-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }

            .course-price-qty {
                flex-direction: column;
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Order Confirmation</h2>

        <div class="message-box <?= ($payment_status === 'completed') ? 'success' : 'error' ?>">
            <?= $success_message ?>
        </div>
        
        <?php if (!empty($order_summary)): ?>
        <div class="details-section">
            <h3>Order Details</h3>
            <div class="details-item">
                <span>Order ID:</span>
                <strong><?= htmlspecialchars($order_id) ?></strong>
            </div>
            <?php if ($payment_id !== 'N/A' && $payment_status === 'completed'): ?>
            <div class="details-item">
                <span>Payment ID:</span>
                <strong><?= htmlspecialchars($payment_id) ?></strong>
            </div>
            <?php endif; ?>
            <?php if (!empty($purchase_date)): ?>
            <div class="details-item">
                <span>Purchased On:</span>
                <strong><?= htmlspecialchars($purchase_date) ?></strong>
            </div>
            <?php endif; ?>
            
            <h4>Items Purchased:</h4>
            <ul class="course-list">
                <?php foreach ($order_summary as $item): ?>
                    <li>
                        <span class="course-name"><?= htmlspecialchars($item['name']) ?></span>
                        <div class="course-details">
                            <div class="course-price-qty">
                                <span><span class="course-price-unit">Unit Price:</span> &#8377;<?= number_format($item['price'], 2) ?></span>
                                <span><span class="course-qty">Qty:</span> <?= htmlspecialchars($item['quantity']) ?></span>
                            </div>
                            <span>Item Subtotal: <span class="course-item-subtotal">&#8377;<?= number_format($item['item_subtotal'], 2) ?></span></span>
                        </div>
                        <?php if (!empty($item['batch'])): ?>
                            <span class="course-details" style="margin-top: 5px; font-style: italic;">
                                Batch: <strong><?= htmlspecialchars($item['batch']) ?></strong>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="details-item total-line">
                <span>Subtotal:</span>
                <span>&#8377;<?= number_format($subtotal_before_discount, 2) ?></span>
            </div>

            <?php if ($order_discount_percent > 0): ?>
            <div class="details-item discount-line">
                <span>Discount Applied (<?= htmlspecialchars($order_discount_percent) ?>%):</span>
                <span>-&#8377;<?= number_format($discount_applied, 2) ?></span>
            </div>
            <?php endif; ?>

            <div class="details-item final-total-line">
                <span>Total Paid:</span>
                <span>&#8377;<?= number_format($total_order_amount - $discount_applied, 2) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <div class="actions-section">
            <?php if ($payment_status === 'completed'): ?>
                <a href="profile.php" class="action-link">Go to My Courses</a>
            <?php else: ?>
                <p style="margin-bottom: 20px; color: var(--text-medium);">
                    Please ensure your payment details are correct and try again, or contact support if the issue persists.
                </p>
                <a href="checkout.php?order_id=<?= htmlspecialchars($order_id) ?>" class="action-link retry">Try Payment Again</a>
                <a href="contact.php" class="action-link" style="margin-left: 10px;">Contact Support</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

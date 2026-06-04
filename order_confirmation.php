<?php
session_start();
include('admin/include/db_config.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? ($_SESSION['order_id'] ?? null);
$order_success = true;
$order_details = [];
$total_amount = 0;
$discount_applied = 0;
$final_amount = 0;
$payment_method = 'Online';
$order_date = date('F j, Y');

// Fetch order and course details
if ($order_id) {
    $order_query = "SELECT o.*, u.name, u.email 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.id = ? AND o.user_id = ?";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order_result = $stmt->get_result();
    
    if ($order_data = $order_result->fetch_assoc()) {
        $total_amount = $order_data['total_amount'];
        $discount_percent = $order_data['discount_percent'] ?? 0;
        $discount_applied = ($total_amount * $discount_percent) / 100;
        $final_amount = $total_amount - $discount_applied;
        $order_date = date('F j, Y', strtotime($order_data['created_at']));
        $customer_name = $order_data['name'];
        $customer_email = $order_data['email'];
        $coupon_code = $order_data['couponcode'] ?? 'N/A';
        
        // Fetch course details
        $courses_query = "SELECT c.s_title, od.quantity, od.price, od.subtotal 
                         FROM order_details od 
                         JOIN courses c ON od.course_id = c.id 
                         WHERE od.order_id = ?";
        $stmt_courses = $conn->prepare($courses_query);
        $stmt_courses->bind_param("i", $order_id);
        $stmt_courses->execute();
        $courses_result = $stmt_courses->get_result();
        
        while ($course = $courses_result->fetch_assoc()) {
            $order_details[] = $course;
        }
        $stmt_courses->close();
    }
    $stmt->close();
}

if ($order_success) {
    // Clear the cart
    unset($_SESSION['cart']);
    unset($_SESSION['quantities']);
    
    if ($final_amount <= 0.01) {
        $success_message = "🎉 Thank You for Your Interest! Your free course access has been activated.";
    } else {
        $success_message = "🎉 Thank You for Your Purchase! Your payment has been processed successfully.";
    }
} else {
    $success_message = "There was an issue processing your order. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Thank You!</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .content {
            padding: 40px;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .order-info-row:last-child {
            border-bottom: none;
        }
        
        .order-info-label {
            font-weight: 500;
            color: #666;
        }
        
        .order-info-value {
            font-weight: 600;
            color: #333;
        }
        
        .order-items {
            margin: 30px 0;
        }
        
        .order-items h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .item-name {
            font-weight: 500;
            color: #333;
        }
        
        .item-details {
            text-align: right;
            color: #666;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        
        .summary-row.total {
            border-top: 2px solid #667eea;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .note {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .note p {
            color: #856404;
            margin: 0;
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .order-info-row, .summary-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .item {
                flex-direction: column;
                gap: 10px;
            }
            
            .item-details {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✓</div>
            <h1>Order Confirmed!</h1>
            <p><?= htmlspecialchars($success_message) ?></p>
        </div>
        
        <div class="content">
            <?php if (!empty($order_details)): ?>
                <div class="order-info">
                    <div class="order-info-row">
                        <span class="order-info-label">Order ID:</span>
                        <span class="order-info-value">#<?= htmlspecialchars($order_id) ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Order Date:</span>
                        <span class="order-info-value"><?= htmlspecialchars($order_date) ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Customer Name:</span>
                        <span class="order-info-value"><?= htmlspecialchars($customer_name ?? 'N/A') ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Email:</span>
                        <span class="order-info-value"><?= htmlspecialchars($customer_email ?? 'N/A') ?></span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Payment Method:</span>
                        <span class="order-info-value"><?= $final_amount <= 0.01 ? 'Free' : 'Online Payment' ?></span>
                    </div>
                    <?php if ($coupon_code !== 'N/A' && $coupon_code !== '0'): ?>
                    <div class="order-info-row">
                        <span class="order-info-label">Coupon Applied:</span>
                        <span class="order-info-value"><?= htmlspecialchars($coupon_code) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="order-items">
                    <h3>📚 Course Details</h3>
                    <?php foreach ($order_details as $item): ?>
                        <div class="item">
                            <div class="item-name"><?= htmlspecialchars($item['s_title']) ?></div>
                            <div class="item-details">
                                <div>Quantity: <?= htmlspecialchars($item['quantity']) ?></div>
                                <div>Price: ₹<?= number_format($item['price'], 2) ?></div>
                                <div><strong>Subtotal: ₹<?= number_format($item['subtotal'], 2) ?></strong></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?= number_format($total_amount, 2) ?></span>
                    </div>
                    <?php if ($discount_applied > 0): ?>
                    <div class="summary-row">
                        <span>Discount (<?= number_format($discount_percent, 2) ?>%):</span>
                        <span style="color: #28a745;">-₹<?= number_format($discount_applied, 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row total">
                        <span>Total Paid:</span>
                        <span>₹<?= number_format($final_amount, 2) ?></span>
                    </div>
                </div>
                
                <div class="note">
                    <p><strong>📧 Email Confirmation:</strong> A confirmation email has been sent to your registered email address with your course access details.</p>
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="profile.php" class="btn btn-primary">Go to My Courses</a>
                <a href="courses.php" class="btn btn-secondary">Browse More Courses</a>
            </div>
        </div>
    </div>
</body>
</html>

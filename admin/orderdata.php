<?php
error_reporting(0);
session_start();

// Check if user is logged in
if (empty($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

include("include/db_config.php");

// Build WHERE clause based on filters (same logic as view-order.php)
$whereConditions = [];
$activeMonth = isset($_GET['month']) ? (int)$_GET['month'] : null;
$activeYear = isset($_GET['year']) ? (int)$_GET['year'] : null;
$activeOrderStatus = isset($_GET['order_status']) ? $_GET['order_status'] : '';
$activePaymentStatus = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($activeMonth && $activeYear) {
    $whereConditions[] = "MONTH(o.created_at) = $activeMonth AND YEAR(o.created_at) = $activeYear";
}

if ($activeOrderStatus) {
    $whereConditions[] = "o.order_status = '" . mysqli_real_escape_string($conn, $activeOrderStatus) . "'";
}

if ($activePaymentStatus) {
    $whereConditions[] = "o.payment_status = '" . mysqli_real_escape_string($conn, $activePaymentStatus) . "'";
}

if ($searchTerm) {
    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
            $whereConditions[] = "(u.name LIKE '%$searchTerm%' OR o.couponcode LIKE '%$searchTerm%' OR o.id LIKE '%$searchTerm%')";
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Enhanced query with more details for export
$query = "SELECT 
    o.id as order_id,
    u.name AS username,
    u.email AS user_email,
    o.total_amount,
    o.discount_percent,
    (o.total_amount - (o.total_amount * o.discount_percent / 100)) as paid_amount_after_discount,
    (o.total_amount * o.discount_percent / 100) as discount_amount,
    o.order_status,
    o.payment_status,
    o.couponcode,
    
    o.created_at,
    o.updated_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $whereClause
    ORDER BY o.id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Generate filename with current date and filters
$filename = "orders_export_" . date('Y-m-d_H-i-s');
if ($activeMonth && $activeYear) {
    $monthNames = [
        1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun",
        7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec"
    ];
    $filename .= "_" . $monthNames[$activeMonth] . "_" . $activeYear;
}
if ($activeOrderStatus) {
    $filename .= "_" . $activeOrderStatus;
}
if ($activePaymentStatus) {
    $filename .= "_payment_" . $activePaymentStatus;
}
$filename .= ".csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 (helps with Excel compatibility)
fputs($output, "\xEF\xBB\xBF");

// CSV Headers
$headers = [
    'Order ID',
    'Username',
    'Email',
    'Phone',
    'Total Amount (₹)',
    'Discount %',
    'Discount Amount (₹)',
    'Paid Amount After Discount (₹)',
    'Order Status',
    'Payment Status',
    'Coupon Code',
    'Batch Code',
    'Created Date',
    'Updated Date'
];

fputcsv($output, $headers);

// Initialize totals for summary
$totalOrders = 0;
$totalAmount = 0;
$totalDiscountAmount = 0;
$totalPaidAmount = 0;
$statusCounts = [
    'pending' => 0,
    'confirmed' => 0,
    'failed' => 0
];
$paymentCounts = [
    'pending' => 0,
    'completed' => 0,
    'failed' => 0
];

// Process and output data
while ($row = mysqli_fetch_assoc($result)) {
    $csvRow = [
        $row['order_id'],
        $row['username'],
        $row['user_email'],
        $row['user_phone'],
        number_format($row['total_amount'], 2),
        $row['discount_percent'],
        number_format($row['discount_amount'], 2),
        number_format($row['paid_amount_after_discount'], 2),
        ucfirst($row['order_status']),
        ucfirst($row['payment_status']),
        $row['couponcode'] ?? '',
        '', // Batch codes are now handled separately
        date('d-m-Y H:i:s', strtotime($row['created_at'])),
        $row['updated_at'] ? date('d-m-Y H:i:s', strtotime($row['updated_at'])) : ''
    ];
    
    fputcsv($output, $csvRow);
    
    // Calculate totals
    $totalOrders++;
    $totalAmount += $row['total_amount'];
    $totalDiscountAmount += $row['discount_amount'];
    $totalPaidAmount += $row['paid_amount_after_discount'];
    
    // Count statuses
    if (isset($statusCounts[$row['order_status']])) {
        $statusCounts[$row['order_status']]++;
    }
    if (isset($paymentCounts[$row['payment_status']])) {
        $paymentCounts[$row['payment_status']]++;
    }
}

// Add summary section
fputcsv($output, []); // Empty row
fputcsv($output, ['=== SUMMARY ===']);
fputcsv($output, ['Total Orders', $totalOrders]);
fputcsv($output, ['Total Amount (Before Discount)', '₹' . number_format($totalAmount, 2)]);
fputcsv($output, ['Total Discount Given', '₹' . number_format($totalDiscountAmount, 2)]);
fputcsv($output, ['Total Paid Amount (After Discount)', '₹' . number_format($totalPaidAmount, 2)]);

fputcsv($output, []); // Empty row
fputcsv($output, ['=== ORDER STATUS SUMMARY ===']);
foreach ($statusCounts as $status => $count) {
    fputcsv($output, [ucfirst($status) . ' Orders', $count]);
}

fputcsv($output, []); // Empty row
fputcsv($output, ['=== PAYMENT STATUS SUMMARY ===']);
foreach ($paymentCounts as $status => $count) {
    fputcsv($output, [ucfirst($status) . ' Payments', $count]);
}

// Add export information
fputcsv($output, []); // Empty row
fputcsv($output, ['=== EXPORT INFO ===']);
fputcsv($output, ['Export Date', date('d-m-Y H:i:s')]);
fputcsv($output, ['Exported By', $_SESSION['name'] ?? 'Admin']);

// Add applied filters info
if (!empty($whereConditions)) {
    fputcsv($output, []); // Empty row
    fputcsv($output, ['=== APPLIED FILTERS ===']);
    
    if ($activeMonth && $activeYear) {
        $monthNames = [
            1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
            7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December"
        ];
        fputcsv($output, ['Month/Year Filter', $monthNames[$activeMonth] . ' ' . $activeYear]);
    }
    
    if ($activeOrderStatus) {
        fputcsv($output, ['Order Status Filter', ucfirst($activeOrderStatus)]);
    }
    
    if ($activePaymentStatus) {
        fputcsv($output, ['Payment Status Filter', ucfirst($activePaymentStatus)]);
    }
    
    if ($searchTerm) {
        fputcsv($output, ['Search Term', $searchTerm]);
    }
}

fclose($output);
mysqli_close($conn);
exit();
?>
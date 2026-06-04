<?php
error_reporting(0);
session_start();
if (empty($_SESSION['name'])) {
    header('Location:index.php');
}
include("include/db_config.php");

// Handle refund request
if (isset($_POST['refund_order'])) {
    $order_id = $_POST['refund_order_id'];
    $refund_amount = $_POST['refund_amount'];
    $refund_reason = $_POST['refund_reason'];

    $insert_query = "INSERT INTO refunds (order_id, refund_amount, refund_reason) VALUES ('$order_id', '$refund_amount', '$refund_reason')";
    $insert_result = mysqli_query($conn, $insert_query);
    
    if ($insert_result) {
        $msg = "<p style='color:green;'>Refund processed successfully for Order ID: $order_id</p>";
    } else {
        $msg = "<p style='color:red;'>Error processing refund: " . mysqli_error($conn) . "</p>";
    }
}

// Handle payment status update
if (isset($_POST['update_payment_status'])) {
    $order_id = $_POST['order_id'];
    $payment_status = $_POST['payment_status'];

    $update_query = "UPDATE orders SET payment_status = '$payment_status' WHERE id = '$order_id'";
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        $msg = "<p style='color:green;'>Payment status updated successfully </p>";
    } else {
        $msg = "<p style='color:red;'>Error updating payment status: " . mysqli_error($conn) . "</p>";
    }
}

// Handle order status update
if (isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    $update_query = "UPDATE orders SET order_status = '$order_status' WHERE id = '$order_id'";
    $update_result = mysqli_query($conn, $update_query);
    
    if ($update_result) {
        $msg = "<p style='color:green;'>Order status updated successfully.</p>";
    } else {
        $msg = "<p style='color:red;'>Error updating payment status: " . mysqli_error($conn) . "</p>";
    }
}

if (isset($_GET['del'])) {
    $id = intval($_GET['del']);

    $sqldetails = "DELETE FROM order_details WHERE order_id = $id";
    $resultdetails = mysqli_query($conn, $sqldetails);

    $sqld = "DELETE FROM orders WHERE id = $id";
    $resultorders = mysqli_query($conn, $sqld);

    if ($resultdetails && $resultorders) {
        if (mysqli_affected_rows($conn) > 0) {
            $msg = "<p style='color:green;'>Record and its associated details have been deleted successfully</p>";
        } else {
            $msg = "<p style='color:red;'>No record found with the provided ID</p>";
        }
    } else {
        $msg = "<p style='color:red;'>Error deleting records: " . mysqli_error($conn) . "</p>";
    }
}

// Fetch all batch name, id
$sql = "SELECT * FROM batch";
$result = $conn->query($sql);

$batches = [];

if ($result->num_rows > 0) {
    while ($rowb = $result->fetch_assoc()) {
        $batches[$rowb['id']] = $rowb['batch_title'];
    }
} 

// Update batch in order table and batch table
if (isset($_POST['submit-batch'])) {
    $order_id = $_POST['order_id'];
    $batch_id = $_POST['batch_id'];
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $title = "New Batch Assign";
   
    if (!empty($order_id) && !empty($batch_id)) {
        $sql = "UPDATE order_details SET batch_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $batch_id, $order_id);
        
        if ($stmt->execute()) {
            $batch_sql = "SELECT batch_title FROM batch WHERE id = ?";
            $batch_stmt = $conn->prepare($batch_sql);
            $batch_stmt->bind_param("i", $batch_id);
            $batch_stmt->execute();
            $batch_stmt->bind_result($batch_title);
            $batch_stmt->fetch();
            $batch_stmt->close();

            if (isset($batch_title)) {
                $sql = "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iss", $user_id, $title, $batch_title);
                if ($stmt->execute()) {
                    echo "<script>alert('Batch assigned successfully!'); window.location.href='view-order.php';</script>";
                } else {
                    echo "<script>alert('Error inserting notification'); history.back();</script>";
                }
            } else {
                echo "<script>alert('Batch title not found'); history.back();</script>";
            }
        } else {
            echo "<script>alert('Error assigning batch'); history.back();</script>";
        }
    } else {
        echo "<script>alert('Please select a batch and an order'); history.back();</script>";
    }
}

// Build WHERE clause based on filters
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

// Main query with filters
$query = "SELECT 
    o.id,
    o.couponcode,
    o.discount_percent,
    u.name AS username, 
    o.total_amount, 
    o.paid_amount,
    o.order_status, 
    o.payment_status, 
     
    o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $whereClause
    ORDER BY o.id DESC";

$result_item = mysqli_query($conn, $query);

// Calculate totals with proper discount calculation
$totalQuery = "SELECT 
    COUNT(*) as total_orders,
    SUM(o.total_amount) as total_amount_sum,
    SUM(o.total_amount - (o.total_amount * o.discount_percent / 100)) as actual_paid_sum,
    SUM(o.total_amount * o.discount_percent / 100) as total_discount_given,
    SUM(CASE WHEN o.order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
    SUM(CASE WHEN o.order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN o.order_status = 'failed' THEN 1 ELSE 0 END) as failed_orders,
    SUM(CASE WHEN o.payment_status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
    SUM(CASE WHEN o.payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
    SUM(CASE WHEN o.payment_status = 'failed' THEN 1 ELSE 0 END) as failed_payments
    FROM orders o
    JOIN users u ON o.user_id = u.id
    $whereClause";

$totalResult = mysqli_query($conn, $totalQuery);
$totals = mysqli_fetch_assoc($totalResult);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin- View Orders</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" href="<?=$base_url;?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">

    <!-- Custom CSS -->
    <style>
        .modal-dialog {
            margin: 119px auto!important;
        }
        .fade {
            opacity: 1;
        }
        .filter-container {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .counter-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .counter-card {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3c8dbc;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            min-width: 200px;
        }
        .counter-card.success {
            border-left-color: #00a65a;
        }
        .counter-card.warning {
            border-left-color: #f39c12;
        }
        .counter-card.danger {
            border-left-color: #dd4b39;
        }
        .counter-card h4 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: bold;
        }
        .counter-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        .search-container {
            margin-bottom: 15px;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }
        .form-group-inline {
            flex: 1;
            min-width: 150px;
        }
        .btn-group-inline {
            display: flex;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .counter-cards {
                flex-direction: column;
            }
            .form-row {
                flex-direction: column;
            }
            .btn-group-inline {
                flex-direction: column;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php include('include/header.php'); ?>
        <?php include('include/side-bar.php'); ?>

        <div class="content-wrapper">
            <section class="content-header" style="margin-top:35px;padding-left:28px">
                <p>
                    <?php if (!empty($msg)) { echo $msg; } ?>
                </p>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- Counter Cards -->
                        <div class="counter-cards">
                            <div class="counter-card">
                                <h4><?php echo number_format($totals['total_orders']); ?></h4>
                                <p>Total Orders</p>
                            </div>
                            <div class="counter-card success">
                                <h4>₹<?php echo number_format($totals['total_amount_sum'], 2); ?></h4>
                                <p>Total Amount (Before Discount)</p>
                            </div>
                            <div class="counter-card success">
                                <h4>₹<?php echo number_format($totals['actual_paid_sum'], 2); ?></h4>
                                <p>Actual Paid Amount (After Discount)</p>
                            </div>
                            <div class="counter-card warning">
                                <h4>₹<?php echo number_format($totals['total_discount_given'], 2); ?></h4>
                                <p>Total Discount Given</p>
                            </div>
                        </div>

                        <!-- Additional Status Cards -->
                        <div class="counter-cards">
                            <div class="counter-card success">
                                <h4><?php echo $totals['confirmed_orders']; ?></h4>
                                <p>Confirmed Orders</p>
                            </div>
                            <div class="counter-card warning">
                                <h4><?php echo $totals['pending_orders']; ?></h4>
                                <p>Pending Orders</p>
                            </div>
                            <div class="counter-card danger">
                                <h4><?php echo $totals['failed_orders']; ?></h4>
                                <p>Failed Orders</p>
                            </div>
                            <div class="counter-card success">
                                <h4><?php echo $totals['completed_payments']; ?></h4>
                                <p>Completed Payments</p>
                            </div>
                        </div>

                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Orders List</h3>
                                <div class="box-tools pull-right">
                                    <form action="orderdata.php" method="get">
                                        <!-- Pass current filter parameters to download -->
                                        <?php if ($activeMonth): ?>
                                            <input type="hidden" name="month" value="<?php echo $activeMonth; ?>">
                                        <?php endif; ?>
                                        <?php if ($activeYear): ?>
                                            <input type="hidden" name="year" value="<?php echo $activeYear; ?>">
                                        <?php endif; ?>
                                        <?php if ($activeOrderStatus): ?>
                                            <input type="hidden" name="order_status" value="<?php echo htmlspecialchars($activeOrderStatus); ?>">
                                        <?php endif; ?>
                                        <?php if ($activePaymentStatus): ?>
                                            <input type="hidden" name="payment_status" value="<?php echo htmlspecialchars($activePaymentStatus); ?>">
                                        <?php endif; ?>
                                        <?php if ($searchTerm): ?>
                                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-success">Download Data <i class="fa fa-download fa-sm"></i></button>
                                    </form>
                                </div>
                            </div>

                            <!-- Filters and Search Container -->
                            <div class="box-body">
                                <div class="filter-container">
                                    <form method="GET" action="view-order.php">
                                        <!-- Search Box -->
                                        <div class="search-container">
                                            <div class="form-group">
                                                <label for="search">Search Orders:</label>
                                                <input type="text" name="search" id="search" class="form-control" 
                                                       placeholder="Search by Name, Order ID, Coupon Code, or Batch Code..." 
                                                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                                            </div>
                                        </div>

                                        <!-- Filter Row -->
                                        <div class="form-row">
                                            <div class="form-group-inline">
                                                <label for="month">Month:</label>
                                                <select name="month" id="month" class="form-control">
                                                    <option value="">All Months</option>
                                                    <?php
                                                    $monthNames = [
                                                        1 => "January", 2 => "February", 3 => "March", 4 => "April",
                                                        5 => "May", 6 => "June", 7 => "July", 8 => "August",
                                                        9 => "September", 10 => "October", 11 => "November", 12 => "December"
                                                    ];
                                                    foreach ($monthNames as $num => $name): ?>
                                                        <option value="<?= $num ?>" <?= ($num == $activeMonth) ? 'selected' : '' ?>>
                                                            <?= $name ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group-inline">
                                                <label for="year">Year:</label>
                                                <select name="year" id="year" class="form-control">
                                                    <option value="">All Years</option>
                                                    <?php
                                                    $currentYear = date('Y');
                                                    for ($y = $currentYear; $y >= $currentYear - 10; $y--): ?>
                                                        <option value="<?= $y ?>" <?= ($y == $activeYear) ? 'selected' : '' ?>>
                                                            <?= $y ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>

                                            <div class="form-group-inline">
                                                <label for="order_status">Order Status:</label>
                                                <select name="order_status" id="order_status" class="form-control">
                                                    <option value="">All Order Status</option>
                                                    <option value="pending" <?= ($activeOrderStatus == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                    <option value="confirmed" <?= ($activeOrderStatus == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="failed" <?= ($activeOrderStatus == 'failed') ? 'selected' : '' ?>>Failed</option>
                                                </select>
                                            </div>

                                            <div class="form-group-inline">
                                                <label for="payment_status">Payment Status:</label>
                                                <select name="payment_status" id="payment_status" class="form-control">
                                                    <option value="">All Payment Status</option>
                                                    <option value="pending" <?= ($activePaymentStatus == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                    <option value="completed" <?= ($activePaymentStatus == 'completed') ? 'selected' : '' ?>>Completed</option>
                                                    <option value="failed" <?= ($activePaymentStatus == 'failed') ? 'selected' : '' ?>>Failed</option>
                                                </select>
                                            </div>

                                            <div class="btn-group-inline">
                                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                                <a href="view-order.php" class="btn btn-warning">Reset All</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table id="example1" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>SR.NO</th>
                                                <th>User Name</th>
                                                <th>Total Amount</th>
                                                <th>Paid Amount (After Discount)</th>
                                                <th>Order Status</th>
                                                <th>Payment Status</th>
                                                <th>Coupon Code</th>
                                                <th>Batch Code</th>
                                                <th>Discount%</th>
                                                <th>Created Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            while ($info_item = mysqli_fetch_object($result_item)) { 
                                                // Calculate the actual paid amount after discount
                                                $discount_amount = ($info_item->total_amount * $info_item->discount_percent) / 100;
                                                $actual_paid_amount = $info_item->total_amount - $discount_amount;
                                                ?>
                                                <tr>
                                                    <td><?php echo $count++; ?></td>
                                                    <td><?php echo htmlspecialchars($info_item->username); ?></td>
                                                    <td>₹<?php echo number_format($info_item->total_amount, 2); ?></td>
                                                    <td>₹<?php echo number_format($actual_paid_amount, 2); ?></td>
                                                    <td>
                                                        <span class="form-control-plaintext" style="display: inline-block; width: auto;">
                                                            <?= ucfirst($info_item->order_status) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="form-control-plaintext" style="display: inline-block; width: auto;">
                                                            <?= ucfirst($info_item->payment_status) ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($info_item->couponcode); ?></td>
                                                    <td>-</td>
                                                    <td><?php echo $info_item->discount_percent; ?>%</td>
                                                    <td><?php echo date('d-m-y H:i', strtotime($info_item->created_at)); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include('include/footer.php'); ?>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>

    <script>
    $(function() {
        $('#example1').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": false, // Disable DataTable's built-in search since we have custom search
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 25
        });
    });
    </script>

    <script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
    <script src="bower_components/fastclick/lib/fastclick.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="dist/js/demo.js"></script>

</body>
</html>
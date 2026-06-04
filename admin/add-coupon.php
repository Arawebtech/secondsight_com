<?php
// generate-coupon.php - Fixed and Enhanced version
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include database configuration
include('include/db_config.php');

// Initialize variables to avoid undefined errors
$success_message = '';
$error_message = '';

// Immediately check for a valid database connection.
if (!$conn) {
    $error_message = "Database connection failed. Please check your configuration and ensure the database server is running.";
} else {
    // SELF-HEALING DB FIX FOR 'type', 'course_id', and 'applicable_to_all' COLUMNS
    // Also ensuring 'discount' can handle large flat values (e.g. ₹1000+)
    $checkDiscountRes = $conn->query("SHOW COLUMNS FROM coupon LIKE 'discount'");
    if ($checkDiscountRes && $row = $checkDiscountRes->fetch_assoc()) {
        if (strpos(strtolower($row['Type']), 'decimal(5,2)') !== false) {
            $conn->query("ALTER TABLE coupon MODIFY COLUMN discount DECIMAL(10,2) NOT NULL");
        }
    }

    $checkType = $conn->query("SHOW COLUMNS FROM coupon LIKE 'type'");
    if ($checkType && $checkType->num_rows == 0) {
        $conn->query("ALTER TABLE coupon ADD COLUMN type VARCHAR(50) DEFAULT 'percent' AFTER discount");
    }
    
    $checkCourse = $conn->query("SHOW COLUMNS FROM coupon LIKE 'course_id'");
    if ($checkCourse && $checkCourse->num_rows == 0) {
        $conn->query("ALTER TABLE coupon ADD COLUMN course_id INT NULL AFTER type");
        $conn->query("ALTER TABLE coupon ADD COLUMN applicable_to_all TINYINT(1) DEFAULT 1 AFTER course_id");
    }
}

// Check if user is logged in
if (empty($_SESSION['name'])) {
    header('Location: index.php');
    exit;
}

// ===== HELPER FUNCTIONS =====

// Function to check if coupon code already exists
function couponExists($code, $conn) {
    if (!$conn) return true;
    $stmt = $conn->prepare("SELECT id FROM coupon WHERE code = ?");
    if (!$stmt) return true;
    
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to validate coupon input
function validateCouponInput($discount, $nooftimes, $expiryDate, $type) {
    $errors = [];
    if (empty($discount) || !is_numeric($discount) || $discount <= 0) {
        $errors[] = "Discount must be a number greater than 0.";
    }
    if ($type === 'percent' && $discount > 100) {
        $errors[] = "Percentage discount cannot exceed 100%.";
    }
    if (empty($nooftimes) || !is_numeric($nooftimes) || $nooftimes <= 0) {
        $errors[] = "Usage limit must be a number greater than 0.";
    }
    if (empty($expiryDate) || strtotime($expiryDate) <= time()) {
        $errors[] = "The expiry date must be set to a future date.";
    }
    return $errors;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    $couponCode = isset($_POST['coupon_code']) ? strtoupper(trim(htmlspecialchars($_POST['coupon_code']))) : '';
    $discount = isset($_POST['discount']) ? round(floatval($_POST['discount']), 2) : 0;
    $type = isset($_POST['type']) ? htmlspecialchars($_POST['type']) : 'percent';
    $nooftimes = isset($_POST['no_times']) ? intval($_POST['no_times']) : 0;
    $expiryDate = isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '';
    $courseId = isset($_POST['course_id']) && !empty($_POST['course_id']) ? intval($_POST['course_id']) : NULL;
    $applicableToAll = ($courseId === NULL || $courseId === 0) ? 1 : 0;

    $validationErrors = validateCouponInput($discount, $nooftimes, $expiryDate, $type);
    
    if (empty($couponCode)) {
        $validationErrors[] = "Coupon code is required.";
    } elseif (couponExists($couponCode, $conn)) {
        $validationErrors[] = "This coupon code already exists.";
    }
    
    if (!empty($validationErrors)) {
        $error_message = implode("<br>", $validationErrors);
    } else {
        $stmt = $conn->prepare("INSERT INTO coupon (code, discount, type, course_id, applicable_to_all, no_of_times, used_count, expiry_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'active', NOW())");
        
        if (!$stmt) {
            $error_message = "Database prepare error: " . $conn->error;
        } else {
            $stmt->bind_param("sdsiiis", $couponCode, $discount, $type, $courseId, $applicableToAll, $nooftimes, $expiryDate);
            
            if ($stmt->execute()) {
                header("Location: view-coupon.php?status=added&code=" . urlencode($couponCode));
                exit;
            } else {
                $error_message = "Error creating coupon: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Function to get coupon statistics
function getCouponStats($conn) {
    $stats = ['total' => 0, 'active' => 0, 'expired' => 0];
    if (!$conn) return $stats;
    
    $result_total = $conn->query("SELECT COUNT(*) as total FROM coupon");
    if ($result_total) $stats['total'] = $result_total->fetch_assoc()['total'];
    
    $result_active = $conn->query("SELECT COUNT(*) as active FROM coupon WHERE status = 'active' AND used_count < no_of_times AND expiry_date >= CURDATE()");
    if ($result_active) $stats['active'] = $result_active->fetch_assoc()['active'];
    
    $result_expired = $conn->query("SELECT COUNT(*) as expired FROM coupon WHERE status = 'expired' OR used_count >= no_of_times OR expiry_date < CURDATE()");
    if ($result_expired) $stats['expired'] = $result_expired->fetch_assoc()['expired'];
    
    return $stats;
}

// Get base URL
$base_url = isset($base_url) ? $base_url : './';

// Fetch all active courses for dropdown
$courses_list = [];
if ($conn) {
    $courses_query = "SELECT id, s_name FROM courses WHERE status = 'Active' ORDER BY s_name ASC";
    $courses_result = $conn->query($courses_query);
    if ($courses_result) {
        while ($course_row = $courses_result->fetch_assoc()) {
            $courses_list[] = $course_row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Add Coupon Code</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="icon" href="<?= htmlspecialchars($base_url); ?>assets/img/logo-fav.png" type="image/png">
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <style>
        .coupon-preview { background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; text-align: center; margin: 20px 0; border: 2px dashed #fff; position: relative; overflow: hidden; }
        .coupon-preview::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,255,255,0.1) 10px, rgba(255,255,255,0.1) 20px); animation: shine 3s linear infinite; pointer-events: none; }
        @keyframes shine { 0% { transform: translateX(-100%) translateY(-100%); } 100% { transform: translateX(100%) translateY(100%); } }
        .coupon-code { font-size: 28px; font-weight: bold; letter-spacing: 3px; margin: 15px 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); position: relative; z-index: 1; }
        .coupon-discount { font-size: 24px; margin-bottom: 10px; position: relative; z-index: 1; }
        .coupon-info { font-size: 13px; opacity: 0.9; position: relative; z-index: 1; }
        .stats-card { background: #fff; padding: 20px; border-radius: 8px; margin: 10px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .stats-card:hover { transform: translateY(-5px); }
        .form-group label { font-weight: 600; color: #333; margin-bottom: 8px; }
        .btn-success { background: linear-gradient(45deg, #28a745, #20c997); border: none; color: white; padding: 12px 25px; font-weight: bold; border-radius: 6px; transition: all 0.3s ease; }
        .btn-success:hover { background: linear-gradient(45deg, #218838, #1ca085); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php 
        if (file_exists('include/header.php')) include('include/header.php'); 
        if (file_exists('include/side-bar.php')) include('include/side-bar.php'); 
        ?>

        <div class="content-wrapper">
            <section class="content-header" style="margin-top:35px;padding-left:28px">
                <h2><i class="fa fa-plus"></i> Add New Coupon Code</h2>
            </section>

            <section class="content">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <strong><i class="icon fa fa-ban"></i> Error:</strong><br><?= $error_message ?>
                    </div>
                <?php endif; ?>

                <?php $stats = getCouponStats($conn); ?>
                <div class="row">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h4 style="margin: 0; color: #007bff;"><i class="fa fa-ticket"></i> Total Coupons: <?= $stats['total'] ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h4 style="margin: 0; color: #28a745;"><i class="fa fa-check-circle"></i> Active: <?= $stats['active'] ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h4 style="margin: 0; color: #dc3545;"><i class="fa fa-times-circle"></i> Expired: <?= $stats['expired'] ?></h4>
                        </div>
                    </div>
                </div>

                <div class="box box-primary" style="margin-top: 20px;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Coupon Details</h3>
                    </div>
                    
                    <form method="post" id="couponForm" action="add-coupon.php">
                        <div class="box-body">
                            
                            <div class="row">
                                <div class="col-md-4 col-md-offset-4">
                                    <div class="coupon-preview" id="couponPreview" style="display: none;">
                                        <div class="coupon-discount">Save <span id="previewSymbolPrefix"></span><span id="previewDiscount">0</span><span id="previewSymbolSuffix"></span> Off</div>
                                        <div class="coupon-code" id="previewCode">SAMPLE20</div>
                                        <div class="coupon-info">
                                            <p><i class="fa fa-calendar"></i> Expires: <span id="previewExpiry">--</span></p>
                                            <p><i class="fa fa-refresh"></i> Limit: <span id="previewUsage">--</span> uses</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="coupon_code"><i class="fa fa-ticket"></i> Coupon Code: <span style="color: red;">*</span></label>
                                        <input type="text" name="coupon_code" id="coupon_code" class="form-control" placeholder="e.g., NEWYEAR50" required style="text-transform: uppercase;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="type"><i class="fa fa-money"></i> Discount Type: <span style="color: red;">*</span></label>
                                        <select name="type" id="type" class="form-control" required>
                                            <option value="percent">Percentage (%)</option>
                                            <option value="flat">Flat Amount (₹)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="discount"><i class="fa fa-percent"></i> Discount Value: <span style="color: red;">*</span></label>
                                        <input type="number" name="discount" id="discount" class="form-control" placeholder="e.g., 15" required min="1" step="0.01">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row" style="margin-top: 15px;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="notimes"><i class="fa fa-users"></i> Usage Limit: <span style="color: red;">*</span></label>
                                        <input type="number" name="no_times" id="notimes" class="form-control" placeholder="e.g., 100" required min="1" value="100">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date"><i class="fa fa-calendar-check-o"></i> Expiry Date: <span style="color: red;">*</span></label>
                                        <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="course_id"><i class="fa fa-book"></i> Applicable To:</label>
                                        <select name="course_id" id="course_id" class="form-control">
                                            <option value="0" selected>All Courses (Universal)</option>
                                            <?php foreach ($courses_list as $course): ?>
                                                <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['s_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        <div class="box-footer">
                            <a href="view-coupon.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to List</a>
                            <!--<button type="button" id="previewBtn" class="btn btn-info"><i class="fa fa-eye"></i> Show Preview</button>-->
                            <button type="submit" class="btn btn-success pull-right"><i class="fa fa-save"></i> Save Coupon</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>

        <?php include('include/footer.php'); ?>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const expiryInput = document.getElementById('expiry_date');
        const today = new Date().toISOString().split('T')[0];
        expiryInput.min = today;

        function updatePreview() {
            const code = document.getElementById('coupon_code').value;
            const discount = document.getElementById('discount').value;
            const type = document.getElementById('type').value;
            const expiry = document.getElementById('expiry_date').value;
            const usage = document.getElementById('notimes').value;
            
            if (!code || !discount) {
                document.getElementById('couponPreview').style.display = 'none';
                return;
            }
            
            document.getElementById('previewCode').textContent = code.toUpperCase();
            document.getElementById('previewDiscount').textContent = discount;
            
            if (type === 'flat') {
                document.getElementById('previewSymbolPrefix').textContent = '₹';
                document.getElementById('previewSymbolSuffix').textContent = '';
            } else {
                document.getElementById('previewSymbolPrefix').textContent = '';
                document.getElementById('previewSymbolSuffix').textContent = '%';
            }
            
            document.getElementById('previewExpiry').textContent = expiry || '---';
            document.getElementById('previewUsage').textContent = usage || '---';
            document.getElementById('couponPreview').style.display = 'block';
        }

        document.getElementById('previewBtn').addEventListener('click', updatePreview);
        ['coupon_code', 'discount', 'type', 'expiry_date', 'notimes'].forEach(id => {
            document.getElementById(id).addEventListener('input', updatePreview);
        });
        
        document.getElementById('couponForm').addEventListener('submit', function(e) {
            const discount = parseFloat(document.getElementById('discount').value);
            const type = document.getElementById('type').value;
            const usage = parseInt(document.getElementById('notimes').value);
            const expiry = document.getElementById('expiry_date').value;
            const code = document.getElementById('coupon_code').value;
            
            let errs = [];
            if (!code) errs.push('Coupon code is required');
            if (isNaN(discount) || discount <= 0) errs.push('Discount must be > 0');
            if (type === 'percent' && discount > 100) errs.push('Percentage cannot exceed 100');
            if (isNaN(usage) || usage <= 0) errs.push('Usage limit must be > 0');
            if (!expiry) errs.push('Expiry date is required');
            
            if (errs.length > 0) {
                alert('Errors:\n' + errs.join('\n'));
                e.preventDefault();
                return;
            }
            
            const btn = this.querySelector('.btn-success');
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
            btn.disabled = true;
        });
    });
    </script>
</body>
</html>
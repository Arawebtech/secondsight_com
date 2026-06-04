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

// Immediately check for a valid database connection. This is a critical first step.
if (!$conn) {
    // Set a user-friendly error message that will be displayed in the HTML body.
    $error_message = "Database connection failed. Please check your configuration and ensure the database server is running.";
}

// Check if user is logged in
if (empty($_SESSION['name'])) {
    header('Location: index.php');
    exit;
}

// ===== HELPER FUNCTIONS =====

// Enhanced function to generate memorable coupon codes
function generateShortCoupon($discount, $type = 'random', $attempt = 0) {
    $adjectives = ['SUPER', 'MEGA', 'SMART', 'QUICK', 'BEST', 'TOP', 'HOT', 'NEW', 'BIG', 'WIN', 'COOL', 'FAST', 'GOLD', 'STAR', 'EPIC', 'RUSH', 'FIRE', 'BOLT', 'JUMP', 'ZOOM'];
    $nouns = ['DEAL', 'SAVE', 'SALE', 'GIFT', 'PLUS', 'ZONE', 'HUB', 'CLUB', 'VIP', 'PRO', 'MAX', 'NOW', 'GO', 'FUN', 'WIN', 'GET', 'USE', 'BUY', 'TRY', 'JOY'];
    
    $suffix = $attempt > 0 ? $attempt : '';
    
    switch($type) {
        case 'word':
            $word = $adjectives[array_rand($adjectives)];
            if ($attempt > 0) $word .= rand(10, 99);
            return $word . $discount . $suffix;
        case 'combo':
            $word = $adjectives[array_rand($adjectives)];
            $ending = $nouns[array_rand($nouns)];
            $shortEnding = substr($ending, 0, 3);
            if ($attempt > 0) $shortEnding .= rand(1, 9);
            return $word . $discount . $shortEnding . $suffix;
        case 'simple':
            $prefixes = ['OFF', 'GET', 'BUY', 'USE', 'GO', 'TRY', 'WIN', 'NEW', 'HOT', 'TOP'];
            $prefix = $prefixes[array_rand($prefixes)];
            if ($attempt > 0) $prefix .= rand(1, 9);
            return $prefix . $discount . $suffix;
        case 'random':
        default:
            $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $code = '';
            $letterCount = $attempt > 0 ? 4 : 3;
            for ($i = 0; $i < $letterCount; $i++) {
                $code .= $letters[rand(0, 25)];
            }
            return $code . $discount . $suffix;
    }
}

// Function to check if coupon code already exists
function couponExists($code, $conn) {
    // Gracefully handle a null connection
    if (!$conn) return true; // Assume it exists to prevent errors
    $stmt = $conn->prepare("SELECT id FROM coupon WHERE code = ?");
    if (!$stmt) return true; // Assume it exists on prepare failure
    
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Function to generate a unique coupon code with multiple fallbacks
function generateUniqueCoupon($discount, $type, $conn, $maxAttempts = 50) {
    $attempts = 0;
    $baseAttempt = 0;
    
    do {
        $couponCode = generateShortCoupon($discount, $type, $baseAttempt);
        $attempts++;
        
        if (couponExists($couponCode, $conn)) {
            $baseAttempt++;
            if ($attempts > 30) { // Add timestamp if collisions persist
                $couponCode = generateShortCoupon($discount, $type, 0) . substr(time(), -3);
            }
            if ($attempts > 40) { // Add hash if collisions are still happening
                $couponCode = generateShortCoupon($discount, $type, 0) . strtoupper(substr(md5(uniqid()), 0, 3));
            }
        }
    } while (couponExists($couponCode, $conn) && $attempts < $maxAttempts);
    
    // Final fallback to guarantee uniqueness
    if (couponExists($couponCode, $conn)) {
        $couponCode = 'UNIQUE' . $discount . strtoupper(substr(uniqid(), -4));
    }
    
    return $couponCode;
}

// Function to validate coupon input from the form
function validateCouponInput($discount, $nooftimes, $expiryDate) {
    $errors = [];
    if (empty($discount) || !is_numeric($discount) || $discount <= 0 || $discount > 100) {
        $errors[] = "Discount must be a number between 1 and 100.";
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
// **FIXED**: Removed isset($_POST['submit']) because the disabled button is not sent.
// This is now the correct, robust way to check for a form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $conn) {
    // Sanitize and validate all inputs
    $discount = isset($_POST['discount']) ? round(floatval($_POST['discount']), 2) : 0;
    $nooftimes = isset($_POST['no_times']) ? intval($_POST['no_times']) : 0;
    $couponType = isset($_POST['coupon_type']) ? htmlspecialchars($_POST['coupon_type']) : '';
    $expiryDate = isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : '';
    $courseId = isset($_POST['course_id']) && !empty($_POST['course_id']) ? intval($_POST['course_id']) : NULL;
    $applicableToAll = ($courseId === NULL || $courseId === 0) ? 1 : 0;

    // Validate processed input
    $validationErrors = validateCouponInput($discount, $nooftimes, $expiryDate);
    
    if (!empty($validationErrors)) {
        // If validation fails, combine errors into one message to be displayed in the HTML.
        $error_message = implode("<br>", $validationErrors);
    } else {
        // All checks passed, proceed to generate coupon and insert into DB
        $couponCode = generateUniqueCoupon($discount, $couponType, $conn);
        
        $stmt = $conn->prepare("INSERT INTO coupon (code, discount, course_id, applicable_to_all, no_of_times, remaining_uses, expiry_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
        
        if (!$stmt) {
            $error_message = "Database prepare error: " . $conn->error;
        } else {
            // Bind parameters ('d' for double, 'i' for integer, 's' for string)
            $stmt->bind_param("sdiiiis", $couponCode, $discount, $courseId, $applicableToAll, $nooftimes, $nooftimes, $expiryDate);
            
            if ($stmt->execute()) {
                // SUCCESS: Redirect to the view page with a success flag and the new code.
                // This is cleaner than using JavaScript alerts and prevents re-submission on refresh.
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
    
    $result_active = $conn->query("SELECT COUNT(*) as active FROM coupon WHERE status = 'active' AND remaining_uses > 0 AND expiry_date >= CURDATE()");
    if ($result_active) $stats['active'] = $result_active->fetch_assoc()['active'];
    
    $result_expired = $conn->query("SELECT COUNT(*) as expired FROM coupon WHERE status = 'expired' OR remaining_uses = 0 OR expiry_date < CURDATE()");
    if ($result_expired) $stats['expired'] = $result_expired->fetch_assoc()['expired'];
    
    return $stats;
}

// Get base URL (fix undefined variable)
$base_url = isset($base_url) ? $base_url : './';

// Fetch all active courses for dropdown
$courses_list = [];
if ($conn) {
    $courses_query = "SELECT id, s_name, s_title FROM courses WHERE status = 'Active' ORDER BY s_name ASC";
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
    <title>Generate Unique Coupon</title>
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
        .coupon-discount { font-size: 20px; margin-bottom: 10px; position: relative; z-index: 1; }
        .coupon-info { font-size: 12px; opacity: 0.9; position: relative; z-index: 1; }
        .code-type-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff; }
        .example-codes { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .example-code { background: #007bff; color: white; padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; transition: all 0.3s ease; }
        .example-code:hover { background: #0056b3; transform: scale(1.05); }
        .stats-card { background: #fff; padding: 15px; border-radius: 5px; margin: 10px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .uniqueness-indicator { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 15px 0; border: 1px solid #c3e6cb; }
        .form-group label { font-weight: 600; color: #333; }
        .btn-generate { background: linear-gradient(45deg, #28a745, #20c997); border: none; color: white; padding: 10px 20px; font-weight: bold; transition: all 0.3s ease; }
        .btn-generate:hover { background: linear-gradient(45deg, #218838, #1ca085); transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        /* Use Bootstrap's alert classes for consistency */
        .alert-danger { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }

        /* New CSS for collapsible content */
        .collapsible-content {
            display: none; /* Hidden by default */
            margin-top: 15px;
        }
        .toggle-details-btn {
            margin-top: 15px;
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }
        .toggle-details-btn:hover {
            color: #0056b3;
        }
    </style>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">
        <?php 
        // Include header and sidebar
        if (file_exists('include/header.php')) include('include/header.php'); 
        if (file_exists('include/side-bar.php')) include('include/side-bar.php'); 
        ?>

        <div class="content-wrapper" style="margin-top:35px;padding-left:28px">
            <section class="content">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <strong>Error:</strong><br><?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success">
                        <strong>Success!</strong><br><?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>

                <?php $stats = getCouponStats($conn); ?>
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-4"><div class="stats-card"><h4 style="margin: 0; color: #007bff;"><i class="fa fa-ticket"></i> Total Coupons: <?= $stats['total'] ?></h4></div></div>
                    <div class="col-md-4"><div class="stats-card"><h4 style="margin: 0; color: #28a745;"><i class="fa fa-check-circle"></i> Active: <?= $stats['active'] ?></h4></div></div>
                    <div class="col-md-4"><div class="stats-card"><h4 style="margin: 0; color: #dc3545;"><i class="fa fa-times-circle"></i> Expired: <?= $stats['expired'] ?></h4></div></div>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-magic"></i> Generate Unique Coupon Code</h3>
                    </div>
                    
                    <form method="post" id="couponForm" action="add-coupon.php">
                        <div class="box-body">
                            <div class="uniqueness-indicator">
                                <strong><i class="fa fa-shield"></i> Uniqueness Guaranteed:</strong> Our system ensures every coupon code is unique using multiple validation and fallback layers.
                            </div>
                            
                            <div class="coupon-preview" id="couponPreview" style="display: none;">
                                <div class="coupon-discount">Save <span id="previewDiscount">0</span>%</div>
                                <div class="coupon-code" id="previewCode">SAMPLE20</div>
                                <div class="coupon-info">
                                    <small>Valid until: <span id="previewExpiry">--</span></small><br>
                                    <small>Usage limit: <span id="previewUsage">--</span> times</small>
                                </div>
                            </div>
                            
                            <div style="display:flex; flex-wrap:wrap; gap:25px;">
                                <div class="form-group" style="min-width: 250px; flex: 1;">
                                    <label for="discount"><i class="fa fa-percent"></i> Discount Percentage: <span style="color: red;">*</span></label>
                                    <input type="number" name="discount" id="discount" class="form-control" placeholder="e.g., 15.5" required min="1" max="100" step="0.01">
                                </div>

                                <div class="form-group" style="min-width: 250px; flex: 1;">
                                    <label for="coupon_type"><i class="fa fa-tags"></i> Code Style: <span style="color: red;">*</span></label>
                                    <select name="coupon_type" id="coupon_type" class="form-control" required>
                                        <option value="" disabled selected>-- Select a Style --</option>
                                        <option value="simple">Simple (e.g., OFF20)</option>
                                        <option value="word">Word-based (e.g., SUPER20)</option>
                                        <option value="combo">Combo (e.g., SAVE20NOW)</option>
                                        <option value="random">Random Letters (e.g., XQZ20)</option>
                                    </select>
                                </div>

                                <div class="form-group" style="min-width: 250px; flex: 1;">
                                    <label for="notimes"><i class="fa fa-refresh"></i> Usage Limit: <span style="color: red;">*</span></label>
                                    <input type="number" name="no_times" id="notimes" class="form-control" placeholder="e.g., 100" required min="1" value="100">
                                </div>
                            </div>
                            
                            <div style="display:flex; flex-wrap:wrap; gap:25px; margin-top:20px;">
                                <div class="form-group" style="min-width: 250px; flex: 1;">
                                    <label for="expiry_date"><i class="fa fa-calendar"></i> Expiry Date: <span style="color: red;">*</span></label>
                                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" required>
                                </div>
                                
                                <div class="form-group" style="min-width: 250px; flex: 1;">
                                    <label for="course_id"><i class="fa fa-book"></i> Applicable To:</label>
                                    <select name="course_id" id="course_id" class="form-control">
                                        <option value="0" selected>All Courses (No Restriction)</option>
                                        <?php foreach ($courses_list as $course): ?>
                                            <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['s_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">Select a specific course or leave as "All Courses" for universal coupon</small>
                                </div>
                            </div>
                            
                            <div class="toggle-details-btn" id="toggleDetailsBtn">
                                <i class="fa fa-plus-circle"></i> See More About Coupon Generation
                            </div>

                            <div class="collapsible-content" id="moreInfoContent">
                                <div class="code-type-info">
                                    <h5><i class="fa fa-info-circle"></i> Coupon Code Styles & Uniqueness</h5>
                                    <div style="margin-top: 15px;">
                                        <strong>Simple:</strong> Short & memorable <div class="example-codes"><span class="example-code">OFF20</span><span class="example-code">GET15</span></div>
                                        <br><strong>Word-based:</strong> Marketing words <div class="example-codes"><span class="example-code">SUPER20</span><span class="example-code">MEGA15</span></div>
                                        <br><strong>Combo:</strong> Catchy combos <div class="example-codes"><span class="example-code">SAVE20NOW</span><span class="example-code">WIN15BIG</span></div>
                                        <br><strong>Random:</strong> Unique letters <div class="example-codes"><span class="example-code">ABC20</span><span class="example-code">XYZ15</span></div>
                                    </div>
                                    <div style="margin-top: 15px; padding: 10px; background: #e7f3ff; border-radius: 5px;">
                                        <strong><i class="fa fa-shield"></i> Uniqueness Features:</strong>
                                        <ul style="margin: 10px 0 0 20px; padding-left: 10px;">
                                            <li>Real-time database validation checks.</li>
                                            <li>Automatic retries with variations if a code exists.</li>
                                            <li>Timestamp and hash-based fallbacks for high-conflict scenarios.</li>
                                            <li>Final unique ID safety net to guarantee success.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="box-footer">
                            <a href="view-coupon.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a>
                            <button type="button" id="previewBtn" class="btn btn-info"><i class="fa fa-eye"></i> Preview</button>
                            <button type="submit" name="submit" value="generate" class="btn btn-generate pull-right"><i class="fa fa-magic"></i> Generate Coupon</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set minimum expiry date to today
        document.getElementById('expiry_date').min = new Date().toISOString().split('T')[0];

        function updatePreview() {
            const discount = document.getElementById('discount').value;
            const type = document.getElementById('coupon_type').value;
            const expiry = document.getElementById('expiry_date').value;
            const usage = document.getElementById('notimes').value;
            
            if (!discount || !type) {
                // Keep preview hidden if essential fields are empty
                document.getElementById('couponPreview').style.display = 'none';
                return;
            }
            
            let previewCode = '';
            const discountVal = discount || 'XX';
            switch(type) {
                case 'simple': previewCode = 'OFF' + discountVal; break;
                case 'word': previewCode = 'SUPER' + discountVal; break;
                case 'combo': previewCode = 'SAVE' + discountVal + 'NOW'; break;
                case 'random': default: previewCode = 'XYZ' + discountVal; break;
            }
            
            document.getElementById('previewCode').textContent = previewCode;
            document.getElementById('previewDiscount').textContent = discount;
            document.getElementById('previewExpiry').textContent = expiry || 'Not set';
            document.getElementById('previewUsage').textContent = usage || 'Not set';
            document.getElementById('couponPreview').style.display = 'block';
        }

        document.getElementById('previewBtn').addEventListener('click', updatePreview);
        
        // Add change listeners to auto-update the preview
        ['discount', 'coupon_type', 'expiry_date', 'notimes'].forEach(function(fieldId) {
            document.getElementById(fieldId).addEventListener('input', updatePreview);
        });
        
        // Client-side validation before submitting
        document.getElementById('couponForm').addEventListener('submit', function(e) {
            const discount = parseFloat(document.getElementById('discount').value);
            const usage = parseInt(document.getElementById('notimes').value);
            const expiry = document.getElementById('expiry_date').value;
            let errors = [];

            if (isNaN(discount) || discount <= 0 || discount > 100) {
                errors.push('Discount must be between 1 and 100.');
            }
            if (isNaN(usage) || usage <= 0) {
                errors.push('Usage limit must be greater than 0.');
            }
            if (!expiry || new Date(expiry) < new Date().setHours(0,0,0,0)) {
                errors.push('Expiry date must be today or in the future.');
            }
            
            if (errors.length > 0) {
                alert('Please fix the following errors:\n\n- ' + errors.join('\n- '));
                e.preventDefault(); // Stop form submission
                return;
            }
            
            // Show a loading state on the submit button
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';
            submitBtn.disabled = true;
        });

        // JavaScript for toggling the "More Info" section
        const toggleBtn = document.getElementById('toggleDetailsBtn');
        const moreInfoContent = document.getElementById('moreInfoContent');

        toggleBtn.addEventListener('click', function() {
            if (moreInfoContent.style.display === 'none' || moreInfoContent.style.display === '') {
                moreInfoContent.style.display = 'block';
                toggleBtn.innerHTML = '<i class="fa fa-minus-circle"></i> Hide Details About Coupon Generation';
            } else {
                moreInfoContent.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fa fa-plus-circle"></i> See More About Coupon Generation';
            }
        });
    });
    </script>
</body>
</html>
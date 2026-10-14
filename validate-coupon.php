<?php
header('Content-Type: application/json'); 
include('admin/include/db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['coupon'])) {
    $coupon = htmlspecialchars(trim($_POST['coupon'])); 
    $current_date = date("Y-m-d");

    // Query to check coupon (case-insensitive and trimmed)
    $stmt = $conn->prepare("SELECT id, code, discount, type, expiry_date, no_of_times, used_count, course_id, applicable_to_all, status FROM coupon WHERE UPPER(TRIM(code)) = UPPER(TRIM(?))");
    $stmt->bind_param("s", $coupon);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 1. Check if coupon is expired by date, reached usage limit, or inactive
        $is_expired_date = (strtotime($row['expiry_date']) < strtotime($current_date));
        $is_limit_reached = ($row['used_count'] >= $row['no_of_times']);
        $is_inactive_status = ($row['status'] === 'expired' || $row['status'] === 'disabled');

        if ($is_expired_date || $is_limit_reached || $is_inactive_status) {
            // Ensure status is marked as expired in database if limit was reached or date passed
            if ($row['status'] === 'active' && ($is_expired_date || $is_limit_reached)) {
                $conn->query("UPDATE coupon SET status = 'expired' WHERE id = " . intval($row['id']));
            }
            echo json_encode(["status" => "error", "message" => "Coupon Expired or Usage Limit Reached!"]);
            exit;
        }

        // 2. Extract cart courses sent via AJAX
        $cart_courses = isset($_POST['course_ids']) ? json_decode($_POST['course_ids'], true) : [];
        if (!is_array($cart_courses)) $cart_courses = [];

        $is_applicable = false;
        if ($row['applicable_to_all'] == 1 || empty($row['course_id'])) {
            $is_applicable = true;
        } else if (in_array($row['course_id'], $cart_courses)) {
            $is_applicable = true;
        }

        if (!$is_applicable) {
            echo json_encode(["status" => "error", "message" => "Coupon is not applicable to any course in your cart!"]);
            exit;
        }

        // Valid coupon
        echo json_encode([
            "status" => "success",
            "discount" => $row['discount'],
            "type" => $row['type'],
            "couponcode" => $row['code']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Coupon Code!"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Please enter a coupon code!"]);
}

$conn->close();
?>

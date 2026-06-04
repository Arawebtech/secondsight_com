<?php
header('Content-Type: application/json'); 
include('admin/include/db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['coupon'])) {
       $coupon = htmlspecialchars(trim($_POST['coupon'])); 
    $current_date = date("Y-m-d");

    // Query to check coupon
    $stmt = $conn->prepare("SELECT discount, type, expiry_date, no_of_times, used_count, course_id, applicable_to_all FROM coupon WHERE code = ?");
    $stmt->bind_param("s", $coupon);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Extract cart courses sent via AJAX
        $cart_courses = isset($_POST['course_ids']) ? json_decode($_POST['course_ids'], true) : [];
        if (!is_array($cart_courses)) $cart_courses = [];

        $is_applicable = false;
        if ($row['applicable_to_all'] == 1) {
            $is_applicable = true;
        } else if (in_array($row['course_id'], $cart_courses)) {
            $is_applicable = true;
        }

        if (!$is_applicable) {
            echo json_encode(["status" => "error", "message" => "Coupon is not applicable to any course in your cart!"]);
            exit;
        }

        if ($row['expiry_date'] >= $current_date && $row['no_of_times'] > $row['used_count']) {
            echo json_encode(["status" => "success", "discount" => $row['discount'], "type" => $row['type'], "couponcode" => $coupon ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Coupon Expired or Usage Limit Reached!"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Coupon Code!"]);
    }
    $stmt->close();
}

$conn->close();
?>

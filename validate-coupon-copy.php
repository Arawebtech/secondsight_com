<?php
header('Content-Type: application/json'); 
include('admin/include/db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['coupon'])) {
    $coupon = htmlspecialchars(trim($_POST['coupon'])); 
    $current_date = date("Y-m-d");
    
    // Get course IDs from cart (if provided)
    $cart_course_ids = isset($_POST['course_ids']) ? json_decode($_POST['course_ids'], true) : [];
    if (!is_array($cart_course_ids) && isset($_SESSION['cart'])) {
        $cart_course_ids = $_SESSION['cart'];
    }

    // Query to check coupon with course information
    $stmt = $conn->prepare("SELECT c.discount, c.expiry_date, c.no_of_times, c.used_count, c.course_id, c.applicable_to_all, co.s_name as course_name 
                            FROM coupon c 
                            LEFT JOIN courses co ON c.course_id = co.id 
                            WHERE c.code = ?");
    $stmt->bind_param("s", $coupon);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Check expiry and usage
        if ($row['expiry_date'] >= $current_date && $row['no_of_times'] > $row['used_count']) {
            
            // Check course-specific restriction
            if ($row['applicable_to_all'] == 0 && !empty($row['course_id'])) {
                // This is a course-specific coupon
                if (empty($cart_course_ids)) {
                    echo json_encode([
                        "status" => "error", 
                        "message" => "This coupon is only valid for: " . htmlspecialchars($row['course_name'])
                    ]);
                } elseif (!in_array($row['course_id'], $cart_course_ids)) {
                    echo json_encode([
                        "status" => "error", 
                        "message" => "This coupon is only valid for the course: " . htmlspecialchars($row['course_name'])
                    ]);
                } else {
                    // Coupon is valid for the course in cart
                    echo json_encode([
                        "status" => "success", 
                        "discount" => $row['discount'], 
                        "couponcode" => $coupon,
                        "course_specific" => true,
                        "course_name" => $row['course_name']
                    ]);
                }
            } else {
                // Universal coupon - applicable to all courses
                echo json_encode([
                    "status" => "success", 
                    "discount" => $row['discount'], 
                    "couponcode" => $coupon,
                    "course_specific" => false
                ]);
            }
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

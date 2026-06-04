<?php
function checkCourseAccess($user_id, $course_id, $conn) {
    // Check if user has purchased the course
    $purchase_query = "
        SELECT COUNT(*) as count 
        FROM orders o 
        JOIN order_details od ON o.id = od.order_id 
        WHERE o.user_id = ? AND od.course_id = ? AND o.order_status = 'confirmed'
    ";
    
    $purchase_stmt = $conn->prepare($purchase_query);
    $purchase_stmt->bind_param("ii", $user_id, $course_id);
    $purchase_stmt->execute();
    $purchase_result = $purchase_stmt->get_result();
    $purchase_data = $purchase_result->fetch_assoc();
    
    if ($purchase_data['count'] > 0) {
        return ['access' => true, 'type' => 'purchased'];
    }
    
    // Check if user has batch access through enrollment
    $batch_query = "
        SELECT COUNT(*) as count 
        FROM user_batch_enrollments ube
        JOIN lesson_batch lb ON ube.batch_id = lb.batch_id
        JOIN lesson_video lv ON lb.lesson_id = lv.id
        WHERE ube.user_id = ? AND lv.course_id = ? AND ube.status = 'Active' AND lv.status = 'Active'
    ";
    
    $batch_stmt = $conn->prepare($batch_query);
    $batch_stmt->bind_param("ii", $user_id, $course_id);
    $batch_stmt->execute();
    $batch_result = $batch_stmt->get_result();
    $batch_data = $batch_result->fetch_assoc();
    
    if ($batch_data['count'] > 0) {
        return ['access' => true, 'type' => 'batch'];
    }
    
    return ['access' => false, 'type' => null];
}
?>
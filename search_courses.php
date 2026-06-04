<?php
include('admin/include/db_config.php');

// Define base_url if it's not available. Adjust the path if necessary.
// This is often defined in a central config file.
if (!isset($base_url)) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . $host;
}

$search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : '';
$output = '';

try {
    if (!empty($search_query)) {
        $search_param = "%{$search_query}%";
        $query = "SELECT * FROM courses WHERE status = 'Active' AND (s_name LIKE ? OR description LIKE ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) { throw new Exception("Prepare failed: " . $conn->error); }
        $stmt->bind_param("ss", $search_param, $search_param);
    } else {
        $query = "SELECT * FROM courses WHERE status = 'Active' ORDER BY RAND() LIMIT 9";
        $stmt = $conn->prepare($query);
        if (!$stmt) { throw new Exception("Prepare failed: " . $conn->error); }
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $originalPrice = $row['price'];
            $discountPercentage = isset($row['discount_percentage']) ? $row['discount_percentage'] : 0;
            $discountedPrice = $originalPrice - ($originalPrice * $discountPercentage / 100);
            $bannerImagePath = $base_url . "/assets/img/course-img/" . htmlspecialchars($row['banner_image'] ?? '');
            $courseUrl = $base_url . '/courses/' . htmlspecialchars($row['url'] ?? '');

            $output .= '
            <div class="col-lg-4 col-md-6">
                <div class="single-course shadow">
                    <a href="javascript:void(0);">
                        <img src="' . $bannerImagePath . '" alt="Image">
                    </a>
                    <div class="course-content">
                        <div class="discount-badge">' . htmlspecialchars($discountPercentage) . '% OFF</div>
                        <div class="price-container">
                            <span class="discounted-price">₹ ' . number_format($discountedPrice, 0) . '</span>
                            <span class="original-price">₹ ' . number_format($originalPrice, 0) . '</span>
                        </div>
                        <a href="' . $courseUrl . '"><h3>' . htmlspecialchars($row['s_name'] ?? '') . '</h3></a>
                        <ul class="rating">';
            for ($i = 0; $i < 5; $i++) { $output .= '<li><i class="bx bxs-star"></i></li>'; }
            $output .= '<li><a href="' . $courseUrl . '">5</a></li></ul>
                        <span class="tag">' . substr(trim(htmlspecialchars($row['description'] ?? '')), 0, 180) . '...</span>
                        <div style="margin-top: 15px;" class="btn-course-view">
                            <a href="' . $courseUrl . '" class="default-btn" style="padding: 10px 20px;">Buy Now</a>
                        </div>
                    </div>
                </div>
            </div>';
        }
    } else {
        $output = '<p class="col-12 text-center">No courses found matching your search.</p>';
    }
    $stmt->close();
} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log($e->getMessage());
    $output = '<p class="col-12 text-center">An error occurred while searching. Please try again later.</p>';
}

$conn->close();
echo $output;
?> 
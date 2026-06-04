<?php
session_start();
include('admin/include/db_config.php');

$query = $_GET['query'] ?? '';
$searchResults = [];

if (!empty($query)) {
    // Sanitize input
    $query = mysqli_real_escape_string($conn, $query);

    // Execute a search query for partial matching
    $sql = "SELECT s_name FROM courses WHERE s_name LIKE '%$query%' LIMIT 10";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $searchResults[] = $row;
        }
    }
}

// Return the results in JSON format
echo json_encode($searchResults);
?>

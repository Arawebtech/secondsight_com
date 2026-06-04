<?php
include('include/db_config.php');

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query to fetch students based on the search
$query = "SELECT id, name FROM users WHERE name LIKE '%$search%' OR id LIKE '%$search%'";
$result = mysqli_query($conn, $query);

$options = "<option value='all'>All Students</option>"; // Default option
while ($row = mysqli_fetch_assoc($result)) {
    $options .= "<option value='{$row['id']}'>{$row['name']}</option>";
}

echo $options;
?>

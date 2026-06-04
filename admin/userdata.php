<?php
include("include/db_config.php");

ob_clean();
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query Failed: " . $conn->error);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=users_data_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Column headers
$fields = $result->fetch_fields();
$header = [];
foreach($fields as $field) {
    $header[] = $field->name;
}
fputcsv($output, $header);

// Data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
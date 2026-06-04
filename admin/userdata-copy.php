<?php
// Execute query with error handling
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    exit('Database query failed');
}

if ($result->num_rows === 0) {
    http_response_code(404);
    exit('No data found');
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="data_' . date('Y-m-d_H-i-s') . '.csv"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Use output buffering for better performance
ob_start();
$output = fopen('php://output', 'w');

// Output UTF-8 BOM for proper Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Get column headers efficiently
$firstRow = $result->fetch_assoc();
if ($firstRow) {
    // Output headers
    fputcsv($output, array_keys($firstRow));
    
    // Output first row
    fputcsv($output, $firstRow);
    
    // Output remaining rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

// Clean up resources
fclose($output);
$result->free();
$conn->close();

// Flush output buffer
ob_end_flush();
exit;
?>
<?php
require 'admin/include/db_config.php';
$stmt = $conn->query('SHOW TABLES');
while($row = $stmt->fetch_array()) {
    if (stripos($row[0], 'batch') !== false) {
        echo "Table: " . $row[0] . "\n";
        $cols = $conn->query("SHOW COLUMNS FROM " . $row[0]);
        while($col = $cols->fetch_assoc()) {
            echo "  - " . $col['Field'] . "\n";
        }
    }
}

<?php
require 'admin/include/db_config.php';
$stmt = $conn->query('SHOW COLUMNS FROM user_notes');
while($row = $stmt->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

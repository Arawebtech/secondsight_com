<?php
require 'admin/include/db_config.php';
$stmt = $conn->query('SHOW COLUMNS FROM course_comment');
while($row = $stmt->fetch_assoc()) {
    echo $row['Field'] . "\n";
}

<?php
require 'admin/include/db_config.php';
$result = $conn->query('SELECT * FROM batch LIMIT 1');
print_r($result->fetch_assoc());

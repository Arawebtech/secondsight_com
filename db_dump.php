<?php
include('admin/include/db_config.php');
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $table = $row[0];
    echo "TABLE: $table\n";
    $colRes = $conn->query("DESCRIBE $table");
    while ($col = $colRes->fetch_assoc()) {
        echo " - " . $col['Field'] . "\n";
    }
}
?>

<?php
require 'admin/include/db_config.php';
$conn->query("ALTER TABLE course_comment ADD COLUMN rating INT DEFAULT 0 AFTER comment");
echo "Added rating column.";

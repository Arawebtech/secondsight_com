<?php
require 'admin/include/db_config.php';
$conn->query("ALTER TABLE user_notes ADD COLUMN batch_id INT DEFAULT 0 AFTER course_id");
$conn->query("ALTER TABLE user_notes DROP INDEX user_course_unique");
$conn->query("ALTER TABLE user_notes ADD UNIQUE INDEX user_course_batch_unique (user_id, course_id, batch_id)");

$conn->query("ALTER TABLE course_comment ADD COLUMN batch_id INT DEFAULT 0 AFTER course_id");

echo "Schema updated.";

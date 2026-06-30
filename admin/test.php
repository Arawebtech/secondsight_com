<?php
include('d:/xampp/htdocs/araweb/vps-secondside-com/admin/include/db_config.php');

$insert_sql = "INSERT INTO lesson_video
(course_id, lesson_title, lesson_desc, video_url, video_thumbnail, status, video_alt, meta_keyword, meta_description, batch_id, created_date)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($insert_sql);
if (!$stmt) {
    echo 'Prepare failed: ' . $conn->error;
} else {
    echo 'Prepare succeeded';
}
?>

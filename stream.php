<?php
@ob_end_clean();
set_time_limit(0);
session_start();

$videoBasePath = '/Users/rakshit/Desktop/App-Development/secondsightfoundation.com/admin/';
$tempVideosPath = $videoBasePath . 'temp_videos/';
 print "Output video path: " . $tempVideosPath . "<br>";

$videoFile = $_GET['file'] ?? '';
if (!$videoFile || strpos($videoFile, '..') !== false) {
    http_response_code(400);
    exit('Invalid file');
}

$fullPath = realpath($tempVideosPath . $videoFile);
if (!$fullPath || !file_exists($fullPath)) {
    http_response_code(404);
    exit('File not found');
}

// Serve the file with headers
header('Content-Type: video/mp4');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($fullPath);
exit;

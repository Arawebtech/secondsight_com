<?php
session_start();
include('admin/include/db_config.php');

// Get parameters from query string (matching your URL structure)
$courseUrl = $_GET['course_id'] ?? '0';
$userId = $_GET['user_id'] ?? null;

// Handle missing user ID
if (!$userId) {
    http_response_code(400);
    echo "<h2>User ID is required.</h2>";
    exit;
}

// Fetch user from database using mysqli (same as your existing code)
$query = "SELECT id, email, name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    http_response_code(500);
    echo "<h2>Database error: " . htmlspecialchars(mysqli_error($conn)) . "</h2>";
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    http_response_code(404);
    echo "<h2>User not found.</h2>";
    exit;
}

mysqli_stmt_close($stmt);

$email = $user['email'];
$userName = $user['name'] ?? 'Unknown User';

// Fetch video from course id from database
$query = "SELECT video_url FROM lesson_video WHERE course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseUrl);
$stmt->execute();
$result = $stmt->get_result();
$video = $result->fetch_assoc();
$inputVideo = $video['video_url'] ?? null;

// // File paths
// $inputVideo = "assets/lessonVideo/lesson.mp4";

// Check if input video exists
if (!file_exists($inputVideo)) {
    http_response_code(404);
    echo "<h2>Source video not found.</h2>";
    exit;
}

$sanitizedEmail = preg_replace('/[^a-zA-Z0-9@.]/', '_', $email);
$outputVideo = "assets/lessonVideo/output_{$sanitizedEmail}.mp4";

// Generate watermarked video if not already done
if (!file_exists($outputVideo)) {
    // Ensure output directory exists
    $outputDir = dirname($outputVideo);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    $inputVideoEsc = escapeshellarg($inputVideo);
    $outputVideoEsc = escapeshellarg($outputVideo);
    $emailText = escapeshellarg($email);

    $cmd = "ffmpeg -i $inputVideoEsc -vf \"drawtext=text=$emailText:fontcolor=gray:fontsize=44:x=10:y=H-th-50\" -codec:a copy $outputVideoEsc -y";
    exec($cmd, $output, $returnCode);

    if ($returnCode !== 0) {
        echo "<pre>FFmpeg failed:\n" . implode("\n", $output) . "</pre>";
        exit;
    }
}

// Optional: Fetch course details if needed
$courseQuery = "SELECT * FROM courses WHERE url = ? AND status = 'Active'";
$courseStmt = mysqli_prepare($conn, $courseQuery);
$courseData = null;

if ($courseStmt) {
    mysqli_stmt_bind_param($courseStmt, "s", $courseUrl);
    mysqli_stmt_execute($courseStmt);
    $courseResult = mysqli_stmt_get_result($courseStmt);
    $courseData = mysqli_fetch_assoc($courseResult);
    mysqli_stmt_close($courseStmt);
}

$courseTitle = $courseData ? $courseData['s_name'] : ucfirst(str_replace('-', ' ', $courseUrl));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($courseTitle) ?> | Profile Video</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #1e1e1e;
            color: #fff;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1, h2 {
            color: #00adb5;
        }
        p {
            font-size: 1.2rem;
        }
        video {
            max-width: 100%;
            border: 3px solid #00adb5;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 173, 181, 0.3);
            margin-top: 2rem;
        }
        .container {
            max-width: 800px;
            width: 100%;
            text-align: center;
        }
        .user-info {
            background: rgba(0, 173, 181, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Course: <?= htmlspecialchars($courseTitle) ?></h1>
        
        <div class="user-info">
            <h2>Welcome, <?= htmlspecialchars($userName) ?>!</h2>
            <p>User ID: <strong><?= htmlspecialchars($userId) ?></strong></p>
            <p>Email: <strong><?= htmlspecialchars($email) ?></strong></p>
        </div>

        <video controls>
            <source src="<?= htmlspecialchars($outputVideo) ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        
        <?php if ($courseData): ?>
        <div style="margin-top: 2rem; text-align: left; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 10px;">
            <h3>Course Details:</h3>
            <p><strong>Price:</strong> ₹<?= htmlspecialchars($courseData['price']) ?></p>
            <?php if (!empty($courseData['description'])): ?>
            <p><strong>Description:</strong> <?= htmlspecialchars($courseData['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('admin/include/db_config.php');
include('include/session_validator.php');

// Validate user session
if (!validateUserSession($conn)) {
    forceLogout('login.php');
}


try {
    echo "<!-- DEBUG: Setting time limit -->\n";
    set_time_limit(300);
    echo "<!-- DEBUG: Time limit set -->\n";
} catch (Exception $e) {
    echo "<!-- DEBUG: Warning - Could not set time limit: " . $e->getMessage() . " -->\n";
}

// Check if user is logged in
echo "<!-- DEBUG: Checking user session -->\n";
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    echo "<!-- DEBUG: User not logged in, redirecting -->\n";
    header("Location: /login.php");
    exit;
}
echo "<!-- DEBUG: User is logged in -->\n";

$user_id = $_SESSION['user_id'];
echo "<!-- DEBUG: User ID: $user_id -->\n";

// Check if database connection exists
if (!isset($conn)) {
    die("<!-- DEBUG: Database connection not available -->");
}
echo "<!-- DEBUG: Database connection available -->\n";

// Fetch user details from DB
echo "<!-- DEBUG: Preparing user query -->\n";
try {
    $userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
    if (!$userQuery) {
        die("<!-- DEBUG: Failed to prepare user query: " . $conn->error . " -->");
    }
    echo "<!-- DEBUG: User query prepared -->\n";

    $userQuery->bind_param("i", $user_id);
    echo "<!-- DEBUG: User query parameters bound -->\n";

    $userQuery->execute();
    echo "<!-- DEBUG: User query executed -->\n";

    $userResult = $userQuery->get_result();
    echo "<!-- DEBUG: User query result obtained -->\n";
} catch (Exception $e) {
    die("<!-- DEBUG: User query failed: " . $e->getMessage() . " -->");
}

if ($userResult->num_rows == 0) {
    echo "<!-- DEBUG: User not found in database, destroying session -->\n";
    session_destroy();
    header("Location: /login.php");
    exit;
}

try {
    $userData = $userResult->fetch_assoc();
    echo "<!-- DEBUG: User data fetched -->\n";
    $userEmail = $userData['email'] ?? '';
    $userPhone = $userData['mobile'] ?? '';
    $watermarkText = $userEmail . ($userPhone ? " | " . $userPhone : "");
    echo "<!-- DEBUG: User email: $userEmail, User phone: $userPhone, Watermark text: $watermarkText -->\n";
} catch (Exception $e) {
    die("<!-- DEBUG: Failed to fetch user data: " . $e->getMessage() . " -->");
}

// Validate course_id
echo "<!-- DEBUG: Validating course ID -->\n";
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
echo "<!-- DEBUG: Course ID: $courseId -->\n";

if (!$courseId) {
    die("<!-- DEBUG: Invalid course ID --><h2>Invalid course.</h2>");
}

// Fetch course
echo "<!-- DEBUG: Preparing course query -->\n";
try {
    $courseQuery = $conn->prepare("SELECT s_name FROM courses WHERE id = ?");
    if (!$courseQuery) {
        die("<!-- DEBUG: Failed to prepare course query: " . $conn->error . " -->");
    }

    $courseQuery->bind_param("i", $courseId);
    $courseQuery->execute();
    $courseResult = $courseQuery->get_result();
    echo "<!-- DEBUG: Course query executed -->\n";
} catch (Exception $e) {
    die("<!-- DEBUG: Course query failed: " . $e->getMessage() . " -->");
}

if ($courseResult->num_rows == 0) {
    die("<!-- DEBUG: Course not found --><h2>Course not found.</h2>");
}

try {
    $courseData = $courseResult->fetch_assoc();
    $courseName = $courseData['s_name'];
    echo "<!-- DEBUG: Course name: $courseName -->\n";
} catch (Exception $e) {
    die("<!-- DEBUG: Failed to fetch course data: " . $e->getMessage() . " -->");
}

// Fetch existing note for the user
echo "<!-- DEBUG: Fetching user notes -->\n";
$note_content = '';
try {
    $noteQuery = $conn->prepare("SELECT note_content FROM user_notes WHERE user_id = ? AND course_id = ?");
    if ($noteQuery) {
        $noteQuery->bind_param("ii", $user_id, $courseId);
        $noteQuery->execute();
        $noteResult = $noteQuery->get_result();
        if ($noteRow = $noteResult->fetch_assoc()) {
            $note_content = $noteRow['note_content'];
            echo "<!-- DEBUG: Note content loaded -->\n";
        } else {
            echo "<!-- DEBUG: No existing notes found -->\n";
        }
        $noteQuery->close();
    } else {
        echo "<!-- DEBUG: Could not prepare note query (table might not exist) -->\n";
    }
} catch (Exception $e) {
    echo "<!-- DEBUG: Note query failed (this is OK if table doesn't exist): " . $e->getMessage() . " -->\n";
}

// Fetch lessons
echo "<!-- DEBUG: Preparing lesson query -->\n";
try {
    $lessonQuery = $conn->prepare("
        SELECT lesson_title, video_url 
        FROM lesson_video 
        WHERE course_id = ?
    ");
    if (!$lessonQuery) {
        die("<!-- DEBUG: Failed to prepare lesson query: " . $conn->error . " -->");
    }

    $lessonQuery->bind_param("i", $courseId);
    $lessonQuery->execute();
    $lessonsResult = $lessonQuery->get_result();
    $lessonCount = $lessonsResult->num_rows;
    echo "<!-- DEBUG: Found $lessonCount lessons -->\n";
} catch (Exception $e) {
    die("<!-- DEBUG: Lesson query failed: " . $e->getMessage() . " -->");
}

// Set up paths
echo "<!-- DEBUG: Setting up paths -->\n";
$videoBasePath = 'https://secondsightfoundation.com/';
$outputDir = '/home2/jhbewdmy/public_html/secondsightfoundationcom/admin/temp_videos/';
$watermarkDir = '/home2/jhbewdmy/public_html/secondsightfoundationcom/admin/watermarks/';

echo "<!-- DEBUG: Output dir: $outputDir -->\n";
echo "<!-- DEBUG: Watermark dir: $watermarkDir -->\n";

// Create directories if they don't exist
if (!is_dir($outputDir)) {
    echo "<!-- DEBUG: Creating output directory -->\n";
    if (!mkdir($outputDir, 0755, true)) {
        echo "<!-- DEBUG: Failed to create output directory -->\n";
    } else {
        echo "<!-- DEBUG: Output directory created -->\n";
    }
} else {
    echo "<!-- DEBUG: Output directory exists -->\n";
}

if (!is_dir($watermarkDir)) {
    echo "<!-- DEBUG: Creating watermark directory -->\n";
    if (!mkdir($watermarkDir, 0755, true)) {
        echo "<!-- DEBUG: Failed to create watermark directory -->\n";
    } else {
        echo "<!-- DEBUG: Watermark directory created -->\n";
    }
} else {
    echo "<!-- DEBUG: Watermark directory exists -->\n";
}

// Path to FFmpeg static binary
$ffmpegPath = '/home2/jhbewdmy/bin/ffmpeg';
echo "<!-- DEBUG: FFmpeg path: $ffmpegPath -->\n";

// Path to font file
$fontFile = '/home2/jhbewdmy/public_html/secondsightfoundationcom/fonts/DejaVuSans-Bold.ttf';
echo "<!-- DEBUG: Font file: $fontFile -->\n";
echo "<!-- DEBUG: Font file exists: " . (file_exists($fontFile) ? 'Yes' : 'No') . " -->\n";

// Function to create watermark image using GD
function createWatermarkImage($text, $outputPath)
{
    echo "<!-- DEBUG: Creating watermark image for: $text -->\n";

    if (!extension_loaded('gd')) {
        echo "<!-- DEBUG: GD extension not loaded -->\n";
        return false;
    }

    try {
        // Create smaller, simpler image
        $width = 300;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        if (!$image) {
            echo "<!-- DEBUG: Failed to create image resource -->\n";
            return false;
        }

        // Enable alpha blending
        imagealphablending($image, false);
        imagesavealpha($image, true);

        // Create transparent background
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        // Create semi-transparent white text (30% opacity for better performance)
        $textColor = imagecolorallocatealpha($image, 255, 255, 255, 90);

        // Use built-in font for better compatibility
        $fontSize = 4; // Built-in font size (1-5)
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);

        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        imagestring($image, $fontSize, $x, $y, $text, $textColor);

        // Save as PNG with transparency
        $result = imagepng($image, $outputPath);
        imagedestroy($image);

        echo "<!-- DEBUG: Watermark image creation result: " . ($result ? 'Success' : 'Failed') . " -->\n";
        return $result;
    } catch (Exception $e) {
        echo "<!-- DEBUG: Exception in createWatermarkImage: " . $e->getMessage() . " -->\n";
        return false;
    }
}

// Function to check if drawtext filter is available
function checkDrawtextAvailable($ffmpegPath)
{
    echo "<!-- DEBUG: Checking drawtext availability -->\n";
    try {
        $cmd = "$ffmpegPath -filters 2>/dev/null | grep -i drawtext";
        $output = shell_exec($cmd);
        // Fix for PHP 8.1+ deprecation warning: handle null return from shell_exec
        $output = $output ?? '';
        $available = !empty(trim($output));
        echo "<!-- DEBUG: Drawtext available: " . ($available ? 'Yes' : 'No') . " -->\n";
        return $available;
    } catch (Exception $e) {
        echo "<!-- DEBUG: Exception checking drawtext: " . $e->getMessage() . " -->\n";
        return false;
    }
}

// Function to apply watermark using overlay method
function applyWatermarkOverlay($ffmpegPath, $inputPath, $watermarkPath, $outputPath)
{
    echo "<!-- DEBUG: Applying watermark overlay -->\n";

    $inputEscaped = escapeshellarg($inputPath);
    $watermarkEscaped = escapeshellarg($watermarkPath);
    $outputEscaped = escapeshellarg($outputPath);

    // 100-position pseudo-random jumping (10x10 grid) every 5 seconds
    // Values are calculated using a prime multiplier (37) for unpredictable sequence
    $gridIdx = "mod(floor(t/5)*37,100)";
    $gridX = "floor(mod($gridIdx,10))*(W-w-60)/9+30";
    $gridY = "floor($gridIdx/10)*(H-h-60)/9+30";

    // Sync'd jump-and-hide: visible for 4.5s, hidden for 0.5s during jump
    $enable = "lt(mod(t,5),4.5)";

    $cmd = "$ffmpegPath -i $inputEscaped -i $watermarkEscaped -filter_complex \"[0:v][1:v]overlay=x='$gridX':y='$gridY':enable='$enable'\" -c:a copy -preset ultrafast $outputEscaped -y 2>&1";

    echo "<!-- DEBUG: FFmpeg overlay command: $cmd -->\n";
    $output = shell_exec($cmd);
    echo "<!-- DEBUG: FFmpeg overlay output: $output -->\n";

    return $cmd . "\n\n" . $output;
}

// Function to apply watermark using drawtext method
function applyWatermarkDrawtext($ffmpegPath, $inputPath, $outputPath, $fontFile, $text)
{
    echo "<!-- DEBUG: Applying watermark drawtext -->\n";

    $inputEscaped = escapeshellarg($inputPath);
    $outputEscaped = escapeshellarg($outputPath);
    $fontEscaped = escapeshellarg($fontFile);
    $emailText = addcslashes($text, ":\\'\"");

    // 100-position pseudo-random jumping (10x10 grid) every 5 seconds
    $gridIdx = "mod(floor(t/5)*37,100)";
    $gridX = "floor(mod($gridIdx,10))*(w-tw-60)/9+30";
    $gridY = "floor($gridIdx/10)*(h-th-60)/9+30";

    // Sync'd jump-and-hide: visible for 4.5s, hidden for 0.5s during jump
    $enable = "lt(mod(t,5),4.5)";

    // New Design: Added semi-transparent background box and increased font padding
    $cmd = "$ffmpegPath -i $inputEscaped -vf \"drawtext=fontfile=$fontEscaped:text='$emailText':fontcolor=white:fontsize=40:box=1:boxcolor=black@0.4:boxborderw=10:x='$gridX':y='$gridY':enable='$enable'\" -c:a copy $outputEscaped -y 2>&1";

    echo "<!-- DEBUG: FFmpeg drawtext command: $cmd -->\n";
    $output = shell_exec($cmd);
    echo "<!-- DEBUG: FFmpeg drawtext output: $output -->\n";

    return $cmd . "\n\n" . $output;
}

// Function to just copy video without watermark (for testing)
function copyVideoWithoutWatermark($ffmpegPath, $inputPath, $outputPath)
{
    echo "<!-- DEBUG: Copying video without watermark -->\n";

    $inputEscaped = escapeshellarg($inputPath);
    $outputEscaped = escapeshellarg($outputPath);

    $cmd = "$ffmpegPath -i $inputEscaped -c copy $outputEscaped -y 2>&1";

    echo "<!-- DEBUG: FFmpeg copy command: $cmd -->\n";
    $output = shell_exec($cmd);
    echo "<!-- DEBUG: FFmpeg copy output: $output -->\n";

    return $cmd . "\n\n" . $output;
}

// Check if GD extension is loaded
echo "<!-- DEBUG: Checking GD extension -->\n";
$gdAvailable = extension_loaded('gd');
echo "<!-- DEBUG: GD extension available: " . ($gdAvailable ? 'Yes' : 'No') . " -->\n";

// Check if drawtext is available
echo "<!-- DEBUG: About to check drawtext -->\n";
$drawtextAvailable = checkDrawtextAvailable($ffmpegPath);
echo "<!-- DEBUG: Drawtext check completed -->\n";

echo "<!-- DEBUG: Starting HTML output -->\n";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?= htmlspecialchars($courseName) ?> | Watch Course</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://www.secondsightfoundation.com/assets/img/logo-fav.png" type="image/png">
    <!-- DEBUG: HTML head loaded -->
    <style>
        :root {
            --main-color: #00adb5;
            --secondary-color: #393e46;
            --text-color: #222831;
            --bg-color: #f5f5f5;
            --white: #ffffff;
            --border-color: #eee;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-title-area {
            background: linear-gradient(135deg, var(--main-color), var(--secondary-color));
            color: var(--white);
            padding: 4rem 0 2rem;
            text-align: center;
        }

        .page-title-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .default-btn {
            display: inline-block;
            background: var(--white);
            color: var(--main-color);
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid var(--white);
            cursor: pointer;
        }

        .default-btn:hover {
            background: transparent;
            color: var(--white);
        }

        .py-5 {
            padding: 3rem 0;
        }

        /* Lesson Container Layout */
        .lesson-container {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }

        .lesson-sidebar {
            flex: 1;
            max-width: 300px;
        }

        .lesson-main-content {
            flex: 2;
        }

        /* Sidebar Styles */
        .lesson-sidebar h3 {
            color: var(--main-color);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .playlist {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .lesson-button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 1rem 1.5rem;
            border: none;
            border-bottom: 1px solid var(--border-color);
            background: var(--white);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .lesson-button:last-child {
            border-bottom: none;
        }

        .lesson-button.active,
        .lesson-button:hover {
            background: var(--main-color);
            color: var(--white);
            transform: translateX(5px);
        }

        /* Main Content Styles */
        .video-wrapper,
        .notes-section,
        .comments-section {
            background: var(--white);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .lesson-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--main-color);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        .video-wrapper video,
        .video-wrapper iframe {
            width: 100%;
            height: 400px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Form Styles */
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
            resize: vertical;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--main-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary-color);
        }

        /* Star Rating */
        .rating-stars {
            display: inline-block;
            direction: rtl;
            margin-bottom: 1rem;
        }

        .rating-stars input[type="radio"] {
            display: none;
        }

        .rating-stars label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            padding: 0 0.2rem;
            transition: color 0.2s ease;
        }

        .rating-stars label:hover,
        .rating-stars label:hover~label,
        .rating-stars input[type="radio"]:checked~label {
            color: #f5b50a;
        }

        /* Comments List */
        .comment-list {
            margin-top: 2rem;
        }

        .comment {
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-author {
            font-weight: 600;
            color: var(--main-color);
            font-size: 1.1rem;
        }

        .comment-meta {
            font-size: 0.9rem;
            color: #777;
            margin: 0.5rem 0 1rem;
        }

        .comment-rating .star {
            color: #f5b50a;
        }

        .no-reviews {
            text-align: center;
            color: #999;
            font-style: italic;
            padding: 2rem;
        }

        /* Debug Info Styles */
        .debug-info {
            background: #2d2d2d;
            color: #00ff00;
            padding: 15px;
            margin: 15px 0;
            border: 2px dashed #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            border-radius: 5px;
        }

        .watermark-method {
            background: #333;
            color: #ffeb3b;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #ffeb3b;
            border-radius: 5px;
        }

        .status {
            padding: 12px 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: 500;
        }

        .status.success {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }

        .status.error {
            background: #ffeaea;
            border: 1px solid #f44336;
            color: #c62828;
        }

        .status.warning {
            background: #fff8e1;
            border: 1px solid #ff9800;
            color: #ef6c00;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .lesson-container {
                flex-direction: column;
            }

            .lesson-sidebar {
                max-width: 100%;
            }

            .page-title-content h2 {
                font-size: 2rem;
            }

            .video-wrapper,
            .notes-section,
            .comments-section {
                padding: 1.5rem;
            }
        }

        /* Loading and Status Styles */
        #note-status {
            font-weight: 600;
            margin-left: 1rem;
        }

        .my-4 {
            margin: 2rem 0;
        }

        hr {
            border: none;
            border-top: 2px solid var(--border-color);
            margin: 2rem 0;
        }
    </style>
    <!-- DEBUG: CSS loaded -->
</head>

<body>
    <!-- DEBUG: Body started -->
    <div class="page-title-area">
        <div class="container">
            <div class="page-title-content">
                <h2><?= htmlspecialchars($courseName) ?></h2>
                <a href="/profile.php" class="default-btn">← Back to Profile</a>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <!-- DEBUG Information Section -->
        <!--<div class="watermark-method">-->
        <!--    <strong>DEBUG INFO:</strong><br>-->
        <!--    <strong>FFmpeg Path:</strong> <?= $ffmpegPath ?><br>-->
        <!--    <strong>Font File:</strong> <?= file_exists($fontFile) ? "✅ Found" : "❌ Not Found" ?> (<?= $fontFile ?>)<br>-->
        <!--    <strong>DrawText Available:</strong> <?= $drawtextAvailable ? '✅ Yes' : '❌ No (will use image overlay)' ?><br>-->
        <!--    <strong>GD Extension:</strong> <?= $gdAvailable ? '✅ Available' : '❌ Not Available' ?><br>-->
        <!--    <strong>Output Directory:</strong> <?= is_dir($outputDir) ? '✅ Exists' : '❌ Missing' ?> (<?= $outputDir ?>)<br>-->
        <!--    <strong>Watermark Directory:</strong> <?= is_dir($watermarkDir) ? '✅ Exists' : '❌ Missing' ?> (<?= $watermarkDir ?>)-->
        <!--</div>-->

        <div class="lesson-container">
            <aside class="lesson-sidebar">
                <h3>Course Lessons (<?= $lessonCount ?>)</h3>
                <div class="playlist">
                    <?php
                    echo "<!-- DEBUG: Starting lesson sidebar -->\n";
                    if ($lessonCount > 0) {
                        $lessonIndex = 0;
                        mysqli_data_seek($lessonsResult, 0);
                        while ($row = $lessonsResult->fetch_assoc()) {
                            $lessonTitle = htmlspecialchars($row['lesson_title'] ?? 'Untitled Lesson');
                            echo "<button class='lesson-button' onclick='showLesson($lessonIndex)'>$lessonTitle</button>";
                            $lessonIndex++;
                        }
                        echo "<!-- DEBUG: Sidebar lessons loaded -->\n";
                    } else {
                        echo "<div style='padding: 1rem; text-align: center; color: #999;'>No lessons available</div>";
                        echo "<!-- DEBUG: No lessons found -->\n";
                    }
                    ?>
                </div>
            </aside>

            <main class="lesson-main-content">
                <div class="video-wrapper">
                    <?php
                    echo "<!-- DEBUG: Starting main video content -->\n";

                    if ($lessonCount === 0) {
                        echo "<h2 style='color: #999; text-align: center; padding: 3rem;'>No course videos uploaded yet.</h2>";
                        echo "<!-- DEBUG: No lessons to display -->\n";
                    } else {
                        mysqli_data_seek($lessonsResult, 0);
                        $lessonIndex = 0;

                        while ($row = $lessonsResult->fetch_assoc()) {
                            echo "<!-- DEBUG: Processing lesson $lessonIndex -->\n";

                            $lessonTitle = $row['lesson_title'];
                            $videoUrl = $row['video_url'];

                            echo "<div class='lesson-content' style='display:" . ($lessonIndex === 0 ? 'block' : 'none') . "'>";
                            echo "<h4 class='lesson-title'>" . htmlspecialchars($lessonTitle) . "</h4>";

                            $localVideoPath = '/home2/jhbewdmy/public_html/secondsightfoundationcom/admin/' . $videoUrl;
                            echo "<!-- DEBUG: Local video path: $localVideoPath -->\n";

                            if (!file_exists($localVideoPath)) {
                                echo "<div class='status error'>Video for <strong>" . htmlspecialchars($lessonTitle) . "</strong> not found on local path: $localVideoPath</div>";
                                echo "<!-- DEBUG: Video file not found -->\n";
                                echo "</div>";
                                $lessonIndex++;
                                continue;
                            }

                            echo "<!-- DEBUG: Video file exists -->\n";

                            $safeEmail = preg_replace('/[^A-Za-z0-9@\._-]/', '_', $userEmail);
                            $safePhone = preg_replace('/[^0-9]/', '', $userPhone);
                            $safeText = preg_replace('/[^A-Za-z0-9@\._-]/', '_', $watermarkText);
                            // v7 forces regeneration with 200px margins and larger font
                            $outputFilename = "lesson_dyn_v10_{$courseId}_user_{$user_id}_{$safeEmail}_{$safePhone}_" . basename($videoUrl);
                            $outputVideoPath = $outputDir . $outputFilename;

                            echo "<!-- DEBUG: Output filename: $outputFilename -->\n";
                            echo "<!-- DEBUG: Output path: $outputVideoPath -->\n";

                            // Only re-generate if it doesn't exist or is older than 1 hour
                            $needsRegeneration = !file_exists($outputVideoPath) || (time() - filemtime($outputVideoPath)) > 3600;
                            echo "<!-- DEBUG: Needs regeneration: " . ($needsRegeneration ? 'Yes' : 'No') . " -->\n";

                            if ($needsRegeneration) {
                                echo "<!-- DEBUG: Starting video processing -->\n";

                                $ffmpegOutput = '';
                                $method = '';
                                $success = false;

                                if ($drawtextAvailable && file_exists($fontFile)) {
                                    // Method 1: Use drawtext filter
                                    echo "<!-- DEBUG: Using drawtext method -->\n";
                                    $method = "DrawText Filter";
                                    $ffmpegOutput = applyWatermarkDrawtext($ffmpegPath, $localVideoPath, $outputVideoPath, $fontFile, $watermarkText);
                                    $success = file_exists($outputVideoPath);

                                } elseif ($gdAvailable) {
                                    // Method 2: Use image overlay
                                    echo "<!-- DEBUG: Using image overlay method -->\n";
                                    $method = "Image Overlay";
                                    $watermarkFilename = "watermark_" . md5($watermarkText) . ".png";
                                    $watermarkPath = $watermarkDir . $watermarkFilename;

                                    echo "<!-- DEBUG: Watermark path: $watermarkPath -->\n";

                                    // Create watermark image if it doesn't exist
                                    if (!file_exists($watermarkPath)) {
                                        echo "<!-- DEBUG: Creating watermark image -->\n";
                                        if (!createWatermarkImage($watermarkText, $watermarkPath)) {
                                            echo "<div class='status error'>Failed to create watermark image for: " . htmlspecialchars($watermarkText) . "</div>";
                                            echo "<!-- DEBUG: Watermark creation failed -->\n";
                                            echo "</div>";
                                            $lessonIndex++;
                                            continue;
                                        }
                                        echo "<div class='status success'>Created watermark image: $watermarkPath (" . number_format(filesize($watermarkPath) / 1024, 2) . " KB)</div>";
                                        echo "<!-- DEBUG: Watermark created successfully -->\n";
                                    } else {
                                        echo "<!-- DEBUG: Using existing watermark image -->\n";
                                    }

                                    // Verify watermark image exists and is valid
                                    if (!file_exists($watermarkPath) || filesize($watermarkPath) == 0) {
                                        echo "<div class='status error'>Watermark image is invalid or empty</div>";
                                        echo "<!-- DEBUG: Watermark validation failed -->\n";
                                        echo "</div>";
                                        $lessonIndex++;
                                        continue;
                                    }

                                    echo "<!-- DEBUG: Watermark validated, applying overlay -->\n";
                                    $ffmpegOutput = applyWatermarkOverlay($ffmpegPath, $localVideoPath, $watermarkPath, $outputVideoPath);
                                    $success = file_exists($outputVideoPath) && filesize($outputVideoPath) > 1000; // At least 1KB
                    
                                } else {
                                    // Method 3: Copy without watermark (fallback)
                                    echo "<!-- DEBUG: Using copy-only method (fallback) -->\n";
                                    $method = "No Watermark (Copy Only)";
                                    $ffmpegOutput = copyVideoWithoutWatermark($ffmpegPath, $localVideoPath, $outputVideoPath);
                                    $success = file_exists($outputVideoPath);
                                }

                                echo "<!-- DEBUG: Processing completed, success: " . ($success ? 'Yes' : 'No') . " -->\n";

                                // Display debug information
                             /*   echo "<div class='debug-info'>";
                                echo "<strong>Method Used:</strong> $method\n\n";
                                echo "<strong>Input File Size:</strong> " . number_format(filesize($localVideoPath) / 1024 / 1024, 2) . " MB\n";
                                echo "<strong>FFmpeg Command & Output:</strong>\n$ffmpegOutput\n\n";
                                echo "<strong>Output File:</strong> " . ($success ? "✅ Created Successfully" : "❌ Failed to Create") . "\n";
                                if ($success) {
                                    $outputSize = filesize($outputVideoPath);
                                    echo "<strong>Output File Size:</strong> " . number_format($outputSize / 1024 / 1024, 2) . " MB\n";
                                    echo "<strong>Processing Status:</strong> " . ($outputSize > 100000 ? "✅ Looks Good" : "⚠️ File might be incomplete") . "\n";
                                }
                                echo "</div>";*/

                                if (!$success) {
                                    echo "<div class='status error'>Failed to create watermarked video for: " . htmlspecialchars($lessonTitle) . "</div>";
                                    echo "<!-- DEBUG: Video processing failed -->\n";
                                    echo "</div>";
                                    $lessonIndex++;
                                    continue;
                                }
                            } else {
                                // echo "<div class='status success'>Using cached video (created " . date('Y-m-d H:i:s', filemtime($outputVideoPath)) . ")</div>";
                                echo "<!-- DEBUG: Using cached video -->\n";
                            }

                            // Final public URL
                            $publicVideoUrl = $base_url . "admin/temp_videos/" . basename($outputVideoPath);
                            $finalVideoUrl = htmlspecialchars($publicVideoUrl) . '?v=' . time(); // cache-busting
                    
                            echo "<!-- DEBUG: About to render video element -->\n";
                            echo "<div class='video-container'>";
                            echo "    <video id='video-$lessonIndex' data-index='$lessonIndex' controls preload='metadata' controlsList='nodownload' class='course-video' style='width:100%'>";
                            echo "        <source src='$finalVideoUrl' type='video/mp4'>";
                            echo "        Your browser does not support the video tag.";
                            echo "    </video>";
                            echo "</div>";
                            echo "<!-- DEBUG: Video element rendered -->\n";

                            echo "</div>"; // .lesson-content
                            $lessonIndex++;
                            echo "<!-- DEBUG: Lesson $lessonIndex completed -->\n";
                        }
                    }
                    echo "<!-- DEBUG: All lessons processed -->\n";
                    ?>
                </div>

                <div class="notes-section">
                    <!-- DEBUG: Notes section started -->
                    <h3>My Personal Notes</h3>
                    <form id="note-form" action="save_note.php" method="POST" class="note-form">
                        <input type="hidden" name="course_id" value="<?= $courseId ?>">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                        <textarea name="note_content" class="form-control" rows="5"
                            placeholder="Jot down your notes for this course here..."><?= htmlspecialchars($note_content) ?></textarea>
                        <!--<button type="submit" class="default-btn" style="">Save Notes</button>-->
                        <button 
  type="submit" 
  class="default-btn"
  style="background-color:#28a745; color:#fff; transition:0.3s;"
  onmouseover="this.style.backgroundColor='#218838'" 
  onmouseout="this.style.backgroundColor='#28a745'">
  Save Notes
</button>
                        <span id="note-status" style="margin-left: 1rem;"></span>
                    </form>
                    <!-- DEBUG: Notes section completed -->
                </div>

                <div class="comments-section">
                    <!-- DEBUG: Comments section started -->
                    <h3>Rate & Review This Course</h3>
                    <form id="comment-form" action="add_comment.php" method="POST" class="comment-form">
                        <input type="hidden" name="course_id" value="<?= $courseId ?>">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">

                       <div class="form-group">
                            <label>Your rating:</label>
                            <div class="rating-stars">
                                <input type="radio" id="star5" name="rating" value="5" required>
                                <label for="star5" style="display:inline-block; font-size:20px; cursor:pointer;">★</label>
                        
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" style="display:inline-block; font-size:20px; cursor:pointer;">★</label>
                        
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3" style="display:inline-block; font-size:20px; cursor:pointer;">★</label>
                        
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" style="display:inline-block; font-size:20px; cursor:pointer;">★</label>
                        
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" style="display:inline-block; font-size:20px; cursor:pointer;">★</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea name="comment" class="form-control" rows="4"
                                placeholder="Write your review here..."></textarea>
                        </div>
                        <button type="submit" class="default-btn">Submit Review</button>
                    </form>

                    <hr class="my-4">

                    <h4>Student Reviews</h4>
                    <div class="comment-list">
                        <?php
                        echo "<!-- DEBUG: Loading existing comments -->\n";
                        try {
                            $commentQuery = $conn->prepare("
                                SELECT c.comment, c.rating, c.created_date, u.name 
                                FROM course_comment c
                                JOIN users u ON c.user_id = u.id
                                WHERE c.course_id = ? 
                                ORDER BY c.created_date DESC
                            ");

                            if (!$commentQuery) {
                                echo "<!-- DEBUG: Failed to prepare comment query: " . $conn->error . " -->\n";
                                echo "<p class='no-reviews'>Unable to load reviews at this time.</p>";
                            } else {
                                $commentQuery->bind_param("i", $courseId);
                                $commentQuery->execute();
                                $commentResult = $commentQuery->get_result();
                                echo "<!-- DEBUG: Found " . $commentResult->num_rows . " comments -->\n";

                                if ($commentResult->num_rows > 0) {
                                    while ($comment = $commentResult->fetch_assoc()) {
                                        ?>
                                        <div class="comment">
                                            <div class="comment-author"><?= htmlspecialchars($comment['name']) ?></div>
                                            <div class="comment-meta">
                                                <span><?= date('F j, Y', strtotime($comment['created_date'])) ?></span>
                                                <?php if (!empty($comment['rating'])): ?>
                                                    <span class="comment-rating">
                                                        -
                                                        <?php for ($i = 0; $i < $comment['rating']; $i++): ?>
                                                            <span class="star">★</span>
                                                        <?php endfor; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <p><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    echo "<p class='no-reviews'>Be the first to review this course!</p>";
                                }
                                $commentQuery->close();
                            }
                        } catch (Exception $e) {
                            echo "<!-- DEBUG: Comment query failed: " . $e->getMessage() . " -->\n";
                            echo "<p class='no-reviews'>Unable to load reviews: " . htmlspecialchars($e->getMessage()) . "</p>";
                        }
                        echo "<!-- DEBUG: Comments section completed -->\n";
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- DEBUG: Starting JavaScript -->
    <script>
        console.log('DEBUG: JavaScript started');

        let currentLesson = 0;
        const lessons = document.querySelectorAll('.lesson-content');
        const buttons = document.querySelectorAll('.lesson-button');

        console.log('DEBUG: Found', lessons.length, 'lessons and', buttons.length, 'buttons');

        function showLesson(index) {
            console.log('DEBUG: Showing lesson', index);

            if (lessons[currentLesson]) {
                lessons[currentLesson].style.display = 'none';
            }
            if (buttons[currentLesson]) {
                buttons[currentLesson].classList.remove('active');
            }

            if (lessons[index]) {
                lessons[index].style.display = 'block';
            }
            if (buttons[index]) {
                buttons[index].classList.add('active');
            }
            currentLesson = index;

            console.log('DEBUG: Lesson switched to', index);
        }

        // Initialize first lesson
        if (buttons.length > 0) {
            buttons[0].classList.add('active');
            console.log('DEBUG: First lesson activated');
        }

        // --- AJAX Form Submission ---
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DEBUG: DOM loaded, setting up forms');

            // Notes Form
            const noteForm = document.getElementById('note-form');
            const noteStatus = document.getElementById('note-status');

            if (noteForm) {
                console.log('DEBUG: Note form found, adding event listener');
                noteForm.addEventListener('submit', function (e) {
                    console.log('DEBUG: Note form submitted');
                    e.preventDefault();
                    const button = this.querySelector('button[type="submit"]');
                    button.disabled = true;
                    noteStatus.textContent = 'Saving...';

                    const formData = new FormData(this);

                    fetch('save_note.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            console.log('DEBUG: Note save response received');
                            return response.json();
                            
                        })
                        .then(data => {
                            console.log('DEBUG: Note save data:', data);
                            if (data.success) {
                                noteStatus.textContent = 'Saved!';
                                noteStatus.style.color = 'green';
                            } else {
                                noteStatus.textContent = 'Error: ' + (data.message || 'Unknown error');
                                noteStatus.style.color = 'red';
                            }
                            setTimeout(() => {
                                noteStatus.textContent = '';
                                button.disabled = false;
                            }, 3000);
                        })
                        .catch(error => {
                            console.log('DEBUG: Note save error:', error);
                            noteStatus.textContent = 'An unexpected error occurred.';
                            noteStatus.style.color = 'red';
                            setTimeout(() => {
                                noteStatus.textContent = '';
                                button.disabled = false;
                            }, 3000);
                        });
                });
            } else {
                console.log('DEBUG: Note form not found');
            }

            // Comment Form
            const commentForm = document.getElementById('comment-form');

            if (commentForm) {
                console.log('DEBUG: Comment form found, adding event listener');
                commentForm.addEventListener('submit', function (e) {
                    console.log('DEBUG: Comment form submitted');
                    e.preventDefault();
                    const button = this.querySelector('button[type="submit"]');
                    button.disabled = true;

                    const formData = new FormData(this);

                    fetch('add_comment.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            console.log('DEBUG: Comment response received');
                            return response.json();
                            
                        })
                        .then(data => {
                            console.log('DEBUG: Comment data:', data);
                            if (data.success) {
                                const commentList = document.querySelector('.comment-list');
                                const newComment = data.comment;

                                // Remove the 'no reviews' message if it exists
                                const noReviews = commentList.querySelector('.no-reviews');
                                if (noReviews) {
                                    noReviews.remove();
                                }

                                // Create rating stars
                                let ratingHTML = '';
                                if (newComment.rating) {
                                    ratingHTML += '<span class="comment-rating"> - ';
                                    for (let i = 0; i < newComment.rating; i++) {
                                        ratingHTML += '<span class="star">★</span>';
                                    }
                                    ratingHTML += '</span>';
                                }

                                // Create new comment element
                                const commentElement = document.createElement('div');
                                commentElement.className = 'comment';
                                commentElement.innerHTML = `
                                <div class="comment-author">${newComment.name}</div>
                                <div class="comment-meta">
                                    <span>${newComment.created_date}</span>
                                    ${ratingHTML}
                                </div>
                                <p>${newComment.comment}</p>
                            `;

                                commentList.prepend(commentElement); // Add to top of the list
                                commentForm.reset(); // Reset form fields

                                // Reset star rating UI
                                const stars = commentForm.querySelectorAll('.rating-stars input');
                                if (stars.length > 0) stars.forEach(star => star.checked = false);

                            } else {
                                alert('Error: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.log('DEBUG: Comment error:', error);
                            alert('An unexpected error occurred.');
                        })
                        .finally(() => {
                            button.disabled = false;
                        });
                });
            } else {
                console.log('DEBUG: Comment form not found');
            }

            console.log('DEBUG: Form setup completed');

        });

        console.log('DEBUG: JavaScript completed');
    </script>





    <script>
        document.addEventListener("contextmenu", function (e) {
            e.preventDefault();
        });
    </script>

    <script>
        document.onkeydown = function (e) {

            if (e.keyCode == 123) { return false; } // F12

            if (e.ctrlKey && e.shiftKey && e.keyCode == 73) { return false; } // Ctrl+Shift+I

            if (e.ctrlKey && e.keyCode == 85) { return false; } // Ctrl+U

        };
    </script>

    <script>
        setInterval(function () {
            if (window.outerWidth - window.innerWidth > 160 || window.outerHeight - window.innerHeight > 160) {
                const video = document.querySelector("video");
                if (video) {
                    video.pause();
                    alert("Developer tools detected. Video paused.");
                }
            }
        }, 1000);
    </script>



































    <!-- DEBUG: JavaScript completed -->
</body>

</html>
<!-- DEBUG: HTML completed -->
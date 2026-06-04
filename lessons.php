<?php


// ALTER TABLE `course_comment` ADD `rating` TINYINT(1) NULL DEFAULT NULL AFTER `comment`;

// CREATE TABLE `user_notes` (
//   `id` INT NOT NULL AUTO_INCREMENT,
//   `user_id` INT NOT NULL,
//   `course_id` INT NOT NULL,
//   `note_content` TEXT,
//   `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
//   `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//   PRIMARY KEY (`id`),
//   UNIQUE KEY `user_course_unique` (`user_id`, `course_id`)
// );

session_start();
include('admin/include/db_config.php');
set_time_limit(300);
// Include the access check function
include('check_course_access.php');
// --- Check if user is logged in ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: /login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id']; // Assuming course_id is passed via GET
$access_check = checkCourseAccess($user_id, $course_id, $conn);
if (!$access_check['access']) {
    // Redirect to profile with error message
    $_SESSION['error_message'] = "You don't have access to this course. Please purchase it or use a valid batch code.";
    header("Location: profile.php");
    exit;
}
// --- Fetch user details ---
$userQuery = $conn->prepare("SELECT * FROM users WHERE id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows == 0) {
    session_destroy();
    header("Location: /login.php");
    exit;
}

$userData = $userResult->fetch_assoc();
$userEmail = $userData['email'] ?? '';
$userPhone = $userData['mobile'] ?? '';

// --- Validate course_id ---
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
if (!$courseId) {
    echo "<h2>Invalid course.</h2>";
    exit;
}

// --- Fetch course ---
$courseQuery = $conn->prepare("SELECT s_name FROM courses WHERE id = ?");
$courseQuery->bind_param("i", $courseId);
$courseQuery->execute();
$courseResult = $courseQuery->get_result();

if ($courseResult->num_rows == 0) {
    echo "<h2>Course not found.</h2>";
    exit;
}

$courseData = $courseResult->fetch_assoc();
$courseName = $courseData['s_name'];

// --- Fetch existing note for the user ---
$note_content = '';
$noteQuery = $conn->prepare("SELECT note_content FROM user_notes WHERE user_id = ? AND course_id = ?");
if ($noteQuery) {
    $noteQuery->bind_param("ii", $user_id, $courseId);
    $noteQuery->execute();
    $noteResult = $noteQuery->get_result();
    if ($noteRow = $noteResult->fetch_assoc()) {
        $note_content = $noteRow['note_content'];
    }
    $noteQuery->close();
}

// --- Fetch lessons ---
$lessonQuery = $conn->prepare("
    SELECT DISTINCT lv.lesson_title, lv.video_url, GROUP_CONCAT(b.batch_title SEPARATOR ', ') as batch_titles
    FROM lesson_video lv
    LEFT JOIN lesson_batch lb ON lv.id = lb.lesson_id
    LEFT JOIN batch b ON lb.batch_id = b.id
    WHERE lv.course_id = ? AND lv.status = 'Active'
    GROUP BY lv.id
    ORDER BY lv.lesson_title
");
$lessonQuery->bind_param("i", $courseId);
$lessonQuery->execute();
$lessonsResult = $lessonQuery->get_result();
$lessonCount = $lessonsResult->num_rows;


// --- Paths ---
$videoBasePath = __DIR__ . '/admin/assets/lessonVideo/';
$outputDir = __DIR__ . '/admin/temp_videos/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Full path to ffmpeg binary. Change this if ffmpeg is not in your system's PATH.
$ffmpeg_path = 'ffmpeg'; // or e.g., 'C:/ffmpeg/bin/ffmpeg.exe'

// --- Helper to sanitize email for filename ---
function sanitizeEmailForFilename(string $email): string {
    return preg_replace('/[^A-Za-z0-9_\.-]/', '_', $email);
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include 'include/head.php'; ?>

<style>
    /* Ultra-sharp, Minimal, Professional Lessons Page */
    body {
        background: #f8fafb;
        font-family: 'Int    /* Custom Styles for lessons page */
    .lesson-container {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .lesson-sidebar {
        flex: 1 1 300px;
        min-width: 250px;
    }

    .lesson-main-content {
        flex: 3 1 600px;
    }
    
    .playlist {
        list-style: none;
        padding: 0;
        margin: 0;
        border-radius: 5px;
        overflow: hidden;
        border: 1px solid #eee;
    }

    .playlist .lesson-button {
        display: block;
        width: 100%;
        text-align: left;
        padding: 1rem;
        border: none;
        border-bottom: 1px solid #eee;
        background: #fff;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .playlist .lesson-button:last-child {
        border-bottom: none;
    }

    .playlist .lesson-button.active,
    .playlist .lesson-button:hover {
        background: var(--main-color);
        color: white;
    }

    .video-wrapper,
    .notes-section,
    .comments-section {
        background: #fff;
        padding: 2rem;
        border-radius: 5px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    
    .video-wrapper video, .video-wrapper iframe {
        width: 100%;
        border-radius: 5px;
    }

    .lesson-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--main-color);
    }
    
    .form-control{
        margin-bottom: 1rem;
    }
    
    /* Star Rating */
    .rating-stars {
        display: inline-block;
        direction: rtl;
    }
    .rating-stars input[type="radio"] {
        display: none;
    }
    .rating-stars label {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
        padding: 0 0.1rem;
    }
    .rating-stars label:hover,
    .rating-stars label:hover ~ label,
    .rating-stars input[type="radio"]:checked ~ label {
        color: #f5b50a;
    }

    /* Comments List */
    .comment {
        border-bottom: 1px solid #eee;
        padding: 1rem 0;
    }
    .comment:last-child {
        border-bottom: none;
    }
    .comment-author {
        font-weight: 600;
        color: var(--main-color);
    }
    .comment-meta {
        font-size: 0.85rem;
        color: #777;
        margin-bottom: 0.5rem;
    }
    .comment-rating .star {
        color: #f5b50a;
    }

</style>

<body>
    <?php include('include/header1.php'); ?>

    <div class="page-title-area bg-1">
        <div class="container">
            <div class="page-title-content">
                <h2><?= htmlspecialchars($courseName) ?></h2>
                <a href="/profile.php" class="default-btn">← Back to Profile</a>
            </div>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="lesson-container">
            
            <aside class="lesson-sidebar">
                <h3>Course Lessons</h3>
                <div class="playlist">
                    <?php
                    $lessonIndex = 0;
                    mysqli_data_seek($lessonsResult, 0);
                    while ($row = $lessonsResult->fetch_assoc()) {
                        $lessonTitle = htmlspecialchars($row['lesson_title'] ?? 'Untitled');
                        echo "<button class='lesson-button' onclick='showLesson($lessonIndex)'>$lessonTitle</button>";
                        $lessonIndex++;
                    }
                    ?>
                </div>
            </aside>
            
            <main class="lesson-main-content">
                <div id="lessons-container" class="video-wrapper">
                <?php
                if ($lessonCount === 0) {
                    echo "<h2 style='color: #999; text-align: center;'>No course videos uploaded yet.</h2>";
                } else {
                    mysqli_data_seek($lessonsResult, 0);
                    $lessonIndex = 0;
                    while ($row = $lessonsResult->fetch_assoc()) {
                        $lessonTitle = htmlspecialchars($row['lesson_title'] ?? 'Untitled');
                        $videoUrl = $row['video_url'] ?? '';

                        echo "<div class='lesson-content' style='display:" . ($lessonIndex === 0 ? 'block' : 'none') . "'>";
                        echo "<h4 class='lesson-title'>$lessonTitle</h4>";

                        if (filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                            // YouTube/Vimeo/etc.
                            $embedCode = '';
                             if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                                $ytId = '';
                                if (strpos($videoUrl, 'youtube.com') !== false) {
                                    parse_str(parse_url($videoUrl, PHP_URL_QUERY), $queryParams);
                                    $ytId = $queryParams['v'] ?? '';
                                }
                                if (!$ytId && preg_match('/youtu\.be\/([^\?]+)/', $videoUrl, $matches)) {
                                    $ytId = $matches[1];
                                }
                                if ($ytId) {
                                    $embedCode = "<iframe src='https://www.youtube.com/embed/$ytId' frameborder='0' allowfullscreen></iframe>";
                                }
                            } else {
                                $safeUrl = htmlspecialchars($videoUrl);
                                $embedCode = "<video controls preload='metadata'><source src='$safeUrl' type='video/mp4'></video>";
                            }
                            echo $embedCode;
                        } else {
                            // Local, watermarked video
                            $sanitizedEmail = sanitizeEmailForFilename($userEmail);
                            $outputFilename = "output_{$sanitizedEmail}.mp4";
                            $outputPath = $outputDir . $outputFilename;

                            if (!file_exists($outputPath)) {
                                $videoPath = $videoBasePath . basename($videoUrl);
                                if (file_exists($videoPath)) {
                                    $watermarkText = "$userEmail\\n$userPhone";
                                    // Use a gray color for the text and a subtle shadow for better visibility.
                                    // Removed the fontfile parameter to use FFmpeg's default font.
                                    $ffmpegCmd = $ffmpeg_path . " -i " . escapeshellarg($videoPath) . " -vf \"drawtext=text='" . addslashes($watermarkText) . "':x=w-tw-10:y=(h-th)/2:fontsize=24:fontcolor=gray:shadowcolor=black@0.7:shadowx=2:shadowy=2\" -y " . escapeshellarg($outputPath);
                                    shell_exec($ffmpegCmd);
                                }
                            }

                            if (file_exists($outputPath)) {
                                $videoUrlForBrowser = 'admin/temp_videos/' . $outputFilename;
                                echo "<video controls preload='metadata' controlsList='nodownload'>
                                        <source src='" . htmlspecialchars($videoUrlForBrowser) . "' type='video/mp4'>
                                        Your browser does not support the video tag.
                                      </video>";
                            } else {
                                echo "<p>Video is being prepared. Please check back shortly.</p>";
                            }
                        }
                        echo "</div>"; // .lesson-content
                        $lessonIndex++;
                    }
                }
                ?>
                </div>

                <div class="notes-section">
                    <h3>My Personal Notes</h3>
                    <form id="note-form" action="save_note.php" method="POST" class="note-form">
                        <input type="hidden" name="course_id" value="<?= $courseId ?>">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                        <textarea name="note_content" class="form-control" placeholder="Jot down your notes for this course here..."><?= htmlspecialchars($note_content) ?></textarea>
                        <button type="submit" class="default-btn">Save Notes</button>
                        <span id="note-status" style="margin-left: 1rem;"></span>
                    </form>
                </div>
                
                <div class="comments-section">
                    <h3>Rate & Review This Course</h3>
                    <form id="comment-form" action="add_comment.php" method="POST" class="comment-form">
                        <input type="hidden" name="course_id" value="<?= $courseId ?>">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                        
                        <div class="form-group">
                            <label>Your rating:</label>
                            <div class="rating-stars">
                                <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <textarea name="comment" class="form-control" rows="4" placeholder="Write your review here..."></textarea>
                        </div>
                        <button type="submit" class="default-btn">Submit Review</button>
                    </form>

                    <hr class="my-4">

                    <h4>Student Reviews</h4>
                    <div class="comment-list">
                        <?php
                        $commentQuery = $conn->prepare("
                            SELECT c.comment, c.rating, c.created_date, u.name 
                            FROM course_comment c
                            JOIN users u ON c.user_id = u.id
                            WHERE c.course_id = ? 
                            ORDER BY c.created_date DESC
                        ");
                        $commentQuery->bind_param("i", $courseId);
                        $commentQuery->execute();
                        $commentResult = $commentQuery->get_result();

                        if ($commentResult->num_rows > 0) {
                            while ($comment = $commentResult->fetch_assoc()) {
                        ?>
                                <div class="comment">
                                    <div class="comment-author"><?= htmlspecialchars($comment['name']) ?></div>
                                    <div class="comment-meta">
                                        <span><?= date('F j, Y', strtotime($comment['created_date'])) ?></span>
                                        <?php if(!empty($comment['rating'])): ?>
                                        <span class="comment-rating">
                                            - 
                                            <?php for($i = 0; $i < $comment['rating']; $i++): ?>
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
                        ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include('include/footer.php'); ?>

    <script>
        let currentLesson = 0;
        const lessons = document.querySelectorAll('.lesson-content');
        const buttons = document.querySelectorAll('.lesson-button');
        
        function showLesson(index) {
            if (lessons[currentLesson]) {
                lessons[currentLesson].style.display = 'none';
            }
            if(buttons[currentLesson]) {
                buttons[currentLesson].classList.remove('active');
            }
            
            lessons[index].style.display = 'block';
            buttons[index].classList.add('active');
            currentLesson = index;
        }

        // Initialize first lesson
        if(buttons.length > 0) {
            buttons[0].classList.add('active');
        }

        // --- AJAX Form Submission ---
        document.addEventListener('DOMContentLoaded', function() {
            // Notes Form
            const noteForm = document.getElementById('note-form');
            const noteStatus = document.getElementById('note-status');
            
            noteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;
                noteStatus.textContent = 'Saving...';
                
                const formData = new FormData(this);

                fetch('save_note.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        noteStatus.textContent = 'Saved!';
                        noteStatus.style.color = 'green';
                    } else {
                        noteStatus.textContent = 'Error: ' + data.message;
                        noteStatus.style.color = 'red';
                    }
                    setTimeout(() => {
                        noteStatus.textContent = '';
                        button.disabled = false;
                    }, 3000);
                })
                .catch(error => {
                    noteStatus.textContent = 'An unexpected error occurred.';
                    noteStatus.style.color = 'red';
                    setTimeout(() => {
                        noteStatus.textContent = '';
                        button.disabled = false;
                    }, 3000);
                });
            });

            // Comment Form
            const commentForm = document.getElementById('comment-form');
            
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const button = this.querySelector('button[type="submit"]');
                button.disabled = true;

                const formData = new FormData(this);

                fetch('add_comment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
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
                        
                        // Reset star rating UI if you have one
                        const stars = commentForm.querySelectorAll('.rating-stars input');
                        if(stars.length > 0) stars.forEach(star => star.checked = false);

                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => alert('An unexpected error occurred.'))
                .finally(() => {
                    button.disabled = false;
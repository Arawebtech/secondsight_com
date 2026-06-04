<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$targetDir = __DIR__ . '/assets/lessonVideo/';
$relativePath = 'assets/lessonVideo/';

if (!file_exists($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory.']);
        exit;
    }
}

if (isset($_FILES['video'])) {
    $error = $_FILES['video']['error'];

    if ($error !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 'error',
            'message' => getUploadError($error),
            'error_code' => $error
        ]);
        exit;
    }

    $fileTmpPath = $_FILES['video']['tmp_name'];
    $fileName = basename($_FILES['video']['name']);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'];

    if (!in_array($fileExtension, $allowedExtensions)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)
        ]);
        exit;
    }

    $newFileName = uniqid('lesson_', true) . '.' . $fileExtension;
    $destPath = $targetDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        echo json_encode([
            'status' => 'success',
            'path' => $relativePath . $newFileName
        ]);
        exit;
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to move uploaded file.'
        ]);
        exit;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No file uploaded.'
    ]);
    exit;
}

function getUploadError($code) {
    $errors = [
        UPLOAD_ERR_OK => 'There is no error, the file uploaded with success.',
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];
    return $errors[$code] ?? 'Unknown upload error.';
}

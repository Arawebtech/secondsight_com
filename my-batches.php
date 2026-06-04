<?php
session_start();
include('admin/include/db_config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's batch enrollments
$query = "
SELECT 
    b.id,
    b.batch_title,
    b.description,
    b.month_year,
    b.max_students,
    b.status,
    b.created_date,
    ube.enrolled_date,
    ube.status AS enrollment_status,
    COUNT(DISTINCT ube2.user_id) AS current_enrolled,
    0 AS lesson_count
FROM batch b
LEFT JOIN user_batch_enrollments ube ON b.id = ube.batch_id AND ube.user_id = ?
LEFT JOIN user_batch_enrollments ube2 ON b.id = ube2.batch_id AND ube2.status = 'Active'
WHERE ube.user_id = ? AND ube.status = 'Active'
GROUP BY b.id, b.batch_title, b.description, b.month_year, b.max_students, b.status, b.created_date, ube.enrolled_date, ube.status
ORDER BY ube.enrolled_date DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$batches = [];

while ($row = $result->fetch_assoc()) {
    $batches[] = $row;
}
$stmt->close();

// Use the BASE_URL constant from db_config.php
$base_url = BASE_URL;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Batches</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
      <?php include('include/head.php'); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
           background: linear-gradient(135deg, #f3c907 0%, #4f2877 100%);
            min-height: 100vh;
          
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1rem;
        }

        .batches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .batch-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .batch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .batch-title {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .batch-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .batch-info-item {
            display: flex;
            flex-direction: column;
        }

        .batch-info-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .batch-description {
            color: #555;
            line-height: 1.5;
            margin: 15px 0;
            font-size: 0.9rem;
        }

        .batch-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .batch-status.active {
            background-color: #d4edda;
            color: #155724;
        }

        .batch-status.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .batch-stats {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-top: 2px solid #eee;
            border-bottom: 2px solid #eee;
            margin: 15px 0;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            margin-top: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .btn-primary {
            background-color: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5568d3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .empty-state {
            background: white;
            padding: 60px 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .empty-state h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }

            .batches-grid {
                grid-template-columns: 1fr;
            }

            .batch-info {
                flex-direction: column;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include('include/header1.php'); ?>

    <div class="container">
        <div class="header">
            <h1>📚 My Batches</h1>
            <p>View and manage all your active batch enrollments</p>
        </div>

        <?php if (empty($batches)): ?>
            <div class="empty-state">
                <h2>No Batches Yet</h2>
                <p>You haven't enrolled in any batches yet. Use a batch code to join a live batch session.</p>
                <a href="<?= $base_url ?>profile.php" class="back-link">← Back to Profile</a>
            </div>
        <?php else: ?>
            <div class="batches-grid">
                <?php foreach ($batches as $batch): ?>
                    <div class="batch-card">
                        <div class="batch-title"><?= htmlspecialchars($batch['batch_title']) ?></div>
                        
                        <span class="batch-status <?= $batch['status'] === 'Active' ? 'active' : 'inactive' ?>">
                            <?= ucfirst($batch['status']) ?>
                        </span>

                        <?php if (!empty($batch['description'])): ?>
                            <div class="batch-description">
                                <?= htmlspecialchars(substr($batch['description'], 0, 100)) ?>
                                <?php if (strlen($batch['description']) > 100): ?>...<?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="batch-stats">
                            <div class="stat-item">
                                <div class="stat-number"><?= htmlspecialchars($batch['month_year']) ?></div>
                                <div class="stat-label">Month</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= htmlspecialchars($batch['lesson_count']) ?></div>
                                <div class="stat-label">Lessons</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?= htmlspecialchars($batch['current_enrolled']) ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                        </div>

                        <div class="batch-info">
                            <div class="batch-info-item">
                                <span class="batch-info-label">Max Capacity:</span>
                                <span><?= htmlspecialchars($batch['max_students']) ?> students</span>
                            </div>
                            <div class="batch-info-item">
                                <span class="batch-info-label">Joined:</span>
                                <span><?= date('M d, Y', strtotime($batch['enrolled_date'])) ?></span>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="<?= $base_url ?>profile.php" class="btn btn-primary">View Courses</a>
                            <a href="<?= $base_url ?>profile.php" class="btn btn-secondary">Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="<?= $base_url ?>profile.php" class="back-link">← Back to Profile</a>
        <?php endif; ?>
    </div>
</body>
</html>

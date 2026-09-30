<?php
$file = 'd:/xampp/htdocs/araweb/secondsight_com_backup/profile.php';
$content = file_get_contents($file);

// Replace the SQL Query
$old_query = "SELECT 
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
    (SELECT COUNT(*) FROM lesson_batch lb WHERE lb.batch_id = b.id) AS lesson_count
FROM batch b
LEFT JOIN user_batch_enrollments ube ON b.id = ube.batch_id AND ube.user_id = ?
LEFT JOIN user_batch_enrollments ube2 ON b.id = ube2.batch_id AND ube2.status = 'Active'
WHERE ube.user_id = ? AND ube.status = 'Active'
GROUP BY b.id, b.batch_title, b.description, b.month_year, b.max_students, b.status, b.created_date, ube.enrolled_date, ube.status
ORDER BY ube.enrolled_date DESC";

$new_query = "SELECT 
    b.id,
    b.batch_title,
    b.description,
    b.month_year,
    b.max_students,
    b.status,
    b.created_date,
    ube.enrolled_date,
    ube.status AS enrollment_status,
    bc.expiry_date AS batchcode_expiry,
    COUNT(DISTINCT ube2.user_id) AS current_enrolled,
    (SELECT COUNT(*) FROM lesson_batch lb WHERE lb.batch_id = b.id) AS lesson_count
FROM batch b
LEFT JOIN user_batch_enrollments ube ON b.id = ube.batch_id AND ube.user_id = ?
LEFT JOIN batchcode bc ON ube.batchcode_id = bc.id
LEFT JOIN user_batch_enrollments ube2 ON b.id = ube2.batch_id AND ube2.status = 'Active'
WHERE ube.user_id = ? AND ube.status = 'Active'
GROUP BY b.id, b.batch_title, b.description, b.month_year, b.max_students, b.status, b.created_date, ube.enrolled_date, ube.status, bc.expiry_date
ORDER BY ube.enrolled_date DESC";

$content = str_replace($old_query, $new_query, $content);

// Replace the Card UI
$old_card = '                    <?php foreach ($batches as $batch): ?>
                        <div class="batch-card" data-batchname="<?= htmlspecialchars(strtolower($batch[\'batch_title\'])) ?>">
                            <div class="batch-title"><?= htmlspecialchars($batch[\'batch_title\']) ?></div>
                            
                            <span class="batch-status <?= $batch[\'status\'] === \'Active\' ? \'active\' : \'inactive\' ?>">
                                <?= ucfirst($batch[\'status\']) ?>
                            </span>

                            <?php if (!empty($batch[\'description\'])): ?>
                                <div class="batch-description">
                                    <?= htmlspecialchars(substr($batch[\'description\'], 0, 100)) ?>
                                    <?php if (strlen($batch[\'description\']) > 100): ?>...<?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="batch-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?= htmlspecialchars($batch[\'month_year\']) ?></div>
                                    <div class="stat-label">Month</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?= htmlspecialchars($batch[\'lesson_count\']) ?></div>
                                    <div class="stat-label">Lessons</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?= htmlspecialchars($batch[\'current_enrolled\']) ?></div>
                                    <div class="stat-label">Students</div>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <a href="<?= $base_url ?>lesson.php?batch_id=<?= $batch[\'id\'] ?>" class="btn btn-primary">View Lessons</a>
                                <a href="<?= $base_url ?>profile.php" class="btn btn-secondary">Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>';

$new_card = '                    <?php foreach ($batches as $batch): 
                        $is_expired = false;
                        if (!empty($batch[\'batchcode_expiry\'])) {
                            $expiry_time = strtotime($batch[\'batchcode_expiry\'] . " 23:59:59");
                            if (time() > $expiry_time) {
                                $is_expired = true;
                            }
                        }
                    ?>
                        <div class="batch-card" data-batchname="<?= htmlspecialchars(strtolower($batch[\'batch_title\'])) ?>" <?= $is_expired ? \'style="opacity: 0.75; background: #fafafa;"\' : \'\' ?>>
                            <div class="batch-title"><?= htmlspecialchars($batch[\'batch_title\']) ?></div>
                            
                            <?php if ($is_expired): ?>
                                <span class="batch-status inactive" style="background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;">
                                    Expired
                                </span>
                            <?php else: ?>
                                <span class="batch-status <?= $batch[\'status\'] === \'Active\' ? \'active\' : \'inactive\' ?>">
                                    <?= ucfirst($batch[\'status\']) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($batch[\'description\'])): ?>
                                <div class="batch-description">
                                    <?= htmlspecialchars(substr($batch[\'description\'], 0, 100)) ?>
                                    <?php if (strlen($batch[\'description\']) > 100): ?>...<?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="batch-stats">
                                <div class="stat-item">
                                    <div class="stat-number"><?= htmlspecialchars($batch[\'month_year\']) ?></div>
                                    <div class="stat-label">Month</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?= htmlspecialchars($batch[\'lesson_count\']) ?></div>
                                    <div class="stat-label">Lessons</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number"><?= htmlspecialchars($batch[\'current_enrolled\']) ?></div>
                                    <div class="stat-label">Students</div>
                                </div>
                            </div>

                            <div class="action-buttons">
                                <?php if ($is_expired): ?>
                                    <button class="btn btn-primary" style="background: #999; cursor: not-allowed;" disabled>Access Expired</button>
                                <?php else: ?>
                                    <a href="<?= $base_url ?>lesson.php?batch_id=<?= $batch[\'id\'] ?>" class="btn btn-primary">View Lessons</a>
                                <?php endif; ?>
                                <a href="<?= $base_url ?>profile.php" class="btn btn-secondary">Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>';

$content = str_replace($old_card, $new_card, $content);
file_put_contents($file, $content);
echo "Done";

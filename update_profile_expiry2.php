<?php
$file = 'd:/xampp/htdocs/araweb/secondsight_com_backup/profile.php';
$content = file_get_contents($file);

$search1 = '<?php foreach ($batches as $batch): ?>
                    <div class="batch-card" data-batchname="<?= htmlspecialchars(strtolower($batch[\'batch_title\'])) ?>">';

$replace1 = '<?php foreach ($batches as $batch): 
                        $is_expired = false;
                        if (!empty($batch[\'batchcode_expiry\'])) {
                            $expiry_time = strtotime($batch[\'batchcode_expiry\'] . " 23:59:59");
                            if (time() > $expiry_time) {
                                $is_expired = true;
                            }
                        }
                    ?>
                    <div class="batch-card" data-batchname="<?= htmlspecialchars(strtolower($batch[\'batch_title\'])) ?>" <?= $is_expired ? \'style="opacity: 0.7; background: #fafafa; pointer-events: none;"\' : \'\' ?>>';

$content = str_replace($search1, $replace1, $content);

$search2 = '<span class="batch-status <?= $batch[\'status\'] === \'Active\' ? \'active\' : \'inactive\' ?>">
                            <?= ucfirst($batch[\'status\']) ?>
                        </span>';

$replace2 = '<?php if ($is_expired): ?>
                            <span class="batch-status inactive" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                                Expired
                            </span>
                        <?php else: ?>
                            <span class="batch-status <?= $batch[\'status\'] === \'Active\' ? \'active\' : \'inactive\' ?>">
                                <?= ucfirst($batch[\'status\']) ?>
                            </span>
                        <?php endif; ?>';

$content = str_replace($search2, $replace2, $content);


$search3 = '<a href="<?= $base_url ?>lesson.php?batch_id=<?= $batch[\'id\'] ?>" class="btn btn-primary">View Lessons</a>';

$replace3 = '<?php if ($is_expired): ?>
                                <button class="btn btn-primary" style="background: #999; cursor: not-allowed; pointer-events: auto;" disabled>Expired</button>
                            <?php else: ?>
                                <a href="<?= $base_url ?>lesson.php?batch_id=<?= $batch[\'id\'] ?>" class="btn btn-primary" style="pointer-events: auto;">View Lessons</a>
                            <?php endif; ?>';

$content = str_replace($search3, $replace3, $content);

file_put_contents($file, $content);
echo "Replaced successfully";

<?php
session_start();
include('admin/include/db_config.php');

$courses = [];
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', $_SESSION['cart']);
    $query = $conn->prepare("SELECT * FROM courses WHERE id IN ($ids)");
    $query->execute();
    $result = $query->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

$total_price = 0;
foreach ($courses as $course) {
    $total_price += $course['price'];
}
?>

<ul class="cart-items">
    <?php if (!empty($courses)) : ?>
        <?php foreach ($courses as $course) : ?>
            <li>
                <p><?= htmlspecialchars($course['s_name']) ?> - ₹<?= htmlspecialchars($course['price']) ?></p>
            </li>
        <?php endforeach; ?>
        <li><strong>Total: ₹<?= number_format($total_price, 2) ?></strong></li>
    <?php else : ?>
        <li>Your cart is empty!</li>
    <?php endif; ?>
</ul>

<?php  
include('admin/include/db_config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['quantities'] = [];
}

if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);

    if (!in_array($course_id, $_SESSION['cart'])) {
        // Add course to cart and set quantity to 1
        $_SESSION['cart'][] = $course_id;
        $_SESSION['quantities'][$course_id] = 1;
    }
    // No need to increment quantity here
}

// Handle removing items from the cart
if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    if (($key = array_search($remove_id, $_SESSION['cart'])) !== false) {
        unset($_SESSION['cart'][$key]);
        unset($_SESSION['quantities'][$remove_id]); // Remove quantity
    }
}

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
$subtotal = 0;

foreach ($courses as $course) {
    $quantity = $_SESSION['quantities'][$course['id']];
    $subtotal += $course['price'] * $quantity; // Calculate subtotal based on quantity
}
$total_price = $subtotal;

?>



<script>
function changeQuantity(courseId, increment) {
    const quantityInput = document.getElementById(quantity-${courseId});
    let currentQuantity = parseInt(quantityInput.value);

    if (increment === 1 && currentQuantity < 10) {
        currentQuantity += 1; // Increment
    } else if (increment === -1 && currentQuantity > 1) {
        currentQuantity -= 1; // Decrement
    }
    
    quantityInput.value = currentQuantity; // Update the input field
    updateSubtotal(courseId); // Update subtotal based on new quantity
    updateCartDropdown(); // Update cart dropdown after changing quantity
}

function updateSubtotal(courseId) {
    const quantityInput = document.getElementById(quantity-${courseId});
    const currentQuantity = parseInt(quantityInput.value);
    
    const price = <?= json_encode(array_column($courses, 'price', 'id')); ?>; // Fetch course prices
    const subtotalCell = document.getElementById(subtotal-${courseId});
    const subtotal = currentQuantity * price[courseId];
    subtotalCell.textContent = '₹' + subtotal.toFixed(2); // Update subtotal

    // Recalculate overall totals
    recalculateTotals();
}

function recalculateTotals() {
    let subtotal = 0;
    const subtotals = document.querySelectorAll('.subtotal');
    subtotals.forEach(subtotalCell => {
        subtotal += parseFloat(subtotalCell.textContent.replace('₹', '').replace(',', '')) || 0;
    });

    const gst = subtotal * 0.18; 
    const total = subtotal + gst;

    // Update order summary
    document.getElementById('order-subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('order-gst').textContent = gst.toFixed(2);
    document.getElementById('order-total').textContent = total.toFixed(2);
    updateCartDropdown(); // Update the dropdown total
}


function updateCartDropdown() {
    const dropdownTotal = document.getElementById('dropdown-total');
    const orderTotal = document.getElementById('order-total');
    dropdownTotal.textContent = orderTotal.textContent; // Sync dropdown total with order total
    updateCartIconCount(); // Update cart icon count
}

function updateCartIconCount() {
    const cartCount = <?= count($_SESSION['cart']); ?>;
    document.getElementById('cart-count').textContent = cartCount;
}
</script>
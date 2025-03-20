<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_name'] === $productName) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'product_name' => $productName,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    echo json_encode(['status' => 'success', 'cart' => $_SESSION['cart']]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
exit;
?>

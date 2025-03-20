<?php
session_start();
include('db/dbconn.php'); 

if (!isset($_SESSION['Id'])) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);

    if (!empty($orderId)) {

        $orderQuery = "SELECT * FROM orders_all WHERE id = '$orderId'";
        $orderResult = mysqli_query($conn, $orderQuery);

        if ($orderResult && mysqli_num_rows($orderResult) > 0) {
            $order = mysqli_fetch_assoc($orderResult);
            $productName = $order['product_name'];
            $quantity = $order['quantity'];

            $updateOrderQuery = "UPDATE orders_all SET status = 'confirmed' WHERE id = '$orderId'";
            if (mysqli_query($conn, $updateOrderQuery)) {
                $updateStockQuery = "UPDATE store SET stock = stock - $quantity WHERE productName = '$productName'";
                if (mysqli_query($conn, $updateStockQuery)) {
                    echo "<script>alert('Order confirmed and stock updated successfully!'); window.location.href='staffadminDashboard.php';</script>";
                } else {
                    echo "<script>alert('Error updating stock.'); window.location.href='staffadminDashboard.php';</script>";
                }
            } else {
                echo "<script>alert('Error confirming order.'); window.location.href='staffadminDashboard.php';</script>";
            }
        } else {
            echo "<script>alert('Order not found.'); window.location.href='staffadminDashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid order ID.'); window.location.href='staffadminDashboard.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='staffadminDashboard.php';</script>";
}
?>
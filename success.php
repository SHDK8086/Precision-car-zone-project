<?php
include('db/dbconn.php');
session_start();

if (!isset($_SESSION['Id']) || !isset($_SESSION['email'])) {
    header("location:login.php");
    exit;
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$booking_id) {
    header("location:profile.php");
    exit;
}

$update_sql = "UPDATE bookings SET payment_status = 'paid' WHERE booking_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $booking_id);
$update_stmt->execute();

if ($update_stmt->affected_rows > 0) {
    $payment_status_message = "Payment status updated to 'Paid'.";
} else {
    $payment_status_message = "Failed to update payment status. Please try again.";
}

$update_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link rel="stylesheet" href="update.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="75" height="75" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                            </svg>
                        </div>
                        <h2 class="card-title">Payment Successful!</h2>
                        <p class="card-text">Your booking payment has been processed successfully.</p>
                        <p class="card-text text-success"><?php echo $payment_status_message; ?></p>
                        <hr>
                        <div class="mt-4">
                            <a href="profile.php" class="btn btn-primary">Return to Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

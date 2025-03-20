<?php 
include('db/dbconn.php'); 
session_start();  

if(!isset($_SESSION['Id']) && !isset($_SESSION['email'])){
    header("location:login.php");
    exit;
}

$user_id = $_SESSION['Id'];  

if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id']; 
    
    $sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, 
                   b.status, b.price, v.vehicle_number, v.vehicle_model, v.vehicle_year,
                   b.user_id, b.progress_status
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            WHERE b.user_id = ? AND b.booking_id = ?";
     
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        header("location:booking_history.php"); 
        exit;
    }
} else {
    header("location:booking_history.php");
    exit;
}

$price = isset($booking['price']) ? (float) $booking['price'] : 0.00;
$formatted_price = number_format($price, 2, '.', '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .receipt-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .receipt-header {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }
        .label {
            font-weight: 600;
            color: #555;
        }
        .price-highlight {
            font-size: 1.2em;
            font-weight: bold;
            color: #28a745;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container receipt-container">
        <div class="receipt-header">
            <h3 class="receipt-title">Service Report</h3>
            <p class="text-muted">Summary of your service details</p>
        </div>
        
        <div class="receipt-body">
            <p><span class="label">Booking ID:</span> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
            <p><span class="label">Vehicle:</span> <?php echo htmlspecialchars($booking['vehicle_number'] . ' - ' . $booking['vehicle_model'] . ' (' . $booking['vehicle_year'] . ')'); ?></p>
            <p><span class="label">Service Type:</span> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $booking['service_type']))); ?></p>
            <p><span class="label">Booking Date:</span> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
            <p><span class="label">Booking Time:</span> <?php echo htmlspecialchars($booking['booking_time']); ?></p>
            <p><span class="label">Status:</span> <?php echo htmlspecialchars(ucfirst($booking['status'])); ?></p>
            
            <hr>
            <h5>Service Charges</h5>
            <div class="price-highlight">
                <span class="label">Total Price:</span> Rs. <?php echo htmlspecialchars($formatted_price); ?> 
            </div>
            <p class="text-muted mt-2">
                <?php if (strtolower($booking['status']) == 'completed'): ?>
                    Thank you for your payment.
                <?php else: ?>
                    Final price will be confirmed upon service completion.
                <?php endif; ?>
            </p>
        </div>
        
        <div class="receipt-footer">
            <a href="booking_history.php" class="btn btn-primary">Back to Dashboard</a>
            <button onclick="window.print()" class="btn btn-outline-secondary ms-2">Print Report</button>
        </div>
    </div>
</body>
</html>

<?php 
$stmt->close();
$conn->close();
?>

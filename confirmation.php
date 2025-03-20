<?php

include("db/dbconn.php");


$bookingDetails = null;
$errorMessage = '';


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $bookingId = $_GET['id'];
    

    $sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, b.status,
                c.user_id, c.name, c.email, c.contact, c.address,
                v.vehicle_number, v.vehicle_model, v.vehicle_year
            FROM bookings b
            JOIN customers c ON b.customer_id = c.customer_id
            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            WHERE b.booking_id = ?";
    

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $bookingDetails = $result->fetch_assoc();
    } else {
        $errorMessage = 'Booking not found';
    }
    
    $stmt->close();
} else {
    $errorMessage = 'Invalid booking ID';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link href="assets/Logo.svg" rel="icon">
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .booking-details {
            margin-top: 20px;
        }
        .booking-section {
            margin-bottom: 20px;
        }
        .booking-section h3 {
            color: #3674B5;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 8px;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-print {
            background-color: #3674B5;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn-home {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="confirmation-container form">
        <h2><i class="fa fa-check-circle"></i> Booking Confirmation</h2>
        
        <?php if ($errorMessage): ?>
            <div class="error-message">
                <?php echo $errorMessage; ?>
            </div>
            <a href="index.html" class="btn-home"><i class="fa fa-home"></i> Return to Home</a>
        <?php elseif ($bookingDetails): ?>
            <div class="success-message">
                Your booking has been confirmed! Your booking ID is: <strong><?php echo $bookingDetails['booking_id']; ?></strong>
            </div>
            
            <div class="booking-details">
                <div class="booking-section">
                    <h3><i class="fa fa-user"></i> Customer Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">User ID:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['user_id']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Name:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['contact']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['address']); ?></span>
                    </div>
                </div>
            </div>
                
                <div class="booking-section">
                    <h3><i class="fa fa-car"></i> Vehicle Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Vehicle Number:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['vehicle_number']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Model:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['vehicle_model']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Year:</span>
                        <span><?php echo htmlspecialchars($bookingDetails['vehicle_year']); ?></span>
                    </div>
                </div>
                
                <div class="booking-section">
                    <h3><i class="fa fa-calendar"></i> Booking Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Service Type:</span>
                        <span><?php echo ucwords(str_replace('_', ' ', $bookingDetails['service_type'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date:</span>
                        <span><?php echo date('F j, Y', strtotime($bookingDetails['booking_date'])); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time:</span>
                        <span><?php echo $bookingDetails['booking_time']; ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span><?php echo ucfirst($bookingDetails['status']); ?></span>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <button onclick="window.print()" class="btn-print"><i class="fa fa-print"></i> Print</button>
                <a href="index.php" class="btn-home"><i class="fa fa-home"></i> Return to Home</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
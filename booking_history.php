<?php 
include('db/dbconn.php'); 
session_start(); 

if(!isset($_SESSION['Id']) && !isset($_SESSION['email'])){
    header("location:login.php");
    exit;
}

// Fetch customer's completed bookings
$customer_id = $_SESSION['Id']; // Assuming the session contains the logged-in user's ID
$completed_progress_status = 6; // Define the progress_status that represents completed bookings

// Check if progress_status column exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'progress_status'");
$use_progress_column = mysqli_num_rows($check_column) > 0;

// SQL query to fetch completed bookings for the logged-in user
if ($use_progress_column) {
    // If progress_status column exists, use it for the query
    $sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, 
                   b.progress_status, v.vehicle_number, v.vehicle_model, v.vehicle_year
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            WHERE b.customer_id = ? AND b.progress_status = ?
            ORDER BY b.booking_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $customer_id, $completed_progress_status); // Binding parameters
} else {
    // Fallback to status field if progress_status doesn't exist
    $status_completed = 'completed';
    $sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, 
                   b.status, v.vehicle_number, v.vehicle_model, v.vehicle_year
            FROM bookings b
            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
            WHERE b.customer_id = ? AND b.status = ?
            ORDER BY b.booking_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $customer_id, $status_completed); // Binding parameters
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="Dashboard.css">
</head>
<body>
    <div class="container mt-4">
        <h3>Your Completed Bookings</h3>
        <p>Below are the details of all your completed service bookings.</p>
        
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Vehicle</th>
                        <th>Service Type</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['vehicle_number'] . ' - ' . $row['vehicle_model']); ?></td>
                        <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $row['service_type']))); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">You don't have any completed bookings yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
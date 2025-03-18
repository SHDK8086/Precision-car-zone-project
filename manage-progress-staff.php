<?php
include('db/dbconn.php'); 
session_start();

if(!isset($_SESSION['Id']) && !isset($_SESSION['email'])){
    header("location:login.php");
    exit;
}

$status_to_progress = [
  'pending' => 0,
  'check_in' => 1,
  'inspection' => 2,
  'maintenance' => 3,
  'cleaning' => 4,
  'billing' => 5,
  'completed' => 6
];

$progress_to_status = array_flip($status_to_progress);

$progress_labels = [
  0 => "Pending",
  1 => "Check-In",
  2 => "Pre-Service Inspection",
  3 => "Mechanical & Maintenance Work",
  4 => "Exterior & Interior Cleaning",
  5 => "Final Inspection",
  6 => "Service Completion and Payment"
];

if (isset($_POST['update_progress'])) {
    $booking_id = $_POST['booking_id'];
    $progress_status = (int)$_POST['progress_status'];

    $booking_query = "SELECT price, payment_status FROM bookings WHERE booking_id = ?";
    $booking_stmt = $conn->prepare($booking_query);
    $booking_stmt->bind_param("i", $booking_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    $booking_row = $booking_result->fetch_assoc();
    $price = $booking_row['price'];
    $payment_status = isset($booking_row['payment_status']) ? $booking_row['payment_status'] : 'unpaid';

    if ($progress_status == 6 && ($price == 0 || $payment_status != 'paid')) {
        if ($price == 0) {
            $error_message = "Staff admin has not added the price. Cannot update to 'Service Completion and Payment'.";
        } else {
            $error_message = "Payment status must be 'paid' before updating to 'Service Completion and Payment'.";
        }
    } else {
        $check_column = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'progress_status'");

        if(mysqli_num_rows($check_column) > 0) {
            $status_value = $progress_to_status[$progress_status];
            $update_query = "UPDATE bookings SET progress_status = ?, status = ? WHERE booking_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("isi", $progress_status, $status_value, $booking_id);
        } else {
            $status_value = $progress_to_status[$progress_status];
            $update_query = "UPDATE bookings SET status = ? WHERE booking_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $status_value, $booking_id);
        }

        if ($stmt->execute()) {
            $check_history = mysqli_query($conn, "SHOW TABLES LIKE 'booking_progress_history'");
            if(mysqli_num_rows($check_history) > 0) {
                $staff_id = $_SESSION['Id'];
                $history_value = (mysqli_num_rows($check_column) > 0) ? $progress_status : $progress_to_status[$progress_status];

                $history_query = "INSERT INTO booking_progress_history (booking_id, progress_status, updated_by, update_time) 
                                VALUES (?, ?, ?, NOW())";
                $history_stmt = $conn->prepare($history_query);
                $history_stmt->bind_param("isi", $booking_id, $history_value, $staff_id);
                $history_stmt->execute();
                $history_stmt->close();
            }

            $success_message = "Progress updated successfully!";
            echo '<meta http-equiv="refresh" content="1;url='.$_SERVER['PHP_SELF'].'">';
        } else {
            $error_message = "Error updating progress: " . $conn->error;
        }

        $stmt->close();
    }
    $booking_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Progress - Staff Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="manage-progress.css">
    
</head>
<body>
    <div class="container mt-4 content">
        <h3>Manage Booking Progress</h3>
        <p>Update the status of customer bookings assigned to you</p>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="card mt-4 p-3">
            <div class="table-responsive">
                <h5 class="mt-2 mb-3">Assigned Bookings</h5>
                <?php
                $bookings_check = mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");
                $customers_check = mysqli_query($conn, "SHOW TABLES LIKE 'customers'");
                $vehicles_check = mysqli_query($conn, "SHOW TABLES LIKE 'vehicles'");

                $tables_exist = mysqli_num_rows($bookings_check) > 0 && mysqli_num_rows($customers_check) > 0 && mysqli_num_rows($vehicles_check) > 0;

                if(!$tables_exist) {
                    echo '<div class="alert alert-warning">Required tables (bookings, customers, vehicles) not found.</div>';
                } else {
                    $payment_status_check = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'payment_status'");
                    $has_payment_status = mysqli_num_rows($payment_status_check) > 0;
                    
                    $progress_column_check = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'progress_status'");
                    $use_progress_column = mysqli_num_rows($progress_column_check) > 0;

                    $staff_id = $_SESSION['Id'];

                    $sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, ";
                    if($use_progress_column) {
                        $sql .= "b.progress_status, ";
                    }
                    $sql .= "b.status, b.price";
                    
                    if($has_payment_status) {
                        $sql .= ", b.payment_status";
                    }
                    
                    $sql .= ", c.name, c.email, c.contact, v.vehicle_number, v.vehicle_model, v.vehicle_year 
                             FROM bookings b
                             JOIN customers c ON b.customer_id = c.customer_id
                             JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                             WHERE b.assigned_staff = '$staff_id'
                             ORDER BY b.booking_date DESC";

                    $result = mysqli_query($conn, $sql);

                    if(!$result) {
                        echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
                    } elseif(mysqli_num_rows($result) > 0) {
                        echo '<table id="staffBookingsTable" class="table table-striped">';
                        echo '<thead><tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Progress Status</th>
                                <th>Price</th>';
                        if($has_payment_status) {
                            echo '<th>Payment</th>';
                        }
                        echo '<th>Update</th>
                              </tr></thead><tbody>';

                        while($row = mysqli_fetch_assoc($result)) {
                            $progress_status = 0;
                            if($use_progress_column && isset($row['progress_status'])) {
                                $progress_status = (int)$row['progress_status'];
                            } else {
                                $status = strtolower($row['status']);
                                $progress_status = isset($status_to_progress[$status]) ? $status_to_progress[$status] : 0;
                            }

                            $payment_status = $has_payment_status ? $row['payment_status'] : 'unpaid';
                            $payment_badge_class = '';
                            if($payment_status == 'paid') {
                                $payment_badge_class = 'payment-paid';
                            } else if($payment_status == 'pending') {
                                $payment_badge_class = 'payment-pending';
                            } else {
                                $payment_badge_class = 'payment-unpaid';
                            }

                            $service_display = ucwords(str_replace('_', ' ', $row['service_type']));

                            echo '<tr>';
                            echo '<td>' . $row['booking_id'] . '</td>';
                            echo '<td>' . htmlspecialchars($row['name']) . '<br><small class="text-muted">' . htmlspecialchars($row['email']) . '</small></td>';
                            echo '<td>' . htmlspecialchars($row['vehicle_number'] . ' - ' . $row['vehicle_model']) . '</td>';
                            echo '<td>' . $service_display . '</td>';
                            echo '<td>' . date('M d, Y', strtotime($row['booking_date'])) . ' at ' . $row['booking_time'] . '</td>';
                            echo '<td><span class="progress-badge badge-step-' . $progress_status . '">' . $progress_labels[$progress_status] . '</span></td>';
                            echo '<td>' . ($row['price'] == 0 ? '<span class="text-danger">Not Set</span>' : 'Rs. ' . $row['price']) . '</td>';
                            
                            if($has_payment_status) {
                                echo '<td><span class="payment-status ' . $payment_badge_class . '">' . ucfirst($payment_status) . '</span></td>';
                            }
                            
                            echo '<td>
                                    <form method="post" class="progress-form" onsubmit="return validateProgressUpdate(' . $row['booking_id'] . ', ' . $row['price'] . ', \'' . $payment_status . '\')">
                                        <input type="hidden" name="booking_id" value="' . $row['booking_id'] . '">
                                        <div class="input-group">
                                            <select name="progress_status" class="form-select form-select-sm progress-select">';
                                                foreach($progress_labels as $key => $label) {
                                                    $selected = $progress_status == $key ? 'selected' : '';
                                                    echo "<option value='$key' $selected>$label</option>";
                                                }
                            echo '          </select>
                                            <button type="submit" name="update_progress" class="btn btn-sm btn-primary">Update</button>
                                        </div>
                                    </form>
                                  </td>';
                            echo '</tr>';
                        }

                        echo '</tbody></table>';
                    } else {
                        echo '<div class="alert alert-info">No bookings assigned to you.</div>';
                    }
                }
                ?>
            </div>
        </div>

        <div class="card mt-4 p-4">
            <h5 class="mb-3">Service Progress Stages Guide</h5>
            <div class="progress-guide">
                <div class="row">
                    <?php foreach ($progress_labels as $key => $label): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="guide-item">
                            <div class="guide-icon badge-step-<?php echo $key; ?>">
                                <i class="bi bi-<?php echo $progress_icons[$key]; ?>"></i>
                            </div>
                            <div class="guide-content">
                                <h6><?php echo $label; ?></h6>
                                <p class="small">
                                    <?php 
                                    $descriptions = [
                                        "Initial booking received, awaiting check-in.",
                                        "Vehicle received at service center.",
                                        "Thorough inspection of vehicle condition.",
                                        "Service work and repairs in progress.",
                                        "Vehicle cleaning and detailing.",
                                        "Final quality check and pricing.",
                                        "Service completed and payment received."
                                    ];
                                    echo $descriptions[$key];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('staffBookingsTable')) {
            $('#staffBookingsTable').DataTable({
                responsive: true,
                order: [[4, 'desc']], 
                language: {
                    search: "Search bookings:",
                    lengthMenu: "Show _MENU_ bookings per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ bookings",
                    infoEmpty: "Showing 0 to 0 of 0 bookings",
                    infoFiltered: "(filtered from _MAX_ total bookings)"
                },
                columnDefs: [
                    { orderable: false, targets: [8] } 
                ]
            });
        }
        
        if (sessionStorage.getItem('progressUpdated') === 'true') {
            const notification = document.createElement('div');
            notification.className = 'alert alert-success';
            notification.textContent = 'Progress updated successfully!';
            document.querySelector('.content').prepend(notification);
            
            setTimeout(() => {
                notification.remove();
                sessionStorage.removeItem('progressUpdated');
            }, 3000);
        }
    });

    function validateProgressUpdate(bookingId, price, paymentStatus) {
        const progressSelect = document.querySelector(`form[onsubmit="return validateProgressUpdate(${bookingId}, ${price}, '${paymentStatus}')"] select[name="progress_status"]`);
        const progressStatus = progressSelect.value;

        if (progressStatus == 6) {
            if (price == 0) {
                alert("Staff admin has not added the price. Cannot update to 'Service Completion and Payment'.");
                return false; 
            }
            
            if (paymentStatus != 'paid') {
                alert("Payment status must be 'paid' before updating to 'Service Completion and Payment'.");
                return false; 
            }
            
            if (!confirm('Setting status to "Service Completion and Payment" will mark this booking as PAID. Continue?')) {
                return false; 
            }
        }
        
        sessionStorage.setItem('progressUpdated', 'true');
        
        return true;
    }
    </script>
</body>
</html>
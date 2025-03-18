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

$progress_icons = [
  0 => "hourglass",
  1 => "calendar-check",  
  2 => "search",
  3 => "tools",
  4 => "droplet",
  5 => "clipboard-check",
  6 => "check-circle"
];

if (isset($_POST['update_progress'])) {
    $booking_id = $_POST['booking_id'];
    $progress_status = (int)$_POST['progress_status'];
    $error = false;
    
    if ($progress_status == 5 && (!isset($_POST['price']) || empty($_POST['price']))) {
        $error_message = "Price is required for Final Inspection stage. Please enter a price.";
        $error = true;
    }
    
    if ($progress_status == 6) {
        $check_price_query = "SELECT price FROM bookings WHERE booking_id = ?";
        $check_stmt = $conn->prepare($check_price_query);
        $check_stmt->bind_param("i", $booking_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $booking_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if (empty($booking_data['price'])) {
            $error_message = "Cannot complete service without setting a price. Please go back to Final Inspection stage and set a price first.";
            $error = true;
        }
    }
    
    if (!$error) {
        $price = null;
        if (isset($_POST['price']) && !empty($_POST['price'])) {
            $price = $_POST['price'];
        }
        
        $check_column = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'progress_status'");
        
        if(mysqli_num_rows($check_column) > 0) {
            if ($progress_status == 6) {
                $status_value = 'completed';
                $payment_status_value = 'paid';
                
                if ($price !== null) {
                    $update_query = "UPDATE bookings SET progress_status = ?, status = ?, payment_status = ?, price = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("isssi", $progress_status, $status_value, $payment_status_value, $price, $booking_id);
                } else {
                    $update_query = "UPDATE bookings SET progress_status = ?, status = ?, payment_status = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("issi", $progress_status, $status_value, $payment_status_value, $booking_id);
                }
            } else {
                if ($price !== null) {
                    $update_query = "UPDATE bookings SET progress_status = ?, price = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("isi", $progress_status, $price, $booking_id);
                } else {
                    $update_query = "UPDATE bookings SET progress_status = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("ii", $progress_status, $booking_id);
                }
            }
        } else {
            $status_value = $progress_to_status[$progress_status];
            
            if ($progress_status == 6) {
                $payment_status_value = 'paid';
                
                if ($price !== null) {
                    $update_query = "UPDATE bookings SET status = ?, payment_status = ?, price = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("sssi", $status_value, $payment_status_value, $price, $booking_id);
                } else {
                    $update_query = "UPDATE bookings SET status = ?, payment_status = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("ssi", $status_value, $payment_status_value, $booking_id);
                }
            } else {
                if ($price !== null) {
                    $update_query = "UPDATE bookings SET status = ?, price = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("ssi", $status_value, $price, $booking_id);
                } else {
                    $update_query = "UPDATE bookings SET status = ? WHERE booking_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("si", $status_value, $booking_id);
                }
            }
        }
        
        if ($stmt->execute()) {
            $check_history = mysqli_query($conn, "SHOW TABLES LIKE 'booking_progress_history'");
            if(mysqli_num_rows($check_history) > 0) {
                $staff_id = $_SESSION['Id'];
                
                if(mysqli_num_rows($check_column) > 0) {
                    $history_value = $progress_status; 
                } else {
                    $history_value = $progress_to_status[$progress_status];
                }
                
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Progress - Precision Car Zone</title>
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
    
    
    <div class="content">
            <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3>Manage Booking Progress</h3>
                <p>Track and update the service status of customer bookings</p>
            </div>
            <div class="d-none d-md-block">
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="table-responsive">
                <h5 class="mt-4 mb-4">Current Bookings & Service Status</h5>
                
                <?php
                $bookings_check = mysqli_query($conn, "SHOW TABLES LIKE 'bookings'");
                $customers_check = mysqli_query($conn, "SHOW TABLES LIKE 'customers'");
                $vehicles_check = mysqli_query($conn, "SHOW TABLES LIKE 'vehicles'");
                
                $tables_exist = mysqli_num_rows($bookings_check) > 0 && 
                               mysqli_num_rows($customers_check) > 0 && 
                               mysqli_num_rows($vehicles_check) > 0;
                
                if(!$tables_exist) {
                    echo '<div class="alert alert-warning">';
                    echo '<h5>Required tables not found</h5>';
                    echo '<p>The necessary tables (bookings, customers, vehicles) do not exist in your database.</p>';
                    echo '</div>';
                } else {
                    $progress_column_check = mysqli_query($conn, "SHOW COLUMNS FROM bookings LIKE 'progress_status'");
                    $use_progress_column = mysqli_num_rows($progress_column_check) > 0;
                    
                    $sql = "SELECT b.booking_id, b.service_type, b.booking_date, b.booking_time, ";
                    
                    if($use_progress_column) {
                        $sql .= "b.progress_status, ";
                    }
                    
                    $sql .= "b.status, b.price, b.payment_status, c.name, c.email, c.contact, 
                            v.vehicle_number, v.vehicle_model, v.vehicle_year 
                            FROM bookings b
                            JOIN customers c ON b.customer_id = c.customer_id
                            JOIN vehicles v ON b.vehicle_id = v.vehicle_id
                            ORDER BY b.booking_date DESC";
                    
                    $result = mysqli_query($conn, $sql);
                    
                    if(!$result) {
                        echo '<div class="alert alert-danger">';
                        echo '<p>Error executing query: ' . mysqli_error($conn) . '</p>';
                        echo '</div>';
                    } else if(mysqli_num_rows($result) > 0) {
                        ?>
                        <table id="progressDataTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Progress Status</th>
                                    <th>Payment</th>
                                    <th>Price</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while($row = mysqli_fetch_assoc($result)) {
                                    if($use_progress_column && isset($row['progress_status'])) {
                                        $progress_status = isset($row['progress_status']) ? (int)$row['progress_status'] : 0;
                                    } else {
                                        $status = isset($row['status']) ? strtolower($row['status']) : 'pending';
                                        $progress_status = isset($status_to_progress[$status]) ? $status_to_progress[$status] : 0;
                                    }
                                    
                                    $payment_status = isset($row['payment_status']) ? strtolower($row['payment_status']) : 'unpaid';
                                    
                                    $service_display = ucwords(str_replace('_', ' ', $row['service_type']));
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $row['booking_id']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle">
                                                    <?php echo substr($row['name'], 0, 1); ?>
                                                </div>
                                                <div class="ms-2">
                                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-envelope-fill me-1"></i><?php echo htmlspecialchars($row['email']); ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="bi bi-telephone-fill me-1"></i><?php echo htmlspecialchars($row['contact']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="bi bi-car-front me-1"></i>
                                                <strong><?php echo htmlspecialchars($row['vehicle_number']); ?></strong>
                                            </div>
                                            <div class="small text-muted">
                                                <?php echo htmlspecialchars($row['vehicle_model']); ?> (<?php echo $row['vehicle_year']; ?>)
                                            </div>
                                        </td>
                                        <td>
                                            <span class="service-badge">
                                                <?php echo $service_display; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-badge">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?php echo date('M d, Y', strtotime($row['booking_date'])); ?>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-clock me-1"></i>
                                                <?php echo $row['booking_time']; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress-tracker-small d-block d-md-none">
                                                <div class="progress-step-single">
                                                    <div class="step-icon <?php echo $progress_status == 6 ? 'completed' : ($progress_status >= 0 ? 'active' : ''); ?>">
                                                        <i class="bi bi-<?php echo $progress_icons[$progress_status]; ?>"></i>
                                                    </div>
                                                    <div class="small fw-bold mt-1">
                                                        <?php echo $progress_status == 6 ? 'Completed' : $progress_labels[$progress_status]; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="progress-tracker d-none d-md-flex">
                                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                                    <div class="progress-step <?php echo $i < $progress_status ? 'completed' : ($i == $progress_status ? 'active' : ''); ?>">
                                                        <div class="step-icon">
                                                            <i class="bi bi-<?php echo $progress_icons[$i]; ?>"></i>
                                                        </div>
                                                        <?php if ($i == $progress_status): ?>
                                                            <div class="step-label"><?php echo $progress_labels[$i]; ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                            
                                            <span class="progress-badge badge-step-<?php echo $progress_status; ?> d-none">
                                                <?php echo $progress_labels[$progress_status]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $payment_badge_class = '';
                                            switch($payment_status) {
                                                case 'paid':
                                                    $payment_badge_class = 'badge-paid';
                                                    break;
                                                case 'unpaid':
                                                    $payment_badge_class = 'badge-unpaid';
                                                    break;
                                                default:
                                                    $payment_badge_class = 'badge-pending';
                                                    break;
                                            }
                                            ?>
                                            <span class="payment-badge <?php echo $payment_badge_class; ?>">
                                                <i class="bi bi-<?php echo $payment_status == 'paid' ? 'credit-card-2-front-fill' : 'hourglass-split'; ?> me-1"></i>
                                                <?php echo ucfirst($payment_status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['price'])): ?>
                                                <span class="price-tag">Rs. <?php echo $row['price'], 2; ?></span>
                                            <?php else: ?>
                                                <span class="price-placeholder">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" action="" class="progress-form">
                                                <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                                <div class="input-group">
                                                    <select name="progress_status" class="form-select form-select-sm progress-select" data-current="<?php echo $progress_status; ?>">
                                                        <?php foreach ($progress_labels as $key => $label): ?>
                                                        <option value="<?php echo $key; ?>" <?php if ($progress_status == $key) echo 'selected'; ?>>
                                                            <?php echo $label; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" name="update_progress" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-arrow-clockwise me-1"></i> Update
                                                    </button>
                                                </div>
                                                <div class="price-input mt-2 <?php echo ($progress_status == 5) ? 'visible' : ''; ?>">
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rs. </span>
                                                        <input type="text" name="price" class="form-control form-control-sm" placeholder="Enter price" value="<?php echo htmlspecialchars($row['price'] ?? ''); ?>">
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                    } else {
                        echo '<div class="alert alert-info">';
                        echo '<i class="bi bi-info-circle me-2"></i>';
                        echo 'No bookings found in the system. New bookings will appear here.';
                        echo '</div>';
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
            $('#progressDataTable').DataTable({
                responsive: true,
                pageLength: 7,
                lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
                dom: '<"top"lf>rt<"bottom"ip>',
                language: {
                    search: "Search bookings:",
                    lengthMenu: "Show _MENU_ bookings",
                    info: "Displaying _START_ to _END_ of _TOTAL_ bookings",
                    infoEmpty: "No bookings found",
                    zeroRecords: "No matching bookings found"
                },
                columnDefs: [
                    { orderable: false, targets: [8] } 
                ]
            });
            
            function togglePriceInput(selectElement) {
                const form = selectElement.closest('form');
                const priceInput = form.querySelector('.price-input');
                const currentValue = parseInt(selectElement.value);
                const previousValue = parseInt(selectElement.getAttribute('data-current'));
                
                if (currentValue === 5) {
                    priceInput.classList.add('visible');
                    const priceField = priceInput.querySelector('input[name="price"]');
                    
                    priceField.setAttribute('required', 'required');
                    
                    priceField.style.animation = 'flash 1s 3';
                } else {
                    priceInput.classList.remove('visible');
                    const priceField = priceInput.querySelector('input[name="price"]');
                    
                    if (currentValue !== 5) {
                        priceField.removeAttribute('required');
                    }
                }
            }
            
            const progressSelects = document.querySelectorAll('.progress-select');
            progressSelects.forEach(select => {
                togglePriceInput(select);
                
                select.addEventListener('change', function() {
                    togglePriceInput(this);
                    
                    if (parseInt(this.value) === 6) {
                        const form = this.closest('form');
                        const priceInput = form.querySelector('input[name="price"]');
                        
                        if (!priceInput.value || priceInput.value.trim() === '') {
                            Swal.fire({
                                title: 'Missing Price',
                                text: 'Cannot complete service without setting a price. Please go back to Final Inspection stage and set a price first.',
                                icon: 'warning',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3f51b5'
                            });
                            
                            this.value = this.getAttribute('data-current');
                            return;
                        }
                    }
                });
            });
            
            const progressForms = document.querySelectorAll('.progress-form');
            progressForms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    const progressSelect = this.querySelector('.progress-select');
                    const priceInput = this.querySelector('input[name="price"]');
                    const currentProgress = parseInt(progressSelect.value);
                    
                    if (currentProgress === 6) {
                        if (!priceInput.value || priceInput.value.trim() === '') {
                            event.preventDefault();
                            
                            Swal.fire({
                                title: 'Missing Price',
                                text: 'Cannot complete service without setting a price. Please go back to Final Inspection stage and set a price first.',
                                icon: 'warning',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3f51b5'
                            });
                            
                            return false;
                        }
                        
                        event.preventDefault();
                        
                        Swal.fire({
                            title: 'Confirm Completion',
                            text: 'Setting status to "Service Completion and Payment" will mark this booking as PAID. Continue?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Complete It',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#4caf50',
                            cancelButtonColor: '#f44336'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                        
                        return false;
                    }
                    
                    if (currentProgress === 5 && (!priceInput.value || priceInput.value.trim() === '')) {
                        event.preventDefault();
                        
                        Swal.fire({
                            title: 'Price Required',
                            text: 'Price is required for Final Inspection stage. Please enter a price.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3f51b5'
                        });
                        
                        return false;
                    }
                });
            });
            
            window.toggleSidebar = function() {
                var sidebar = document.getElementById("sidebar");
                sidebar.classList.toggle("open");
                
                let overlay = document.getElementById('sidebar-overlay');
                
                if (sidebar.classList.contains('open')) {
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.id = 'sidebar-overlay';
                        overlay.style.position = 'fixed';
                        overlay.style.top = '0';
                        overlay.style.left = '0';
                        overlay.style.width = '100%';
                        overlay.style.height = '100%';
                        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
                        overlay.style.zIndex = '998';
                        overlay.style.opacity = '0';
                        overlay.style.transition = 'opacity 0.3s ease';
                        document.body.appendChild(overlay);
                        
                        overlay.addEventListener('click', function() {
                            toggleSidebar();
                        });
                        
                        setTimeout(() => {
                            overlay.style.opacity = '1';
                        }, 10);
                    }
                } else {
                    if (overlay) {
                        overlay.style.opacity = '0';
                        setTimeout(() => {
                            document.body.removeChild(overlay);
                        }, 300);
                    }
                }
            };
            
            <?php if (isset($success_message)): ?>
            setTimeout(() => {
                Swal.fire({
                    title: 'Success!',
                    text: '<?php echo $success_message; ?>',
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }, 500);
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            setTimeout(() => {
                Swal.fire({
                    title: 'Error',
                    text: '<?php echo $error_message; ?>',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3f51b5'
                });
            }, 500);
            <?php endif; ?>
        });
    </script>
</body>
</html>
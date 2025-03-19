<?php
session_start();
include('db/dbconn.php'); 

if (!isset($_SESSION['Id'])) {
    header("location: login.php");
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
$userRole = strtolower($_SESSION['user_type'] ?? '');

$redirectPages = [
    'user' => 'profile.php',
    'admin' => 'SuperAdminDashboard.php',
    'staff' => 'staffDashboard.php',
    'staff admin' => 'staffadminDashboard.php'
];

if (isset($redirectPages[$userRole]) && $currentPage !== $redirectPages[$userRole]) {
    header("location: " . $redirectPages[$userRole]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_staff'])) {
    $bookingId = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $assignedStaff = mysqli_real_escape_string($conn, $_POST['assigned_staff']);

    if (!empty($bookingId) && !empty($assignedStaff)) {
        $updateQuery = "UPDATE bookings SET assigned_staff = '$assignedStaff', status = 'confirmed' WHERE booking_id = '$bookingId'";
        if (mysqli_query($conn, $updateQuery)) {
            echo "<script>
                    setTimeout(function() {
                        showSuccessAlert('Staff assigned successfully! The booking status has been updated to Confirmed.');
                    }, 500);
                  </script>";
        } else {
            echo "<script>
                    setTimeout(function() {
                        showErrorAlert('Error assigning staff. Please try again.');
                    }, 500);
                  </script>";
        }
    }
}

$sqlPending = "SELECT COUNT(*) AS pending_count FROM bookings WHERE status = 'pending'";
$resultPending = mysqli_query($conn, $sqlPending);
$pendingCount = mysqli_fetch_assoc($resultPending)['pending_count'];

$sqlConfirmed = "SELECT COUNT(*) AS confirmed_count FROM bookings WHERE status = 'confirmed'";
$resultConfirmed = mysqli_query($conn, $sqlConfirmed);
$confirmedCount = mysqli_fetch_assoc($resultConfirmed)['confirmed_count'];

$sqlCompleted = "SELECT COUNT(*) AS completed_count FROM bookings WHERE status = 'completed'";
$resultCompleted = mysqli_query($conn, $sqlCompleted);
$completedCount = mysqli_fetch_assoc($resultCompleted)['completed_count'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `staff-table` WHERE role = 'staff'");
$staffCount = mysqli_fetch_assoc($result)['total'] ?? 0;

$staffOptions = "";
$sql = "SELECT Id, fname, lname FROM `staff-table` WHERE role = 'staff'"; 
$staffResult = mysqli_query($conn, $sql);

if ($staffResult && mysqli_num_rows($staffResult) > 0) {
    while ($staff = mysqli_fetch_assoc($staffResult)) {
        $staffId = htmlspecialchars($staff['Id']);
        $staffName = htmlspecialchars($staff['fname'] . " " . $staff['lname']);
        $staffOptions .= "<option value='{$staffId}'>{$staffId} - {$staffName}</option>";
    }
} else {
    $staffOptions .= "<option disabled>No staff found</option>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="Dashboard.css">
</head>
<body>

    <button class="btn btn-dark d-md-none m-2" onclick="toggleSidebar()">☰ Menu</button>
    <nav class="sidebar" id="sidebar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Staff Admin</h4>
            <button class="btn btn-light btn-sm d-md-none" onclick="toggleSidebar()">✖</button> 
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="staffadminDashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Home</a></li>
            <li class="nav-item"><a href="index.php#about" class="nav-link"><i class="bi bi-info-circle me-2"></i> About Us</a></li>
            <li class="nav-item"><a href="index.php#service" class="nav-link"><i class="bi bi-gear me-2"></i> Service</a></li>
            <li class="nav-item"><a href="index.php#testimonials" class="nav-link"><i class="bi bi-star me-2"></i> Reviews</a></li>
            <li class="nav-item"><a href="index.php#contact" class="nav-link"><i class="bi bi-envelope me-2"></i> Contact</a></li>
            <li class="nav-item"><a href="blog.php" class="nav-link"><i class="bi bi-newspaper me-2"></i> Blog</a></li>
        </ul>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="addstaff.php" class="nav-link"><i class="bi bi-person-plus me-2"></i> Add Staff</a></li>
            <li class="nav-item"><a href="staffBooking.php" class="nav-link"><i class="bi bi-newspaper me-2"></i> Place Booking</a></li>
            <li class="nav-item"><a href="manage-progress.php" class="nav-link"><i class="bi bi-kanban me-2"></i> Manage Progress</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
    </nav>

    <div class="content">
        <h3>Welcome Back!</h3>
        <p>Here's what's happening with your services today</p>
        
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card">
                    <h6>Pending Bookings</h6>
                    <h3><?php echo $pendingCount; ?></h3>
                    <i class="bi bi-hourglass-split card-icon"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card">
                    <h6>Confirmed Bookings</h6>
                    <h3><?php echo $confirmedCount; ?></h3>
                    <i class="bi bi-calendar-check card-icon"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card">
                    <h6>Completed Bookings</h6>
                    <h3><?php echo $completedCount; ?></h3>
                    <i class="bi bi-check-circle card-icon"></i>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card">
                    <h6>Staff Count</h6>
                    <h3><?php echo $staffCount; ?></h3>
                    <i class="bi bi-people card-icon"></i>
                </div>
            </div>
        </div>

        <!-- Table Toggle Section -->
        <div class="card mt-4">
            <div class="d-flex align-items-center">
                <a href="#" class="text-decoration-none text-dark fw-bold me-3" id="staffTab" onclick="showTable('staff')">
                    <i class="bi bi-people me-2"></i>Staff Table
                </a>
                <a href="#" class="text-decoration-none text-dark fw-bold me-3" id="bookingTab" onclick="showTable('booking')">
                    <i class="bi bi-calendar-date me-2"></i>Bookings Table
                </a>
                <a href="#" class="text-decoration-none text-dark fw-bold" id="ordersTab" onclick="showTable('orders')">
                    <i class="bi bi-bag-check me-2"></i>Orders Table
                </a>
            </div>
            
            <!-- Staff Table -->
            <div class="table-responsive" id="staffTable">
                <h5 class="mt-4 mb-3">Staff Members</h5>
                <table id="staffDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM `staff-table` WHERE role = 'staff'"; 
                        $userResult = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($userResult)) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Id']); ?></td>
                            <td><?php echo htmlspecialchars($row['fname']); ?></td>
                            <td><?php echo htmlspecialchars($row['lname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['contnumber']); ?></td>
                            <td><a href="deletestaff.php?id=<?php echo $row['Id']; ?>" class="btn btn-danger" onclick="return confirmDelete()">Delete</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive" id="bookingTable" style="display: none;">
                <h5 class="mt-4 mb-3">Bookings</h5>
                <table id="BookingDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>User ID</th>
                            <th>Vehicle ID</th>
                            <th>Service</th>
                            <th>Date/Time</th>
                            <th>Status</th>
                            <th>Assign Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM bookings WHERE status != 'completed' ORDER BY booking_date DESC, booking_time DESC";
                        $bookingResult = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($bookingResult)) {
                            $statusClass = '';
                            switch(strtolower($row['status'])) {
                                case 'pending':
                                    $statusClass = 'status-pending';
                                    break;
                                case 'confirmed':
                                    $statusClass = 'status-confirmed';
                                    break;
                                case 'completed':
                                    $statusClass = 'status-completed';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'status-cancelled';
                                    break;
                                default:
                                    $statusClass = '';
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['User_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['vehicle_id']); ?></td>
                            <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($row['service_type']))); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?> <br>
                                <small><?php echo htmlspecialchars($row['booking_time']); ?></small></td>
                            <td><span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <select name="assigned_staff" class="form-select form-select-sm mb-2">
                                        <option value="0">Select Staff</option>
                                        <?php
                                        $sqlStaff = "SELECT Id, fname, lname FROM `staff-table` WHERE role = 'staff'";
                                        $staffResult = mysqli_query($conn, $sqlStaff);
                                        while ($staff = mysqli_fetch_assoc($staffResult)) {
                                            $selected = ($row['assigned_staff'] == $staff['Id']) ? 'selected' : '';
                                            echo "<option value='{$staff['Id']}' $selected>{$staff['Id']} - {$staff['fname']} {$staff['lname']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="submit" name="assign_staff" class="btn btn-primary btn-sm">
                                        <i class="bi bi-person-check me-1"></i> Assign
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="table-responsive" id="ordersTable" style="display: none;">
                <h5 class="mt-4 mb-3">Orders</h5>
                <table id="OrdersDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sqlOrders = "SELECT * FROM orders_all ORDER BY order_date DESC";
                        $ordersResult = mysqli_query($conn, $sqlOrders);
                        while ($row = mysqli_fetch_assoc($ordersResult)) {
                            $orderStatusClass = '';
                            switch(strtolower($row['status'])) {
                                case 'pending':
                                    $orderStatusClass = 'status-pending';
                                    break;
                                case 'confirmed':
                                    $orderStatusClass = 'status-confirmed';
                                    break;
                                case 'shipped':
                                    $orderStatusClass = 'status-shipped';
                                    break;
                                case 'cancelled':
                                    $orderStatusClass = 'status-cancelled';
                                    break;
                                default:
                                    $orderStatusClass = '';
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td>$<?php echo number_format($row['order_total'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="<?php echo $orderStatusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td>
                                <?php if ($row['status'] !== 'confirmed'): ?>
                                    <form method="post" action="confirmorder.php">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="confirm_order" class="btn btn-success btn-sm">
                                            <i class="bi bi-check-circle me-1"></i> Confirm
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-success text-white"><i class="bi bi-check-circle-fill"></i> Confirmed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#staffDataTable').DataTable({
                responsive: true,
                pageLength: 7,
                lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
                dom: '<"top"lf>rt<"bottom"ip>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records..."
                }
            });
            
            $('#BookingDataTable').DataTable({
                responsive: true,
                pageLength: 7,
                lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
                dom: '<"top"lf>rt<"bottom"ip>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search bookings..."
                },
                order: [[4, 'desc']] 
            });
            
            $('#OrdersDataTable').DataTable({
                responsive: true,
                pageLength: 7,
                lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
                dom: '<"top"lf>rt<"bottom"ip>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search orders..."
                },
                order: [[6, 'desc']] 
            });
            
            const cards = document.querySelectorAll('.card');
            let delay = 0;
            
            cards.forEach(card => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, delay);
                delay += 100;
            });
            
            const currentUrl = window.location.href;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (currentUrl.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
            
            showTable('staff');
        });

        function showTable(table) {
            document.getElementById('staffTable').style.display = 'none';
            document.getElementById('bookingTable').style.display = 'none';
            document.getElementById('ordersTable').style.display = 'none';
            
            document.querySelectorAll('.d-flex a').forEach(link => {
                link.classList.remove('active-tab');
            });

            if (table === 'staff') {
                document.getElementById('staffTable').style.display = 'block';
                document.getElementById('staffTab').classList.add('active-tab');
            } else if (table === 'booking') {
                document.getElementById('bookingTable').style.display = 'block';
                document.getElementById('bookingTab').classList.add('active-tab');
            } else if (table === 'orders') {
                document.getElementById('ordersTable').style.display = 'block';
                document.getElementById('ordersTab').classList.add('active-tab');
            }
        }
        
        function confirmDelete() {
            return Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3f51b5',
                cancelButtonColor: '#f44336',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                return result.isConfirmed;
            });
        }
        
        function toggleSidebar() {
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
        }
        
        function showSuccessAlert(message) {
            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                confirmButtonColor: '#3f51b5',
                timer: 3000,
                timerProgressBar: true
            });
        }
        
        function showErrorAlert(message) {
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error',
                confirmButtonColor: '#3f51b5',
                timer: 3000,
                timerProgressBar: true
            });
        }
    </script>
</body>
</html>
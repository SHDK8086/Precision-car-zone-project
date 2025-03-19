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

if (isset($_SESSION['login_success'])) {
    $message = $_SESSION['login_success'];
    $title = "Login Successful";
    
    echo '<div id="customAlert" class="custom-alert custom-alert-success">
            <div class="custom-alert-content">
                <div class="custom-alert-icon">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="custom-alert-text">
                    <p class="custom-alert-title">' . $title . '</p>
                    <p class="custom-alert-message">' . htmlspecialchars($message) . '</p>
                </div>
                <button type="button" class="custom-alert-close" onclick="closeCustomAlert()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="custom-alert-progress"></div>
        </div>';
    
    echo '<script>
            // Auto-dismiss the alert after 5 seconds
            setTimeout(function() {
                closeCustomAlert();
            }, 5000);
            
            // Function to close the alert
            function closeCustomAlert() {
                const alert = document.getElementById("customAlert");
                if (alert) {
                    alert.style.opacity = "0";
                    alert.style.transform = "translate(-50%, -20px)";
                    alert.style.transition = "opacity 0.3s, transform 0.3s";
                    setTimeout(function() {
                        alert.remove();
                    }, 300);
                }
            }
        </script>';
    
    unset($_SESSION['login_success']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="Dashboard.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</head>
<body>
    <button class="btn btn-dark d-md-none m-2" onclick="toggleSidebar()">
        <i class="bi bi-list"></i> Menu
    </button>
    
    <nav class="sidebar" id="sidebar">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="bi bi-person-badge me-2"></i>Staff
            </h4>
            <button class="btn btn-light btn-sm d-md-none" onclick="toggleSidebar()">
                <i class="bi bi-x"></i>
            </button> 
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item"><a href="staffadminDashboard.php" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-house-door me-2"></i>Home</a></li>
            <li class="nav-item"><a href="index.php#about" class="nav-link"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
            <li class="nav-item"><a href="index.php#service" class="nav-link"><i class="bi bi-tools me-2"></i>Service</a> </li> </li>
            <li class="nav-item"><a href="index.php#contact" class="nav-link"><i class="bi bi-envelope me-2"></i>Contact</a></li>
            <li class="nav-item"><a href="blog.php" class="nav-link"><i class="bi bi-journal-text me-2"></i>Blog</a></li>
        </ul>
        
        <hr>
        
        <ul class="nav flex-column">
            <li class="nav-item"><a href="updatestaff.php?id=<?php echo $_SESSION['Id']; ?>" class="nav-link">
                    <i class="bi bi-person-gear me-2"></i>Update User Data
                </a>
            </li>
            <li class="nav-item"><a href="staffBooking.php" class="nav-link"><i class="bi bi-newspaper me-2"></i> Place Booking</a></li>
            <li class="nav-item"><a href="manage-progress-staff.php?id=<?php echo $_SESSION['Id']; ?>" class="nav-link">
                    <i class="bi bi-clipboard-check me-2"></i>Manage Progress
                </a>
            </li>
        </ul>
        
        <hr>
        
        <a href="logout.php" class="btn btn-danger w-100">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </nav>
    
    <div class="content">
        <div class="header-section mb-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3><i class="bi bi-speedometer2 me-2"></i>Welcome Back!</h3>
                    <p>Here's what's happening with your services today</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="date-display text-muted mb-2">
                        <i class="bi bi-calendar3 me-2"></i><?php echo date('l, F j, Y'); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Pending Bookings</h6>
                            <h3><?php echo $pendingCount; ?></h3>
                        </div>
                        <div class="stat-icon bg-light rounded-circle p-3">
                            <i class="bi bi-hourglass-split text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Confirmed Bookings</h6>
                            <h3><?php echo $confirmedCount; ?></h3>
                        </div>
                        <div class="stat-icon bg-light rounded-circle p-3">
                            <i class="bi bi-calendar-check text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Completed Bookings</h6>
                            <h3><?php echo $completedCount; ?></h3>
                        </div>
                        <div class="stat-icon bg-light rounded-circle p-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-6">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6>Staff Count</h6>
                            <h3><?php echo $staffCount; ?></h3>
                        </div>
                        <div class="stat-icon bg-light rounded-circle p-3">
                            <i class="bi bi-people text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Staff Members</h5>
                <div class="card-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="staffDataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-1"></i>Staff ID</th>
                                <th><i class="bi bi-person me-1"></i>First Name</th>
                                <th><i class="bi bi-person me-1"></i>Last Name</th>
                                <th><i class="bi bi-envelope me-1"></i>Email</th>
                                <th><i class="bi bi-geo-alt me-1"></i>Address</th>
                                <th><i class="bi bi-telephone me-1"></i>Contact</th>
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
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;">
                                        <?php echo htmlspecialchars($row['address']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['contnumber']); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#staffDataTable').DataTable({
                responsive: true,
                language: {
                    search: "<i class='bi bi-search'></i> Search:",
                    lengthMenu: "<i class='bi bi-list'></i> _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ staff members",
                    infoEmpty: "No staff members available",
                    infoFiltered: "(filtered from _MAX_ total staff members)",
                    paginate: {
                        first: "<i class='bi bi-chevron-double-left'></i>",
                        previous: "<i class='bi bi-chevron-left'></i>",
                        next: "<i class='bi bi-chevron-right'></i>",
                        last: "<i class='bi bi-chevron-double-right'></i>"
                    }
                }
            });
        });

        function confirmDelete() {
            return confirm("Are you sure you want to delete this staff member?");
        }

        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open"); 
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('#staffDataTable tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.classList.add('row-highlight');
                });
                row.addEventListener('mouseleave', function() {
                    this.classList.remove('row-highlight');
                });
            });
        });
    </script>
    
    <style>
        .stat-icon {
            height: 48px;
            width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media print {
            .sidebar, button, .btn, .card-actions {
                display: none !important;
            }
            .content {
                margin-left: 0;
                padding: 0;
            }
        }
    </style>
</body>
</html>
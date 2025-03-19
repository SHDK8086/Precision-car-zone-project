<?php
session_start();
include('db/dbconn.php'); 

if (!isset($_SESSION['Id'])) {
    header("location: login.php");
    exit;
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="Dashboard.css">
</head>
<body>

    <button class="btn btn-dark d-md-none m-2" onclick="toggleSidebar()">☰ Menu</button>
    <nav class="sidebar" id="sidebar">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Admin Panel</h4>
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
        <li class="nav-item"><a href="addAdmin.php" class="nav-link"><i class="bi bi-shield-plus me-2"></i> Add Admin</a></li>
        <li class="nav-item"><a href="addblog.php" class="nav-link"><i class="bi bi-file-earmark-plus me-2"></i> Add Blog</a></li>
        <li class="nav-item"><a href="addproducts.php" class="nav-link"><i class="bi bi-bag-plus me-2"></i> Add Product</a></li>
        <li class="nav-item"><a href="updateadmin.php?id=<?php echo $_SESSION['Id']; ?>" class="nav-link"><i class="bi bi-person-gear me-2"></i> Update User Info</a></li>
    </ul>
    <hr>
    <a href="logout.php" class="btn btn-danger w-100"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
</nav>

    <div class="content">
        <h3>Welcome Back!</h3>
        <p>Here’s what’s happening with your services today</p>
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
        <div class="card mt-4 p-3">
        <div class="d-flex align-items-center">
        <a href="#" class="text-decoration-none text-dark fw-bold me-3" id="staffTab" onclick="showTable('staff')">
            <i class="bi bi-people me-2"></i>Staff Table
        </a>
        <a href="#" class="text-decoration-none text-dark fw-bold" id="staffAdminTab" onclick="showTable('staff_admin')">
            <i class="bi bi-person-badge me-2"></i>Staff Admin Table
        </a>
        <a href="#" class="text-decoration-none text-dark fw-bold ms-3" id="blogTab" onclick="showTable('blog_table')">
            <i class="bi bi-file-earmark-text me-2"></i>Blog Table
        </a>
        <a href="#" class="text-decoration-none text-dark fw-bold ms-3" id="ProductTab" onclick="showTable('product_table')">
            <i class="bi bi-box-seam me-2"></i>Product Table
        </a>
    </div>

            <!-- Staff Table -->
            <div class="table-responsive" id="staffTable">
                <h5 class="mt-4 mb-2">Staff Members</h5>
                <table id="staffDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Delete</th>
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

            <!-- Staff Admin Table -->
            <div class="table-responsive" id="staffAdminTable" style="display: none;">
                <h5 class="mt-4 mb-2">Staff Admins</h5>
                <table id="staffAdminDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Staff ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM `staff-table` WHERE role = 'staff admin'"; 
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

            <!-- Blog Table -->
            <div class="table-responsive" id="blogTable" style="display: none;">
                <h5 class="mt-4 mb-2">Blog</h5>
                <table id="blogDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Blog Name</th>
                            <th>Author</th>
                            <th>Created Day</th>
                            <th>status</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM `blog`"; 
                        $userResult = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($userResult)) {
                        ?>
                        <tr>
                            <td><?php echo $row['Id'] ?></td>
                            <td><?php echo $row['BTitle'] ?></td>
                            <td><?php echo $row['Author'] ?></td>
                            <td><?php echo $row['Create-Day'] ?></td>
                            <td><?php echo $row['status'] ?></td>
                            <td><a href="updateblog.php?id=<?php echo $row['Id']; ?>" class="btn btn-primary">Edit</a></td>
                            <td><a href="deleteBlog.php?id=<?php echo $row['Id']; ?>" class="btn btn-danger" onclick="return confirmDelete()">Delete</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Product Table -->
            <div class="table-responsive" id="ProductTable" style="display: none;">
                <h5 class="mt-4 mb-2">Blog</h5>
                <table id="ProductDataTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>category</th>
                            <th>product Name</th>
                            <th>serial Number</th>
                            <th>ratings</th>
                            <th>price</th>
                            <th>stock</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM `store`"; 
                        $userResult = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($userResult)) {
                        ?>
                        <tr>
                            <td><?php echo $row['id'] ?></td>
                            <td><?php echo $row['category'] ?></td>
                            <td><?php echo $row['productName'] ?></td>
                            <td><?php echo $row['serialNumber'] ?></td>
                            <td><?php echo $row['ratings'] ?></td>
                            <td><?php echo $row['price'] ?></td>
                            <td><?php echo $row['stock'] ?></td>
                            <td><a href="updateproduct.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a></td>
                            <td><a href="deleteproduct.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirmDelete()">Delete</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this item?");
        }
        $(document).ready(function() {
            $('#staffDataTable').DataTable();
            $('#staffAdminDataTable').DataTable();
            $('#blogDataTable').DataTable();
            $('#ProductDataTable').DataTable();
        });

        function showTable(table) {
            document.getElementById('staffTable').style.display = 'none';
            document.getElementById('staffAdminTable').style.display = 'none';
            document.getElementById('blogTable').style.display = 'none';
            document.getElementById('ProductTable').style.display = 'none';

            if (table === 'staff') {
                document.getElementById('staffTable').style.display = 'block';
            } else if (table === 'staff_admin') {
                document.getElementById('staffAdminTable').style.display = 'block';
            } else if (table === 'blog_table') {
                document.getElementById('blogTable').style.display = 'block';
            } else if (table === 'product_table') {
                document.getElementById('ProductTable').style.display = 'block';
            }
        }

        function toggleSidebar() {
            var sidebar = document.getElementById("sidebar");
            sidebar.classList.toggle("open"); 
        }


document.addEventListener('DOMContentLoaded', function() {
    const currentUrl = window.location.href;
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    navLinks.forEach(link => {
        if (currentUrl.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
    
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
    
    $('#staffAdminDataTable').DataTable({
        responsive: true,
        pageLength: 7,
        lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records..."
        }
    });
    
    $('#blogDataTable').DataTable({
        responsive: true,
        pageLength: 7,
        lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records..."
        }
    });
    
    $('#ProductDataTable').DataTable({
        responsive: true,
        pageLength: 7,
        lengthMenu: [[5, 7, 10, 25, -1], [5, 7, 10, 25, "All"]],
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records..."
        }
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
    
    const tableLinks = document.querySelectorAll('.d-flex a');
    
    tableLinks.forEach(link => {
        link.addEventListener('click', function() {
            tableLinks.forEach(l => l.classList.remove('active-tab'));
            
            this.classList.add('active-tab');
        });
    });
    
    document.getElementById('staffTab').classList.add('active-tab');
});

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
if (!window.Swal) {
    const sweetalertScript = document.createElement('script');
    sweetalertScript.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    document.head.appendChild(sweetalertScript);
}
    </script>
</body>
</html>
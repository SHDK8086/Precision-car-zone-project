<?php
include('db/dbconn.php'); 
session_start();

if(!isset($_SESSION['Id']) && !isset($_SESSION['email'])){
    header("location:login.php");
    exit;
}

if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'user':
            if (basename($_SERVER['PHP_SELF']) !== 'profile.php') {
                header("location:profile.php");
                exit; 
            }
            break;
        case 'admin':
            if (basename($_SERVER['PHP_SELF']) !== 'SuperAdminDashboard.php') {
                header("location:SuperAdminDashboard.php");
                exit;
            }
            break;
        case 'staff':
            if (basename($_SERVER['PHP_SELF']) !== 'staffDashboard.php') {
                header("location:staffDashboard.php");
                exit;
            }
            break;
        case 'staff admin':
            if (basename($_SERVER['PHP_SELF']) !== 'staffadminDashboard.php') {
                header("location:staffadminDashboard.php");
                exit;
            }
            break;    
        default:
            header("location:logout.php");
            exit;
    }
}

$profileImage = !empty($_SESSION['profile_image']) ? 'profileimages/' . $_SESSION['profile_image'] : 'Images/default-profile.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update</title>
    <link rel="stylesheet" href="update.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background-image: url('images/logout.png'); /* Path to your logout.png */
            background-size: cover;  /* Ensures the image covers the button */
            background-position: center;  /* Centers the image */
            border: none; /* Removes button border */
            cursor: pointer; /* Shows pointer cursor when hovering */
            padding: 0; /* Removes any default padding */
        }

        /* Optional: Adjust the size for smaller screens */
        @media (max-width: 768px) {
            .logout-btn {
                width: 35px;
                height: 35px;
            }
        }

        @media (max-width: 576px) {
            .logout-btn {
                width: 30px;
                height: 30px;
            }
        }

        /* Add hover effect if needed */
        .logout-btn:hover {
            opacity: 0.8;
        }
        @media (max-width: 576px) {
            .logout-btn {
                width: 30px;
                height: 30px;
            }
        }

        /* Profile image responsiveness */
        .profile-img {
            width: 100%;
            height: auto;
            max-width: 150px;
            max-height: 150px;
        }
    </style>
</head>
<body>
    <!-- Logout Button -->
    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn"></button>
    </form>


    <div class="container">
        <div class="row gutters">
            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="account-settings">
                            <div class="user-profile">
                                <div class="user-avatar">
                                    <form id="uploadForm" action="upload_profile.php" method="POST" enctype="multipart/form-data">
                                        <label for="profilePicInput">
                                            <img class="rounded-circle profile-img" src="<?php echo $profileImage; ?>" alt="Profile Image" id="profilePic" style="cursor: pointer;">
                                        </label>
                                        <input type="file" name="profile_image" id="profilePicInput" accept="image/*" style="display: none;">
                                    </form>
                                </div>
                                <h5 class="user-name"><?php echo $_SESSION['username']?></h5>
                                <h6 class="user-email"><?php echo $_SESSION['email']?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-9 col-md-12 col-sm-12 col-12">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="row gutters">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <h6 class="mb-2 text-primary">Personal Details</h6>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="fullName">First Name</label>
                                    <h5><?php echo $_SESSION['fname']?></h5>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="eMail">Last Name</label>
                                    <h5><?php echo $_SESSION['lname']?></h5>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <h5><?php echo $_SESSION['mobilenumber']?></h5>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="website">Address</label>
                                    <h6><?php echo $_SESSION['useraddress']?></h6>
                                </div>
                            </div>
                        </div>
                        <div class="row gutters">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="Street">Username</label>
                                    <h5 class="user-name"><?php echo $_SESSION['username']?></h5>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label for="ciTy">Email</label>
                                    <h5><?php echo $_SESSION['email']?></h5>
                                </div>
                            </div>
                        </div>
                        <div class="row gutters">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="text-right">
                                    <a type="button" id="submit" name="submit" class="btn btn-secondary" href="index.php">Cancel</a>
                                    <a href="profile-edit.php?id=<?php echo $_SESSION['Id']?>" type="button" id="submit" name="submit" class="btn btn-primary">Update</a>
                                    <a href="booking_history.php?user_id=<?php echo $_SESSION['Id']; ?>" type="button" class="btn btn-info">Booking History</a>
                                    <a href="user_orders.php?user_id=<?php echo $_SESSION['Id']; ?>" type="button" class="btn btn-success">My Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('profilePic').addEventListener('click', function () {
            document.getElementById('profilePicInput').click();
        });

        document.getElementById('profilePicInput').addEventListener('change', function () {
            if (this.files.length > 0) {
                document.getElementById('uploadForm').submit();
            }
        });
    </script>
</body>
</html>

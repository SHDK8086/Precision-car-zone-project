<?php
include('db/dbconn.php'); 
session_start();

// If user is already logged in, redirect based on user type
if (isset($_SESSION['Id']) && isset($_SESSION['email'])) {
    switch ($_SESSION['user_type']) {
        case 'user':
            header("location:index.php");
            break;
        case 'admin':
            header("location:SuperAdminDashboard.php");
            break;
        case 'staff':
            header("location:staffDashboard.php");
            break;
        case 'staff admin':
            header("location:staffadminDashboard.php");
            break;    
    }
    exit(); 
}

try {
    if (isset($_POST['submit'])) {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = "Email and password are required.";
            header("location:login.php");
            exit();
        }

        $_SESSION['login_email'] = $email; 

        $stmt = $conn->prepare("SELECT * FROM `usertable` WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashedpassword = $row['password'];

            if (password_verify($password, $hashedpassword)) {
                $_SESSION['Id'] = $row['Id'];
                $_SESSION['fname'] = $row['fname'];
                $_SESSION['lname'] = $row['lname'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['mobilenumber'] = $row['mobilenumber'];
                $_SESSION['useraddress'] = $row['useraddress'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['profile_image'] = $row['profile_image'];
                unset($_SESSION['login_email']);
                $_SESSION['user_type'] = 'user';

                $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'profile.php';
                $redirect = filter_var($redirect, FILTER_SANITIZE_URL); 
                
                if (strpos($redirect, 'Booking.php') !== false && strpos($redirect, 'user_id=') === false) {
                    $redirect .= (strpos($redirect, '?') === false ? '?' : '&') . "user_id=" . $_SESSION['Id'];
                }

                header("location: " . urldecode($redirect));
                exit();
            } else {
                $_SESSION['login_error'] = "Invalid email or password.";
                header("location:login.php");
                exit();
            }}else{
            $stmt = $conn->prepare("SELECT * FROM `staff-table` WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $hashedpassword = $row['password'];

                if (password_verify($password, $hashedpassword)) {
                    $_SESSION['Id'] = $row['Id'];
                    $_SESSION['fname'] = $row['fname'];
                    $_SESSION['lname'] = $row['lname'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['gender'] = $row['gender'];
                    unset($_SESSION['login_email']);
                    $_SESSION['password'] = $row['password'];
                    $_SESSION['user_type'] = strtolower($row['role']);  

                    if ($_SESSION['user_type'] === 'admin') {
                        header("location:SuperAdminDashboard.php");
                    } elseif ($_SESSION['user_type'] === 'staff') {
                        header("location:staffDashboard.php");
                    } elseif ($_SESSION['user_type'] === 'staff admin') {
                        header("location:staffadminDashboard.php");
                    } 
                    exit();
                } else {
                    $_SESSION['login_error'] = "Invalid email or password.";
                    header("location:login.php");
                    exit();
                }
            } else {
                $_SESSION['login_error'] = "No account found with that email.";
                header("location:login.php");
                exit();
            }
        }
    }
} catch (Exception $e) {
    $_SESSION['login_error'] = "An error occurred: " . $e->getMessage();
    header("location:login.php");
    exit();
}
?>
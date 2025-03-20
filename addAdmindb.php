<?php
include("db/dbconn.php");

if (isset($_POST['submit'])) {
    $fname = trim($_POST["fname"] ?? '');        
    $lname = trim($_POST["lname"] ?? '');        
    $email = trim($_POST["email"] ?? '');        
    $address = trim($_POST["address"] ?? '');     
    $contact_number = trim($_POST["mnumber"] ?? ''); 
    $role = trim($_POST["role"] ?? '');
    
    if (empty($fname) || empty($lname) || empty($email) || empty($address) || empty($contact_number) || empty($role)) {
        header("Location: addstaff.php?error=missing_fields&fname=$fname&lname=$lname&email=$email&address=$address&contnumber=$contact_number");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: addstaff.php?error=invalid_email&fname=$fname&lname=$lname&email=$email&address=$address&contnumber=$contact_number");
        exit;
    }

    $sql_check_email = "SELECT * FROM `staff-table` WHERE email = ?";
    $stmt_check_email = mysqli_prepare($conn, $sql_check_email);
    mysqli_stmt_bind_param($stmt_check_email, "s", $email);
    mysqli_stmt_execute($stmt_check_email);
    $result = mysqli_stmt_get_result($stmt_check_email);

    if (mysqli_num_rows($result) > 0) {
        header("Location: addstaff.php?error=email_exists&fname=$fname&lname=$lname&email=$email&address=$address&contnumber=$contact_number");
        exit;
    }

    $Password = substr("password" . rand() . "service", 0, 12);
    $hashedPassword = password_hash($Password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO `staff-table` (fname, lname, email, address, contnumber, role, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssss", $fname, $lname, $email, $address, $contact_number, $role, $hashedPassword);

    try {
        if (mysqli_stmt_execute($stmt)) {
            header("Location: addstaff.php?success=staff_added&email=$email&password=$Password&role=$role");
            exit;
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } catch (Exception $e) {
        echo "Exception occurred: " . $e->getMessage();
    }
}
?>

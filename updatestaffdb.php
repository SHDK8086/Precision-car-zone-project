<?php
include("db/dbconn.php");

if (isset($_POST['submit'])) {
    $updateid = $_POST['updateid'];  
    $fname = trim($_POST["fname"] ?? '');          
    $lname = trim($_POST["lname"] ?? '');          
    $email = trim($_POST["email"] ?? '');          
    $address = trim($_POST["address"] ?? '');      
    $contact_number = trim($_POST["mnumber"] ?? '');  
    $password = trim($_POST["password"] ?? '');

    if (empty($fname) || empty($lname) || empty($email) || empty($address) || empty($contact_number) || empty($password)) {
        header("Location: addstaff.php?error=missing_fields&fname=$fname&lname=$lname&email=$email&address=$address&contnumber=$contact_number");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: addstaff.php?error=invalid_email&fname=$fname&lname=$lname&email=$email&address=$address&contnumber=$contact_number");
        exit;
    }

    // Password hashing
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists (excluding the current record)
    $sql_check_email = "SELECT * FROM `staff-table` WHERE email = ? AND Id != ?";
    $stmt_check_email = mysqli_prepare($conn, $sql_check_email);
    mysqli_stmt_bind_param($stmt_check_email, "si", $email, $updateid);
    mysqli_stmt_execute($stmt_check_email);
    $result = mysqli_stmt_get_result($stmt_check_email);

    if (mysqli_num_rows($result) > 0) {
        header("Location: addstaff.php?error=email_exists&fname=$fname&lname=$lname&email=$email&address=$address&contnumber=$contact_number");
        exit;
    }

    // Update staff details
    $sql = "UPDATE `staff-table` SET fname = ?, lname = ?, email = ?, address = ?, contnumber = ?, password = ? WHERE Id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssi", $fname, $lname, $email, $address, $contact_number, $hashedPassword, $updateid);

    try {
        if (mysqli_stmt_execute($stmt)) {
            header("Location: staffDashboard.php?success=staff_updated&email=$email");
            exit;
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } catch (Exception $e) {
        echo "Exception occurred: " . $e->getMessage();
    }
}
?>

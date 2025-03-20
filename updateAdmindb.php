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

    // Check if required fields are empty
    if (empty($fname) || empty($lname) || empty($email) || empty($address) || empty($contact_number) || empty($password)) {
        header("Location: updateAdmin.php?id=$updateid&error=missing_fields");
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: updateAdmin.php?id=$updateid&error=invalid_email");
        exit;
    }

    // Password hashing
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email is already taken by another user (excluding the current one)
    $sql_check_email = "SELECT * FROM `staff-table` WHERE email = ? AND Id != ?";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bind_param("si", $email, $updateid);
    $stmt_check_email->execute();
    $result = $stmt_check_email->get_result();

    if ($result->num_rows > 0) {
        header("Location: updateAdmin.php?id=$updateid&error=email_exists");
        exit;
    }

    // Update the staff information
    $sql = "UPDATE `staff-table` SET fname = ?, lname = ?, email = ?, address = ?, contnumber = ?, password = ? WHERE Id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $fname, $lname, $email, $address, $contact_number, $hashedPassword, $updateid);

    try {
        if ($stmt->execute()) {
            header("Location: staffadminDashboard.php?success=staff_updated");
            exit;
        } else {
            echo "Error updating record: " . $stmt->error;
        }
    } catch (Exception $e) {
        echo "Exception occurred: " . $e->getMessage();
    }
}
?>

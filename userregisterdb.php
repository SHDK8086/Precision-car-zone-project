<?php

include("db/dbconn.php");

$fname = trim($_POST["fname"]);
$lname = trim($_POST["lname"]);
$username = trim($_POST["uname"]);
$mnumber = trim($_POST["mnumber"]);
$Uaddress = trim($_POST["address"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];
$profileImage = "default-profile.jpg";  

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: usersignup.php?error=invalid_email&fname=$fname&lname=$lname&uname=$username&mnumber=$mnumber&address=$Uaddress");
    exit;
}

$sql_check_email = "SELECT * FROM `usertable` WHERE email = ?";
$stmt_check_email = mysqli_prepare($conn, $sql_check_email);
mysqli_stmt_bind_param($stmt_check_email, "s", $email);
mysqli_stmt_execute($stmt_check_email);
$result = mysqli_stmt_get_result($stmt_check_email);

if (mysqli_num_rows($result) > 0) {
    header("Location: usersignup.php?error=email_exists&fname=$fname&lname=$lname&uname=$username&mnumber=$mnumber&address=$Uaddress");
    exit;
}

$hashedpassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $sql = "INSERT INTO `usertable` (fname, lname, username, mobilenumber, useraddress, email, password, profile_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "ssssssss", $fname, $lname, $username, $mnumber, $Uaddress, $email, $hashedpassword, $profileImage);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} catch (Exception $e) {
    echo "Message: " . $e->getMessage();
}
?>

<?php
session_start();
include("db/dbconn.php");

$updateid = $_POST["updateid"];
$Fname = $_POST["fname"];
$lname = $_POST["lname"];
$username = $_POST["uname"];
$Mnumber = $_POST["mnumber"];
$Address = $_POST["address"];
$Email = $_POST["email"];
$Password = $_POST["password"];

$hashedpassword = password_hash($Password, PASSWORD_BCRYPT);

try {

    $sql = "UPDATE `usertable` SET fname = ?, lname = ?, username = ?, mobilenumber = ?, useraddress = ?, email = ?, password = ? WHERE Id = ?";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("sssssssi", $Fname, $lname, $username, $Mnumber, $Address, $Email, $hashedpassword, $updateid);
    
    if ($stmt->execute()) {

        $sql = "SELECT * FROM `usertable` WHERE Id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $updateid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $_SESSION['Id'] = $row['Id'];
            $_SESSION['fname'] = $row['fname'];
            $_SESSION['lname'] = $row['lname'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['mobilenumber'] = $row['mobilenumber'];
            $_SESSION['useraddress'] = $row['useraddress'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_type'] = 'user';

            header("Location: profile.php");
            exit(); 
        } else {
            echo 'Error: Could not retrieve updated user data.';
        }
    } else {
        echo 'Error: ' . $stmt->error;
    }
} catch (Exception $e) {
    echo "Message: " . $e->getMessage();
}
?>

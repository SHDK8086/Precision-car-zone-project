<?php
include('db/dbconn.php');
session_start();

if (!isset($_SESSION['Id'])) {
    header("location:login.php");
    exit;
}

$userId = $_SESSION['Id'];
$target_dir = "profileimages/";

// Ensure the folder exists
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_FILES["profile_image"])) {
    $file = $_FILES["profile_image"];
    $fileName = basename($file["name"]);
    $fileTmpName = $file["tmp_name"];
    $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate image type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedTypes)) {
        echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        exit;
    }

    // Rename file to avoid conflicts
    $newFileName = "profile_" . $userId . "." . $imageFileType;
    $target_file = $target_dir . $newFileName;

    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $target_file)) {
        // Update database
        $sql = "UPDATE usertable SET profile_image = '$newFileName' WHERE Id = '$userId'";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['profile_image'] = $newFileName; // Update session
            header("Location: profile.php");
            exit;
        } else {
            echo "Error updating profile image: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    echo "No file uploaded.";
}
?>

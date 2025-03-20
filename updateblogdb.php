<?php
session_start();
include("db/dbconn.php");

$updateid = $_POST["updateid"];
$title = $_POST["title"];
$author = $_POST["author"];
$category = $_POST["category"];
$status = $_POST["status"];
$Content = $_POST["Content"];
$fileName = $_FILES["fileToUpload"]["name"];

if (!empty($fileName)) {
    $tempfilename = $_FILES["fileToUpload"]["tmp_name"];
    $target_dir = 'Blogimages/';
    $target_file = $target_dir . basename($fileName);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($tempfilename);
    if ($check === false) {
        die("File is not an image.");
    }
    if ($_FILES["fileToUpload"]["size"] > 10000000) {
        die("File is too large. Maximum size is 10MB.");
    }
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        die("Only JPG, JPEG, PNG, and GIF files are allowed.");
    }
    if (move_uploaded_file($tempfilename, $target_file)) {
        $fileName = basename($fileName);  
    } else {
        die("Error uploading file.");
    }
} else {
    $sql = "SELECT Filename FROM `blog` WHERE Id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $updateid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fileName = $row['Filename']; 
    } else {
        die("No item found with the provided ID.");
    }
}
try {
    $sql = "UPDATE `blog` SET BTitle = ?, category = ?, BContent = ?, Author = ?, status = ?, Filename = ? WHERE Id = ?";
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ssssssi", $title, $category, $Content, $author, $status, $fileName, $updateid);

    if ($stmt->execute()) {
        header("Location: SuperAdminDashboard.php");
        exit();
    } else {
        echo 'Database update failed: ' . $stmt->error;
    }
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage();
}

$conn->close();
?>
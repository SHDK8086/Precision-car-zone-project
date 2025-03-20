<?php
include("db/dbconn.php");

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $content = mysqli_real_escape_string($conn, $_POST['Content']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $fileName = $_FILES["fileToUpload"]["name"];
    $tempfilename = $_FILES["fileToUpload"]["tmp_name"];
    $target_dir = 'Blogimages/';
    $target_file = $target_dir . basename($fileName);
    
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($tempfilename);
    if ($check !== false) {
        echo "File is an image - " . $check["mime"] . ".<br>";
        $uploadOk = 1;
    } else {
        echo "File is not an image.<br>";
        $uploadOk = 0;
    }
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.<br>";
        $uploadOk = 0;
    }
    if ($_FILES["fileToUpload"]["size"] > 10000000) { 
        echo "Sorry, your file is too large.<br>";
        $uploadOk = 0;
    }
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.<br>";
        $uploadOk = 0;
    }
    if ($uploadOk == 1) {
        if (move_uploaded_file($tempfilename, $target_file)) {
            echo "The file " . htmlspecialchars(basename($fileName)) . " has been uploaded.<br>";

            $sql = "INSERT INTO blog (BTitle, category, BContent, Author, status, Filename) 
                    VALUES ('$title', '$category', '$content', '$author', '$status', '$fileName')";

            try {
                if (mysqli_query($conn, $sql)) {
                    header("Location: admin.php");
                    exit; 
                } else {
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
            } catch (Exception $e) {
                echo "Message: " . $e->getMessage();
            }
        } else {
            echo "Sorry, there was an error uploading your file.<br>";
        }
    } else {
        echo "Sorry, your file was not uploaded due to an error.<br>";
    }
?>
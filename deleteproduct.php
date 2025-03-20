<?php
include('db/dbconn.php'); 

if (isset($_GET['id'])) {
    $delete = intval($_GET['id']);
    
    echo $delete;

    $sql = "DELETE FROM `store` WHERE Id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete);
    
    if ($stmt->execute()) {
        header("Location: SuperAdminDashboard.php");
        exit();
    } else {
        echo "ERROR: " . $stmt->error . "<br>";
    }
} else {
    echo "No ID specified.";
}
?>
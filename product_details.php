<?php
if (isset($_SESSION['Id']) && isset($_SESSION['email'])) {
    if ($_SESSION['user_type'] === 'user') {
        header("location:profile.php");
    }elseif ($_SESSION['user_type'] === 'admin') {
        header("location:SuperAdminDashboard.php");
    }elseif ($_SESSION['user_type'] === 'staff') {
        header("location:staffDashboard.php");
    }elseif ($_SESSION['user_type'] === 'staff admin') {
      header("location:staffadminDashboard.php");
  }
    exit(); 
  }
  
  session_start();

error_reporting(0);

include 'connection.php';

header('Content-Type: application/json');

if (ob_get_length()) ob_clean();

if (isset($_GET['name'])) {
    $product_name = mysqli_real_escape_string($conn, $_GET['name']);
    
    $sql = "SELECT * FROM store WHERE productName = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $product_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} elseif (isset($_GET['serial'])) {
    $serial = mysqli_real_escape_string($conn, $_GET['serial']);
    
    $sql = "SELECT * FROM store WHERE serialNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $serial);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found with this serial number']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}

exit;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_name']; ?></title>
    <link rel="stylesheet" href="Store.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>
<body>

<div class="product-card">
    <img src="<?php echo $product['ProductImage']; ?>" alt="<?php echo $product['productName']; ?>">
    <h2><?php echo $product['productName']; ?></h2>
    <p>Serial: <?php echo $product['serialNumber']; ?></p>
    <p>Description: * <?php echo $product['description'];?></p>
    <p>Ratings:  <?php echo $product['ratings']; ?></p>
    <p>Price: R$ <?php echo $product['price']; ?></p>
    <?php if (isset($_SESSION['Id']) && isset($_SESSION['email'])): ?>
        <form method="POST" action="cart.php">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <button type="submit" name="add_to_cart">Add to Cart</button>
        </form>
    <?php else: ?>
        <a href="login.php" class="download-btn">
            <i class="bi bi-calendar-check"></i> <span>Add to Cart</span>
        </a>
    <?php endif; ?>
</div>

</body>
</html>

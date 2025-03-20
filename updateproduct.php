<?php
@include 'connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = $_GET['id'];
    $query = "SELECT * FROM store WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $productName = mysqli_real_escape_string($conn, $_POST['productName']);
    $serialNumber = mysqli_real_escape_string($conn, $_POST['serialNumber']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    if (!is_numeric($_POST['ratings']) || $_POST['ratings'] < 1 || $_POST['ratings'] > 5) {
        echo "Ratings must be a number between 1 and 5.";
        exit;
    }
    $ratings = mysqli_real_escape_string($conn, $_POST['ratings']);
    
    if (!is_numeric($_POST['price'])) {
        echo "Price must be a valid number.";
        exit;
    }
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);

    $image = $_FILES['productImage'];
    if ($image['error'] != UPLOAD_ERR_NO_FILE) {
        $imageName = $image['name'];
        $imageTmpName = $image['tmp_name'];
        $imageError = $image['error'];
        $imageSize = $image['size'];

        $uploadDir = 'uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($imageError === UPLOAD_ERR_OK) {
            if ($imageSize < 2097152) {
                $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
                $imageExtensionLower = strtolower($imageExtension);
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (in_array($imageExtensionLower, $allowedExtensions)) {
                    $newImageName = uniqid('', true) . '.' . $imageExtensionLower;
                    $imageUploadPath = $uploadDir . '/' . $newImageName;
                    move_uploaded_file($imageTmpName, $imageUploadPath);
                } else {
                    echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
                    exit;
                }
            } else {
                echo "File is too large. Maximum size is 2MB.";
                exit;
            }
        } else {
            echo "Error uploading file.";
            exit;
        }
    } else {
        $imageUploadPath = $product['productImage']; 
    }

    $updateQuery = "UPDATE store SET category = ?, productName = ?, serialNumber = ?, description = ?, ratings = ?, price = ?, stock = ?, productImage = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssdddsi", $category, $productName, $serialNumber, $description, $ratings, $price, $stock, $imageUploadPath, $productId);

    if ($stmt->execute()) {
        echo "<div class='success'>Product updated successfully!</div>";
    
        header("Location: SuperAdminDashboard.php");
        exit(); 
    } else {
        echo "Error updating product: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="addstaff.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: rgb(133, 133, 133);
        }

        .form-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .form-title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 25px;
            color: #333;
        }

        .current-file {
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <div class="form-title">Edit Product</div>
        <form action="<?php echo ($_SERVER['PHP_SELF'] . '?id=' . $productId); ?>" method="POST" enctype="multipart/form-data">

            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">--Select Category--</option>
                <option value="Engine Parts" <?php echo ($product['category'] == 'Engine Parts') ? 'selected' : ''; ?>>Engine Parts</option>
                <option value="Suspension & Steering" <?php echo ($product['category'] == 'Suspension & Steering') ? 'selected' : ''; ?>>Suspension & Steering</option>
                <option value="Braking System" <?php echo ($product['category'] == 'Braking System') ? 'selected' : ''; ?>>Braking System</option>
                <option value="Electrical Components" <?php echo ($product['category'] == 'Electrical Components') ? 'selected' : ''; ?>>Electrical Components</option>
                <option value="Body & Exterior Parts" <?php echo ($product['category'] == 'Body & Exterior Parts') ? 'selected' : ''; ?>>Body & Exterior Parts</option>
                <option value="Transmission & Drivetrain" <?php echo ($product['category'] == 'Transmission & Drivetrain') ? 'selected' : ''; ?>>Transmission & Drivetrain</option>
                <option value="Cooling & Heating" <?php echo ($product['category'] == 'Cooling & Heating') ? 'selected' : ''; ?>>Cooling & Heating</option>
            </select>

            <label for="productName">Product Name:</label>
            <input type="text" name="productName" id="productName" value="<?php echo htmlspecialchars($product['productName']); ?>" required>

            <label for="serialNumber">Serial Number:</label>
            <input type="text" name="serialNumber" id="serialNumber" value="<?php echo htmlspecialchars($product['serialNumber']); ?>" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>

            <label for="ratings">Ratings (1-5):</label>
            <input type="number" name="ratings" id="ratings" min="1" max="5" step="0.1" value="<?php echo htmlspecialchars($product['ratings']); ?>" required>

            <label for="price">Price:</label>
            <input type="number" name="price" id="price" min="0" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>

            <label for="stock">Stock:</label>
            <input type="number" name="stock" id="stock" min="0" step="0.01" value="<?php echo htmlspecialchars($product['stock']); ?>" required>

            <label for="productImage">Product Image:</label>
            <input type="file" name="productImage" id="productImage" accept="image/*">
            <?php if ($product['productImage']): ?>
                <p class="current-file">Current file: <?php echo basename($product['productImage']); ?></p>
            <?php endif; ?>

            <button type="submit">Update Product</button>
        </form>
    </div>

</body>

</html>

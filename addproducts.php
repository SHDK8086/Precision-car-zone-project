<?php

@include 'connection.php';

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

    if (!isset($_FILES['productImage']) || $_FILES['productImage']['error'] === UPLOAD_ERR_NO_FILE) {
        echo "Product image is required.";
        exit;
    }

    $image = $_FILES['productImage'];
    $imageName = $image['name'];
    $imageTmpName = $image['tmp_name'];
    $imageError = $image['error'];
    $imageSize = $image['size'];

    $uploadDir = 'uploads';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            echo "Failed to create uploads directory.";
            exit;
        }
    }

    if ($imageError === UPLOAD_ERR_OK) {
        if ($imageSize < 2097152) { 
            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            $imageExtensionLower = strtolower($imageExtension);

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($imageExtensionLower, $allowedExtensions)) {
                $newImageName = uniqid('', true) . '.' . $imageExtensionLower;
                $imageUploadPath = $uploadDir . '/' . $newImageName;

                if (is_writable($uploadDir)) {
                    if (move_uploaded_file($imageTmpName, $imageUploadPath)) {                        
                        $stmt = $conn->prepare("INSERT INTO store (category, productName, serialNumber, description, ratings, price, stock, productImage) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        if (!$stmt) {
                            echo "Error preparing statement: " . $conn->error;
                            exit;
                        }
                        
                        $stmt->bind_param("ssssddds", $category, $productName, $serialNumber, $description, $ratings, $price, $stock, $imageUploadPath);
                        
                        if ($stmt->execute()) {
                            echo "<div class='success'>Product added successfully!</div>";
                        } else {
                            echo "Error: " . $stmt->error;
                        }
                        
                        $stmt->close();
                    } else {
                        echo "Failed to move uploaded file.";
                    }
                } else {
                    echo "The 'uploads' directory is not writable.";
                }
            } else {
                echo "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            }
        } else {
            echo "File is too large. Maximum size is 2MB.";
        }
    } else {
        switch ($imageError) {
            case UPLOAD_ERR_INI_SIZE:
                echo "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "The uploaded file was only partially uploaded.";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "No file was uploaded.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "Missing a temporary folder.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Failed to write file to disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "A PHP extension stopped the file upload.";
                break;
            default:
                echo "Unknown error occurred.";
                break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Form</title>
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
    </style>
</head>

<body>

    <div class="form-container">
        <div class="form-title">Add New Product</div>
        <form action="<?php echo ($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">

            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">--Select Category--</option>
                <option value="Engine Parts">Engine Parts</option>
                <option value="Suspension & Steering">Suspension & Steering</option>
                <option value="Braking System">Braking System</option>
                <option value="Electrical Components">Electrical Components</option>
                <option value="Body & Exterior Parts">Body & Exterior Parts</option>
                <option value="Transmission & Drivetrain">Transmission & Drivetrain</option>
                <option value="Cooling & Heating">Cooling & Heating</option>
            </select>

            <label for="productName">Product Name:</label>
            <input type="text" name="productName" id="productName" required>

            <label for="serialNumber">Serial Number:</label>
            <input type="text" name="serialNumber" id="serialNumber" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" required></textarea>

            <label for="ratings">Ratings (1-5):</label>
            <input type="number" name="ratings" id="ratings" min="1" max="5" step="0.1" required>

            <label for="price">Price:</label>
            <input type="number" name="price" id="price" min="0" step="0.01" required>

            <label for="stock">Stock:</label>
            <input type="number" name="stock" id="stock" min="0" step="0.01" required>

            <label for="productImage">Product Image:</label>
            <input type="file" name="productImage" id="productImage" accept="image/*" required>

            <button type="submit">Submit Product</button>
        </form>
    </div>

</body>

</html>

<?php
include('db/dbconn.php'); 

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $update = intval($_GET['id']);
    $sql = "SELECT * FROM `blog` WHERE Id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $update);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$row = $result->fetch_assoc()) {
        die("No Item found with the provided ID.");
    }
} else {
    die("Invalid ID");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Blog</title>
  <link rel="icon" href="Images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="addblog.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background-color: rgb(133, 133, 133);
    }
    .current { color: dark !important; }
  </style>
</head>
<body class="bg-light">

  <section class="d-flex justify-content-center align-items-center min-vh-100" id="formSection">
    <div class="form-container">
      <h2 class="text-center mb-4">Edit Blog</h2>
      <form id="blogForm" action="updateblogdb.php" method="POST" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="hidden" name="updateid" value="<?php echo htmlspecialchars($row['Id']); ?>">
            <input type="text" class="form-control" id="title" value="<?php echo htmlspecialchars($row['BTitle']); ?>" name="title" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="author" class="form-label">Author Name</label>
            <input type="text" class="form-control" id="author" value="<?php echo htmlspecialchars($row['Author']); ?>" name="author" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="file" class="form-label">Upload New Image</label>
            <input type="file" name="fileToUpload" class="form-control" id="file">
            <?php if (!empty($row['Filename'])): ?>
              <small class="form-text text-muted current">Current file: <?php echo htmlspecialchars($row['Filename']); ?></small>
            <?php endif; ?>
          </div>
          <div class="col-md-3 mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" id="category" name="category" required>
              <option value="" disabled selected>Select a category</option>
              <option value="Car Maintenance Tips" <?php echo ($row['category'] == 'Car Maintenance Tips') ? 'selected' : ''; ?>>Car Maintenance Tips</option>
              <option value="Service Station Updates" <?php echo ($row['category'] == 'Service Station Updates') ? 'selected' : ''; ?>>Service Station Updates</option>
              <option value="Automotive Troubleshooting" <?php echo ($row['category'] == 'Automotive Troubleshooting') ? 'selected' : ''; ?>>Automotive Troubleshooting</option>
              <option value="Customer Stories and Testimonials" <?php echo ($row['category'] == 'Customer Stories and Testimonials') ? 'selected' : ''; ?>>Customer Stories and Testimonials</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="" disabled>Select a Status</option>
              <option value="Normal" <?php echo ($row['status'] == 'Normal') ? 'selected' : ''; ?>>Normal</option>
              <option value="Special" <?php echo ($row['status'] == 'Special') ? 'selected' : ''; ?>>Special</option>
              <option value="Featured" <?php echo ($row['status'] == 'Featured') ? 'selected' : ''; ?>>Featured</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label for="Content" class="form-label">Content</label>
          <textarea class="form-control" id="Content" name="Content" rows="15" required><?php echo htmlspecialchars($row['BContent']); ?></textarea>
        </div>

        <div class="row">
          <div class="col-md-4 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
          <div class="col-md-4 text-center">
            <button class="btn btn-secondary" type="button" onclick="window.location.href='admin.php';">Cancel</button>
          </div>
          <div class="col-md-4 text-center">
            <button type="button" class="btn btn-danger" onclick="confirmClear()">Clear</button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>
    function confirmClear() {
      if (confirm("Are you sure you want to clear all fields? This action cannot be undone.")) {
        document.getElementById("blogForm").reset();
      }
    }
  </script>
</body>
</html>

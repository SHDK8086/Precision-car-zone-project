<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Blog</title>
  <link rel="icon" href="Images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="addblog.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: rgb(133, 133, 133);
    }
  </style>
</head>

<body class="bg-light">

  <section class="d-flex justify-content-center align-items-center min-vh-100" id="formSection">
    <div class="form-container">
      <form id="blogForm" action="addblogdb.php" method="POST" enctype="multipart/form-data">
        <div class="form-title mb-4">
          Add Blog Details
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" placeholder="Title of the Blog" name="title" required>
          </div>
          <div class="col-md-6 mb-3">
            <label for="author" class="form-label">Author Name</label>
            <input type="text" class="form-control" id="author" placeholder="Author's Name" name="author" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="file" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="file" name="fileToUpload" required>
          </div>
          <div class="col-md-3 mb-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" id="category" name="category" required>
              <option value="" disabled selected>Select a category</option>
              <option value="Car Maintenance Tips">Car Maintenance Tips</option>
              <option value="Service Station Updates">Service Station Updates</option>
              <option value="Automotive Troubleshooting">Automotive Troubleshooting</option>
              <option value="Customer Stories and Testimonials">Customer Stories and Testimonials</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="" disabled selected>Select a Status</option>
              <option value="Normal">Normal</option>
              <option value="Special">Special</option>
              <option value="Featured">Featured</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label for="Content" class="form-label">Content</label>
          <textarea class="form-control" id="Content" placeholder="Content" name="Content" rows="15" required></textarea>
        </div>

        <div class="row">
          <div class="col-md-4 text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
          <div class="col-md-4 text-center">
            <button class="btn btn-secondary" type="button" onclick="window.location.href='SuperAdminDashboard.php';">Cancel</button>
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

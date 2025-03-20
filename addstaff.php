<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Staff</title>
  <link rel="icon" href="Images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="addstaff.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
</head>

<body class="bg-light">

  <section class="d-flex justify-content-center align-items-center min-vh-100" id="formSection">
    <div class="form-container">
      <form action="addstaffdb.php" method="POST" enctype="multipart/form-data">
        <div class="form-title">
          Add Staff
        </div>

        <div class="row">
          <div class="col-md-6">
            <label for="fname" class="labels">First Name</label>
            <input id="fname" name="fname" type="text" class="form-control" placeholder="First name" required>
          </div>
          <div class="col-md-6">
            <label for="lname" class="labels">Last Name</label>
            <input id="lname" name="lname" type="text" class="form-control" placeholder="Last Name" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <label for="email" class="labels">Email</label>
            <input id="email" name="email" type="email" class="form-control" placeholder="Enter email" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <label for="address" class="labels">Address</label>
            <input id="address" name="address" type="text" class="form-control" placeholder="Enter Address" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <label for="mnumber" class="labels">Mobile Number</label>
            <input id="mnumber" name="mnumber" type="text" class="form-control" placeholder="Enter Mobile Number" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 text-center">
            <button class="btn btn-primary profile-button" type="submit" name="submit">Register</button>
          </div>
          <div class="col-md-6 text-center">
            <button class="btn btn-secondary" type="button" onclick="window.location.href='staffadminDashboard.php';">Cancel</button>
          </div>
        </div>
      </form>
    </div>
  </section>

  <div id="popupBackground" class="popup-background" style="display: none;"></div>
  <div id="popup" class="popup" style="display: none;">
    <p id="popupMessage"></p>
    <button onclick="redirectToDashboard()">OK</button>
  </div>
  <script src="addstaff.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>

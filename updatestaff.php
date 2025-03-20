<?php
include('db/dbconn.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $update = intval($_GET['id']);
    $sql = "SELECT * FROM `staff-table` WHERE Id = ?";  
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $update);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the result is empty
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
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
  <title>Add Staff</title>
  <link rel="icon" href="Images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="addstaff.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
  <style>
    #togglePassword {
      float: right;
      position: relative;
      margin-left: -25px;
      margin-top: -50px;
      right: 10px;
      z-index: 2;
      cursor: pointer;
      color: Black;
    }

    #togglePassword:active {
      color: gray;
    }

    .input-group {
      position: relative;
    }
  </style>
</head>

<body class="bg-light">

  <section class="d-flex justify-content-center align-items-center min-vh-100" id="formSection">
    <div class="form-container">
      <form action="updatestaffdb.php" method="POST" enctype="multipart/form-data">
        <div class="form-title">
          Add Staff
        </div>

        <div class="row">
          <div class="col-md-6">
          <input type="hidden" name="updateid" value="<?php echo htmlspecialchars($row['Id']); ?>">
            <label for="fname" class="labels">First Name</label>
            <input id="fname" name="fname" type="text" class="form-control" value="<?php echo htmlspecialchars($row['fname']); ?>" required>
          </div>
          <div class="col-md-6">
            <label for="lname" class="labels">Last Name</label>
            <input id="lname" name="lname" type="text" class="form-control" value="<?php echo htmlspecialchars($row['lname']); ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <label for="email" class="labels">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <label for="address" class="labels">Address</label>
            <input id="address" name="address" type="text" class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>" required>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <label for="mnumber" class="labels">Mobile Number</label>
            <input id="mnumber" name="mnumber" type="text" class="form-control" value="<?php echo htmlspecialchars($row['contnumber']); ?>" required>
          </div>
        </div>

        <div class="col-md-12">
            <label for="password" class="labels">Password</label>
            <input id="password" name="password" type="password" class="form-control" placeholder="Password" required>
            <i class="bi bi-eye-slash" id="togglePassword"></i>
        </div>

        <div class="row">
          <div class="col-md-6 text-center">
            <button class="btn btn-primary profile-button" type="submit" name="submit">Update</button>
          </div>
          <div class="col-md-6 text-center">
            <button class="btn btn-secondary" type="button" onclick="window.location.href='staffDashboard.php';">Cancel</button>
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
  <script>
    const togglePassword =document.querySelector('#togglePassword');
    const password=document.querySelector('#password');
    togglePassword.addEventListener('click', (e) =>{
        const type =password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        e.target.classList.toggle('bi-eye');
    });
    </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>

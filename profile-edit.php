<?php
include('db/dbconn.php'); 

$update = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $update = intval($_GET['id']);
} else {
    header("Location: profile.php");
    exit();
}

$sql = "SELECT * FROM `usertable` WHERE Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $update);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

if (!$row) { 
    echo "<script>alert('No user found with the provided ID.'); window.location.href='profile.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="icon" href="Images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="addstaff.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <form action="profileupdate.php" method="POST" enctype="multipart/form-data">
                <div class="form-title">Edit Profile</div>

                <input type="hidden" name="updateid" value="<?php echo htmlspecialchars($row['Id']); ?>">

                <div class="row">
                    <div class="col-md-6">
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
                        <label for="uname" class="labels">Username</label>
                        <input id="uname" name="uname" type="text" class="form-control" value="<?php echo htmlspecialchars($row['username']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="mnumber" class="labels">Mobile Number</label>
                        <input id="mnumber" name="mnumber" type="text" class="form-control" value="<?php echo htmlspecialchars($row['mobilenumber']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <label for="address" class="labels">Address</label>
                        <input id="address" name="address" type="text" class="form-control" value="<?php echo htmlspecialchars($row['useraddress']); ?>" required>
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
                        <label for="password" class="labels">Password</label>
                        <input id="password" name="password" type="password" class="form-control" placeholder="Enter new password" required>
                        <i class="bi bi-eye-slash" id="togglePassword"></i>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 text-center">
                        <button class="btn btn-primary profile-button" type="submit" name="submit">Save Profile</button>
                    </div>
                    <div class="col-md-6 text-center">
                        <button class="btn btn-secondary" type="button" onclick="window.location.href='profile.php';">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <script>
    document.getElementById("togglePassword").addEventListener("click", function() {
      var passwordField = document.getElementById("password");
      if (passwordField.type === "password") {
        passwordField.type = "text";
        this.classList.replace("bi-eye-slash", "bi-eye");
      } else {
        passwordField.type = "password";
        this.classList.replace("bi-eye", "bi-eye-slash");
      }
    });
  </script>
    <script src="usersignup.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

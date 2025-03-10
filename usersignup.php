<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="icon" href="Images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="usersignup.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: url('images/mehmet-talha-onuk-5M-72czGFl4-unsplash.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      filter: blur(5px); 
      z-index: -1; 
    }

    .login-container {
      background: rgba(255, 255, 255, 0.8); 
      border-radius: 12px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
      padding: 30px;
      width: 100%;
      max-width: 600px;
      position: relative;
      z-index: 1;
    }

    .form-control {
      border-radius: 8px;
      padding: 12px;
    }

    .btn-primary {
      border-radius: 8px;
      padding: 10px;
      font-weight: bold;
      width: 100%;
    }

    .error-message {
      color: #d9534f;
      background: #f8d7da;
      padding: 10px;
      border-radius: 8px;
      font-size: 14px;
    }

    .input-group-text {
      background: none;
      border: none;
      cursor: pointer;
    }

    #togglePassword {
      float: right;
      position: relative;
      margin-left: -25px;
      margin-top: -35px;
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
<body class="d-flex justify-content-center align-items-center min-vh-100">

  <div class="login-container">
    <form action="userregisterdb.php" method="POST"> 
      <h3 class="text-center mb-3">Sign Up</h3>

      <div class="row mt-2">
        <div class="col-md-6">
          <label for="fname" class="labels">First Name</label>
          <input id="fname" name="fname" type="text" class="form-control" placeholder="First name" value="<?php echo htmlspecialchars($_GET['fname'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
          <label for="lname" class="labels">Last Name</label>
          <input id="lname" name="lname" type="text" class="form-control" placeholder="Last Name" value="<?php echo htmlspecialchars($_GET['lname'] ?? ''); ?>" required>
        </div>
      </div>

      <div class="row mt-2">
        <div class="col-md-12">
          <label for="uname" class="labels">Username</label>
          <input id="uname" name="uname" type="text" class="form-control" placeholder="Username" value="<?php echo htmlspecialchars($_GET['uname'] ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
          <label for="mnumber" class="labels">Mobile Number</label>
          <input id="mnumber" name="mnumber" type="tel" class="form-control" placeholder="Mobile Number" value="<?php echo htmlspecialchars($_GET['mnumber'] ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
          <label for="address" class="labels">Address</label>
          <input id="address" name="address" type="text" class="form-control" placeholder="Address" value="<?php echo htmlspecialchars($_GET['address'] ?? ''); ?>" required>
        </div>
        <div class="col-md-12">
          <label for="email" class="labels">Email</label>
          <input id="email" name="email" type="email" class="form-control" placeholder="Enter email id" required>
        </div>
        <div class="col-md-12">
          <label for="password" class="labels">Password</label>
          <div class="col-md-12">
            <input id="password" name="password" type="password" class="form-control" placeholder="Password" required>
            <i class="bi bi-eye-slash" id="togglePassword"></i>
          </div>
        </div>
      </div>

      <div class="mt-2 text-center">
        <button class="btn btn-primary" type="submit" name="submit">Sign Up</button>
      </div>

      <div class="text-center mt-2">
        <p>I have an account? <a href="login.php">Log In</a></p>
      </div>
    </form>
  </div>

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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

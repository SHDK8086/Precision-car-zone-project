<?php
include('db/dbconn.php'); 
session_start();

if (isset($_SESSION['Id'], $_SESSION['email'], $_SESSION['user_type'])) {
  $redirectPage = '';

  switch ($_SESSION['user_type']) {
      case 'user':
          $redirectPage = 'index.html';
          break;
      case 'admin':
          $redirectPage = 'SuperAdminDashboard.php';
          break;
      case 'staff':
          $redirectPage = 'staffDashboard.php';
          break;
      case 'staff admin':
          $redirectPage = 'staffadminDashboard.php';
          break;
      default:
          $redirectPage = 'login.php'; 
          break;
  }

  header("Location: $redirectPage");
  exit();
}

$error_message = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$email_value = isset($_SESSION['login_email']) ? $_SESSION['login_email'] : ''; 
unset($_SESSION['login_error']); 
unset($_SESSION['login_email']); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
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
      max-width: 400px;
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
    <form action="logindb.php" method="POST" id="loginForm">
      <h3 class="text-center mb-3">Log In</h3>
      <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : 'index.php' ?>">
      <div class="col-md-12">
        <label for="email" class="labels mt-3 mb-2">Email</label>
        <input 
          id="email" name="email" type="email" class="form-control <?php echo !empty($error_message) ? 'error-border' : ''; ?>" placeholder="Enter email id" value="<?php echo htmlspecialchars($email_value); ?>"  required>
      </div>

      <div class="col-md-12">
        <label for="password" class="labels mt-3 mb-2">Password</label>
        <input id="password" name="password" type="password" class="form-control <?php echo !empty($error_message) ? 'error-border' : ''; ?>" placeholder="Password" required>
        <i class="bi bi-eye-slash" id="togglePassword"></i>
      </div>

      <?php if (!empty($error_message)) { ?>
        <div class="error-message text-center mb-3"><?php echo htmlspecialchars($error_message); ?></div>
      <?php } ?>

      <button class="btn btn-primary mt-4" type="submit" name="submit">Log In</button>

      <div class="text-center mt-3">
        <p>Don't have an account? <a href="usersignup.php">Sign Up</a></p>
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

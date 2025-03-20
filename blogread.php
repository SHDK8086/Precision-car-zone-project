<?php
include('db/dbconn.php'); 
session_start();

$blogId=1;

if (isset($_GET['id'])) {
    $blogId = $_GET['id'];}

$profileImage = !empty($_SESSION['profile_image']) ? 'profileimages/' . $_SESSION['profile_image'] : 'Images/default-profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repair - BlogRead</title>
  <link rel="icon" href="Images/favicon.ico" type="Images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="blog.css">
  <link rel="stylesheet" href="styles.css">
  <script src="script.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color:rgb(216, 216, 216);
      color: #000000;
      font-family: "Inter", sans-serif;
      margin: 0;
      padding: 0;
    }

    .goal{
      width: 20%;
      height: 70px;
    }
    .account {
      width: 35px;
      height: 35px;
      object-fit: cover; 
      border-radius: 50%; 
      border: 2px solid #fff; 
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); 
    }

    .nav-item {
      list-style: none !important;
      padding: 0;
      margin: 0;
    }
  </style>
</head>
<body>
  <nav class="custom-navbar">
      <div class="logo">REPAIR</div>
      <div class="nav-links">
          <a href="index.php">Home</a>
          <a href="index.php#about">About Us</a>
          <a href="index.php#service">Services</a>
          <a href="index.php#testimonials">Reviews</a>
          <a href="blog.php">Blog</a>
          <a href="index.php#contact">Contact</a>
        
      </div>
      <?php
        if (!isset($_SESSION['Id']) || !isset($_SESSION['email'])): 
        ?>
          <li class="nav-item">
              <a href="login.php" class="booking-btn">Sign In</a>
          </li>
        <?php else: ?>
          <li class="nav-item text-center text-lg-start mt-4 mt-sm-0">
              <a href="profile.php" class="nav-link profile">
                  <img class="account rounded-profile" src="<?php echo $profileImage; ?>" alt="Profile Image">
              </a>
          </li>
      <?php endif; ?>
  </nav>

    <section class="featured-blog">
      <div class="container">
        <div class="row">
        <?php
          $query = "SELECT * FROM blog WHERE Id = $blogId";
          $result = mysqli_query($conn, $query);

          if (!$result) {
              die("Query Failed: " . mysqli_error($conn)); 
          }

          while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="col-md-8 d-flex flex-column justify-content-start mt-4">
            <h1 class="mb-4 text-dark"><?php echo htmlspecialchars($row['BTitle']); ?></h1>
            <img class="featured-hero" alt="Featured Image" src="Blogimages/<?php echo htmlspecialchars($row['Filename']); ?>">  
            <h5 class="B-category mt-2 text-dark"><?php echo htmlspecialchars($row['category']); ?></h5>
            <p class="blog-author-date p-0 m-0">
                <span class="author-name text-dark"><?php echo htmlspecialchars($row['Author']); ?></span> - 
                <span class="post-date text-dark"><?php echo htmlspecialchars($row['Create-Day']); ?></span>
            </p>

            <p class="mt-3 text-dark"><?php echo nl2br(htmlspecialchars($row['BContent'])); ?></p>
            </div>
          <?php } ?>

          <div class="col-md-4">
            <div class="row">

              <?php
              $query = "SELECT * FROM blog WHERE status = 'Featured'LIMIT 8";
              $result = mysqli_query($conn, $query);
              
              if (!$result) {
                  die("Query Failed: " . mysqli_error($conn)); 
              }

              while ($row = mysqli_fetch_assoc($result)) { ?>
              <div class="col-md-12 mb-3 blog-post mt-4">
                <a href="blogread.php?id=<?php echo htmlspecialchars($row['Id']); ?>" class="text-decoration-none d-flex align-items-center">
                  <img class="col-md-6" src="Blogimages/<?php echo htmlspecialchars($row['Filename']); ?>" alt="Blog Post 1">
                  <div class="blog-info col-md-6">
                    <p class="p-0 m-0 text-dark" ><?php echo htmlspecialchars($row['category']); ?></p>
                    <p class="blog-title p-0 m-0 text-dark"><?php echo htmlspecialchars($row['BTitle']); ?></p>
                    <p class="blog-author-date p-0 m-0">
                      <span class="author-name text-dark"><?php echo htmlspecialchars($row['Author']); ?></span> - 
                      <span class="post-date text-dark"><?php echo htmlspecialchars($row['Create-Day']); ?></span>
                    </p>
                  </div>
                </a>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="footer">
    <div class="footer-row">
      <div class="footer-col">
        <h4>Info</h4>
        <ul class="links">
          <li><a href="#home">Home</a></li>
          <li><a href="#about">About Us</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#review">Reviews</a></li>
          <li><a href="#service">Service</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Explore</h4>
        <ul class="links">
          <li><a href="#">Oil Change</a></li>
          <li><a href="#">Battery Service</a></li>
          <li><a href="#">Tire Services</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Newsletter</h4>
        <p>
          Subscribe to our newsletter for a weekly dose
          of news, updates, helpful tips, and
          exclusive offers.
        </p>
        <form action="#">
          <input type="text" placeholder="Your email" required>
          <button type="submit">SUBSCRIBE</button>
        </form>
        <div class="icons">
          <i class="fa-brands fa-facebook-f"></i>
          <i class="fa-brands fa-twitter"></i>
          <i class="fa-brands fa-linkedin"></i>
          <i class="fa-brands fa-github"></i>
        </div>
      </div>
    </div>
  </section>
    <script src="index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

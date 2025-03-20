<?php
include('db/dbconn.php'); 
session_start();

$profileImage = !empty($_SESSION['profile_image']) ? 'profileimages/' . $_SESSION['profile_image'] : 'images/default-profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Repair - Blog</title>
  <link rel="icon" href="Images/favicon.ico" type="Images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="blog.css">
  <link rel="stylesheet" href="styles.css">
  <script src="script.js"></script>
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Starter Page - Precision Car Zone</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
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
        <h1 class="mb-4 text-dark mt-3">Featured Blog Posts</h1>
        <div class="row">
        <?php
          $query = "SELECT * FROM blog WHERE status = 'Special' LIMIT 1";
          $result = mysqli_query($conn, $query);

          if (!$result) {
              die("Query Failed: " . mysqli_error($conn)); 
          }

          while ($row = mysqli_fetch_assoc($result)) { ?>
          <div class="col-md-7 d-flex flex-column justify-content-start">
              <a href="blogread.php?id=<?php echo htmlspecialchars($row['Id']); ?>" class="text-decoration-none">
                  <img class="featured-hero" alt="Featured Image" src="Blogimages/<?php echo htmlspecialchars($row['Filename']); ?>">  
                  <h6 class="B-category text-dark"><?php echo htmlspecialchars($row['category']); ?></h6>
                  <h3 class="B-Title text-dark"><?php echo htmlspecialchars($row['BTitle']); ?></h3>
              </a>
              <div class="d-flex align-items-center justify-content-start">
                  <p class="blog-author-date">
                      <span class="author-name text-dark"><?php echo htmlspecialchars($row['Author']); ?></span> - 
                      <span class="post-date text-dark"><?php echo htmlspecialchars($row['Create-Day']); ?></span>
                  </p>
              </div>
          </div>
          <?php } ?>

          <div class="col-md-5">
            <div class="row">
              <?php
              $query = "SELECT * FROM blog WHERE status = 'Featured'LIMIT 4";
              $result = mysqli_query($conn, $query);
              
              if (!$result) {
                  die("Query Failed: " . mysqli_error($conn)); 
              }

              while ($row = mysqli_fetch_assoc($result)) { ?>
              <div class="col-md-12 mb-3 blog-post">
                <a href="blogread.php?id=<?php echo htmlspecialchars($row['Id']); ?>" class="text-decoration-none d-flex align-items-center">
                  <img class="col-md-6" src="Blogimages/<?php echo htmlspecialchars($row['Filename']); ?>" alt="Blog Post 1">
                  <div class="blog-info col-md-6">
                    <p class="p-0 m-0 text-dark"><?php echo htmlspecialchars($row['category']); ?></p>
                    <p class="blog-title p-0 m-0 text-dark"><?php echo htmlspecialchars($row['BTitle']); ?></p>
                    <p class="blog-author-date p-0 m-0 text-dark">
                      <span class="author-name"><?php echo htmlspecialchars($row['Author']); ?></span> - 
                      <span class="post-date"><?php echo htmlspecialchars($row['Create-Day']); ?></span>
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

    <section class="blog">
      <div class="container">
        <div class="row mt-n5" id="blog-container">
          <h1 class="text-dark text-center">Blog</h1>

          <?php
              $query = "SELECT * FROM blog ORDER BY id DESC";
              $result = mysqli_query($conn, $query);
              
              if (!$result) {
                  die("Query Failed: " . mysqli_error($conn)); 
              }

              while ($row = mysqli_fetch_assoc($result)) { ?>
          <div class="col-md-6 col-lg-4 col-6 mt-5 text-dark wow fadeInUp" data-wow-delay=".2s" style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInUp;">
            <a href="blogread.php?id=<?php echo htmlspecialchars($row['Id']); ?>" class="text-decoration-none">
              <div class="blog-grid">
                <div class="blog-grid-img position-relative">
                  <img class="allblog-img" alt="img" src="Blogimages/<?php echo htmlspecialchars($row['Filename']); ?>">
                </div>
                <div class="blog-grid-text p-4 text-dark">
                  <h6 class="text-dark"><?php echo htmlspecialchars($row['category']); ?></h6>
                  <h3 class="h5 mb-3 text-dark"><a class="text-decoration-none text-dark" href="#!"><?php echo htmlspecialchars($row['BTitle']); ?></a></h3>
                  <div class="meta text-dark meta-style2">
                    <div class="row justify-content-between">
                      <p class="col-auto mr-5"><span class="author-name text-dark"><?php echo htmlspecialchars($row['Author']); ?></span></p>
                      <p class="col-auto ml-5"><span class="post-date text-dark"><?php echo htmlspecialchars($row['Create-Day']); ?></span></p>
                    </div>
                  </div>
                </div>
              </div>
            </a>
          </div>
          <?php } ?>
        </div>
        <div class="row mt-6 wow fadeInUp" data-wow-delay=".6s" style="visibility: visible; animation-delay: 0.6s; animation-name: fadeInUp;">
          <div class="col-12">
            <div class="pagination text-small text-uppercase text-extra-dark-gray">
              <ul id="pagination-controls">
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="description text-dark text-center">
      <div class="container">
        <h6 class="mt-5">Explore</h6>
        <h1>Discover Our Essential Car Care Tips & Services</h1>
        <p class="mb-4">
          At our service station, we believe in keeping your vehicle in top condition. 
          Explore our expert tips and services to ensure a smooth and safe drive.
        </p>
        <div class="row col-md justify-content-between">
          <div class="col-md-4 col-12 mb-3">
            <img src="images/oil-change.png" alt="Oil Change" class="mb-2 goal">
            <h3>Regular Maintenance for a Smooth Ride</h3>
            <p>From oil changes to tire rotations, learn how to keep your car running like new.</p>
          </div>

          <div class="col-md-4 col-12">
            <img src="images/car-repair.png" alt="Car Repair" class="mb-2 goal">
            <h3>Expert Repairs for Every Vehicle</h3>
            <p>We handle brake fixes, engine diagnostics, and electrical repairs with precision.</p>
          </div>

          <div class="col-md-4 col-12">
            <img src="images/car-care.png" alt="Car Care" class="mb-2 goal">
            <h3>Seasonal Car Care Tips</h3>
            <p>Get ready for winter, summer, and rainy seasons with our expert car maintenance guides.</p>
          </div>
        </div>
      </div>
    </section>

   
    <footer id="footer" class="footer dark-background">
    <div class="container">
      <h3 class="sitename">Precision Car Zone</h3>
      <p> Whether you need a quick tune-up or a complete overhaul, our mission is to keep your vehicle running at its best while prioritizing safety, reliability, and customer satisfaction.</p>
      <div class="social-links d-flex justify-content-center">
        <a href=""><i class="bi bi-twitter-x"></i></a>
        <a href=""><i class="bi bi-facebook"></i></a>
        <a href=""><i class="bi bi-instagram"></i></a>
        <a href=""><i class="bi bi-skype"></i></a>
        <a href=""><i class="bi bi-linkedin"></i></a>
      </div>
      <div class="container">
        <div class="copyright">
          <span>Copyright</span> <strong class="px-1 sitename">Precision Car Zone</strong> <span>All Rights Reserved</span>
        </div>
        <div class="credits">
        </div>
      </div>
    </div>
  </footer>


  <script src="index.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
</body>
</html>
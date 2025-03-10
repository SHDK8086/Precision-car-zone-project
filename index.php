<?php
if (isset($_SESSION['Id']) && isset($_SESSION['email'])) {
  if ($_SESSION['user_type'] === 'user') {
      header("location:profile.php");
  }elseif ($_SESSION['user_type'] === 'admin') {
      header("location:SuperAdminDashboard.php");
  }elseif ($_SESSION['user_type'] === 'staff') {
      header("location:staffDashboard.php");
  }elseif ($_SESSION['user_type'] === 'staff admin') {
    header("location:staffadminDashboard.php");
}
  exit(); 
}

session_start();

$profileImage = !empty($_SESSION['profile_image']) ? 'profileimages/' . $_SESSION['profile_image'] : 'images/default-profile.jpg';

$activeBookingsCount = 0;
$activeBookings = [];

$statusToProgress = [
  'pending' => 0,
  'check_in' => 1,
  'inspection' => 2,
  'maintenance' => 3,
  'cleaning' => 4,
  'billing' => 5,
  'completed' => 6
];

$progressLabels = [
  0 => "Pending",
  1 => "Check-In",
  2 => "Pre-Service Inspection",
  3 => "Mechanical & Maintenance Work",
  4 => "Exterior & Interior Cleaning",
  5 => "Final Inspection",
  6 => "Service Completion and Payment"
];

// Initialize variables
$activeBookings = [];
$activeBookingsCount = 0;

// Only proceed if user is logged in
if (isset($_SESSION['Id'])) {
  $current_user_id = $_SESSION['Id'];
  
  try {
      // Create a new database connection with hardcoded credentials
      // IMPORTANT: Replace these with your actual database credentials
      $db_server = "localhost";
      $db_username = "root";
      $db_password = "";
      $db_name = "service_center";
      
      $popup_conn = new mysqli($db_server, $db_username, $db_password, $db_name);
      
      if (!$popup_conn->connect_error) {
          // First find the customer_id for the current user
          $customer_id = null;
          
          // Try to find customer by matching email
          if (isset($_SESSION['email'])) {
              $user_email = $_SESSION['email'];
              $customer_query = "SELECT customer_id FROM customers WHERE email = ?";
              $stmt = $popup_conn->prepare($customer_query);
              
              if ($stmt) {
                  $stmt->bind_param("s", $user_email);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  
                  if ($result && $result->num_rows > 0) {
                      $customer = $result->fetch_assoc();
                      $customer_id = $customer['customer_id'];
                  }
                  
                  $stmt->close();
              }
          }
          
          // If no match by email, try user_id directly (if it exists)
          if (!$customer_id) {
              $customer_query = "SELECT customer_id FROM customers WHERE user_id = ? LIMIT 1";
              $stmt = $popup_conn->prepare($customer_query);
              
              if ($stmt) {
                  $stmt->bind_param("i", $current_user_id);
                  $stmt->execute();
                  $result = $stmt->get_result();
                  
                  if ($result && $result->num_rows > 0) {
                      $customer = $result->fetch_assoc();
                      $customer_id = $customer['customer_id'];
                  }
                  
                  $stmt->close();
              }
          }
          
          // If we found a customer_id, get their bookings
          if ($customer_id) {
            $query = "SELECT b.*, b.progress_status, v.vehicle_number, v.vehicle_model 
          FROM bookings b
          JOIN vehicles v ON b.vehicle_id = v.vehicle_id
          WHERE b.customer_id = ? 
          AND b.status != 'completed' 
          ORDER BY b.booking_date DESC";
                        
              $stmt = $popup_conn->prepare($query);
              $stmt->bind_param("i", $customer_id);
              $stmt->execute();
              $result = $stmt->get_result();
              
              if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      // Get progress status from the text status
                      $status = isset($row['status']) ? strtolower($row['status']) : 'pending';
                      $progressNum = isset($statusToProgress[$status]) ? $statusToProgress[$status] : 0;
                      $row['progress_num'] = $progressNum;
                      
                      $activeBookings[] = $row;
                  }
                  $activeBookingsCount = count($activeBookings);
              }
              
              $stmt->close();
          }
          
          $popup_conn->close();
      }
  } catch (Exception $e) {
      // Silently handle errors
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Precision Car Zone</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/Logo.svg" rel="icon">
  <link href="assets/img/Logo.svg" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .account {
        width: 35px;
        height: 35px;
        object-fit: cover; 
        border-radius: 50%; 
        border: 2px solid #fff; 
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); 
    }
  </style>

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <h1 class="sitename">Precision Car Zone</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="blog.php">Blog</a></li>
          <li><a href="#review">Reviews</a></li>
          <li><a href="#contact">Contact</a></li>
          <!-- <li><a href="#login">Sign In</a></li> -->
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
          <div class="ongoing-activity-icon me-3">
            <a href="javascript:void(0)" onclick="openCartPopup()">
              <div class="d-flex flex-column align-items-center">
                <img src="images/ongoing.png" class="cart-icon">
                <span class="activity-text">Ongoing Activity</span>
                <?php if ($activeBookingsCount > 0): ?>
                  <span id="cart-badge" class="cart-badge"><?php echo $activeBookingsCount; ?></span>
                <?php endif; ?>
              </div>
            </a>
          </div>

        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background">

    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-8 d-flex flex-column justify-content-center align-items text-center text-md-start" data-aos="fade-up">
          <h2>Keeping Your Car New</h2>
          <p>Experience top-notch car repair services at us</p>
          <div class="d-flex mt-4 justify-content-center justify-content-md-start">
              <a href="Store.php" class="download-btn">
                  <i class="bi bi-shop"></i> <span>View Store</span>
              </a>
              <?php 
              $redirectUrl = urlencode("Booking.php?user_id=" . (isset($_SESSION['Id']) ? $_SESSION['Id'] : ''));

              if (isset($_SESSION['Id']) && isset($_SESSION['email']) && $_SESSION['user_type'] === 'user'): ?>
                  <a href="Booking.php?user_id=<?php echo $_SESSION['Id']; ?>" class="download-btn">
                      <i class="bi bi-calendar-check"></i> <span>Book Now</span>
                  </a>
              <?php else: ?>
                  <a href="login.php?redirect=<?= $redirectUrl ?>" class="download-btn">
                      <i class="bi bi-calendar-check"></i> <span>Book Now</span>
                  </a>
              <?php endif; ?>
          </div>
        </div>
      </div>
    </div>


    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-xl-center gy-5">
          <div class="col-xl-5 content">
            <h3>About Us</h3>
            <h2>Why Choose Our Expert Auto Services?</h2>
            <p>
              At <strong>AutoCare Professionals</strong>, we bring over 15 years of industry-leading expertise to ensure your vehicle receives the care it deserves. 
            </p>
            <a href="#" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
          </div>
          <div class="col-xl-7">
            <div class="row gy-4 icon-boxes">
              <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="icon-box">
                  <i class="bi bi-speedometer2"></i>
                  <h3>Engine Diagnostics</h3>
                  <p>Accurate and detailed engine diagnostics to identify and address any issues affecting performance, ensuring your car runs smoothly.</p>
                </div>
              </div> <!-- End Icon Box -->
              <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-box">
                  <i class="bi bi-tools"></i>
                  <h3>Body Repairs & Painting</h3>
                  <p>Restore your vehicle’s aesthetics with professional auto body repairs and premium-grade painting services.</p>
                </div>
              </div> <!-- End Icon Box -->
              <div class="col-md-6" data-aos="fade-up" data-aos-delay="400">
                <div class="icon-box">
                  <i class="bi bi-lightning-charge"></i>
                  <h3>Advanced Electrical Systems</h3>
                  <p>Comprehensive solutions for electrical issues, including diagnostics, repair, and upgrades for modern vehicles.</p>
                </div>
              </div> <!-- End Icon Box -->
              <div class="col-md-6" data-aos="fade-up" data-aos-delay="500">
                <div class="icon-box">
                  <i class="bi bi-shield-check"></i>
                  <h3>Preventive Maintenance</h3>
                  <p>Regular maintenance and tune-ups designed to prevent potential problems and extend your vehicle’s lifespan.</p>
                </div>
              </div> <!-- End Icon Box -->
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /About Section -->

    <!-- Featured Section -->
    <section id="services" class="featured section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Quality Auto Services You Can Trust</h2>
        <p>Ensuring top-tier vehicle maintenance and repair solutions for a smooth and safe driving experience.</p>
      </div><!-- End Section Title -->
    
      <div class="container">
    
        <div class="row gy-4" data-aos="fade-up" data-aos-delay="100">
    
          <div class="col-md-4">
            <div class="card">
              <div class="img">
                <img src="assets/img/undraw_car-repair_wski.svg" alt="" class="img-fluid">
                <div class="icon"><i class="bi bi-tools"></i></div>
              </div>
              <h2 class="title">Professional Auto Repairs</h2>
              <p>
                From minor fixes to major overhauls, our certified technicians provide expert auto repair services using high-quality parts and equipment.
              </p>
            </div>
          </div><!-- End Card Item -->
    
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card">
              <div class="img">
                <img src="assets/img/undraw_towing_e407.svg" alt="" class="img-fluid">
                <div class="icon"><i class="bi bi-speedometer2"></i></div>
              </div>
              <h2 class="title">Computerized Diagnostics</h2>
              <p>
                Our advanced diagnostic tools detect and resolve engine, transmission, and electrical issues to keep your vehicle running smoothly.
              </p>
            </div>
          </div><!-- End Card Item -->
    
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card">
              <div class="img">
                <img src="assets/img/undraw_vintage_q09n.svg" alt="" class="img-fluid">
                <div class="icon"><i class="bi bi-ev-front-fill"></i></div>
              </div>
              <h2 class="title">Preventive Maintenance</h2>
              <p>
                Stay ahead of potential problems with routine maintenance, including oil changes, brake inspections, and tire services for longevity and performance.
              </p>
            </div>
          </div><!-- End Card Item -->
    
        </div>
    
      </div>
    
    </section>
    <!-- /Featured Section -->

    <!-- Cards Section -->
    <section id="cards" class="cards section">

      <div class="container">
    
        <div class="row gy-4">
    
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card-item">
              <span>01</span>
              <h4>Book an Appointment</h4>
              <p>Schedule your service online or visit our service station to book an appointment at your convenience.</p>
            </div>
          </div><!-- Card Item -->
    
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card-item">
              <span>02</span>
              <h4><a href="" class="stretched-link">Vehicle Servicing</a></h4>
              <p>Our certified mechanics inspect and service your vehicle using high-quality parts and equipment.</p>
            </div>
          </div><!-- Card Item -->
    
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card-item">
              <span>03</span>
              <h4><a href="" class="stretched-link">Easy Payment</a></h4>
              <p>Make a secure payment via cash, card, or online methods after your service is complete.</p>
            </div>
          </div><!-- Card Item -->
    
        </div>
    
      </div>
    
    </section>
    <!-- /Cards Section -->

    <!-- Features Section -->
    <section id="features" class="features section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Our Services</h2>
        <p>Experience top-quality vehicle maintenance and repair services at our service station.</p>
      </div><!-- End Section Title -->
    
      <div class="container">
    
        <div class="row gy-4 align-items-center features-item">
          <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
            <img src="assets/img/women repair.jpg" class="img-fluid" alt="Vehicle Inspection">
          </div>
          <div class="col-md-7" data-aos="fade-up" data-aos-delay="100">
            <h3>Comprehensive Vehicle Inspection</h3>
            <p class="fst-italic">Our expert mechanics perform a thorough inspection to ensure your vehicle runs smoothly.</p>
            <ul>
              <li><i class="bi bi-check"></i><span> Engine, brakes, and suspension system check.</span></li>
              <li><i class="bi bi-check"></i> <span>Fluid and oil level assessment.</span></li>
              <li><i class="bi bi-check"></i> <span>Battery and tire condition evaluation.</span></li>
            </ul>
          </div>
        </div><!-- Features Item -->
    
        <div class="row gy-4 align-items-center features-item">
          <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="200">
            <img src="assets/img/vehicle exchange.jpg" class="img-fluid" alt="Repair Services">
          </div>
          <div class="col-md-7 order-2 order-md-1" data-aos="fade-up" data-aos-delay="200">
            <h3>Professional Repair Services</h3>
            <p class="fst-italic">We handle all types of vehicle repairs using high-quality parts and expert care.</p>
            <p>
              From minor fixes to major overhauls, our technicians ensure your vehicle remains reliable and safe on the road.
            </p>
          </div>
        </div><!-- Features Item -->
    
        <div class="row gy-4 align-items-center features-item">
          <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out">
            <img src="assets/img/driving.jpg" class="img-fluid" alt="Regular Maintenance">
          </div>
          <div class="col-md-7" data-aos="fade-up">
            <h3>Regular Maintenance & Tune-Ups</h3>
            <p>Keep your car in top condition with routine maintenance services tailored to your vehicle's needs.</p>
            <ul>
              <li><i class="bi bi-check"></i> <span>Oil changes and fluid top-ups.</span></li>
              <li><i class="bi bi-check"></i><span> Tire rotation and alignment services.</span></li>
              <li><i class="bi bi-check"></i> <span>Brake system servicing.</span></li>
            </ul>
          </div>
        </div><!-- Features Item -->
    
        <div class="row gy-4 align-items-center features-item">
          <div class="col-md-5 order-1 order-md-2 d-flex align-items-center" data-aos="zoom-out">
            <img src="assets/img/repair.jpg" class="img-fluid" alt="Car Wash & Detailing">
          </div>
          <div class="col-md-7 order-2 order-md-1" data-aos="fade-up">
            <h3>Car Wash & Detailing</h3>
            <p class="fst-italic">Give your car a fresh look with our premium car wash and detailing services.</p>
            <p>
              We offer interior and exterior cleaning, waxing, and polishing to make your car shine like new.
            </p>
          </div>
        </div><!-- Features Item -->
    
      </div>
    
    </section>
    <!-- /Features Section -->

    <!-- Gallery Section -->
 
    <!-- Reviews Section -->
    <section id="review" class="testimonials section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Reviews</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-1.jpg" class="testimonial-img" alt="">
                <h3>Saul Goodman</h3>
                <h4>Ceo &amp; Founder</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-2.jpg" class="testimonial-img" alt="">
                <h3>Sara Wilsson</h3>
                <h4>Designer</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-3.jpg" class="testimonial-img" alt="">
                <h3>Jena Karlis</h3>
                <h4>Store Owner</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-4.jpg" class="testimonial-img" alt="">
                <h3>Matt Brandon</h3>
                <h4>Freelancer</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

            <div class="swiper-slide">
              <div class="testimonial-item">
                <img src="assets/img/testimonials/testimonials-5.jpg" class="testimonial-img" alt="">
                <h3>John Larson</h3>
                <h4>Entrepreneur</h4>
                <div class="stars">
                  <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                </div>
                <p>
                  <i class="bi bi-quote quote-icon-left"></i>
                  <span>Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam culpa fore nisi cillum quid.</span>
                  <i class="bi bi-quote quote-icon-right"></i>
                </p>
              </div>
            </div><!-- End testimonial item -->

          </div>
          <div class="swiper-pagination"></div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Need More Details Contact Us</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-4">
            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
              <i class="bi bi-geo-alt flex-shrink-0"></i>
              <div>
                <h3>Address</h3>
                <p>No: 365, D.S. Senanayake Street, Kandy.</p>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-telephone flex-shrink-0"></i>
              <div>
                <h3>Call Us</h3>
                <p>076 066 5594</p>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-envelope flex-shrink-0"></i>
              <div>
                <h3>Email Us</h3>
                <p>info.carzone@email.com</p>
              </div>
            </div><!-- End Info Item -->

          </div>

          <div class="col-lg-8">
            <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">

                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
                </div>

                <div class="col-md-6 ">
                  <input type="email" class="form-control" name="email" placeholder="Your Email" required="">
                </div>

                <div class="col-md-12">
                  <input type="text" class="form-control" name="subject" placeholder="Subject" required="">
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Message" required=""></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Your message has been sent. Thank you!</div>

                  <button type="submit">Send Message</button>
                </div>

              </div>
            </form>
          </div><!-- End Contact Form -->

        </div>

      </div>

    </section><!-- /Contact Section -->
    
    <div id="activityPopup" class="activity-popup">
  <div class="activity-popup-content">
    <span class="close-popup" onclick="closePopup()">&times;</span>
    
    <?php if ($activeBookingsCount > 0): ?>
      <?php 
        // Get the most recent booking
        $currentBooking = $activeBookings[0];
        
        // Determine progress status - first check for progress_status column
        if (isset($currentBooking['progress_status'])) {
            $progress_status = (int)$currentBooking['progress_status'];
        } else {
            // Get progress status from the text status
            $status = isset($currentBooking['status']) ? strtolower($currentBooking['status']) : 'pending';
            $progress_status = isset($statusToProgress[$status]) ? $statusToProgress[$status] : 0;
        }
        
        // Format service type for display
        $serviceDisplay = ucwords(str_replace('_', ' ', $currentBooking['service_type']));
      ?>
      
      <h3 class="popup-title">Ongoing Service Status</h3>
      <div class="booking-details mb-3">
        <p><strong>Vehicle:</strong> <?php echo htmlspecialchars($currentBooking['vehicle_number']); ?> - <?php echo htmlspecialchars($currentBooking['vehicle_model']); ?></p>
        <p><strong>Service:</strong> <?php echo $serviceDisplay; ?></p>
        <p><strong>Status:</strong> <?php echo $progressLabels[$progress_status]; ?></p>
        <p class="text-muted small">
            Status text: <?php echo isset($currentBooking['status']) ? $currentBooking['status'] : 'N/A'; ?> | 
            Progress value: <?php echo $progress_status; ?> | 
            Updated: <?php echo date('Y-m-d H:i:s'); ?>
        </p>
      </div>
      
      <!-- Simple Progress Bar Implementation -->
      <div class="progress-container">
          <!-- Progress Bar -->
          <div class="progress-bar-wrapper">
              <div class="progress-bar-fill" style="width: <?php echo min(($progress_status / 6) * 100, 100); ?>%"></div>
          </div>
          
          <!-- Progress Steps -->
          <div class="progress-steps">
              <!-- Step 1: Check-In -->
              <div class="step <?php echo ($progress_status > 0) ? 'completed' : ($progress_status == 0 ? 'active' : ''); ?>">
                  <div class="step-dot"></div>
                  <span class="step-label">Check-In</span>
              </div>
              
              <!-- Step 2: Inspection -->
              <div class="step <?php echo ($progress_status > 1) ? 'completed' : ($progress_status == 1 ? 'active' : ''); ?>">
                  <div class="step-dot"></div>
                  <span class="step-label">Inspection</span>
              </div>
              
              <!-- Step 3: Maintenance -->
              <div class="step <?php echo ($progress_status > 2) ? 'completed' : ($progress_status == 2 ? 'active' : ''); ?>">
                  <div class="step-dot"></div>
                  <span class="step-label">Maintenance</span>
              </div>
              
              <!-- Step 4: Cleaning -->
              <div class="step <?php echo ($progress_status > 3) ? 'completed' : ($progress_status == 3 ? 'active' : ''); ?>">
                  <div class="step-dot"></div>
                  <span class="step-label">Cleaning</span>
              </div>
              
              <!-- Step 5: Billing -->
              <div class="step <?php echo ($progress_status > 4) ? 'completed' : ($progress_status == 4 ? 'active' : ''); ?>">
                  <div class="step-dot"></div>
                  <span class="step-label">Final Inspection</span>
              </div>
              
              <!-- Step 6: Completion -->
              <div class="step <?php echo ($progress_status > 5) ? 'completed' : ($progress_status == 5 ? 'active' : ''); ?>">
                  <div class="step-dot"></div>
                  <span class="step-label">Completion and Payment</span>
              </div>
          </div>
      </div>
      
      <?php if ($activeBookingsCount > 1): ?>
        <div class="mt-3 text-center">
          <p>You have <?php echo $activeBookingsCount; ?> ongoing bookings. <a href="my-bookings.php">View all</a></p>
        </div>
      <?php endif; ?>
      
    <?php else: ?>
    
      <h3 class="popup-title">No Ongoing Services</h3>
      <div class="text-center p-4">
        <p>You don't have any active service bookings at the moment.</p>
        <?php 
          $redirectUrl = urlencode("Booking.php?user_id=" . (isset($_SESSION['Id']) ? $_SESSION['Id'] : ''));

          if (isset($_SESSION['Id']) && isset($_SESSION['email']) && $_SESSION['user_type'] === 'user'): ?>
              <a href="Booking.php?user_id=<?php echo $_SESSION['Id']; ?>" class="download-btn">
                  <i class="btn btn-primary"></i> <span>Book Now</span>
              </a>
          <?php else: ?>
              <a href="login.php?redirect=<?= $redirectUrl ?>" class="download-btn">
                  <i class="btn btn-primary"></i> <span>Book Now</span>
              </a>
        <?php endif; ?>
      </div>

    <?php endif; ?>
  </div>
</div>
    <!-- First, add this CSS to the <style> section in the head of your document -->
<style>
  .activity-popup {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
  
.activity-popup-content {
    background-color: #fff;
    width: 90%;
    max-width: 800px;
    height: auto;
    max-height: 80vh;
    overflow-y: auto;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    position: relative;
}
  
.close-popup {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 28px;
    cursor: pointer;
    color: #666;
}
  
.close-popup:hover {
    color: #000;
}
  
.popup-title {
    margin-top: 0;
    margin-bottom: 20px;
    font-weight: 600;
    color: #333;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.booking-details {
    margin-bottom: 20px;
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 12px;
    text-align: center;
    line-height: 18px;
}
  
.activity-text {
    font-size: 12px;
    margin-top: 5px;
}
  
.cart-icon {
    width: 24px;
    height: 24px;
    position: relative;
}
  
.ongoing-activity-icon {
    position: relative;
    cursor: pointer;
}

/* Progress Bar Styles */
.progress-container {
    margin: 20px 0;
}

.progress-bar-wrapper {
    height: 10px;
    background-color: #f0f0f0;
    border-radius: 5px;
    margin-bottom: 20px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background-color: #4CAF50;
    border-radius: 5px;
    transition: width 0.5s;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    position: relative;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: #e0e0e0;
    z-index: 1;
}

.step {
    width: 16.66%; /* 100% / 6 steps */
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: #f0f0f0;
    border: 2px solid #ccc;
    display: inline-block;
    position: relative;
    margin-bottom: 5px;
}

.step.completed .step-dot {
    background-color: #4CAF50;
    border-color: #4CAF50;
}

.step.active .step-dot {
    background-color: #fff;
    border-color: #4CAF50;
    animation: pulse 1.5s infinite;
}

.step.completed .step-dot::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 14px;
    font-weight: bold;
}

.step-label {
    font-size: 12px;
    color: #666;
    margin-top: 8px;
    display: block;
}

.step.active .step-label {
    font-weight: bold;
    color: #333;
}

.progress-bar-fill {
    height: 100%;
    background-color: #4CAF50;
    border-radius: 5px;
    transition: width 0.5s ease; /* Smooth transition */
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
    }
}

@media (max-width: 768px) {
    .step-label {
        font-size: 10px;
    }
    
    .step-dot {
        width: 25px;
        height: 25px;
    }
}

@media (max-width: 576px) {
    .progress-steps {
        display: block;
    }
    
    .progress-steps::before {
        display: none;
    }
    
    .step {
        width: 100%;
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .step-label {
        margin-top: 0;
        margin-left: 10px;
        font-size: 14px;
    }
}
</style>

<!-- Then, add this HTML code right before the closing body tag -->


<!-- Finally, add this JavaScript code just before the closing body tag -->
<script>
// Function to open popup
function openCartPopup() {
  // Force reload to get latest data
  window.location.href = window.location.href.split('#')[0] + '?refresh=' + new Date().getTime() + '#popup';
  
  setTimeout(function() {
    document.getElementById("activityPopup").style.display = "flex";
    document.body.style.overflow = "hidden";
  }, 300); // Give a bit more time to ensure page loads
}

// Function to close popup
function closePopup() {
  document.getElementById("activityPopup").style.display = "none";
  document.body.style.overflow = "auto"; 
}

// Close popup when clicking outside
window.addEventListener('click', function(event) {
  const popup = document.getElementById('activityPopup');
  if (event.target === popup) {
    closePopup();
  }
});

// Close popup when pressing Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closePopup();
  }
});

// Auto-open popup if hash is #popup
document.addEventListener('DOMContentLoaded', function() {
  if (window.location.hash === '#popup') {
    setTimeout(function() {
      document.getElementById("activityPopup").style.display = "flex";
      document.body.style.overflow = "hidden";
    }, 500);
  }
});
</script>

  </main>

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
          <!-- All the links in the footer should remain intact. -->
          <!-- You can delete the links only if you've purchased the pro version. -->
          <!-- Licensing information: https://bootstrapmade.com/license/ -->
          <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
          Designed by <a href="https://web-celestia.store">Web Celsetia Studio</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
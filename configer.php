<?php
// config.php - Store configuration variables
// This file should be outside the public web directory for security

// Stripe API Keys
define('STRIPE_SECRET_KEY', 'sk_test_51R1LydAs2j5lCWqocy3mrJaKoEJxYq1SBRz6QBKlQKoID1mhTBEQwneUvsKdnJRTywuLTPbf0jSDbNT3czSK9M0z00eVBhf9y7');
define('STRIPE_PUBLIC_KEY', 'pk_test_your_public_key_here');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Website Configuration
define('SITE_URL', 'https://your-site-url.com');
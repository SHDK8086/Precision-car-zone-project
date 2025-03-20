<?php
session_start();

if (!isset($_SESSION['Id'])) {
    $_SESSION['redirect_after_login'] = 'Booking.php' . (isset($_GET['user_id']) ? '?user_id=' . $_GET['user_id'] : '');
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Service - Precision Car Zone</title>
    <link href="assets/Logo.svg" rel="icon">
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* Booking Alert Styles */
        .booking-alert {
          position: fixed;
          top: 30px;
          left: 50%;
          transform: translateX(-50%);
          width: 90%;
          max-width: 500px;
          z-index: 1000;
          border-radius: 10px;
          overflow: hidden;
          box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
          opacity: 0;
          transition: all 0.3s ease;
        }
        
        .booking-alert.show {
          opacity: 1;
          animation: slideInDown 0.5s forwards, pulse 2s infinite alternate;
        }
        
        /* Success Alert */
        .booking-success {
          background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
          border-left: 5px solid #4caf50;
        }
        
        /* Error Alert */
        .booking-error {
          background: linear-gradient(135deg, #ffebee, #ffcdd2);
          border-left: 5px solid #f44336;
        }
        
        .alert-content {
          display: flex;
          padding: 20px;
        }
        
        .alert-icon {
          flex-shrink: 0;
          width: 50px;
          height: 50px;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          margin-right: 15px;
        }
        
        .success-icon {
          background-color: #4caf50;
          box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }
        
        .error-icon {
          background-color: #f44336;
          box-shadow: 0 4px 10px rgba(244, 67, 54, 0.3);
        }
        
        .alert-icon i {
          color: white;
          font-size: 24px;
        }
        
        .alert-text {
          flex-grow: 1;
        }
        
        .success-text {
          color: #2e7d32;
        }
        
        .error-text {
          color: #c62828;
        }
        
        .alert-title {
          font-size: 18px;
          font-weight: 600;
          margin: 0 0 5px 0;
        }
        
        .alert-message {
          font-size: 14px;
          margin: 0;
          opacity: 0.9;
        }
        
        .alert-close {
          position: absolute;
          top: 12px;
          right: 12px;
          background: transparent;
          border: none;
          cursor: pointer;
          color: inherit;
          opacity: 0.7;
          font-size: 18px;
        }
        
        .alert-close:hover {
          opacity: 1;
        }
        
        .alert-progress {
          height: 3px;
          width: 100%;
        }
        
        .success-progress {
          background-color: #4caf50;
          animation: progressShrink 5s linear forwards;
        }
        
        .error-progress {
          background-color: #f44336;
          animation: progressShrink 5s linear forwards;
        }
        
        /* Field Error Indicator */
        .input-error {
          border: 1px solid #f44336 !important;
          background-color: rgba(244, 67, 54, 0.05);
        }
        
        .error-label {
          color: #f44336;
          font-size: 12px;
          margin-top: 4px;
          display: none;
        }
        
        .input-error + .error-label {
          display: block;
        }
        
        /* Animations */
        @keyframes slideInDown {
          from {
            transform: translate(-50%, -20px);
            opacity: 0;
          }
          to {
            transform: translate(-50%, 0);
            opacity: 1;
          }
        }
        
        @keyframes pulse {
          0% {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
          }
          100% {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
          }
        }
        
        @keyframes progressShrink {
          from {
            width: 100%;
          }
          to {
            width: 0%;
          }
        }
        
        /* Responsive adjustments */
        @media (max-width: 576px) {
          .booking-alert {
            width: 95%;
            max-width: none;
            top: 20px;
          }
          
          .alert-icon {
            width: 40px;
            height: 40px;
          }
          
          .alert-icon i {
            font-size: 20px;
          }
          
          .alert-title {
            font-size: 16px;
          }
          
          .alert-message {
            font-size: 13px;
          }
        }
        
        /* Success confetti animation */
        .confetti-container {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          pointer-events: none;
          z-index: 999;
        }
        
        .confetti {
          position: absolute;
          width: 10px;
          height: 10px;
          background-color: #f44336;
          opacity: 0.7;
          animation: fall 5s linear forwards;
        }
        
        @keyframes fall {
          to {
            transform: translateY(100vh) rotate(720deg);
          }
        }
        
        /* Back to home button */
        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #3f51b5;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .back-to-home:hover {
            background-color: #303f9f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .back-to-home i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <a href="staffDashboard.php" class="back-to-home">
        <i class="fa fa-home"></i> Back to Home
    </a>
    
    <form id="bookingForm" class="form">
        <h2 class="text-center">Book Your Car Service</h2>
        <p class="subtitle text-center">Complete the form below to schedule your premium car service</p>

        <!-- Progress Bar  -->
        <div class="progressbar">
            <div class="progress" id="progress"></div>
            <div class="progress-step progress-step-active" data-title="Customer"><i class="fa fa-user"></i></div>
            <div class="progress-step" data-title="Vehicle"><i class="fa fa-car"></i></div>
            <div class="progress-step" data-title="Scheduling"><i class="fa fa-calendar"></i></div>
            <div class="progress-step" data-title="Confirm"><i class="fa fa-check"></i></div>
        </div>

        <!-- Customer Details -->
        <div class="form-step form-step-active">
            <h3 class="step-title">Personal Information</h3>
            <p class="step-description">Tell us who you are so we can provide personalized service</p>

            <div class="input-group">
    <label for="UserId"><i class="fa fa-id-card"></i> User ID (Optional)</label>
    <input type="text" name="UserId" id="UserId" placeholder="Enter User ID (if applicable)" />
</div>

            <div class="input-group">
                <label for="Name"><i class="fa fa-user"></i> Full Name</label>
                <input type="text" name="Name" id="Name" placeholder="Enter your full name" required />
            </div>
            <div class="input-group">
                <label for="Email"><i class="fa fa-envelope"></i> Email Address</label>
                <input type="email" name="Email" id="Email" placeholder="email@example.com" required />
            </div>
            <div class="input-group">
                <label for="Contact"><i class="fa fa-phone"></i> Contact Number</label>
                <input type="text" name="Contact" id="Contact" placeholder="+94 7X XXX XXXX" required />
            </div>
            <div class="input-group">
                <label for="Address"><i class="fa fa-map-marker"></i> Address</label>
                <input type="text" name="Address" id="Address" placeholder="Your current address" required />
            </div>
            <div class="btns-group">
                <a href="#" class="btn btn-next width-50 ml-auto">Next <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="form-step">
            <h3 class="step-title">Vehicle Information</h3>
            <p class="step-description">Provide details about your vehicle for accurate service estimation</p>
            
            <div class="input-group">
                <label for="VehicleNumber"><i class="fa fa-car"></i> Vehicle Number</label>
                <input type="text" name="VehicleNumber" id="VehicleNumber" placeholder="e.g., ABC-1234" required />
            </div>
            <div class="input-group">
                <label for="VehicleModel"><i class="fa fa-cogs"></i> Vehicle Model</label>
                <input type="text" name="VehicleModel" id="VehicleModel" placeholder="e.g., Toyota Corolla" required />
            </div>
            <div class="input-group">
                <label for="VehicleYear"><i class="fa fa-calendar"></i> Vehicle Year</label>
                <input type="number" name="VehicleYear" id="VehicleYear" placeholder="e.g., 2020" min="1980" max="<?php echo date('Y'); ?>" required />
            </div>
            <div class="input-group">
                <label for="ServiceType"><i class="fa fa-wrench"></i> Service Type</label>
                <select name="ServiceType" id="ServiceType" required>
                    <option value="" disabled selected>Select a service</option>
                    <option value="oil_change">Oil Change</option>
                    <option value="tire_rotation">Tire Rotation</option>
                    <option value="brake_inspection">Brake Inspection</option>
                    <option value="engine_diagnosis">Engine Diagnosis</option>
                    <option value="general_maintenance">General Maintenance</option>
                </select>
            </div>
            <div class="btns-group">
                <a href="#" class="btn btn-prev"><i class="fa fa-arrow-left"></i> Back</a>
                <a href="#" class="btn btn-next">Next <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="form-step">
            <h3 class="step-title">Schedule Your Service</h3>
            <p class="step-description">Select your preferred date and time for the service</p>
            
            <div class="input-group">
                <label for="BookingDate"><i class="fa fa-calendar"></i> Preferred Date</label>
                <input type="date" name="BookingDate" id="BookingDate" min="<?php echo date('Y-m-d'); ?>" required />
                <small>Please select a date at least one day in advance</small>
            </div>
            <div class="input-group">
                <label for="BookingTime"><i class="fa fa-clock-o"></i> Preferred Time</label>
                <select name="BookingTime" id="BookingTime" required>
                    <option value="" disabled selected>Select a time slot</option>
                    <option value="09:00 AM">09:00 AM</option>
                    <option value="10:00 AM">10:00 AM</option>
                    <option value="11:00 AM">11:00 AM</option>
                    <option value="12:00 PM">12:00 PM</option>
                    <option value="02:00 PM">02:00 PM</option>
                    <option value="03:00 PM">03:00 PM</option>
                    <option value="04:00 PM">04:00 PM</option>
                    <option value="05:00 PM">05:00 PM</option>
                </select>
            </div>
            <div class="input-group">
                <label for="AdditionalNotes"><i class="fa fa-sticky-note"></i> Additional Notes (Optional)</label>
                <textarea name="AdditionalNotes" id="AdditionalNotes" rows="3" placeholder="Any special requests or additional information about your vehicle"></textarea>
            </div>
            <div class="btns-group">
                <a href="#" class="btn btn-prev"><i class="fa fa-arrow-left"></i> Back</a>
                <a href="#" class="btn btn-next">Next <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="form-step">
            <h3 class="step-title">Confirm Your Booking</h3>
            <p class="step-description">Review your information and complete your booking</p>
            
            <div class="booking-summary">
                <h4><i class="fa fa-file-text-o"></i> Booking Summary</h4>
                <div id="summary-content">
                    <p>Please review all details before confirming.</p>
                </div>
            </div>
            
            <div class="terms">
                <input type="checkbox" id="termsCheckbox" required />
                <label for="termsCheckbox">I agree to the <a href="#" class="terms-link">terms and conditions</a> of Precision Car Zone</label>
            </div>
            <div class="btns-group">
                <a href="#" class="btn btn-prev"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="submit" id="submitBtn" class="btn"><i class="fa fa-check-circle"></i> Confirm Booking</button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const nextBtns = document.querySelectorAll(".btn-next");

        nextBtns.forEach((btn, index) => {
            if (index === 2) { 
                // Trigger the updateSummary function when the "Next" button is clicked on the third step
                btn.addEventListener("click", updateSummary);
            }
        });

        function updateSummary() {
          const summaryContent = document.getElementById('summary-content');

          // Collect form values
          const userId = document.getElementById('UserId').value.trim() || "0";  // Defaults to 0 if empty
          const name = document.getElementById('Name').value;
          const email = document.getElementById('Email').value;
          const contact = document.getElementById('Contact').value;
          const vehicleNumber = document.getElementById('VehicleNumber').value;
          const vehicleModel = document.getElementById('VehicleModel').value;
          const vehicleYear = document.getElementById('VehicleYear').value;
          const serviceType = document.getElementById('ServiceType');
          const serviceText = serviceType.options[serviceType.selectedIndex].text;
          const bookingDate = document.getElementById('BookingDate').value;
          const bookingTime = document.getElementById('BookingTime').value;
          const additionalNotes = document.getElementById('AdditionalNotes').value;

          // Validate required fields
          if (!name || !email || !contact || !vehicleNumber || !vehicleModel || !vehicleYear || !serviceType.value || !bookingDate || !bookingTime) {
              alert("Please fill out all required fields.");
              return; // Prevent proceeding
          }

          // Format the date
          const formattedDate = new Date(bookingDate).toLocaleDateString('en-US', {
              weekday: 'long',
              year: 'numeric',
              month: 'long',
              day: 'numeric'
          });

          // Update summary
          summaryContent.innerHTML = `
              <div class="summary-item">
                  <span class="summary-label">User ID:</span>
                  <span class="summary-value">${userId}</span>
              </div>
              <div class="summary-item">
                  <span class="summary-label">Customer:</span>
                  <span class="summary-value">${name}</span>
              </div>
              <div class="summary-item">
                  <span class="summary-label">Contact:</span>
                  <span class="summary-value">${email} | ${contact}</span>
              </div>
              <div class="summary-item">
                  <span class="summary-label">Vehicle:</span>
                  <span class="summary-value">${vehicleNumber} (${vehicleModel})</span>
              </div>
              <div class="summary-item">
                  <span class="summary-label">Service:</span>
                  <span class="summary-value">${serviceText}</span>
              </div>
              <div class="summary-item">
                  <span class="summary-label">Booking Schedule:</span>
                  <span class="summary-value">${formattedDate} at ${bookingTime}</span>
              </div>
              <div class="summary-item">
                  <span class="summary-label">Additional Notes:</span>
                  <span class="summary-value">${additionalNotes}</span>
              </div>
          `;
      }
    });

    </script>

    <script src="staffBookingscript.js"></script>
</body>
</html>
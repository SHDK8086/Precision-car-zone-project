<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking</title>
    <link href="assets/Logo.svg" rel="icon">
    <link rel="stylesheet" href="booking.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <!-- Form Begins -->
    <form id="bookingForm" class="form">
        <h2 class="text-center">Booking Form</h2>
        <!-- Credit Link -->
        <div class="text-center"><a href="https://www.instagram.com/kalana_kavinda__/" target="_blank">Created by Wheel Dick</a></div>

        <!-- Progress Bar  -->
        <div class="progressbar">
            <div class="progress" id="progress"></div>
            <div class="progress-step progress-step-active" data-title="Customer"><i class="fa fa-user"></i></div>
            <div class="progress-step" data-title="Vehicle"><i class="fa fa-car"></i></div>
            <div class="progress-step" data-title="Booking"><i class="fa fa-calendar"></i></div>
            <div class="progress-step" data-title="Confirm"><i class="fa fa-check"></i></div>
        </div>

        <!-- Customer Details -->
        <div class="form-step form-step-active">
            <!-- Hidden field for user ID -->
            <input type="hidden" name="user_id" id="user_id" value="<?php echo $_SESSION['Id']; ?>" />

            <div class="input-group">
                <label for="Name"><i class="fa fa-user"></i> Name</label>
                <input type="text" name="Name" id="Name" required />
            </div>
            <div class="input-group">
                <label for="Email"><i class="fa fa-envelope"></i> Email</label>
                <input type="email" name="Email" id="Email" required />
            </div>
            <div class="input-group">
                <label for="Contact"><i class="fa fa-phone"></i> Contact Number</label>
                <input type="text" name="Contact" id="Contact" required />
            </div>
            <div class="input-group">
                <label for="Address"><i class="fa fa-map-marker"></i> Address</label>
                <input type="text" name="Address" id="Address" required />
            </div>
            <div class="">
                <a href="#" class="btn btn-next width-50 ml-auto">Next <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="form-step">
            <div class="input-group">
                <label for="VehicleNumber"><i class="fa fa-car"></i> Vehicle Number</label>
                <input type="text" name="VehicleNumber" id="VehicleNumber" required />
            </div>
            <div class="input-group">
                <label for="VehicleModel"><i class="fa fa-cogs"></i> Vehicle Model</label>
                <input type="text" name="VehicleModel" id="VehicleModel" required />
            </div>
            <div class="input-group">
                <label for="VehicleYear"><i class="fa fa-calendar"></i> Vehicle Year</label>
                <input type="number" name="VehicleYear" id="VehicleYear" required />
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
            <div class="input-group">
                <label for="BookingDate"><i class="fa fa-calendar"></i> Booking Date</label>
                <input type="date" name="BookingDate" id="BookingDate" min="<?php echo date('Y-m-d'); ?>" required />
            </div>
            <div class="input-group">
                <label for="BookingTime"><i class="fa fa-clock-o"></i> Booking Time</label>
                <select name="BookingTime" id="BookingTime" required>
                    <option value="" disabled selected>Select a time</option>
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
            <div class="btns-group">
                <a href="#" class="btn btn-prev"><i class="fa fa-arrow-left"></i> Back</a>
                <a href="#" class="btn btn-next">Next <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Booking Confirm -->
        <div class="form-step">
            <div class="terms">
                <input type="checkbox" id="termsCheckbox" required />
                <label for="termsCheckbox">I agree to the terms and conditions</label>
            </div>
            <div class="btns-group">
                <a href="#" class="btn btn-prev"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="submit" id="submitBtn" class="btn">Confirm Booking</button>
            </div>
        </div>
    </form>

    <!-- Social Credit Links -->
    <div class="social_media_div">
        <ul class="icons_list">
            <li>
                <a href="#" target="_blank"><i class="fa fa-github"></i></a>
            </li>
            <li>
                <a href="https://www.instagram.com/kalana_kavinda__/" target="_blank"><i class="fa fa-instagram"></i></a>
            </li>
        </ul>
    </div>

    <script src="Bookingscript.js"></script>
</body>
</html>
document.addEventListener('DOMContentLoaded', function() {
    const prevBtns = document.querySelectorAll(".btn-prev");
    const nextBtns = document.querySelectorAll(".btn-next");
    const progress = document.querySelector(".progress");
    const formSteps = document.querySelectorAll(".form-step");
    const progressSteps = document.querySelectorAll(".progress-step");

    let formStepsNum = 0;

    nextBtns.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();

            if (validateFormStep(formStepsNum)) {
                formStepsNum++;
                updateFormSteps();
                updateProgressbar();
            }
        });
    });

    prevBtns.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            formStepsNum--;
            updateFormSteps();
            updateProgressbar();
        });
    });

    function updateFormSteps() {
        formSteps.forEach((formStep) => {
            formStep.classList.remove("form-step-active");
        });
        formSteps[formStepsNum].classList.add("form-step-active");
    }

    function updateProgressbar() {
        progressSteps.forEach((progressStep, index) => {
            if (index < formStepsNum + 1) {
                progressStep.classList.add("progress-step-active");
            } else {
                progressStep.classList.remove("progress-step-active");
            }
        });
        const progressActive = document.querySelectorAll(".progress-step-active");
        progress.style.width = ((progressActive.length - 1) / (progressSteps.length - 1)) * 100 + "%";
    }

    function validateFormStep(stepNumber) {
        clearInputErrors();

        let isValid = true;
        const currentStep = formSteps[stepNumber];
        const inputs = currentStep.querySelectorAll('input[required], select[required]');

        inputs.forEach(input => {
            if (!input.value.trim()) {
                showInputError(input, "This field is required");
                isValid = false;
            } else if (input.type === 'email' && !isValidEmail(input.value)) {
                showInputError(input, "Please enter a valid email address");
                isValid = false;
            }
        });

        if (!isValid) {
            showAlert('error', 'Missing Information', 'Please fill in all the required fields to continue.');
        }

        return isValid;
    }

    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    function showInputError(input, message) {
        input.classList.add('input-error');
        
        let errorLabel = input.nextElementSibling;
        if (!errorLabel || !errorLabel.classList.contains('error-label')) {
            errorLabel = document.createElement('div');
            errorLabel.className = 'error-label';
            input.parentNode.insertBefore(errorLabel, input.nextSibling);
        }

        errorLabel.textContent = message;
    }

    function clearInputErrors() {
        const errorInputs = document.querySelectorAll('.input-error');
        const errorLabels = document.querySelectorAll('.error-label');
        
        errorInputs.forEach(input => {
            input.classList.remove('input-error');
        });
        
        errorLabels.forEach(label => {
            label.style.display = 'none';
        });
    }

    const bookingForm = document.getElementById("bookingForm");
    bookingForm.addEventListener("submit", function(e) {
        e.preventDefault();

        const termsCheckbox = document.getElementById("termsCheckbox");
        if (!termsCheckbox.checked) {
            showAlert('error', 'Terms Required', 'Please agree to the terms and conditions to complete your booking.');
            return;
        }

        if (!validateAllSteps()) {
            return;
        }

        const formData = new FormData(bookingForm);

        const submitBtn = document.getElementById("submitBtn");
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;

        fetch('process_staffbooking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.innerHTML = 'Confirm Booking';
            submitBtn.disabled = false;

            if (data.status === 'success') {
                showSuccessWithConfetti(data.booking_id);

                setTimeout(() => {
                    window.location.href = `confirmation.php?id=${data.booking_id}`;
                }, 5000);
            } else {
                showAlert('error', 'Booking Failed', data.message || 'An error occurred while processing your booking.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.innerHTML = 'Confirm Booking';
            submitBtn.disabled = false;
            showAlert('error', 'Submission Error', 'An error occurred while processing your booking. Please try again.');
        });
    });

    function validateAllSteps() {
        let isValid = true;

        const currentStepNum = formStepsNum;

        for (let i = 0; i < formSteps.length; i++) {
            formStepsNum = i;
            updateFormSteps();

            if (!validateFormStep(i)) {
                isValid = false;
                break;
            }
        }
        formStepsNum = currentStepNum;
        updateFormSteps();

        return isValid;
    }

    function showAlert(type, title, message) {
        const existingAlert = document.querySelector('.booking-alert');
        if (existingAlert) {
            document.body.removeChild(existingAlert);
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `booking-alert booking-${type}`;

        alertDiv.innerHTML = `
            <div class="alert-content">
                <div class="alert-icon ${type}-icon">
                    <i class="fa ${type === 'success' ? 'fa-check' : 'fa-exclamation-triangle'}"></i>
                </div>
                <div class="alert-text ${type}-text">
                    <h3 class="alert-title">${title}</h3>
                    <p class="alert-message">${message}</p>
                </div>
                <button class="alert-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="alert-progress ${type}-progress"></div>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.classList.add('show');
        }, 10);

        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        document.body.removeChild(alertDiv);
                    }
                }, 300);
            }
        }, 5000);
    }

    function showSuccessWithConfetti(bookingId) {
        showAlert(
            'success', 
            'Booking Confirmed!', 
            `Your booking has been successfully confirmed. Your booking ID is #${bookingId}. Redirecting to confirmation page...`
        );

        const confettiContainer = document.createElement('div');
        confettiContainer.className = 'confetti-container';
        document.body.appendChild(confettiContainer);

        const colors = ['#f44336', '#2196f3', '#ffeb3b', '#4caf50', '#9c27b0'];
        for (let i = 0; i < 100; i++) {
            createConfetti(confettiContainer, colors);
        }

        setTimeout(() => {
            if (confettiContainer.parentNode) {
                document.body.removeChild(confettiContainer);
            }
        }, 5000);
    }

    function createConfetti(container, colors) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';

        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.top = -10 + 'px';

        const size = Math.random() * 10 + 5;
        confetti.style.width = size + 'px';
        confetti.style.height = size + 'px';

        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];

        confetti.style.transform = `rotate(${Math.random() * 360}deg)`;

        const shapes = ['circle', 'square', 'triangle'];
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        if (shape === 'circle') {
            confetti.style.borderRadius = '50%';
        } else if (shape === 'triangle') {
            confetti.style.width = '0';
            confetti.style.height = '0';
            confetti.style.backgroundColor = 'transparent';
            confetti.style.borderLeft = `${size/2}px solid transparent`;
            confetti.style.borderRight = `${size/2}px solid transparent`;
            confetti.style.borderBottom = `${size}px solid ${colors[Math.floor(Math.random() * colors.length)]}`;
        }

        confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';

        container.appendChild(confetti);
    }

    const userId = document.getElementById('user_id');
    if (userId && userId.value && userId.value !== '0') {
        fetch(`get_user_data.php?id=${userId.value}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('Name').value = data.name || '';
                    document.getElementById('Email').value = data.email || '';
                    document.getElementById('Contact').value = data.contact || '';
                    document.getElementById('Address').value = data.address || '';
                }
            })
            .catch(error => console.error('Error fetching user data:', error));
    }
});

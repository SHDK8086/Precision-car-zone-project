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
            formStepsNum++;
            updateFormSteps();
            updateProgressbar();
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

    // Form submission handling
    const bookingForm = document.getElementById("bookingForm");
    bookingForm.addEventListener("submit", function(e) {
        e.preventDefault();

        // Check if terms checkbox is checked
        const termsCheckbox = document.getElementById("termsCheckbox");
        if (!termsCheckbox.checked) {
            alert("Please agree to the terms and conditions.");
            return;
        }

        const formData = new FormData(bookingForm);

        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = `confirmation.php?id=${data.booking_id}`;
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your booking.');
        });
    });
});
const urlParams = new URLSearchParams(window.location.search);
const success = urlParams.get('success');

if (success === 'staff_added') {

  const email = urlParams.get('email');
  const password = urlParams.get('password');
  const role = urlParams.get('role'); 

  const popup = document.getElementById('popup');
  const popupMessage = document.getElementById('popupMessage');
  popupMessage.innerHTML = `Staff Added Successfully!<br><br>Email: ${email}<br>Password: ${password}<br>Role: ${role}`;
  popup.style.display = 'block';

  const formSection = document.getElementById('formSection');
  const popupBackground = document.getElementById('popupBackground');
  formSection.classList.add('blurred');
  popupBackground.style.display = 'block';
}

function redirectToDashboard() {
  window.location.href = "staffadminDashboard.php";
}

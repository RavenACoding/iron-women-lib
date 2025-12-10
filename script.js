// Form Management
function showForm(formId) {
    document.querySelectorAll('.form-box').forEach(form => form.classList.remove('active'));
    document.getElementById(formId).classList.add('active');
}
// Modal Management um fix this.. pls
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const matchDiv = document.getElementById('password-match');
    const registerBtn = document.getElementById('register-btn');
    if (confirm === '') { matchDiv.classList.add('hidden'); registerBtn.disabled = false; return; }
    matchDiv.classList.remove('hidden');
    if (password === confirm) { matchDiv.className = 'password-match match'; matchDiv.textContent = 'Passwords match'; registerBtn.disabled = false; }
    else { matchDiv.className = 'password-match no-match'; matchDiv.textContent = 'Passwords do not match'; registerBtn.disabled = true; }
}

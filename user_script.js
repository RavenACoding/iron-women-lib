// Navigation Management
function showSection(section) {
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.getElementById(section + '-section').classList.add('active');
    document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
    if (event && event.target) event.target.classList.add('active');
}
//DARK MODE EVIL CODE
function toggleDarkMode() { document.body.classList.toggle('dark-mode'); localStorage.setItem('darkMode', document.body.classList.contains('dark-mode')); }
function changeFontSize(size) { document.documentElement.style.fontSize = size + 'px'; localStorage.setItem('fontSize', size); }
if (localStorage.getItem('darkMode') === 'true') { document.body.classList.add('dark-mode'); const toggle = document.getElementById('darkModeToggle'); if (toggle) toggle.checked = true; }
if (localStorage.getItem('fontSize')) { const size = localStorage.getItem('fontSize'); changeFontSize(size); const select = document.getElementById('fontSizeSelect'); if (select) select.value = size; }

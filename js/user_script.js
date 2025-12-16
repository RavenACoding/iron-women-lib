// Navigation Management
function showSection(section) {
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.getElementById(section + '-section').classList.add('active');
    document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
    if (event && event.target) event.target.classList.add('active');
}
//DARK MODE EVIL CODE
function toggleDarkMode() { document.body.classList.toggle('dark-mode'); 
localStorage.setItem('darkMode', document.body.classList.contains('dark-mode')); }
if (localStorage.getItem('darkMode') === 'true') { document.body.classList.add('dark-mode'); const toggle = document.getElementById('darkModeToggle'); if (toggle) toggle.checked = true; }
if (localStorage.getItem('fontSize')) { const size = localStorage.getItem('fontSize'); changeFontSize(size); const select = document.getElementById('fontSizeSelect'); if (select) select.value = size; }

// ===== FIXED: Font Size now affects Nav Bar =====
function changeFontSize(size) {
    const baseSize = 16;
    const scale = size / baseSize;
    
    // Set root font size
    document.documentElement.style.fontSize = size + 'px';
    
    // FIX: Scale nav items proportionally
    document.querySelectorAll('.nav-item').forEach(item => {
        item.style.fontSize = (14 * scale) + 'px';
        item.style.padding = (15 * scale) + 'px ' + (20 * scale) + 'px';
    });
    
    // Scale logo text
    document.querySelectorAll('.logo h2').forEach(logo => {
        logo.style.fontSize = (16 * scale) + 'px';
    });
    
    // Scale logo padding
    document.querySelectorAll('.logo').forEach(logo => {
        logo.style.padding = (20 * scale) + 'px';
    });
    
    localStorage.setItem('fontSize', size);
}

// Load saved settings on page load
if (localStorage.getItem('darkMode') === 'true') { 
    document.body.classList.add('dark-mode'); 
    const toggle = document.getElementById('darkModeToggle'); 
    if (toggle) toggle.checked = true; 
}
if (localStorage.getItem('fontSize')) { 
    const size = localStorage.getItem('fontSize'); 
    changeFontSize(size); 
    const select = document.getElementById('fontSizeSelect'); 
    if (select) select.value = size; 
}
// Debug: Log when script loads
console.log('user_script.js loaded successfully');

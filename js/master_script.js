//  FIXED: Navigation and Section Handling 
// Wait for DOM to load, then attach event listeners properly
document.addEventListener('DOMContentLoaded', function() {
    // Attach click handlers to nav items
    document.querySelectorAll('.nav-item:not(.logout)').forEach(function(item) {
        item.style.cursor = 'pointer'; 
        // Ensure pointer cursor
    });
});

function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(function(s) {
        s.classList.remove('active');
    });
    
    // Show the target section
    var targetSection = document.getElementById(section + '-section');
    if (targetSection) {
        targetSection.classList.add('active');
    } else {
        console.error('Section not found: ' + section + '-section');
    }
    
    // Update nav item active state
    document.querySelectorAll('.nav-item').forEach(function(a) {
        a.classList.remove('active');
    });
    
    // Find and activate the clicked nav item
    if (event && event.target) {
        event.target.classList.add('active');
    }
    
    // Prevent default link behavior
    if (event) {
        event.preventDefault();
    }
    
    return false; // Extra safety to prevent navigation
}

// Modal Management
function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
document.querySelectorAll('.modal').forEach(modal => { modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal.id); }); });
// User Edit and Profile Management
function openEditUser(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editFirstName').value = user.first_name || '';
    document.getElementById('editMiddleName').value = user.middle_name || '';
    document.getElementById('editLastName').value = user.last_name || '';
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editCompany').value = user.company || '';
    document.getElementById('editDept').value = user.department || '';
    document.getElementById('editHire').value = user.date_of_hire || '';
    const passwordField = document.getElementById('editPassword');
    if (passwordField) passwordField.value = '';
    const assignGroup = document.getElementById('assignAdminGroup');
    if (assignGroup) { if (user.role === 'user') { assignGroup.style.display = 'block'; document.getElementById('editAssignedAdmin').value = user.assigned_admin || ''; } else { assignGroup.style.display = 'none'; } }
    openModal('editUserModal');
}

function saveUserEdit(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('user_id', document.getElementById('editUserId').value);
    formData.append('first_name', document.getElementById('editFirstName').value);
    formData.append('middle_name', document.getElementById('editMiddleName').value);
    formData.append('last_name', document.getElementById('editLastName').value);
    formData.append('email', document.getElementById('editEmail').value);
    formData.append('phone', document.getElementById('editPhone').value);
    formData.append('company', document.getElementById('editCompany').value);
    formData.append('department', document.getElementById('editDept').value);
    formData.append('date_of_hire', document.getElementById('editHire').value);
    const assignedAdmin = document.getElementById('editAssignedAdmin');
    if (assignedAdmin) formData.append('assigned_admin', assignedAdmin.value);
    const newPassword = document.getElementById('editPassword');
    if (newPassword && newPassword.value.trim() !== '') formData.append('new_password', newPassword.value.trim());
    fetch('update_profile.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Error: ' + data.message); });
}
// Toggle User Status aka Activate/Deactivate
function toggleStatus(userId, currentStatus) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', currentStatus == 1 ? 0 : 1);
    fetch('toggle.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Error'); });
}

function openEditProfile() { openModal('profileModal'); }
function saveProfile(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('user_id', document.getElementById('profileUserId').value);
    formData.append('first_name', document.getElementById('profileFirstName').value);
    formData.append('middle_name', document.getElementById('profileMiddleName').value);
    formData.append('last_name', document.getElementById('profileLastName').value);
    formData.append('email', document.getElementById('profileEmail').value);
    formData.append('phone', document.getElementById('profilePhone').value);
    fetch('update_profile.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Error'); });
}
// Table Filtering copied pasted from instagram
function filterTable(tableId, search) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    search = search.toLowerCase();
    rows.forEach(row => { row.style.display = row.textContent.toLowerCase().includes(search) ? '' : 'none'; });
}
// attempt filter admin table — move the input/table markup into your HTML file, e.g.
// <input type="text" id="adminSearch" placeholder="Search admins..." />
// <table id="adminsTable"> ... </table>
// Initialization of the adminSearch element is done later in the file to avoid duplicate declarations.

//Attempt to calculate expiry date based on issue date and cert type...
function calculateExpiry() {
    const certType = document.getElementById('certType');
    const issueDate = document.getElementById('issueDate');
    const expiryDate = document.getElementById('expiryDate');
    if (certType.value && issueDate.value) {
        const months = parseInt(certType.options[certType.selectedIndex].dataset.months);
        const issue = new Date(issueDate.value);
        issue.setMonth(issue.getMonth() + months);
        expiryDate.value = issue.toISOString().split('T')[0];
    }
}

// Incident and Certificate Management
function submitIncident(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('user_id', document.getElementById('incidentUserId').value);
    formData.append('incident_date', document.getElementById('incidentDate').value);
    formData.append('incident_time', document.getElementById('incidentTime').value);
    formData.append('witness_name', document.getElementById('incidentWitness').value);
    formData.append('description', document.getElementById('incidentDesc').value);
    formData.append('notes', document.getElementById('incidentNotes').value);
    fetch('add_incident.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Error'); });
}

// Certificate Management
function submitCertificate(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('certificate_type_id', document.getElementById('certType').value);
    formData.append('issue_date', document.getElementById('issueDate').value);
    formData.append('expiry_date', document.getElementById('expiryDate').value);
    const checkboxes = document.querySelectorAll('input[name="certUsers[]"]:checked');
    checkboxes.forEach(cb => formData.append('user_ids[]', cb.value));
    fetch('add_certificate.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Error'); });
}

// Remove Certificate
function removeCertificate(certId) {
    if (!confirm('Remove?')) return;
    const formData = new FormData();
    formData.append('certificate_id', certId);
    fetch('remove_certificate.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
}
// Settings Management
function toggleDarkMode() { document.body.classList.toggle('dark-mode'); localStorage.setItem('darkMode', document.body.classList.contains('dark-mode')); }
function changeFontSize(size) { document.documentElement.style.fontSize = size + 'px'; localStorage.setItem('fontSize', size); }
if (localStorage.getItem('darkMode') === 'true') { document.body.classList.add('dark-mode'); const toggle = document.getElementById('darkModeToggle'); if (toggle) toggle.checked = true; }
if (localStorage.getItem('fontSize')) { const size = localStorage.getItem('fontSize'); changeFontSize(size); const select = document.getElementById('fontSizeSelect'); if (select) select.value = size; }

// attempt filter admin table
const adminSearch = document.getElementById('adminSearch');
if (adminSearch) {
    adminSearch.addEventListener('keyup', (e) => filterTable('adminsTable', e.target.value));
}

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
        logo.style.fontSize = (18 * scale) + 'px';
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

// Filter incidents by name - FIXED
function filterIncidents(search) {
    search = search.toLowerCase();
    const items = document.querySelectorAll('.incident-item');
    items.forEach(function (item) {
        item.style.display = item.textContent.toLowerCase().includes(search) ? '' : 'none';
    });
}

// ===== NEW: View User Certificates Function =====
function viewUserCertificates(userId, userName) {
    // Open modal and show loading
    document.getElementById('viewCertsTitle').textContent = userName + "'s Certificates";
    document.getElementById('viewCertsContent').innerHTML = '<p style="text-align: center;">Loading...</p>';
    openModal('viewCertsModal');
    
    // Fetch user certificates via AJAX
    fetch('user_certlook.php?user_id=' + userId)
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('user_certlook');
            if (data.certificates && data.certificates.length > 0) {
                container.innerHTML = data.certificates.map(cert => {
                    const today = new Date();
                    const expiry = new Date(cert.expiry_date);
                    const diffDays = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
                    const isExpired = diffDays < 0;
                    const isExpiring = !isExpired && diffDays <= 30;
                    const statusClass = isExpired ? 'expired' : (isExpiring ? 'expiring' : 'valid');
                    const statusText = isExpired ? 'Expired' : (isExpiring ? 'Expiring Soon' : 'Valid');
                    const statusColor = isExpired ? '#ef4444' : (isExpiring ? '#f59e0b' : '#10b981');
                    
                    return `
                        <div class="cert-card ${statusClass}">
                            <strong>${cert.cert_name}</strong>
                            <p>Issued: ${cert.issue_date}</p>
                            <p>Expires: ${cert.expiry_date}</p>
                            <span class="cert-status" style="color: ${statusColor};">${statusText}</span>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<p style="text-align: center; padding: 20px; color: var(--text-secondary);">No certificates found for this user.</p>';
            }
        })
        .catch(err => {
            document.getElementById('viewCertsContent').innerHTML = '<p style="color: #ef4444; text-align: center;">Error loading certificates</p>';
        });
}

// Debug: Log when script loads
console.log('master_script.js loaded successfully');

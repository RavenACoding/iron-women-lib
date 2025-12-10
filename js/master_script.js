// Navigation and Section Handling
function showSection(section) {
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.getElementById(section + '-section').classList.add('active');
    document.querySelectorAll('.nav-item').forEach(a => a.classList.remove('active'));
    if (event && event.target) event.target.classList.add('active');
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
// then the JS below will attach the live filtering behavior safely:
const adminSearch = document.getElementById('adminSearch');
if (adminSearch) {
    adminSearch.addEventListener('keyup', (e) => filterTable('adminsTable', e.target.value));
}

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

    //attempt to fliter incident reports by name
function filterIncidents(search) {
    const container = document.getElementById('incidentsContainer');
    const items = container.querySelectorAll('.incident-item');
    search = search.toLowerCase();
    items.forEach(item => { 
        item.style.display = item.textContent.toLowerCase().includes(search) ? '' : 'none'; 
    });
}
    fetch('add_incident.php', { method: 'POST', body: formData }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert('Error'); });
}

//attempt to fliter incident reports by name
function filterIncidents(search) {
    const container = document.getElementById('incidentsContainer');
    const items = container.querySelectorAll('.incident-item');
    search = search.toLowerCase();
    items.forEach(item => { 
        item.style.display = item.textContent.toLowerCase().includes(search) ? '' : 'none'; 
    });
}
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

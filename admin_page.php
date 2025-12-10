<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin' || $_SESSION['admin_type'] !== 'regular') { header("Location: index.php"); exit(); }
if (isset($_GET['lang'])) { $_SESSION['language'] = $_GET['lang']; header("Location: admin_page.php"); exit(); }
$lang = $_SESSION['language'] ?? 'en';
// Simple translation function,ignore the 258 references
function t($en, $es) { global $lang; return $lang === 'es' ? $es : $en; }

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$currentAdmin = $stmt->get_result()->fetch_assoc();
$adminId = $currentAdmin['id'];
// Fetch data for dashboard
$assignedUsers = $conn->query("SELECT * FROM users WHERE role = 'user' AND assigned_admin = $adminId ORDER BY is_active DESC, name ASC");
$certificateTypes = getCertificateTypes($conn);
$departments = getDepartments();
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE assigned_admin = $adminId")->fetch_assoc()['c'];
$myIncidents = $conn->query("SELECT ir.*, u.name as user_name FROM incident_reports ir JOIN users u ON ir.user_id = u.id WHERE ir.reported_by = $adminId OR u.assigned_admin = $adminId ORDER BY ir.incident_date DESC");
$myCerts = $conn->query("SELECT c.*, ct.name as cert_name, u.name as user_name FROM certificates c JOIN certificate_types ct ON c.certificate_type_id = ct.id JOIN users u ON c.user_id = u.id WHERE u.assigned_admin = $adminId ORDER BY c.expiry_date ASC");
?>

<!-- Big Focus --->
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/admin_style.css">
</head>
<body>
<!-- NAVIGATION --->
    <nav>
        <div class="logo"><h2>Admin Panel</h2></div>
        <a class="nav-item active" onclick="showSection('dashboard')"><?= t('Dashboard', 'Panel') ?></a>
        <a class="nav-item" onclick="showSection('users')"><?= t('My Users', 'Mis Usuarios') ?></a>
        <a class="nav-item" onclick="showSection('incidents')"><?= t('Incident Reports', 'Incidentes') ?></a>
        <a class="nav-item" onclick="showSection('certificates')"><?= t('Certificates', 'Certificados') ?></a>
        <a class="nav-item" onclick="showSection('profile')"><?= t('Profile', 'Perfil') ?></a>
        <a class="nav-item" onclick="showSection('settings')"><?= t('Settings', 'Configuracion') ?></a>
        <a href="logout.php" class="nav-item logout"><?= t('Logout', 'Salir') ?></a>
    </nav>
    <!-- MAIN CONTENT W CARDS--->
    <main class="main">
        <div class="main-top"><h1><?= t('Welcome', 'Bienvenido') ?>, <span class="highlight"><?= htmlspecialchars($currentAdmin['first_name']) ?></span></h1></div>
        <section id="dashboard-section" class="content-section active">
            <div class="info-box"><?= t('You can only manage your assigned users.', 'Solo puede administrar sus usuarios asignados.') ?></div>
            <div class="stats-grid">
                <div class="stat-card"><h3><?= $totalUsers ?></h3><p><?= t('Assigned Users', 'Usuarios') ?></p></div>
                <div class="stat-card"><h3><?= $myIncidents->num_rows ?></h3><p><?= t('Incidents', 'Incidentes') ?></p></div>
                <div class="stat-card"><h3><?= $myCerts->num_rows ?></h3><p><?= t('Certificates', 'Certificados') ?></p></div>
            </div>
        </section>

        <section id="users-section" class="content-section">
            <!-- MY USERS TABLE --->
            <div class="section-header"><h2><?= t('My Assigned Users', 'Mis Usuarios') ?></h2><input type="text" id="userSearch" placeholder="<?= t('Search...', 'Buscar...') ?>" onkeyup="filterTable('usersTable', this.value)"></div>
            <div class="card">
                <table id="usersTable">
                    <thead><tr><th><?= t('Name', 'Nombre') ?></th><th><?= t('Email', 'Correo') ?></th><th><?= t('Dept', 'Depto') ?></th><th><?= t('Status', 'Estado') ?></th><th><?= t('Actions', 'Acciones') ?></th></tr></thead>
                    <tbody>
                        <?php while ($user = $assignedUsers->fetch_assoc()): ?>
                        <tr class="<?= $user['is_active'] == 0 ? 'inactive' : '' ?>">
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['department'] ?? 'N/A') ?></td>
                            <td><span class="status-badge status-<?= $user['is_active'] == 1 ? 'active' : 'inactive' ?>"><?= $user['is_active'] == 1 ? t('Active', 'Activo') : t('Inactive', 'Inactivo') ?></span></td>
                            <td><button class="btn" onclick='openEditUser(<?= json_encode($user) ?>)'><?= t('Edit', 'Editar') ?></button></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <!-- INCIDENT REPORTS SECTION --->
        <section id="incidents-section" class="content-section">
            <div class="section-header"><h2><?= t('Incident Reports', 'Incidentes') ?></h2><button class="btn" onclick="openModal('incidentModal')"><?= t('New Report', 'Nuevo') ?></button></div>
            <div class="card">
                <?php $myIncidents->data_seek(0); while ($inc = $myIncidents->fetch_assoc()): ?>
                <div class="incident-item"><strong><?= htmlspecialchars($inc['description']) ?></strong><p><?= t('User', 'Usuario') ?>: <?= htmlspecialchars($inc['user_name']) ?> | <?= t('Date', 'Fecha') ?>: <?= $inc['incident_date'] ?></p></div>
                
              
    <!-- table content -->
</table>
                <?php endwhile; ?>
            </div>
        </section>
        <!-- CERTIFICATES SECTION --->
        <section id="certificates-section" class="content-section">
            <div class="section-header"><h2><?= t('Certificates', 'Certificados') ?></h2><button class="btn" onclick="openModal('certModal')"><?= t('Upload', 'Subir') ?></button></div>
            <div class="card"><div class="cert-grid">
                <?php $myCerts->data_seek(0); while ($cert = $myCerts->fetch_assoc()): $expiry = new DateTime($cert['expiry_date']); $today = new DateTime(); $diff = $today->diff($expiry)->days; $isExpired = $today > $expiry; $isExpiring = !$isExpired && $diff <= 30; $statusClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'valid'); ?>
                <div class="cert-card <?= $statusClass ?>">
                    <strong><?= htmlspecialchars($cert['cert_name']) ?></strong>
                    <p><?= htmlspecialchars($cert['user_name']) ?></p>
                    <p><?= t('Expires', 'Vence') ?>: <?= $cert['expiry_date'] ?></p>
                    <span class="cert-status"><?= $isExpired ? t('Expired', 'Vencido') : ($isExpiring ? t('Expiring', 'Por vencer') : t('Valid', 'Valido')) ?></span>
                    <button class="btn btn-danger btn-sm" onclick="removeCertificate(<?= $cert['id'] ?>)"><?= t('Remove', 'Quitar') ?></button>
                </div>
                <?php endwhile; ?>
            </div></div>
        </section>
        <!-- PROFILE SECTION --->
        <section id="profile-section" class="content-section">
            <div class="section-header"><h2><?= t('My Profile', 'Mi Perfil') ?></h2><button class="btn" onclick="openEditProfile()"><?= t('Edit', 'Editar') ?></button></div>
            <div class="card"><div class="profile-grid">
                <div class="profile-item"><strong><?= t('First Name', 'Nombre') ?></strong><?= htmlspecialchars($currentAdmin['first_name']) ?></div>
                <div class="profile-item"><strong><?= t('Middle Name', 'Segundo') ?></strong><?= htmlspecialchars($currentAdmin['middle_name'] ?? '') ?></div>
                <div class="profile-item"><strong><?= t('Last Name', 'Apellido') ?></strong><?= htmlspecialchars($currentAdmin['last_name']) ?></div>
                <div class="profile-item"><strong><?= t('Email', 'Correo') ?></strong><?= htmlspecialchars($currentAdmin['email']) ?></div>
                <div class="profile-item"><strong><?= t('Phone', 'Telefono') ?></strong><?= htmlspecialchars($currentAdmin['phone'] ?? 'N/A') ?></div>
                <div class="profile-item"><strong><?= t('Department', 'Depto') ?></strong><?= htmlspecialchars($currentAdmin['department'] ?? 'N/A') ?></div>
            </div></div>
        </section>
        <!-- SETTINGS SECTION --->
        <section id="settings-section" class="content-section">
            <div class="section-header"><h2><?= t('Settings', 'Configuracion') ?></h2></div>
            <div class="settings-group"><h3><?= t('Language', 'Idioma') ?></h3><div class="setting-item"><a href="?lang=en" class="btn <?= $lang === 'en' ? '' : 'btn-secondary' ?>">English</a><a href="?lang=es" class="btn <?= $lang === 'es' ? '' : 'btn-secondary' ?>">Espanol</a></div></div>
            <div class="settings-group"><h3><?= t('Appearance', 'Apariencia') ?></h3>
                <div class="setting-item"><label><?= t('Font Size', 'Fuente') ?></label><select onchange="changeFontSize(this.value)" id="fontSizeSelect"><option value="14"><?= t('Small', 'Pequeno') ?></option><option value="16" selected><?= t('Medium', 'Mediano') ?></option><option value="18"><?= t('Large', 'Grande') ?></option><option value="20"><?= t('Extra Large', 'Extra') ?></option></select></div>
                <div class="setting-item"><label><?= t('Dark Mode', 'Modo Oscuro') ?></label><label class="toggle-switch"><input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()"><span class="toggle-slider"></span></label></div>
            </div>
        </section>
    </main>
    <!-- MODALS --->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h2><?= t('Edit User', 'Editar') ?></h2><span class="close-modal" onclick="closeModal('editUserModal')">X</span></div>
            <form id="editUserForm" onsubmit="saveUserEdit(event)">
                <input type="hidden" id="editUserId">
                <div class="form-row"><div class="form-group"><label><?= t('First', 'Nombre') ?></label><input type="text" id="editFirstName" required></div><div class="form-group"><label><?= t('Middle', 'Segundo') ?></label><input type="text" id="editMiddleName"></div><div class="form-group"><label><?= t('Last', 'Apellido') ?></label><input type="text" id="editLastName" required></div></div>
                <div class="form-group"><label><?= t('Email', 'Correo') ?></label><input type="email" id="editEmail" required></div>
                <div class="form-group"><label><?= t('Phone', 'Telefono') ?></label><input type="text" id="editPhone"></div>
                <div class="form-row"><div class="form-group"><label><?= t('Company', 'Empresa') ?></label><input type="text" id="editCompany"></div><div class="form-group"><label><?= t('Department', 'Depto') ?></label><select id="editDept"><option value="">--</option><?php foreach ($departments as $d): ?><option value="<?= $d ?>"><?= $d ?></option><?php endforeach; ?></select></div></div>
                <div class="form-group"><label><?= t('Hire Date', 'Contrato') ?></label><input type="date" id="editHire"></div>
                <div class="form-group password-section"><label><?= t('Change Password (leave blank to keep current)', 'Cambiar Contrasena (dejar vacio para mantener)') ?></label><input type="password" id="editPassword" placeholder="<?= t('Enter new password...', 'Nueva contrasena...') ?>"></div>
                <div class="form-actions"><button type="submit" class="btn"><?= t('Save', 'Guardar') ?></button><button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')"><?= t('Cancel', 'Cancelar') ?></button></div>
            </form>
        </div>
    </div>
    <!-- INCIDENT REPORT MODAL --->
    <div id="incidentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h2><?= t('New Incident', 'Nuevo Incidente') ?></h2><span class="close-modal" onclick="closeModal('incidentModal')">X</span></div>
            <form id="incidentForm" onsubmit="submitIncident(event)">
                <div class="form-group"><label><?= t('Select User', 'Usuario') ?> *</label><select id="incidentUserId" required><option value="">--</option><?php $assignedUsers->data_seek(0); while ($u = $assignedUsers->fetch_assoc()): ?><option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option><?php endwhile; ?></select></div>
                <div class="form-row"><div class="form-group"><label><?= t('Date', 'Fecha') ?> *</label><input type="date" id="incidentDate" required></div><div class="form-group"><label><?= t('Time', 'Hora') ?></label><input type="time" id="incidentTime"></div></div>
                <div class="form-group"><label><?= t('Witness', 'Testigo') ?></label><input type="text" id="incidentWitness"></div>
                <div class="form-group"><label><?= t('Description', 'Descripcion') ?> *</label><textarea id="incidentDesc" rows="3" required></textarea></div>
                <div class="form-group"><label><?= t('Notes', 'Notas') ?></label><input type="text" id="incidentNotes"></div>
                <button type="submit" class="btn"><?= t('Submit', 'Enviar') ?></button>
            </form>
        </div>
    </div>
    <!-- CERTIFICATE UPLOAD MODAL --->
    <div id="certModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h2><?= t('Upload Certificate', 'Subir Certificado') ?></h2><span class="close-modal" onclick="closeModal('certModal')">X</span></div>
            <form id="certForm" onsubmit="submitCertificate(event)">
                <div class="form-group"><label><?= t('Certificate Type', 'Tipo') ?> *</label><select id="certType" onchange="calculateExpiry()" required><option value="">--</option><?php foreach ($certificateTypes as $ct): ?><option value="<?= $ct['id'] ?>" data-months="<?= $ct['valid_months'] ?>"><?= htmlspecialchars($ct['name']) ?> (<?= $ct['valid_months'] ?>m)</option><?php endforeach; ?></select></div>
                <div class="form-row"><div class="form-group"><label><?= t('Issue Date', 'Emision') ?> *</label><input type="date" id="issueDate" onchange="calculateExpiry()" required></div><div class="form-group"><label><?= t('Expiry (Auto)', 'Vencimiento') ?></label><input type="date" id="expiryDate" readonly></div></div>
                <div class="form-group"><label><?= t('Select Users', 'Usuarios') ?></label><div id="certUserList" class="user-checkbox-list"><?php $assignedUsers->data_seek(0); while ($u = $assignedUsers->fetch_assoc()): ?><label class="checkbox-item"><input type="checkbox" name="certUsers[]" value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></label><?php endwhile; ?></div></div>
                <button type="submit" class="btn"><?= t('Upload', 'Subir') ?></button>
            </form>
        </div>
    </div>
    <!-- PROFILE EDIT MODAL --->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h2><?= t('Edit Profile', 'Editar Perfil') ?></h2><span class="close-modal" onclick="closeModal('profileModal')">X</span></div>
            <form id="profileForm" onsubmit="saveProfile(event)">
                <input type="hidden" id="profileUserId" value="<?= $currentAdmin['id'] ?>">
                <div class="form-row"><div class="form-group"><label><?= t('First', 'Nombre') ?></label><input type="text" id="profileFirstName" value="<?= htmlspecialchars($currentAdmin['first_name']) ?>" required></div><div class="form-group"><label><?= t('Middle', 'Segundo') ?></label><input type="text" id="profileMiddleName" value="<?= htmlspecialchars($currentAdmin['middle_name'] ?? '') ?>"></div><div class="form-group"><label><?= t('Last', 'Apellido') ?></label><input type="text" id="profileLastName" value="<?= htmlspecialchars($currentAdmin['last_name']) ?>" required></div></div>
                <div class="form-group"><label><?= t('Email', 'Correo') ?></label><input type="email" id="profileEmail" value="<?= htmlspecialchars($currentAdmin['email']) ?>" required></div>
                <div class="form-group"><label><?= t('Phone', 'Telefono') ?></label><input type="text" id="profilePhone" value="<?= htmlspecialchars($currentAdmin['phone'] ?? '') ?>"></div>
                <button type="submit" class="btn"><?= t('Save', 'Guardar') ?></button>
            </form>
        </div>
    </div>
    <!-- SCRIPTS MAKE IT ECHO IF IT DOEN'T WORK--->
    <script>const currentUserId = <?= $currentAdmin['id'] ?>;</script>
    <script src="js/admin_script.js"></script>
</body>
</html>

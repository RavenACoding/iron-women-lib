<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin' || $_SESSION['admin_type'] !== 'master') { header("Location: index.php"); exit(); }
if (isset($_GET['lang'])) { $_SESSION['language'] = $_GET['lang']; header("Location: master_page.php"); exit(); }
$lang = $_SESSION['language'] ?? 'en';
// SIMPLE TRANSLATION FUNCTION  IGNORE 258 REFERENCES
function t($en, $es) { global $lang; return $lang === 'es' ? $es : $en; }

// FETCH DATA FOR DISPLAY
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
$allUsers = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY is_active DESC, name ASC");
$allAdmins = $conn->query("SELECT * FROM users WHERE role = 'admin' ORDER BY admin_type DESC, name ASC");
$regularAdmins = $conn->query("SELECT * FROM users WHERE role = 'admin' AND admin_type = 'regular' ORDER BY name ASC");
$certificateTypes = getCertificateTypes($conn);
$departments = getDepartments();
$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'user'")->fetch_assoc()['c'];
$totalAdmins = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'admin'")->fetch_assoc()['c'];
$totalIncidents = $conn->query("SELECT COUNT(*) as c FROM incident_reports")->fetch_assoc()['c'];
$totalCerts = $conn->query("SELECT COUNT(*) as c FROM certificates")->fetch_assoc()['c'];
$incidents = $conn->query("SELECT ir.*, u.name as user_name FROM incident_reports ir JOIN users u ON ir.user_id = u.id ORDER BY ir.incident_date DESC");
$certificates = $conn->query("SELECT c.*, ct.name as cert_name, u.name as user_name FROM certificates c JOIN certificate_types ct ON c.certificate_type_id = ct.id JOIN users u ON c.user_id = u.id ORDER BY c.expiry_date ASC");
$subDepts = $conn->query("SELECT DISTINCT sub_department FROM users WHERE sub_department IS NOT NULL AND sub_department != '' ORDER BY sub_department");
$existingSubDepts = [];


while ($row = $subDepts->fetch_assoc()) $existingSubDepts[] = $row['sub_department'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Master Admin Panel</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="CSS/master_style.css">
    </head>

    <body>
        <!--MASTER NAVIGATION --->
        <nav>
            <div class="logo">
                <h2>Admin Panel</h2>
            </div>
            <a class="nav-item active" onclick="showSection('dashboard')"><?= t('Dashboard', 'Panel') ?></a>
            <a class="nav-item " onclick="showSection('users')"><?= t('User Management', 'Usuarios') ?></a>
            <a class="nav-item " onclick="showSection('admins')"><?= t('Admin Management', 'Administradores') ?></a>
            <a class="nav-item " onclick="showSection('incidents')"><?= t('Incident Reports', 'Incidentes') ?></a>
            <a class="nav-item " onclick="showSection('certificates')"><?= t('Certificates', 'Certificados') ?></a>
            <a class="nav-item " onclick="showSection('profile')"><?= t('Profile', 'Perfil') ?></a>
            <a class="nav-item" onclick="showSection('settings')"><?= t('Settings', 'Configuracion') ?></a>
            <a href="logout.php" class="nav-item logout"><?= t('Logout', 'Salir') ?></a>
        </nav>

        <!-- MAIN CONTENT --->
        <main class="main">
            <div class="main-top">
                <h1>
                    <?= t('Welcome', 'Bienvenido') ?>, <span class="highlight">
                    <?= htmlspecialchars($currentUser['first_name']) ?></span>
                </h1>
            </div>

            <section id="dashboard-section" class="content-section active">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?= $totalUsers ?></h3>
                        <p><?= t('Total Users', 'Usuarios') ?></p>
                    </div>

                    <div class="stat-card">
                        <h3><?= $totalAdmins ?></h3>
                        <p><?= t('Total Admins', 'Admins') ?></p>
                    </div>

                    <div class="stat-card">
                        <h3><?= $totalIncidents ?></h3>
                        <p><?= t('Incidents', 'Incidentes') ?></p>
                    </div>

                    <div class="stat-card">
                        <h3><?= $totalCerts ?></h3>
                        <p><?= t('Certificates', 'Certificados') ?></p>
                    </div>
                </div>
                <h3><?= t('Departments', 'Departamentos') ?></h3>

                <!-- DEPARTMENT CARDS --->
                <div class="dept-grid">
                    <?php 
                    foreach ($departments as $dept): 
                        $deptAdminsQuery = $conn->query("SELECT * FROM users WHERE department = '$dept' AND role = 'admin' AND admin_type = 'regular' ORDER BY name ASC");
                        $deptAdminCount = $deptAdminsQuery->num_rows;
                        if ($deptAdminCount > 1):
                            $letter = 'A';
                            while ($admin = $deptAdminsQuery->fetch_assoc()):
                                $assignedCount = $conn->query("SELECT COUNT(*) as c FROM users WHERE assigned_admin = {$admin['id']}")->fetch_assoc()['c']; ?>

                                <div class="dept-card" onclick="location.href='?view_admin=<?= $admin['id'] ?>'">
                                    <strong>
                                        <?= $dept ?>
                                        <?= $letter ?>
                                    </strong>
                                    <p> <?= $admin['name'] ?></p>

                                    <p>
                                        <?= $assignedCount ?> 
                                        <?= t('users', 'usuarios') ?>
                                    </p>
                                </div>
                        
                                <?php 
                                $letter++; 
                                endwhile; 
                            else: 
                                $deptUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE department = '$dept' AND role = 'user'")->fetch_assoc()['c']; ?>

                                <div class="dept-card">
                                    <strong>
                                        <?= $dept ?>
                                    </strong>

                                    <p>
                                        <?= $deptUsers ?> 
                                        <?= t('users', 'usuarios') ?>, <?= $deptAdminCount ?> admins
                                    </p>
                                </div>
                    <?php endif; endforeach;?>
                </div>
            </section>
            
            <!-- USER MANAGEMENT SECTION --->
            <section id="users-section" class="content-section">

                <div class="section-header">
                    <h2>
                        <?= t('User Management', 'Usuarios') ?>
                    </h2>
                    <input type="text" id="userSearch" placeholder="
                    <?= t('Search...', 'Buscar...') ?>" onkeyup="filterTable('usersTable', this.value)">
                </div>
            
                <div class="card">
                    <table id="usersTable">
                        <thead>
                            <tr>
                                <th><?= t('Name', 'Nombre') ?></th>   
                                <th><?= t('Email', 'Correo') ?></th>
                                <th><?= t('Dept', 'Depto') ?></th>
                                <th><?= t('Sub-Dept', 'Sub-Depto') ?></th>
                                <th><?= t('Status', 'Estado') ?></th>
                                <th><?= t('Actions', 'Acciones') ?></th>                                   
                            </tr>
                        </thead>

                        <tbody>
                            <!-- TO EDIT USER DATA/SEEK (peekaboo)--->
                            <?php $allUsers->data_seek(0); 
                            while ($user = $allUsers->fetch_assoc()): ?>

                            <tr class="<?= $user['is_active'] == 0 ? 'inactive' : '' ?>">
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['department'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($user['sub_department'] ?? '-') ?></td>

                                <td>
                                    <span class="status-badge status-<?= $user['is_active'] == 1 ? 'active' : 'inactive' ?>">
                                    <?= $user['is_active'] == 1 ? t('Active', 'Activo') : t('Inactive', 'Inactivo') ?></span>
                                </td>

                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm" onclick='openEditUser(<?= json_encode($user) ?>)'>
                                            <?= t('Edit', 'Editar') ?>
                                        </button>

                                        <button class="btn btn-sm btn-success" onclick="viewUserCertificates(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['name'])) ?>')">
                                            <?= t('View Certs', 'Ver Certs') ?>
                                        </button>

                                        <button class="btn btn-sm 
                                            <?= $user['is_active'] == 1 ? 'btn-danger' : 'btn-success' ?>" onclick="toggleStatus(<?= $user['id'] ?>, 
                                            <?= $user['is_active'] ?>)"><?= $user['is_active'] == 1 ? t('Deactivate', 'Desactivar') : t('Activate', 'Activar') ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- ADMIN MANAGEMENT SECTION --->
            <section id="admins-section" class="content-section">
                <div class="admin-header"><h2><?= t('Admin Management', 'Admins') ?></h2></div>
                    <table>
                        <thead><tr><th><?= t('Name', 'Nombre') ?></th><th><?= t('Email', 'Correo') ?></th><th><?= t('Dept', 'Depto') ?></th><th>
                            <?= t('Sub-Dept', 'Sub-Depto') ?></th><th>
                            <?= t('Type', 'Tipo') ?></th><th>
                            <?= t('Status', 'Estado') ?></th><th>
                            <?= t('Actions', 'Acciones') ?></th></tr></thead>

                            <tbody>
                                <?php $allAdmins->data_seek(0); while ($admin = $allAdmins->fetch_assoc()): ?>   
                                <tr>
                                    <td><?= htmlspecialchars($admin['name']) ?></td>
                                    <td><?= htmlspecialchars($admin['email']) ?></td>
                                    <td><?= htmlspecialchars($admin['department'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($admin['sub_department'] ?? '-') ?></td>
                                    
                                    <td><span class="status-badge admin-badge"><?= ucfirst($admin['admin_type']) ?></span></td>

                                    <td>
                                        <span class="status-badge status-<?= $admin['is_active'] == 1 ? 'active' : 'inactive' ?>">
                                        <?= $admin['is_active'] == 1 ? t('Active', 'Activo') : t('Inactive', 'Inactivo') ?></span>
                                    </td>

                                    <td>
                                        <div class="action-buttons">

                                            <button class="btn btn-sm" onclick='openEditUser(<?= json_encode($admin) ?>)'>
                                                <?= t('Edit', 'Editar') ?>
                                            </button>
                                            
                                            <button class="btn btn-sm <?= $admin['is_active'] == 1 ? 'btn-danger' : 'btn-success' ?>" onclick="toggleStatus(<?= $admin['id'] ?>, 
                                                <?= $admin['is_active'] ?>)"><?= $admin['is_active'] == 1 ? t('Deactivate', 'Desactivar') : t('Activate', 'Activar') ?>
                                            </button>
                                        </div> 
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                    </table>
                </div>
            </section>

            <!-- INCIDENT REPORTS SECTION --->
            <section id="incidents-section" class="content-section">
                <div class="section-header">
                    <h2>
                        <?= t('Incident Reports', 'Incidentes') ?>
                        <input type="text" id="incidentSearch" placeholder="Search incidents..." onkeyup="filterIncidents(this.value)">
                        
                    </h2>
                            <div id="incidentsContainer">
                            <button class="btn" onclick="openModal('incidentModal')">
                                <?= t('New Report', 'Nuevo') ?>
                            </button>
                        </div> 
                        
                    <div class="card">
                        <?php $incidents->data_seek(0);
                        while ($inc = $incidents->fetch_assoc()): ?>

                        <div class="incident-item">
                            <strong><?= htmlspecialchars($inc['description']) ?></strong>
                            <p>
                                <?= t('User', 'Usuario') ?> : 
                                <?= htmlspecialchars($inc['user_name']) ?> | 
                                <?= t('Date', 'Fecha') ?>:
                                <?= $inc['incident_date'] ?>
                            </p>

                            <?php if ($inc['notes']): ?>
                                <p class="notes">
                                    <?= t('Notes', 'Notas') ?>: 
                                    <?= htmlspecialchars($inc['notes']) ?>
                                </p>
                                <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </section>

            <!-- CERTIFICATES SECTION --->
            <section id="certificates-section" class="content-section">

                <div class="section-header">
                    <h2><?= t('Certificates', 'Certificados') ?></h2>
                    <button class="btn" onclick="openModal('certModal')"><?= t('Upload', 'Subir') ?></button>
                </div>

                <div class="card">

                    <div class="cert-grid">

                        <?php $certificates->data_seek(0);
                        while ($cert = $certificates->fetch_assoc()): 
                            $expiry = new DateTime($cert['expiry_date']); 

                            $today = new DateTime(); 
                            $diff = $today -> diff($expiry) -> days; 
                            $isExpired = $today > $expiry; 
                            $isExpiring = !$isExpired && $diff <= 30; 
                            $statusClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'valid'); ?>
                        
                        <div class="cert-card <?= $statusClass ?>">
                            <strong><?= htmlspecialchars($cert['cert_name']) ?></strong>
                            <p><?= htmlspecialchars($cert['user_name']) ?></p>
                            <p><?= t('Expires', 'Vence') ?>: <?= $cert['expiry_date'] ?></p>

                            <span class="cert-status">
                                <?= $isExpired ? t('Expired', 'Vencido') : ($isExpiring ? t('Expiring', 'Por vencer') : t('Valid', 'Valido')) ?>
                            </span>

                            <button class="btn btn-danger btn-sm" onclick="removeCertificate(<?= $cert['id'] ?>)"><?= t('Remove', 'Quitar') ?></button>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>

                <!-- PROFILE SECTION --->
                <section id="profile-section" class="content-section">
                    <div class="section-header">
                        <h2><?= t('My Profile', 'Mi Perfil') ?></h2>

                        <button class="btn" onclick="openEditProfile()">
                            <?= t('Edit', 'Editar') ?>
                        </button>
                    </div>

                    <div class="card">
                        <div class="profile-grid">
                            <div class="profile-item">
                                <strong><?= t('First Name', 'Nombre') ?></strong>
                                <span><?= htmlspecialchars($currentUser['first_name']) ?></span>
                            </div>

                            <div class="profile-item">
                                <strong><?= t('Middle Name', 'Segundo') ?></strong>
                                <span><?= htmlspecialchars($currentUser['middle_name'] ?? '') ?></span>
                            </div>

                            <div class="profile-item">
                                <strong><?= t('Last Name', 'Apellido') ?></strong>
                                <span><?= htmlspecialchars($currentUser['last_name']) ?></span>
                            </div>

                            <div class="profile-item">
                                <strong><?= t('Email', 'Correo') ?></strong>
                                <span><?= htmlspecialchars($currentUser['email']) ?></span>
                            </div>

                            <div class="profile-item">
                                <strong><?= t('Phone', 'Telefono') ?></strong>
                                <span><?= htmlspecialchars($currentUser['phone'] ?? 'N/A') ?></span>
                            </div>

                            <div class="profile-item">
                                <strong><?= t('Department', 'Depto') ?></strong>
                                <span><?= htmlspecialchars($currentUser['department'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- SETTINGS SECTION --->
                <section id="settings-section" class="content-section">
                    <div class="section-header">
                        <h2><?= t('Settings', 'Configuracion') ?></h2>
                    </div>

                    <div class="settings-group">
                        <h3><?= t('Language', 'Idioma') ?></h3>

                        <div class="setting-item">
                            <a href="?lang=en" class="btn <?= $lang === 'en' ? '' : 'btn-secondary' ?>">English</a>
                            <a href="?lang=es" class="btn <?= $lang === 'es' ? '' : 'btn-secondary' ?>">Espanol</a>
                        </div>
                    </div>

                    <div class="settings-group">
                        <h3><?= t('Appearance', 'Apariencia') ?></h3>

                        <div class="setting-item">
                            <label><?= t('Font Size', 'Fuente') ?></label>

                            <select onchange="changeFontSize(this.value)" id="fontSizeSelect">
                                <option value="14"><?= t('Small', 'Pequeno') ?></option>
                                <option value="16" selected><?= t('Medium', 'Mediano') ?></option>
                                <option value="18"><?= t('Large', 'Grande') ?></option>
                                <option value="20"><?= t('Extra Large', 'Extra') ?></option>
                            </select>
                        </div>

                        <div class="setting-item">
                            <label><?= t('Dark Mode', 'Modo Oscuro') ?></label>

                            <label class="toggle-switch">
                                <input type="checkbox" id="darkModeToggle" onchange="toggleDarkMode()">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </section>
            </main>

            <!-- MODALS --->
            <div id="editUserModal" class="modal">

                <div class="modal-content">

                    <div class="modal-header">
                        <h2><?= t('Edit User', 'Editar') ?></h2>
                        <span class="close-modal" onclick="closeModal('editUserModal')"></span>
                    </div>

                    <form id="editUserForm" onsubmit="saveUserEdit(event)">
                        <input type="hidden" id="editUserId">
                        <input type="hidden" id="editUserRole">

                        <div class="form-row">

                            <div class="form-group">
                                <label><?= t('First', 'Nombre') ?></label>
                                <input type="text" id="editFirstName" required>

                            </div><div class="form-group">
                                <label><?= t('Middle', 'Segundo') ?></label>
                                <input type="text" id="editMiddleName">
                            </div>

                            <div class="form-group">
                                <label><?= t('Last', 'Apellido') ?></label>
                                <input type="text" id="editLastName" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?= t('Email', 'Correo') ?></label>
                            <input type="email" id="editEmail" required>
                        </div>

                        <div class="form-group">
                            <label><?= t('Phone', 'Telefono') ?></label>
                            <input type="text" id="editPhone">
                        </div>

                        <div class="form-row">

                            <div class="form-group">
                                <label><?= t('Company', 'Empresa') ?></label>
                                <input type="text" id="editCompany">
                            </div>
                        
                            <div class="form-group">
                                <label><?= t('Department', 'Depto') ?></label>
                                <select id="editDept"><option value="">
                                    </option>
                                        <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d ?>"><?= $d ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><?= t('Sub-Department', 'Sub-Depto') ?></label>
                                <select id="editSubDept" onchange="toggleNewSubDept()">
                                    <option value=""> <?= t('None', 'Ninguno') ?> </option>
                                    <?php foreach ($existingSubDepts as $sd): ?>
                                        <option value="<?= htmlspecialchars($sd) ?>">
                                            <?= htmlspecialchars($sd) ?>
                                        </option>
                                        <?php endforeach; ?>

                                    <option value="__new__"> <?= t('Create New', 'Crear Nuevo') ?></option>
                                </select>
                            </div>
                            <!-- NEW SUB-DEPT INPUT --->
                            <div class="form-group" id="newSubDeptGroup" style="display:none;">
                                <label><?= t('New Sub-Dept Name', 'Nombre Sub-Depto') ?></label>
                                <input type="text" id="newSubDeptName" placeholder="<?= t('e.g. Operations A', 'ej. Operaciones A') ?>">
                            </div>
                        </div>

                        <!-- ADDITIONAL FIELDS FOR ADMINS --->
                        <div class="form-group">
                            <label><?= t('Hire Date', 'Contrato') ?></label>
                            <input type="date" id="editHire">
                        </div>

                        <div class="form-group" id="assignAdminGroup">
                            <label><?= t('Assign Admin', 'Asignar') ?></label>
                            <select id="editAssignedAdmin">
                                <option value=""><?= t('Unassigned', 'Sin asignar') ?></option>
                                <?php $regularAdmins->data_seek(0); 

                                while ($ra = $regularAdmins->fetch_assoc()): ?>
                                <option value="<?= $ra['id'] ?>">
                                    <?= htmlspecialchars($ra['name']) ?>
                                    <?= $ra['sub_department'] ? ' (' . htmlspecialchars($ra['sub_department']) . ')' : '' ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group password-section">
                            <label><?= t('Change Password (leave blank to keep current)', 'Cambiar Contrasena (dejar vacio para mantener)') ?></label>

                            <input type="password" id="editPassword" placeholder="
                            <?= t('Enter new password...', 'Nueva contrasena...') ?>">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn">
                                <?= t('Save', 'Guardar') ?>
                            </button>

                            <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">
                                <?= t('Cancel', 'Cancelar') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- INCIDENT REPORTS SECTION --->
            <div id="incidentModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2><?= t('New Incident', 'Nuevo Incidente') ?></h2>
                        <span class="close-modal" onclick="closeModal('incidentModal')"></span>
                    </div>

                    <form id="incidentForm" onsubmit="submitIncident(event)">

                        <div class="form-group">
                            <label><?= t('Select User', 'Usuario') ?> </label>
                            <select id="incidentUserId" required>
                                <option value=""></option>
                                <?php $allUsers->data_seek(0); 

                                while ($u = $allUsers->fetch_assoc()): ?>
                                <option value="
                                    <?= $u['id'] ?>">
                                    <?= htmlspecialchars($u['name']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-row">

                            <div class="form-group">
                                <label><?= t('Date', 'Fecha') ?></label>
                                <input type="date" id="incidentDate" required>
                            </div>

                            <div class="form-group">
                                <label><?= t('Time', 'Hora') ?></label>
                                <input type="time" id="incidentTime">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?= t('Witness', 'Testigo') ?></label>
                            <input type="text" id="incidentWitness">
                        </div>

                        <div class="form-group">
                            <label><?= t('Description', 'Descripcion') ?> *</label>
                            <textarea id="incidentDesc" rows="3" required></textarea>
                        </div>

                        <div class="form-group">
                            <label><?= t('Notes', 'Notas') ?></label>
                            <input type="text" id="incidentNotes">
                        </div>

                        <button type="submit" class="btn">
                            <?= t('Submit', 'Enviar') ?>
                        </button>
                    </form>
                </div>
            </div>
            <!-- CERTIFICATES SECTION --->
            <div id="certModal" class="modal">

                <div class="modal-content">

                    <div class="modal-header">
                        <h2><?= t('Upload Certificate', 'Subir Certificado') ?></h2>
                        <span class="close-modal" onclick="closeModal('certModal')"></span>
                    </div>

                    <form id="certForm" onsubmit="submitCertificate(event)">

                        <div class="form-group">
                            <label><?= t('Certificate Type', 'Tipo') ?> *</label>
                            <select id="certType" onchange="calculateExpiry()" required>
                                <option value=""></option>
                                <?php foreach ($certificateTypes as $ct): ?>
                                    <option value="
                                        <?= $ct['id'] ?>" data-months="
                                        <?= $ct['valid_months'] ?>">
                                        <?= htmlspecialchars($ct['name']) ?> 
                                        <?= $ct['valid_months'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label><?= t('Issue Date', 'Emision') ?> *</label>
                                <input type="date" id="issueDate" onchange="calculateExpiry()" required>
                            </div>

                            <div class="form-group">
                                <label><?= t('Expiry (Auto)', 'Vencimiento') ?></label>
                                <input type="date" id="expiryDate" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><?= t('Select Users', 'Usuarios') ?></label>
                            <div id="certUserList" class="user-checkbox-list">
                                <?php $allUsers->data_seek(0); 

                                while ($u = $allUsers->fetch_assoc()): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="certUsers[]" value="
                                    <?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?>
                                </label>
                                <?php endwhile; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn"><?= t('Upload', 'Subir') ?></button>

                    </form>
                </div>
            </div>
            <!-- PROFILE EDIT MODAL --->
            <div id="profileModal" class="modal">

                <div class="modal-content">

                    <div class="modal-header">
                        <h2><?= t('Edit Profile', 'Editar Perfil') ?></h2>
                        <span class="close-modal" onclick="closeModal('profileModal')"></span>
                    </div>

                    <form id="profileForm" onsubmit="saveProfile(event)">
                        <input type="hidden" id="profileUserId" value="<?= $currentUser['id'] ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label><?= t('First', 'Nombre') ?></label>
                                <input type="text" id="profileFirstName" value="
                                <?= htmlspecialchars($currentUser['first_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label><?= t('Middle', 'Segundo') ?></label>
                                <input type="text" id="profileMiddleName" value="
                                <?= htmlspecialchars($currentUser['middle_name'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label><?= t('Last', 'Apellido') ?></label>
                                <input type="text" id="profileLastName" value="
                                <?= htmlspecialchars($currentUser['last_name']) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?= t('Email', 'Correo') ?></label>
                            <input type="email" id="profileEmail" value="
                            <?= htmlspecialchars($currentUser['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label><?= t('Phone', 'Telefono') ?></label>
                            <input type="text" id="profilePhone" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
                        </div>

                        <button type="submit" class="btn"><?= t('Save', 'Guardar') ?></button>
                    </form>
                </div>
            </div>
        <!-- VIEW USER CERTIFICATES MODAL (NEW) --->
            <div id="viewCertsModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="viewCertsTitle"><?= t('User Certificates', 'Certificados del Usuario') ?></h2>
                        <span class="close-modal" onclick="closeModal('viewCertsModal')"></span>
                    </div>

                    <div id="viewCertsContent" class="cert-grid">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>

        <!-- SCRIPTS MAKE IT ECHO IF IT DOEN'T WORK--->
        <script>const currentUserId = <?= $currentUser['id'] ?>;</script>
        <script src="js/master_script.js"></script>
        
    </body>
</html>


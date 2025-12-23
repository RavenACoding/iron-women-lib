<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user')
{
    header("Location: index.php"); 
    exit(); 
}

if (isset($_GET['lang']))
{ 
    $_SESSION['language'] = $_GET['lang']; 
    header("Location: user_page.php"); 
    exit(); 
}
$lang = $_SESSION['language'] ?? 'en';

function t($en, $es)
{ 
    global $lang; 
    return $lang === 'es' ? $es : $en; 
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

if ($currentUser['is_active'] == 0)
{ 
    session_destroy(); 
    header("Location: index.php"); 
    exit(); 
}

$userId = $currentUser['id'];
$certificates = $conn->query("SELECT c.*, ct.name as cert_name FROM certificates c JOIN certificate_types ct ON c.certificate_type_id = ct.id WHERE c.user_id = $userId ORDER BY c.expiry_date ASC");
$incidents = $conn->query("SELECT * FROM incident_reports WHERE user_id = $userId ORDER BY incident_date DESC");
$expiringSoon = $conn->query("SELECT COUNT(*) as c FROM certificates WHERE user_id = $userId AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/user_style.css">
</head>
<body>
    <!-- NAVIGATION --->
    <nav>
        <div class="logo"><h2>Iron Women</h2></div>
        <a class="nav-item active" onclick="showSection('dashboard')"><?= t('Dashboard', 'Panel') ?></a>
        <a class="nav-item" onclick="showSection('profile')"><?= t('Profile', 'Perfil') ?></a>
        <a class="nav-item" onclick="showSection('certificates')"><?= t('Certificates', 'Certificados') ?></a>
        <a class="nav-item" onclick="showSection('incidents')"><?= t('Incidents', 'Incidentes') ?></a>
        <a class="nav-item" onclick="showSection('settings')"><?= t('Settings', 'Configuracion') ?></a>
        <a href="logout.php" class="nav-item logout"><?= t('Logout', 'Salir') ?></a>
    </nav>
    <!-- MAIN CONTENT --->
    <main class="main">
        <div class="main-top">
            <h1>
                <?= t('Welcome back', 'Bienvenido') ?>,
                 <span class="highlight"><?= htmlspecialchars($currentUser['first_name']) ?></span>
                </h1>
            </div>

        <section id="dashboard-section" class="content-section active">
            <?php if ($expiringSoon > 0): ?>
                <div class="alert-box">
                    <h3>
                        <?= $expiringSoon ?> 
                        <?= t('Certificate(s) Expiring Soon', 'Certificado(s) Por Vencer') ?>
                    </h3>
                    <p><?= t('Contact your admin.', 'Contacte a su administrador.') ?></p>
                </div>
                <?php endif; ?>

            <div class="card">
                <h3><?= t('Quick Overview', 'Resumen') ?></h3>
                <p><?= t('Certificates', 'Certificados') ?>: <?= $certificates->num_rows ?></p>
                <p><?= t('Incidents', 'Incidentes') ?>: <?= $incidents->num_rows ?></p>
                <p><?= t('Status', 'Estado') ?>: <?= $currentUser['is_active'] == 1 ? t('Active', 'Activo') : t('Inactive', 'Inactivo') ?></p>
            </div>
        </section>

        <section id="profile-section" class="content-section">
            <div class="section-header"><h2><?= t('My Profile', 'Mi Perfil') ?></h2></div>

            <div class="card">
                <div class="read-only-notice">
                    <?= t('Read-only - Contact your admin', 'Solo lectura - Contacte a su admin') ?>
                </div>

                <div class="profile-grid">

                    <div class="profile-item">
                        <strong><?= t('First Name', 'Nombre') ?></strong>
                        <?= htmlspecialchars($currentUser['first_name']) ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Middle Name', 'Segundo') ?></strong>
                        <?= htmlspecialchars($currentUser['middle_name'] ?? '') ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Last Name', 'Apellido') ?></strong>
                        <?= htmlspecialchars($currentUser['last_name']) ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Email', 'Correo') ?></strong>
                        <?= htmlspecialchars($currentUser['email']) ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Phone', 'Telefono') ?></strong>
                        <?= htmlspecialchars($currentUser['phone'] ?? 'N/A') ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Company', 'Empresa') ?></strong>
                        <?= htmlspecialchars($currentUser['company'] ?? 'N/A') ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Department', 'Depto') ?></strong>
                        <?= htmlspecialchars($currentUser['department'] ?? 'N/A') ?>
                    </div>

                    <div class="profile-item">
                        <strong><?= t('Hire Date', 'Contrato') ?></strong>
                        <?= htmlspecialchars($currentUser['date_of_hire'] ?? 'N/A') ?>
                    </div>
                </div>
            </div>
        </section>
        <!--CERTIFICATES SECTION --->
        <section id="certificates-section" class="content-section">

            <div class="section-header">
                <h2><?= t('My Certificates', 'Mis Certificados') ?></h2>
            </div>

            <div class="cert-grid">
                <?php while ($cert = $certificates->fetch_assoc()): $expiryDate = new DateTime($cert['expiry_date']); 
                $today = new DateTime(); 
                $diff = $today->diff($expiryDate); 
                $daysUntilExpiry = (int)$diff->format('%r%a'); 
                $isExpired = $daysUntilExpiry < 0; 
                $isExpiring = !$isExpired && $daysUntilExpiry <= 30; 
                $cardClass = $isExpired ? 'expired' : ($isExpiring ? 'expiring' : ''); ?>

                <div class="cert-card <?= $cardClass ?>">
                    <h3><?= htmlspecialchars($cert['cert_name']) ?></h3>
                    <p>
                        <strong><?= t('Issued', 'Emitido') ?>:</strong> 
                        <?= date('M j, Y', strtotime($cert['issue_date'])) ?>
                    </p>

                    <p>
                        <strong><?= t('Expires', 'Vence') ?>:</strong> 
                        <?= date('M j, Y', strtotime($cert['expiry_date'])) ?>
                    </p>
                    
                    <p class="cert-status <?= $cardClass ?>">
                        <?= $isExpired ? t('Expired', 'Vencido') : ($isExpiring ? t('Expires Soon', 'Vence Pronto') : t('Valid', 'Valido')) ?>
                    </p>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
        <!-- INCIDENTS SECTION --->
        <section id="incidents-section" class="content-section">

            <div class="section-header">
                <h2><?= t('My Incidents', 'Mis Incidentes') ?></h2>
            </div>

            <?php while ($incident = $incidents->fetch_assoc()): ?>
            <div class="incident-card">
                <h4><?= htmlspecialchars($incident['description']) ?></h4>
                <p>
                    <strong><?= t('Date', 'Fecha') ?>:</strong>
                     <?= date('M j, Y', strtotime($incident['incident_date'])) ?>
                </p>

                <?php if ($incident['incident_result']): ?>
                    <p>
                        <strong><?= t('Result', 'Resultado') ?>:</strong>
                         <?= htmlspecialchars($incident['incident_result']) ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
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
    <!--ECHO ECHO ECHO IF NO WORK-->
    <script src="js/user_script.js"></script>
</body>
</html>

<?php
session_start();
if (isset($_GET['lang'])) $_SESSION['language'] = $_GET['lang'];
$lang = $_SESSION['language'] ?? 'en';
function t($en, $es) { global $lang; return $lang === 'es' ? $es : $en; }
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iron Women Library</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>
    <div class="container">
        <div class="lang-toggle">
            <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">English</a>
            <a href="?lang=es" class="<?= $lang === 'es' ? 'active' : '' ?>">Espanol</a>
        </div>
        <div class="form-box active" id="login-form">
            <h2><?= t('Login', 'Iniciar Sesion') ?></h2>
            <?php if ($error): ?><p class="error-message"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form action="login_register.php" method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="<?= t('Email', 'Correo Electronico') ?>" required>
                <input type="password" name="password" placeholder="<?= t('Password', 'Contrasena') ?>" required>
                <button type="submit"><?= t('Login', 'Iniciar Sesion') ?></button>
            </form>
            <p><?= t("Don't have an account?", "No tienes cuenta?") ?> <a href="#" onclick="showForm('register-form')"><?= t('Register', 'Registrarse') ?></a></p>
        </div>
        <div class="form-box" id="register-form">
            <h2><?= t('Register', 'Registrarse') ?></h2>
            <form action="login_register.php" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="name-row">
                    <input type="text" name="first_name" placeholder="<?= t('First Name', 'Nombre') ?>" required>
                    <input type="text" name="middle_name" placeholder="<?= t('Middle Name', 'Segundo Nombre') ?>">
                </div>
                <input type="text" name="last_name" placeholder="<?= t('Last Name', 'Apellido') ?>" required>
                <input type="email" name="email" placeholder="<?= t('Email', 'Correo Electronico') ?>" required>
                <input type="tel" name="phone" placeholder="<?= t('Phone (555-1234)', 'Telefono (555-1234)') ?>">
               
               <!-- 
                just remove this
               <select name="role" required>  
                <option value=""><?= t('--Select Role--', '--Seleccionar Rol--') ?></option>
                    <option value="master_admin">Master Admin</option>
                    <option value="admin">Admin</option>
                    <option value="user"><?= t('User', 'Usuario') ?></option>
        
                </select> -->


                <input type="password" name="password" id="password" placeholder="<?= t('Password (min 6 chars)', 'Contrasena (min 6 caracteres)') ?>" required onkeyup="checkPasswordMatch()">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="<?= t('Confirm Password', 'Confirmar Contrasena') ?>" required onkeyup="checkPasswordMatch()">
                <div id="password-match" class="password-match hidden"></div>
                <button type="submit" id="register-btn"><?= t('Register', 'Registrarse') ?></button>
            </form>
        
            <p><?= t('Already have an account?', 'Ya tienes cuenta?') ?> <a href="#" onclick="showForm('login-form')"><?= t('Login', 'Iniciar Sesion') ?></a></p>
        </div>
    </div>
    <script src="js/script.js"></script>
</body>
</html>

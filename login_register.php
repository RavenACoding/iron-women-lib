<?php
session_start();
require_once 'config.php';
// LOGIN HANDLING
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if ($user['is_active'] == 0) {
                $_SESSION['login_error'] = 'Your account is inactive. Contact admin.';
                header("Location: index.php");
                exit();
            }
            if (password_verify($password, $user['password'])) {
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['admin_type'] = $user['admin_type'];
                
                if ($user['role'] === 'admin' && $user['admin_type'] === 'master') {
                    header("Location: master_page.php");
                } elseif ($user['role'] === 'admin') {
                    header("Location: admin_page.php");
                } else {
                    header("Location: user_page.php");
                }
                exit();
            }
        }
        $_SESSION['login_error'] = 'Incorrect email or password';
        header("Location: index.php");
        exit();
    }
    // Registration
    if ($action === 'register') {
        $first_name = trim($_POST['first_name']);
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone'] ?? '');
        $role_select = $_POST['role'];
        $password = $_POST['password'];
        $confirm = $_POST['confirm_password'];
        //PASSWORD VALIDATION
        if ($password !== $confirm) {
            $_SESSION['login_error'] = 'Passwords do not match';
            header("Location: index.php");
            exit();
        }
        if (strlen($password) < 6) {
            $_SESSION['login_error'] = 'Password must be at least 6 characters';
            header("Location: index.php");
            exit();
        }
        
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $_SESSION['login_error'] = 'Email already registered';
            header("Location: index.php");
            exit();
        }
        //INSERT USER IN DB
        $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
        $name = preg_replace('/\s+/', ' ', $name);
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $role = ($role_select === 'master_admin' || $role_select === 'admin') ? 'admin' : 'user';
        $admin_type = $role_select === 'master_admin' ? 'master' : ($role_select === 'admin' ? 'regular' : NULL);
        
        $stmt = $conn->prepare("INSERT INTO users (name, first_name, middle_name, last_name, email, phone, password, role, admin_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $name, $first_name, $middle_name, $last_name, $email, $phone, $hashed, $role, $admin_type);
        //EXECUTE AND LOGIN
        if ($stmt->execute()) {
            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['role'] = $role;
            $_SESSION['admin_type'] = $admin_type;
            
            if ($role === 'admin' && $admin_type === 'master') {
                header("Location: master_page.php");
            } elseif ($role === 'admin') {
                header("Location: admin_page.php");
            } else {
                header("Location: user_page.php");
            }
            exit();
        }
        $_SESSION['login_error'] = 'Registration failed';
        header("Location: index.php");
        exit();
    }
}
header("Location: index.php");
exit();
?>

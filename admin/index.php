<?php
session_start();
require_once '../includes/functions.php';

if(isset($_SESSION['id_admin'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

    $username = clean_input($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT id_admin, username, password FROM tbl_admin WHERE username = '$username'";
    $result = query($sql);

    $is_user_found = false;
    $user_hash = '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'; // Dummy hash

    if (mysqli_num_rows($result) === 1) { 
        $user = fetch_assoc($result);
        $user_hash = $user['password'];
        $is_user_found = true;
    } 

    // Verifikasi Selalu Dijalankan (Timing Attack Mitigation)
    if (password_verify($password, $user_hash) && $is_user_found) {
        
        // --- LOGIKA BERHASIL ---
        $_SESSION['id_admin'] = $user['id_admin'];
        $_SESSION['username'] = $user['username'];

        header('Location: dashboard.php');
        exit();
        
    } else {
        // --- LOGIKA GAGAL ---
        $error_message = 'Username atau password salah. Silakan coba lagi.';
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin - Salemba Kitchen</title>
  <style>
    body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); width: 300px; }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background-color: #ff5722; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: red; text-align: center; margin-bottom: 10px; }
  </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if (!empty($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
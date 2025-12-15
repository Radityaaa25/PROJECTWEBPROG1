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
  <title>Login Admin - Resto Kita</title>
  <link rel="stylesheet" href="../css/style.css?v=3">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>Admin Login</h2>
        
        <?php if (!empty($error_message)): ?>
            <p class="login-error"><?php echo $error_message; ?></p>
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
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>
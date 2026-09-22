<?php
session_start();
include 'koneksi.php';

$token = trim($_GET['token'] ?? '');
$is_valid = false;
$user = null;

if (!empty($token)) {
    $token_escaped = mysqli_real_escape_string($koneksi, $token);
    $query = "SELECT * FROM users WHERE reset_token='$token_escaped' AND reset_expires > NOW() LIMIT 1";
    $result = $koneksi->query($query);
    if ($result && $result->num_rows > 0) {
        $is_valid = true;
        $user = $result->fetch_assoc();
    }
}

if ($is_valid && isset($_POST['reset'])) {
    $new_pass     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if (strlen($new_pass) < 8) {
        echo "<script>alert('Password minimal harus 8 karakter!');</script>";
    } elseif ($new_pass !== $confirm_pass) {
        echo "<script>alert('Konfirmasi password tidak cocok!');</script>";
    } else {
        $password_hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $user_id = $user['id'];

        $update = $koneksi->query("UPDATE users SET password='$password_hashed', reset_token=NULL, reset_expires=NULL WHERE id='$user_id'");
        if ($update) {
            header("Location: login.php?pesan=reset_sukses");
            exit();
        } else {
            echo "<script>alert('Gagal mengubah password!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Reset Password - Sosmed App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Bikin Password Baru</h2>
        <?php if (!$is_valid): ?>
            <p style="text-align: center; color: #ff6b6b; font-size: 14px; margin-bottom: 20px;">
                Token tidak valid atau sudah kedaluwarsa.
            </p>
            <div class="auth-links">
                <a href="lupa_password.php"><strong>Minta Reset Baru</strong></a>
                <a href="login.php">Kembali ke Login</a>
            </div>
        <?php else: ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Password Baru" minlength="8" required>
                </div>
                <div class="form-group">
                    <label>Ulangi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" minlength="8" required>
                </div>
                <button type="submit" name="reset" class="btn btn-outline">Simpan Password</button>
            </form>
            <div class="auth-links">
                <a href="login.php">Batal & Kembali ke Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

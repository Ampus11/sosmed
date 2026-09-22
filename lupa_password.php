<?php
session_start();
include 'koneksi.php';

if (isset($_POST['cek'])) {
    $user_input = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $tgl_lahir  = mysqli_real_escape_string($koneksi, trim($_POST['tanggal_lahir']));

    $query = "SELECT * FROM users WHERE (username='$user_input' OR email='$user_input') AND tanggal_lahir='$tgl_lahir'";
    $result = $koneksi->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));

        $update = $koneksi->query("UPDATE users SET reset_token='$token', reset_expires=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id='{$user['id']}'");
        if ($update) {
            header("Location: reset_password.php?token=" . urlencode($token));
            exit();
        } else {
            echo "<script>alert('Gagal memproses permintaan!');</script>";
        }
    } else {
        echo "<script>alert('Username/Email atau Tanggal Lahir tidak cocok!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Lupa Password - Sosmed App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Lupa Password</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label>Username atau Email</label>
                <input type="text" name="username" class="form-control" placeholder="Username or Email" required>
            </div>
            <div class="form-group">
                <label>Tanggal Lahir Terdaftar</label>
                <input type="date" name="tanggal_lahir" class="form-control" required>
            </div>
            <button type="submit" name="cek" class="btn btn-outline">Verifikasi</button>
        </form>
        <div class="auth-links">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>

<?php
session_start();
include 'koneksi.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass     = $_POST['password'];
    $bulan    = $_POST['bulan'];
    $tanggal  = $_POST['tanggal'];
    $tahun    = $_POST['tahun'];

    // Cek apakah username sudah dipakai atau belum
    $cek = $koneksi->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
    if ($cek->num_rows > 0) {
        echo "<script>alert('Username atau Email sudah terdaftar!'); window.history.back();</script>";
    } else {
        $tanggal_format = str_pad($tanggal, 2, '0', STR_PAD_LEFT);
        $tgl_lahir = "$tahun-$bulan-$tanggal_format";
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);

        // Kolom bio dan foto_profil bisa diisi nanti di halaman profil
        $sql = "INSERT INTO users (username, email, password, tanggal_lahir) 
                VALUES ('$username', '$email', '$password_hashed', '$tgl_lahir')";
        
        if ($koneksi->query($sql) === TRUE) {
            echo "<script>alert('Registrasi Berhasil! Silakan Login'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Error: " . $koneksi->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sosmed App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Buat Akun Baru</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <div class="birthday-group">
                    <select name="bulan" class="form-control" required>
                        <option value="" disabled selected>Bulan</option>
                        <?php
                        $months = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                            '07' => 'July', '08' => 'Agustus', '09' => 'September',
                            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        foreach ($months as $num => $name) {
                            echo "<option value='$num'>$name</option>";
                        }
                        ?>
                    </select>
                    <select name="tanggal" class="form-control" required>
                        <option value="" disabled selected>Tanggal</option>
                        <?php
                        for ($i = 1; $i <= 31; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>
                    <select name="tahun" class="form-control" required>
                        <option value="" disabled selected>Tahun</option>
                        <?php
                        $currentYear = date('Y');
                        for ($i = $currentYear; $i >= 1900; $i--) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" minlength="8" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="disclaimer">
                Dengan mendaftar, Anda menyetujui<a href="#"> Ketentuan Pengguna </a> kami dan menyetujui <a href="#">Kebijakan Privasi</a>.
            </div>
            <button type="submit" name="register" class="btn btn-solid">Daftar</button>
        </form>
        <div class="auth-links">
            <a href="login.php">Sudah Punya akun? Login</a>
        </div>
    </div>
</body>
</html>
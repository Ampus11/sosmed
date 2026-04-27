<?php
include 'koneksi.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $pass     = $_POST['password'];
    $bulan   = $_POST['bulan'];
    $tanggal = $_POST['tanggal'];
    $tahun   = $_POST['tahun'];
    $tanggal_format = str_pad($tanggal, 2, '0', STR_PAD_LEFT);
    $tgl_lahir = "$tahun-$bulan-$tanggal_format";
    $password_hashed = password_hash($pass, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, email, password, tanggal_lahir) 
            VALUES ('$username', '$email', '$password_hashed', '$tgl_lahir')";
    if ($koneksi->query($sql) === TRUE) {
        echo "<script>alert('Registrasi Berhasil! Silakan Login'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $koneksi->error . "');</script>";
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
                            '01' => 'January', '02' => 'February', '03' => 'March',
                            '04' => 'April', '05' => 'May', '06' => 'June',
                            '07' => 'July', '08' => 'August', '09' => 'September',
                            '10' => 'October', '11' => 'November', '12' => 'December'
                        ];
                        // Looping untuk menampilkan option bulan
                        foreach ($months as $num => $name) {
                            echo "<option value='$num'>$name</option>";
                        }
                        ?>
                    </select>
                    <select name="tanggal" class="form-control" required>
                        <option value="" disabled selected>Day</option>
                        <?php
                        // Looping dari tanggal 1 sampai 31
                        for ($i = 1; $i <= 31; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select>

                    <select name="tahun" class="form-control" required>
                        <option value="" disabled selected>Year</option>
                        <?php
                        // Mengambil tahun saat ini, dan melooping mundur hingga tahun 1900
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
                <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
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
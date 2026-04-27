<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// PROSES UPDATE PROFIL
if (isset($_POST['update_profil'])) {
    $username_baru = $_POST['username']; // Menangkap input username baru
    $bio = $_POST['bio'];
    
    // 1. Cek apakah username baru sudah dipakai orang lain (Poin Debugging/Validasi)
    $cek_user = $koneksi->query("SELECT id FROM users WHERE username='$username_baru' AND id != '$user_id'");
    
    if ($cek_user->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan orang lain! Silakan pilih yang lain.'); window.location='profil.php';</script>";
    } else {
        $nama_file = $_FILES['foto_profil']['name'];
        $tmp_file  = $_FILES['foto_profil']['tmp_name'];

        if ($nama_file != "") {
            $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
            $nama_baru = "profil_" . $user_id . "." . $ekstensi;
            $direktori = "uploads/profil/" . $nama_baru;
            move_uploaded_file($tmp_file, $direktori);

            // Update Username, Bio, dan Foto
            $sql_update = "UPDATE users SET username='$username_baru', bio='$bio', foto_profil='$nama_baru' WHERE id='$user_id'";
        } else {
            // Update Username dan Bio saja
            $sql_update = "UPDATE users SET username='$username_baru', bio='$bio' WHERE id='$user_id'";
        }

        if ($koneksi->query($sql_update) === TRUE) {
            // Update Session agar nama di navbar juga berubah otomatis
            $_SESSION['username'] = $username_baru;
            echo "<script>alert('Profil & Username berhasil diperbarui!'); window.location='profil.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . $koneksi->error . "');</script>";
        }
    }
}

// AMBIL DATA USER TERBARU
$query_user = "SELECT * FROM users WHERE id='$user_id'";
$result = $koneksi->query($query_user);
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - Sosmed LSP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; }
        .card { background-color: #1d1d1d; border: 1px solid #333; color: white; }
        label { color: #f8f9fa !important; font-weight: 500; }
        .form-control { background-color: #2a2a2a; color: #ffffff; border: 1px solid #555; }
        .form-control:focus { background-color: #333; color: #ffffff; border-color: #0d6efd; box-shadow: none; }
        .foto-preview { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #6c757d; }
        .foto-inisial { width: 150px; height: 150px; border-radius: 50%; background-color: #495057; color: white; font-size: 60px; font-weight: bold; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 3px solid #6c757d; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-black border-bottom border-secondary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">SOSMED LSP</a>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3 small">Halo, @<?= $_SESSION['username'] ?></span>
            <a href="index.php" class="btn btn-outline-light btn-sm me-2">Timeline</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="max-width: 600px;">
    <div class="card shadow">
        <div class="card-body">
            <h4 class="card-title text-center mb-4">Pengaturan Profil</h4>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="text-center mb-4">
                    <?php 
                    $foto_path = "uploads/profil/" . $user_data['foto_profil'];
                    if (!empty($user_data['foto_profil']) && file_exists($foto_path)): 
                    ?>
                        <img src="<?= $foto_path ?>" class="foto-preview mb-2" alt="Foto Profil">
                    <?php else: ?>
                        <div class="foto-inisial mb-2"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="mt-3">
                        <label class="form-label small d-block">Ganti Foto Profil</label>
                        <input type="file" name="foto_profil" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= $user_data['username'] ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Bio Anda</label>
                    <textarea name="bio" class="form-control" rows="3" placeholder="Ceritakan diri Anda..."><?= $user_data['bio'] ?></textarea>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" name="update_profil" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
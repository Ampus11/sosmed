<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['update_profil'])) {
    $username_baru = mysqli_real_escape_string($koneksi, $_POST['username']); 
    $bio = mysqli_real_escape_string($koneksi, $_POST['bio']);
    $cek_user = $koneksi->query("SELECT id FROM users WHERE username='$username_baru' AND id != '$user_id'");
    
    if ($cek_user->num_rows > 0) {
        echo "<script>alert('Username sudah digunakan orang lain!'); window.location='profil.php';</script>";
    } else {
        $nama_file = $_FILES['foto_profil']['name'];
        $tmp_file  = $_FILES['foto_profil']['tmp_name'];
        
        if ($nama_file != "") {
            $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
            $nama_baru = "profil_" . $user_id . "_" . time() . "." . $ekstensi;
            $direktori = "uploads/profil/" . $nama_baru;
            
            move_uploaded_file($tmp_file, $direktori);
            $sql_update = "UPDATE users SET username='$username_baru', bio='$bio', foto_profil='$nama_baru' WHERE id='$user_id'";
        } else {
            $sql_update = "UPDATE users SET username='$username_baru', bio='$bio' WHERE id='$user_id'";
        }

        if ($koneksi->query($sql_update) === TRUE) {
            $_SESSION['username'] = $username_baru;
            echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . $koneksi->error . "');</script>";
        }
    }
}

$query_user = "SELECT * FROM users WHERE id='$user_id'";
$result = $koneksi->query($query_user);
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings - Sosmed LSP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #030303; color: #d7dadc; font-family: 'Segoe UI', sans-serif; }
        
        .navbar { background-color: #1a1a1b; border-bottom: 1px solid #343536; }
        
        .card { 
            background-color: #1a1a1b; 
            border: 1px solid #343536; 
            border-radius: 5px; 
            color: #d7dadc;
        }

        label { color: #818384; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }

        .form-control { 
            background-color: #272729; 
            color: #ffffff; 
            border: 1px solid #343536; 
            border-radius: 4px;
        }
        .form-control:focus { 
            background-color: #272729; 
            color: #ffffff; 
            border-color: #d7dadc; 
            box-shadow: none; 
        }

        .foto-preview { 
            width: 120px; 
            height: 120px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 2px solid #343536; 
        }

        .foto-inisial { 
            width: 120px; 
            height: 120px; 
            border-radius: 8px; 
            background-color: #d7dadc; 
            color: #1a1a1b; 
            font-size: 50px; 
            font-weight: bold; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0 auto;
        }

        .btn-primary { 
            background-color: #d7dadc; 
            color: #1a1a1b; 
            border: none; 
            font-weight: bold; 
            border-radius: 20px; 
            padding: 8px 20px;
        }
        .btn-primary:hover { background-color: #ffffff; color: #000; }
        .btn-outline-light:hover { background-color: #272729; border-color: #d7dadc; color: #fff; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark sticky-top mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <span class="text-danger fs-4 me-2">⭕</span> SOSMED
        </a>
        <div class="d-flex">
            <a href="index.php" class="btn btn-outline-light btn-sm me-2 rounded-pill">dashboard</a>
            <a href="logout.php" class="btn btn-danger btn-sm rounded-pill">Logout</a>
        </div>
    </div>
</nav>
<div class="container" style="max-width: 650px;">
    <div class="settings-header mb-4">
        <h5 class="fw-bold">User Settings</h5>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="row align-items-center mb-5">
                    <div class="col-auto">
                        <?php 
                        $foto_path = "uploads/profil/" . $user_data['foto_profil'];
                        if (!empty($user_data['foto_profil']) && file_exists($foto_path)): 
                        ?>
                            <img src="<?= $foto_path ?>" class="foto-preview" alt="Foto Profil">
                        <?php else: ?>
                            <div class="foto-inisial"><?= strtoupper(substr($user_data['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <label class="mb-2">Foto Profil</label>
                        <input type="file" name="foto_profil" class="form-control form-control-sm" accept="image/*">
                        <small class="text-secondary d-block mt-1">Format: JPG, PNG. Maks 2MB.</small>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-2">Display Name / Username</label>
                    <input type="text" name="username" class="form-control py-2" value="<?= htmlspecialchars($user_data['username']) ?>" required>
                </div>

                <div class="mb-4">
                    <label class="mb-2">About (Bio)</label>
                    <textarea name="bio" class="form-control py-2" rows="4"><?= htmlspecialchars($user_data['bio'] ?? "") ?></textarea>

                <div class="border-top border-secondary pt-4 mt-2 d-flex justify-content-end">
                    <button type="submit" name="update_profil" class="btn btn-primary px-4">SIMPAN PERUBAHAN</button>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center mt-4">
        <p class="text-secondary small">Terdaftar pada SOSMED 2026</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = $_GET['id'];
$query = "SELECT * FROM posts WHERE id='$post_id' AND user_id='$user_id'";
$result = $koneksi->query($query);
if ($result->num_rows == 0) {
    echo "<script>alert('Akses ditolak! Anda tidak bisa mengedit postingan orang lain.'); window.location='index.php';</script>";
    exit();
}

$post_data = $result->fetch_assoc();
if (isset($_POST['update_post'])) {
    $teks_baru = substr($koneksi->real_escape_string($_POST['teks']), 0, 250);

    $sql_update = "UPDATE posts SET teks='$teks_baru' WHERE id='$post_id'";

    if ($koneksi->query($sql_update) === TRUE) {
        echo "<script>alert('Postingan berhasil diupdate!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal update: " . $koneksi->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Postingan - Sosmed LSP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; }
        .card { background-color: #1d1d1d; border: 1px solid #333; color: white; }
        .form-control { background-color: #2a2a2a; color: white; border: 1px solid #555; }
        .form-control:focus { background-color: #333; color: white; border-color: #0d6efd; box-shadow: none; }
    </style>
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title mb-4">Edit Postingan</h5>
            
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label text-light">Teks Postingan (Maks 250 Karakter)</label>
                    <textarea name="teks" class="form-control" rows="4" maxlength="250" required><?= $post_data['teks'] ?></textarea>
                </div>

                <?php if($post_data['gambar'] || $post_data['file_lampiran']): ?>
                    <div class="alert alert-secondary py-2" role="alert">
                        <small>📎 Postingan ini memiliki lampiran. (Mengubah lampiran dinonaktifkan di halaman edit sederhana ini).</small>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" name="update_post" class="btn btn-primary px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
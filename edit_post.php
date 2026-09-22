<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$uid = $_SESSION['user_id'];
$pid = $_GET['id'];

// Ambil data lama & pastikan milik sendiri
$res = $koneksi->query("SELECT * FROM posts WHERE id = '$pid' AND user_id = '$uid'");
$data = $res->fetch_assoc();
if (!$data) header("Location: index.php");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Post | SOSMED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #000; color: #fff; font-family: -apple-system, sans-serif; }
        .edit-box { max-width: 600px; margin: 50px auto; border: 1px solid #2f3336; border-radius: 16px; padding: 20px; }
        .form-control { background: transparent; border: none; color: #fff; font-size: 1.2rem; }
        .form-control:focus { background: transparent; color: #fff; box-shadow: none; }
        #pv-edit { width: 100%; border-radius: 12px; margin-top: 15px; display: <?= $data['gambar'] ? 'block' : 'none' ?>; border: 1px solid #333; }
    </style>
</head>
<body>
<div class="container">
    <div class="edit-box">
        <div class="d-flex justify-content-between mb-4">
            <a href="index.php" class="text-white text-decoration-none">✕</a>
            <h6 class="fw-bold">Edit Postingan</h6>
            <button type="submit" form="f-edit" name="update" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">Simpan</button>
        </div>

        <form id="f-edit" action="aksi_postingan.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_post" value="<?= $pid ?>">
            <textarea name="teks" class="form-control" rows="5" style="resize:none" required><?= $data['teks'] ?></textarea>
            
            <img id="pv-edit" src="<?= $data['gambar'] ? 'uploads/lampiran/'.$data['gambar'] : '#' ?>">

            <div class="mt-4 pt-3 border-top border-secondary">
                <label class="text-primary small" style="cursor:pointer">
                    <input type="file" name="lampiran" hidden onchange="showPv(this)"> Ganti Foto/File
                </label>
            </div>
        </form>
    </div>
</div>
<script>
function showPv(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => { 
            const pv = document.getElementById('pv-edit');
            pv.src = e.target.result; 
            pv.style.display = 'block'; 
        };
        r.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
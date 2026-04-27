<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id_login = $_SESSION['user_id'];

$filter = "";
if (isset($_GET['hashtag']) && !empty($_GET['hashtag'])) {
    $tag = $_GET['hashtag'];
    $filter = " WHERE teks LIKE '%#$tag%' ";
}

$query_posts = "SELECT posts.*, users.username, users.foto_profil 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                $filter
                ORDER BY posts.created_at DESC";
$result_posts = $koneksi->query($query_posts);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Timeline - Sosmed LSP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; color: white; }
        .card { background-color: #1d1d1d; border: 1px solid #333; color: white; }
        .form-control, .form-control:focus { background-color: #2a2a2a; color: white; border: 1px solid #444; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-black border-bottom border-secondary mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">SOSMED</a>
        <div class="d-flex">
            <a href="profil.php" class="btn btn-outline-light btn-sm me-2">Profile</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>
<div class="container" style="max-width: 700px;">
    <div class="card mb-4 shadow">
        <div class="card-body">
            <h6 class="card-title">Buat Postingan Baru</h6>
            <form action="aksi_postingan.php" method="POST" enctype="multipart/form-data">
                <textarea name="teks" class="form-control mb-2" maxlength="250" placeholder="Apa yang terjadi? Gunakan #hashtag" required></textarea>
                <div class="row g-2">
                    <div class="col">
                        <input type="file" name="lampiran" class="form-control form-control-sm" accept="image/*, .pdf, .doc, .docx">
                        <small class="text-muted">Gambar/File (Opsional)</small>
                    </div>
                    <div class="col-auto">
                        <button type="submit" name="tambah_post" class="btn btn-primary btn-sm px-4">Post</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <form action="" method="GET" class="mb-4">
        <div class="input-group">
            <span class="input-group-text bg-dark text-white border-secondary">#</span>
            <input type="text" name="hashtag" class="form-control" placeholder="Cari hashtag...">
            <button class="btn btn-secondary" type="submit">Filter</button>
        </div>
    </form>
    <?php while($post = $result_posts->fetch_assoc()): ?>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6 class="mb-1 text-primary">@<?= $post['username'] ?></h6>
                <small class="text-muted"><?= $post['created_at'] ?></small>
            </div>
            <p class="card-text my-2"><?= $post['teks'] ?></p>
            <?php if(!empty($post['gambar'])): ?>
                <div class="mb-3 mt-2">
                    <img src="uploads/lampiran/<?= $post['gambar'] ?>" alt="Gambar Postingan" class="img-fluid rounded border border-secondary" style="max-height: 400px; width: 100%; object-fit: cover;">
                </div>
            <?php endif; ?>

            <?php if(!empty($post['file_lampiran'])): ?>
                <div class="mb-3 mt-2 p-2 border border-secondary rounded" style="background-color: #222;">
                    <small class="text-light">📎 File Terlampir: </small>
                    <a href="uploads/lampiran/<?= $post['file_lampiran'] ?>" target="_blank" class="text-info text-decoration-none fw-bold">
                        Lihat Dokumen
                    </a>
                </div>
            <?php endif; ?>
            <?php if($post['user_id'] == $user_id_login): ?>
                <div class="mb-3">
                    <a href="edit_post.php?id=<?= $post['id'] ?>" class="btn btn-link btn-sm text-warning p-0 me-2">Edit</a>
                    <a href="aksi_postingan.php?hapus=<?= $post['id'] ?>" class="btn btn-link btn-sm text-danger p-0" onclick="return confirm('Hapus postingan?')">Hapus</a>
                </div>
            <?php endif; ?>
            <hr class="border-secondary">
            <div class="comments-section ms-4">
                <?php
                $p_id = $post['id'];
                $q_comm = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = $p_id ORDER BY created_at ASC";
                $res_comm = $koneksi->query($q_comm);
                while($comm = $res_comm->fetch_assoc()):
                ?>
                    <div class="mb-2 border-start border-secondary ps-3">
                        <small class="fw-bold text-info">@<?= $comm['username'] ?></small>
                        <p class="mb-0 small"><?= $comm['teks'] ?></p>
                    </div>
                <?php endwhile; ?>

                <form action="aksi_komentar.php" method="POST" class="mt-2">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" name="teks_komentar" class="form-control" placeholder="Tulis komentar..." required>
                        <button class="btn btn-outline-primary" type="submit" name="tambah_komentar">Balas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
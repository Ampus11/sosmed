<?php
session_start();
include 'koneksi.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];

$show_id = 0;
if (isset($_POST['open_id'])) {
    $show_id = $_POST['open_id'];
} elseif (isset($_SESSION['last_reply'])) {
    $show_id = $_SESSION['last_reply'];
    unset($_SESSION['last_reply']); // Hapus setelah dibaca
}

// logika search & filter   
$tag_f = isset($_GET['tag']) ? mysqli_real_escape_string($koneksi, $_GET['tag']) : '';
$q = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';

$where = "WHERE 1=1";
if ($tag_f) {
    $where .= " AND p.teks LIKE '%#$tag_f%'";
} elseif ($q) {
    if (strpos($q, '@') === 0) {
        $username = substr($q, 1);
        $where .= " AND u.username LIKE '%$username%'";
    } else {
        $where .= " AND p.teks LIKE '%$q%'";
    }
}

// query data postingan dengan join ke tabel users untuk menampilkan username & foto profil, serta subquery untuk menghitung total komentar
$posts = $koneksi->query("SELECT p.*, u.username, u.foto_profil, 
    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as total_komen 
    FROM posts p JOIN users u ON p.user_id = u.id $where ORDER BY p.created_at DESC");

// logika untuk menampilkan trending tag berdasarkan 50 postingan terbaru
$res_t = $koneksi->query("SELECT teks FROM posts ORDER BY created_at DESC LIMIT 50");
$tags = [];
while($r = $res_t->fetch_assoc()){ 
    preg_match_all('/#(\w+)/', $r['teks'], $m); 
    if($m[1]) foreach($m[1] as $t) $tags[] = strtolower($t); 
}
$top_tags = array_slice(array_count_values($tags), 0, 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SOSMED - Beranda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #000; color: #fff; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: #1a1a1b; border-bottom: 1px solid #333; }
        .card { background: #1a1a1b; border: 1px solid #333; color: #fff; margin-bottom: 12px; border-radius: 12px; }
        .pp-small { width: 38px; height: 38px; object-fit: cover; border-radius: 50%; }
        .pp-comm { width: 28px; height: 28px; object-fit: cover; border-radius: 50%; }
        .search-input { background: #272729 !important; border: 1px solid #444 !important; color: white !important; border-radius: 20px; }
        .reply-box { background: #000; padding: 15px; border-radius: 10px; border: 1px solid #333; margin-top: 10px; }
        .preview-img { max-height: 180px; display: none; border-radius: 8px; margin-bottom: 10px; border: 1px solid #444; }
        .btn-link-clean { color: #888; text-decoration: none; font-size: 0.9rem; }
        .btn-link-clean:hover { color: #fff; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><span class="text-danger">⭕</span> SOSMED</a>
        
        <form action="index.php" method="GET" class="mx-auto w-50 d-none d-md-flex">
            <input type="text" name="q" class="form-control form-control-sm search-input px-3" 
                   placeholder="Cari @user atau kata kunci..." value="<?= htmlspecialchars($q) ?>">
        </form>

        <div class="ms-auto d-flex align-items-center">
            <a href="profil.php" class="btn btn-outline-light btn-sm rounded-pill px-3 me-2">Profil</a>
            <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <?php if($q || $tag_f): ?>
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <span class="text-secondary">Hasil pencarian: <b><?= htmlspecialchars($q ?: '#'.$tag_f) ?></b></span>
                    <a href="index.php" class="btn btn-sm btn-dark border-secondary rounded-pill">Reset</a>
                </div>
            <?php else: ?>
                <div class="card p-3 mb-4">
                    <form action="aksi_postingan.php" method="POST" enctype="multipart/form-data">
                        <textarea name="teks" class="form-control bg-dark text-white border-0 mb-2" 
                                  placeholder="Apa yang kamu pikirkan? #tag" style="resize:none" required></textarea>
                        <img id="pv-main" class="preview-img">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="cursor:pointer">
                                Gambar/file <input type="file" name="lampiran" hidden onchange="showPv(this, 'pv-main')">
                            </label>
                            <button name="posting" class="btn btn-primary btn-sm px-4 rounded-pill fw-bold">Posting</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            <?php while($p = $posts->fetch_assoc()): ?>
            <div class="card p-3">
                <div class="d-flex align-items-center mb-2">
                    <img src="uploads/profil/<?= $p['foto_profil'] ?: 'default.jpg' ?>" class="pp-small me-2 border border-secondary">
                    <div>
                        <div class="fw-bold small">@<?= $p['username'] ?></div>
                        <div class="text-secondary" style="font-size: 0.75rem;"><?= date('d M Y', strtotime($p['created_at'])) ?></div>
                    </div>
                </div>
                
                <p class="mb-2 mt-1"><?= preg_replace('/#(\w+)/', '<a href="?tag=$1" class="text-info text-decoration-none">#$1</a>', nl2br(htmlspecialchars($p['teks']))) ?></p>
                
                <?php if($p['gambar']): ?>
                    <img src="uploads/lampiran/<?= $p['gambar'] ?>" class="img-fluid rounded border border-secondary mb-2" style="max-height:450px; width:100%; object-fit:cover;">
                <?php endif; ?>
                <div class="mt-2 pt-2 border-top border-secondary d-flex justify-content-between align-items-center">
                    <form action="" method="POST">
                        <input type="hidden" name="open_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-link btn-link-clean p-0">
                            💬 <?= $p['total_komen'] ?> Komentar
                        </button>
                    </form>
                    <?php if($p['user_id'] == $uid): ?>
                        <div class="d-flex gap-3"> 
                            <a href="edit_post.php?id=<?= $p['id'] ?>" class="text-info small text-decoration-none">Edit</a>
                            <a href="aksi_postingan.php?hapus=<?= $p['id'] ?>" class="text-danger small text-decoration-none" onclick="return confirm('Hapus?')">Hapus</a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if($show_id == $p['id']): ?>
                <div class="reply-box">
                    <form action="aksi_komentar.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                        <textarea name="teks" class="form-control bg-dark text-white border-0 mb-2 small" placeholder="Tulis balasan..." required></textarea>
                        <img id="pv-comm-<?= $p['id'] ?>" class="preview-img pv-comm-el">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="small text-secondary" style="cursor:pointer">Gambar/file <input type="file" name="lampiran" hidden onchange="showPv(this, 'pv-comm-<?= $p['id'] ?>')"></label>
                            <button name="kirim" class="btn btn-primary btn-sm rounded-pill px-3">Kirim</button>
                        </div>
                    </form>
                    
                    <div class="comm-list mt-3">
                        <?php 
                        $pid = $p['id'];
                        $comms = $koneksi->query("SELECT c.*, u.username, u.foto_profil FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = '$pid' ORDER BY c.created_at DESC");
                        while($c = $comms->fetch_assoc()): ?>
                            <div class="mb-3 pb-2 border-bottom border-secondary">
                                <div class="d-flex align-items-center mb-1">
                                    <img src="uploads/profil/<?= $c['foto_profil'] ?: 'default.jpg' ?>" class="pp-comm me-2">
                                    <small class="fw-bold text-info">@<?= $c['username'] ?></small>
                                    <?php if($c['user_id'] == $uid): ?>
                                        <a href="aksi_komentar.php?hapus=<?= $c['id'] ?>&pid=<?= $pid ?>" class="ms-auto text-danger" style="font-size:0.7rem;" onclick="return confirm('Hapus komentar?')">Hapus</a>
                                    <?php endif; ?>
                                </div>
                                <p class="small mb-1 ps-4 text-light"><?= nl2br(htmlspecialchars($c['teks'])) ?></p>
                                <?php if($c['gambar']): ?>
                                    <img src="uploads/lampiran/<?= $c['gambar'] ?>" class="rounded mb-2 ms-4" style="max-width:120px; border:1px solid #333;">
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="text-center mt-2">
                        <a href="index.php" class="btn btn-sm btn-link text-secondary text-decoration-none">✖ Tutup Komentar</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="col-md-4">
            <div class="card p-3 border-secondary sticky-top" style="top: 85px;">
                <h6 class="fw-bold mb-3"><span class="text-info"></span> Trending Hari Ini</h6>
                <?php if($top_tags): foreach($top_tags as $tag => $count): ?>
                    <a href="?tag=<?= $tag ?>" class="d-block text-info text-decoration-none py-1 small">
                        #<?= $tag ?> <span class="text-secondary float-end"><?= $count ?> Post</span>
                    </a>
                <?php endforeach; else: echo "<small class='text-secondary'>Belum ada hashtag.</small>"; endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showPv(input, targetId) {
    const img = document.getElementById(targetId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
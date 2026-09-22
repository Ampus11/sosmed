<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$uid = $_SESSION['user_id'];

// Aksi untuk menambah komentar
if (isset($_POST['kirim'])) {
    $pid = $_POST['post_id']; 
    $teks = substr($koneksi->real_escape_string($_POST['teks']), 0, 250);
    $img = "";

    if (!empty($_FILES['lampiran']['name'])) {
        $file_name = "comm_".time().".".pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES['lampiran']['tmp_name'], "uploads/lampiran/".$file_name)) $img = $file_name;
    }
    
    $koneksi->query("INSERT INTO comments (post_id, user_id, teks, gambar) VALUES ('$pid', '$uid', '$teks', '$img')");
    
    // session untuk scroll ke komentar yang baru dibuat
    $_SESSION['last_reply'] = $pid;
    header("Location: index.php#post-$pid"); 
    exit();
}

// Aksi untuk menghapus komentar
if (isset($_GET['hapus'])) {
    $cid = $_GET['hapus'];
    $pid = $_GET['pid']; 
    $koneksi->query("DELETE FROM comments WHERE id='$cid' AND user_id='$uid'");
    
    $_SESSION['last_reply'] = $pid;
    header("Location: index.php#post-$pid");
    exit();
}
?>
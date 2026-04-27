<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['tambah_komentar'])) {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $teks_komentar = substr($koneksi->real_escape_string($_POST['teks_komentar']), 0, 250);
    $sql = "INSERT INTO comments (post_id, user_id, teks) VALUES ('$post_id', '$user_id', '$teks_komentar')";

    if ($koneksi->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Gagal menambah komentar: " . $koneksi->error . "'); window.location='index.php';</script>";
    }
}
?>
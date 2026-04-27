<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
if (isset($_POST['tambah_post'])) {
    // Menangkap teks dan memotong string maksimal 250 karakter (sesuai soal)
    $teks = substr($koneksi->real_escape_string($_POST['teks']), 0, 250);

    // Menangani upload file opsional
    $nama_file = $_FILES['lampiran']['name'];
    $tmp_file  = $_FILES['lampiran']['tmp_name'];

    $nama_gambar = "";
    $nama_dokumen = "";

    // Jika user mengunggah file (Gambar atau File Opsional)
    if ($nama_file != "") {
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        // Membuat nama file unik (waktu + id user) agar tidak tertimpa
        $nama_baru = time() . "_" . $user_id . "." . $ekstensi;
        $direktori = "uploads/lampiran/" . $nama_baru;

        // Memindahkan file ke folder uploads/lampiran/
        if(move_uploaded_file($tmp_file, $direktori)) {
            // Memisahkan mana yang masuk kolom gambar, mana yang file_lampiran
            $ext_gambar = array('png', 'jpg', 'jpeg', 'gif');
            if (in_array($ekstensi, $ext_gambar)) {
                $nama_gambar = $nama_baru;
            } else {
                $nama_dokumen = $nama_baru;
            }
        }
    }

    // Query untuk menyimpan data ke database
    $sql = "INSERT INTO posts (user_id, teks, gambar, file_lampiran) 
            VALUES ('$user_id', '$teks', '$nama_gambar', '$nama_dokumen')";

    if ($koneksi->query($sql) === TRUE) {
        // Jika sukses, kembalikan ke halaman timeline
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $koneksi->error;
    }
}

if (isset($_GET['hapus'])) {
    $post_id = $_GET['hapus'];

    // AMAN: Cek dulu apakah postingan ini benar-benar milik user yang sedang login
    $query_cek = $koneksi->query("SELECT gambar, file_lampiran FROM posts WHERE id='$post_id' AND user_id='$user_id'");
    
    if ($query_cek->num_rows > 0) {
        $row = $query_cek->fetch_assoc();
        
        // Best Practice: Hapus file fisiknya juga dari folder agar memori server tidak penuh
        if ($row['gambar'] != "" && file_exists("uploads/lampiran/" . $row['gambar'])) {
            unlink("uploads/lampiran/" . $row['gambar']);
        }
        if ($row['file_lampiran'] != "" && file_exists("uploads/lampiran/" . $row['file_lampiran'])) {
            unlink("uploads/lampiran/" . $row['file_lampiran']);
        }

        // Hapus dari database (Komentar di dalamnya otomatis terhapus karena ON DELETE CASCADE)
        $hapus_sql = "DELETE FROM posts WHERE id='$post_id'";
        if ($koneksi->query($hapus_sql) === TRUE) {
            header("Location: index.php");
            exit();
        }
    } else {
        echo "<script>alert('Anda tidak memiliki akses menghapus postingan ini!'); window.location='index.php';</script>";
    }
}
?>
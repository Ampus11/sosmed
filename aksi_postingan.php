<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Aksi untuk menambah postingan
$user_id = $_SESSION['user_id'];
if (isset($_POST['posting'])) {
    $teks = substr($koneksi->real_escape_string($_POST['teks']), 0, 250);
    $nama_file = $_FILES['lampiran']['name'];
    $tmp_file  = $_FILES['lampiran']['tmp_name'];

    $nama_gambar = "";
    $nama_dokumen = "";
    if ($nama_file != "") {
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $nama_baru = time() . "_" . $user_id . "." . $ekstensi;
        $direktori = "uploads/lampiran/" . $nama_baru;
        if(move_uploaded_file($tmp_file, $direktori)) {
            $ext_gambar = array('png', 'jpg', 'jpeg', 'gif');
            if (in_array($ekstensi, $ext_gambar)) {
                $nama_gambar = $nama_baru;
            } else {
                $nama_dokumen = $nama_baru;
            }
        }
    }

    $sql = "INSERT INTO posts (user_id, teks, gambar, file_lampiran) 
            VALUES ('$user_id', '$teks', '$nama_gambar', '$nama_dokumen')";

    if ($koneksi->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $koneksi->error;
    }
}

// Aksi untuk mengupdate postingan
if (isset($_POST['update'])) {
    $pid = $_POST['id_post'];
    $teks = $koneksi->real_escape_string($_POST['teks']);
    $uid = $_SESSION['user_id'];

    if (!empty($_FILES['lampiran']['name'])) {
        $img = "post_".time().".".pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['lampiran']['tmp_name'], "uploads/lampiran/".$img);
        $koneksi->query("UPDATE posts SET teks='$teks', gambar='$img' WHERE id='$pid' AND user_id='$uid'");
    } else {
        $koneksi->query("UPDATE posts SET teks='$teks' WHERE id='$pid' AND user_id='$uid'");
    }
    header("Location: index.php");
}

// Aksi untuk menghapus postingan
if (isset($_GET['hapus'])) {
    $post_id = $_GET['hapus'];
    $query_cek = $koneksi->query("SELECT gambar, file_lampiran FROM posts WHERE id='$post_id' AND user_id='$user_id'");
    
    if ($query_cek->num_rows > 0) {
        $row = $query_cek->fetch_assoc();
        if ($row['gambar'] != "" && file_exists("uploads/lampiran/" . $row['gambar'])) {
            unlink("uploads/lampiran/" . $row['gambar']);
        }
        if ($row['file_lampiran'] != "" && file_exists("uploads/lampiran/" . $row['file_lampiran'])) {
            unlink("uploads/lampiran/" . $row['file_lampiran']);
        }
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
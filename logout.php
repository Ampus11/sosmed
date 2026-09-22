<?php
session_start();
session_destroy(); // menghapus semua data di session
header("Location: login.php"); // kembali ke halaman login setelah logout
exit();
?>
<?php
$host     = "localhost";    
$username = "root";        
$password = "";            
$database = "sosmed_db";

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// Set zona waktu default ke Indonesia (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');
?>
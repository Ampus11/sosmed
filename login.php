<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $user_input = $_POST['username']; 
    $password   = $_POST['password'];
    $query = "SELECT * FROM users WHERE username='$user_input' OR email='$user_input'";
    $result = $koneksi->query($query);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
        } else {
            echo "<script>alert('Password Salah!');</script>";
        }
    } else {
        echo "<script>alert('User tidak ditemukan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login - Sosmed App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Add account</h2>
        <form action="" method="POST">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Username or Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-outline">Login</button>
        </form>
        <div class="auth-links">
            <a href="register.php"><strong>Buat akun Baru</strong></a>
        </div>
    </div>
</body>
</html>
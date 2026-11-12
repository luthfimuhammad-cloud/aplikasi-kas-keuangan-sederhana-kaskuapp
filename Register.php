<?php
session_start();
require_once 'koneksi.php';

/** @var mysqli|null $koneksi */
if (!isset($koneksi) || !$koneksi) {
    $koneksi = null;
}

if (isset($_SESSION['login'])) {
    header("Location: uts_aplikasi.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$koneksi) {
        $error = "Database belum dibuat di phpMyAdmin!";
    } else {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $username     = trim($_POST['username'] ?? '');
        $password     = $_POST['password'] ?? '';

        if ($nama_lengkap === '' || $username === '' || $password === '') {
            $error = "Semua kolom wajib diisi!";
        } elseif (strlen($password) < 4) {
            $error = "Password minimal 4 karakter!";
        } else {

            $cek = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
            mysqli_stmt_bind_param($cek, "s", $username);
            mysqli_stmt_execute($cek);
            $hasil_cek = mysqli_stmt_get_result($cek);

            if ($hasil_cek && mysqli_num_rows($hasil_cek) > 0) {
                $error = "Username sudah digunakan, silakan pilih username lain!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $nama_lengkap);

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['flash_daftar'] = "Pendaftaran berhasil! Silakan login dengan akun baru Anda.";
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Gagal mendaftar akun: " . mysqli_error($koneksi);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - KasKuApp</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body {
            background: radial-gradient(circle at top right, #1e3a8a, #0f172a 60%, #020617 100%);
            display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 1rem;
        }
        .register-box { background: rgba(255, 255, 255, 0.98); width: 100%; max-width: 400px; padding: 2.25rem 2rem; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6); }
        .register-header { text-align: center; margin-bottom: 1.75rem; }
        .register-header i { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 13px; border-radius: 12px; font-size: 1.5rem; margin-bottom: 10px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); }
        .register-header h2 { font-size: 1.4rem; color: #0f172a; font-weight: 700; }
        .error { background-color: #fef2f2; color: #ef4444; border: 1px solid #fca5a5; padding: 10px; border-radius: 8px; font-size: 0.85rem; text-align: center; margin-bottom: 1.2rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #0f172a; }
        .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem; background: #f8fafc; }
        .form-group input:focus { border-color: #2563eb; background: #fff; }
        .btn-submit { width: 100%; background: linear-gradient(135deg, #10b981, #059669); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.95rem; margin-top: 0.5rem; transition: 0.2s; }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 15px rgba(16, 185, 129, 0.35); }
        .back-link { display: block; text-align: center; margin-top: 1.25rem; font-size: 0.85rem; color: #64748b; text-decoration: none; }
        .back-link strong { color: #2563eb; }
    </style>
</head>
<body>

<div class="register-box">
    <div class="register-header">
        <i class="fa-solid fa-user-plus"></i>
        <h2>Daftar Akun Baru</h2>
        <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Buat akun untuk mengakses KasKuApp</p>
    </div>

    <?php if ($error != ""): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan Nama Lengkap Anda" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Buat Username Login" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimal 4 karakter" required>
        </div>

        <button type="submit" class="btn-submit"><i class="fa-solid fa-check"></i> DAFTAR SEKARANG</button>
    </form>

    <a href="login.php" class="back-link">Sudah punya akun? <strong>Login di sini</strong></a>
</div>

</body>
</html>
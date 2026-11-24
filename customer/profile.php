<?php
require 'auth.php';
require '../customer/koneksi.php';

$id_user = (int) $_SESSION['id_user'];
$sukses  = null;
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_nama') {
        $nama = trim($_POST['nama'] ?? '');

        if ($nama === '') {
            $error = 'Nama tidak boleh kosong.';
        } else {
            $namaEsc = mysqli_real_escape_string($koneksi, $nama);
            mysqli_query($koneksi, "UPDATE users SET nama='$namaEsc' WHERE id_user='$id_user'");
            $_SESSION['nama'] = $nama;
            $sukses = 'Nama berhasil diperbarui.';
        }
    }

    if ($action === 'update_password') {
        $baru    = $_POST['password_baru'] ?? '';
        $konfirm = $_POST['password_konfirmasi'] ?? '';

        if (strlen($baru) < 6) {
            $error = 'Password baru minimal 6 karakter.';
        } elseif ($baru !== $konfirm) {
            $error = 'Konfirmasi password tidak sama.';
        } else {
            // Mengikuti pola hashing yang sudah dipakai di login.php (md5)
            $hash = md5($baru);
            mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE id_user='$id_user'");
            $sukses = 'Password berhasil diatur. Sekarang Anda juga bisa login manual pakai email & password ini.';
        }
    }
}

$q    = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($q);
$punyaPassword = !empty($user['password']);
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil - RESTO UTM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
:root {
    --bg-void: #170f19;
    --panel: #241726;
    --panel-2: #2f1b2f;
    --gold: #e3a83a;
    --gold-soft: #f0c674;
    --wine: #8a2846;
    --wine-soft: #b23a5c;
    --cream: #f5ead9;
    --muted: #a4919a;
    --danger: #e0587a;
    --success: #5cc99a;
}

* { box-sizing: border-box; }

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Manrope', sans-serif;
    background: var(--bg-void);
    color: var(--cream);
}

.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 32px;
    background: var(--panel);
    border-bottom: 1px solid rgba(164, 145, 154, 0.15);
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Fraunces', serif;
    font-style: italic;
    font-weight: 700;
    font-size: 1.2rem;
    color: var(--gold-soft);
    text-decoration: none;
}

.navbar-actions a {
    color: var(--cream);
    text-decoration: none;
    font-size: 0.9rem;
}

.container {
    max-width: 560px;
    margin: 0 auto;
    padding: 32px 24px 60px;
}

.page-title {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.6rem;
    margin: 0 0 20px;
}

.alert {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 0.85rem;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-error {
    background: rgba(224, 88, 122, 0.12);
    border: 1px solid rgba(224, 88, 122, 0.4);
    color: var(--danger);
}

.alert-success {
    background: rgba(92, 201, 154, 0.12);
    border: 1px solid rgba(92, 201, 154, 0.4);
    color: var(--success);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    background: var(--panel);
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 20px;
    border: 1px solid rgba(164, 145, 154, 0.12);
}

.profile-header img,
.avatar-fallback {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--gold-soft);
    flex-shrink: 0;
}

.avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--wine);
    font-weight: 700;
    font-size: 1.5rem;
}

.profile-header .nama {
    font-weight: 700;
    font-size: 1.1rem;
}

.profile-header .email {
    color: var(--muted);
    font-size: 0.85rem;
    margin-top: 2px;
}

.profile-header .badge-role {
    display: inline-block;
    margin-top: 6px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--gold-soft);
    background: rgba(227, 168, 58, 0.1);
    padding: 3px 10px;
    border-radius: 999px;
}

.card {
    background: var(--panel);
    border-radius: 16px;
    padding: 22px 24px;
    margin-bottom: 20px;
    border: 1px solid rgba(164, 145, 154, 0.12);
}

.card h2 {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.05rem;
    margin: 0 0 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 0.82rem;
    color: var(--muted);
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    background: var(--bg-void);
    border: 1px solid rgba(164, 145, 154, 0.3);
    border-radius: 10px;
    color: var(--cream);
    padding: 10px 12px;
    font-family: 'Manrope', sans-serif;
    font-size: 0.9rem;
}

.hint {
    font-size: 0.76rem;
    color: var(--muted);
    margin-top: 6px;
}

.btn-save {
    border: none;
    border-radius: 10px;
    padding: 11px 22px;
    font-weight: 700;
    font-size: 0.88rem;
    color: var(--panel-2);
    background: linear-gradient(120deg, var(--gold), var(--gold-soft));
    cursor: pointer;
}
</style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand"><i class="bi bi-egg-fried"></i> RESTO UTM</a>
    <div class="navbar-actions">
        <a href="index.php"><i class="bi bi-arrow-left"></i> Beranda</a>
    </div>
</nav>

<div class="container">
    <h1 class="page-title">Profil</h1>

    <?php if ($sukses): ?>
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($sukses) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-header">
        <?php if (!empty($user['foto'])): ?>
            <img src="<?= htmlspecialchars($user['foto']) ?>" alt="Foto profil">
        <?php else: ?>
            <div class="avatar-fallback"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
        <?php endif; ?>
        <div>
            <div class="nama"><?= htmlspecialchars($user['nama']) ?></div>
            <div class="email"><?= htmlspecialchars($user['email']) ?></div>
            <span class="badge-role"><?= htmlspecialchars($user['role']) ?></span>
        </div>
    </div>

    <div class="card">
        <h2>Ubah Nama</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_nama">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <button type="submit" class="btn-save">Simpan</button>
        </form>
    </div>

    <div class="card">
        <h2><?= $punyaPassword ? 'Ubah Password' : 'Atur Password' ?></h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_password">
            <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru" minlength="6" required>
            </div>
            <div class="form-group">
                <label for="password_konfirmasi">Konfirmasi Password</label>
                <input type="password" id="password_konfirmasi" name="password_konfirmasi" minlength="6" required>
            </div>
            <p class="hint">
                <?= $punyaPassword
                    ? 'Password ini dipakai kalau Anda ingin login manual (tanpa Google) di kemudian hari.'
                    : 'Akun ini dibuat lewat Google dan belum punya password. Atur password di sini kalau Anda ingin bisa login manual juga.' ?>
            </p>
            <button type="submit" class="btn-save">Simpan Password</button>
        </form>
    </div>
</div>

</body>
</html>
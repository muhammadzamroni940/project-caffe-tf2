<?php
require 'auth.php';
require '../admin/koneksi.php';

$id_user = (int) $_SESSION['id_user'];

// Ambil data lengkap user (termasuk foto dari Google, kalau ada)
$q    = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($q);

// Ambil pesanan yang masih aktif (belum selesai) milik customer ini
$qOrder = mysqli_query($koneksi, "
    SELECT * FROM orders
    WHERE id_customer='$id_user' AND status != 'selesai'
    ORDER BY created_at DESC
");

// Hitung total pesanan yang sudah selesai (buat sapaan/statistik kecil)
$qSelesai = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM orders
    WHERE id_customer='$id_user' AND status = 'selesai'
");
$totalSelesai = (int) (mysqli_fetch_assoc($qSelesai)['total'] ?? 0);

// Ambil beberapa menu buat rekomendasi di beranda
$qRekomendasi = mysqli_query($koneksi, "
    SELECT m.*, k.nama_kategori
    FROM menu m
    LEFT JOIN kategori k ON m.id_kategori = k.id_kategori
    WHERE m.stok > 0
    ORDER BY RAND()
    LIMIT 3
");

// Sapaan dinamis berdasarkan jam
$jam = (int) date('H');
if ($jam >= 4 && $jam < 11) {
    $sapaan = 'Selamat pagi';
    $subtitle = 'Sarapan enak buat mulai hari, yuk cek menunya.';
} elseif ($jam >= 11 && $jam < 15) {
    $sapaan = 'Selamat siang';
    $subtitle = 'Waktunya makan siang — sudah tahu mau pesan apa?';
} elseif ($jam >= 15 && $jam < 18) {
    $sapaan = 'Selamat sore';
    $subtitle = 'Ngopi atau ngemil sore ini kayaknya pas.';
} else {
    $sapaan = 'Selamat malam';
    $subtitle = 'Lapar malam-malam? Kami masih buka untuk Anda.';
}

$statusLabel = [
    'pending'   => 'Menunggu Pembayaran',
    'dibayar'   => 'Dibayar',
    'diproses'  => 'Sedang Diproses',
    'selesai'   => 'Selesai',
];

$statusColor = [
    'pending'   => 'var(--muted)',
    'dibayar'   => 'var(--gold-soft)',
    'diproses'  => 'var(--wine-soft)',
    'selesai'   => 'var(--success)',
];
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Beranda - RESTO UTM</title>
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
}

.navbar-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.navbar-user img,
.avatar-fallback {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid var(--gold-soft);
}

.avatar-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--wine);
    color: var(--cream);
    font-weight: 700;
    font-size: 0.95rem;
}

.navbar-user .nama {
    font-size: 0.92rem;
    font-weight: 600;
}

.navbar-user a.logout {
    color: var(--muted);
    font-size: 1.15rem;
    text-decoration: none;
    margin-left: 4px;
    transition: color 0.2s ease;
}

.navbar-user a.logout:hover {
    color: var(--danger);
}

.container {
    max-width: 980px;
    margin: 0 auto;
    padding: 40px 24px 60px;
}

/* ---- Hero ---- */
.hero {
    position: relative;
    margin-bottom: 32px;
    padding: 28px 26px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--wine) 0%, var(--panel) 65%);
    border: 1px solid rgba(240, 198, 116, 0.18);
    overflow: hidden;
}

.hero::after {
    content: "";
    position: absolute;
    right: -40px;
    top: -40px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(240,198,116,0.25), transparent 70%);
}

.hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--gold-soft);
    background: rgba(240, 198, 116, 0.12);
    padding: 5px 12px;
    border-radius: 999px;
    margin-bottom: 12px;
}

.hero h1 {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.9rem;
    margin: 0 0 8px;
    position: relative;
}

.hero p {
    color: var(--cream);
    opacity: 0.8;
    margin: 0;
    font-size: 0.96rem;
    max-width: 480px;
    position: relative;
}

.hero-stat {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 16px;
    font-size: 0.82rem;
    color: var(--gold-soft);
    font-weight: 600;
    position: relative;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
}

.menu-card {
    background: var(--panel);
    border-radius: 16px;
    padding: 24px 20px;
    text-decoration: none;
    color: var(--cream);
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid rgba(164, 145, 154, 0.12);
}

.menu-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.35);
    border-color: var(--gold-soft);
}

.menu-card i {
    font-size: 1.6rem;
    color: var(--gold-soft);
}

.menu-card .label {
    font-weight: 700;
    font-size: 1rem;
}

.menu-card .desc {
    color: var(--muted);
    font-size: 0.82rem;
}

.section-title {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    margin: 0 0 16px;
}

.section-title h2 {
    font-family: 'Fraunces', serif;
    font-weight: 600;
    font-size: 1.2rem;
    margin: 0;
}

.section-title a {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--gold-soft);
    text-decoration: none;
    white-space: nowrap;
}

.section-title a:hover { text-decoration: underline; }

/* ---- Rekomendasi menu ---- */
.rekomendasi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 40px;
}

.rekomendasi-card {
    background: var(--panel);
    border-radius: 16px;
    overflow: hidden;
    text-decoration: none;
    color: var(--cream);
    border: 1px solid rgba(164, 145, 154, 0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.rekomendasi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.35);
    border-color: var(--gold-soft);
}

.rekomendasi-foto {
    width: 100%;
    height: 130px;
    object-fit: cover;
    display: block;
    background: var(--panel-2);
}

.rekomendasi-foto-fallback {
    width: 100%;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--panel-2);
    color: var(--muted);
    font-size: 1.8rem;
}

.rekomendasi-body {
    padding: 14px 16px 16px;
}

.rekomendasi-kategori {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--gold-soft);
    font-weight: 700;
}

.rekomendasi-nama {
    font-weight: 700;
    font-size: 0.98rem;
    margin: 4px 0 6px;
}

.rekomendasi-harga {
    color: var(--muted);
    font-size: 0.85rem;
}

.order-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 8px;
}

.order-card {
    background: var(--panel);
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border: 1px solid rgba(164, 145, 154, 0.12);
}

.order-card .kode {
    font-weight: 700;
    font-size: 0.95rem;
}

.order-card .meta {
    color: var(--muted);
    font-size: 0.8rem;
    margin-top: 2px;
}

.order-card .status-badge {
    font-size: 0.78rem;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 999px;
    background: rgba(245, 234, 217, 0.08);
    white-space: nowrap;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: var(--muted);
    font-size: 0.9rem;
    background: var(--panel);
    border: 1px dashed rgba(164, 145, 154, 0.25);
    border-radius: 14px;
    padding: 32px 24px;
    text-align: center;
}

.empty-state i {
    font-size: 1.8rem;
    color: var(--gold-soft);
}

.empty-state a {
    margin-top: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--gold-soft);
    color: var(--bg-void);
    font-weight: 700;
    font-size: 0.82rem;
    padding: 9px 18px;
    border-radius: 999px;
    text-decoration: none;
}

footer.tagline {
    text-align: center;
    color: var(--muted);
    font-size: 0.8rem;
    padding: 8px 0 0;
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-brand"><i class="bi bi-egg-fried"></i> RESTO UTM</div>
    <div class="navbar-user">
        <?php if (!empty($user['foto'])): ?>
            <img src="<?= htmlspecialchars($user['foto']) ?>" alt="Foto profil">
        <?php else: ?>
            <div class="avatar-fallback"><?= strtoupper(substr($user['nama'], 0, 1)) ?></div>
        <?php endif; ?>
        <span class="nama"><?= htmlspecialchars($user['nama']) ?></span>
        <a href="logout.php" class="logout" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>

<div class="container">

    <div class="hero">
        <span class="hero-eyebrow"><i class="bi bi-stars"></i> RESTO UTM</span>
        <h1><?= $sapaan ?>, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?> 👋</h1>
        <p><?= $subtitle ?> Semua menu favorit kampus, tinggal beberapa klik dari genggaman Anda.</p>
        <?php if ($totalSelesai > 0): ?>
        <div class="hero-stat">
            <i class="bi bi-award"></i>
            Anda sudah menikmati <?= $totalSelesai ?> pesanan bersama kami. Terima kasih sudah jadi pelanggan setia!
        </div>
        <?php endif; ?>
    </div>

    <div class="menu-grid">
        <a href="menu.php" class="menu-card">
            <i class="bi bi-journal-bookmark"></i>
            <span class="label">Lihat Menu</span>
            <span class="desc">Jelajahi menu & kategori</span>
        </a>
        <a href="keranjang.php" class="menu-card">
            <i class="bi bi-cart3"></i>
            <span class="label">Keranjang</span>
            <span class="desc">Cek pesanan sebelum checkout</span>
        </a>
        <a href="pesanan.php" class="menu-card">
            <i class="bi bi-receipt"></i>
            <span class="label">Riwayat Pesanan</span>
            <span class="desc">Lihat semua pesanan Anda</span>
        </a>
        <a href="profile.php" class="menu-card">
            <i class="bi bi-person-circle"></i>
            <span class="label">Profil</span>
            <span class="desc">Kelola data akun Anda</span>
        </a>
    </div>

    <?php if (mysqli_num_rows($qRekomendasi) > 0): ?>
    <div class="section-title">
        <h2>Boleh Dicoba Hari Ini</h2>
        <a href="menu.php">Lihat semua menu <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="rekomendasi-grid">
        <?php while ($r = mysqli_fetch_assoc($qRekomendasi)): ?>
        <a href="menu.php" class="rekomendasi-card">
            <?php if (!empty($r['foto'])): ?>
                <img class="rekomendasi-foto" src="../../assets/img/<?= htmlspecialchars($r['foto']) ?>" alt="<?= htmlspecialchars($r['nama_menu']) ?>">
            <?php else: ?>
                <div class="rekomendasi-foto-fallback"><i class="bi bi-egg-fried"></i></div>
            <?php endif; ?>
            <div class="rekomendasi-body">
                <div class="rekomendasi-kategori"><?= htmlspecialchars($r['nama_kategori'] ?? 'Menu') ?></div>
                <div class="rekomendasi-nama"><?= htmlspecialchars($r['nama_menu']) ?></div>
                <div class="rekomendasi-harga">Rp <?= number_format((int) $r['harga'], 0, ',', '.') ?></div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <div class="section-title">
        <h2>Pesanan Aktif</h2>
    </div>

    <?php if (mysqli_num_rows($qOrder) > 0): ?>
        <div class="order-list">
            <?php while ($order = mysqli_fetch_assoc($qOrder)):
                $status = $order['status'];
                $label  = $statusLabel[$status] ?? $status;
                $color  = $statusColor[$status] ?? 'var(--muted)';
            ?>
                <div class="order-card">
                    <div>
                        <div class="kode">#<?= htmlspecialchars($order['kode_order']) ?></div>
                        <div class="meta">
                            <?= $order['tipe_order'] === 'dine_in' ? 'Makan di tempat' : 'Bawa pulang' ?>
                            <?php if ($order['tipe_order'] === 'dine_in' && !empty($order['nomor_meja'])): ?>
                                &middot; Meja <?= htmlspecialchars($order['nomor_meja']) ?>
                            <?php endif; ?>
                            &middot; Rp<?= number_format((int) $order['total_harga'], 0, ',', '.') ?>
                        </div>
                    </div>
                    <span class="status-badge" style="color: <?= $color ?>;"><?= htmlspecialchars($label) ?></span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-cup-hot"></i>
            <div>Belum ada pesanan aktif nih. Perut keroncongan? Yuk lihat-lihat menunya dulu.</div>
            <a href="menu.php"><i class="bi bi-journal-bookmark"></i> Mulai Pesan</a>
        </div>
    <?php endif; ?>

    <footer class="tagline">Dibuat dengan ❤️ untuk kamu yang lapar — RESTO UTM selalu siap melayani.</footer>

</div>

</body>
</html>
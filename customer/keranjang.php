<?php
require 'auth.php';
require '../admin/koneksi.php';

if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$id_user = (int) $_SESSION['id_user'];
$error   = null;

// -----------------------------------------------------------------
// Proses semua aksi POST dulu (Post/Redirect/Get) sebelum ada output
// -----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $id_menu = (int) $_POST['id_menu'];
        $jumlah  = max(1, (int) ($_POST['jumlah'] ?? 1));

        $qCek = mysqli_query($koneksi, "SELECT stok FROM menu WHERE id_menu='$id_menu'");
        $menu = mysqli_fetch_assoc($qCek);

        if ($menu) {
            $stok       = (int) $menu['stok'];
            $sekarang   = $_SESSION['keranjang'][$id_menu] ?? 0;
            $baru       = min($stok, $sekarang + $jumlah);
            if ($baru > 0) {
                $_SESSION['keranjang'][$id_menu] = $baru;
            }
        }

        $redirect = $_POST['redirect'] ?? 'menu.php';
        header("Location: $redirect" . (str_contains($redirect, '?') ? '&' : '?') . "added=1");
        exit;
    }

    if ($action === 'update') {
        $id_menu = (int) $_POST['id_menu'];
        $jumlah  = (int) ($_POST['jumlah'] ?? 0);

        if ($jumlah <= 0) {
            unset($_SESSION['keranjang'][$id_menu]);
        } else {
            $qCek = mysqli_query($koneksi, "SELECT stok FROM menu WHERE id_menu='$id_menu'");
            $menu = mysqli_fetch_assoc($qCek);
            $stok = $menu ? (int) $menu['stok'] : 0;
            $_SESSION['keranjang'][$id_menu] = min($jumlah, $stok);
        }

        header("Location: keranjang.php");
        exit;
    }

    if ($action === 'remove') {
        $id_menu = (int) $_POST['id_menu'];
        unset($_SESSION['keranjang'][$id_menu]);
        header("Location: keranjang.php");
        exit;
    }

    if ($action === 'checkout') {

        if (empty($_SESSION['keranjang'])) {
            header("Location: keranjang.php?error=kosong");
            exit;
        }

        $tipe_order  = $_POST['tipe_order'] === 'take_away' ? 'take_away' : 'dine_in';
        $nomor_meja  = $tipe_order === 'dine_in' ? mysqli_real_escape_string($koneksi, $_POST['nomor_meja'] ?? '') : '';
        $metode      = in_array($_POST['metode_bayar'] ?? '', ['qris', 'cash', 'ewallet']) ? $_POST['metode_bayar'] : 'cash';
        $catatan     = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');

        // Validasi ulang stok & hitung total dari data terbaru di database
        // (jangan percaya harga/stok yang mungkin sudah basi di session)
        $items = [];
        $total = 0;
        $stokKurang = false;

        foreach ($_SESSION['keranjang'] as $id_menu => $jumlah) {
            $id_menu = (int) $id_menu;
            $jumlah  = (int) $jumlah;

            $q = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu='$id_menu'");
            $m = mysqli_fetch_assoc($q);

            if (!$m || (int) $m['stok'] < $jumlah) {
                $stokKurang = true;
                continue;
            }

            $subtotal = (int) $m['harga'] * $jumlah;
            $total   += $subtotal;
            $items[]  = ['id_menu' => $id_menu, 'jumlah' => $jumlah, 'subtotal' => $subtotal];
        }

        if ($stokKurang) {
            header("Location: keranjang.php?error=stok");
            exit;
        }

        if (empty($items)) {
            header("Location: keranjang.php?error=kosong");
            exit;
        }

        $kode_order = 'ORD' . date('ymdHis') . rand(10, 99);

        mysqli_query($koneksi, "
            INSERT INTO orders (kode_order, tipe_order, status, total_harga, metode_bayar, nomor_meja, catatan, created_at, id_customer)
            VALUES (
                '$kode_order',
                '$tipe_order',
                'pending',
                '$total',
                '$metode',
                '$nomor_meja',
                '$catatan',
                NOW(),
                '$id_user'
            )
        ");

        $id_order = mysqli_insert_id($koneksi);

        foreach ($items as $it) {
            mysqli_query($koneksi, "
                INSERT INTO order_detail (id_order, id_menu, jumlah, subtotal)
                VALUES ('$id_order', '{$it['id_menu']}', '{$it['jumlah']}', '{$it['subtotal']}')
            ");

            mysqli_query($koneksi, "
                UPDATE menu SET stok = stok - {$it['jumlah']} WHERE id_menu = '{$it['id_menu']}'
            ");
        }

        mysqli_query($koneksi, "
            INSERT INTO pembayaran (id_order, metode, total_bayar, status, created_at)
            VALUES ('$id_order', '$metode', '$total', 'pending', NOW())
        ");

        $_SESSION['keranjang'] = [];

        header("Location: index.php?checkout=1");
        exit;
    }
}

// -----------------------------------------------------------------
// Tampilan keranjang
// -----------------------------------------------------------------
$items = [];
$total = 0;

if (!empty($_SESSION['keranjang'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['keranjang'])));
    $q = mysqli_query($koneksi, "SELECT * FROM menu WHERE id_menu IN ($ids)");
    while ($m = mysqli_fetch_assoc($q)) {
        $jumlah   = (int) $_SESSION['keranjang'][$m['id_menu']];
        $subtotal = (int) $m['harga'] * $jumlah;
        $total   += $subtotal;
        $items[]  = array_merge($m, ['jumlah' => $jumlah, 'subtotal' => $subtotal]);
    }
}

$errParam = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keranjang - RESTO UTM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
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

        * {
            box-sizing: border-box;
        }

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
            max-width: 760px;
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

        .cart-item {
            background: var(--panel);
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
            border: 1px solid rgba(164, 145, 154, 0.12);
        }

        .cart-item .foto,
        .cart-item .foto-fallback {
            width: 56px;
            height: 56px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .cart-item .foto-fallback {
            background: var(--panel-2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 1.3rem;
        }

        .cart-item .info {
            flex: 1;
        }

        .cart-item .nama {
            font-weight: 700;
            font-size: 0.95rem;
        }

        .cart-item .harga {
            color: var(--muted);
            font-size: 0.8rem;
        }

        .qty-form {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qty-form input[type="number"] {
            width: 52px;
            background: var(--bg-void);
            border: 1px solid rgba(164, 145, 154, 0.3);
            border-radius: 8px;
            color: var(--cream);
            padding: 7px;
            text-align: center;
            font-family: 'Manrope', sans-serif;
        }

        .qty-form button,
        .remove-form button {
            border: none;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
        }

        .qty-form button {
            background: var(--gold-soft);
            color: var(--panel-2);
        }

        .remove-form button {
            background: transparent;
            color: var(--danger);
            font-size: 1.1rem;
            padding: 6px;
        }

        .subtotal {
            font-weight: 700;
            font-size: 0.9rem;
            min-width: 90px;
            text-align: right;
        }

        .summary {
            background: var(--panel);
            border-radius: 16px;
            padding: 22px 24px;
            margin-top: 24px;
            border: 1px solid rgba(164, 145, 154, 0.12);
        }

        .summary .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(164, 145, 154, 0.15);
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

        .form-group select,
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            background: var(--bg-void);
            border: 1px solid rgba(164, 145, 154, 0.3);
            border-radius: 10px;
            color: var(--cream);
            padding: 10px 12px;
            font-family: 'Manrope', sans-serif;
            font-size: 0.9rem;
        }

        .radio-group {
            display: flex;
            gap: 10px;
        }

        .radio-group label {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px;
            border-radius: 10px;
            border: 1.5px solid rgba(164, 145, 154, 0.3);
            cursor: pointer;
            font-size: 0.85rem;
            color: var(--muted);
        }

        .radio-group input {
            accent-color: var(--gold-soft);
        }

        .radio-group input:checked+span {
            color: var(--cream);
        }

        .btn-checkout {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 0.98rem;
            color: var(--panel-2);
            background: linear-gradient(120deg, var(--gold), var(--gold-soft));
            cursor: pointer;
        }

        .empty-state {
            color: var(--muted);
            text-align: center;
            padding: 48px;
            background: var(--panel);
            border-radius: 16px;
        }

        .empty-state a {
            color: var(--gold-soft);
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="bi bi-egg-fried"></i> RESTO UTM</a>
        <div class="navbar-actions">
            <a href="menu.php"><i class="bi bi-arrow-left"></i> Kembali ke Menu</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Keranjang</h1>

        <?php if ($errParam === 'kosong'): ?>
        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> Keranjang masih kosong.</div>
        <?php elseif ($errParam === 'stok'): ?>
        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> Ada item yang stoknya tidak mencukupi.
            Silakan sesuaikan jumlahnya.</div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            Keranjang Anda masih kosong.<br>
            <a href="menu.php">Lihat menu &rarr;</a>
        </div>
        <?php else: ?>

        <?php foreach ($items as $it): ?>
        <div class="cart-item">
            <?php if (!empty($it['foto'])): ?>
            <img class="foto" src="../../assets/img/<?= htmlspecialchars($it['foto']) ?>" alt="">
            <?php else: ?>
            <div class="foto-fallback"><i class="bi bi-egg-fried"></i></div>
            <?php endif; ?>

            <div class="info">
                <div class="nama"><?= htmlspecialchars($it['nama_menu']) ?></div>
                <div class="harga">Rp<?= number_format((int) $it['harga'], 0, ',', '.') ?> / porsi</div>
            </div>

            <form class="qty-form" action="keranjang.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_menu" value="<?= $it['id_menu'] ?>">
                <input type="number" name="jumlah" value="<?= $it['jumlah'] ?>" min="1" max="<?= (int) $it['stok'] ?>">
                <button type="submit"><i class="bi bi-arrow-repeat"></i></button>
            </form>

            <div class="subtotal">Rp<?= number_format($it['subtotal'], 0, ',', '.') ?></div>

            <form class="remove-form" action="keranjang.php" method="POST">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="id_menu" value="<?= $it['id_menu'] ?>">
                <button type="submit" title="Hapus"><i class="bi bi-trash3"></i></button>
            </form>
        </div>
        <?php endforeach; ?>

        <div class="summary">
            <div class="total-row">
                <span>Total</span>
                <span>Rp<?= number_format($total, 0, ',', '.') ?></span>
            </div>

            <form action="keranjang.php" method="POST">
                <input type="hidden" name="action" value="checkout">

                <div class="form-group">
                    <label>Tipe Pesanan</label>
                    <div class="radio-group">
                        <label><input type="radio" name="tipe_order" value="dine_in" checked
                                onclick="document.getElementById('mejaGroup').style.display='block'"><span>Makan di
                                Tempat</span></label>
                        <label><input type="radio" name="tipe_order" value="take_away"
                                onclick="document.getElementById('mejaGroup').style.display='none'"><span>Bawa
                                Pulang</span></label>
                    </div>
                </div>

                <div class="form-group" id="mejaGroup">
                    <label>Nomor Meja</label>
                    <input type="text" name="nomor_meja" placeholder="Misal: 12">
                </div>

                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_bayar">
                        <option value="qris">QRIS</option>
                        <option value="cash">Cash</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Catatan (opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Contoh: tidak pedas"></textarea>
                </div>

                <button type="submit" class="btn-checkout"><i class="bi bi-bag-check"></i> Checkout</button>
            </form>
        </div>

        <?php endif; ?>
    </div>

</body>

</html>
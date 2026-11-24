<?php
require 'auth.php';
require '../admin/koneksi.php';

$id_kategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;

$qKategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

$sqlMenu = "
    SELECT menu.*, kategori.nama_kategori
    FROM menu
    LEFT JOIN kategori ON kategori.id_kategori = menu.id_kategori
";
if ($id_kategori > 0) {
    $sqlMenu .= " WHERE menu.id_kategori = $id_kategori";
}
$sqlMenu .= " ORDER BY menu.nama_menu ASC";
$qMenu = mysqli_query($koneksi, $sqlMenu);

$jumlahKeranjang = isset($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu - RESTO UTM</title>
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
            position: sticky;
            top: 0;
            z-index: 5;
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

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .navbar-actions a {
            color: var(--cream);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .cart-link {
            position: relative;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -14px;
            background: var(--wine-soft);
            color: var(--cream);
            font-size: 0.68rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 999px;
        }

        .container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 32px 24px 60px;
        }

        .page-title {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.6rem;
            margin: 0 0 20px;
        }

        .toast-success {
            background: rgba(92, 201, 154, 0.12);
            border: 1px solid rgba(92, 201, 154, 0.4);
            color: var(--success);
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .kategori-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 24px;
        }

        .kategori-tabs a {
            text-decoration: none;
            color: var(--muted);
            background: var(--panel);
            border: 1px solid rgba(164, 145, 154, 0.2);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
        }

        .kategori-tabs a.active,
        .kategori-tabs a:hover {
            color: var(--panel-2);
            background: var(--gold-soft);
            border-color: var(--gold-soft);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }

        .menu-item {
            background: var(--panel);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(164, 145, 154, 0.12);
            display: flex;
            flex-direction: column;
        }

        .menu-item .foto {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            background: var(--panel-2);
        }

        .menu-item .foto-fallback {
            width: 100%;
            aspect-ratio: 4/3;
            background: var(--panel-2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--muted);
        }

        .menu-item .body {
            padding: 14px 16px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .menu-item .kategori-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
        }

        .menu-item .nama {
            font-weight: 700;
            font-size: 1rem;
        }

        .menu-item .harga {
            color: var(--gold-soft);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .menu-item .stok-info {
            font-size: 0.76rem;
            color: var(--muted);
        }

        .menu-item .stok-info.habis {
            color: var(--danger);
        }

        .add-form {
            margin-top: auto;
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .add-form input[type="number"] {
            width: 56px;
            background: var(--bg-void);
            border: 1px solid rgba(164, 145, 154, 0.3);
            border-radius: 8px;
            color: var(--cream);
            padding: 8px;
            text-align: center;
            font-family: 'Manrope', sans-serif;
        }

        .add-form button {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 9px 10px;
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--panel-2);
            background: linear-gradient(120deg, var(--gold), var(--gold-soft));
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: transform 0.15s ease;
        }

        .add-form button:hover {
            transform: translateY(-1px);
        }

        .add-form button:disabled {
            background: rgba(164, 145, 154, 0.2);
            color: var(--muted);
            cursor: not-allowed;
            transform: none;
        }

        .empty-state {
            color: var(--muted);
            text-align: center;
            padding: 48px;
            background: var(--panel);
            border-radius: 16px;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="bi bi-egg-fried"></i> RESTO UTM</a>
        <div class="navbar-actions">
            <a href="index.php"><i class="bi bi-house"></i> Beranda</a>
            <a href="keranjang.php" class="cart-link">
                <i class="bi bi-cart3"></i> Keranjang
                <?php if ($jumlahKeranjang > 0): ?>
                <span class="cart-badge"><?= $jumlahKeranjang ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Menu</h1>

        <?php if (isset($_GET['added'])): ?>
        <div class="toast-success"><i class="bi bi-check-circle-fill"></i> Ditambahkan ke keranjang.</div>
        <?php endif; ?>

        <div class="kategori-tabs">
            <a href="menu.php" class="<?= $id_kategori === 0 ? 'active' : '' ?>">Semua</a>
            <?php while ($kat = mysqli_fetch_assoc($qKategori)): ?>
            <a href="menu.php?kategori=<?= $kat['id_kategori'] ?>"
                class="<?= $id_kategori === (int) $kat['id_kategori'] ? 'active' : '' ?>">
                <?= htmlspecialchars($kat['nama_kategori']) ?>
            </a>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($qMenu) > 0): ?>
        <div class="menu-grid">
            <?php while ($item = mysqli_fetch_assoc($qMenu)):
                $stok  = (int) $item['stok'];
                $habis = $stok <= 0;
            ?>
            <div class="menu-item">
                <?php if (!empty($item['foto'])): ?>
                <img class="foto" src="../../assets/img/<?= htmlspecialchars($item['foto']) ?>"
                    alt="<?= htmlspecialchars($item['nama_menu']) ?>">
                <?php else: ?>
                <div class="foto-fallback"><i class="bi bi-egg-fried"></i></div>
                <?php endif; ?>
                <div class="body">
                    <span
                        class="kategori-label"><?= htmlspecialchars($item['nama_kategori'] ?? 'Tanpa kategori') ?></span>
                    <span class="nama"><?= htmlspecialchars($item['nama_menu']) ?></span>
                    <span class="harga">Rp<?= number_format((int) $item['harga'], 0, ',', '.') ?></span>
                    <span class="stok-info <?= $habis ? 'habis' : '' ?>">
                        <?= $habis ? 'Stok habis' : 'Stok: ' . $stok ?>
                    </span>

                    <form class="add-form" action="keranjang.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id_menu" value="<?= $item['id_menu'] ?>">
                        <input type="hidden" name="redirect"
                            value="menu.php<?= $id_kategori ? '?kategori=' . $id_kategori : '' ?>">
                        <input type="number" name="jumlah" value="1" min="1" max="<?= $habis ? 1 : $stok ?>"
                            <?= $habis ? 'disabled' : '' ?>>
                        <button type="submit" <?= $habis ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus"></i> Tambah
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">Belum ada menu di kategori ini.</div>
        <?php endif; ?>
    </div>

</body>

</html>
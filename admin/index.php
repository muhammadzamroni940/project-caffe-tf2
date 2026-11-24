<?php
require __DIR__ . '/../session_boot.php';
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] === 'customer') {
    header("Location: ../customer/index.php");
    exit;
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$initial = strtoupper(substr($nama, 0, 1));

// Daftar halaman yang boleh diakses -> file di folder app/
$menuMap = [
    'home'     => ['file' => 'app/home.php',     'label' => 'Beranda',  'icon' => 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9z'],
    'kategori' => ['file' => 'app/kategori.php',  'label' => 'Kategori', 'icon' => 'M4 6h16M4 12h16M4 18h10'],
    'menu'     => ['file' => 'app/menu.php',      'label' => 'Menu',     'icon' => 'M4 19h16M4 15h16M4 11h10M4 7h6'],
    'order'    => ['file' => 'app/order.php',     'label' => 'Order',    'icon' => 'M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z'],
    'laporan'  => ['file' => 'app/laporan.php',   'label' => 'Laporan',  'icon' => 'M3 3v18h18M9 17V9m4 8V5m4 12v-6'],
    'user'     => ['file' => 'app/user.php',      'label' => 'User',     'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75'],
];

$page = $_GET['page'] ?? 'home';
if (!array_key_exists($page, $menuMap)) {
    $page = 'home';
}

// Export CSV harus ditangani sebelum ada output HTML apapun,
// supaya header() untuk download file bisa terkirim dengan benar.
if ($page === 'laporan' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    require 'koneksi.php';
    require 'app/laporan_export.php';
    exit;
}

$activeFile = $menuMap[$page]['file'];
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($menuMap[$page]['label']) ?> - RESTO UTM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-void: #170f19;
            --panel: #241726;
            --panel-2: #2f1b2f;
            --gold: #c8862a;
            --gold-soft: #e0a94f;
            --wine: #8a2846;
            --cream: #f5ead9;
            --muted: #a4919a;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            background: #f4f1ea;
            color: #2b2320;
        }

        .app-shell { display: flex; min-height: 100vh; }

        .sidebar {
            width: 240px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--panel), var(--panel-2));
            color: var(--cream);
            padding: 24px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 8px 20px;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .brand-mark {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: linear-gradient(140deg, var(--gold-soft), var(--gold));
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fraunces', serif; font-weight: 700; color: var(--wine);
        }
        .brand-name { font-family: 'Fraunces', serif; font-style: italic; font-weight: 700; font-size: 1.05rem; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 4px;
            transition: background 0.18s ease, color 0.18s ease;
        }
        .nav-item svg { width: 17px; height: 17px; flex-shrink: 0; }
        .nav-item:hover { background: rgba(255,255,255,0.06); color: var(--cream); }
        .nav-item.active {
            background: rgba(224,169,79,0.14);
            color: var(--gold-soft);
        }

        .sidebar-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .main { flex: 1; min-width: 0; }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 28px;
            background: #fff;
            border-bottom: 1px solid #eee;
        }
        .topbar h1 { font-size: 1.15rem; font-weight: 700; margin: 0; }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.86rem;
        }
        .avatar-sm {
            width: 32px; height: 32px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem; color: #fff;
            background: linear-gradient(140deg, var(--gold-soft), var(--gold));
        }

        .content { padding: 24px 28px; }
    </style>
</head>

<body>

    <div class="app-shell">

        <aside class="sidebar">
            <div class="brand">
                <span class="brand-mark">R</span>
                <span class="brand-name">RESTO UTM</span>
            </div>

            <nav>
                <?php foreach ($menuMap as $key => $item): ?>
                    <a href="index.php?page=<?= $key ?>" class="nav-item <?= $page === $key ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="<?= $item['icon'] ?>"></path>
                        </svg>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="logout.php" class="nav-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path>
                    </svg>
                    Keluar
                </a>
            </div>
        </aside>

        <div class="main">
            <div class="topbar">
                <h1><?= htmlspecialchars($menuMap[$page]['label']) ?></h1>
                <div class="user-chip">
                    <span class="avatar-sm"><?= htmlspecialchars($initial) ?></span>
                    <div>
                        <div style="font-weight:700;"><?= htmlspecialchars($nama) ?></div>
                        <div style="color:#999;font-size:0.78rem;"><?= htmlspecialchars($role) ?></div>
                    </div>
                </div>
            </div>

            <div class="content">
                <?php include $activeFile; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
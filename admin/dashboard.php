<?php
require __DIR__ . '/../session_boot.php';
session_start();

// Uncomment to protect this page once wired into your project:
// if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit();
// }

include 'koneksi.php';

/* =========================================================
   DATA LAYER
   Based on the actual schema:
   `orders`       -> id_order, kode_order, tipe_order, status, total_harga,
                     metode_bayar, nomor_meja, catatan, created_at,
                     id_pegawai, id_customer, waktu_diproses, waktu_selesai
   `order_detail` -> id_detail, id_order, id_menu, jumlah, subtotal
   `pembayaran`   -> id_pembayaran, id_order, metode, total_bayar, status, created_at
   I assumed a `menu` table with columns `id_menu`, `nama_menu` for the
   "menu terlaris" widget — rename those two if your table differs.
   ========================================================= */

$today = date('Y-m-d');

// Total orders hari ini
$r = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM `orders` WHERE DATE(created_at) = '$today'");
$totalOrdersHariIni = $r ? (int)(mysqli_fetch_assoc($r)['total'] ?? 0) : 0;

// Pendapatan hari ini (pembayaran lunas)
$r = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM pembayaran WHERE status = 'lunas' AND DATE(created_at) = '$today'");
$pendapatanHariIni = $r ? (int)(mysqli_fetch_assoc($r)['total'] ?? 0) : 0;

// Orders aktif (pending / diproses)
$r = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM `orders` WHERE status IN ('pending','diproses')");
$ordersAktif = $r ? (int)(mysqli_fetch_assoc($r)['total'] ?? 0) : 0;

// Orders selesai hari ini
$r = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM `orders` WHERE status = 'selesai' AND DATE(waktu_selesai) = '$today'");
$ordersSelesaiHariIni = $r ? (int)(mysqli_fetch_assoc($r)['total'] ?? 0) : 0;

// Rata-rata nilai orders hari ini
$rataRata = $totalOrdersHariIni > 0 ? round($pendapatanHariIni / $totalOrdersHariIni) : 0;

// Pendapatan 7 hari terakhir, untuk grafik
$revenueLabels = [];
$revenueValues = [];

for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $rq = mysqli_query($koneksi, "SELECT SUM(total_bayar) AS total FROM pembayaran WHERE status = 'lunas' AND DATE(created_at) = '$d'");
    $row = $rq ? mysqli_fetch_assoc($rq) : null;
    $revenueLabels[] = date('d M', strtotime($d));
    $revenueValues[] = (int)($row['total'] ?? 0);
}

// Sebaran status orders (untuk donut)
$statusCounts = [
    'pending'  => 0,
    'diproses' => 0,
    'dibayar'  => 0,
    'selesai'  => 0,
];
$rq = mysqli_query($koneksi, "SELECT status, COUNT(*) AS total FROM `orders` GROUP BY status");

if ($rq) {
    while ($row = mysqli_fetch_assoc($rq)) {
        if (isset($statusCounts[$row['status']])) {
            $statusCounts[$row['status']] = (int)$row['total'];
        }
    }
}

// Orders terbaru — untuk "ticket rail"
$recentOrders = [];

$rq = mysqli_query($koneksi,
    "SELECT o.*,
        (SELECT COUNT(*) FROM order_detail od WHERE od.id_order = o.id_order) AS jumlah_item
     FROM `orders` o
     ORDER BY o.created_at DESC
     LIMIT 6");
if ($rq) {
    while ($row = mysqli_fetch_assoc($rq)) {
        $recentOrders[] = $row;
    }
}

// Menu terlaris — sesuaikan nama_menu/id_menu jika kolom tabel menu Anda berbeda
$menuTerlaris = [];

$rq = @mysqli_query($koneksi, "SELECT m.nama_menu, SUM(od.jumlah) AS total_terjual
    FROM order_detail od
    JOIN menu m ON od.id_menu = m.id_menu
    GROUP BY od.id_menu
    ORDER BY total_terjual DESC
    LIMIT 5");

if ($rq) {
    while ($row = mysqli_fetch_assoc($rq)) {
        $menuTerlaris[] = $row;
    }
}

function rupiah($n) {
    return 'Rp' . number_format((float)$n, 0, ',', '.');
}

$statusLabel = [
    'pending'  => 'Menunggu',
    'diproses' => 'Diproses',
    'dibayar'  => 'Dibayar',
    'selesai'  => 'Selesai',
];

?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beranda - RESTO UTM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg-void: #170f19;
            --panel: #241726;
            --panel-2: #2f1b2f;
            --panel-3: #391f3b;
            --gold: #e3a83a;
            --gold-soft: #f0c674;
            --wine: #8a2846;
            --wine-soft: #b23a5c;
            --cream: #f5ead9;
            --muted: #a4919a;
            --danger: #e0587a;
            --success: #5cc99a;
            --info: #7aa7d9;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--bg-void);
            color: var(--cream);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            filter: blur(130px);
            opacity: .22;
            z-index: 0;
            pointer-events: none;
            animation: drift 22s ease-in-out infinite alternate;
        }

        body::before {
            background: var(--wine);
            top: -160px;
            left: -140px;
        }

        body::after {
            background: var(--gold);
            bottom: -180px;
            right: -120px;
            opacity: .12;
            animation-delay: -8s;
        }

        @keyframes drift {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(50px, 30px) scale(1.15);
            }
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            flex: 0 0 250px;
            background: var(--panel);
            padding: 26px 18px;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(227, 168, 58, .08);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 8px 28px;
        }

        .brand .mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(150deg, var(--gold-soft), var(--gold));
            color: var(--panel-2);
            font-weight: 800;
            font-family: 'Fraunces', serif;
            font-size: 1.15rem;
        }

        .brand .name {
            font-family: 'Fraunces', serif;
            font-weight: 700;
            font-style: italic;
            font-size: 1.15rem;
            color: var(--cream);
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 8px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: var(--muted);
            font-size: .92rem;
            font-weight: 600;
            transition: all .18s ease;
            position: relative;
        }

        .nav a i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
        }

        .nav a:hover {
            color: var(--cream);
            background: rgba(245, 234, 217, .04);
        }

        .nav a.active {
            color: var(--gold-soft);
            background: linear-gradient(90deg, rgba(227, 168, 58, .14), transparent);
        }

        .nav a.active::before {
            content: "";
            position: absolute;
            left: -18px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            border-radius: 3px;
            background: var(--gold);
        }

        .nav-spacer {
            flex: 1;
        }

        .nav .logout {
            color: var(--wine-soft);
        }

        .nav .logout:hover {
            color: var(--danger);
            background: rgba(224, 88, 122, .08);
        }

        .main {
            flex: 1;
            padding: 30px 36px 48px;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .topbar h1 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.6rem;
            margin: 0 0 4px;
        }

        .topbar .sub {
            color: var(--muted);
            font-size: .88rem;
        }

        .profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile .avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(150deg, var(--gold-soft), var(--gold));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--panel-2);
            font-weight: 800;
            font-family: 'Fraunces', serif;
        }

        .profile .who {
            line-height: 1.25;
        }

        .profile .who b {
            display: block;
            font-size: .9rem;
        }

        .profile .who span {
            color: var(--muted);
            font-size: .76rem;
        }

        .rise {
            opacity: 0;
            transform: translateY(14px);
            animation: riseIn .55s cubic-bezier(.2, .8, .2, 1) forwards;
        }

        @keyframes riseIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: var(--panel);
            border-radius: 18px;
            padding: 20px 22px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(245, 234, 217, .05);
        }

        .stat-card .icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            margin-bottom: 14px;
        }

        .stat-card .value {
            font-family: 'Fraunces', serif;
            font-weight: 700;
            font-size: 1.7rem;
            line-height: 1;
        }

        .stat-card .label {
            color: var(--muted);
            font-size: .8rem;
            margin-top: 6px;
        }

        .stat-card.gold .icon {
            background: rgba(227, 168, 58, .14);
            color: var(--gold-soft);
        }

        .stat-card.wine .icon {
            background: rgba(178, 58, 92, .16);
            color: var(--wine-soft);
        }

        .stat-card.info .icon {
            background: rgba(122, 167, 217, .14);
            color: var(--info);
        }

        .stat-card.success .icon {
            background: rgba(92, 201, 154, .14);
            color: var(--success);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .card {
            background: var(--panel);
            border-radius: 18px;
            padding: 22px 24px;
            border: 1px solid rgba(245, 234, 217, .05);
        }

        .card h3 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.05rem;
            margin: 0 0 2px;
        }

        .card .card-sub {
            color: var(--muted);
            font-size: .78rem;
            margin-bottom: 16px;
        }

        .chart-wrap {
            height: 230px;
            position: relative;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-top: 6px;
        }

        .donut-wrap {
            width: 120px;
            height: 120px;
            position: relative;
            flex: 0 0 120px;
        }

        .donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .donut-center b {
            font-family: 'Fraunces', serif;
            font-size: 1.3rem;
        }

        .donut-center span {
            color: var(--muted);
            font-size: .68rem;
        }

        .legend {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .legend .row {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: .83rem;
        }

        .legend .dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex: 0 0 9px;
        }

        .legend .row span.n {
            margin-left: auto;
            font-weight: 700;
            color: var(--cream);
        }

        .rail-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .rail {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .ticket {
            position: relative;
            background: var(--panel-2);
            border-radius: 12px;
            padding: 14px 18px 14px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px dashed rgba(227, 168, 58, .18);
        }

        .ticket::before,
        .ticket::after {
            content: "";
            position: absolute;
            left: -9px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: var(--bg-void);
        }

        .ticket::before {
            top: -9px;
        }

        .ticket::after {
            bottom: -9px;
        }

        .ticket .kode {
            font-family: 'Fraunces', serif;
            font-weight: 700;
            font-size: .98rem;
            color: var(--gold-soft);
            min-width: 92px;
        }

        .ticket .meta {
            flex: 1;
            min-width: 0;
        }

        .ticket .meta .top-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: var(--muted);
        }

        .ticket .meta .top-row b {
            color: var(--cream);
            font-weight: 600;
        }

        .ticket .amount {
            font-weight: 700;
            font-size: .9rem;
            white-space: nowrap;
        }

        .badge {
            font-size: .68rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .badge.pending {
            background: rgba(227, 168, 58, .16);
            color: var(--gold-soft);
        }

        .badge.diproses {
            background: rgba(122, 167, 217, .16);
            color: var(--info);
        }

        .badge.dibayar {
            background: rgba(92, 201, 154, .16);
            color: var(--success);
        }

        .badge.selesai {
            background: rgba(164, 145, 154, .16);
            color: var(--muted);
        }

        .empty-state {
            text-align: center;
            padding: 34px 10px;
            color: var(--muted);
            font-size: .86rem;
        }

        .empty-state i {
            font-size: 1.8rem;
            display: block;
            margin-bottom: 10px;
            color: rgba(245, 234, 217, .25);
        }

        .menu-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 4px;
        }

        .menu-list .row {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .menu-list .rank {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: var(--panel-3);
            color: var(--gold-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 800;
            flex: 0 0 26px;
        }

        .menu-list .name {
            flex: 1;
            font-size: .86rem;
        }

        .menu-list .qty {
            color: var(--muted);
            font-size: .78rem;
        }

        .bar-track {
            height: 5px;
            border-radius: 3px;
            background: rgba(245, 234, 217, .06);
            margin-top: 6px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--wine-soft), var(--gold));
        }

        @media (max-width:1180px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width:760px) {
            .shell {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                flex: none;
                flex-direction: row;
                overflow-x: auto;
                padding: 14px;
            }

            .brand {
                padding: 0 12px 0 0;
            }

            .nav {
                flex-direction: row;
            }

            .nav-spacer {
                display: none;
            }

            .main {
                padding: 22px 16px 40px;
            }

            .stat-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (prefers-reduced-motion:reduce) {

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="mark">R</div>
                <div class="name">RESTO UTM</div>
            </div>
            <nav class="nav">
                <a href="index.php?page=beranda" class="active"><i class="bi bi-house-door"></i> Beranda</a>
                <a href="index.php?page=kategori"><i class="bi bi-tags"></i> Kategori</a>
                <a href="index.php?page=menu"><i class="bi bi-egg-fried"></i> Menu</a>
                <a href="index.php?page=order"><i class="bi bi-receipt"></i> Order</a>
                <a href="index.php?page=laporan"><i class="bi bi-bar-chart-line"></i> Laporan</a>
                <a href="index.php?page=user"><i class="bi bi-people"></i> User</a>
                <div class="nav-spacer"></div>
                <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </nav>
        </aside>
        <main class="main">
            <div class="topbar rise">
                <div>
                    <h1>Selamat datang
                        kembali<?php echo isset($_SESSION['nama']) ? ', ' . htmlspecialchars($_SESSION['nama']) : ''; ?>
                    </h1>
                    <div class="sub"><?php echo date('l, d F Y'); ?> &middot; Berikut ringkasan dapur hari ini</div>
                </div>
                <div class="profile">
                    <div class="who">
                        <b><?php echo isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Admin'; ?></b>
                        <span><?php echo isset($_SESSION['role']) ? htmlspecialchars($_SESSION['role']) : 'admin'; ?></span>
                    </div>
                    <div class="avatar">
                        <?php echo isset($_SESSION['nama']) ? strtoupper(substr($_SESSION['nama'], 0, 1)) : 'A'; ?>
                    </div>
                </div>
            </div>

            <div class="stat-grid">
                <div class="stat-card gold rise" style="animation-delay:.05s">
                    <div class="icon"><i class="bi bi-receipt-cutoff"></i></div>
                    <div class="value" data-count="<?php echo $totalOrdersHariIni; ?>">0</div>
                    <div class="label">Orders hari ini</div>
                </div>
                <div class="stat-card success rise" style="animation-delay:.1s">
                    <div class="icon"><i class="bi bi-cash-coin"></i></div>
                    <div class="value" data-money="<?php echo $pendapatanHariIni; ?>">Rp0</div>
                    <div class="label">Pendapatan hari ini</div>
                </div>
                <div class="stat-card wine rise" style="animation-delay:.15s">
                    <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                    <div class="value" data-count="<?php echo $ordersAktif; ?>">0</div>
                    <div class="label">Orders masih berjalan</div>
                </div>
                <div class="stat-card info rise" style="animation-delay:.2s">
                    <div class="icon"><i class="bi bi-graph-up"></i></div>
                    <div class="value" data-money="<?php echo $rataRata; ?>">Rp0</div>
                    <div class="label">Rata-rata nilai orders</div>
                </div>
            </div>

            <div class="content-grid">
                <div class="card rise" style="animation-delay:.25s">
                    <h3>Pendapatan 7 hari terakhir</h3>
                    <div class="card-sub">Dari pembayaran berstatus lunas</div>
                    <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
                </div>

                <div class="card rise" style="animation-delay:.3s">
                    <h3>Sebaran status orders</h3>
                    <div class="card-sub">Seluruh orders tercatat</div>
                    <div class="status-row">
                        <div class="donut-wrap">
                            <canvas id="statusDonut"></canvas>
                            <div class="donut-center"><b><?php echo array_sum($statusCounts); ?></b><span>total
                                    orders</span></div>
                        </div>
                        <div class="legend">
                            <div class="row"><span class="dot" style="background:var(--gold-soft)"></span> Menunggu
                                <span class="n"><?php echo $statusCounts['pending']; ?></span></div>
                            <div class="row"><span class="dot" style="background:var(--info)"></span> Diproses <span
                                    class="n"><?php echo $statusCounts['diproses']; ?></span></div>
                            <div class="row"><span class="dot" style="background:var(--success)"></span> Dibayar <span
                                    class="n"><?php echo $statusCounts['dibayar']; ?></span></div>
                            <div class="row"><span class="dot" style="background:var(--muted)"></span> Selesai <span
                                    class="n"><?php echo $statusCounts['selesai']; ?></span></div>
                        </div>
                    </div>
                </div>

                <div class="card rise" style="grid-column:1;animation-delay:.35s">
                    <div class="rail-title">
                        <div>
                            <h3 style="margin-bottom:2px;">Orders terbaru</h3>
                            <div class="card-sub" style="margin-bottom:0;">6 orders paling baru masuk</div>
                        </div>
                        <a href="index.php?page=order"
                            style="font-size:.8rem;color:var(--gold-soft);font-weight:700;">Lihat semua &rarr;</a>
                    </div>
                    <?php if (empty($recentOrders)): ?>
                    <div class="empty-state"><i class="bi bi-inbox"></i>Belum ada orders masuk.</div>
                    <?php else: ?>
                    <div class="rail">
                        <?php foreach ($recentOrders as $o):
    $status = $o['status'] ?? 'pending';
    $label = $statusLabel[$status] ?? $status;
?>
                        <div class="ticket">
                            <div class="kode"><?php echo htmlspecialchars($o['kode_order']); ?></div>
                            <div class="meta">
                                <div class="top-row">
                                    <b>Meja <?php echo htmlspecialchars($o['nomor_meja'] ?? '-'); ?></b> &middot;
                                    <?php echo (int)($o['jumlah_item'] ?? 0); ?> item &middot;
                                    <?php echo $o['tipe_order'] === 'take_away' ? 'Bawa pulang' : 'Makan di tempat'; ?>
                                </div>
                            </div>
                            <span
                                class="badge <?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($label); ?></span>
                            <div class="amount"><?php echo rupiah($o['total_harga']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card rise" style="animation-delay:.4s">
                    <h3>Menu terlaris</h3>
                    <div class="card-sub">Berdasarkan jumlah terjual</div>
                    <?php if (empty($menuTerlaris)): ?>
                    <div class="empty-state"><i class="bi bi-egg-fried"></i>Belum ada data penjualan menu.</div>
                    <?php else:
    $maxQty = max(array_column($menuTerlaris, 'total_terjual'));
    $rank = 1;
?>
                    <div class="menu-list">
                        <?php foreach ($menuTerlaris as $m): ?>
                        <div>
                            <div class="row">
                                <div class="rank"><?php echo $rank++; ?></div>
                                <div class="name"><?php echo htmlspecialchars($m['nama_menu']); ?></div>
                                <div class="qty"><?php echo (int)$m['total_terjual']; ?>x</div>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill"
                                    style="width:<?php echo $maxQty > 0 ? round(($m['total_terjual'] / $maxQty) * 100) : 0; ?>%">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count, 10) || 0;
            let cur = 0;
            const step = Math.max(1, Math.ceil(target / 40));
            const t = setInterval(() => {
                cur += step;
                if (cur >= target) {
                    cur = target;
                    clearInterval(t);
                }
                el.textContent = cur.toLocaleString('id-ID');
            }, 20);
        });

        document.querySelectorAll('[data-money]').forEach(el => {
            const target = parseInt(el.dataset.money, 10) || 0;
            let cur = 0;
            const step = Math.max(1, Math.ceil(target / 40));
            const t = setInterval(() => {
                cur += step;
                if (cur >= target) {
                    cur = target;
                    clearInterval(t);
                }
                el.textContent = 'Rp' + cur.toLocaleString('id-ID');
            }, 20);
        });

        const revCtx = document.getElementById('revenueChart').getContext('2d');
        const gradient = revCtx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(227,168,58,0.35)');
        gradient.addColorStop(1, 'rgba(227,168,58,0)');

        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: < ? php echo json_encode($revenueLabels); ? > ,
                datasets : [{
                    data: < ? php echo json_encode($revenueValues); ? > ,
                    borderColor : '#e3a83a',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#f0c674',
                    pointBorderColor: '#170f19',
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#a4919a',
                            font: {
                                family: 'Manrope',
                                size: 11
                            }
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(245,234,217,0.06)'
                        },
                        ticks: {
                            color: '#a4919a',
                            font: {
                                family: 'Manrope',
                                size: 11
                            },
                            callback: v => 'Rp' + (v >= 1000 ? (v / 1000) + 'k' : v)
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('statusDonut').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Menunggu', 'Diproses', 'Dibayar', 'Selesai'],
                datasets: [{
                    data: < ? php echo json_encode(array_values($statusCounts)); ? > ,
                    backgroundColor : ['#f0c674', '#7aa7d9', '#5cc99a', '#a4919a'],
                    borderColor: '#241726',
                    borderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            }
        });
    </script>
</body>

</html>
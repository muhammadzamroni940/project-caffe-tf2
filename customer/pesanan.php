<?php
require 'auth.php';
require '../admin/koneksi.php';

$id_user = (int) $_SESSION['id_user'];

$qOrder = mysqli_query($koneksi, "
    SELECT orders.*, pembayaran.status AS status_bayar, pembayaran.metode AS metode_bayar_final
    FROM orders
    LEFT JOIN pembayaran ON pembayaran.id_order = orders.id_order
    WHERE orders.id_customer = '$id_user'
    ORDER BY orders.created_at DESC
");

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
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Saya - RESTO UTM</title>
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

        .order-card {
            background: var(--panel);
            border-radius: 16px;
            border: 1px solid rgba(164, 145, 154, 0.12);
            margin-bottom: 14px;
            overflow: hidden;
        }

        .order-card summary {
            list-style: none;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            cursor: pointer;
        }

        .order-card summary::-webkit-details-marker {
            display: none;
        }

        .order-card summary .left .kode {
            font-weight: 700;
            font-size: 0.95rem;
        }

        .order-card summary .left .meta {
            color: var(--muted);
            font-size: 0.78rem;
            margin-top: 3px;
        }

        .order-card summary .right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 999px;
            background: rgba(245, 234, 217, 0.08);
            white-space: nowrap;
        }

        .chevron {
            color: var(--muted);
            transition: transform 0.2s ease;
        }

        .order-card[open] .chevron {
            transform: rotate(180deg);
        }

        .order-detail-list {
            padding: 0 20px 18px;
            border-top: 1px solid rgba(164, 145, 154, 0.12);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(164, 145, 154, 0.15);
        }

        .detail-row:last-of-type {
            border-bottom: none;
        }

        .detail-row .qty {
            color: var(--muted);
            margin-left: 6px;
        }

        .detail-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            font-size: 0.9rem;
            padding-top: 12px;
        }

        .pay-info {
            font-size: 0.78rem;
            color: var(--muted);
            padding-top: 8px;
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
            <a href="index.php"><i class="bi bi-arrow-left"></i> Beranda</a>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">Pesanan Saya</h1>

        <?php if (mysqli_num_rows($qOrder) > 0): ?>

        <?php while ($order = mysqli_fetch_assoc($qOrder)):
            $status = $order['status'];
            $label  = $statusLabel[$status] ?? $status;
            $color  = $statusColor[$status] ?? 'var(--muted)';

            $qDetail = mysqli_query($koneksi, "
                SELECT order_detail.*, menu.nama_menu
                FROM order_detail
                LEFT JOIN menu ON menu.id_menu = order_detail.id_menu
                WHERE order_detail.id_order = '{$order['id_order']}'
            ");
        ?>
        <details class="order-card">
            <summary>
                <div class="left">
                    <div class="kode">#<?= htmlspecialchars($order['kode_order']) ?></div>
                    <div class="meta">
                        <?= date('d M Y, H:i', strtotime($order['created_at'])) ?>
                        &middot; <?= $order['tipe_order'] === 'dine_in' ? 'Makan di tempat' : 'Bawa pulang' ?>
                        <?php if ($order['tipe_order'] === 'dine_in' && !empty($order['nomor_meja'])): ?>
                        &middot; Meja <?= htmlspecialchars($order['nomor_meja']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="right">
                    <span class="status-badge" style="color: <?= $color ?>;"><?= htmlspecialchars($label) ?></span>
                    <i class="bi bi-chevron-down chevron"></i>
                </div>
            </summary>

            <div class="order-detail-list">
                <?php while ($d = mysqli_fetch_assoc($qDetail)): ?>
                <div class="detail-row">
                    <span><?= htmlspecialchars($d['nama_menu'] ?? 'Menu tidak tersedia') ?><span
                            class="qty">x<?= $d['jumlah'] ?></span></span>
                    <span>Rp<?= number_format((int) $d['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php endwhile; ?>

                <div class="detail-total">
                    <span>Total</span>
                    <span>Rp<?= number_format((int) $order['total_harga'], 0, ',', '.') ?></span>
                </div>

                <div class="pay-info">
                    Pembayaran: <?= strtoupper($order['metode_bayar_final'] ?? $order['metode_bayar'] ?? '-') ?>
                    &middot; Status: <?= ucfirst($order['status_bayar'] ?? 'pending') ?>
                    <?php if (!empty($order['catatan'])): ?>
                    <br>Catatan: <?= htmlspecialchars($order['catatan']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </details>
        <?php endwhile; ?>

        <?php else: ?>
        <div class="empty-state">
            Belum ada riwayat pesanan.<br>
            <a href="menu.php">Mulai pesan &rarr;</a>
        </div>
        <?php endif; ?>
    </div>

</body>

</html>
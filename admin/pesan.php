<?php
session_start();

require 'auth.php';
cek_role(['customer']); // hanya role customer yang boleh membuka halaman ini

include 'koneksi.php';

$nama        = $_SESSION['nama'];
$id_customer = $_SESSION['id_user'];

$errorMsg   = '';
$successMsg = '';

/* =========================================================
   PROSES PEMESANAN (POST)
   Asumsi tabel `menu`: id_menu, nama_menu, harga
   (ubah nama kolom di query bawah jika skema tabelmu berbeda)
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buat_pesanan'])) {

    $items        = json_decode($_POST['items_json'] ?? '[]', true) ?: [];
    $tipe_order   = in_array($_POST['tipe_order'] ?? '', ['dine_in', 'take_away']) ? $_POST['tipe_order'] : 'dine_in';
    $nomor_meja   = trim($_POST['nomor_meja'] ?? '');
    $catatan      = trim($_POST['catatan'] ?? '');
    $metode_bayar = $_POST['metode_bayar'] ?? 'cash';

    if (empty($items)) {
        $errorMsg = 'Keranjang masih kosong, silakan pilih menu dulu.';
    } else {
        $total      = 0;
        $validItems = [];

        // Validasi ulang harga dari DB (jangan percaya harga dari client)
        foreach ($items as $it) {
            $id_menu = (int)($it['id_menu'] ?? 0);
            $jumlah  = (int)($it['jumlah'] ?? 0);
            if ($id_menu <= 0 || $jumlah <= 0) continue;

            $stmt = mysqli_prepare($koneksi, "SELECT id_menu, harga FROM menu WHERE id_menu = ?");
            mysqli_stmt_bind_param($stmt, "i", $id_menu);
            mysqli_stmt_execute($stmt);
            $menuRow = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            if (!$menuRow) continue;

            $subtotal     = (float)$menuRow['harga'] * $jumlah;
            $total       += $subtotal;
            $validItems[] = ['id_menu' => $id_menu, 'jumlah' => $jumlah, 'subtotal' => $subtotal];
        }

        if (empty($validItems)) {
            $errorMsg = 'Menu yang dipilih tidak valid.';
        } else {
            $kode_order = 'ORD' . date('ymdHis') . rand(10, 99);

            $stmt = mysqli_prepare($koneksi, "
                INSERT INTO `orders`
                    (kode_order, tipe_order, status, total_harga, metode_bayar, nomor_meja, catatan, created_at, id_customer)
                VALUES
                    (?, ?, 'pending', ?, ?, ?, ?, NOW(), ?)
            ");
            mysqli_stmt_bind_param(
                $stmt,
                "ssdsssi",
                $kode_order,
                $tipe_order,
                $total,
                $metode_bayar,
                $nomor_meja,
                $catatan,
                $id_customer
            );

            if (mysqli_stmt_execute($stmt)) {
                $id_order = mysqli_insert_id($koneksi);

                $stmtDetail = mysqli_prepare($koneksi, "
                    INSERT INTO order_detail (id_order, id_menu, jumlah, subtotal)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($validItems as $vi) {
                    mysqli_stmt_bind_param(
                        $stmtDetail,
                        "iiid",
                        $id_order,
                        $vi['id_menu'],
                        $vi['jumlah'],
                        $vi['subtotal']
                    );
                    mysqli_stmt_execute($stmtDetail);
                }

                $successMsg = "Pesanan berhasil dibuat! Kode order: $kode_order";
            } else {
                $errorMsg = 'Gagal menyimpan pesanan, coba lagi.';
            }
        }
    }
}

/* =========================================================
   DATA MENU (untuk ditampilkan)
   ========================================================= */
$menuList = [];
$rq = mysqli_query($koneksi, "SELECT id_menu, nama_menu, harga FROM menu ORDER BY nama_menu ASC");
if ($rq) {
    while ($row = mysqli_fetch_assoc($rq)) {
        $menuList[] = $row;
    }
}

/* =========================================================
   RIWAYAT PESANAN MILIK CUSTOMER INI SAJA
   ========================================================= */
$myOrders = [];
$stmt = mysqli_prepare($koneksi, "
    SELECT o.*,
        (SELECT COUNT(*) FROM order_detail od WHERE od.id_order = o.id_order) AS jumlah_item
    FROM `orders` o
    WHERE o.id_customer = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");
mysqli_stmt_bind_param($stmt, "i", $id_customer);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res)) {
    $myOrders[] = $row;
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
    <title>Pesan Menu - RESTO UTM</title>
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

        a {
            color: inherit;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 32px;
            border-bottom: 1px solid rgba(227, 168, 58, .1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand .mark {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(150deg, var(--gold-soft), var(--gold));
            color: var(--panel-2);
            font-weight: 800;
            font-family: 'Fraunces', serif;
        }

        .brand .name {
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: .88rem;
        }

        .user-chip .logout {
            color: var(--wine-soft);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-chip .logout:hover {
            color: var(--danger);
        }

        .wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 28px 24px 64px;
            display: grid;
            grid-template-columns: 1.6fr 1fr;
            gap: 24px;
        }

        @media (max-width: 860px) {
            .wrap {
                grid-template-columns: 1fr;
            }
        }

        h2 {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            margin: 0 0 16px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: .9rem;
        }

        .alert.error {
            background: rgba(224, 88, 122, .14);
            border: 1px solid rgba(224, 88, 122, .4);
            color: var(--danger);
        }

        .alert.success {
            background: rgba(92, 201, 154, .14);
            border: 1px solid rgba(92, 201, 154, .4);
            color: var(--success);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }

        .menu-card {
            background: var(--panel);
            border-radius: 14px;
            padding: 16px;
            border: 1px solid rgba(227, 168, 58, .08);
        }

        .menu-card .nama {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .menu-card .harga {
            color: var(--gold-soft);
            font-weight: 700;
            font-size: .92rem;
            margin-bottom: 12px;
        }

        .qty-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            background: rgba(245, 234, 217, .08);
            color: var(--cream);
            font-weight: 700;
            font-size: 1rem;
        }

        .qty-btn:hover {
            background: rgba(245, 234, 217, .16);
        }

        .qty-val {
            min-width: 24px;
            text-align: center;
            font-weight: 700;
        }

        .cart {
            background: var(--panel);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(227, 168, 58, .08);
            align-self: start;
            position: sticky;
            top: 24px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            font-size: .88rem;
            padding: 6px 0;
            border-bottom: 1px solid rgba(245, 234, 217, .06);
        }

        .cart-empty {
            color: var(--muted);
            font-size: .88rem;
            padding: 12px 0;
        }

        .cart-total {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(245, 234, 217, .1);
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .field select,
        .field input,
        .field textarea {
            width: 100%;
            background: var(--panel-2);
            border: 1px solid rgba(245, 234, 217, .1);
            border-radius: 8px;
            color: var(--cream);
            padding: 9px 12px;
            font-family: 'Manrope', sans-serif;
            font-size: .9rem;
        }

        .field textarea {
            resize: vertical;
            min-height: 60px;
        }

        .btn-submit {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 13px;
            margin-top: 8px;
            font-weight: 700;
            background: linear-gradient(120deg, var(--gold), var(--gold-soft));
            color: var(--panel-2);
            cursor: pointer;
        }

        .btn-submit:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .history {
            margin-top: 12px;
        }

        .ticket {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--panel);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 8px;
            border: 1px solid rgba(227, 168, 58, .08);
            font-size: .86rem;
        }

        .badge {
            padding: 3px 10px;
            border-radius: 999px;
            font-size: .74rem;
            font-weight: 700;
        }

        .badge.pending {
            background: rgba(240, 198, 116, .18);
            color: var(--gold-soft);
        }

        .badge.diproses {
            background: rgba(122, 167, 217, .18);
            color: #7aa7d9;
        }

        .badge.dibayar {
            background: rgba(92, 201, 154, .18);
            color: var(--success);
        }

        .badge.selesai {
            background: rgba(164, 145, 154, .18);
            color: var(--muted);
        }
    </style>
</head>

<body>

    <div class="topbar">
        <div class="brand">
            <span class="mark">R</span>
            <span class="name">RESTO UTM</span>
        </div>
        <div class="user-chip">
            <span><?= htmlspecialchars($nama) ?></span>
            <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </div>

    <div class="wrap">
        <div>
            <h2>Pilih Menu</h2>

            <?php if ($errorMsg): ?>
            <div class="alert error"><?= htmlspecialchars($errorMsg) ?></div>
            <?php endif; ?>
            <?php if ($successMsg): ?>
            <div class="alert success"><?= htmlspecialchars($successMsg) ?></div>
            <?php endif; ?>

            <?php if (empty($menuList)): ?>
            <p style="color:var(--muted)">Belum ada menu tersedia.</p>
            <?php else: ?>
            <div class="menu-grid" id="menuGrid">
                <?php foreach ($menuList as $m): ?>
                <div class="menu-card" data-id="<?= (int)$m['id_menu'] ?>"
                    data-nama="<?= htmlspecialchars($m['nama_menu']) ?>" data-harga="<?= (float)$m['harga'] ?>">
                    <div class="nama"><?= htmlspecialchars($m['nama_menu']) ?></div>
                    <div class="harga"><?= rupiah($m['harga']) ?></div>
                    <div class="qty-row">
                        <button type="button" class="qty-btn minus">-</button>
                        <span class="qty-val">0</span>
                        <button type="button" class="qty-btn plus">+</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h2 style="margin-top:32px;">Pesanan Saya</h2>
            <div class="history">
                <?php if (empty($myOrders)): ?>
                <p style="color:var(--muted)">Belum ada riwayat pesanan.</p>
                <?php else: foreach ($myOrders as $o):
                $status = $o['status'] ?? 'pending';
                $label  = $statusLabel[$status] ?? $status;
            ?>
                <div class="ticket">
                    <div>
                        <b><?= htmlspecialchars($o['kode_order']) ?></b>
                        &middot; <?= (int)($o['jumlah_item'] ?? 0) ?> item
                        &middot; <?= rupiah($o['total_harga']) ?>
                    </div>
                    <span class="badge <?= htmlspecialchars($status) ?>"><?= htmlspecialchars($label) ?></span>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <div class="cart">
            <h2>Keranjang</h2>
            <div id="cartItems">
                <div class="cart-empty">Belum ada menu dipilih.</div>
            </div>
            <div class="cart-total"><span>Total</span><span id="cartTotal">Rp0</span></div>

            <form method="POST" id="orderForm">
                <input type="hidden" name="items_json" id="itemsJson">
                <input type="hidden" name="buat_pesanan" value="1">

                <div class="field">
                    <label>Tipe Order</label>
                    <select name="tipe_order">
                        <option value="dine_in">Makan di tempat</option>
                        <option value="take_away">Bawa pulang</option>
                    </select>
                </div>
                <div class="field">
                    <label>Nomor Meja (jika makan di tempat)</label>
                    <input type="text" name="nomor_meja" placeholder="Contoh: 5">
                </div>
                <div class="field">
                    <label>Metode Bayar</label>
                    <select name="metode_bayar">
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div class="field">
                    <label>Catatan (opsional)</label>
                    <textarea name="catatan" placeholder="Contoh: tidak pedas"></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn" disabled>Buat Pesanan</button>
            </form>
        </div>
    </div>

    <script>
        const cart = {}; // { id_menu: { nama, harga, jumlah } }

        function rupiahFmt(n) {
            return 'Rp' + Math.round(n).toLocaleString('id-ID');
        }

        function renderCart() {
            const cartItemsEl = document.getElementById('cartItems');
            const ids = Object.keys(cart).filter(id => cart[id].jumlah > 0);

            if (ids.length === 0) {
                cartItemsEl.innerHTML = '<div class="cart-empty">Belum ada menu dipilih.</div>';
            } else {
                cartItemsEl.innerHTML = ids.map(id => {
                    const it = cart[id];
                    return `<div class="cart-item"><span>${it.nama} x${it.jumlah}</span><span>${rupiahFmt(it.harga * it.jumlah)}</span></div>`;
                }).join('');
            }

            const total = ids.reduce((sum, id) => sum + cart[id].harga * cart[id].jumlah, 0);
            document.getElementById('cartTotal').textContent = rupiahFmt(total);
            document.getElementById('submitBtn').disabled = ids.length === 0;

            const itemsPayload = ids.map(id => ({
                id_menu: parseInt(id, 10),
                jumlah: cart[id].jumlah
            }));
            document.getElementById('itemsJson').value = JSON.stringify(itemsPayload);
        }

        document.querySelectorAll('.menu-card').forEach(card => {
            const id = card.dataset.id;
            cart[id] = {
                nama: card.dataset.nama,
                harga: parseFloat(card.dataset.harga),
                jumlah: 0
            };

            const qtyEl = card.querySelector('.qty-val');
            card.querySelector('.plus').addEventListener('click', () => {
                cart[id].jumlah++;
                qtyEl.textContent = cart[id].jumlah;
                renderCart();
            });
            card.querySelector('.minus').addEventListener('click', () => {
                if (cart[id].jumlah > 0) cart[id].jumlah--;
                qtyEl.textContent = cart[id].jumlah;
                renderCart();
            });
        });
    </script>

</body>

</html>
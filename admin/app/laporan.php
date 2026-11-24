<?php
include 'koneksi.php';

// ================== FILTER ==================
$tgl_dari = isset($_GET['tgl_dari']) && $_GET['tgl_dari'] !== '' ? $_GET['tgl_dari'] : date('Y-m-01');
$tgl_sampai = isset($_GET['tgl_sampai']) && $_GET['tgl_sampai'] !== '' ? $_GET['tgl_sampai'] : date('Y-m-d');
$f_metode = isset($_GET['metode']) ? $_GET['metode'] : '';
$f_status = isset($_GET['status']) ? $_GET['status'] : '';

// Build WHERE clause aman (pakai prepared statement)
$where = "WHERE DATE(p.created_at) BETWEEN ? AND ?";
$types = "ss";
$params = [$tgl_dari, $tgl_sampai];

if ($f_metode !== '') {
    $where .= " AND p.metode = ?";
    $types .= "s";
    $params[] = $f_metode;
}
if ($f_status !== '') {
    $where .= " AND p.status = ?";
    $types .= "s";
    $params[] = $f_status;
}

// ================== SUMMARY ==================
$sqlSummary = "SELECT
                    COUNT(*) AS total_transaksi,
                    COALESCE(SUM(CASE WHEN p.status = 'lunas' THEN p.total_bayar ELSE 0 END), 0) AS total_pendapatan,
                    COALESCE(SUM(CASE WHEN p.status = 'lunas' THEN 1 ELSE 0 END), 0) AS jumlah_lunas,
                    COALESCE(SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END), 0) AS jumlah_pending
               FROM pembayaran p
               $where";

$stmt = mysqli_prepare($koneksi, $sqlSummary);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$summary = mysqli_stmt_get_result($stmt)->fetch_assoc();

// ================== DATA PER METODE (untuk grafik ringkas) ==================
$sqlMetode = "SELECT p.metode, COUNT(*) AS jumlah, COALESCE(SUM(p.total_bayar),0) AS total
              FROM pembayaran p
              $where AND p.status = 'lunas'
              GROUP BY p.metode";
$stmt2 = mysqli_prepare($koneksi, $sqlMetode);
mysqli_stmt_bind_param($stmt2, $types, ...$params);
mysqli_stmt_execute($stmt2);
$resMetode = mysqli_stmt_get_result($stmt2);
$metodeData = [];
while ($row = mysqli_fetch_assoc($resMetode)) {
    $metodeData[$row['metode']] = $row;
}
$metodeList = ['qris', 'cash', 'ewallet'];
$maxMetodeTotal = 0;
foreach ($metodeList as $m) {
    $t = $metodeData[$m]['total'] ?? 0;
    if ($t > $maxMetodeTotal) $maxMetodeTotal = $t;
}

// ================== DATA TABEL (join ke orders) ==================
$sqlData = "SELECT p.*, o.kode_order, o.tipe_order, o.nomor_meja
            FROM pembayaran p
            LEFT JOIN orders o ON p.id_order = o.id_order
            $where
            ORDER BY p.created_at DESC";
$stmt3 = mysqli_prepare($koneksi, $sqlData);
mysqli_stmt_bind_param($stmt3, $types, ...$params);
mysqli_stmt_execute($stmt3);
$dataResult = mysqli_stmt_get_result($stmt3);
?>

<style>
    .laporan-panel {
        --lp-gold: #c8862a;
        --lp-gold-soft: #e0a94f;
        --lp-blue: #2f6fb0;
        --lp-amber: #b3760f;
        --lp-green: #2f9e6b;
        --lp-grey: #7a7a7a;
        --lp-danger: #d0475c;
    }

    .laporan-panel .card { border: none; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .laporan-panel .card-body { padding: 1.75rem; }
    .laporan-panel .panel-title { font-weight: 700; margin: 0; }
    .laporan-panel .panel-title span { color: #8a8a8a; font-weight: 400; font-size: 0.85rem; display: block; margin-top: 2px; }

    /* ---- Summary Cards ---- */
    .laporan-panel .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 1.75rem;
    }
    @media (max-width: 900px) { .laporan-panel .summary-grid { grid-template-columns: repeat(2, 1fr); } }

    .laporan-panel .summary-card {
        border-radius: 14px;
        padding: 18px 20px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .laporan-panel .summary-card .label { font-size: 0.78rem; opacity: 0.85; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
    .laporan-panel .summary-card .value { font-size: 1.55rem; font-weight: 800; margin-top: 4px; }
    .laporan-panel .summary-card.pendapatan { background: linear-gradient(135deg, var(--lp-gold), var(--lp-gold-soft)); }
    .laporan-panel .summary-card.transaksi { background: linear-gradient(135deg, #3a3a3a, #5a5a5a); }
    .laporan-panel .summary-card.lunas     { background: linear-gradient(135deg, #2f9e6b, #57c690); }
    .laporan-panel .summary-card.pending   { background: linear-gradient(135deg, #b3760f, #d9a01f); }

    /* ---- Filter Bar ---- */
    .laporan-panel .filter-bar {
        background: #faf7f2;
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 1.5rem;
    }
    .laporan-panel .filter-bar label { font-size: 0.76rem; font-weight: 700; color: #8a8a8a; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px; }
    .laporan-panel .filter-bar .form-control,
    .laporan-panel .filter-bar .form-select { border-radius: 9px; }
    .laporan-panel .btn-filter {
        border: none; border-radius: 10px; padding: 8px 20px; font-weight: 600; font-size: 0.85rem;
        color: #fff; background: linear-gradient(120deg, var(--lp-gold), var(--lp-gold-soft));
    }
    .laporan-panel .btn-reset { border-radius: 10px; font-weight: 600; font-size: 0.85rem; }

    /* ---- Mini Bar Chart per Metode ---- */
    .laporan-panel .metode-chart { margin-bottom: 1.75rem; }
    .laporan-panel .metode-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
    .laporan-panel .metode-name { width: 90px; font-size: 0.82rem; font-weight: 700; text-transform: capitalize; }
    .laporan-panel .metode-bar-track { flex: 1; background: #f0ece4; border-radius: 999px; height: 10px; overflow: hidden; }
    .laporan-panel .metode-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--lp-gold), var(--lp-gold-soft)); }
    .laporan-panel .metode-total { width: 150px; text-align: right; font-size: 0.82rem; font-weight: 700; color: #555; }

    /* ---- Table ---- */
    .laporan-panel table { border-collapse: separate; border-spacing: 0; }
    .laporan-panel table thead th {
        border: none; font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.04em;
        color: #8a8a8a; background: #faf7f2;
    }
    .laporan-panel table tbody tr:hover { background: #fdf6ea; }
    .laporan-panel table td { vertical-align: middle; border-color: #f0ece4; }
    .laporan-panel .kode-order { font-weight: 700; font-family: monospace; font-size: 0.85rem; }

    .laporan-panel .pill {
        display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 999px;
        font-size: 0.74rem; font-weight: 700; white-space: nowrap;
    }
    .laporan-panel .metode-qris    { background: rgba(47,111,176,0.12); color: var(--lp-blue); }
    .laporan-panel .metode-cash    { background: rgba(47,158,107,0.12); color: var(--lp-green); }
    .laporan-panel .metode-ewallet { background: rgba(200,134,42,0.14); color: var(--lp-gold); }

    .laporan-panel .status-pending { background: rgba(179,118,15,0.14); color: var(--lp-amber); }
    .laporan-panel .status-lunas   { background: rgba(47,158,107,0.12); color: var(--lp-green); }

    .laporan-panel .meta-mini { font-size: 0.78rem; color: #9a9a9a; }

    .laporan-panel .empty-state { text-align: center; padding: 48px 20px; color: #9a9a9a; }
    .laporan-panel .empty-state svg { width: 40px; height: 40px; color: #ddd; display: block; margin: 0 auto 10px; }

    .laporan-panel .btn-export {
        border: none; border-radius: 10px; padding: 8px 16px; font-weight: 600; font-size: 0.85rem;
        color: #fff; display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(120deg, var(--lp-green), #57c690);
    }
</style>

<div class="laporan-panel">
    <div class="card shadow">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="panel-title">Laporan Pembayaran
                    <span>Ringkasan transaksi periode <?= date('d M Y', strtotime($tgl_dari)) ?> – <?= date('d M Y', strtotime($tgl_sampai)) ?></span>
                </h4>
                <a href="?page=laporan&tgl_dari=<?= $tgl_dari ?>&tgl_sampai=<?= $tgl_sampai ?>&metode=<?= $f_metode ?>&status=<?= $f_status ?>&export=csv" class="btn-export">
                    <i class="icon-download"></i> Export CSV
                </a>
            </div>
            <hr>

            <!-- ================= FILTER ================= -->
            <form method="GET" class="filter-bar row g-3 align-items-end">
                <input type="hidden" name="page" value="laporan">
                <div class="col-md-3 col-6">
                    <label>Dari Tanggal</label>
                    <input type="date" class="form-control" name="tgl_dari" value="<?= htmlspecialchars($tgl_dari) ?>">
                </div>
                <div class="col-md-3 col-6">
                    <label>Sampai Tanggal</label>
                    <input type="date" class="form-control" name="tgl_sampai" value="<?= htmlspecialchars($tgl_sampai) ?>">
                </div>
                <div class="col-md-2 col-6">
                    <label>Metode</label>
                    <select class="form-select" name="metode">
                        <option value="">Semua</option>
                        <?php foreach (['qris', 'cash', 'ewallet'] as $m): ?>
                        <option value="<?= $m ?>" <?= $f_metode === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label>Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua</option>
                        <?php foreach (['pending', 'lunas'] as $s): ?>
                        <option value="<?= $s ?>" <?= $f_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 col-12 d-flex gap-2">
                    <button type="submit" class="btn-filter">Terapkan</button>
                    <a href="?page=laporan" class="btn btn-outline-secondary btn-reset">Reset</a>
                </div>
            </form>

            <!-- ================= SUMMARY CARDS ================= -->
            <div class="summary-grid">
                <div class="summary-card pendapatan">
                    <div class="label">Total Pendapatan</div>
                    <div class="value">Rp <?= number_format($summary['total_pendapatan'], 0, ',', '.') ?></div>
                </div>
                <div class="summary-card transaksi">
                    <div class="label">Total Transaksi</div>
                    <div class="value"><?= number_format($summary['total_transaksi'], 0, ',', '.') ?></div>
                </div>
                <div class="summary-card lunas">
                    <div class="label">Transaksi Lunas</div>
                    <div class="value"><?= number_format($summary['jumlah_lunas'], 0, ',', '.') ?></div>
                </div>
                <div class="summary-card pending">
                    <div class="label">Transaksi Pending</div>
                    <div class="value"><?= number_format($summary['jumlah_pending'], 0, ',', '.') ?></div>
                </div>
            </div>

            <!-- ================= MINI CHART PER METODE ================= -->
            <div class="metode-chart">
                <h6 class="mb-3" style="font-weight:700;">Pendapatan per Metode Pembayaran (Lunas)</h6>
                <?php foreach ($metodeList as $m):
                    $total = $metodeData[$m]['total'] ?? 0;
                    $jumlah = $metodeData[$m]['jumlah'] ?? 0;
                    $pct = $maxMetodeTotal > 0 ? ($total / $maxMetodeTotal) * 100 : 0;
                ?>
                <div class="metode-row">
                    <div class="metode-name"><?= $m ?></div>
                    <div class="metode-bar-track">
                        <div class="metode-bar-fill" style="width: <?= $pct ?>%;"></div>
                    </div>
                    <div class="metode-total">Rp <?= number_format($total, 0, ',', '.') ?> (<?= $jumlah ?>x)</div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ================= TABEL DETAIL ================= -->
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode Order</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th>Total Bayar</th>
                            <th>Status</th>
                            <th>Waktu Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($dataResult) === 0): ?>
                        <tr><td colspan="7">
                            <div class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path d="M6 2l-3 4v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                </svg>
                                Tidak ada data pembayaran pada periode/filter ini.
                            </div>
                        </td></tr>
                        <?php else: while ($row = mysqli_fetch_assoc($dataResult)): ?>
                        <tr>
                            <td class="meta-mini">#<?= $row['id_pembayaran'] ?></td>
                            <td><span class="kode-order"><?= htmlspecialchars($row['kode_order'] ?? '-') ?></span></td>
                            <td><span class="meta-mini"><?= $row['tipe_order'] === 'dine_in' ? 'Dine In · Meja ' . htmlspecialchars($row['nomor_meja']) : 'Take Away' ?></span></td>
                            <td><span class="pill metode-<?= $row['metode'] ?>"><?= ucfirst($row['metode']) ?></span></td>
                            <td>Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                            <td><span class="pill status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                            <td><span class="meta-mini"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></span></td>
                        </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
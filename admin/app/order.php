<?php
include 'koneksi.php';

// UPDATE STATUS ORDER
if (isset($_POST['update_status'])) {
    $id_order    = (int) $_POST['id_order'];
    $status_baru = mysqli_real_escape_string($koneksi, $_POST['status']);

    $extra = "";
    if ($status_baru === 'diproses') {
        $extra = ", waktu_diproses = NOW()";
    } elseif ($status_baru === 'selesai') {
        $extra = ", waktu_selesai = NOW()";
    }

    $query  = "UPDATE orders SET status = '$status_baru' $extra WHERE id_order = '$id_order'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> Status order berhasil diupdate</div>";

        // Sinkronkan status pembayaran mengikuti status order
        // dibayar/diproses/selesai -> pembayaran dianggap lunas
        // pending -> pembayaran kembali pending
        if (in_array($status_baru, ['dibayar', 'diproses', 'selesai'])) {
            mysqli_query($koneksi, "UPDATE pembayaran SET status = 'lunas' WHERE id_order = '$id_order'");
        } elseif ($status_baru === 'pending') {
            mysqli_query($koneksi, "UPDATE pembayaran SET status = 'pending' WHERE id_order = '$id_order'");
        }
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> Gagal update status: " . mysqli_error($koneksi) . "</div>";
    }
}

// HAPUS ORDER
if (isset($_POST['hapus_order'])) {
    $id_order = (int) $_POST['id_order'];

    mysqli_query($koneksi, "DELETE FROM order_detail WHERE id_order = '$id_order'");
    $result = mysqli_query($koneksi, "DELETE FROM orders WHERE id_order = '$id_order'");

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> Order berhasil dihapus</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> Gagal menghapus order: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<style>
    .order-panel {
        --op-gold: #c8862a;
        --op-gold-soft: #e0a94f;
        --op-blue: #2f6fb0;
        --op-amber: #b3760f;
        --op-green: #2f9e6b;
        --op-grey: #7a7a7a;
        --op-danger: #d0475c;
    }

    .order-panel .card { border: none; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .order-panel .card-body { padding: 1.75rem; }

    .order-panel .panel-title { font-weight: 700; margin: 0; }
    .order-panel .panel-title span { color: #8a8a8a; font-weight: 400; font-size: 0.85rem; display: block; margin-top: 2px; }

    .order-panel table { border-collapse: separate; border-spacing: 0; }
    .order-panel table thead th {
        border: none;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a8a8a;
        background: #faf7f2;
    }
    .order-panel table tbody tr { transition: background 0.18s ease, transform 0.18s ease; }
    .order-panel table tbody tr:hover { background: #fdf6ea; transform: translateX(2px); }
    .order-panel table td { vertical-align: middle; border-color: #f0ece4; }

    .order-panel .kode-order { font-weight: 700; font-family: monospace; font-size: 0.85rem; }

    .order-panel .pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .order-panel .tipe-dine_in    { background: rgba(47,111,176,0.12); color: var(--op-blue); }
    .order-panel .tipe-take_away  { background: rgba(200,134,42,0.14); color: var(--op-gold); }

    .order-panel .status-pending   { background: rgba(122,122,122,0.14); color: var(--op-grey); }
    .order-panel .status-dibayar   { background: rgba(47,111,176,0.12); color: var(--op-blue); }
    .order-panel .status-diproses  { background: rgba(217,154,31,0.16); color: var(--op-amber); }
    .order-panel .status-selesai   { background: rgba(47,158,107,0.12); color: var(--op-green); }

    .order-panel .btn-icon-sm {
        border-radius: 8px;
        width: 34px;
        height: 34px;
        padding: 0;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.15s ease;
    }
    .order-panel .btn-icon-sm:hover { transform: translateY(-1px); }
    .order-panel .btn-icon-sm svg { width: 16px; height: 16px; }

    .order-panel .btn-add {
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(120deg, var(--op-gold), var(--op-gold-soft));
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .order-panel .btn-add:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(200,134,42,0.3); color: #fff; }

    .order-panel .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    .order-panel .modal.fade .modal-dialog { transform: scale(0.94) translateY(10px); transition: transform 0.25s cubic-bezier(.2,.8,.2,1); }
    .order-panel .modal.show .modal-dialog { transform: scale(1) translateY(0); }
    .order-panel .modal-header { background: #faf7f2; border-bottom: 1px solid #eee; }

    .order-panel .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 9px 0;
        border-bottom: 1px dashed #eee;
        font-size: 0.88rem;
    }
    .order-panel .detail-row:last-child { border-bottom: none; }
    .order-panel .detail-total {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 2px solid #eee;
        font-weight: 700;
    }

    .order-panel .meta-mini { font-size: 0.78rem; color: #9a9a9a; }

    .order-panel .empty-state { text-align: center; padding: 48px 20px; color: #9a9a9a; }
    .order-panel .empty-state svg { width: 40px; height: 40px; color: #ddd; display: block; margin: 0 auto 10px; }

    .order-panel .form-select:focus { border-color: var(--op-gold-soft); box-shadow: 0 0 0 3px rgba(224,169,79,0.18); }

    .alert-float {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px; border-radius: 12px; margin-bottom: 16px;
        font-size: 0.9rem; font-weight: 600;
        opacity: 0; transform: translateY(-8px);
        animation: alertIn 0.35s ease forwards;
    }
    @keyframes alertIn { to { opacity: 1; transform: translateY(0); } }
    .alert-float-success { background: rgba(47,158,107,0.1); color: var(--op-green); }
    .alert-float-danger  { background: rgba(208,71,92,0.1); color: var(--op-danger); }
</style>

<div class="order-panel">

    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="panel-title">Data Order <span>Pantau dan kelola pesanan masuk</span></h4>
            </div>
            <hr>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tipe</th>
                            <th>Meja</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = mysqli_query($koneksi,
                            "SELECT o.*, u1.nama AS nama_pegawai, u2.nama AS nama_customer
                             FROM orders o
                             LEFT JOIN users u1 ON o.id_pegawai = u1.id_user
                             LEFT JOIN users u2 ON o.id_customer = u2.id_user
                             ORDER BY o.created_at DESC");

                        if (mysqli_num_rows($query) === 0) {
                            echo "<tr><td colspan='7'>
                                    <div class='empty-state'>
                                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.6'>
                                            <path d='M6 2l-3 4v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z'></path>
                                        </svg>
                                        Belum ada order masuk.
                                    </div>
                                  </td></tr>";
                        }

                        while ($o = mysqli_fetch_assoc($query)):
                            $tipeClass = 'tipe-' . $o['tipe_order'];
                            $tipeLabel = $o['tipe_order'] === 'dine_in' ? 'Dine In' : 'Take Away';
                            $statusClass = 'status-' . $o['status'];
                            $statusLabel = ucfirst($o['status']);
                        ?>
                        <tr>
                            <td><span class="kode-order"><?= htmlspecialchars($o['kode_order']) ?></span></td>
                            <td><span class="pill <?= $tipeClass ?>"><?= $tipeLabel ?></span></td>
                            <td><?= $o['nomor_meja'] ? htmlspecialchars($o['nomor_meja']) : '<span class="meta-mini">-</span>' ?></td>
                            <td>Rp <?= number_format($o['total_harga'], 0, ',', '.') ?></td>
                            <td><span class="pill <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td><span class="meta-mini"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-icon-sm" data-bs-toggle="modal"
                                    data-bs-target="#detailModal<?= $o['id_order'] ?>" title="Lihat Detail">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-warning btn-icon-sm" data-bs-toggle="modal"
                                    data-bs-target="#statusModal<?= $o['id_order'] ?>" title="Update Status">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"></path>
                                        <path d="M12 8v4l3 2"></path>
                                    </svg>
                                </button>
                                <button type="button" class="btn btn-danger btn-icon-sm" data-bs-toggle="modal"
                                    data-bs-target="#hapusModal<?= $o['id_order'] ?>" title="Hapus">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"></path>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                        <path d="M10 11v6M14 11v6"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- MODAL DETAIL -->
                        <div class="modal fade" id="detailModal<?= $o['id_order'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Order <?= htmlspecialchars($o['kode_order']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="meta-mini mb-3">
                                            Pegawai: <?= htmlspecialchars($o['nama_pegawai'] ?? '-') ?> ·
                                            Customer: <?= htmlspecialchars($o['nama_customer'] ?? '-') ?> ·
                                            Bayar: <?= $o['metode_bayar'] ? strtoupper($o['metode_bayar']) : '-' ?>
                                        </p>
                                        <?php
                                        $detail = mysqli_query($koneksi,
                                            "SELECT d.*, m.nama_menu
                                             FROM order_detail d
                                             JOIN menu m ON d.id_menu = m.id_menu
                                             WHERE d.id_order = '{$o['id_order']}'");
                                        while ($item = mysqli_fetch_assoc($detail)):
                                        ?>
                                        <div class="detail-row">
                                            <span><?= htmlspecialchars($item['nama_menu']) ?> &times; <?= $item['jumlah'] ?></span>
                                            <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                                        </div>
                                        <?php endwhile; ?>
                                        <div class="detail-total">
                                            <span>Total</span>
                                            <span>Rp <?= number_format($o['total_harga'], 0, ',', '.') ?></span>
                                        </div>
                                        <?php if (!empty($o['catatan'])): ?>
                                        <p class="meta-mini mt-3">Catatan: <?= htmlspecialchars($o['catatan']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL UPDATE STATUS -->
                        <div class="modal fade" id="statusModal<?= $o['id_order'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                                            <label class="form-label">Status Order</label>
                                            <select class="form-select" name="status" required>
                                                <?php foreach (['pending', 'dibayar', 'diproses', 'selesai'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_status" class="btn-add">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL HAPUS -->
                        <div class="modal fade" id="hapusModal<?= $o['id_order'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Hapus Order</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                                            <p>Hapus order <strong><?= htmlspecialchars($o['kode_order']) ?></strong> beserta semua item di dalamnya?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="hapus_order" class="btn btn-danger">Ya, Hapus</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    document.querySelectorAll('.order-panel .alert-float').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 400);
        }, 3500);
    });
</script>
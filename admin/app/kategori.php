<?php
include 'koneksi.php';

if (isset($_POST['tambah'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);

    $query  = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> Kategori berhasil ditambahkan</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> Kategori gagal ditambahkan: " . mysqli_error($koneksi) . "</div>";
    }
}

if (isset($_POST['edit'])) {
    $id_kategori   = $_POST['id_kategori'];
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);

    $query  = "UPDATE kategori SET nama_kategori='$nama_kategori' WHERE id_kategori='$id_kategori'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> Kategori berhasil diubah</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> Kategori gagal diubah: " . mysqli_error($koneksi) . "</div>";
    }
}

if (isset($_POST['hapus'])) {
    $id_kategori = $_POST['id_kategori'];

    $query  = "DELETE FROM kategori WHERE id_kategori='$id_kategori'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> Kategori berhasil dihapus</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> Kategori gagal dihapus: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<style>
    .kategori-panel {
        --kp-gold: #c8862a;
        --kp-gold-soft: #e0a94f;
        --kp-danger: #d0475c;
        --kp-success: #2f9e6b;
    }

    .kategori-panel .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .kategori-panel .card-body {
        padding: 1.75rem;
    }

    .kategori-panel .panel-title {
        font-weight: 700;
        margin: 0;
    }

    .kategori-panel .panel-title span {
        color: #8a8a8a;
        font-weight: 400;
        font-size: 0.85rem;
        display: block;
        margin-top: 2px;
    }

    .kategori-panel .btn-add {
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(120deg, var(--kp-gold), var(--kp-gold-soft));
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .kategori-panel .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(200, 134, 42, 0.35);
        color: #fff;
    }

    .kategori-panel .btn-add:active {
        transform: translateY(0);
    }

    .kategori-panel .btn-add svg {
        width: 15px;
        height: 15px;
    }

    .kategori-panel table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .kategori-panel table thead th {
        border: none;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a8a8a;
        background: #faf7f2;
    }

    .kategori-panel table tbody tr {
        transition: background 0.18s ease, transform 0.18s ease;
    }

    .kategori-panel table tbody tr:hover {
        background: #fdf6ea;
        transform: translateX(2px);
    }

    .kategori-panel table td {
        vertical-align: middle;
        border-color: #f0ece4;
    }

    .kategori-panel .cat-name {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
    }

    .kategori-panel .cat-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--kp-gold-soft);
        flex-shrink: 0;
    }

    .kategori-panel .btn-icon-sm {
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

    .kategori-panel .btn-icon-sm:hover {
        transform: translateY(-1px);
    }

    .kategori-panel .btn-icon-sm svg {
        width: 16px;
        height: 16px;
    }

    .kategori-panel .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }

    .kategori-panel .modal.fade .modal-dialog {
        transform: scale(0.94) translateY(10px);
        transition: transform 0.25s cubic-bezier(.2, .8, .2, 1);
    }

    .kategori-panel .modal.show .modal-dialog {
        transform: scale(1) translateY(0);
    }

    .kategori-panel .modal-header {
        background: #faf7f2;
        border-bottom: 1px solid #eee;
    }

    .kategori-panel .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #9a9a9a;
    }

    .kategori-panel .empty-state svg {
        width: 40px;
        height: 40px;
        color: #ddd;
        display: block;
        margin: 0 auto 10px;
    }

    .kategori-panel .form-control:focus {
        border-color: var(--kp-gold-soft);
        box-shadow: 0 0 0 3px rgba(224, 169, 79, 0.18);
    }

    .alert-float {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 16px;
        font-size: 0.9rem;
        font-weight: 600;
        opacity: 0;
        transform: translateY(-8px);
        animation: alertIn 0.35s ease forwards;
    }

    @keyframes alertIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-float-success {
        background: rgba(47, 158, 107, 0.1);
        color: var(--kp-success);
    }

    .alert-float-danger {
        background: rgba(208, 71, 92, 0.1);
        color: var(--kp-danger);
    }
</style>

<div class="kategori-panel">

    <div class="card shadow">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="panel-title">Data Kategori <span>Kelola kategori menu</span></h4>
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#TambahModal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah Kategori
                </button>
            </div>

            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th width="10%">No</th>
                        <th>Nama Kategori</th>
                        <th width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no    = 1;
                    $query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id_kategori ASC");

                    if (mysqli_num_rows($query) === 0) {
                        echo "<tr><td colspan='3'>
                                <div class='empty-state'>
                                    <svg viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='1.6'>
                                        <path d='M4 7h16M4 12h16M4 17h10'></path>
                                    </svg>
                                    Belum ada kategori. Klik <strong>Tambah Kategori</strong> untuk memulai.
                                </div>
                              </td></tr>";
                    }

                    while ($row = mysqli_fetch_assoc($query)) :
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><span class="cat-name"><span
                                    class="cat-dot"></span><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                        <td>
                            <button class="btn btn-warning btn-icon-sm" data-bs-toggle="modal"
                                data-bs-target="#EditModal<?= $row['id_kategori'] ?>" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button class="btn btn-danger btn-icon-sm" data-bs-toggle="modal"
                                data-bs-target="#HapusModal<?= $row['id_kategori'] ?>" title="Hapus">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                    <path d="M10 11v6M14 11v6"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>

                    <!-- ===== MODAL EDIT ===== -->
                    <div class="modal fade" id="EditModal<?= $row['id_kategori'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                                        <label class="form-label">Nama Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control"
                                            value="<?= htmlspecialchars($row['nama_kategori']) ?>" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="edit" class="btn-add">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ===== MODAL TAMBAH ===== -->
                    <div class="modal fade" id="TambahModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tambah Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Nama Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control"
                                            placeholder="Masukkan nama kategori" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="tambah" class="btn-add">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

        </div>

        <!-- ===== MODAL HAPUS ===== -->
        <div class="modal fade" id="HapusModal<?= $row['id_kategori'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Hapus Kategori</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id_kategori" value="<?= $row['id_kategori'] ?>">
                            <p>Apakah Anda yakin ingin menghapus kategori
                                <strong><?= htmlspecialchars($row['nama_kategori']) ?></strong>?
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="hapus" class="btn btn-danger">Ya, Hapus</button>
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

<script>
    document.querySelectorAll('.kategori-panel .alert-float').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () {
                el.remove();
            }, 400);
        }, 3500);
    });
</script>
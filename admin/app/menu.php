<?php
include 'koneksi.php';

// TAMBAH MENU
if (isset($_POST['tambah_menu'])) {
    $id_kategori = $_POST['id_kategori'];
    $nama_menu   = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $harga       = $_POST['harga'];
    $stok        = $_POST['stok'];

    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    $folder = "../../assets/img/";

    if (!is_dir($folder)) mkdir($folder, 0777, true);
    move_uploaded_file($tmp, $folder . $foto);

    $query  = "INSERT INTO menu (id_kategori, nama_menu, harga, stok, foto, created_at)
                VALUES ('$id_kategori','$nama_menu','$harga','$stok','$foto',NOW())";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='bi bi-check-circle-fill'></i> Menu berhasil ditambahkan</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='bi bi-x-circle-fill'></i> Menu gagal ditambahkan: " . mysqli_error($koneksi) . "</div>";
    }
}

// EDIT MENU
if (isset($_POST['ubah_menu'])) {
    $id_menu     = $_POST['id_menu'];
    $id_kategori = $_POST['id_kategori'];
    $nama_menu   = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $harga       = $_POST['harga'];
    $stok        = $_POST['stok'];

    if (!empty($_FILES['foto_baru']['name'])) {
        $foto = $_FILES['foto_baru']['name'];
        $tmp  = $_FILES['foto_baru']['tmp_name'];
        $folder = "../../assets/img/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        move_uploaded_file($tmp, $folder . $foto);
    } else {
        $foto = $_POST['foto_lama'];
    }

    $query  = "UPDATE menu
                SET id_kategori='$id_kategori', nama_menu='$nama_menu',
                    harga='$harga', stok='$stok', foto='$foto'
                WHERE id_menu='$id_menu'";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='bi bi-check-circle-fill'></i> Menu berhasil diubah</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='bi bi-x-circle-fill'></i> Menu gagal diubah: " . mysqli_error($koneksi) . "</div>";
    }
}

// HAPUS MENU
if (isset($_POST['hapus_menu'])) {
    $id_menu = $_POST['id_menu'];
    $query   = "DELETE FROM menu WHERE id_menu='$id_menu'";
    $result  = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='bi bi-check-circle-fill'></i> Menu berhasil dihapus</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='bi bi-x-circle-fill'></i> Menu gagal dihapus: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<style>
    .menu-panel {
        --mp-gold: #c8862a;
        --mp-gold-soft: #e0a94f;
        --mp-wine: #8a2846;
        --mp-danger: #d0475c;
        --mp-success: #2f9e6b;
        --mp-warning: #d99a1f;
    }

    .menu-panel .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .menu-panel .card-body {
        padding: 1.75rem;
    }

    .menu-panel .panel-title {
        font-weight: 700;
        margin: 0;
    }

    .menu-panel .panel-title span {
        color: #8a8a8a;
        font-weight: 400;
        font-size: 0.85rem;
        display: block;
        margin-top: 2px;
    }

    .menu-panel .btn-add {
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        color: #fff;
        background: linear-gradient(120deg, var(--mp-gold), var(--mp-gold-soft));
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .menu-panel .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(200, 134, 42, 0.35);
        color: #fff;
    }

    .menu-panel .btn-add:active {
        transform: translateY(0);
    }

    .menu-panel table thead th {
        border: none;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a8a8a;
        background: #faf7f2;
    }

    .menu-panel table tbody tr {
        transition: background 0.18s ease, transform 0.18s ease;
    }

    .menu-panel table tbody tr:hover {
        background: #fdf6ea;
        transform: translateX(2px);
    }

    .menu-panel table td {
        vertical-align: middle;
    }

    .menu-panel .thumb-wrap {
        overflow: hidden;
        border-radius: 10px;
        width: 64px;
        height: 64px;
        display: inline-block;
    }

    .menu-panel .thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.35s ease;
    }

    .menu-panel .thumb-wrap:hover img {
        transform: scale(1.15);
    }

    .menu-panel .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .menu-panel .stock-ok {
        background: rgba(47, 158, 107, 0.12);
        color: var(--mp-success);
    }

    .menu-panel .stock-low {
        background: rgba(217, 154, 31, 0.14);
        color: var(--mp-warning);
    }

    .menu-panel .stock-out {
        background: rgba(208, 71, 92, 0.12);
        color: var(--mp-danger);
    }

    .menu-panel .btn-icon-sm {
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

    .menu-panel .btn-icon-sm:hover {
        transform: translateY(-1px);
    }

    .menu-panel .btn-icon-sm svg {
        width: 16px;
        height: 16px;
    }

    .menu-panel .btn-icon-sm:hover {
        transform: translateY(-1px);
    }

    .menu-panel .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
    }

    .menu-panel .modal.fade .modal-dialog {
        transform: scale(0.94) translateY(10px);
        transition: transform 0.25s cubic-bezier(.2, .8, .2, 1);
    }

    .menu-panel .modal.show .modal-dialog {
        transform: scale(1) translateY(0);
    }

    .menu-panel .modal-header {
        background: #faf7f2;
        border-bottom: 1px solid #eee;
    }

    .menu-panel .preview-box {
        margin-top: 10px;
        width: 84px;
        height: 84px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px dashed #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #faf7f2;
        opacity: 0;
        transform: scale(0.9);
        transition: opacity 0.25s ease, transform 0.25s ease;
    }

    .menu-panel .preview-box.show {
        opacity: 1;
        transform: scale(1);
    }

    .menu-panel .preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .menu-panel .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #9a9a9a;
    }

    .menu-panel .empty-state i {
        font-size: 2.4rem;
        color: #ddd;
        display: block;
        margin-bottom: 10px;
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
        color: var(--mp-success);
    }

    .alert-float-danger {
        background: rgba(208, 71, 92, 0.1);
        color: var(--mp-danger);
    }
</style>

<div class="menu-panel">

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="panel-title">Data Menu <span>Kelola daftar menu resto</span></h4>
                <button type="button" class="btn-add ms-auto" data-bs-toggle="modal" data-bs-target="#TambahMenuModal">
                    <i class="bi bi-plus-lg"></i> Tambah Menu
                </button>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Kategori</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no    = 1;
                        $query = mysqli_query($koneksi,
                            "SELECT menu.*, kategori.nama_kategori
                             FROM menu
                             INNER JOIN kategori ON menu.id_kategori = kategori.id_kategori
                             ORDER BY menu.created_at DESC");

                        if (mysqli_num_rows($query) === 0) {
                            echo "<tr><td colspan='7'>
                                    <div class='empty-state'>
                                        <i class='bi bi-egg-fried'></i>
                                        Belum ada menu. Klik <strong>Tambah Menu</strong> untuk mulai mengisi.
                                    </div>
                                  </td></tr>";
                        }

                        while ($data = mysqli_fetch_assoc($query)) :
                            $stok = (int) $data['stok'];
                            if ($stok <= 0) {
                                $stokClass = 'stock-out'; $stokIcon = 'bi-x-circle'; $stokLabel = 'Habis';
                            } elseif ($stok <= 5) {
                                $stokClass = 'stock-low'; $stokIcon = 'bi-exclamation-circle'; $stokLabel = $stok . ' tersisa';
                            } else {
                                $stokClass = 'stock-ok'; $stokIcon = 'bi-check-circle'; $stokLabel = $stok . ' tersedia';
                            }
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <span class="thumb-wrap">
                                    <img src="../../assets/img/<?= htmlspecialchars($data['foto']) ?>"
                                        alt="<?= htmlspecialchars($data['nama_menu']) ?>">
                                </span>
                            </td>
                            <td><?= htmlspecialchars($data['nama_kategori']) ?></td>
                            <td><?= htmlspecialchars($data['nama_menu']) ?></td>
                            <td>Rp <?= number_format($data['harga'], 0, ',', '.') ?></td>
                            <td><span class="stock-badge <?= $stokClass ?>"><i class="bi <?= $stokIcon ?>"></i>
                                    <?= $stokLabel ?></span></td>
                            <td>
                                <button class="btn btn-warning btn-icon-sm" data-bs-toggle="modal"
                                    data-bs-target="#EditModal<?= $data['id_menu'] ?>" title="Edit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                                <button class="btn btn-danger btn-icon-sm" data-bs-toggle="modal"
                                    data-bs-target="#HapusModal<?= $data['id_menu'] ?>" title="Hapus">
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
                        <div class="modal fade" id="EditModal<?= $data['id_menu'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Ubah Menu</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_menu" value="<?= $data['id_menu'] ?>">
                                            <input type="hidden" name="foto_lama"
                                                value="<?= htmlspecialchars($data['foto']) ?>">

                                            <div class="mb-3">
                                                <label class="form-label">Kategori</label>
                                                <select class="form-select" name="id_kategori" required>
                                                    <?php
                                                    $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                                                    while ($kat = mysqli_fetch_assoc($q_kat)) {
                                                        $sel = ($kat['id_kategori'] == $data['id_kategori']) ? 'selected' : '';
                                                        echo "<option value='{$kat['id_kategori']}' $sel>
                                                                  {$kat['nama_kategori']}
                                                              </option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nama Menu</label>
                                                <input type="text" class="form-control" name="nama_menu"
                                                    value="<?= htmlspecialchars($data['nama_menu']) ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Harga (Rp)</label>
                                                <input type="number" class="form-control" name="harga"
                                                    value="<?= $data['harga'] ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Stok</label>
                                                <input type="number" class="form-control" name="stok"
                                                    value="<?= $data['stok'] ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Ganti Foto (Opsional)</label><br>
                                                <img src="../../assets/img/<?= htmlspecialchars($data['foto']) ?>"
                                                    width="80" class="mb-2 img-thumbnail">
                                                <input type="file" class="form-control foto-input" name="foto_baru"
                                                    accept="image/*">
                                                <div class="preview-box"><img src="" alt=""></div>
                                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah
                                                    foto.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" name="ubah_menu" class="btn-add">Simpan
                                                Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ===== MODAL HAPUS ===== -->
                        <div class="modal fade" id="HapusModal<?= $data['id_menu'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <form action="" method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_menu" value="<?= $data['id_menu'] ?>">
                                            <p>Apakah Anda yakin ingin menghapus menu
                                                <strong><?= htmlspecialchars($data['nama_menu']) ?></strong>?
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="hapus_menu" class="btn btn-danger">Ya,
                                                Hapus</button>
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

    <!-- ===== MODAL TAMBAH ===== -->
    <div class="modal fade" id="TambahMenuModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="id_kategori" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php
                                $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                                while ($kat = mysqli_fetch_assoc($q_kat)) {
                                    echo "<option value='{$kat['id_kategori']}'>{$kat['nama_kategori']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" name="nama_menu" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" class="form-control" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stok" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto Menu</label>
                            <input type="file" class="form-control foto-input" name="foto" accept="image/*" required>
                            <div class="preview-box"><img src="" alt=""></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="tambah_menu" class="btn-add">Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    document.querySelectorAll('.menu-panel .foto-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var box = input.parentElement.querySelector('.preview-box');
            var img = box.querySelector('img');
            var file = input.files[0];
            if (!file) {
                box.classList.remove('show');
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                box.classList.add('show');
            };
            reader.readAsDataURL(file);
        });
    });

    document.querySelectorAll('.menu-panel .alert-float').forEach(function (el) {
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
<?php
include 'koneksi.php';

// TAMBAH USER
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = md5($_POST['password']);
    $role     = ($_POST['role'] === 'admin') ? 'admin' : 'customer';

    $query  = "INSERT INTO users (nama, email, password, role, id_google, foto)
                VALUES ('$nama', '$email', '$password', '$role', '', '')";
    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> User berhasil ditambahkan</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> User gagal ditambahkan: " . mysqli_error($koneksi) . "</div>";
    }
}

// EDIT USER
if (isset($_POST['edit'])) {
    $id_user = $_POST['id_user'];
    $nama    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email   = mysqli_real_escape_string($koneksi, $_POST['email']);

    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $update = mysqli_query($koneksi, "UPDATE users SET
            nama = '$nama',
            email = '$email',
            password = '$password'
            WHERE id_user = '$id_user'
        ");
    } else {
        $update = mysqli_query($koneksi, "UPDATE users SET
            nama = '$nama',
            email = '$email'
            WHERE id_user = '$id_user'
        ");
    }

    if ($update) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> User berhasil diedit</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> User gagal diedit: " . mysqli_error($koneksi) . "</div>";
    }

// HAPUS USER
} else if (isset($_POST['hapus'])) {
    $id_user = $_POST['id_user'];
    $query   = "DELETE FROM users WHERE id_user='$id_user'";
    $delete  = mysqli_query($koneksi, $query);

    if ($delete) {
        echo "<div class='alert-float alert-float-success'><i class='icon-check'></i> User berhasil dihapus</div>";
    } else {
        echo "<div class='alert-float alert-float-danger'><i class='icon-x'></i> User gagal dihapus: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<style>
    .user-panel {
        --up-gold: #c8862a;
        --up-gold-soft: #e0a94f;
        --up-blue: #2f6fb0;
        --up-danger: #d0475c;
        --up-success: #2f9e6b;
    }

    .user-panel .card { border: none; border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .user-panel .card-body { padding: 1.75rem; }

    .user-panel .panel-title { font-weight: 700; margin: 0; }
    .user-panel .panel-title span { color: #8a8a8a; font-weight: 400; font-size: 0.85rem; display: block; margin-top: 2px; }

    .user-panel .btn-add {
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(120deg, var(--up-gold), var(--up-gold-soft));
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .user-panel .btn-add:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(200,134,42,0.35); color: #fff; }
    .user-panel .btn-add:active { transform: translateY(0); }
    .user-panel .btn-add svg { width: 15px; height: 15px; }

    .user-panel table { border-collapse: separate; border-spacing: 0; }
    .user-panel table thead th {
        border: none;
        font-size: 0.76rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #8a8a8a;
        background: #faf7f2;
    }
    .user-panel table tbody tr { transition: background 0.18s ease, transform 0.18s ease; }
    .user-panel table tbody tr:hover { background: #fdf6ea; transform: translateX(2px); }
    .user-panel table td { vertical-align: middle; border-color: #f0ece4; }

    .user-panel .user-cell { display: flex; align-items: center; gap: 10px; }
    .user-panel .avatar-sm {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.82rem; color: #fff;
        background: linear-gradient(140deg, var(--up-gold-soft), var(--up-gold));
        flex-shrink: 0;
    }
    .user-panel .user-name { font-weight: 600; }

    .user-panel .role-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .user-panel .role-admin { background: rgba(47,111,176,0.12); color: var(--up-blue); }
    .user-panel .role-customer { background: rgba(217,154,31,0.14); color: #b3760f; }

    .user-panel .btn-icon-sm {
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
    .user-panel .btn-icon-sm:hover { transform: translateY(-1px); }
    .user-panel .btn-icon-sm svg { width: 16px; height: 16px; }

    .user-panel .modal-content { border: none; border-radius: 16px; overflow: hidden; }
    .user-panel .modal.fade .modal-dialog { transform: scale(0.94) translateY(10px); transition: transform 0.25s cubic-bezier(.2,.8,.2,1); }
    .user-panel .modal.show .modal-dialog { transform: scale(1) translateY(0); }
    .user-panel .modal-header { background: #faf7f2; border-bottom: 1px solid #eee; }

    .user-panel .pw-hint {
        display: block;
        margin: 4px 0 8px;
        font-size: 0.78rem;
        color: var(--up-danger);
    }

    .user-panel .form-control:focus,
    .user-panel .form-select:focus {
        border-color: var(--up-gold-soft);
        box-shadow: 0 0 0 3px rgba(224,169,79,0.18);
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
    @keyframes alertIn { to { opacity: 1; transform: translateY(0); } }
    .alert-float-success { background: rgba(47,158,107,0.1); color: var(--up-success); }
    .alert-float-danger  { background: rgba(208,71,92,0.1); color: var(--up-danger); }
</style>

<div class="user-panel">

    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="panel-title">Data User <span>Kelola akun admin & pelanggan</span></h4>
                <button type="button" class="btn-add ms-auto" data-bs-toggle="modal" data-bs-target="#TambahModal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Tambah User
                </button>
            </div>

            <hr>

            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $no     = 1;
                    $query  = "SELECT * FROM users";
                    $result = mysqli_query($koneksi, $query);

                    while ($rows = mysqli_fetch_assoc($result)) {
                        $initial = strtoupper(substr($rows['nama'], 0, 1));
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <div class="user-cell">
                                <span class="avatar-sm"><?= htmlspecialchars($initial) ?></span>
                                <span class="user-name"><?= htmlspecialchars($rows['nama']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($rows['email']) ?></td>
                        <td>
                            <?php if ($rows['role'] == 'admin'): ?>
                                <span class="role-pill role-admin">Admin</span>
                            <?php else: ?>
                                <span class="role-pill role-customer">Customer</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-warning btn-icon-sm" data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $rows['id_user'] ?>" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button type="button" class="btn btn-danger btn-icon-sm" data-bs-toggle="modal"
                                data-bs-target="#hapusModal<?= $rows['id_user'] ?>" title="Hapus">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                    <path d="M10 11v6M14 11v6"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>

                    <!-- MODAL EDIT -->
                    <div class="modal fade" id="editModal<?= $rows['id_user'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User: <?= htmlspecialchars($rows['nama']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_user" value="<?= $rows['id_user'] ?>">

                                        <div class="mb-3">
                                            <label class="form-label">Nama</label>
                                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($rows['nama']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($rows['email']) ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <span class="pw-hint">Biarkan kosong jika tidak ingin mengubah password</span>
                                            <input type="password" class="form-control" name="password">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" name="edit" class="btn-add">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL HAPUS -->
                    <div class="modal fade" id="hapusModal<?= $rows['id_user'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Hapus User</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_user" value="<?= $rows['id_user'] ?>">
                                        <p>Apakah Anda yakin ingin menghapus user
                                            <strong><?= htmlspecialchars($rows['nama']) ?></strong>?
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

                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="TambahModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="tambah" class="btn-add">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    document.querySelectorAll('.user-panel .alert-float').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { el.remove(); }, 400);
        }, 3500);
    });
</script>
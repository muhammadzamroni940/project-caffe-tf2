<?php
/**
 * auth.php
 * Helper otorisasi berbasis session & role.
 * Panggil salah satu fungsi ini di baris PALING ATAS setiap halaman
 * (setelah session_start()), sebelum kode/query lain dijalankan.
 */

function cek_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * @param array $allowed_roles contoh: ['admin'] atau ['customer']
 * @param string $redirect_jika_ditolak halaman tujuan jika role tidak sesuai
 */
function cek_role(array $allowed_roles, string $redirect_jika_ditolak = 'pesan.php') {
    cek_login();

    if (!in_array($_SESSION['role'], $allowed_roles, true)) {
        header("Location: " . $redirect_jika_ditolak);
        exit;
    }
}
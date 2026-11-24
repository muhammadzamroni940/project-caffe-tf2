<?php
/**
 * session_boot.php
 * Konfigurasi session terpusat. Wajib di-require SEBELUM session_start()
 * di setiap halaman yang menggunakan session, supaya durasi login
 * konsisten di seluruh aplikasi (admin & customer).
 *
 * Tanpa ini, PHP memakai default bawaan server (biasanya session.gc_maxlifetime
 * hanya ~1440 detik / 24 menit), sehingga user bisa "tiba-tiba" ke-logout
 * padahal tidak pernah menekan tombol logout.
 */

$session_lifetime = 60 * 60 * 8; // 8 jam

ini_set('session.gc_maxlifetime', (string) $session_lifetime);

session_set_cookie_params([
    'lifetime' => $session_lifetime, // cookie tetap hidup 8 jam walau browser ditutup
    'path'     => '/',               // berlaku untuk semua folder (admin, customer, root)
    'httponly' => true,
    'samesite' => 'Lax',
]);
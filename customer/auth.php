<?php
require __DIR__ . '/../session_boot.php';
session_start();

// Halaman ini di-require di paling atas setiap file customer/*.php
// Kalau belum login, atau login tapi bukan role 'customer' (misal admin/kasir
// iseng buka URL customer), tendang balik ke halaman login.
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../admin/login.php");
    exit;
}
<?php
// Dipanggil langsung dari admin/index.php SEBELUM ada output HTML apapun,
// supaya header() untuk download CSV bisa dikirim dengan benar.

$tgl_dari = isset($_GET['tgl_dari']) && $_GET['tgl_dari'] !== '' ? $_GET['tgl_dari'] : date('Y-m-01');
$tgl_sampai = isset($_GET['tgl_sampai']) && $_GET['tgl_sampai'] !== '' ? $_GET['tgl_sampai'] : date('Y-m-d');
$f_metode = isset($_GET['metode']) ? $_GET['metode'] : '';
$f_status = isset($_GET['status']) ? $_GET['status'] : '';

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

$sqlData = "SELECT p.*, o.kode_order, o.tipe_order, o.nomor_meja
            FROM pembayaran p
            LEFT JOIN orders o ON p.id_order = o.id_order
            $where
            ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($koneksi, $sqlData);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$dataResult = mysqli_stmt_get_result($stmt);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="laporan_pembayaran_' . $tgl_dari . '_sd_' . $tgl_sampai . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'Kode Order', 'Metode', 'Total Bayar', 'Status', 'Waktu']);
while ($row = mysqli_fetch_assoc($dataResult)) {
    fputcsv($out, [$row['id_pembayaran'], $row['kode_order'], $row['metode'], $row['total_bayar'], $row['status'], $row['created_at']]);
}
fclose($out);
exit;
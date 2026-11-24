<?php

require __DIR__ . '/session_boot.php';
session_start();

require 'admin/koneksi.php';
require 'config-google.php';

if (!isset($_GET['code'])) {
    header("Location: login.php?error=1");
    exit;
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    header("Location: login.php?error=1");
    exit;
}

$client->setAccessToken($token);

$oauth = new Google_Service_Oauth2($client);

$googleUser = $oauth->userinfo->get();

$google_id = mysqli_real_escape_string($koneksi, $googleUser->id);
$nama      = mysqli_real_escape_string($koneksi, $googleUser->name);
$email     = mysqli_real_escape_string($koneksi, $googleUser->email);
$foto      = mysqli_real_escape_string($koneksi, $googleUser->picture);

$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");

if (mysqli_num_rows($cek) > 0) {

    $user = mysqli_fetch_assoc($cek);

    mysqli_query($koneksi, "
        UPDATE users
        SET
            id_google='$google_id',
            foto='$foto'
        WHERE id_user='" . $user['id_user'] . "'
    ");

    // refresh data user setelah update, biar foto/id_google terbaru ikut ke session
    $q = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='" . $user['id_user'] . "'");
    $user = mysqli_fetch_assoc($q);

} else {

    mysqli_query($koneksi, "
        INSERT INTO users
        (
            nama,
            email,
            password,
            role,
            id_google,
            created_at,
            foto
        )

        VALUES

        (
            '$nama',
            '$email',
            NULL,
            'customer',
            '$google_id',
            NOW(),
            '$foto'
        )
    ");

    $id = mysqli_insert_id($koneksi);

    $q = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id'");

    $user = mysqli_fetch_assoc($q);

}

$_SESSION['id_user'] = $user['id_user'];
$_SESSION['nama']    = $user['nama'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

if ($user['role'] === 'customer') {
    header("Location: customer/index.php");
} else {
    header("Location: admin/index.php");
}
exit;
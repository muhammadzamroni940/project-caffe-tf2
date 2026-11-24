<?php

$koneksi = mysqli_connect("localhost", "root", "", "caffe");

if (!$koneksi) {
    die("koneksigagal: " . mysqli_connect_error());
}

<?php

require __DIR__ . '/../session_boot.php';
session_start();

session_destroy();

header("Location: ../admin/login.php");
exit;
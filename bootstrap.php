<?php

$connectionParams = [
    'dbname' => DB_NAME,
    'user' => DB_USERNAME,
    'password' => DB_PASSWORD,
    'host' => DB_HOST,
    'driver' => 'pdo_mysql',
];
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);

$library_name = 'Perpustakaan Ideal Serbaguna';
$device_id = 'put_your_device_id_here';
$footer_text = 'Harap simpan resi ini sebagai bukti transaksi.';

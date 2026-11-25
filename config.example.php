<?php
/*
 * Salin file ini menjadi config.local.php lalu isi sesuai server kamu.
 * config.local.php TIDAK ikut ke GitHub (ada di .gitignore), jadi password database aman.
 *
 * Contoh di hosting (InfinityFree / Hostinger / dll):
 *   'db_host' => 'sql123.infinityfree.com',
 *   'db_user' => 'if0_12345678',
 *   'db_pass' => 'password-dari-panel-hosting',
 *   'db_name' => 'if0_12345678_avem',
 *   'debug'   => false,
 */
return [
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',
    'db_name' => 'avem',
    'db_port' => 3306,

    // true  = tampilkan error PHP (untuk development di XAMPP)
    // false = sembunyikan error (WAJIB false saat sudah online)
    'debug' => true,
];

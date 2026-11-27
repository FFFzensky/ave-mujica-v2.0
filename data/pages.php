<?php
/*
 * Halaman utama situs (selain halaman member, lihat data/members.php).
 * page_id dipakai kolom komentar: tiap halaman punya komentar sendiri.
 *   0 = beranda, 1–5 = member, 6+ = halaman di bawah.
 * Menambah halaman baru = tambah satu baris di sini + buat file PHP-nya.
 */
return [
    'index'         => ['page_id' => 0, 'url' => 'index.php',         'label' => 'Beranda'],
    'musik'         => ['page_id' => 6, 'url' => 'musik.php',         'label' => 'Musik'],
    'live'          => ['page_id' => 7, 'url' => 'live.php',          'label' => 'Live'],
    'galeri'        => ['page_id' => 8, 'url' => 'galeri.php',        'label' => 'Galeri'],
    'pengisi-suara' => ['page_id' => 9, 'url' => 'pengisi-suara.php', 'label' => 'Pengisi Suara'],
];

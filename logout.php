<?php
require __DIR__ . '/includes/bootstrap.php';

// Logout hanya lewat tombol (POST + CSRF), supaya tidak bisa dipicu dari link di web lain.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid()) {
    redirect('index.php');
}

// Kosongkan data login lalu ganti ID session (session lama dihapus dari server).
// Session baru tetap dipakai untuk menampilkan pesan "sudah logout".
$_SESSION = [];
session_regenerate_id(true);

flash('success', 'Kamu sudah logout.');
redirect('index.php');

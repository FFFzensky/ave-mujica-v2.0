<?php
/*
 * Hapus komentar — SATU file untuk semua halaman (pengganti hapuskomen1–5.php di v1).
 *
 * page_id tidak lagi dikirim lewat URL: diambil langsung dari baris komentar di database,
 * lalu halaman tujuan dicari lewat page_url(). Jadi menambah halaman baru tidak perlu file baru.
 */
require __DIR__ . '/includes/bootstrap.php';

// Hapus hanya lewat POST + token CSRF, supaya tidak bisa terpicu dari link/gambar di web lain.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!is_logged_in()) {
    flash('error', 'Silakan login dulu.');
    redirect('login.php');
}

$commentId = (int) ($_POST['comment_id'] ?? 0);

$stmt = db()->prepare('SELECT username, page_id FROM comments WHERE id = ?');
$stmt->bind_param('i', $commentId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

// v1: kalau komentar tidak ada, $row bernilai null dan memunculkan warning PHP.
if ($row === null) {
    flash('error', 'Komentar tidak ditemukan atau sudah dihapus.');
    redirect('index.php');
}

$back = page_url((int) $row['page_id']) . '#komentar';

if (!csrf_is_valid()) {
    flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    redirect($back);
}

if ($row['username'] !== current_user()) {
    flash('error', 'Kamu hanya bisa menghapus komentarmu sendiri.');
    redirect($back);
}

// Cek pemilik sekali lagi di query DELETE-nya sendiri.
$username = current_user();
$stmt = db()->prepare('DELETE FROM comments WHERE id = ? AND username = ?');
$stmt->bind_param('is', $commentId, $username);
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();

flash($deleted ? 'success' : 'error', $deleted ? 'Komentar dihapus.' : 'Komentar gagal dihapus.');
redirect($back);

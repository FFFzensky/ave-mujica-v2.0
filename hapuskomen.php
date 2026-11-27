<?php
/*
 * Hapus komentar — SATU file untuk semua halaman (pengganti hapuskomen1–5.php di v1).
 *
 * page_id tidak dikirim lewat URL: diambil langsung dari baris komentar di database,
 * lalu halaman tujuan dicari lewat page_url(). Jadi menambah halaman baru tidak perlu file baru.
 *
 * Boleh menghapus: pemilik komentar, atau admin (komentar siapa pun).
 * Menghapus komentar utama ikut menghapus semua balasannya.
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
$returnTo = (string) ($_POST['return'] ?? '');

$stmt = db()->prepare('SELECT username, page_id, parent_id FROM comments WHERE id = ?');
$stmt->bind_param('i', $commentId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row === null) {
    flash('error', 'Komentar tidak ditemukan atau sudah dihapus.');
    redirect($returnTo === 'admin' && is_admin() ? 'admin.php' : 'index.php');
}

// Dari panel admin kembali ke panel admin, selain itu ke halaman komentarnya.
$back = ($returnTo === 'admin' && is_admin())
    ? 'admin.php#komentar-terbaru'
    : page_url((int) $row['page_id']) . '#komentar';

if (!csrf_is_valid()) {
    flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    redirect($back);
}

$isOwner = $row['username'] === current_user();
if (!$isOwner && !is_admin()) {
    flash('error', 'Kamu hanya bisa menghapus komentarmu sendiri.');
    redirect($back);
}

// Hapus komentar + balasannya (kalau komentar utama).
$stmt = db()->prepare('DELETE FROM comments WHERE id = ? OR parent_id = ?');
$stmt->bind_param('ii', $commentId, $commentId);
$stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();

if ($deleted > 0) {
    $message = $isOwner ? 'Komentar dihapus.' : 'Komentar milik ' . $row['username'] . ' dihapus oleh admin.';
    if ($deleted > 1) {
        $message .= ' (' . ($deleted - 1) . ' balasan ikut terhapus)';
    }
    flash('success', $message);
} else {
    flash('error', 'Komentar gagal dihapus.');
}
redirect($back);

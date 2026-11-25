<?php
/*
 * Kirim komentar — SATU file untuk semua halaman.
 * Halaman tujuan setelah kirim diambil dari page_url($pageId).
 */
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$pageId = (int) ($_POST['page_id'] ?? -1);
$back = page_exists($pageId) ? page_url($pageId) : 'index.php';

if (!is_logged_in()) {
    flash('error', 'Silakan login dulu untuk berkomentar.');
    redirect('login.php?from=' . $pageId);
}

if (!csrf_is_valid()) {
    flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    redirect($back . '#komentar');
}

if (!page_exists($pageId)) {
    flash('error', 'Halaman tidak dikenal.');
    redirect('index.php');
}

// Tidak perlu real_escape_string: prepared statement sudah aman dari SQL injection.
$comment = trim((string) ($_POST['comment'] ?? ''));

if ($comment === '') {
    flash('error', 'Komentar tidak boleh kosong.');
    redirect($back . '#komentar');
}

if (text_length($comment) > COMMENT_MAX_LENGTH) {
    flash('error', 'Komentar maksimal ' . COMMENT_MAX_LENGTH . ' karakter.');
    redirect($back . '#komentar');
}

$username = current_user();
$createdAt = date('Y-m-d H:i:s');

$stmt = db()->prepare('INSERT INTO comments (username, comment, created_at, page_id) VALUES (?, ?, ?, ?)');
$stmt->bind_param('sssi', $username, $comment, $createdAt, $pageId);
$stmt->execute();
$stmt->close();

flash('success', 'Komentar terkirim.');
redirect($back . '#komentar');

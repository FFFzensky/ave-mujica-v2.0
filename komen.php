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

/*
 * Balasan: parent_id menunjuk ke komentar utama (thread).
 * Balasan untuk balasan tetap masuk ke thread yang sama (cuma 1 tingkat),
 * dan username yang dibalas disimpan di reply_to untuk tampilan "@username".
 */
$parentId = null;
$replyTo = null;
if (!empty($_POST['parent_id'])) {
    $requestedParent = (int) $_POST['parent_id'];
    $stmt = db()->prepare('SELECT id, username, page_id, parent_id FROM comments WHERE id = ?');
    $stmt->bind_param('i', $requestedParent);
    $stmt->execute();
    $parent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($parent === null || (int) $parent['page_id'] !== $pageId) {
        flash('error', 'Komentar yang mau dibalas sudah tidak ada.');
        redirect($back . '#komentar');
    }

    $parentId = $parent['parent_id'] !== null ? (int) $parent['parent_id'] : (int) $parent['id'];
    $replyTo = trim((string) ($_POST['reply_to'] ?? ''));
    if (!preg_match('/^[A-Za-z0-9_.-]{1,50}$/', $replyTo)) {
        $replyTo = $parent['username'];
    }
}

$username = current_user();
$createdAt = date('Y-m-d H:i:s');

$stmt = db()->prepare(
    'INSERT INTO comments (username, comment, created_at, page_id, parent_id, reply_to) VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('sssiis', $username, $comment, $createdAt, $pageId, $parentId, $replyTo);
$stmt->execute();
$newId = $stmt->insert_id;
$stmt->close();

flash('success', $parentId ? 'Balasan terkirim.' : 'Komentar terkirim.');
redirect($back . '#komentar-' . $newId);

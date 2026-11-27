<?php
/*
 * Panel Admin — hanya untuk akun dengan role 'admin'.
 * - Membuat akun admin baru (username wajib berawalan "admin", password ditentukan sendiri)
 * - Melihat daftar admin
 * - Memoderasi komentar terbaru dari semua halaman
 * - Mereset password pengguna yang lupa (password sementara, wajib diganti saat login)
 */
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    flash('error', 'Silakan login sebagai admin.');
    redirect('login.php');
}
if (!is_admin()) {
    flash('error', 'Halaman ini khusus admin.');
    redirect('index.php');
}

$newUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_admin') {
    $newUsername = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    $error = null;
    if (!csrf_is_valid()) {
        $error = 'Sesi formulir kedaluwarsa. Silakan coba lagi.';
    } elseif (!preg_match('/^admin[A-Za-z0-9_.-]{1,25}$/i', $newUsername)) {
        $error = 'Username admin harus berawalan "admin", contoh: admintimoris.';
    } elseif (strlen($password) < 8) {
        $error = 'Password admin minimal 8 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $stmt = db()->prepare('SELECT id FROM akun WHERE username = ?');
        $stmt->bind_param('s', $newUsername);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username sudah dipakai.';
        }
        $stmt->close();
    }

    if ($error !== null) {
        flash('error', $error);
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';
        $stmt = db()->prepare('INSERT INTO akun (username, password, role) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $newUsername, $hash, $role);
        $stmt->execute();
        $stmt->close();
        $code = issue_recovery_code($newUsername);
        flash('success', 'Akun admin ' . $newUsername . ' berhasil dibuat. Catat kode pemulihannya.');
        reveal_recovery_code($newUsername, $code, 'admin.php#admin-list');
    }
}

/*
 * Reset password pengguna lain (untuk yang lupa password dan tidak punya kode pemulihan).
 * Sistem membuat password sementara yang ditampilkan SEKALI ke admin;
 * pengguna wajib menggantinya saat login berikutnya.
 */
$resetUsername = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    $resetUsername = trim((string) ($_POST['username'] ?? ''));

    $stmt = db()->prepare('SELECT username FROM akun WHERE username = ?');
    $stmt->bind_param('s', $resetUsername);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif ($target === null) {
        flash('error', 'Username "' . $resetUsername . '" tidak ditemukan.');
    } elseif (strcasecmp($target['username'], current_user()) === 0) {
        flash('error', 'Untuk akunmu sendiri, ganti password lewat menu Akun Saya.');
    } else {
        $temporary = 'AM-' . strtolower(str_replace('-', '', generate_recovery_code()));
        $hash = password_hash($temporary, PASSWORD_DEFAULT);
        $stmt = db()->prepare('UPDATE akun SET password = ?, must_change_password = 1 WHERE username = ?');
        $stmt->bind_param('ss', $hash, $target['username']);
        $stmt->execute();
        $stmt->close();

        $_SESSION['admin_temp_password'] = ['username' => $target['username'], 'password' => $temporary];
        redirect('admin.php#reset-password');
    }
}

// Password sementara hanya ditampilkan sekali.
$tempReveal = $_SESSION['admin_temp_password'] ?? null;
unset($_SESSION['admin_temp_password']);

$admins = db()->query("SELECT username FROM akun WHERE role = 'admin' ORDER BY username")->fetch_all(MYSQLI_ASSOC);
$stats = db()->query(
    "SELECT (SELECT COUNT(*) FROM akun) AS accounts,
            (SELECT COUNT(*) FROM comments) AS comments,
            (SELECT COUNT(*) FROM comments WHERE parent_id IS NOT NULL) AS replies"
)->fetch_assoc();
$latest = db()->query(
    'SELECT c.id, c.username, c.comment, c.created_at, c.page_id, c.parent_id, c.reply_to, a.role
       FROM comments c LEFT JOIN akun a ON a.username = c.username
      ORDER BY c.created_at DESC, c.id DESC
      LIMIT 50'
)->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Panel Admin | Ave Mujica Fan Web';
require APP_ROOT . '/includes/header.php';
?>

<section class="content-card glass-card section-gap">
  <span class="eyebrow">Panel Admin</span>
  <h1 class="admin-title">Halo, <?= h(current_user()) ?></h1>
  <p class="muted">Sebagai admin, kamu bisa menghapus komentar siapa pun, baik dari halaman mana pun maupun dari daftar di bawah, dan membuat akun admin baru.</p>
  <div class="stat-row">
    <div><strong><?= (int) $stats['accounts'] ?></strong><span>Akun</span></div>
    <div><strong><?= (int) $stats['comments'] - (int) $stats['replies'] ?></strong><span>Komentar</span></div>
    <div><strong><?= (int) $stats['replies'] ?></strong><span>Balasan</span></div>
    <div><strong><?= count($admins) ?></strong><span>Admin</span></div>
  </div>
</section>

<section class="content-card glass-card section-gap" id="komentar-terbaru">
  <h2 class="section-title">Komentar Terbaru</h2>
  <?php if (!$latest): ?>
    <p class="empty-state">Belum ada komentar.</p>
  <?php else: ?>
    <div class="admin-comments">
      <?php foreach ($latest as $row): $time = strtotime($row['created_at']); ?>
        <article class="comment-item<?= $row['parent_id'] ? ' comment-item--reply' : '' ?>">
          <div class="comment-item__top">
            <div class="comment-item__who"><?php render_comment_author($row); ?></div>
            <time datetime="<?= h(date('c', $time)) ?>"><?= h(date('d-m-Y H:i', $time)) ?> WIB</time>
          </div>
          <p>
            <?php if (!empty($row['reply_to'])): ?><span class="mention">@<?= h($row['reply_to']) ?></span><?php endif; ?>
            <?= nl2br(h($row['comment'])) ?>
          </p>
          <div class="comment-actions">
            <a class="comment-action" href="<?= h(page_url((int) $row['page_id'])) ?>#komentar-<?= (int) $row['id'] ?>">
              <?= $row['parent_id'] ? 'Balasan' : 'Komentar' ?> di <?= h(page_label((int) $row['page_id'])) ?> ↗
            </a>
            <form class="delete-form" action="hapuskomen.php" method="post"
                  data-confirm="<?= $row['parent_id'] ? 'Hapus balasan ini?' : 'Hapus komentar ini beserta semua balasannya?' ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="comment_id" value="<?= (int) $row['id'] ?>">
              <input type="hidden" name="return" value="admin">
              <button type="submit" class="comment-action comment-action--danger">Hapus</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="content-card glass-card section-gap" id="reset-password">
  <h2 class="section-title">Reset Password Pengguna</h2>
  <p class="muted">Untuk pengguna yang lupa password dan tidak punya kode pemulihan. Sistem membuat password sementara; berikan ke pengguna itu secara pribadi. Saat login, pengguna wajib menggantinya.</p>
  <?php if ($tempReveal): ?>
    <div class="temp-password" role="status">
      Password sementara untuk <strong><?= h($tempReveal['username']) ?></strong>:
      <code id="temp-password"><?= h($tempReveal['password']) ?></code>
      <button type="button" class="comment-action" data-copy="#temp-password">Salin</button>
      <small>Hanya ditampilkan sekali ini. Password lama pengguna sudah tidak berlaku.</small>
    </div>
  <?php endif; ?>
  <form class="admin-form admin-form--inline" method="post" action="admin.php#reset-password"
        data-confirm="Reset password akun ini? Password lamanya langsung tidak berlaku.">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="reset_password">
    <label for="reset-username" class="sr-only">Username</label>
    <input type="text" id="reset-username" name="username" value="<?= h($resetUsername) ?>" placeholder="Username pengguna" required>
    <button type="submit" class="primary-btn">Buat Password Sementara</button>
  </form>
</section>

<div class="content-grid">
  <section class="content-card glass-card" id="buat-admin">
    <h2 class="section-title">Buat Akun Admin</h2>
    <p class="muted">Username wajib berawalan <strong>admin</strong>, contoh <code>admintimoris</code>. Tentukan password sendiri (minimal 8 karakter). Setelah dibuat, kode pemulihan akun itu ditampilkan sekali.</p>
    <form class="admin-form" method="post" action="admin.php#buat-admin" autocomplete="off">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create_admin">
      <label for="admin-username">Username</label>
      <input type="text" id="admin-username" name="username" value="<?= h($newUsername) ?>"
             placeholder="admintimoris" pattern="[Aa][Dd][Mm][Ii][Nn][A-Za-z0-9_.\-]{1,25}" maxlength="30" required>
      <label for="admin-password">Password</label>
      <input type="password" id="admin-password" name="password" minlength="8" autocomplete="new-password" required>
      <label for="admin-password2">Ulangi password</label>
      <input type="password" id="admin-password2" name="password_confirm" minlength="8" autocomplete="new-password" required>
      <button type="submit" class="primary-btn">Buat Admin</button>
    </form>
  </section>

  <aside class="side-card glass-card" id="admin-list">
    <h2 class="section-title">Daftar Admin</h2>
    <ul class="admin-list">
      <?php foreach ($admins as $admin): $slug = admin_member_slug($admin['username']); ?>
        <li>
          <span class="admin-badge<?= $slug ? ' admin-badge--' . h($slug) : '' ?>">Admin</span>
          <strong><?= h($admin['username']) ?></strong>
          <?php if ($slug): ?><small><?= h(members()[$slug]['name']) ?> · <?= h(members()[$slug]['stage_name']) ?></small><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </aside>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>

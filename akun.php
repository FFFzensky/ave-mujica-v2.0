<?php
/*
 * Akun Saya: ganti password + buat kode pemulihan baru.
 */
require __DIR__ . '/includes/bootstrap.php';

if (!is_logged_in()) {
    flash('error', 'Silakan login dulu.');
    redirect('login.php');
}

$account = current_account();
$username = $account['username'];

/** Cek password saat ini (dipakai sebelum ganti password / buat kode baru). */
function verify_current_password(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT password FROM akun WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row !== null && password_verify($password, $row['password']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $current = (string) ($_POST['current_password'] ?? '');
    $attemptKey = 'akun:' . strtolower($username);

    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif (failed_attempts($attemptKey) >= 5) {
        flash('error', 'Terlalu banyak percobaan password salah. Coba lagi dalam ' . ATTEMPT_WINDOW_MINUTES . ' menit.');
    } elseif (!verify_current_password($username, $current)) {
        record_failed_attempt($attemptKey);
        flash('error', 'Password saat ini salah.');
    } elseif ($action === 'change_password') {
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');
        $min = is_admin() ? 8 : 6;
        if (($problem = password_problem($password, $confirm, $min)) !== null) {
            flash('error', $problem);
        } elseif ($password === $current) {
            flash('error', 'Password baru harus berbeda dari password lama.');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE akun SET password = ?, must_change_password = 0 WHERE username = ?');
            $stmt->bind_param('ss', $hash, $username);
            $stmt->execute();
            $stmt->close();
            clear_failed_attempts($attemptKey);
            session_regenerate_id(true);
            flash('success', 'Password berhasil diganti.');
            redirect('akun.php');
        }
    } elseif ($action === 'new_recovery') {
        clear_failed_attempts($attemptKey);
        $code = issue_recovery_code($username);
        flash('success', 'Kode pemulihan baru dibuat. Kode lama sudah tidak berlaku.');
        reveal_recovery_code($username, $code, 'akun.php');
    }
    redirect('akun.php' . ($action === 'new_recovery' ? '#kode-pemulihan' : '#ganti-password'));
}

$pageTitle = 'Akun Saya | Ave Mujica Fan Web';
require APP_ROOT . '/includes/header.php';
?>

<section class="page-banner glass-card">
  <span class="eyebrow">Akun Saya</span>
  <h1><?= h($username) ?></h1>
  <p>
    <?= is_admin() ? 'Admin Ave Mujica Fan Web.' : 'Anggota Ave Mujica Fan Web.' ?>
    Di sini kamu bisa mengganti password dan membuat kode pemulihan.
  </p>
</section>

<?php if (!empty($account['must_change_password'])): ?>
  <div class="flash flash--error" role="alert">
    Password kamu baru saja direset oleh admin (password sementara). Ganti dengan password milikmu sendiri di bawah ini.
  </div>
<?php endif; ?>

<?php if (empty($account['has_recovery'])): ?>
  <div class="flash flash--warning" role="status">
    Kamu belum punya kode pemulihan. Buat sekarang supaya bisa mengganti password sendiri kalau suatu saat lupa.
    <a href="#kode-pemulihan">Buat kode pemulihan →</a>
  </div>
<?php endif; ?>

<div class="content-grid">
  <section class="content-card glass-card" id="ganti-password">
    <h2 class="section-title">Ganti Password</h2>
    <form class="admin-form" method="post" action="akun.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="change_password">
      <label for="current-password">Password saat ini</label>
      <input type="password" id="current-password" name="current_password" autocomplete="current-password" required>
      <label for="new-password">Password baru</label>
      <input type="password" id="new-password" name="password" minlength="<?= is_admin() ? 8 : 6 ?>" maxlength="72" autocomplete="new-password" required>
      <label for="new-password2">Ulangi password baru</label>
      <input type="password" id="new-password2" name="password_confirm" minlength="<?= is_admin() ? 8 : 6 ?>" maxlength="72" autocomplete="new-password" required>
      <button type="submit" class="primary-btn">Simpan Password</button>
    </form>
  </section>

  <aside class="side-card glass-card" id="kode-pemulihan">
    <h2 class="section-title">Kode Pemulihan</h2>
    <p class="muted">
      Status:
      <?php if (!empty($account['has_recovery'])): ?>
        <strong class="status-ok">✓ Sudah punya kode</strong>
      <?php else: ?>
        <strong class="status-warn">Belum ada</strong>
      <?php endif; ?>
    </p>
    <p class="muted">Kode pemulihan dipakai di halaman <a href="lupa-password.php">Lupa Password</a>. Lupa kodenya? Buat yang baru di sini; kode lama otomatis tidak berlaku.</p>
    <form class="admin-form" method="post" action="akun.php"
          data-confirm="Buat kode pemulihan baru? Kode lama tidak akan berlaku lagi.">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="new_recovery">
      <label for="current-password-2">Password saat ini</label>
      <input type="password" id="current-password-2" name="current_password" autocomplete="current-password" required>
      <button type="submit" class="secondary-btn">Buat Kode Pemulihan Baru</button>
    </form>
  </aside>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>

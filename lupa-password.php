<?php
/*
 * Lupa password: username + kode pemulihan (didapat saat daftar) -> password baru.
 * Setelah berhasil, kode lama hangus dan pengguna mendapat kode pemulihan baru.
 * Dibatasi 5 percobaan gagal per username per IP dalam 15 menit.
 */
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/auth_layout.php';

if (is_logged_in()) {
    redirect('akun.php');
}

const RESET_MAX_PER_USER = 5;
const RESET_MAX_PER_IP = 20;

$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $code = normalize_recovery_code((string) ($_POST['recovery_code'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');
    $attemptKey = 'reset:' . strtolower($username);

    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif ($username === '' || $code === '') {
        flash('error', 'Username dan kode pemulihan wajib diisi.');
    } elseif (failed_attempts($attemptKey) >= RESET_MAX_PER_USER || failed_attempts('', true) >= RESET_MAX_PER_IP) {
        flash('error', 'Terlalu banyak percobaan. Coba lagi dalam ' . ATTEMPT_WINDOW_MINUTES . ' menit.');
    } elseif (($problem = password_problem($password, $confirm)) !== null) {
        flash('error', $problem);
    } else {
        $stmt = db()->prepare('SELECT username, recovery_hash FROM akun WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Pesan error dibuat sama supaya orang tidak bisa menebak username/kode mana yang benar.
        if ($row && $row['recovery_hash'] && password_verify($code, $row['recovery_hash'])) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE akun SET password = ?, must_change_password = 0 WHERE username = ?');
            $stmt->bind_param('ss', $hash, $row['username']);
            $stmt->execute();
            $stmt->close();
            clear_failed_attempts($attemptKey);

            $newCode = issue_recovery_code($row['username']);
            flash('success', 'Password berhasil diganti. Ini kode pemulihan BARU kamu (kode lama sudah tidak berlaku).');
            reveal_recovery_code($row['username'], $newCode, 'login.php');
        }

        record_failed_attempt($attemptKey);
        flash('error', 'Username atau kode pemulihan salah.');
    }
}

auth_page_start('Lupa Password', 'Pulihkan akun', 'Masukkan username dan kode pemulihan yang kamu dapat saat membuat akun, lalu buat password baru.');
?>
        <form method="post" action="lupa-password.php" autocomplete="off">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
              <input type="text" id="username" name="username" value="<?= h($username) ?>" autocomplete="username" required>
            </div>
          </div>

          <div class="form-group">
            <label for="recovery_code">Kode pemulihan</label>
            <div class="input-wrap">
              <input type="text" id="recovery_code" name="recovery_code" placeholder="XXXX-XXXX-XXXX"
                     autocapitalize="characters" spellcheck="false" maxlength="20" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password baru</label>
            <div class="input-wrap">
              <input type="password" id="password" name="password" minlength="6" maxlength="72" autocomplete="new-password" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirm">Ulangi password baru</label>
            <div class="input-wrap">
              <input type="password" id="password_confirm" name="password_confirm" minlength="6" maxlength="72" autocomplete="new-password" required>
            </div>
          </div>

          <button type="submit" class="auth-btn">Ganti Password</button>
        </form>
<?php
auth_page_end('Tidak punya kode pemulihan? Minta admin mereset password-mu.<br>Ingat password? <a href="login.php">Kembali ke login</a>');

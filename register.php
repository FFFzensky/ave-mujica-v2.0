<?php
/* Registrasi akun (v1: Bikin.php; Au2.php yang menyimpan password tanpa hash sudah dihapus). */
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/auth_layout.php';

if (is_logged_in()) {
    redirect('index.php');
}

$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');

    $error = null;
    if (!csrf_is_valid()) {
        $error = 'Sesi formulir kedaluwarsa. Silakan coba lagi.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
        $error = 'Username 3–30 karakter, hanya huruf, angka, titik, minus, atau garis bawah.';
    } elseif (is_reserved_username($username)) {
        $error = 'Username berawalan "admin" khusus pengelola web. Pilih username lain.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $stmt = db()->prepare('SELECT id FROM akun WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $taken = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($taken) {
            $error = 'Username sudah dipakai. Coba username lain.';
        }
    }

    if ($error !== null) {
        flash('error', $error);
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO akun (username, password) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $hash);
        $stmt->execute();
        $stmt->close();

        // Kode pemulihan untuk lupa password: wajib dicatat, ditampilkan sekali saja.
        $code = issue_recovery_code($username);
        flash('success', 'Akun ' . $username . ' berhasil dibuat. Catat kode pemulihanmu dulu, lalu login.');
        reveal_recovery_code($username, $code, 'login.php');
    }
}

auth_page_start('Registrasi', 'Create New Account', 'Isi data di bawah untuk membuat akun baru. Setelah itu kamu akan mendapat kode pemulihan yang wajib dicatat (untuk lupa password).');
?>
        <form method="post" action="register.php" autocomplete="on">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
              <input type="text" id="username" name="username" value="<?= h($username) ?>"
                     placeholder="3–30 karakter" pattern="[A-Za-z0-9_.\-]{3,30}" maxlength="30"
                     autocomplete="username" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
              <input type="password" id="password" name="password" placeholder="Minimal 6 karakter"
                     minlength="6" autocomplete="new-password" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password_confirm">Ulangi password</label>
            <div class="input-wrap">
              <input type="password" id="password_confirm" name="password_confirm" placeholder="Ketik ulang password"
                     minlength="6" autocomplete="new-password" required>
            </div>
          </div>

          <button type="submit" class="auth-btn">Buat Akun</button>
        </form>
<?php
auth_page_end('Sudah punya akun? <a href="login.php">Kembali ke login</a>');

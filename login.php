<?php
/* Form login + proses login dalam satu file (v1: login.php + Au.php). */
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/auth_layout.php';

// Halaman asal (page_id), supaya setelah login kembali ke halaman yang sama.
$from = (int) ($_GET['from'] ?? $_POST['from'] ?? 0);
$back = (page_exists($from) ? page_url($from) : 'index.php');

if (is_logged_in()) {
    redirect($back);
}

$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif ($username === '' || $password === '') {
        flash('error', 'Username dan password wajib diisi.');
    } else {
        $stmt = db()->prepare('SELECT username, password FROM akun WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Pesan error dibuat sama supaya orang tidak bisa menebak username mana yang terdaftar.
        if ($row && password_verify($password, $row['password'])) {
            session_regenerate_id(true); // cegah session fixation
            $_SESSION['login_user'] = $row['username'];
            flash('success', 'Berhasil login. Halo, ' . $row['username'] . '!');
            redirect($back);
        }
        flash('error', 'Username atau password salah.');
    }
}

auth_page_start('Login', 'Ave Mujica Fan Web · Fanweb Indonesia', 'Masuk untuk menulis dan menghapus komentarmu.');
?>
        <form method="post" action="login.php" autocomplete="on">
          <?= csrf_field() ?>
          <input type="hidden" name="from" value="<?= $from ?>">
          <div class="form-group">
            <label for="username">Username</label>
            <div class="input-wrap">
              <input type="text" id="username" name="username" value="<?= h($username) ?>"
                     placeholder="Masukkan username" autocomplete="username" required>
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
              <input type="password" id="password" name="password" placeholder="Masukkan password"
                     autocomplete="current-password" required>
            </div>
          </div>

          <button type="submit" class="auth-btn">Masuk Sekarang</button>
        </form>
<?php
auth_page_end('Belum punya akun? <a href="register.php">Buat akun di sini</a>');

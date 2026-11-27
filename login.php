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

/*
 * Pembatasan percobaan login (anti tebak password):
 * maks. 5 kali gagal per username dari IP yang sama, atau 20 kali gagal per IP, dalam 15 menit.
 */
const LOGIN_MAX_PER_USER = 5;
const LOGIN_MAX_PER_IP = 20;

$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $attemptKey = $username;

    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif ($username === '' || $password === '') {
        flash('error', 'Username dan password wajib diisi.');
    } elseif (failed_attempts($attemptKey) >= LOGIN_MAX_PER_USER || failed_attempts('', true) >= LOGIN_MAX_PER_IP) {
        flash('error', 'Terlalu banyak percobaan login yang gagal. Coba lagi dalam ' . ATTEMPT_WINDOW_MINUTES . ' menit, atau pakai Lupa Password.');
    } else {
        $stmt = db()->prepare(
            'SELECT username, password, must_change_password, (recovery_hash IS NOT NULL) AS has_recovery FROM akun WHERE username = ?'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Pesan error dibuat sama supaya orang tidak bisa menebak username mana yang terdaftar.
        if ($row && password_verify($password, $row['password'])) {
            clear_failed_attempts($attemptKey);
            session_regenerate_id(true); // cegah session fixation
            $_SESSION['login_user'] = $row['username'];

            if (!empty($row['must_change_password'])) {
                flash('error', 'Kamu login dengan password sementara dari admin. Ganti password sekarang.');
                redirect('akun.php#ganti-password');
            }

            flash('success', 'Berhasil login. Halo, ' . $row['username'] . '!');
            if (empty($row['has_recovery'])) {
                flash('warning', 'Akunmu belum punya kode pemulihan untuk lupa password. Buat di menu Akun Saya (klik namamu di pojok kanan atas).');
            }
            redirect($back);
        }

        record_failed_attempt($attemptKey);
        flash('error', 'Username atau password salah.');
    }
}

auth_page_start('Login', 'Ave Mujica Fan Web · Fanweb Indonesia', 'Masuk untuk menulis, membalas, dan menghapus komentarmu.');
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

          <p class="auth-forgot"><a href="lupa-password.php">Lupa password?</a></p>

          <button type="submit" class="auth-btn">Masuk Sekarang</button>
        </form>
<?php
auth_page_end('Belum punya akun? <a href="register.php">Buat akun di sini</a>');

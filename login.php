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

/*
 * Pembatasan percobaan login (anti tebak password):
 * maks. 5 kali gagal per username dari IP yang sama, atau 20 kali gagal per IP, dalam 15 menit.
 */
const LOGIN_WINDOW_MINUTES = 15;
const LOGIN_MAX_PER_USER = 5;
const LOGIN_MAX_PER_IP = 20;

function login_failures(string $ip, ?string $username): int
{
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);
    if ($username === null) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?');
        $stmt->bind_param('ss', $ip, $since);
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND username = ? AND attempted_at > ?');
        $stmt->bind_param('sss', $ip, $username, $since);
    }
    $stmt->execute();
    $count = (int) $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $ip = client_ip();

    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif ($username === '' || $password === '') {
        flash('error', 'Username dan password wajib diisi.');
    } elseif (login_failures($ip, $username) >= LOGIN_MAX_PER_USER || login_failures($ip, null) >= LOGIN_MAX_PER_IP) {
        flash('error', 'Terlalu banyak percobaan login yang gagal. Coba lagi dalam ' . LOGIN_WINDOW_MINUTES . ' menit.');
    } else {
        $stmt = db()->prepare('SELECT username, password FROM akun WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Pesan error dibuat sama supaya orang tidak bisa menebak username mana yang terdaftar.
        if ($row && password_verify($password, $row['password'])) {
            $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip = ? AND username = ?');
            $stmt->bind_param('ss', $ip, $username);
            $stmt->execute();
            $stmt->close();

            session_regenerate_id(true); // cegah session fixation
            $_SESSION['login_user'] = $row['username'];
            flash('success', 'Berhasil login. Halo, ' . $row['username'] . '!');
            redirect($back);
        }

        $now = date('Y-m-d H:i:s');
        $stmt = db()->prepare('INSERT INTO login_attempts (ip, username, attempted_at) VALUES (?, ?, ?)');
        $loggedName = substr($username, 0, 50);
        $stmt->bind_param('sss', $ip, $loggedName, $now);
        $stmt->execute();
        $stmt->close();
        // Bersihkan catatan lama (lebih dari 1 hari).
        db()->query("DELETE FROM login_attempts WHERE attempted_at < '" . date('Y-m-d H:i:s', time() - 86400) . "'");

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

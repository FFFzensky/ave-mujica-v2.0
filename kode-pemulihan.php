<?php
/*
 * Menampilkan kode pemulihan SEKALI (setelah daftar, setelah reset, atau saat membuat kode baru).
 * Kode asli tidak disimpan di database (hanya hash-nya), jadi tidak bisa dilihat lagi nanti.
 */
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/auth_layout.php';

$reveal = $_SESSION['recovery_reveal'] ?? null;
if (!$reveal) {
    redirect(is_logged_in() ? 'akun.php' : 'login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid()) {
        flash('error', 'Sesi formulir kedaluwarsa. Silakan coba lagi.');
    } elseif (empty($_POST['saved'])) {
        flash('error', 'Centang dulu "Saya sudah mencatat kode ini".');
    } else {
        unset($_SESSION['recovery_reveal']);
        redirect($reveal['next']);
    }
}

auth_page_start('Kode Pemulihan', 'Simpan baik-baik', 'Kode ini dipakai kalau kamu lupa password. Kode hanya ditampilkan SEKALI ini, jadi catat atau simpan sekarang.');
?>
        <div class="recovery-box">
          <span class="recovery-box__label">Username</span>
          <strong class="recovery-box__user"><?= h($reveal['username']) ?></strong>
          <span class="recovery-box__label">Kode pemulihan</span>
          <code class="recovery-box__code" id="recovery-code"><?= h($reveal['code']) ?></code>
          <div class="recovery-box__actions">
            <button type="button" class="auth-btn auth-btn--ghost" data-copy="#recovery-code">Salin kode</button>
            <button type="button" class="auth-btn auth-btn--ghost"
                    data-download-text="Ave Mujica Fan Web (Fanweb Indonesia)&#10;Username: <?= h($reveal['username']) ?>&#10;Kode pemulihan: <?= h($reveal['code']) ?>&#10;&#10;Pakai kode ini di halaman Lupa Password. Kode lama otomatis tidak berlaku setelah dipakai."
                    data-download-name="kode-pemulihan-<?= h($reveal['username']) ?>.txt">Unduh .txt</button>
          </div>
          <ul class="recovery-box__tips">
            <li>Simpan di catatan HP, foto layar, atau tulis di kertas.</li>
            <li>Jangan berikan ke siapa pun, termasuk admin.</li>
            <li>Setelah dipakai untuk reset password, kamu akan mendapat kode baru.</li>
          </ul>
        </div>

        <form method="post" action="kode-pemulihan.php">
          <?= csrf_field() ?>
          <label class="check-row">
            <input type="checkbox" name="saved" value="1" required>
            <span>Saya sudah mencatat / menyimpan kode ini</span>
          </label>
          <button type="submit" class="auth-btn">Lanjut</button>
        </form>
        <script src="assets/js/app.js?v=2.3.0"></script>
<?php
auth_page_end('Kode bisa dibuat ulang kapan saja di halaman <strong>Akun Saya</strong> setelah login.');

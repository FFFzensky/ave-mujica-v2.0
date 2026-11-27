<?php

/** Escape teks sebelum ditampilkan di HTML (mencegah XSS). */
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit();
}

/* ---------- Login & role ---------- */

/**
 * Akun yang sedang login, dibaca dari database sekali per request.
 * Kalau akunnya sudah dihapus, session otomatis dianggap logout.
 * Role (user/admin) selalu diambil dari database, jadi perubahan role langsung berlaku.
 */
function current_account(): ?array
{
    static $loaded = false;
    static $account = null;

    if ($loaded) {
        return $account;
    }
    $loaded = true;

    $username = $_SESSION['login_user'] ?? '';
    if ($username === '') {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT username, role, must_change_password, (recovery_hash IS NOT NULL) AS has_recovery FROM akun WHERE username = ?'
    );
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $account = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    if ($account === null) {
        unset($_SESSION['login_user']);
    }
    return $account;
}

function current_user(): ?string
{
    return current_account()['username'] ?? null;
}

function is_logged_in(): bool
{
    return current_account() !== null;
}

function is_admin(): bool
{
    return (current_account()['role'] ?? '') === 'admin';
}

/* ---------- Kode pemulihan (untuk lupa password) ---------- */

/** Kode acak 12 karakter, contoh "K7QM-2XPA-9DHT" (tanpa huruf/angka yang mirip: 0/O, 1/I/L). */
function generate_recovery_code(): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 12; $i++) {
        $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return implode('-', str_split($code, 4));
}

/** Samakan format input: huruf besar, tanpa spasi/strip. "k7qm 2xpa-9dht" -> "K7QM2XPA9DHT". */
function normalize_recovery_code(string $code): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
}

/** Buat kode pemulihan baru untuk akun, simpan hash-nya, dan kembalikan kode aslinya (ditampilkan sekali). */
function issue_recovery_code(string $username): string
{
    $code = generate_recovery_code();
    $hash = password_hash(normalize_recovery_code($code), PASSWORD_DEFAULT);
    $stmt = db()->prepare('UPDATE akun SET recovery_hash = ? WHERE username = ?');
    $stmt->bind_param('ss', $hash, $username);
    $stmt->execute();
    $stmt->close();
    return $code;
}

/** Simpan kode di session lalu tampilkan sekali di kode-pemulihan.php. */
function reveal_recovery_code(string $username, string $code, string $next): void
{
    $_SESSION['recovery_reveal'] = ['username' => $username, 'code' => $code, 'next' => $next];
    redirect('kode-pemulihan.php');
}

/* ---------- Pembatasan percobaan (login & lupa password) ---------- */

const ATTEMPT_WINDOW_MINUTES = 15;

function failed_attempts(string $key, bool $perIpOnly = false): int
{
    $ip = client_ip();
    $since = date('Y-m-d H:i:s', time() - ATTEMPT_WINDOW_MINUTES * 60);
    if ($perIpOnly) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?');
        $stmt->bind_param('ss', $ip, $since);
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND username = ? AND attempted_at > ?');
        $stmt->bind_param('sss', $ip, $key, $since);
    }
    $stmt->execute();
    $count = (int) $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $count;
}

function record_failed_attempt(string $key): void
{
    $ip = client_ip();
    $key = substr($key, 0, 50);
    $now = date('Y-m-d H:i:s');
    $stmt = db()->prepare('INSERT INTO login_attempts (ip, username, attempted_at) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $ip, $key, $now);
    $stmt->execute();
    $stmt->close();
    db()->query("DELETE FROM login_attempts WHERE attempted_at < '" . date('Y-m-d H:i:s', time() - 86400) . "'");
}

function clear_failed_attempts(string $key): void
{
    $ip = client_ip();
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip = ? AND username = ?');
    $stmt->bind_param('ss', $ip, $key);
    $stmt->execute();
    $stmt->close();
}

/** Validasi password baru; kembalikan pesan error atau null. */
function password_problem(string $password, string $confirm, int $min = 6): ?string
{
    if (strlen($password) < $min) {
        return 'Password minimal ' . $min . ' karakter.';
    }
    if (strlen($password) > 72) {
        return 'Password maksimal 72 karakter.';
    }
    if ($password !== $confirm) {
        return 'Konfirmasi password tidak sama.';
    }
    return null;
}

/** Username berawalan "admin" khusus pengelola, tidak bisa didaftarkan lewat form biasa. */
function is_reserved_username(string $username): bool
{
    return stripos($username, 'admin') === 0;
}

/**
 * Admin seperti "admintimoris" atau "adminumiri" dipasangkan ke member-nya
 * (cocok dengan slug atau stage name), untuk warna badge admin di komentar.
 */
function admin_member_slug(string $username): ?string
{
    $name = strtolower(substr($username, 5));
    foreach (members() as $slug => $member) {
        if ($name === $slug || $name === strtolower($member['stage_name'])) {
            return $slug;
        }
    }
    return null;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/** URL dasar situs, untuk link absolut (Open Graph, kalender). */
function base_url(): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = preg_replace('/[^A-Za-z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

/* ---------- Pesan sekali tampil (flash) ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

/* ---------- CSRF: memastikan form benar-benar dikirim dari web kita ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_is_valid(): bool
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}

/* ---------- Halaman & member ---------- */

function members(): array
{
    static $members = null;
    if ($members === null) {
        $members = require APP_ROOT . '/data/members.php';
    }
    return $members;
}

function static_pages(): array
{
    static $pages = null;
    if ($pages === null) {
        $pages = require APP_ROOT . '/data/pages.php';
    }
    return $pages;
}

/**
 * Semua halaman yang punya kolom komentar: page_id => ['url' => ..., 'label' => ...].
 * Inilah "trik efisien"-nya: satu daftar page_id -> URL.
 * hapuskomen.php dan komen.php cukup satu file, tujuan redirect diambil dari sini.
 * Karena daftarnya tetap, orang tidak bisa menyisipkan URL lain (aman dari open redirect).
 */
function page_registry(): array
{
    static $registry = null;
    if ($registry === null) {
        $registry = [];
        foreach (static_pages() as $page) {
            $registry[$page['page_id']] = ['url' => $page['url'], 'label' => $page['label']];
        }
        foreach (members() as $slug => $member) {
            $registry[$member['page_id']] = ['url' => 'member.php?m=' . $slug, 'label' => $member['name']];
        }
    }
    return $registry;
}

function page_url(int $pageId): string
{
    return page_registry()[$pageId]['url'] ?? 'index.php';
}

function page_exists(int $pageId): bool
{
    return isset(page_registry()[$pageId]);
}

function page_label(int $pageId): string
{
    return page_registry()[$pageId]['label'] ?? 'Halaman ' . $pageId;
}

/**
 * Menu situs: dipakai bar menu atas dan bar menu bawah (HP).
 * 'active' ditentukan dari file yang sedang dibuka.
 */
function site_nav_items(): array
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $items = [
        ['label' => 'Beranda',       'url' => 'index.php',         'icon' => 'home',   'match' => ['index.php'],          'bottom' => true],
        ['label' => 'Member',        'url' => 'index.php#member',  'icon' => 'member', 'match' => ['member.php'],         'bottom' => true],
        ['label' => 'Musik',         'url' => 'musik.php',         'icon' => 'music',  'match' => ['musik.php'],          'bottom' => true],
        ['label' => 'Live',          'url' => 'live.php',          'icon' => 'live',   'match' => ['live.php'],           'bottom' => true],
        ['label' => 'Galeri',        'url' => 'galeri.php',        'icon' => 'galeri', 'match' => ['galeri.php'],         'bottom' => true],
        ['label' => 'Pengisi Suara', 'url' => 'pengisi-suara.php', 'icon' => 'mic',    'match' => ['pengisi-suara.php'],  'bottom' => false],
    ];
    if (is_admin()) {
        $items[] = ['label' => '⚙ Panel Admin', 'url' => 'admin.php', 'icon' => 'admin', 'match' => ['admin.php'], 'bottom' => false];
    }
    foreach ($items as &$item) {
        $item['active'] = in_array($script, $item['match'], true);
    }
    return $items;
}

/** Ikon garis sederhana untuk bar menu bawah. */
function nav_icon(string $name): string
{
    $paths = [
        'home'   => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v9.5h13V10"/><path d="M10 19.5v-5h4v5"/>',
        'member' => '<circle cx="9" cy="8.5" r="3.2"/><path d="M3.5 19c.6-3.2 2.8-5 5.5-5s4.9 1.8 5.5 5"/><circle cx="17" cy="9.5" r="2.4"/><path d="M15.8 14.2c2.4.1 4.1 1.7 4.7 4.8"/>',
        'music'  => '<path d="M9 18.5V6l11-2v12.5"/><circle cx="6.5" cy="18.5" r="2.5"/><circle cx="17.5" cy="16.5" r="2.5"/>',
        'live'   => '<rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/><path d="m10.5 13.5 3.5 2-3.5 2z"/>',
        'galeri' => '<rect x="3.5" y="4.5" width="17" height="15" rx="2.5"/><circle cx="9" cy="9.5" r="1.8"/><path d="m4 17 5-4.5 4 3.5 3-2.5 4.5 4"/>',
        'mic'    => '<rect x="9" y="3.5" width="6" height="11" rx="3"/><path d="M5.5 11.5a6.5 6.5 0 0 0 13 0M12 18v3"/>',
        'admin'  => '<path d="M12 3.5 19 6v5.5c0 4.3-2.9 7.6-7 9-4.1-1.4-7-4.7-7-9V6z"/>',
    ];
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . ($paths[$name] ?? $paths['home']) . '</svg>';
}

/* ---------- Sosial media ---------- */

/** Kenali platform & handle dari URL, contoh https://x.com/Kanon_Takao -> ['x', 'X', '@Kanon_Takao']. */
function social_info(string $url): array
{
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $host = preg_replace('/^www\./', '', $host);
    $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
    $handle = $path !== '' ? '@' . explode('/', $path)[0] : $host;

    if ($host === 'instagram.com') {
        return ['platform' => 'instagram', 'label' => 'Instagram', 'handle' => $handle];
    }
    if ($host === 'x.com' || $host === 'twitter.com') {
        return ['platform' => 'x', 'label' => 'X', 'handle' => $handle];
    }
    return ['platform' => 'web', 'label' => 'Situs', 'handle' => $host];
}

/** Ikon SVG kecil (inline, tanpa library). */
function social_icon(string $platform): string
{
    $icons = [
        'instagram' => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 7.3A4.7 4.7 0 1 0 16.7 12 4.7 4.7 0 0 0 12 7.3Zm0 7.7a3 3 0 1 1 3-3 3 3 0 0 1-3 3Zm6-7.9a1.1 1.1 0 1 1-1.1-1.1A1.1 1.1 0 0 1 18 7.1ZM21.9 8.2a5.4 5.4 0 0 0-1.5-3.8A5.4 5.4 0 0 0 16.6 3c-1.5-.1-6-.1-7.5 0a5.4 5.4 0 0 0-3.8 1.4A5.4 5.4 0 0 0 3.9 8.2c-.1 1.5-.1 6 0 7.5a5.4 5.4 0 0 0 1.4 3.8 5.4 5.4 0 0 0 3.8 1.5c1.5.1 6 .1 7.5 0a5.4 5.4 0 0 0 3.8-1.5 5.4 5.4 0 0 0 1.5-3.8c.1-1.5.1-6 0-7.5Zm-2 9.1a3 3 0 0 1-1.7 1.7c-1.2.5-4 .4-5.3.4s-4.1.1-5.3-.4a3 3 0 0 1-1.7-1.7c-.5-1.2-.4-4-.4-5.3s-.1-4.1.4-5.3a3 3 0 0 1 1.7-1.7c1.2-.5 4-.4 5.3-.4s4.1-.1 5.3.4a3 3 0 0 1 1.7 1.7c.5 1.2.4 4 .4 5.3s.1 4.1-.4 5.3Z"/></svg>',
        'x'         => '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.2-8.3L1.8 3h6.4l4.4 5.8L17.8 3Zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5Z"/></svg>',
        'web'       => '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm6.9 6h-3a15.7 15.7 0 0 0-1.4-3.6A8 8 0 0 1 18.9 8ZM12 4a14 14 0 0 1 1.9 4h-3.8A14 14 0 0 1 12 4ZM4.3 14a7.8 7.8 0 0 1 0-4h3.4a16.5 16.5 0 0 0 0 4Zm.8 2h3a15.7 15.7 0 0 0 1.4 3.6A8 8 0 0 1 5.1 16Zm3-8h-3a8 8 0 0 1 4.3-3.6A15.7 15.7 0 0 0 8.1 8ZM12 20a14 14 0 0 1-1.9-4h3.8A14 14 0 0 1 12 20Zm2.3-6H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4Zm.3 5.6a15.7 15.7 0 0 0 1.4-3.6h3a8 8 0 0 1-4.4 3.6Zm1.7-5.6a16.5 16.5 0 0 0 0-4h3.4a7.8 7.8 0 0 1 0 4Z"/></svg>',
    ];
    return $icons[$platform] ?? $icons['web'];
}

/** Hitung panjang teks multibyte (Jepang/emoji) dengan benar. */
function text_length(string $text): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }
    return (int) preg_match_all('/./us', $text);
}

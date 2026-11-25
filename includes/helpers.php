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

/* ---------- Login ---------- */

function current_user(): ?string
{
    $user = $_SESSION['login_user'] ?? '';
    return $user !== '' ? $user : null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
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

/**
 * Inilah "trik efisien"-nya: satu daftar page_id -> URL.
 * hapuskomen.php dan komen.php cukup satu file, tujuan redirect diambil dari sini.
 * Karena daftarnya tetap, orang tidak bisa menyisipkan URL lain (aman dari open redirect).
 */
function page_url(int $pageId): string
{
    if ($pageId === 0) {
        return 'index.php';
    }
    foreach (members() as $slug => $member) {
        if ($member['page_id'] === $pageId) {
            return 'member.php?m=' . $slug;
        }
    }
    return 'index.php';
}

function page_exists(int $pageId): bool
{
    if ($pageId === 0) {
        return true;
    }
    foreach (members() as $member) {
        if ($member['page_id'] === $pageId) {
            return true;
        }
    }
    return false;
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

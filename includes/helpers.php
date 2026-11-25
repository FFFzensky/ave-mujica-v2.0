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

/** Hitung panjang teks multibyte (Jepang/emoji) dengan benar. */
function text_length(string $text): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($text, 'UTF-8');
    }
    return (int) preg_match_all('/./us', $text);
}

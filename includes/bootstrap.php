<?php
/*
 * Dipanggil di baris pertama setiap halaman:  require __DIR__ . '/includes/bootstrap.php';
 * Isinya: setting dasar, session, koneksi database (satu kali saja), dan helper.
 */

define('APP_ROOT', dirname(__DIR__));

$configFile = APP_ROOT . '/config.local.php';
if (!is_file($configFile)) {
    $configFile = APP_ROOT . '/config.example.php';
}
$config = require $configFile;

date_default_timezone_set('Asia/Jakarta');

if (!empty($config['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require_once APP_ROOT . '/includes/helpers.php';
require_once APP_ROOT . '/includes/comments.php';
require_once APP_ROOT . '/includes/content.php';

/*
 * Koneksi database dibuat sekali di sini dan dipakai bersama lewat db().
 * Kalau gagal, pengunjung melihat pesan singkat, bukan detail error server.
 */
function db(): mysqli
{
    static $conn = null;
    global $config;

    if ($conn instanceof mysqli) {
        return $conn;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name'],
            (int) ($config['db_port'] ?? 3306)
        );
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        if (!empty($config['debug'])) {
            exit('Koneksi database gagal: ' . h($e->getMessage()));
        }
        exit('Maaf, server sedang bermasalah. Coba lagi nanti.');
    }

    return $conn;
}

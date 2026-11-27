<?php
/*
 * Header bersama untuk semua halaman.
 * Variabel yang bisa di-set sebelum include:
 *   $pageTitle  (string)     judul tab browser
 *   $colors     (array|null) warna aksen member, lihat data/members.php
 *   $metaDescription, $ogImage  untuk preview link
 * Menu (atas & bawah di HP) sama untuk semua halaman, lihat site_nav_items().
 */
$pageTitle = $pageTitle ?? 'Ave Mujica Fan Web';
$navItems  = site_nav_items();
$metaDescription = $metaDescription
    ?? 'Fanweb Indonesia untuk Ave Mujica: profil member, music video, jadwal live, pengisi suara, dan komentar penggemar.';
// Gambar untuk preview link (WhatsApp, Discord, X). Harus URL absolut.
$ogImage = base_url() . '/' . ltrim($ogImage ?? 'assets/img/stage-visual.jpg', '/');
$canonicalUrl = base_url() . '/' . basename($_SERVER['SCRIPT_NAME'] ?? 'index.php')
    . (!empty($_GET['m']) ? '?m=' . rawurlencode((string) $_GET['m']) : '');

$bodyStyle = '';
if (!empty($colors)) {
    $bodyStyle = sprintf(
        ' style="--accent:%s; --accent-2:%s; --accent-rgb:%s; --accent-text:%s;"',
        h($colors['accent']), h($colors['accent2']), h($colors['rgb']), h($colors['text'])
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?></title>
  <meta name="description" content="<?= h($metaDescription) ?>">
  <link rel="canonical" href="<?= h($canonicalUrl) ?>">
  <!-- Preview link saat dibagikan (Open Graph / X) -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Ave Mujica Fan Web">
  <meta property="og:locale" content="id_ID">
  <meta property="og:title" content="<?= h($pageTitle) ?>">
  <meta property="og:description" content="<?= h($metaDescription) ?>">
  <meta property="og:url" content="<?= h($canonicalUrl) ?>">
  <meta property="og:image" content="<?= h($ogImage) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= h($pageTitle) ?>">
  <meta name="twitter:description" content="<?= h($metaDescription) ?>">
  <meta name="twitter:image" content="<?= h($ogImage) ?>">
  <meta name="theme-color" content="#130814">
  <link rel="icon" href="assets/img/logo.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&display=swap">
  <link rel="stylesheet" href="assets/css/style.css?v=2.3.0">
</head>
<body<?= $bodyStyle ?>>
  <a class="skip-link" href="#konten">Langsung ke konten</a>
  <div class="main-container">
    <header class="site-header glass-card">
      <div class="header-top">
        <a class="brand" href="index.php" aria-label="Ave Mujica Fan Web, ke beranda">
          <img src="assets/img/logo.jpg" alt="">
          <div class="brand-copy">
            <strong>Ave Mujica Fan Web</strong>
            <span class="brand-tag"><span class="flag-id" aria-hidden="true"></span>Fanweb Indonesia</span>
          </div>
        </a>

        <div class="header-right">
          <?php if (is_logged_in()): ?>
            <a class="user-badge<?= is_admin() ? ' user-badge--admin' : '' ?>" href="akun.php" title="Akun Saya (<?= h(current_user()) ?>)">
              <span class="user-badge__avatar" aria-hidden="true"><?= h(strtoupper(substr(current_user(), 0, 1))) ?></span>
              <strong><?= h(current_user()) ?></strong>
            </a>
            <form class="header-actions" action="logout.php" method="post">
              <?= csrf_field() ?>
              <button type="submit">Logout</button>
            </form>
          <?php else: ?>
            <a class="secondary-btn" href="login.php">Login</a>
            <a class="primary-btn" href="register.php">Daftar</a>
          <?php endif; ?>
        </div>
      </div>

      <nav class="site-nav" aria-label="Navigasi utama">
        <?php foreach ($navItems as $item): ?>
          <a href="<?= h($item['url']) ?>"<?= $item['active'] ? ' class="is-active" aria-current="page"' : '' ?>><?= h($item['label']) ?></a>
        <?php endforeach; ?>
      </nav>
    </header>

    <?php foreach (take_flashes() as $flash): ?>
      <div class="flash flash--<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div>
    <?php endforeach; ?>

    <main id="konten">

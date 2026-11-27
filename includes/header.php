<?php
/*
 * Header bersama untuk semua halaman.
 * Variabel yang bisa di-set sebelum include:
 *   $pageTitle  (string)     judul tab browser
 *   $colors     (array|null) warna aksen member, lihat data/members.php
 *   $navLinks   (array)      ['Label' => 'url', ...] untuk bar menu
 */
$pageTitle = $pageTitle ?? 'Ave Mujica Fan Web';
$navLinks  = $navLinks ?? [];
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
  <meta name="description" content="Fanweb Indonesia untuk Ave Mujica: profil member, music video, jadwal live, pengisi suara, dan komentar penggemar.">
  <meta name="theme-color" content="#130814">
  <link rel="icon" href="assets/img/logo.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&display=swap">
  <link rel="stylesheet" href="assets/css/style.css?v=2.1.4">
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
            <div class="user-badge" title="Login sebagai <?= h(current_user()) ?>">
              <span class="user-badge__avatar" aria-hidden="true"><?= h(strtoupper(substr(current_user(), 0, 1))) ?></span>
              <strong><?= h(current_user()) ?></strong>
            </div>
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

      <?php if ($navLinks): ?>
        <nav class="site-nav" aria-label="Navigasi utama">
          <?php foreach ($navLinks as $label => $url): ?>
            <a href="<?= h($url) ?>"><?= h($label) ?></a>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>
    </header>

    <?php foreach (take_flashes() as $flash): ?>
      <div class="flash flash--<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div>
    <?php endforeach; ?>

    <main id="konten">

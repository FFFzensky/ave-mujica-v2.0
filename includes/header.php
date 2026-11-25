<?php
/*
 * Header bersama untuk semua halaman.
 * Variabel yang bisa di-set sebelum include:
 *   $pageTitle  (string)  judul tab browser
 *   $brandSub   (string)  teks kecil di bawah logo
 *   $colors     (array|null) warna aksen member, lihat data/members.php
 *   $navLinks   (array)   ['Label' => 'url', ...]
 */
$pageTitle = $pageTitle ?? 'Ave Mujica Fan Web';
$brandSub  = $brandSub ?? 'Ave Mujica';
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
  <meta name="description" content="Fansite Ave Mujica: profil member, music video, jadwal live, dan komentar penggemar.">
  <link rel="icon" href="assets/img/logo.jpg">
  <link rel="stylesheet" href="assets/css/style.css?v=2.0.0">
</head>
<body<?= $bodyStyle ?>>
  <a class="skip-link" href="#konten">Langsung ke konten</a>
  <div class="main-container">
    <header class="site-header glass-card">
      <a class="brand" href="index.php">
        <img src="assets/img/logo.jpg" alt="Logo Ave Mujica">
        <div class="brand-copy">
          <span>Ave Mujica Fan Web</span>
          <strong><?= h($brandSub) ?></strong>
        </div>
      </a>

      <nav class="site-nav" aria-label="Navigasi utama">
        <?php foreach ($navLinks as $label => $url): ?>
          <a href="<?= h($url) ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="header-right">
        <?php if (is_logged_in()): ?>
          <div class="user-badge">Login sebagai <strong><?= h(current_user()) ?></strong></div>
          <form class="header-actions" action="logout.php" method="post">
            <?= csrf_field() ?>
            <button type="submit">Logout</button>
          </form>
        <?php else: ?>
          <a class="secondary-btn" href="login.php">Login</a>
          <a class="primary-btn" href="register.php">Daftar</a>
        <?php endif; ?>
      </div>
    </header>

    <?php foreach (take_flashes() as $flash): ?>
      <div class="flash flash--<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div>
    <?php endforeach; ?>

    <main id="konten">

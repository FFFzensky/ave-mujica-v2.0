<?php
/*
 * Tampilan bersama untuk login.php dan register.php (v1: HTML-nya ditulis dua kali).
 * Panggil auth_page_start($judul, $tag) lalu tulis form, lalu auth_page_end($footerHtml).
 */
function auth_page_start(string $title, string $tag, string $subtext): void
{
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?> | Ave Mujica</title>
  <link rel="icon" href="assets/img/logo.jpg">
  <link rel="stylesheet" href="assets/css/auth.css?v=2.3.0">
</head>
<body>
  <div class="auth-shell">
    <section class="auth-visual">
      <div class="auth-tag"><?= h($tag) ?></div>
      <div class="auth-brand">
        <a class="auth-logo-wrap" href="index.php" title="Kembali ke beranda">
          <img src="assets/img/logo.jpg" alt="Logo Ave Mujica">
        </a>
      </div>
    </section>

    <section class="auth-panel">
      <div class="auth-card">
        <h2><?= h($title) ?></h2>
        <p class="subtext"><?= h($subtext) ?></p>

        <?php foreach (take_flashes() as $flash): ?>
          <div class="message <?= in_array($flash['type'], ['success', 'warning'], true) ? $flash['type'] : 'error' ?>"><?= h($flash['message']) ?></div>
        <?php endforeach; ?>
    <?php
}

function auth_page_end(string $footerHtml): void
{
    ?>
        <div class="auth-footer"><?= $footerHtml ?></div>
        <div class="auth-footer"><a href="index.php">&larr; Kembali ke beranda</a></div>
      </div>
    </section>
  </div>
</body>
</html>
    <?php
}

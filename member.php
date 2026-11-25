<?php
/*
 * SATU template untuk 5 member (pengganti Uika.php, Mutsumi.php, Umiri.php, Nyamu.php, Sakiko.php).
 * Contoh: member.php?m=uika   — isi profil ada di data/members.php
 */
require __DIR__ . '/includes/bootstrap.php';

$members = members();
$slug = strtolower((string) ($_GET['m'] ?? ''));

if (!isset($members[$slug])) {
    flash('error', 'Member tidak ditemukan.');
    redirect('index.php#member');
}

$m = $members[$slug];

$pageTitle = $m['name'] . ' | Ave Mujica';
$brandSub  = $m['name'];
$colors    = $m['colors'];
$navLinks  = ['Beranda' => 'index.php', 'Profil' => '#profil', 'Komentar' => '#komentar'];
require APP_ROOT . '/includes/header.php';
?>

<section class="hero">
  <article class="hero-copy glass-card">
    <span class="eyebrow"><?= h($m['stage_name']) ?> &bull; <?= h($m['position']) ?></span>
    <h1><?= h($m['name']) ?></h1>
    <p><?= h($m['summary']) ?></p>

    <div class="member-meta">
      <span>Nama panggung: <?= h($m['stage_name']) ?></span>
      <span>Posisi: <?= h($m['position']) ?></span>
      <span>Sekolah: <?= h($m['school']) ?></span>
    </div>

    <div class="hero-actions">
      <a class="primary-btn" href="#profil">Lihat profil</a>
      <a class="secondary-btn" href="#komentar">Tulis komentar</a>
    </div>
  </article>

  <aside class="hero-media glass-card">
    <img src="<?= h($m['image']) ?>" alt="<?= h($m['name']) ?>">
  </aside>
</section>

<section class="content-grid" id="profil">
  <article class="content-card glass-card">
    <h2 class="section-title">Profil Karakter</h2>
    <div class="section-copy">
      <?php foreach ($m['profile'] as $paragraph): ?>
        <p><?= $paragraph /* HTML statis dari data/members.php */ ?></p>
      <?php endforeach; ?>
    </div>
  </article>

  <aside class="side-card glass-card">
    <h2 class="section-title">Info Singkat</h2>
    <ul class="fact-list">
      <li><span>Nama lengkap</span><strong><?= h($m['name']) ?> (<?= h($m['name_jp']) ?>)</strong></li>
      <li><span>Nama panggung</span><strong><?= h($m['stage_name']) ?></strong></li>
      <li><span>Peran</span><strong><?= h($m['role']) ?></strong></li>
      <li><span>Sekolah</span><strong><?= h($m['school']) ?></strong></li>
      <li><span>Pengisi suara</span><strong><a href="<?= h($m['cv']['url']) ?>" target="_blank" rel="noopener"><?= h($m['cv']['name']) ?> ↗</a></strong></li>
      <li><span>Ciri khas</span><strong><?= h($m['traits']) ?></strong></li>
    </ul>

    <div class="side-note">Lihat member lain lewat daftar di bawah.</div>

    <div class="member-links">
      <?php foreach ($members as $otherSlug => $other): if ($otherSlug === $slug) continue; ?>
        <a class="member-link member-link--<?= h($otherSlug) ?>" href="member.php?m=<?= h($otherSlug) ?>"><?= h($other['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </aside>
</section>

<?php render_comment_section($m['page_id'], 'Tulis pendapatmu tentang ' . $m['name'] . '.'); ?>

<?php require APP_ROOT . '/includes/footer.php'; ?>

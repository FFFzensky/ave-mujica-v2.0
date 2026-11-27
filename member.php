<?php
/*
 * SATU template untuk 5 member (pengganti Uika.php, Mutsumi.php, Umiri.php, Nyamu.php, Sakiko.php).
 * - member.php          -> daftar kelima member
 * - member.php?m=uika   -> profil satu member (isi profil ada di data/members.php)
 */
require __DIR__ . '/includes/bootstrap.php';

$members = members();
$slug = strtolower((string) ($_GET['m'] ?? ''));

if ($slug === '') {
    // ---------- Halaman daftar member ----------
    $pageTitle = 'Member | Ave Mujica Fan Web';
    $metaDescription = 'Kelima member Ave Mujica: Misumi Uika (Doloris), Wakaba Mutsumi (Mortis), Yahata Umiri (Timoris), Yuutenji Nyamu (Amoris), dan Togawa Sakiko (Oblivionis).';
    require APP_ROOT . '/includes/header.php';

    render_page_banner('Member', 'Member Ave Mujica', 'Lima member, lima topeng. Pilih member untuk melihat profil lengkap, info singkat, dan komentar penggemar.');
    ?>
    <section class="member-list section-gap">
      <?php foreach ($members as $memberSlug => $member): ?>
        <a class="member-list-card glass-card" href="member.php?m=<?= h($memberSlug) ?>"
           style="--accent:<?= h($member['colors']['accent']) ?>; --accent-2:<?= h($member['colors']['accent2']) ?>; --accent-rgb:<?= h($member['colors']['rgb']) ?>; --accent-text:<?= h($member['colors']['text']) ?>;">
          <span class="member-list-card__img"><img src="<?= h($member['image']) ?>" alt="<?= h($member['name']) ?>" loading="lazy"></span>
          <span class="member-list-card__body">
            <span class="member-list-card__stage"><?= h($member['stage_name']) ?></span>
            <strong><?= h($member['name']) ?> <small lang="ja"><?= h($member['name_jp']) ?></small></strong>
            <span class="member-list-card__role"><?= h($member['position']) ?></span>
            <span class="muted"><?= h($member['summary']) ?></span>
            <span class="member-list-card__meta">🎙️ <?= h($member['cv']['name']) ?> · 🏫 <?= h($member['school']) ?></span>
            <span class="member-list-card__more">Lihat profil →</span>
          </span>
        </a>
      <?php endforeach; ?>
    </section>
    <?php
    require APP_ROOT . '/includes/sections/about.php';
    require APP_ROOT . '/includes/footer.php';
    exit();
}

if (!isset($members[$slug])) {
    flash('error', 'Member tidak ditemukan.');
    redirect('member.php');
}

$m = $members[$slug];

$pageTitle = $m['name'] . ' | Ave Mujica';
$colors    = $m['colors'];
$metaDescription = $m['summary'] . ' Profil, info singkat, dan komentar penggemar di Ave Mujica Fan Web (Fanweb Indonesia).';
$ogImage   = $m['image'];
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

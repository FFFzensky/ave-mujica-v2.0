<?php
/*
 * Beranda: ringkasan. Bagian lengkapnya ada di halaman masing-masing
 * (musik.php, live.php, galeri.php, pengisi-suara.php) supaya di HP tidak perlu scroll panjang.
 */
require __DIR__ . '/includes/bootstrap.php';

$members  = members();
$upcoming = upcoming_events();
$nextLive = $upcoming[0] ?? null;
$newSongs = array_slice(array_values(array_filter(songs(), fn ($s) => !empty($s['new']) && !empty($s['youtube']))), 0, 3);
$gallery  = array_slice(gallery_items(), 0, 4);

$pageTitle = 'Ave Mujica Fan Web | Fanweb Indonesia';
require APP_ROOT . '/includes/header.php';
?>

<section class="hero-stage glass-card" aria-labelledby="hero-title">
  <div class="hero-stage__bg" aria-hidden="true"></div>
  <div class="hero-stage__content">
    <span class="eyebrow">
      <?= is_logged_in() ? 'Selamat datang kembali, ' . h(current_user()) . '!' : 'Fanweb Indonesia untuk Ave Mujica' ?>
    </span>
    <h1 id="hero-title">
      <span class="hero-stage__small">Welcome to the world of</span>
      <span class="hero-stage__big">Ave Mujica</span>
    </h1>
    <p>Profil member, music video &amp; lagu terbaru, hitung mundur live berikutnya, galeri, dan ruang komentar untuk sesama penggemar di Indonesia.</p>
    <div class="hero-actions">
      <a class="primary-btn" href="musik.php">Putar musik</a>
      <a class="secondary-btn" href="live.php">Live berikutnya</a>
    </div>
  </div>
</section>

<section class="explore section-gap" aria-label="Jelajahi">
  <!-- Live terdekat -->
  <a class="explore-card glass-card explore-card--live" href="live.php">
    <span class="explore-card__label">📅 Live terdekat</span>
    <?php if ($nextLive): $jst = new DateTimeImmutable($nextLive['start']); ?>
      <strong class="explore-card__title"><?= h($nextLive['title']) ?></strong>
      <span class="muted"><?= h($jst->format('d M Y')) ?> · <?= h($nextLive['venue']) ?></span>
      <span class="mini-countdown" data-countdown-target="<?= h($nextLive['start']) ?>">
        <b data-mini="d">0</b> hari <b data-mini="h">00</b> jam <b data-mini="m">00</b> menit
      </span>
    <?php else: ?>
      <strong class="explore-card__title">Belum ada jadwal baru</strong>
    <?php endif; ?>
    <span class="explore-card__more">Semua jadwal &amp; kalender →</span>
  </a>

  <!-- Lagu terbaru -->
  <div class="explore-card glass-card">
    <span class="explore-card__label">🎵 Rilisan terbaru</span>
    <ul class="song-teasers">
      <?php foreach ($newSongs as $song): ?>
        <li>
          <a href="musik.php?lagu=<?= h($song['slug']) ?>">
            <img src="https://i.ytimg.com/vi/<?= h($song['youtube']) ?>/mqdefault.jpg" alt="" loading="lazy">
            <span><strong><?= h($song['title']) ?></strong><small><?= h($song['note'] ?? 'Music video resmi') ?></small></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <a class="explore-card__more" href="musik.php">Buka pemutar &amp; playlist →</a>
  </div>

  <!-- Galeri -->
  <a class="explore-card glass-card" href="galeri.php">
    <span class="explore-card__label">🖼️ Galeri</span>
    <span class="gallery-teaser">
      <?php foreach ($gallery as $item): ?>
        <img src="<?= h($item['src']) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
      <?php endforeach; ?>
    </span>
    <span class="explore-card__more">Lihat galeri →</span>
  </a>

  <!-- Pengisi suara -->
  <a class="explore-card glass-card" href="pengisi-suara.php">
    <span class="explore-card__label">🎙️ Pengisi suara</span>
    <span class="cv-teaser">
      <?php foreach ($members as $slug => $m): ?>
        <span class="cv-teaser__face" style="--accent:<?= h($m['colors']['accent']) ?>;"><img src="<?= h($m['image']) ?>" alt="<?= h($m['name']) ?>" loading="lazy"></span>
      <?php endforeach; ?>
    </span>
    <span class="muted">Sasaki Rico, Watase Yuzuki, Okada Mei, Yonezawa Akane, dan Takao Kanon.</span>
    <span class="explore-card__more">Seiyuu &amp; sosial media →</span>
  </a>
</section>

<?php require APP_ROOT . '/includes/sections/member-grid.php'; ?>

<?php require APP_ROOT . '/includes/sections/about.php'; ?>

<?php render_comment_section(0, 'Tulis pendapatmu tentang Ave Mujica.'); ?>

<?php require APP_ROOT . '/includes/footer.php'; ?>

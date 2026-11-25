<?php
require __DIR__ . '/includes/bootstrap.php';

$members = members();
$songs   = require APP_ROOT . '/data/songs.php';
$events  = require APP_ROOT . '/data/events.php';

// Live yang belum lewat (tetap tampil sampai 6 jam setelah mulai).
$now = time();
$upcoming = array_values(array_filter($events, function (array $event) use ($now): bool {
    return strtotime($event['start']) + 6 * 3600 > $now;
}));
usort($upcoming, fn ($a, $b) => strtotime($a['start']) <=> strtotime($b['start']));

// Pilihan lagu awal: ?lagu=slug, kalau tidak ada pakai lagu pertama (rilisan terbaru).
$selectedSlug = $_GET['lagu'] ?? $songs[0]['slug'];

function song_video_url(array $song): string
{
    if (empty($song['video'])) {
        return '';
    }
    $file = basename($song['video']);
    return is_file(APP_ROOT . '/videos/' . $file) ? 'videos/' . rawurlencode($file) : '';
}

$gallery = [
    ['src' => 'assets/img/personil.jpg',     'title' => 'Personil Ave Mujica', 'caption' => 'Foto grup seluruh member.'],
    ['src' => 'assets/img/stage-visual.jpg', 'title' => 'Stage Visual',        'caption' => 'Nuansa teatrikal yang kuat.'],
    ['src' => 'assets/img/performance.jpg',  'title' => 'Performance Shot',    'caption' => 'Tampilan yang dramatis dan fokus.'],
    ['src' => 'assets/img/promo.jpg',        'title' => 'Promotional Image',   'caption' => 'Visual promosi Ave Mujica.'],
    ['src' => 'assets/img/konser.jpg',       'title' => 'Konser',              'caption' => 'Poster live Ave Mujica.'],
];

$pageTitle = 'Ave Mujica Fan Web';
$brandSub  = 'Welcome to the world of Ave Mujica';
$navLinks  = ['Member' => '#member', 'Musik' => '#musik', 'Live' => '#live', 'Galeri' => '#galeri', 'Komentar' => '#komentar'];
require APP_ROOT . '/includes/header.php';
?>

<section class="hero hero--home">
  <article class="hero-copy glass-card">
    <span class="eyebrow">
      <?= is_logged_in() ? 'Selamat datang kembali, ' . h(current_user()) . '!' : 'Fansite Ave Mujica' ?>
    </span>
    <h1>Welcome to the world of Ave Mujica.</h1>
    <p>Profil member, music video &amp; lagu terbaru, jadwal live beserta hitung mundurnya, galeri, dan ruang komentar untuk sesama penggemar.</p>
    <div class="hero-actions">
      <a class="primary-btn" href="#musik">Putar musik</a>
      <a class="secondary-btn" href="#live">Live berikutnya</a>
    </div>
  </article>
  <aside class="hero-media glass-card">
    <img src="assets/img/ave-mujica.jpg" alt="Ave Mujica">
  </aside>
</section>

<section class="content-card glass-card section-gap" id="tentang">
  <h2 class="section-title">Biodata</h2>
  <div class="section-copy">
    <p>
      <a href="https://bang-dream.com/avemujica" target="_blank" rel="noopener"><b>Ave Mujica</b></a>
      adalah band beranggotakan perempuan di franchise BanG Dream!. Grup ini beranggotakan lima orang:
      <a href="member.php?m=uika" class="member-color--uika"><b>Misumi Uika (Doloris)</b></a> pada vokal dan gitar utama,
      <a href="member.php?m=mutsumi" class="member-color--mutsumi"><b>Wakaba Mutsumi (Mortis)</b></a> pada gitar ritme,
      <a href="member.php?m=umiri" class="member-color--umiri"><b>Yahata Umiri (Timoris)</b></a> pada bass,
      <a href="member.php?m=nyamu" class="member-color--nyamu"><b>Yuutenji Nyamu (Amoris)</b></a> pada drum, dan
      <a href="member.php?m=sakiko" class="member-color--sakiko"><b>Togawa Sakiko (Oblivionis)</b></a> pada keyboard.
    </p>
    <p>
      Ave Mujica dibentuk oleh Sakiko setelah bubarnya CRYCHIC. Awalnya Sakiko mengajak Mutsumi, Uika, dan Umiri,
      lalu Nyamu ikut bergabung setelah melihat daya tarik grup tersebut.
    </p>
    <p>
      Para member berasal dari sekolah yang berbeda: Uika dan Umiri bersekolah di SMA Putri Hanasakigawa,
      Mutsumi di Akademi Putri Tsukinomori, Sakiko di SMA Putri Haneoka (sebelumnya Tsukinomori),
      dan Nyamu di Geijutsu Academy. Keunikan tiap karakter inilah yang membuat Ave Mujica terasa kuat,
      intens, dan sangat berkarakter.
    </p>
  </div>
</section>

<section class="section-gap" id="member">
  <h2 class="section-title section-title--outside">Member</h2>
  <div class="member-grid">
    <?php foreach ($members as $slug => $m): ?>
      <a class="member-card glass-card" href="member.php?m=<?= h($slug) ?>"
         style="--accent:<?= h($m['colors']['accent']) ?>; --accent-rgb:<?= h($m['colors']['rgb']) ?>; --accent-text:<?= h($m['colors']['text']) ?>;">
        <img src="<?= h($m['image']) ?>" alt="<?= h($m['name']) ?>" loading="lazy">
        <div class="member-card__info">
          <span><?= h($m['stage_name']) ?></span>
          <strong><?= h($m['name']) ?></strong>
          <small><?= h($m['position']) ?></small>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="content-card glass-card section-gap" id="musik">
  <div class="section-head">
    <div>
      <h2 class="section-title">Music Video &amp; Lagu</h2>
      <p class="muted"><?= count($songs) ?> lagu: MV dari file sendiri atau YouTube resmi, plus Spotify.</p>
    </div>
    <label class="select-wrap">
      <span class="sr-only">Pilih lagu</span>
      <select id="songSelection">
        <?php
        $groups = [
            'Rilisan terbaru 2026' => array_filter($songs, fn ($s) => !empty($s['new'])),
            'Lagu sebelumnya'      => array_filter($songs, fn ($s) => empty($s['new'])),
        ];
        foreach ($groups as $groupLabel => $groupSongs): ?>
          <optgroup label="<?= h($groupLabel) ?>">
            <?php foreach ($groupSongs as $song):
                $label = $song['title'] . ($song['title_jp'] !== '' ? ' — ' . $song['title_jp'] : '');
            ?>
              <option value="<?= h($song['slug']) ?>"
                      data-title="<?= h($song['title']) ?>"
                      data-title-jp="<?= h($song['title_jp']) ?>"
                      data-video="<?= h(song_video_url($song)) ?>"
                      data-youtube="<?= h($song['youtube'] ?? '') ?>"
                      data-spotify="<?= h($song['spotify'] ?? '') ?>"
                      data-note="<?= h($song['note'] ?? '') ?>"
                      data-new="<?= !empty($song['new']) ? '1' : '' ?>"
                      <?= $song['slug'] === $selectedSlug ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </label>
  </div>

  <div class="player" data-player>
    <div class="player__meta">
      <div>
        <span class="badge-new" data-player-new hidden>Baru</span>
        <h3 data-player-title></h3>
        <p class="muted" data-player-note></p>
      </div>
      <div class="source-switch" data-source-switch hidden role="group" aria-label="Sumber video">
        <button type="button" data-source="video">MP4</button>
        <button type="button" data-source="youtube">YouTube</button>
      </div>
    </div>

    <div class="player__stage" data-player-stage>
      <video data-player-video controls preload="metadata" playsinline hidden></video>
      <div class="player__yt" data-player-yt hidden></div>
      <p class="player__empty" data-player-empty hidden>Lagu ini belum punya music video. Dengarkan lewat Spotify di bawah.</p>
    </div>

    <div class="player__spotify" data-player-spotify></div>

    <div class="player__links">
      <a class="secondary-btn" data-link-youtube target="_blank" rel="noopener" hidden>Buka di YouTube</a>
      <a class="secondary-btn" data-link-spotify target="_blank" rel="noopener" hidden>Buka di Spotify</a>
      <a class="secondary-btn" href="https://open.spotify.com/artist/5BKIH2Kwc6LbetG04Boai7" target="_blank" rel="noopener">Ave Mujica di Spotify</a>
    </div>
  </div>
</section>

<section class="content-card glass-card section-gap" id="live">
  <div class="section-head">
    <div>
      <h2 class="section-title">Live Berikutnya</h2>
      <p class="muted">Jam ditampilkan dalam zona waktu perangkatmu. Sumber: situs resmi BanG Dream!.</p>
    </div>
  </div>

  <?php if (!$upcoming): ?>
    <p class="empty-state">Belum ada jadwal live baru yang diumumkan.</p>
  <?php else: ?>
    <div class="countdown" data-countdown>
      <p class="countdown__label">Menuju <strong data-countdown-title>…</strong></p>
      <div class="countdown__grid" aria-live="off">
        <div><strong data-unit="days">0</strong><span>Hari</span></div>
        <div><strong data-unit="hours">0</strong><span>Jam</span></div>
        <div><strong data-unit="minutes">0</strong><span>Menit</span></div>
        <div><strong data-unit="seconds">0</strong><span>Detik</span></div>
      </div>
      <p class="countdown__when muted" data-countdown-when></p>
      <div class="countdown__filter" role="group" aria-label="Pilih jenis live">
        <button type="button" class="is-active" data-countdown-filter="all">Semua live</button>
        <button type="button" data-countdown-filter="solo">Live solo Ave Mujica</button>
      </div>
    </div>

    <ol class="event-list">
      <?php foreach ($upcoming as $event):
          // Tanggal ditampilkan sesuai waktu Jepang (offset +09:00 di data), bukan WIB server.
          $jst = new DateTimeImmutable($event['start']);
      ?>
        <li class="event-item" data-event
            data-start="<?= h($event['start']) ?>"
            data-tba="<?= $event['time_tba'] ? '1' : '' ?>"
            data-solo="<?= $event['solo'] ? '1' : '' ?>"
            data-title="<?= h($event['title']) ?>">
          <div class="event-item__date">
            <strong><?= h($jst->format('d')) ?></strong>
            <span><?= h($jst->format('M Y')) ?></span>
          </div>
          <div class="event-item__body">
            <h3>
              <?= h($event['title']) ?>
              <?php if ($event['solo']): ?><span class="tag">Solo</span><?php endif; ?>
            </h3>
            <p class="muted"><?= h($event['venue']) ?> &middot; <span data-local-time><?= $event['time_tba'] ? 'Jam belum diumumkan' : h($jst->format('H:i')) . ' JST' ?></span></p>
            <?php if (!empty($event['note'])): ?><p><?= h($event['note']) ?></p><?php endif; ?>
          </div>
          <a class="secondary-btn" href="<?= h($event['url']) ?>" target="_blank" rel="noopener">Info resmi</a>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>

<section class="content-card glass-card section-gap" id="galeri">
  <h2 class="section-title">Galeri</h2>
  <p class="muted">Geser untuk melihat lainnya, klik untuk memperbesar.</p>
  <div class="gallery-slider">
    <div class="gallery-track">
      <?php foreach ($gallery as $item): ?>
        <a href="<?= h($item['src']) ?>" class="gallery-card" data-gallery-item data-caption="<?= h($item['title']) ?>">
          <img src="<?= h($item['src']) ?>" alt="<?= h($item['title']) ?>" loading="lazy">
          <div class="gallery-info">
            <strong><?= h($item['title']) ?></strong>
            <span><?= h($item['caption']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="content-card glass-card section-gap" id="sosmed">
  <h2 class="section-title">Pengisi Suara &amp; Sosial Media</h2>
  <ul class="cv-list">
    <?php foreach ($members as $slug => $m): ?>
      <li>
        <a href="<?= h($m['cv']['url']) ?>" target="_blank" rel="noopener" class="member-link member-link--<?= h($slug) ?>">
          <span><strong><?= h($m['cv']['name']) ?></strong> <small>sebagai <?= h($m['name']) ?></small></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<?php render_comment_section(0, 'Tulis pendapatmu tentang Ave Mujica.'); ?>

<div class="gallery-modal" id="galleryModal" role="dialog" aria-modal="true" aria-label="Galeri">
  <div class="gallery-modal__dialog">
    <button class="gallery-modal__close" type="button" data-close-gallery aria-label="Tutup">✕</button>
    <div class="gallery-modal__image-wrap">
      <button class="gallery-modal__nav gallery-modal__nav--prev" type="button" data-prev-gallery aria-label="Sebelumnya">‹</button>
      <img alt="" class="gallery-modal__image">
      <button class="gallery-modal__nav gallery-modal__nav--next" type="button" data-next-gallery aria-label="Berikutnya">›</button>
    </div>
    <p class="gallery-modal__caption"></p>
  </div>
</div>

<?php require APP_ROOT . '/includes/footer.php'; ?>

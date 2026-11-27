<?php
/* Countdown + daftar jadwal live + tombol kalender. Dipakai di live.php. */
require_once APP_ROOT . '/includes/calendar.php';
$upcoming = upcoming_events();
?>

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
          <div class="event-item__actions">
            <a class="secondary-btn" href="<?= h($event['url']) ?>" target="_blank" rel="noopener">Info resmi</a>
            <details class="cal-menu">
              <summary class="secondary-btn">📅 Tambah ke kalender</summary>
              <div class="cal-menu__list">
                <a href="<?= h(event_google_url($event)) ?>" target="_blank" rel="noopener">Google Calendar</a>
                <a href="kalender.php?live=<?= (int) $event['index'] ?>">Kalender HP / Outlook (.ics)</a>
              </div>
            </details>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>

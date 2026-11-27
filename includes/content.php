<?php
/*
 * Data konten yang dipakai beberapa halaman (beranda + halaman masing-masing).
 */

// Playlist Spotify resmi "This Is Ave Mujica" (dibuat & diperbarui oleh Spotify).
const SPOTIFY_PLAYLIST_ID = '37i9dQZF1DZ06evO3iLHJH';
const SPOTIFY_ARTIST_ID = '5BKIH2Kwc6LbetG04Boai7';

function songs(): array
{
    static $songs = null;
    if ($songs === null) {
        $songs = require APP_ROOT . '/data/songs.php';
    }
    return $songs;
}

function song_video_url(array $song): string
{
    if (empty($song['video'])) {
        return '';
    }
    $file = basename($song['video']);
    return is_file(APP_ROOT . '/videos/' . $file) ? 'videos/' . rawurlencode($file) : '';
}

/** Live yang belum lewat (tetap tampil sampai 6 jam setelah mulai), urut dari yang terdekat. */
function upcoming_events(): array
{
    $events = require APP_ROOT . '/data/events.php';
    foreach ($events as $i => $event) {
        $events[$i]['index'] = $i; // dipakai link kalender.php?live=N
    }
    $now = time();
    $upcoming = array_values(array_filter($events, function (array $event) use ($now): bool {
        return strtotime($event['start']) + 6 * 3600 > $now;
    }));
    usort($upcoming, fn ($a, $b) => strtotime($a['start']) <=> strtotime($b['start']));
    return $upcoming;
}

function gallery_items(): array
{
    return [
        ['src' => 'assets/img/personil.jpg',     'title' => 'Personil Ave Mujica', 'caption' => 'Foto grup seluruh member.'],
        ['src' => 'assets/img/stage-visual.jpg', 'title' => 'Stage Visual',        'caption' => 'Nuansa teatrikal yang kuat.'],
        ['src' => 'assets/img/performance.jpg',  'title' => 'Performance Shot',    'caption' => 'Tampilan yang dramatis dan fokus.'],
        ['src' => 'assets/img/promo.jpg',        'title' => 'Promotional Image',   'caption' => 'Visual promosi Ave Mujica.'],
        ['src' => 'assets/img/konser.jpg',       'title' => 'Konser',              'caption' => 'Poster live Ave Mujica.'],
        ['src' => 'assets/img/ave-mujica.jpg',   'title' => 'Alea jacta est',      'caption' => 'Sampul EP Ave Mujica.'],
    ];
}

/** Judul halaman kecil di atas tiap halaman (musik, live, galeri, pengisi suara). */
function render_page_banner(string $eyebrow, string $title, string $text): void
{
    ?>
    <section class="page-banner glass-card">
      <span class="eyebrow"><?= h($eyebrow) ?></span>
      <h1><?= h($title) ?></h1>
      <p><?= h($text) ?></p>
    </section>
    <?php
}

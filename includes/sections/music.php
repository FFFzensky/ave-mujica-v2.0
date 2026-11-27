<?php
/* Pemutar musik (MP4 / YouTube / Spotify) + playlist. Dipakai di musik.php. */
$songs = songs();
$selectedSlug = (string) ($_GET['lagu'] ?? $songs[0]['slug']);
?>

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
      <a class="secondary-btn" href="https://open.spotify.com/artist/<?= SPOTIFY_ARTIST_ID ?>" target="_blank" rel="noopener">Ave Mujica di Spotify</a>
    </div>
  </div>

  <div class="playlist-block">
    <div class="playlist-block__head">
      <div>
        <h3>Playlist: This Is Ave Mujica</h3>
        <p class="muted">Playlist resmi dari Spotify. Putar langsung di sini dan lanjut ke lagu berikutnya.</p>
      </div>
      <a class="secondary-btn" href="https://open.spotify.com/playlist/<?= SPOTIFY_PLAYLIST_ID ?>" target="_blank" rel="noopener">Buka playlist di Spotify</a>
    </div>
    <iframe class="playlist-block__embed" title="Spotify playlist This Is Ave Mujica"
            src="https://open.spotify.com/embed/playlist/<?= SPOTIFY_PLAYLIST_ID ?>?utm_source=generator&amp;theme=0"
            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>
    <p class="spotify-note">
      <strong>Tips:</strong> login ke Spotify di browser yang sama (open.spotify.com) supaya lagu diputar penuh.
      Tanpa login, Spotify hanya memberi preview 30 detik. Ini aturan dari Spotify untuk semua situs.
    </p>
  </div>
</section>

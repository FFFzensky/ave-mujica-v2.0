<?php

const COMMENT_MAX_LENGTH = 1000;

function fetch_comments(int $pageId): array
{
    $stmt = db()->prepare(
        'SELECT id, username, comment, created_at FROM comments WHERE page_id = ? ORDER BY created_at DESC, id DESC'
    );
    $stmt->bind_param('i', $pageId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Bagian komentar lengkap (form + daftar) untuk halaman mana pun.
 * Semua halaman memanggil fungsi yang sama, hanya beda $pageId.
 */
function render_comment_section(int $pageId, string $subtitle): void
{
    $comments = fetch_comments($pageId);
    $user = current_user();
    ?>
    <section class="comment-card glass-card" id="komentar">
      <div class="comment-head">
        <div>
          <h2 class="section-title">Komentar <span class="count-pill"><?= count($comments) ?></span></h2>
          <p><?= h($subtitle) ?></p>
        </div>
        <button type="button" class="toggle-btn" data-comment-toggle aria-expanded="true" aria-controls="comments-<?= $pageId ?>">Sembunyikan komentar</button>
      </div>

      <div class="comment-layout">
        <?php if ($user !== null): ?>
          <form class="comment-form" action="komen.php" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="page_id" value="<?= $pageId ?>">
            <label for="comment-<?= $pageId ?>">Komentar sebagai <strong><?= h($user) ?></strong></label>
            <textarea id="comment-<?= $pageId ?>" name="comment" maxlength="<?= COMMENT_MAX_LENGTH ?>"
                      placeholder="Tulis komentar kamu di sini..." required data-char-counter></textarea>
            <div class="form-row">
              <small class="char-counter" aria-live="polite">0 / <?= COMMENT_MAX_LENGTH ?></small>
              <button type="submit">Kirim Komentar</button>
            </div>
          </form>
        <?php else: ?>
          <div class="comment-form comment-form--guest">
            <p>Masuk dulu untuk ikut berkomentar.</p>
            <a class="primary-btn" href="login.php?from=<?= $pageId ?>">Login</a>
            <a class="secondary-btn" href="register.php">Buat akun</a>
          </div>
        <?php endif; ?>

        <div id="comments-<?= $pageId ?>" class="comment-box" data-comment-box>
          <?php if (!$comments): ?>
            <p class="empty-state">Belum ada komentar. Jadilah yang pertama kirim komentar.</p>
          <?php else: ?>
            <div class="comment-list">
              <?php foreach ($comments as $row): ?>
                <article class="comment-item">
                  <div class="comment-item__top">
                    <strong><?= h($row['username']) ?></strong>
                    <time datetime="<?= h(date('c', strtotime($row['created_at']))) ?>"><?= h(date('d-m-Y H:i', strtotime($row['created_at']))) ?> WIB</time>
                  </div>
                  <p><?= nl2br(h($row['comment'])) ?></p>
                  <?php if ($user !== null && $user === $row['username']): ?>
                    <form class="delete-form" action="hapuskomen.php" method="post"
                          data-confirm="Hapus komentar ini?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="comment_id" value="<?= (int) $row['id'] ?>">
                      <button type="submit" class="delete-link">Hapus</button>
                    </form>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
}

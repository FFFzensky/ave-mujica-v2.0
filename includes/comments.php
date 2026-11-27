<?php

const COMMENT_MAX_LENGTH = 1000;

/**
 * Ambil komentar satu halaman, dikelompokkan jadi thread:
 * komentar utama (terbaru di atas) + balasan di bawahnya (urut dari yang terlama).
 */
function fetch_comment_threads(int $pageId): array
{
    $stmt = db()->prepare(
        'SELECT c.id, c.username, c.comment, c.created_at, c.parent_id, c.reply_to, a.role
           FROM comments c
           LEFT JOIN akun a ON a.username = c.username
          WHERE c.page_id = ?
          ORDER BY c.created_at ASC, c.id ASC'
    );
    $stmt->bind_param('i', $pageId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $threads = [];
    $replies = [];
    foreach ($rows as $row) {
        if ($row['parent_id'] === null) {
            $threads[(int) $row['id']] = $row + ['replies' => []];
        } else {
            $replies[] = $row;
        }
    }
    foreach ($replies as $reply) {
        $parentId = (int) $reply['parent_id'];
        if (isset($threads[$parentId])) {
            $threads[$parentId]['replies'][] = $reply;
        }
    }

    return array_reverse(array_values($threads));
}

/** Nama + badge admin (berwarna sesuai member, misal admintimoris = warna Umiri). */
function render_comment_author(array $row): void
{
    echo '<strong class="comment-author">' . h($row['username']) . '</strong>';
    if (($row['role'] ?? '') === 'admin') {
        $slug = admin_member_slug($row['username']);
        $class = $slug ? ' admin-badge--' . h($slug) : '';
        echo ' <span class="admin-badge' . $class . '" title="Admin Ave Mujica Fan Web">Admin</span>';
    }
}

/** Satu komentar (utama atau balasan), lengkap dengan tombol Balas dan Hapus. */
function render_comment_item(array $row, int $pageId, int $threadId, bool $isReply): void
{
    $user = current_user();
    $isOwner = $user !== null && $user === $row['username'];
    $canDelete = $isOwner || is_admin();
    $replyCount = $isReply ? 0 : count($row['replies'] ?? []);
    $time = strtotime($row['created_at']);
    ?>
    <article class="comment-item<?= $isReply ? ' comment-item--reply' : '' ?>" id="komentar-<?= (int) $row['id'] ?>">
      <div class="comment-item__top">
        <div class="comment-item__who"><?php render_comment_author($row); ?></div>
        <time datetime="<?= h(date('c', $time)) ?>"><?= h(date('d-m-Y H:i', $time)) ?> WIB</time>
      </div>
      <p>
        <?php if ($isReply && !empty($row['reply_to'])): ?>
          <span class="mention">@<?= h($row['reply_to']) ?></span>
        <?php endif; ?>
        <?= nl2br(h($row['comment'])) ?>
      </p>

      <div class="comment-actions">
        <?php if ($user !== null): ?>
          <button type="button" class="comment-action" data-reply-button
                  data-thread="<?= $threadId ?>" data-reply-to="<?= h($row['username']) ?>">Balas</button>
        <?php else: ?>
          <a class="comment-action" href="login.php?from=<?= $pageId ?>">Login untuk membalas</a>
        <?php endif; ?>

        <?php if ($canDelete): ?>
          <form class="delete-form" action="hapuskomen.php" method="post"
                data-confirm="<?= $isReply || $replyCount === 0 ? 'Hapus komentar ini?' : 'Hapus komentar ini beserta ' . $replyCount . ' balasannya?' ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="comment_id" value="<?= (int) $row['id'] ?>">
            <button type="submit" class="comment-action comment-action--danger"><?= $isOwner ? 'Hapus' : 'Hapus (admin)' ?></button>
          </form>
        <?php endif; ?>
      </div>
    </article>
    <?php
}

/**
 * Bagian komentar lengkap (form + daftar thread) untuk halaman mana pun.
 * Semua halaman memanggil fungsi yang sama, hanya beda $pageId.
 */
function render_comment_section(int $pageId, string $subtitle): void
{
    $threads = fetch_comment_threads($pageId);
    $total = 0;
    foreach ($threads as $thread) {
        $total += 1 + count($thread['replies']);
    }
    $user = current_user();
    ?>
    <section class="comment-card glass-card" id="komentar">
      <div class="comment-head">
        <div>
          <h2 class="section-title">Komentar <span class="count-pill"><?= $total ?></span></h2>
          <p><?= h($subtitle) ?></p>
        </div>
        <button type="button" class="toggle-btn" data-comment-toggle aria-expanded="true" aria-controls="comments-<?= $pageId ?>">Sembunyikan komentar</button>
      </div>

      <div class="comment-layout">
        <?php if ($user !== null): ?>
          <form class="comment-form" action="komen.php" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="page_id" value="<?= $pageId ?>">
            <label for="comment-<?= $pageId ?>">Komentar sebagai <strong><?= h($user) ?></strong><?= is_admin() ? ' <span class="admin-badge">Admin</span>' : '' ?></label>
            <textarea id="comment-<?= $pageId ?>" name="comment" maxlength="<?= COMMENT_MAX_LENGTH ?>"
                      placeholder="Tulis komentar kamu di sini..." required data-char-counter></textarea>
            <div class="form-row">
              <small class="char-counter" aria-live="polite">0 / <?= COMMENT_MAX_LENGTH ?></small>
              <button type="submit">Kirim Komentar</button>
            </div>
          </form>
        <?php else: ?>
          <div class="comment-form comment-form--guest">
            <p>Masuk dulu untuk ikut berkomentar dan membalas komentar.</p>
            <a class="primary-btn" href="login.php?from=<?= $pageId ?>">Login</a>
            <a class="secondary-btn" href="register.php">Buat akun</a>
          </div>
        <?php endif; ?>

        <div id="comments-<?= $pageId ?>" class="comment-box" data-comment-box>
          <?php if (!$threads): ?>
            <p class="empty-state">Belum ada komentar. Jadilah yang pertama kirim komentar.</p>
          <?php else: ?>
            <div class="comment-list">
              <?php foreach ($threads as $thread): $threadId = (int) $thread['id']; ?>
                <div class="comment-thread">
                  <?php render_comment_item($thread, $pageId, $threadId, false); ?>

                  <?php if ($thread['replies']): ?>
                    <div class="comment-replies">
                      <?php foreach ($thread['replies'] as $reply): ?>
                        <?php render_comment_item($reply, $pageId, $threadId, true); ?>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($user !== null): ?>
                    <form class="reply-form" action="komen.php" method="post" data-reply-form="<?= $threadId ?>" hidden>
                      <?= csrf_field() ?>
                      <input type="hidden" name="page_id" value="<?= $pageId ?>">
                      <input type="hidden" name="parent_id" value="<?= $threadId ?>">
                      <input type="hidden" name="reply_to" value="<?= h($thread['username']) ?>" data-reply-to-input>
                      <label class="reply-form__label" for="reply-<?= $threadId ?>">
                        Membalas <span class="mention" data-reply-to-label>@<?= h($thread['username']) ?></span>
                      </label>
                      <textarea id="reply-<?= $threadId ?>" name="comment" maxlength="<?= COMMENT_MAX_LENGTH ?>"
                                placeholder="Tulis balasan..." required data-char-counter></textarea>
                      <div class="form-row">
                        <small class="char-counter" aria-live="polite">0 / <?= COMMENT_MAX_LENGTH ?></small>
                        <div class="reply-form__buttons">
                          <button type="button" class="comment-action" data-reply-cancel>Batal</button>
                          <button type="submit">Kirim Balasan</button>
                        </div>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php
}

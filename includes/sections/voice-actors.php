<?php /* Kartu pengisi suara + sosial media. Dipakai di pengisi-suara.php. */ $members = members(); ?>

<section class="content-card glass-card section-gap" id="pengisi-suara">
  <div class="section-head">
    <div>
      <h2 class="section-title">Pengisi Suara &amp; Sosial Media</h2>
      <p class="muted">Kenalan dengan seiyuu di balik kelima member, sekaligus member band di panggung live, lalu follow akun mereka.</p>
    </div>
  </div>
  <div class="cv-grid">
    <?php foreach ($members as $slug => $m):
        $social = social_info($m['cv']['url']);
    ?>
      <article class="cv-card"
               style="--accent:<?= h($m['colors']['accent']) ?>; --accent-rgb:<?= h($m['colors']['rgb']) ?>; --accent-text:<?= h($m['colors']['text']) ?>;">
        <a class="cv-card__avatar" href="member.php?m=<?= h($slug) ?>" title="Profil <?= h($m['name']) ?>">
          <span class="cv-card__face"><img src="<?= h($m['image']) ?>" alt="<?= h($m['name']) ?>" loading="lazy"></span>
        </a>
        <div class="cv-card__body">
          <span class="cv-card__role"><?= h($m['stage_name']) ?> &middot; <?= h($m['position']) ?></span>
          <h3><?= h($m['cv']['name']) ?></h3>
          <p class="cv-card__jp" lang="ja"><?= h($m['cv']['name_jp']) ?></p>
          <p class="cv-card__as">Pengisi suara <a href="member.php?m=<?= h($slug) ?>"><?= h($m['name']) ?></a></p>
          <a class="social-btn social-btn--<?= h($social['platform']) ?>" href="<?= h($m['cv']['url']) ?>" target="_blank" rel="noopener"
             aria-label="<?= h($social['label'] . ' ' . $m['cv']['name'] . ' (' . $social['handle'] . ')') ?>">
            <?= social_icon($social['platform']) ?>
            <span><?= h($social['handle']) ?></span>
          </a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

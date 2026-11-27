<?php /* Kartu 5 member. */ $members = members(); ?>

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

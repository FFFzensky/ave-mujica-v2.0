<?php /* Galeri + modal perbesar gambar. Dipakai di galeri.php. */ $gallery = gallery_items(); ?>

<section class="content-card glass-card section-gap" id="galeri">
  <p class="muted">Klik gambar untuk memperbesar. Geser kiri/kanan (atau tombol panah) untuk pindah gambar.</p>
  <div class="gallery-grid">
    <div class="gallery-grid__inner">
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

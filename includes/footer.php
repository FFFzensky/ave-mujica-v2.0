    </main>

    <footer class="site-footer glass-card">
      <p>&copy; <?= date('Y') ?> Ave Mujica Fan Web &middot; Fanweb Indonesia &middot; proyek penggemar non-komersial.</p>
      <p class="footer-small">Ave Mujica, nama member, logo, dan gambar adalah milik BanG Dream! Project / Bushiroad.</p>
    </footer>
  </div>

  <!-- Bar menu bawah (hanya tampil di HP) -->
  <nav class="bottom-nav" aria-label="Menu cepat">
    <?php foreach (site_nav_items() as $item): if (!$item['bottom']) continue; ?>
      <a href="<?= h($item['url']) ?>"<?= $item['active'] ? ' class="is-active" aria-current="page"' : '' ?>>
        <?= nav_icon($item['icon']) ?>
        <span><?= h($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <script src="assets/js/app.js?v=2.3.0"></script>
</body>
</html>

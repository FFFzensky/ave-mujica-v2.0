<?php
require __DIR__ . '/includes/bootstrap.php';

$page = static_pages()['galeri'];
$pageTitle = 'Galeri | Ave Mujica Fan Web';
$metaDescription = 'Galeri foto dan visual Ave Mujica.';
require APP_ROOT . '/includes/header.php';

render_page_banner('Galeri', 'Galeri Ave Mujica', 'Visual panggung, foto grup, dan poster Ave Mujica. Klik gambar untuk memperbesar.');
require APP_ROOT . '/includes/sections/gallery.php';
render_comment_section($page['page_id'], 'Visual Ave Mujica favoritmu yang mana?');

require APP_ROOT . '/includes/footer.php';

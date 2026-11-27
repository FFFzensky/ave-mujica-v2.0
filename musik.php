<?php
require __DIR__ . '/includes/bootstrap.php';

$page = static_pages()['musik'];
$pageTitle = 'Musik | Ave Mujica Fan Web';
$metaDescription = 'Putar music video dan lagu Ave Mujica: MV YouTube resmi, embed Spotify, dan playlist This Is Ave Mujica.';
require APP_ROOT . '/includes/header.php';

render_page_banner('Musik', 'Music Video & Lagu', 'Sekitar 30 lagu Ave Mujica, termasuk rilisan 2026. Pilih lagu, tonton MV-nya, atau dengarkan lewat Spotify.');
require APP_ROOT . '/includes/sections/music.php';
render_comment_section($page['page_id'], 'Ngobrolin lagu dan MV Ave Mujica di sini. Lagu favoritmu yang mana?');

require APP_ROOT . '/includes/footer.php';

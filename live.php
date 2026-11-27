<?php
require __DIR__ . '/includes/bootstrap.php';

$page = static_pages()['live'];
$pageTitle = 'Jadwal Live | Ave Mujica Fan Web';
$metaDescription = 'Jadwal live Ave Mujica berikutnya lengkap dengan hitung mundur dalam WIB/WITA/WIT dan tombol tambah ke kalender.';
require APP_ROOT . '/includes/header.php';

render_page_banner('Live', 'Jadwal Live Ave Mujica', 'Hitung mundur ke live berikutnya, jam dalam zona waktumu, dan tombol untuk menyimpannya ke kalender.');
require APP_ROOT . '/includes/sections/live.php';
render_comment_section($page['page_id'], 'Ada yang mau nonton (atau streaming) live berikutnya? Cerita di sini.');

require APP_ROOT . '/includes/footer.php';

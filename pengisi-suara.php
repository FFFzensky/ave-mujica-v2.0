<?php
require __DIR__ . '/includes/bootstrap.php';

$page = static_pages()['pengisi-suara'];
$pageTitle = 'Pengisi Suara | Ave Mujica Fan Web';
$metaDescription = 'Seiyuu Ave Mujica: Sasaki Rico, Watase Yuzuki, Okada Mei, Yonezawa Akane, Takao Kanon, beserta sosial media mereka.';
require APP_ROOT . '/includes/header.php';

render_page_banner('Pengisi Suara', 'Seiyuu Ave Mujica', 'Kenalan dengan pengisi suara kelima member (yang juga tampil sebagai band di panggung live), lalu follow akun mereka.');
require APP_ROOT . '/includes/sections/voice-actors.php';
render_comment_section($page['page_id'], 'Seiyuu favoritmu siapa? Tulis di sini.');

require APP_ROOT . '/includes/footer.php';

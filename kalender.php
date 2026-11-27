<?php
/* Unduh satu jadwal live sebagai file .ics: kalender.php?live=3 */
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/calendar.php';

$events = require APP_ROOT . '/data/events.php';
$index = (int) ($_GET['live'] ?? -1);

if (!isset($events[$index])) {
    http_response_code(404);
    exit('Jadwal tidak ditemukan.');
}

$event = $events[$index];
$host = preg_replace('/[^A-Za-z0-9.\-]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'avemujica'));
$uid = 'live-' . md5($event['title'] . $event['start']) . '@' . $host;
$filename = 'ave-mujica-' . trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($event['title'])), '-') . '.ics';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');
echo event_ics($event, $uid);

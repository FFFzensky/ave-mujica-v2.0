<?php
/*
 * Tombol "Tambah ke kalender" untuk jadwal live.
 * - Google Calendar: link template (tanpa login ke web kita)
 * - File .ics: bisa dibuka di kalender HP (iPhone/Android), Outlook, dll. (lihat kalender.php)
 * Live yang jamnya belum diumumkan dibuat sebagai acara sehari penuh (tanggal Jepang).
 */

const LIVE_DURATION_HOURS = 3;

function event_times(array $event): array
{
    $start = new DateTimeImmutable($event['start']);
    $end = $start->modify('+' . LIVE_DURATION_HOURS . ' hours');
    return [$start, $end];
}

function event_details(array $event): string
{
    $lines = [];
    if (!empty($event['note'])) {
        $lines[] = $event['note'];
    }
    if (!empty($event['time_tba'])) {
        $lines[] = 'Jam mulai belum diumumkan.';
    }
    $lines[] = 'Info resmi: ' . $event['url'];
    $lines[] = 'Dari Ave Mujica Fan Web (Fanweb Indonesia)';
    return implode("\n", $lines);
}

function event_google_url(array $event): string
{
    [$start, $end] = event_times($event);
    if (!empty($event['time_tba'])) {
        $dates = $start->format('Ymd') . '/' . $start->modify('+1 day')->format('Ymd');
    } else {
        $utc = new DateTimeZone('UTC');
        $dates = $start->setTimezone($utc)->format('Ymd\THis\Z') . '/' . $end->setTimezone($utc)->format('Ymd\THis\Z');
    }

    return 'https://calendar.google.com/calendar/render?' . http_build_query([
        'action'   => 'TEMPLATE',
        'text'     => 'Ave Mujica: ' . $event['title'],
        'dates'    => $dates,
        'details'  => event_details($event),
        'location' => $event['venue'],
    ]);
}

function ics_escape(string $text): string
{
    return str_replace(["\\", ";", ",", "\r\n", "\n"], ["\\\\", "\\;", "\\,", "\\n", "\\n"], $text);
}

function event_ics(array $event, string $uid): string
{
    [$start, $end] = event_times($event);
    $utc = new DateTimeZone('UTC');

    $lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Ave Mujica Fan Web//Fanweb Indonesia//ID',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
    ];
    if (!empty($event['time_tba'])) {
        $lines[] = 'DTSTART;VALUE=DATE:' . $start->format('Ymd');
        $lines[] = 'DTEND;VALUE=DATE:' . $start->modify('+1 day')->format('Ymd');
    } else {
        $lines[] = 'DTSTART:' . $start->setTimezone($utc)->format('Ymd\THis\Z');
        $lines[] = 'DTEND:' . $end->setTimezone($utc)->format('Ymd\THis\Z');
    }
    $lines[] = 'SUMMARY:' . ics_escape('Ave Mujica: ' . $event['title']);
    $lines[] = 'LOCATION:' . ics_escape($event['venue']);
    $lines[] = 'DESCRIPTION:' . ics_escape(event_details($event));
    $lines[] = 'URL:' . $event['url'];
    // Pengingat 1 hari sebelumnya
    $lines[] = 'BEGIN:VALARM';
    $lines[] = 'TRIGGER:-P1D';
    $lines[] = 'ACTION:DISPLAY';
    $lines[] = 'DESCRIPTION:' . ics_escape('Besok: Ave Mujica ' . $event['title']);
    $lines[] = 'END:VALARM';
    $lines[] = 'END:VEVENT';
    $lines[] = 'END:VCALENDAR';

    // Baris .ics maksimal 75 byte; lipat baris panjang sesuai standar (RFC 5545).
    $folded = [];
    foreach ($lines as $line) {
        while (strlen($line) > 74) {
            $cut = 74;
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--; // jangan potong di tengah karakter UTF-8
            }
            $folded[] = substr($line, 0, $cut);
            $line = ' ' . substr($line, $cut);
        }
        $folded[] = $line;
    }
    return implode("\r\n", $folded) . "\r\n";
}

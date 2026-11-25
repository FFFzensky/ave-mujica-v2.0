<?php
/*
 * Jadwal live Ave Mujica. Countdown di beranda otomatis memilih live TERDEKAT yang belum lewat,
 * jadi setelah satu live selesai, countdown langsung pindah ke live berikutnya tanpa edit kode.
 *
 * 'start' memakai waktu Jepang (+09:00) — browser pengunjung menghitung selisihnya sendiri,
 * jadi countdown benar di zona waktu mana pun (WIB, WITA, dll).
 * 'time_tba' => true  artinya jam mulai belum diumumkan (countdown dihitung ke awal harinya).
 * 'solo' => true      untuk live yang khusus Ave Mujica (bukan festival / tamu).
 *
 * Sumber: https://bang-dream.com/events/?artist=avemujica (dicek 26 Nov 2026).
 */
return [
    [
        'title'    => 'EVANESCENCE 2026 JAPAN (Tokyo, hari 1)',
        'start'    => '2026-12-01T00:00:00+09:00',
        'time_tba' => true,
        'venue'    => 'SGC HALL ARIAKE, Tokyo',
        'note'     => 'Ave Mujica tampil sebagai band tamu. Juga 2 Des (Tokyo) dan 4 Des (Zepp Osaka Bayside).',
        'url'      => 'https://bang-dream.com/events/evanescence_avemujica/',
        'solo'     => false,
    ],
    [
        'title'    => 'EVANESCENCE 2026 JAPAN (Tokyo, hari 2)',
        'start'    => '2026-12-02T00:00:00+09:00',
        'time_tba' => true,
        'venue'    => 'SGC HALL ARIAKE, Tokyo',
        'note'     => 'Ave Mujica tampil sebagai band tamu.',
        'url'      => 'https://bang-dream.com/events/evanescence_avemujica/',
        'solo'     => false,
    ],
    [
        'title'    => 'EVANESCENCE 2026 JAPAN (Osaka)',
        'start'    => '2026-12-04T00:00:00+09:00',
        'time_tba' => true,
        'venue'    => 'Zepp Osaka Bayside, Osaka',
        'note'     => 'Ave Mujica tampil sebagai band tamu.',
        'url'      => 'https://bang-dream.com/events/evanescence_avemujica/',
        'solo'     => false,
    ],
    [
        'title'    => '4th Single Release Live 「Anima Incorrupta」',
        'start'    => '2027-02-27T18:00:00+09:00',
        'time_tba' => false,
        'venue'    => 'duo MUSIC EXCHANGE, Tokyo',
        'note'     => 'Pintu dibuka 17:00 JST. Ditayangkan gratis secara live di YouTube "バンドリちゃんねる☆".',
        'url'      => 'https://bang-dream.com/events/avemujica_anima-incorrupta/',
        'solo'     => true,
    ],
    [
        'title'    => 'ANISAMA FUKUOKA 2027',
        'start'    => '2027-03-14T00:00:00+09:00',
        'time_tba' => true,
        'venue'    => 'Mizuho PayPay Dome Fukuoka',
        'note'     => 'Festival. Ave Mujica tampil 14 Maret (MyGO!!!!! 13 Maret).',
        'url'      => 'https://bang-dream.com/events/anisama-fukuoka-2027/',
        'solo'     => false,
    ],
    [
        'title'    => '8th LIVE 「Discrimen Fati」 DAY 1',
        'start'    => '2027-05-29T18:30:00+09:00',
        'time_tba' => false,
        'venue'    => 'K-Arena Yokohama',
        'note'     => 'Pintu dibuka 17:00 JST.',
        'url'      => 'https://bang-dream.com/events/avemujica-8th/',
        'solo'     => true,
    ],
    [
        'title'    => '8th LIVE 「Discrimen Fati」 DAY 2',
        'start'    => '2027-05-30T17:30:00+09:00',
        'time_tba' => false,
        'venue'    => 'K-Arena Yokohama',
        'note'     => 'Pintu dibuka 16:00 JST.',
        'url'      => 'https://bang-dream.com/events/avemujica-8th/',
        'solo'     => true,
    ],
];

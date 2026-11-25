<?php
/*
 * Daftar lagu untuk pemutar musik di beranda.
 *
 * Setiap lagu bisa punya sampai 3 sumber, semuanya opsional:
 *   'video'   => nama file MP4 di folder videos/      (upload sendiri, seperti di v1)
 *   'youtube' => ID video YouTube                    (https://www.youtube.com/watch?v=ID_INI)
 *   'spotify' => ID lagu Spotify                     (https://open.spotify.com/track/ID_INI)
 *
 * Kalau file MP4-nya ada di folder videos/, file itu yang diputar.
 * Kalau tidak ada, otomatis pakai YouTube. Spotify selalu tampil di bawahnya.
 *
 * Menambah lagu baru = tambah satu blok di bawah. Tidak perlu menyentuh HTML/JS.
 * 'new' => true  memberi label "Baru" dan memasukkannya ke grup rilisan terbaru.
 *
 * Sumber ID: channel YouTube resmi Ave Mujica & halaman artis Ave Mujica di Spotify (dicek 26 Nov 2026).
 */
return [
    // ---- Rilisan 2026 ----
    ['slug' => 'a-song-of-romance',   'title' => 'A Song Of Romance',          'title_jp' => '',            'new' => true, 'youtube' => 'uSOfMAT_Hqo', 'spotify' => '0TUeaielgGFNumZ6oxcmmv', 'note' => 'Lagu sisipan film "Ave Mujica prima aurora"'],
    ['slug' => 'baddy-baddy',         'title' => 'BADDY BADDY',                'title_jp' => '',            'new' => true, 'youtube' => 'DdJcldvN3Ck', 'spotify' => '0Ia1r2bRe8Uh9j8rhIfvEK', 'note' => 'Mini album "神の名を" (21 Okt 2026)'],
    ['slug' => 'kami-no-na-wo',       'title' => 'Kami no Na wo',              'title_jp' => '神の名を',      'new' => true, 'spotify' => '6HuRAiVekbXHrNKbIUkkOb', 'note' => 'Lagu utama mini album "神の名を"'],
    ['slug' => 'a-song-of-romance-2', 'title' => 'A Song Of Romance Pt.2',     'title_jp' => '',            'new' => true, 'spotify' => '0kgXakO9GnycldN5sGScwZ', 'note' => 'Mini album "神の名を"'],
    ['slug' => 'the-whole-blue-world','title' => 'The Whole Blue World',       'title_jp' => '',            'new' => true, 'youtube' => 'lkS9XIzWObc', 'spotify' => '4OwLwl4VyFQGBQ3ApJ7ayA'],

    // ---- Lagu sebelumnya ----
    ['slug' => 'sophie',              'title' => 'Sophie',                     'title_jp' => '',            'youtube' => 'ivXQ21xDHfE', 'spotify' => '3kNstETQbKxO8bSph97UZO'],
    ['slug' => 's-the-way',           'title' => '‘S/’ The Way',               'title_jp' => '',            'youtube' => 'p6ptNdglySk', 'spotify' => '6O36bjXdeg0e1wv51Qp1kw'],
    ['slug' => 'aoi-hitomi',          'title' => 'Aoi Hitomi no Naka ni',      'title_jp' => '碧い瞳の中に',   'youtube' => 'vBSWbEjIuTU', 'spotify' => '4UQmtceQpVzfSgwW7EWfwf', 'note' => 'Kolaborasi Arknights (video dari channel resmi Arknights)'],
    ['slug' => 'divine',              'title' => 'DIVINE',                     'title_jp' => '',            'spotify' => '5xlKd4DVqdFd7EWvGvsyWu'],
    ['slug' => 'georgette',           'title' => 'Georgette Me, Georgette You','title_jp' => '',            'spotify' => '1EsSC5I5sh1qWNQFBRJ0hO'],
    ['slug' => 'alter-ego',           'title' => 'Alter Ego',                  'title_jp' => '顔',           'youtube' => 'cVfQBLdIUXk', 'spotify' => '0RXD7I4huPBaenGC002MwA'],
    ['slug' => 'musica-caelestis',    'title' => 'Musica Caelestis',           'title_jp' => '天球(そら)のMúsica', 'youtube' => '2Bo-ULStqLo', 'spotify' => '7fh1kd9kH1F6aw1zjFOI6X'],
    ['slug' => 'octagram-dance',      'title' => 'Octagram Dance',             'title_jp' => '八芒星ダンス',    'youtube' => 'tevtnb427Mw', 'spotify' => '4eR1tqIsDouIiI1cbTuCMy'],
    ['slug' => 'crucifix-x',          'title' => 'Crucifix X',                 'title_jp' => '',            'youtube' => 'XP8al1l38Po', 'spotify' => '1CWxAyTe6Rst8ZHSTz1O3G'],
    ['slug' => 'imprisoned-xii',      'title' => 'Imprisoned XII',             'title_jp' => '',            'youtube' => '0YNMV7xljD4', 'spotify' => '5sUCmYVWX2blPomsCOu0Ij'],
    ['slug' => 'killkiss',            'title' => 'KiLLKiSS',                   'title_jp' => '',            'youtube' => 'FWXkipC-vqs', 'spotify' => '6P4eoHU0xJ1SuiDUHgfrbQ', 'note' => 'Opening anime "BanG Dream! Ave Mujica"'],
    ['slug' => 'ether',               'title' => 'Ether',                      'title_jp' => '',            'youtube' => 'z6k7YIIZ6Hk', 'spotify' => '4oqU858FytlLNXhlHq6O3v'],
    ['slug' => 'symbol-4',            'title' => 'Symbol IV : Earth',          'title_jp' => '',            'youtube' => 'WqQPM0dZpXc', 'spotify' => '0k7IpY0imMYMfZtZ49tQA5'],
    ['slug' => 'symbol-3',            'title' => 'Symbol III : ▽',             'title_jp' => '',            'youtube' => 'NB3PxWLn9v4', 'spotify' => '60pIkGRQ1VnpT0d8LDfb8U'],
    ['slug' => 'symbol-2',            'title' => 'Symbol II : Air',            'title_jp' => '',            'youtube' => 'pMk3iHPlUaI', 'spotify' => '7DMKA4owuOXcVa00BeSGhf'],
    ['slug' => 'symbol-1',            'title' => 'Symbol I : △',               'title_jp' => '',            'video' => 'symbol-1.mp4',       'youtube' => '2O5C0mJx0Fs', 'spotify' => '6tNk1tjuv4s7yiN4xjELdS'],
    ['slug' => 'angles',              'title' => 'Angles',                     'title_jp' => '',            'video' => 'angles.mp4',         'youtube' => 'MbVgSM7Hhso', 'spotify' => '6jEQRfYq0IGBoFOT2Z6Wgi'],
    ['slug' => 'utopia',              'title' => 'Utopia',                     'title_jp' => '素晴らしき世界 でも どこにもない場所', 'video' => 'utopia.mp4', 'youtube' => 'MyI6sKuV4kY', 'spotify' => '2iFgS3Y3LHSSnrGRt1kY8L'],
    ['slug' => 'masquerade',          'title' => 'Mas?uerade Rhapsody Re?uest','title_jp' => '',            'video' => 'masquerade-rhapsody-request.mp4', 'youtube' => 'VLxM8q6PF4I', 'spotify' => '3RtcR7pL6YNQ57fsRciMdp', 'note' => 'Judul resminya memang memakai tanda "?"'],
    ['slug' => 'kamisama-baka',       'title' => 'Memento Mori',               'title_jp' => '神さま、バカ',    'video' => 'kamisama-baka.mp4',  'youtube' => 'OT9KqYBAkQc', 'spotify' => '0iNEdxHGgVMsCBgHFycwmL'],
    ['slug' => 'choir-s-choir',       'title' => 'Choir ‘S’ Choir',            'title_jp' => '',            'video' => 'choir-s-choir.mp4',  'youtube' => '7llj4mh4L-8', 'spotify' => '4lPNuizRfDvXB8ltrNPd1v'],
    ['slug' => 'two-moons',           'title' => 'Two Moons ~Deep Into The Forest~', 'title_jp' => 'ふたつの月', 'video' => 'two-moons.mp4', 'youtube' => '6USzFX8HnSY', 'spotify' => '4Mudg5HCSIhNyjN8Rg08gM'],
    ['slug' => 'black-birthday',      'title' => 'Black Birthday',             'title_jp' => '黒のバースデイ',  'video' => 'black-birthday.mp4', 'youtube' => 'WIhtJ7eJ-oI', 'spotify' => '2LFR4WmBjnKYyRElUEii4f'],
    ['slug' => 'ave-mujica',          'title' => 'Ave Mujica',                 'title_jp' => '',            'youtube' => 'QDsd0nyzwz0', 'spotify' => '2W99f2JxL6d2myh7nKAJKg', 'note' => 'Official Anime × Live Video'],
];

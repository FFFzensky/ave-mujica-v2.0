# Ave Mujica Fan Web v2.0

**Fanweb Indonesia untuk Ave Mujica (BanG Dream!).**
Live: **https://avemujica.rf.gd**

Fitur web ini:

- Profil kelima member
- Music video dan lagu terbaru (YouTube + Spotify)
- Countdown ke live Ave Mujica berikutnya
- Galeri foto
- Daftar pengisi suara beserta sosial medianya
- Komentar penggemar

## Fitur

- **Musik:** sekitar 30 lagu, termasuk rilisan 2026. Bisa diputar lewat MV YouTube resmi, embed Spotify, atau file MP4 sendiri.
- **Countdown live:** memakai jadwal resmi dan otomatis pindah ke live berikutnya. Jam ditampilkan dalam WIB/WITA/WIT beserta JST.
- **Member:** satu halaman profil untuk tiap member.
- **Komentar:** semua orang bisa membaca. Login hanya diperlukan untuk menulis atau menghapus komentar sendiri.
- **Responsif:** nyaman dibuka di HP maupun desktop.

## Apa yang beda dari v1

Versi ini adalah penulisan ulang dari [ave-mujica-fansite](https://github.com/FFFzensky/ave-mujica-fansite), proyek akhir mata kuliah Pemrograman Web.

- **Kode lebih ringkas.** 12 file komentar dan 5 halaman member yang hampir sama digabung menjadi satu `komen.php`, satu `hapuskomen.php`, dan satu template `member.php`.
- **Konten berbasis data.** Lagu, jadwal live, dan profil member ada di folder `data/`. Menambah lagu atau live cukup dengan menambah satu baris, tanpa mengubah HTML.
- **Lebih aman.** Memakai token CSRF, hapus komentar dan logout lewat POST, session yang aman, dan password yang selalu di-hash.
- **Konten diperbarui.** Ada lagu dan MV 2026, jadwal live sampai 2027, dan data member yang sudah dikoreksi.
- **Siap online.** Berjalan di hosting PHP biasa seperti InfinityFree.

## Tech stack

PHP 8 · MySQL · HTML · CSS · JavaScript (tanpa framework)

## Menjalankan di komputer

1. Copy folder ini ke `htdocs` XAMPP, lalu nyalakan Apache dan MySQL.
2. Import `database/schema.sql` di phpMyAdmin.
3. Copy `config.example.php` menjadi `config.local.php`, lalu sesuaikan isinya kalau perlu.
4. Buka `http://localhost/ave-mujica-v2.0/`.

Untuk menambah lagu atau jadwal live, edit `data/songs.php` atau `data/events.php`.

## Sumber data

- Jadwal live: [bang-dream.com](https://bang-dream.com/events/?artist=avemujica)
- Music video: channel YouTube resmi Ave Mujica
- Lagu: [Spotify](https://open.spotify.com/artist/5BKIH2Kwc6LbetG04Boai7)

## Disclaimer

Proyek penggemar non-komersial. Ave Mujica, nama member, logo, gambar, dan music video adalah milik BanG Dream! Project / Bushiroad.

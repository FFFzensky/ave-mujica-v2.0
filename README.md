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

- **Kode jauh lebih rapi.** Belasan file komentar dan halaman member yang isinya mirip-mirip sekarang disederhanakan jadi `komen.php`, `hapuskomen.php`, dan satu template `member.php`.
- **Update konten jadi gampang.** Lagu, jadwal live, dan profil member ditaruh di folder `data/`. Mau nambah lagu atau jadwal live? Tinggal tambah satu baris data tanpa perlu ngoprek HTML.
- **Keamanan ditingkatkan.** Sudah pakai token CSRF, hapus komentar dan logout pakai metode POST, session lebih aman, dan password di-hash.
- **Data paling update.** Sudah masuk lagu dan MV 2026, jadwal live sampai 2027, plus info member yang sudah diperbaiki.

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

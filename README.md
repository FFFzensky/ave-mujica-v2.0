# Ave Mujica Fan Web v2.0

**Fanweb Indonesia untuk Ave Mujica (BanG Dream!).**
Live: **https://avemujica.rf.gd**

Fitur web ini:

- Profil kelima member
- Music video dan lagu terbaru (YouTube + Spotify), plus playlist *This Is Ave Mujica*
- Countdown ke live Ave Mujica berikutnya, bisa langsung disimpan ke kalender
- Galeri foto
- Daftar pengisi suara beserta sosial medianya
- Komentar penggemar yang bisa saling dibalas

## Fitur

- **Musik:** sekitar 30 lagu, termasuk rilisan 2026. Bisa diputar lewat MV YouTube resmi, embed Spotify, atau file MP4 sendiri. Ada juga playlist resmi *This Is Ave Mujica* (login Spotify di browser supaya lagu diputar penuh, bukan preview 30 detik).
- **Countdown live:** memakai jadwal resmi dan otomatis pindah ke live berikutnya. Jam ditampilkan dalam WIB/WITA/WIT beserta JST. Tiap jadwal bisa ditambahkan ke Google Calendar atau kalender HP.
- **Member:** satu halaman profil untuk tiap member.
- **Komentar & balasan:** semua orang bisa membaca. Login untuk menulis, membalas komentar orang lain, atau menghapus komentar sendiri.
- **Admin:** akun admin (ditandai badge sesuai warna member) bisa menghapus komentar siapa pun dan punya Panel Admin untuk moderasi.
- **Responsif & ringan:** nyaman dibuka di HP maupun desktop. Video YouTube baru dimuat saat diputar.

## Apa yang beda dari v1

Versi ini adalah penulisan ulang dari [ave-mujica-fansite](https://github.com/FFFzensky/ave-mujica-fansite), proyek akhir mata kuliah Pemrograman Web.

- **Kode jauh lebih rapi.** Belasan file komentar dan halaman member yang isinya mirip-mirip sekarang disederhanakan jadi `komen.php`, `hapuskomen.php`, dan satu template `member.php`.
- **Update konten jadi gampang.** Lagu, jadwal live, dan profil member ditaruh di folder `data/`. Mau nambah lagu atau jadwal live? Tinggal tambah satu baris data tanpa perlu ngoprek HTML.
- **Keamanan ditingkatkan.** Sudah pakai token CSRF, hapus komentar dan logout pakai metode POST, session lebih aman, password di-hash, dan percobaan login dibatasi.
- **Data paling update.** Sudah masuk lagu dan MV 2026, jadwal live sampai 2027, plus info member yang sudah diperbaiki.

## Tech stack

PHP 8 · MySQL · HTML · CSS · JavaScript (tanpa framework)

## Menjalankan di komputer

1. Copy folder ini ke `htdocs` XAMPP, lalu nyalakan Apache dan MySQL.
2. Import `database/schema.sql` di phpMyAdmin. (Sudah punya database versi lama? Jalankan `database/migrate_v2.2.sql` sekali.)
3. Copy `config.example.php` menjadi `config.local.php`, lalu sesuaikan isinya kalau perlu.
4. Buka `http://localhost/ave-mujica-v2.0/`.

Untuk menambah lagu atau jadwal live, edit `data/songs.php` atau `data/events.php`.

## Sumber data

- Jadwal live: [bang-dream.com](https://bang-dream.com/events/?artist=avemujica)
- Music video: channel YouTube resmi Ave Mujica
- Lagu: [Spotify](https://open.spotify.com/artist/5BKIH2Kwc6LbetG04Boai7)

## Disclaimer

Proyek penggemar non-komersial. Ave Mujica, nama member, logo, gambar, dan music video adalah milik BanG Dream! Project / Bushiroad.

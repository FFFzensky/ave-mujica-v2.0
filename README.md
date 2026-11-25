# Ave Mujica Fan Web v2.0

Fansite untuk band **Ave Mujica** (BanG Dream!). Isinya profil member, music video dan lagu terbaru (MP4 sendiri, YouTube, dan Spotify), countdown live berikutnya, galeri, dan komentar.

Versi ini adalah penulisan ulang dari [ave-mujica-fansite](https://github.com/FFFzensky/ave-mujica-fansite), proyek akhir mata kuliah Pemrograman Web (Pendidikan Teknik Informatika, Universitas Negeri Yogyakarta).

## Yang baru di v2.0

- **30 lagu** di pemutar musik, termasuk rilisan 2026: *A Song Of Romance*, *BADDY BADDY*, *神の名を*, *The Whole Blue World*, dan lainnya.
- **Pemutar 3 sumber.** Kalau file MP4 milikmu ada di `videos/`, file itu yang diputar. Kalau tidak ada, otomatis pakai MV YouTube resmi. Embed **Spotify** selalu tampil di bawahnya.
- **Countdown live otomatis.** Memakai jadwal resmi sampai *8th LIVE「Discrimen Fati」* (29–30 Mei 2027). Countdown memilih live terdekat dan pindah sendiri ke live berikutnya setelah satu live dimulai. Jam ditampilkan dalam zona waktu pengunjung (WIB/WITA/WIT), lengkap dengan JST. Ada filter "Live solo Ave Mujica".
- **Bebas dilihat tanpa login.** Login hanya diperlukan untuk menulis dan menghapus komentar.
- **Satu template untuk 5 member** (`member.php?m=uika`), menggantikan 5 file yang hampir sama.
- **Satu `komen.php` dan satu `hapuskomen.php`**, menggantikan `komen.php`–`komen5.php` dan `hapuskomen.php`–`hapuskomen5.php`.

### Trik efisiennya

Di v1, tiap halaman punya file hapus komentar sendiri. Bedanya hanya satu baris redirect.
Di v2, halaman tujuan diambil dari **satu daftar** `page_id → URL` (lihat `page_url()` di `includes/helpers.php`):

```php
// hapuskomen.php — page_id dibaca dari komentar itu sendiri di database
$back = page_url((int) $row['page_id']) . '#komentar';
```

Menambah halaman baru cukup dengan menambah data di `data/members.php`. Tidak perlu file `hapuskomen6.php`.
Daftarnya tetap, jadi orang juga tidak bisa menyisipkan URL lain lewat redirect (*open redirect*).

## Bug v1 yang diperbaiki

| Bug di v1 | Perbaikan di v2 |
|---|---|
| 6 file `komen*.php` dan 6 file `hapuskomen*.php` isinya sama | Masing-masing jadi 1 file |
| `real_escape_string` + prepared statement → `it's` tersimpan jadi `it\'s` | Cukup prepared statement. Untuk data lama, lihat `database/fix_v1_comments.sql` |
| Koneksi database dibuat dua kali per request | Satu koneksi bersama lewat `db()` |
| Komentar yang tidak ada → `$row` null → warning PHP | Dicek dulu, lalu tampil pesan yang jelas |
| Hapus komentar lewat link GET, bisa terpicu tanpa sengaja | Lewat form POST + token CSRF + konfirmasi |
| Logout lewat GET tanpa proteksi | POST + CSRF |
| Session ID tidak diganti saat login (*session fixation*) | `session_regenerate_id()` |
| `Au2.php` menyimpan password **tanpa hash** (kode mati) | Dihapus |
| `display_errors` selalu aktif, membocorkan detail error saat online | Diatur lewat `debug` di config |
| `UikaMisumi.Webp`, `AveMujicaPersonil.jpeg`: huruf besar/kecil tidak cocok, gambar **rusak di hosting Linux** | Nama file konsisten huruf kecil di `assets/img/` |
| Countdown terkunci ke 8 Juni 2024 dan memakai jam lokal | Data jadwal + zona waktu JST yang benar |
| Info sekolah member saling bertentangan | Dikoreksi: Uika & Umiri Hanasakigawa, Mutsumi Tsukinomori, Sakiko Haneoka (dulu Tsukinomori), Nyamu Geijutsu Academy |
| Pesan login membocorkan apakah username terdaftar | Pesan disamakan: "Username atau password salah" |
| Tidak ada validasi panjang username/komentar | Username 3–30 karakter, komentar maks. 1000 karakter |

## Struktur folder

```
index.php            Beranda (biodata, member, musik, live, galeri, komentar)
member.php           Satu template untuk 5 member
login.php            Login (form + proses dalam satu file)
register.php         Registrasi
logout.php           Logout (POST)
komen.php            Kirim komentar (semua halaman)
hapuskomen.php       Hapus komentar (semua halaman)
includes/            bootstrap, helper, layout, bagian komentar
data/
  members.php        Profil 5 member + page_id
  songs.php          Daftar lagu (MP4 / YouTube / Spotify)
  events.php         Jadwal live untuk countdown
assets/              CSS, JS, gambar
videos/              File MP4 milikmu (tidak ikut ke GitHub)
database/schema.sql  Skema database
```

### Menambah lagu baru

Buka `data/songs.php`, lalu tambahkan satu baris:

```php
['slug' => 'judul-lagu', 'title' => 'Judul Lagu', 'title_jp' => '', 'new' => true,
 'youtube' => 'ID_YOUTUBE', 'spotify' => 'ID_SPOTIFY', 'video' => 'judul-lagu.mp4'],
```

ID YouTube adalah bagian setelah `watch?v=`. ID Spotify adalah bagian setelah `open.spotify.com/track/`. Kolom `video` opsional.

### Menambah jadwal live

Buka `data/events.php`, lalu tambahkan satu blok. Tulis jamnya dalam waktu Jepang, contoh `2027-05-29T18:30:00+09:00`.

## Menjalankan di komputer (XAMPP)

1. Copy folder ini ke `C:\xampp\htdocs\ave-mujica-v2.0`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP.
3. Di phpMyAdmin, import `database/schema.sql`. Database `avem` akan dibuat otomatis. Database v1 juga bisa dipakai langsung, karena strukturnya sama.
4. Copy `config.example.php` menjadi `config.local.php`, lalu sesuaikan isinya kalau perlu.
5. Buka `http://localhost/ave-mujica-v2.0/`.

## Hosting (online)

Web ini butuh **PHP 8 + MySQL**, jadi tidak bisa di GitHub Pages atau Vercel. Ada dua pilihan:

| | InfinityFree (gratis) | Shared hosting berbayar |
|---|---|---|
| Biaya | Gratis | Sekitar belasan–puluhan ribu rupiah per bulan |
| PHP + MySQL | Ya | Ya |
| Upload MP4 sendiri | **Tidak.** Maks. 10 MB per file, sedangkan MV-mu 18–78 MB | **Ya.** Lewat File Manager / FTP |
| YouTube & Spotify embed | Ya | Ya |
| Domain | Subdomain gratis (mis. `avemujica.rf.gd`) | Bisa domain sendiri (`.my.id`, `.com`) |

Di InfinityFree pun web tetap lengkap: lagu yang file MP4-nya tidak ada otomatis memakai MV YouTube resmi.
Kalau ingin file MP4 sendiri ikut online, pilih hosting berbayar. Kodenya tidak perlu diubah.

> Catatan: MV adalah milik Bushiroad/BanG Dream!. Mengunggah ulang file MV ke web publik bisa kena laporan hak cipta (DMCA). Embed YouTube resmi adalah cara paling aman.

### Langkah deploy (berlaku untuk hosting PHP mana pun)

1. Buat akun hosting, lalu buat **database MySQL** di panel. Catat host, user, password, dan nama database.
2. Buka **phpMyAdmin** di panel, pilih database tadi, lalu **Import** `database/schema.sql`. Kalau ditolak, hapus dulu baris `CREATE DATABASE` dan `USE`.
3. Upload semua file ke folder `htdocs/` atau `public_html/` lewat File Manager atau FTP (misalnya FileZilla).
4. Di server, buat `config.local.php` dari `config.example.php`, isi data database, lalu set `'debug' => false`.
5. (Hosting berbayar) Upload file MP4 ke folder `videos/`. Nama file lihat `videos/README.md`.
6. Buka domainmu dan coba daftar, login, kirim komentar, lalu hapus komentar.

## Tech stack

PHP 8 (mysqli, prepared statements, CSRF token, `password_hash`) · MySQL · HTML · CSS · JavaScript (tanpa library)

## Sumber data

- Jadwal live dan diskografi: [bang-dream.com](https://bang-dream.com/events/?artist=avemujica)
- Music video: channel YouTube resmi Ave Mujica dan バンドリちゃんねる☆
- Lagu: [Ave Mujica di Spotify](https://open.spotify.com/artist/5BKIH2Kwc6LbetG04Boai7)

Data dicek terakhir pada 26 November 2026.

## Disclaimer

Proyek penggemar non-komersial untuk belajar. Ave Mujica, nama member, logo, gambar, dan music video adalah milik pemiliknya masing-masing (BanG Dream! Project / Bushiroad). Tidak ada maksud melanggar hak cipta.

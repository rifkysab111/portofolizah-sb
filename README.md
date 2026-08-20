# PORTOFOLIZAH

Website portfolio aesthetic pink/cloudy/minimalis untuk Aininna Halizah Rahma.

## Fitur
- Landing page responsif dengan animasi reveal saat scroll.
- Menu About Me, Gallery, Contact.
- Gallery 4 kategori: Master of Ceremony, Public Speaker, Voice Actor, Reviewer.
- Setiap kategori memiliki satu foto utama yang bisa diganti melalui dashboard admin.
- Admin login dengan session.
- Upload foto dibatasi JPG/PNG/WEBP dan maksimal 5 MB.
- Data profil, kontak, judul, deskripsi, dan foto tersimpan di MySQL.

## Instalasi XAMPP / Laragon
1. Copy folder `portofolizah` ke `htdocs` (XAMPP) atau document root Laragon.
2. Jalankan Apache dan MySQL.
3. Buka phpMyAdmin.
4. Import file `database.sql`.
5. Buka `config/config.php` dan sesuaikan `$user` / `$pass` bila MySQL Anda memiliki password.
6. Akses `http://localhost/portofolizah/`.
7. Dashboard admin: `http://localhost/portofolizah/admin/login.php`.

### Login awal
Username: `admin`
Password: `admin12345`

Segera ubah password admin untuk website production. Versi ini menggunakan password hash PHP, bukan menyimpan password plaintext.

## Struktur
- `index.php` — halaman publik
- `admin/` — login + dashboard
- `config/config.php` — koneksi DB + helper upload
- `database.sql` — schema + data awal
- `assets/css/` — style frontend/admin
- `assets/js/` — animasi scroll reveal
- `uploads/` — foto gallery hasil upload admin

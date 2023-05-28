# Aplikasi Kasir (Point Of Sale)
Aplikasi sistem penjualan berbasis web menggunakan framework codeigniter 4.

## Persyaratan
 - Semua persyaratan mengacu ke dokumentasi codeigniter 4. [Dokumentasi](https://codeigniter.com/user_guide/intro/requirements.html)

## Cara Install
 - Masuk ke direktori `xampp/htdocs` dan gunakan `git bash here`
 - Atau Buka `command prompt` masuk ke direktori `xampp/htdocs` pada cmd
 - Download project ini. `git clone https://github.com/Fiztick/sistem_pos_pakaian`
 - Masuk ke direktori `cd sistem_pos_pakaian`
 - Jalankan `composer update` untuk mendownload dependensinya.
 - Ganti nama file `env.sampel` menjadi `.env`
 - Ubah kofigurasi databasenya (sesuaikan dengan konfigurasi database anda):
    - `database.default.hostname = localhost`
    - `database.default.database = posci4`
    - `database.default.username = root`
    - `database.default.password = `
    - `database.default.DBDriver = MySQLi`
 - Buat nama database `posci4` kemudian import file `posci4-05-27-2023.sql`
 - Jalankan aplikasi `php spark serve` kemudian buka urlnya `http://localhost:8080/`
 - Akun untuk login :
    - Username : superadmin / admin / kasir
    - Password : 123456

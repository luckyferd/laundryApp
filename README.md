# LaundryApp — Dashboard Manajemen Laundry

Aplikasi web manajemen laundry berbasis **Laravel 9** dengan dua role pengguna: **Admin** dan **Kasir**.

## Fitur

| Fitur | Admin | Kasir |
|---|:---:|:---:|
| Login (akun nonaktif otomatis ditolak / di-logout) | ✅ | ✅ |
| **Dashboard pemasukan**: hari ini, minggu, bulan (vs bulan lalu), tahun, piutang, grafik 30 hari & 12 bulan | ✅ | — |
| **Status laundry**: Baru → Diproses → Selesai → Diambil, filter & pencarian, cucian lewat estimasi | ✅ | ✅ |
| Transaksi baru (multi layanan, total & estimasi selesai dihitung otomatis), pelunasan, cetak nota | ✅ | ✅ |
| Data pelanggan | ✅ | ✅ (tanpa hapus) |
| Laporan pemasukan per periode + export CSV | ✅ | — |
| Layanan & harga (kg / pcs / meter) | ✅ | — |
| Kelola pengguna (admin / kasir, aktif / nonaktif) | ✅ | — |
| Hapus transaksi | ✅ | — |

Aturan bisnis:
- Pemasukan dihitung dari transaksi **lunas** berdasarkan **tanggal bayar**.
- Cucian belum lunas tidak bisa diubah ke status *Diambil*.
- Layanan yang sudah pernah dipakai tidak dihapus, melainkan dinonaktifkan.
- Admin tidak dapat menghapus, menonaktifkan, atau menurunkan role akunnya sendiri.

## Kebutuhan

- PHP 8.0 – 8.3 (ekstensi: pdo_mysql atau pdo_sqlite, mbstring, openssl, xml)
- Composer 2
- MySQL / MariaDB (atau SQLite)

Tampilan memakai Bootstrap 5, Bootstrap Icons, dan Chart.js dari CDN, jadi **tidak perlu `npm`** (browser butuh koneksi internet).

## Instalasi

```bash
git clone https://github.com/luckyferd/laundryApp.git
cd laundryApp
composer install
cp .env.example .env
php artisan key:generate
```

Atur koneksi database di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry_app
DB_USERNAME=root
DB_PASSWORD=
```

> Ingin cepat tanpa MySQL? Pakai SQLite: `DB_CONNECTION=sqlite`, hapus baris `DB_DATABASE`, lalu buat file kosong `database/database.sqlite`.

Migrasi + data awal, lalu jalankan:

```bash
php artisan migrate --seed
php artisan serve
```

Buka http://127.0.0.1:8000.

### Akun bawaan

| Role | Email | Password |
|---|---|---|
| Admin | `admin@laundry.test` | `password` |
| Kasir | `kasir@laundry.test` | `password` |

**Segera ganti password** lewat menu *Pengguna* setelah login pertama.

`php artisan migrate --seed` juga mengisi daftar layanan contoh dan **data demo** (25 pelanggan, ±90 hari transaksi) agar grafik langsung terisi. Untuk produksi tanpa data demo:

```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ServiceSeeder
```

## Struktur Utama

```
app/Http/Controllers/
  Auth/LoginController.php    login & logout
  DashboardController.php     ringkasan pemasukan, status, grafik (admin)
  OrderController.php         transaksi, status laundry, pembayaran, nota
  CustomerController.php      pelanggan
  ServiceController.php       layanan & harga (admin)
  UserController.php          pengguna admin/kasir (admin)
  ReportController.php        laporan pemasukan + CSV (admin)
app/Http/Middleware/
  EnsureUserHasRole.php       middleware `role:admin` / `role:admin,kasir`
  EnsureUserIsActive.php      logout paksa akun yang dinonaktifkan
app/Models/                   User, Customer, Service, Order, OrderItem
resources/views/              Blade + Bootstrap 5
routes/web.php                pembagian route per role
```

Tabel: `users` (role `admin`/`kasir`), `customers`, `services`, `orders` (status, status bayar, total, tanggal terima/estimasi/bayar/ambil), `order_items`.

## Pengujian

```bash
php artisan test
```

Test memakai SQLite in-memory (lihat `phpunit.xml`), mencakup login & hak akses per role, pembuatan transaksi, alur status & pembayaran, perhitungan pemasukan dashboard, laporan/CSV, serta manajemen pengguna & layanan.

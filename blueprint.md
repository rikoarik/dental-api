# Technical Blueprint & Architecture Design (Final Version)

## 1. Spesifikasi Teknis (Tech Stack)
- **Framework:** Laravel 12 (Struktur modern tanpa `Http/Kernel.php`)
- **Bahasa:** PHP 8.2+
- **Database:** MySQL 8.x
- **Autentikasi:** Laravel Sanctum (Stateful / Token-based untuk API Admin).
- **Library Tambahan:**
  - `spatie/laravel-medialibrary` v11 (Manajemen file media/gambar polimorfik tanpa tabel kustom).
  - `spatie/laravel-permission` v6 (Manajemen izin Admin/Role, disiapkan agar mudah di-scale).
- **Format Respons API:** Standar *wrapper* JSend via Trait Kustom `ApiResponser`.
- **API Documentation:** Ekspor format Postman Collection v2.1 (tersimpan di repositori pada `/docs`).

## 2. Struktur Direktori Proyek Khusus API
Sesuai standar Laravel 12, routing berada di `routes/api.php` dan dikonfigurasi melalui `bootstrap/app.php`.

```text
backend/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── RotateDailyTip.php      # Command penjadwalan rotasi tip (Cron)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Admin/              # Namespace Endpoint Terproteksi
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── NewsController.php
│   │   │   │   │   ├── ArticleController.php
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   ├── TipController.php
│   │   │   │   │   └── OrderController.php
│   │   │   │   └── Public/             # Namespace Endpoint Terbuka (Guest)
│   │   │   │       ├── HomeController.php (Agregat beranda)
│   │   │   │       ├── InteractionController.php (Hit Like & View)
│   │   │   │       └── OrderController.php (Kirim pemesanan anonim)
│   │   └── Requests/                   # FormRequest Validations (Mencegah Fat Controller)
│   ├── Models/
│   │   ├── User.php
│   │   ├── News.php
│   │   ├── Article.php
│   │   ├── Product.php
│   │   ├── Tip.php
│   │   └── Order.php
│   └── Traits/
│       └── ApiResponser.php            # Standar output API (status, message, data)
├── bootstrap/
│   └── app.php                         # Pusat registrasi Middleware, Exceptions, Routing, dan Task Scheduling
├── database/
│   └── migrations/                     # File skema database
├── docs/
│   └── DentalClinic_API_Postman.json   # Dokumentasi Kontrak API
└── routes/
    └── api.php                         # Definisi rute dengan prefix v1
```

## 3. Desain Skema Database (MySQL)

**Catatan**: *Soft-deletes* opsional untuk MVP ini kecuali ditentukan lain. Fokus pada kesederhanaan. Relasi relasional difokuskan pada tabel `orders` ke `products`.

### Tabel `news` & `articles`
Dipisah secara fisik di *database* agar manajemen lebih independen (misalnya berita adalah untuk pengumuman klinik, artikel untuk literasi edukasi gigi).
- `id` (bigint, PK)
- `title` (varchar)
- `slug` (varchar, unique)
- `content` (longtext) — *Digunakan untuk menyimpan string HTML Editor WYSIWYG*.
- `view_count` (unsigned integer, default: 0)
- `like_count` (unsigned integer, default: 0)
- `is_published` (boolean, default: 1)
- `created_at`, `updated_at` (timestamps)
- **Media (Gambar)**: Di-handle secara polimorfik oleh tabel `media` bawaan Spatie (koleksi `'cover_image'`).

### Tabel `products`
- `id` (bigint, PK)
- `name` (varchar)
- `slug` (varchar, unique)
- `description` (text, nullable)
- `price` (decimal 15,2, nullable) — *Nullable untuk produk yang membutuhkan konsultasi harga*.
- `is_active` (boolean, default: 1)
- `created_at`, `updated_at` (timestamps)

### Tabel `tips`
- `id` (bigint, PK)
- `content` (text)
- `is_active` (boolean, default: 0) — *Hanya 1 baris yang bernilai true di database pada waktu tertentu.*
- `created_at`, `updated_at` (timestamps)

### Tabel `orders`
- `id` (bigint, PK)
- `product_id` (bigint, FK ke `products.id`, on delete: cascade/restrict)
- `quantity` (integer)
- `name` (varchar) — *Nama Guest*
- `email` (varchar, nullable)
- `phone` (varchar) — *Nomor WhatsApp/HP*
- `address` (text)
- `status` (enum: 'pending', 'processing', 'completed', default: 'pending')
- `total_price` (decimal 15,2, default: 0) — *Snapshot harga (Products.price x qty) yang di-generate backend saat order masuk.*
- `created_at`, `updated_at` (timestamps)

## 4. Desain URL API (Routes & Endpoint)

Semua endpoint dilindungi prefix: `/api/v1`

### A. Rute Publik (Guest Area) - `Rate Limiting Diterapkan`
- `GET /home` -> Menarik Agregat: 3 Berita terbaru, 5 Artikel terpopuler, 1 Tips aktif.
- `GET /articles` -> List artikel publik (berpaginasi).
- `GET /articles/{slug}` -> Detail artikel (otomatis *trigger* increment `view_count` di backend).
- `POST
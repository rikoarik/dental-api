# Product Requirements Document (PRD) — Dental Health API

## 0. Interpretasi Permintaan Mentah
- **Niat Pengguna Asli**: Membangun backend REST API murni untuk aplikasi kesehatan gigi (klinik) yang mencakup edukasi (artikel/berita) dan e-commerce ringan (pesan produk kesehatan gigi).
- **Konteks Bisnis**: Klinik gigi membutuhkan CMS dan backend terpusat untuk mengelola konten edukasi dan pesanan produk tanpa memaksakan pengguna (pasien) untuk mendaftar/login, guna mengurangi friksi pengguna.
- **Masalah Pengguna Inti**: Pasien sering malas membaca artikel kesehatan atau membeli produk jika diwajibkan membuat akun. Di sisi lain, klinik butuh sistem tertutup yang aman untuk mengelola data.
- **Tipe Produk**: REST API Backend / Headless CMS.
- **Batasan MVP (MVP Boundary)**: Hanya Backend API menggunakan Laravel 12. Dokumentasi hanya menggunakan Postman. Tidak ada pengembangan frontend web/mobile.
- **Ide Non-MVP (Ditunda)**: Registrasi pasien, sistem booking/janji temu dokter, rekam medis elektronik, integrasi Payment Gateway (Xendit/Midtrans).
- **Ketidakpastian Kunci**: Apakah harga produk bersifat wajib? (Diasumsikan bisa *null* untuk kasus "Hubungi untuk harga").
- **Default Cerdas Digunakan**: Penggunaan standar format *JSON Response* seragam (JSend) agar memudahkan integrasi tim frontend di masa depan `[Default — sesuaikan jika perlu]`.

## 0b. Pemindaian Pasar & Referensi (Market & Reference Scan)

| Referensi | Segmen Pengguna | Janji Inti | Fitur Terkuat | Kelemahan / Peluang | Pelajaran Produk |
|----------|--------------|--------------|-------------------|------------------------|----------------|
| **Stripe API** | Developer | Integrasi yang mulus | Dokumentasi jelas, *error handling* standar | Terlalu kompleks untuk MVP | Gunakan arsitektur pembungkus *response* (JSend/ApiResponser) agar output konsisten. |
| **Ghost CMS** | Admin / Editor | *Headless Publishing* | Pemisahan rute Publik dan Admin | Fokus murni pada blog | Terapkan pemisahan grup *routing* (`/api/v1/public` vs `/api/v1/admin`) secara ketat. |
| **Halodoc** | Pasien Publik | Akses kesehatan instan | Artikel dan apotek terintegrasi | Sering memaksa login | Kita berikan akses *checkout* produk kesehatan gigi secara *anonymous* (Guest Order). |

## 0c. Kesenjangan Peluang (Opportunity Gap)
Mayoritas aplikasi rumah sakit atau klinik sangat kaku dan menuntut proses *onboarding* (pendaftaran) yang panjang. API ini mengeksploitasi celah **"Frictionless Guest Experience"**, di mana siapa pun bisa membaca artikel, memberikan "*Like*", dan memesan sikat gigi/pasta gigi khusus dengan mulus, sementara sisi keamanan sepenuhnya dipegang oleh Admin di balik layar.

## 0d. Uji Ketajaman MVP
- **Kecepatan Bangun (Speed)**: 9/10 (Stack standar Laravel sangat matang)
- **Nilai Pengguna (User Value)**: 8/10
- **Diferensiasi**: 8/10 (Anonimitas penuh untuk kemudahan transaksi)
- **Potensi Monetisasi**: 7/10 (Direct order tanpa payment gateway)
- **Kesederhanaan Teknis**: 9/10
- **Kesiapan Konten**: 8/10
*Skor Keseluruhan: Sangat Baik. Lingkup MVP sudah terkunci dan siap dieksekusi.*

## 0e. Catatan Keputusan (Decision Log)

| Keputusan | Pilihan | Alasan | Alternatif Ditolak | Dampak |
|----------|--------|-----|----------------------|-------------------|
| **Format API** | Standar Trait `ApiResponser` | Memudahkan developer Frontend menangani respons & *error*. | Format JSON bawaan Laravel yang tidak terstruktur. | Mengurangi bug saat integrasi FE di masa depan. |
| **Metode Order** | Guest (Tanpa Login) | Menurunkan angka *cart abandonment* (pembatalan pesan). | Registrasi wajib via Sanctum bagi user. | Harus menambahkan *Rate Limiting* (Throttle) agar tidak diserang bot. |
| **Upload Gambar** | Spatie Media Library | Bersih, polimorfik, mudah dikembangkan untuk banyak gambar. | Menyimpan path file secara manual di kolom tabel. | Tabel utama bersih, *controller* tidak dipenuhi logika *file system*. |

---

## 1. Piagam Proyek (Project Charter)
- **Nama Proyek**: Dental Health API (Headless API)
- **Visi**: Menjadi mesin penggerak ekosistem digital klinik gigi yang tanpa hambatan dan edukatif.
- **Misi**: Menyediakan antarmuka data (API) yang aman, standar, dan cepat untuk menghubungkan manajemen klinik (Admin) dengan pasien anonim (Guest).
- **Pernyataan Masalah**: Klinik sulit mengedukasi dan menjual produk ringan kepada calon pasien tanpa memaksa mereka melewati proses pendaftaran yang rumit.
- **Nilai Proposisi**: *Backend API* tangguh yang memungkinkan interaksi anonim instan (baca, *like*, order) dengan kendali konten tersentralisasi.
- **Hasil Utama**: Kode sumber Laravel 12 beserta Koleksi Postman yang mencakup 100% *endpoint*.
- **Janji MVP**: Semua operasi CRUD Admin dilindungi Sanctum, semua interaksi publik aman dengan pembatasan *rate-limiting*.

## 2. Pengguna Target & Persona

### Persona 1: Admin Klinik (Content Manager)
- **Konteks Penggunaan**: Mengelola konten via dashboard web (yang akan dibangun terpisah di masa depan).
- **Tujuan**: Memublikasikan artikel, mengupdate tip harian, memproses pesanan masuk.
- **Titik Sakit (*Pain Points*)**: Kesulitan melacak pesanan dari pasien anonim jika data tidak terpusat.
- **Kriteria Sukses**: Semua pesanan dapat diubah statusnya menjadi "Selesai".

### Persona 2: Developer (API Consumer)
- **Konteks Penggunaan**: Membaca Postman Collection untuk membuat aplikasi Frontend.
- **Tujuan**: Menyelesaikan integrasi UI dengan backend secepat mungkin.
- **Titik Sakit (*Pain Points*)**: Dokumentasi API yang buruk, respons JSON yang struktur datanya selalu berubah.
- **Kriteria Sukses**: *Response format* dapat ditebak (`status`, `message`, `data`).

## 3. Peta Cerita Pengguna (User Story Map)

### Epic 1: Autentikasi & Profil Admin
- **Sebagai Admin**, saya ingin login dengan email & password, sehingga mendapatkan akses token Sanctum.
- **Sebagai Admin**, saya ingin memperbarui profil saya (nama, email), sehingga data admin tetap relevan.

### Epic 2: Manajemen Konten Edukasi
- **Sebagai Admin**, saya ingin membuat Berita/Artikel menggunakan *Rich Text* (HTML) dan mengunggah *cover image*, sehingga konten terlihat menarik.
- **Sebagai Guest**, saya ingin melihat daftar Berita dan Artikel Terpopuler di halaman Beranda secara instan, tanpa perlu login.
- **Sebagai Guest**, saya ingin menekan tombol *Like* pada Artikel, sehingga artikel tersebut mendapatkan *like_count* bertambah.

### Epic 3: Pemesanan & Produk
- **Sebagai Guest**, saya ingin mengirimkan form pesanan (nama, email, no HP, alamat, ID produk, qty), sehingga saya bisa membeli produk tanpa mendaftar.
  - *Acceptance Criteria*:
    - [ ] Endpoint me-return sukses dan mencatat total_price saat itu (snapshot harga).
    - [ ] `rate_limiting` diaktifkan untuk mencegah *spamming* form order.
- **Sebagai Admin**, saya ingin mengubah status pesanan (Pending -> Diproses -> Selesai), sehingga pengunjung dapat dilayani.

## 4. Prioritas Fitur (MoSCoW)

| Prioritas | Fitur | Alasan |
|----------|----------|--------|
| **Must-Have** | CRUD Berita, Artikel, Produk, Order | Core bisnis dari MVP ini. |
| **Must-Have** | Auth Sanctum (Admin) & Spatie Media Library | Fondasi keamanan dan manajemen media yang bersih. |
| **Must-Have** | Endpoint agregat `/home` & Scheduler Cron Tip | Sesuai spesifikasi untuk mempermudah render beranda aplikasi. |
| **Should-Have** | *Rate Limiting* / Throttle pada endpoint publik | Melindungi DB dari eksploitasi dan bot tanpa login. |
| **Could-Have** | Pagination kustom dengan parameter `?limit=` | Menambah fleksibilitas untuk *frontend developer*. |
| **Won't-Have** | User Auth (Pasien) & Payment Gateway | Secara eksplisit berada di luar ruang lingkup (Out of scope). |

### 4b. Penguncian Lingkup MVP
**Alur Pengguna MVP (Dari sisi API):**
1. Aplikasi melakukan request `GET /api/v1/home` -> Mereturn berita, artikel populer, dan 1 tips harian.
2. Pengguna membaca detail artikel -> Memicu `GET /api/v1/articles/{id}` (auto increment view_count).
3. Pengguna menekan suka -> Memicu `POST /api/v1/articles/{id}/like` (auto increment like_count).
4. Pengguna melihat katalog -> Memicu `GET /api/v1/products`.
5. Pengguna memesan -> Memicu `POST /api/v1/orders`.
6. Admin login `POST /api/v1/admin/login` -> Mendapatkan Bearer Token.
7. Admin mereview order `GET /api/v1/admin/orders`.

**Kriteria Selesai (Definition of Done):**
- [ ] Koleksi Postman di-*export* dan dites berjalan sukses.
- [ ] Semua endpoint mematuhi format Trait `ApiResponse`.
- [ ] Upload gambar menggunakan *multipart/form-data* berhasil via API.

## 5. Arahan Desain (Design Direction - API DX Edition)
*Karena ini adalah Backend API tanpa Frontend UI, Desain difokuskan pada Pengalaman Pengembang (Developer Experience/DX).*
- **Standar Format JSON**: Semua respons harus memiliki kunci struktur: `status` (boolean), `message` (string), `data` (object/array), dan opsional `meta` untuk paginasi.
- **Status Codes**: 200 (OK), 201 (Created), 400 (Bad Request), 401 (Unauthorized), 403 (Forbidden), 404 (Not Found), 422 (Validation Error), 429 (Too Many Requests).
- **RESTful Naming**: Rute menggunakan bentuk jamak (`/products`, `/orders`, `/articles`).

## 6. Arahan Teknis (Technical Direction)
- **Stack Utama**: Laravel 12 (menggunakan standar direktori baru tanpa *Http/Kernel*), PHP 8.2+.
- **Database**: MySQL 8.x.
- **Autentikasi**: Laravel Sanctum (Token-based untuk Admin).
- **Arsitektur Media**: `spatie/laravel-medialibrary` v11. Media terkait disimpan di *local storage* untuk MVP `[Asumsi - bisa diganti ke S3 jika diperlukan]`.
- **CORS**: Harus dibuka untuk semua domain `*` `[Default — sesuaikan jika perlu]` karena frontend belum diketahui domain spesifiknya.
- **Keamanan**: *Throttle/Rate Limiting* wajib pada rute publik (Guest) seperti *Like* (Max 20/menit) dan *Order* (Max 5/menit) berbasis IP Address.

## 7. Kebutuhan Data & Konten

| Entitas | Kolom Utama (Fields) | Catatan Khusus |
|-------|--------|-------|
| **News & Articles** | `title`, `slug`, `content`, `view_count`, `like_count`, `is_published` | `content` berupa HTML. Gambar di *Spatie Media*. |
| **Products** | `name`, `slug`, `description`, `price`, `is_active` | `price` nullable (boleh kosong). |
| **Tips** | `content`, `is_active` | Scheduler otomatis merotasi `is_active` ke 1 baris acak tiap jam 00:00. |
| **Orders** | `product_id`, `quantity`, `name`, `email`, `phone`, `address`, `status`, `total_price` | `total_price` adalah *snapshot* (price * quantity) saat form dikirim. Status: pending, processing, completed. |

## 8. State, Edge Cases & Perilaku Sistem
- **Produk Tanpa Harga**: Jika `products.price` null, saat Guest memesan, maka `orders.total_price` diset `0` `[Asumsi - ini akan diinterpretasikan sebagai "Konfirmasi harga via admin" kelak]`.
- **Guest Order Validation Error**: Jika format email salah, kembalikan HTTP 422 dengan detail error agar frontend bisa memberi tahu pengguna.
- **Gambar Tidak Dikirim (Image Null)**: Pembuatan Artikel/Berita diizinkan tanpa gambar, frontend harus menyiapkan gambar *placeholder* jika API mengembalikan media kosong `[Default — sesuaikan jika perlu]`.
- **Scheduler Tip Gagal**: Jika tidak ada data di tabel *Tips*, scheduler tidak boleh *crash*. (Harus ada pengecekan count > 0).

## 9. Metrik Keberhasilan & KPI

| Metrik | Target | Cara Mengukur |
|--------|--------|----------------|
| Kecepatan Respons API (P90) | < 300ms | Laravel Telescope / Postman |
| Cakupan Endpoints | 100% | Postman Collection terisi semua |
| Akurasi Tipe Data | 0% Bug | Validasi tipe data pada FormRequest Backend |

## 10. Rencana Event Analitik (Sederhana via Database)
- API mencatat `view_count` dan `like_count` secara implisit via Database.
- Saat rute detail artikel di-*hit* (`GET /api/v1/articles/{id}`), backend akan secara otomatis melakukan `.increment('view_count')`.

## 11. Batasan & Asumsi
- **Asumsi**: Proyek ini dijalankan sebagai SPA/Mobile App dari domain/platform yang berbeda di masa depan, sehingga berbasis Stateless (Token Bearer Sanctum), bukan stateful Session/Cookies.
- **Batasan**: Unit testing adalah opsional (fokus pada penyelesaian MVP lewat *manual testing* Postman).

## 12. Penilaian Risiko (Risk Assessment)

| Risiko | Probabilitas | Dampak | Mitigasi |
|------|-------------|--------|------------|
| Serangan *Spam* Order & Like dari Guest | Tinggi | Menengah | Menerapkan `Route::middleware('throttle:10,1')` berbasis IP pada API Publik. |
| Inkonsistensi Dokumentasi | Menengah | Tinggi | Ekspor JSON dari Postman yang telah lolos *run test* wajib ditaruh di folder `/docs/`. |
| Kesalahan Kalkulasi Harga Order | Rendah | Tinggi | Lakukan perkalian harga x kuantitas di *Backend Controller*, JANGAN menerima `total_price` dari input klien (Frontend). |

## 13. Rincian Sprint (Sprint Breakdown)

| Sprint | Durasi | Tujuan | Serahan (Deliverables) |
|--------|----------|------|--------------|
| Sprint 1 | Hari 1-2 | Fondasi | Inisialisasi Laravel 12, Migrasi Database, Sanctum Auth. |
| Sprint 2 | Hari 3-4 | Publik API | Get `/home`, baca artikel, hit views, like, submit order. Scheduler Cron. |
| Sprint 3 | Hari 5-6 | Admin API & Docs | Operasi CRUD lengkap dengan Spatie Media, dokumentasi Postman, Final QA. |

## 14. Kontrak Eksekusi Agen (Agent Execution Contract)

| Agen | Input Dari PRD | Output Diharapkan | Standar Kualitas |
|------|----------------|-----------------|-------------|
| **Backend Engineer** | Blueprint Arsitektur, Model Data, Endpoints | Kode API Laravel 12 (Controllers, Models, Routes) | Bersih, DRY, menggunakan *FormRequests*, mematuhi format `ApiResponser`. |
| **QA Engineer** | PRD Edge Cases, Postman Plan | Postman Collection JSON | Mencakup otentikasi Bearer Token dan simulasi Payload *form-data*. |
| **Tech Lead** | Keseluruhan dokumen | Blueprint final & Validasi Keamanan | Tidak *over-engineering*, validasi `price` di backend dengan ketat. |

## 15. Pembagian Tugas & Delegasi (JIRA Style)

**[Peran: Backend Engineer]**
- **Task 1**: Inisialisasi proyek Laravel 12 (`laravel new backend --api`) dan setup MySQL *credentials* di `.env`.
  - **Ketergantungan**: None. Lakukan pertama.
- **Task 2**: Buat Migrasi untuk tabel (Users, News, Articles, Products, Tips, Orders) sesuai struktur di Blueprint.
  - **Ketergantungan**: Task 1.
- **Task 3**: Instalasi & konfigurasi Sanctum, Spatie MediaLibrary, Spatie Permission. Siapkan *Trait* `ApiResponse`.
  - **Ketergantungan**: Task 2.
- **Task 4**: Kerjakan Controller Publik (Home, Interaction, Order) dengan `throttle` middleware. Pastikan Snapshot harga di-generate di Backend.
  - **Ketergantungan**: Task 3.
- **Task 5**: Kerjakan Controller Admin CRUD (News, Articles, Products, Tips, Orders, Auth). Lindungi dengan middleware `auth:sanctum`.
  - **Ketergantungan**: Task 3.
- **Task 6**: Registrasi Console Command `tip:rotate` di `bootstrap/app.php` sesuai aturan Laravel 12.
  - **Ketergantungan**: Task 2.

**[Peran: QA Engineer]**
- **Task 1**: Susun Postman Collection yang merepresentasikan semua rute publik & admin, lengkap dengan variabel `{{base_url}}` dan `{{token}}`. Simpan ke `/docs/DentalClinic_API_Postman.json`.
  - **Ketergantungan**: Diblokir oleh Task 5 (Backend).
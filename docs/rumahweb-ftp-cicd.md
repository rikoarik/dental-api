# Tutorial Deploy Laravel ke Rumahweb via FTP CI/CD

Panduan ini untuk deploy **Laravel 12 API Dental Health** ke **shared hosting Rumahweb** memakai **GitHub Actions + FTP**, tanpa Terminal cPanel/SSH.

## Gambaran Alur

1. Setup domain/subdomain di cPanel Rumahweb.
2. Buat database MySQL dan user database.
3. Buat file `.env` production manual via File Manager.
4. Export database lokal ke `.sql`, lalu import via phpMyAdmin.
5. Isi GitHub Secrets FTP.
6. Push ke branch `main`, GitHub Actions upload project ke hosting.

Workflow CI/CD ada di:

```text
.github/workflows/deploy-ftp.yml
```

## Syarat Hosting

Pastikan di cPanel Rumahweb:

- PHP minimal `8.2`
- Extension umum aktif: `mbstring`, `fileinfo`, `openssl`, `pdo_mysql`, `gd`, `zip`
- Domain/subdomain bisa diarahkan ke folder `public`
- Ada akses **File Manager**, **MySQL Databases**, **phpMyAdmin**, dan **FTP Accounts**

## 0. Checklist Sebelum Mulai

Siapkan data berikut dulu supaya proses deploy tidak bolak-balik:

| Kebutuhan | Contoh |
|-----------|--------|
| Domain/subdomain API | `api.domainkamu.com` |
| Folder root Laravel | `/home/username/dental-backend` |
| Document root | `/home/username/dental-backend/public` |
| FTP host | `ftp.domainkamu.com` |
| FTP username | `username_ftp` |
| FTP password | password FTP |
| FTP target folder | `/dental-backend/` |
| Database name | `username_dental` |
| Database user | `username_dentaluser` |
| Database password | password database |
| APP_KEY | `base64:...` |

Kalau belum punya subdomain, buat dulu subdomain khusus API. Ini biasanya lebih rapi daripada memaksa domain utama.

## 0.1. Set PHP Version dan Extension di Rumahweb

Di cPanel Rumahweb cari menu:

```text
Select PHP Version
```

atau:

```text
MultiPHP Manager
```

Set versi PHP ke:

```text
PHP 8.2 atau lebih baru
```

Aktifkan extension:

```text
bcmath
ctype
curl
dom
fileinfo
filter
gd
json
mbstring
openssl
pdo
pdo_mysql
session
tokenizer
xml
zip
```

Minimal yang paling penting untuk project ini:

```text
mbstring, fileinfo, openssl, pdo_mysql, gd, zip
```

Kalau salah satu tidak aktif, upload gambar, database, atau Laravel bootstrap bisa error 500.

## 1. Pilih Struktur Folder Hosting

### Opsi A — Direkomendasikan

Buat folder project di luar `public_html`:

```text
/home/username/dental-backend
├── app
├── bootstrap
├── config
├── database
├── public
├── routes
├── storage
├── vendor
├── .env
└── artisan
```

Lalu arahkan **Document Root** domain/subdomain ke:

```text
/home/username/dental-backend/public
```

Ini paling aman karena hanya folder `public` yang bisa diakses browser.

### Opsi B — Kalau Rumahweb Tidak Bisa Ubah Document Root Domain Utama

Buat **subdomain** di cPanel, misalnya:

```text
api.domainkamu.com
```

Saat membuat subdomain, set document root ke:

```text
dental-backend/public
```

Gunakan subdomain ini untuk API production:

```text
https://api.domainkamu.com/api/v1
```

### Opsi C — Terpaksa Pakai `public_html` Domain Utama

Opsi ini **tidak direkomendasikan**, tapi kadang dipakai kalau hosting tidak bisa mengubah document root domain utama dan tidak mau pakai subdomain.

Struktur:

```text
/home/username/laravel-app
├── app
├── bootstrap
├── config
├── routes
├── storage
├── vendor
├── .env
└── artisan

/home/username/public_html
├── index.php
├── .htaccess
├── build
└── storage
```

Artinya:

1. Isi folder `public` Laravel dipindah/di-copy ke `public_html`.
2. File `index.php` di `public_html` harus disesuaikan path-nya.

Default Laravel:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

Kalau root Laravel ada di `/home/username/laravel-app`, ubah menjadi:

```php
require __DIR__.'/../laravel-app/vendor/autoload.php';
$app = require_once __DIR__.'/../laravel-app/bootstrap/app.php';
```

Catatan:

- Workflow FTP saat ini mengupload struktur Laravel normal ke `FTP_SERVER_DIR`.
- Kalau memakai opsi C, deployment perlu workflow berbeda karena `public` harus dipisah ke `public_html`.
- Untuk project ini, lebih disarankan pakai **Opsi A** atau **Opsi B**.

## 2. Buat Database di Rumahweb

Di cPanel:

```text
MySQL Databases
```

Buat:

1. Database, contoh `username_dental`
2. User database, contoh `username_dentaluser`
3. Password database
4. Add User To Database
5. Centang **ALL PRIVILEGES**

Catat data ini:

```text
DB_DATABASE=username_dental
DB_USERNAME=username_dentaluser
DB_PASSWORD=password_database
DB_HOST=localhost
DB_PORT=3306
```

## 3. Buat `.env` Production di Hosting

Di cPanel:

```text
File Manager -> masuk ke folder dental-backend -> New File -> .env
```

Isi contoh:

```env
APP_NAME="Dental Health"
APP_ENV=production
APP_KEY=base64:ISI_DENGAN_KEY_PRODUCTION
APP_DEBUG=false
APP_URL=https://api.domainkamu.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_dental
DB_USERNAME=username_dentaluser
DB_PASSWORD=password_database

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
QUEUE_CONNECTION=database
CACHE_STORE=database

# Penting untuk Rumahweb tanpa Terminal/SSH.
# Tidak perlu php artisan storage:link.
FILESYSTEM_DISK=public_uploads
MEDIA_DISK=public_uploads

MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=mail.bitssolution.id
MAIL_PORT=465
MAIL_USERNAME=dental-health@bitssolution.id
MAIL_PASSWORD=ISI_PASSWORD_EMAIL_DENTAL_HEALTH
MAIL_FROM_ADDRESS=dental-health@bitssolution.id
MAIL_FROM_NAME="${APP_NAME}"
```

Generate `APP_KEY` dari lokal:

```bash
php artisan key:generate --show
```

Copy hasilnya ke:

```env
APP_KEY=base64:...
```

## 4. Siapkan Folder Upload Gambar

Karena tidak ada Terminal cPanel, kita tidak pakai:

```bash
php artisan storage:link
```

Project sudah disiapkan memakai disk `public_uploads`, jadi media akan langsung masuk ke:

```text
public/storage
```

Buat manual folder ini di File Manager:

```text
dental-backend/public/storage
```

Kalau ada masalah permission upload, set permission folder:

```text
755
```

Jika masih gagal, coba:

```text
775
```

## 5. Export Database dari Lokal

Karena server tidak punya Terminal, migration dijalankan di lokal lalu hasil database di-import via phpMyAdmin.

Pastikan database lokal sudah sesuai:

```bash
php artisan migrate
php artisan db:seed
```

### Export Struktur Saja

Gunakan ini kalau production ingin database kosong:

```bash
mysqldump -u root -p --no-data dental_clinic > dental_schema.sql
```

### Export Struktur + Dummy Data

Gunakan ini kalau production ingin langsung ada banner, artikel, produk, FAQ, user demo:

```bash
mysqldump -u root -p dental_clinic > dental_full.sql
```

### Export via phpMyAdmin Lokal

Kalau tidak mau pakai command `mysqldump`, pakai phpMyAdmin lokal:

1. Buka phpMyAdmin lokal.
2. Pilih database `dental_clinic`.
3. Klik **Export**.
4. Pilih **Custom** jika ingin pilih tabel.
5. Format: **SQL**.
6. Klik **Go**.

Untuk production kosong, export **structure only**. Untuk langsung ada dummy data, export **structure + data**.

### Tabel yang Wajib Ada

Pastikan SQL mengandung tabel Laravel dan aplikasi:

```text
users
roles
permissions
model_has_roles
personal_access_tokens
banners
news
articles
products
tips
faqs
bookmarks
media
cache
jobs
sessions
```

Nama tabel bisa bertambah sesuai migration terbaru.

## 6. Import Database ke Rumahweb

Di cPanel:

```text
phpMyAdmin -> pilih database production -> Import
```

Upload salah satu file:

```text
dental_schema.sql
```

atau:

```text
dental_full.sql
```

Klik **Go**.

Jika import gagal karena file terlalu besar, gunakan export struktur saja dulu, lalu import data per tabel yang dibutuhkan.

Setelah import, cek tab **Structure** di phpMyAdmin dan pastikan tabel-tabel muncul. Kalau tabel belum muncul, aplikasi akan error 500 saat endpoint dipanggil.

## 7. Buat FTP Account / Ambil Data FTP

Di cPanel:

```text
FTP Accounts
```

Gunakan akun FTP utama atau buat akun baru khusus deploy.

Catat:

```text
FTP Host: ftp.domainkamu.com
FTP Username: username_ftp
FTP Password: password_ftp
FTP Folder tujuan: /dental-backend/
```

Untuk `FTP_SERVER_DIR`, gunakan folder root Laravel, bukan folder `public`.

Contoh:

```text
/dental-backend/
```

atau kalau project ada di `public_html`:

```text
/public_html/dental-backend/
```

### Catatan FTP Rumahweb

Beberapa hosting memakai path relatif terhadap home FTP user. Jadi nilai `FTP_SERVER_DIR` bisa berbeda:

| Kondisi | Contoh `FTP_SERVER_DIR` |
|---------|--------------------------|
| FTP user diarahkan ke home account | `/dental-backend/` |
| FTP user diarahkan ke `public_html` | `/dental-backend/` atau `/` |
| Project di dalam `public_html` | `/public_html/dental-backend/` |

Kalau deploy sukses tapi file tidak berubah, hampir pasti `FTP_SERVER_DIR` salah.

Cara cek:

1. Login FTP via FileZilla.
2. Lihat folder yang muncul pertama kali.
3. Masuk sampai menemukan folder project Laravel.
4. Path itulah yang dipakai untuk `FTP_SERVER_DIR`.

Untuk FTP connection:

```text
Protocol: FTP
Encryption: Use explicit FTP over TLS if available
Port: 21
Transfer mode: Passive
```

## 8. Setup GitHub Secrets

Di GitHub repository:

```text
Settings -> Secrets and variables -> Actions -> New repository secret
```

Tambahkan:

| Secret | Contoh Isi |
|--------|------------|
| `FTP_SERVER` | `ftp.domainkamu.com` |
| `FTP_USERNAME` | `username_ftp` |
| `FTP_PASSWORD` | `password_ftp` |
| `FTP_SERVER_DIR` | `/dental-backend/` |

Jangan simpan credential FTP atau `.env` di repository.

Jika repository memakai branch `master`, ubah workflow:

```yaml
on:
  push:
    branches:
      - master
```

Default workflow project ini memakai branch:

```text
main
```

## 9. Deploy dari GitHub Actions

Deploy otomatis berjalan setiap push ke branch:

```text
main
```

Deploy manual:

```text
GitHub -> Actions -> Deploy to Rumahweb Shared Hosting -> Run workflow
```

Workflow akan:

1. Install dependency PHP
2. Run test
3. Build asset Vite
4. Install Composer production `--no-dev`
5. Upload hasil build ke Rumahweb via FTP

Workflow **tidak upload**:

```text
.env
.github
.git
node_modules
tests
public/storage
storage/logs
storage/framework/cache
storage/framework/sessions
storage/framework/views
```

### Kalau Deploy Pertama Kali

Urutan aman:

1. Buat folder project di hosting.
2. Buat `.env` manual di hosting.
3. Buat folder `public/storage` manual.
4. Import database via phpMyAdmin.
5. Isi GitHub Secrets.
6. Run workflow deploy.

Jangan menjalankan deploy sebelum `.env` dibuat kalau ingin langsung test API setelah upload selesai.

### Kalau Deploy Gagal di GitHub Actions

Cek tab:

```text
GitHub -> Actions -> Deploy to Rumahweb Shared Hosting -> failed run
```

Penyebab umum:

| Step | Penyebab |
|------|----------|
| `Run tests` | test lokal gagal |
| `npm ci` | `package-lock.json` tidak sinkron |
| `npm run build` | error asset/Vite |
| `composer install --no-dev` | package PHP tidak kompatibel |
| `Deploy via FTP` | FTP credential/path salah |

Kalau gagal di FTP:

- Cek `FTP_SERVER`
- Cek `FTP_USERNAME`
- Cek `FTP_PASSWORD`
- Cek `FTP_SERVER_DIR`
- Pastikan FTP account tidak dibatasi ke folder lain

## 10. Cek Hasil Deploy

Buka:

```text
https://api.domainkamu.com/api/documentation
```

Cek endpoint publik:

```text
https://api.domainkamu.com/api/v1/public/faqs
https://api.domainkamu.com/api/v1/public/banners
```

Expected response:

```json
{
  "status": true,
  "message": "Data FAQ berhasil dimuat.",
  "data": []
}
```

Kalau pakai dummy data, `data` harus berisi list FAQ/banner.

### Test Login Admin

Jika import `dental_full.sql`, coba login admin:

```http
POST https://api.domainkamu.com/api/v1/admin/login
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "email": "admin@clinic.com",
  "password": "password"
}
```

Response sukses:

```json
{
  "status": true,
  "message": "Login berhasil",
  "data": {
    "user": {},
    "token": "..."
  }
}
```

### Test Upload Gambar

Di Swagger:

1. Login admin.
2. Copy token.
3. Klik **Authorize**.
4. Coba endpoint create banner/product/news dengan file image.
5. Cek URL gambar di response.

URL harus mengarah ke:

```text
https://api.domainkamu.com/storage/...
```

## 11. Update Berikutnya

Untuk update code:

```bash
git add .
git commit -m "Update API"
git push origin main
```

GitHub Actions akan deploy otomatis.

Kalau ada migration baru:

1. Jalankan migration di lokal.
2. Export SQL perubahan dari lokal.
3. Import ke phpMyAdmin Rumahweb.
4. Push code ke `main`.

### Cara Aman Update Database Production

Karena tidak ada Terminal, jangan asal import full SQL ke production kalau sudah ada data real. Pilih salah satu:

1. Buat migration baru di lokal.
2. Lihat perubahan tabel yang dihasilkan.
3. Export hanya SQL `ALTER TABLE`/tabel baru.
4. Import SQL perubahan ke phpMyAdmin production.

Kalau belum ada data real, boleh import ulang `dental_full.sql`.

Kalau sudah ada data real, jangan drop table kecuali sudah backup.

### Backup Sebelum Update

Sebelum import SQL baru:

1. Buka phpMyAdmin Rumahweb.
2. Pilih database production.
3. Klik **Export**.
4. Simpan backup SQL.

Nama file contoh:

```text
backup-production-2026-06-18.sql
```

## Checklist Deploy Pertama

- [ ] Domain/subdomain sudah mengarah ke folder `public`
- [ ] PHP 8.2+ aktif
- [ ] Database dan user database sudah dibuat
- [ ] User database sudah diberi **ALL PRIVILEGES**
- [ ] `.env` production sudah dibuat manual
- [ ] `APP_KEY` sudah diisi
- [ ] `APP_URL` sudah pakai domain production
- [ ] `FILESYSTEM_DISK=public_uploads`
- [ ] `MEDIA_DISK=public_uploads`
- [ ] Folder `public/storage` sudah dibuat
- [ ] SQL sudah diimport via phpMyAdmin
- [ ] GitHub Secrets FTP sudah diisi
- [ ] GitHub Actions berhasil
- [ ] Swagger bisa dibuka
- [ ] Endpoint `/api/v1/public/faqs` return JSON

## Checklist Update Rutin

- [ ] Pull latest branch lokal
- [ ] Test lokal jalan
- [ ] Kalau ada migration, siapkan SQL update
- [ ] Backup database production
- [ ] Push ke `main`
- [ ] GitHub Actions sukses
- [ ] Test endpoint public
- [ ] Test endpoint admin jika ada perubahan admin

## Troubleshooting

### 500 Internal Server Error

Cek file log via File Manager:

```text
storage/logs/laravel.log
```

Penyebab umum:

- `.env` belum ada di hosting
- `APP_KEY` kosong/salah
- `APP_DEBUG=false` menutupi error detail
- database credential salah
- database belum diimport
- permission folder `storage` atau `bootstrap/cache` bermasalah
- PHP version masih di bawah 8.2
- extension PHP belum aktif
- file `.env` typo, misalnya `DB_DATABASE` salah

Untuk debug sementara, ubah:

```env
APP_DEBUG=true
```

Setelah ketemu error, balikin:

```env
APP_DEBUG=false
```

### 404 untuk semua endpoint API

Penyebab paling umum: document root bukan ke folder `public`.

Harus:

```text
dental-backend/public
```

Pastikan juga file ini ada:

```text
public/.htaccess
```

Kalau endpoint `/api/documentation` 404 tapi root domain menampilkan folder listing atau halaman default, berarti document root belum benar.

### Gambar Tidak Muncul

Cek:

```env
APP_URL=https://api.domainkamu.com
FILESYSTEM_DISK=public_uploads
MEDIA_DISK=public_uploads
```

Pastikan folder ini ada:

```text
public/storage
```

URL gambar harus berbentuk:

```text
https://api.domainkamu.com/storage/1/file.jpg
```

Kalau file berhasil terupload tapi URL 404:

- Cek file benar-benar ada di `public/storage`
- Cek permission folder `public/storage`
- Cek `APP_URL`
- Cek `MEDIA_DISK=public_uploads`

### Deploy Sukses Tapi File Tidak Berubah

Cek `FTP_SERVER_DIR` di GitHub Secrets. Harus mengarah ke folder root project Laravel yang benar.

Contoh benar:

```text
/dental-backend/
```

Contoh salah:

```text
/dental-backend/public/
```

Solusi cepat:

1. Login FileZilla.
2. Upload file kecil test, misalnya `deploy-test.txt`.
3. Lihat di File Manager Rumahweb muncul di folder mana.
4. Sesuaikan `FTP_SERVER_DIR`.

### Login Admin Gagal

Jika database production kosong, admin belum ada. Import `dental_full.sql` atau insert admin manual lewat phpMyAdmin.

Default dummy seeder:

```text
admin@clinic.com
password
```

### CORS / Network Failure

Pastikan FE memakai base URL production:

```text
https://api.domainkamu.com/api/v1
```

Jangan pakai:

```text
http://127.0.0.1:8000/api/v1
```

untuk app yang sudah jalan di device/hosting.

### Authorization Header Hilang

Kalau endpoint admin selalu `Unauthenticated` padahal token benar, cek `public/.htaccess` harus punya bagian ini:

```apache
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```

Project ini sudah punya rule tersebut. Pastikan file `.htaccess` ikut terupload.

### Error Permission Storage

Kalau muncul error tidak bisa menulis file/cache:

Set permission folder berikut via File Manager:

```text
storage
bootstrap/cache
public/storage
```

Mulai dari:

```text
755
```

Kalau masih gagal di shared hosting tertentu:

```text
775
```

Hindari `777` kecuali benar-benar terakhir dan sementara.

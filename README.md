# dental-api

Backend API for Dental Clinic Application. Built with Laravel 12.

## Deploy ke Rumahweb

CI/CD FTP untuk shared hosting Rumahweb tersedia di:

```text
.github/workflows/deploy-ftp.yml
```

Panduan setup hosting, `.env`, database, dan GitHub Secrets:

```text
docs/rumahweb-ftp-cicd.md
```

## Swagger UI

Jalankan server lalu buka:

```
http://127.0.0.1:8000/api/documentation
```

Spec OpenAPI (YAML): `http://127.0.0.1:8000/api/docs/openapi.yaml`

Untuk endpoint yang butuh auth, klik **Authorize** dan masukkan token Sanctum (tanpa prefix `Bearer`, Swagger menambahkannya otomatis).

## Tunnel dengan ngrok

**Bad Gateway** hampir selalu karena Laravel belum jalan atau ngrok mengarah ke port yang salah.

### Setup sekali

```bash
# 1. Daftar & ambil authtoken di https://dashboard.ngrok.com/get-started/your-authtoken
ngrok config add-authtoken <TOKEN_KAMU>
```

### Jalankan (2 terminal)

```bash
# Terminal 1 — backend HARUS jalan dulu
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 — tunnel (port harus sama: 8000)
ngrok http 8000
```

Atau pakai script helper:

```bash
chmod +x scripts/tunnel-ngrok.sh
./scripts/tunnel-ngrok.sh
```

### URL setelah ngrok aktif

Ngrok menampilkan `Forwarding https://xxxx.ngrok-free.app -> http://localhost:8000`

- Swagger: `https://xxxx.ngrok-free.app/api/documentation`
- API: `https://xxxx.ngrok-free.app/api/v1/...`

Update `.env` agar URL gambar benar:

```env
APP_URL=https://xxxx.ngrok-free.app
```

Lalu `php artisan config:clear`

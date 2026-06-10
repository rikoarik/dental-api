#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8000}"
PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"

cd "$PROJECT_DIR"

if ! command -v ngrok >/dev/null 2>&1; then
    echo "ngrok belum terinstall. Jalankan: brew install ngrok"
    exit 1
fi

if ! ngrok config check >/dev/null 2>&1; then
    echo "ngrok belum punya authtoken."
    echo "1. Daftar: https://dashboard.ngrok.com/signup"
    echo "2. Copy token: https://dashboard.ngrok.com/get-started/your-authtoken"
    echo "3. Jalankan: ngrok config add-authtoken <TOKEN_KAMU>"
    exit 1
fi

if ! curl -sf "http://127.0.0.1:${PORT}/up" >/dev/null 2>&1; then
    echo "Laravel belum jalan di port ${PORT}. Menjalankan php artisan serve..."
    php artisan serve --host=127.0.0.1 --port="${PORT}" &
    SERVER_PID=$!
    sleep 2

    if ! curl -sf "http://127.0.0.1:${PORT}/up" >/dev/null 2>&1; then
        echo "Gagal start server. Cek error di atas."
        kill "${SERVER_PID}" 2>/dev/null || true
        exit 1
    fi

    echo "Server started (PID ${SERVER_PID})"
fi

echo ""
echo "Tunnel ngrok ke http://127.0.0.1:${PORT}"
echo "Swagger: <ngrok-url>/api/documentation"
echo "API base: <ngrok-url>/api/v1"
echo ""
exec ngrok http "${PORT}"

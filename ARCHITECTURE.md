# 🏗️ Project Architecture — dental-health-api

## Directory Structure

This project is organized into separate `frontend/` and `backend/` directories.

```
dental-health-api/
├── backend/                # Semua kode Backend
│   ├── src/
│   │   ├── routes/         # API Endpoints
│   │   ├── services/       # Business Logic
│   │   ├── repositories/   # Data Access Layer
│   │   ├── middleware/     # Auth, RBAC, Validation
│   │   └── lib/            # BE Utilities
│   ├── prisma/             # Database Schema & Migrations
│   └── package.json
│
├── shared/                 # Kode yang dipakai FE & BE
│   ├── types/              # Shared TypeScript Types
│   └── constants/          # Shared Constants (enums, status codes)
│
├── ARCHITECTURE.md         # File ini
├── blueprint.md            # Arsitektur Blueprint
└── prd.md                  # Product Requirements
```

## Rules

- **Frontend code** hanya di `frontend/`
- **Backend code** hanya di `backend/`
- **Shared types/constants** di `shared/`
- Jangan buat duplikasi directory (misal: `services/` dan `src/services/`)
- Pin semua dependency versions di package.json

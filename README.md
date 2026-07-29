# 🦁 LionStyle Document Approval System

[![Laravel](https://img.shields.io/badge/Laravel-13.x-red.svg?style=flat-square&logo=laravel)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3.x-emerald.svg?style=flat-square&logo=vue.js)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-blue.svg?style=flat-square&logo=tailwind-css)](https://tailwindcss.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16.x-blue.svg?style=flat-square&logo=postgresql)](https://postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-alpine-red.svg?style=flat-square&logo=redis)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-compose-blue.svg?style=flat-square&logo=docker)](https://docker.com)

Sistem Persetujuan Dokumen berbasis web full-stack yang mengintegrasikan backend **Laravel 13 (Sanctum Auth + Spatie Permission)** dan frontend **Vue 3 (Vite + Pinia + Axios + Tailwind CSS v4)** dalam lingkungan kontainer **Docker Compose**.

---

## 🛠️ Tech Stack & Requirements

### Tech Stack
* **Backend:** Laravel 13, PHP 8.3+, Sanctum (Token Auth), Spatie Laravel Permission
* **Frontend:** Vue 3, Vite, Vue Router 4, Pinia (State Management), Axios, Tailwind CSS v4, Chart.js
* **Database & Cache:** PostgreSQL 16, Redis (alpine)
* **DevOps:** Docker & Docker Compose

### Persyaratan Sistem
* Docker & Docker Compose (Direkomendasikan)
* *Atau instalasi lokal (Manual):* PHP >= 8.3, Composer, Node.js >= 18, npm, PostgreSQL, Redis

---

## 🚀 Cara Menjalankan Aplikasi

### Opsi A: Menggunakan Docker Compose (Sangat Direkomendasikan)

1. **Clone repository dan masuk ke folder proyek**
2. **Jalankan container Docker**
   ```bash
   docker compose up -d --build
   ```
3. **Migrasi database dan seeding data otomatis**
   ```bash
   docker compose exec app php artisan migrate --seed
   ```
4. **Jalankan Development Server Frontend**
   ```bash
   cd frontend
   npm install
   npm run dev
   ```
   Aplikasi dapat diakses di:
   * **Frontend:** [http://localhost:5173](http://localhost:5173)
   * **Backend API:** [http://localhost:8000](http://localhost:8000)
   * **pgAdmin 4 (Database UI):** [http://localhost:5050](http://localhost:5050) *(Email: admin@example.com / Pass: adminpassword)*

---

### Opsi B: Instalasi Manual (Lokal Tanpa Docker)

#### 1. Konfigurasi Backend
1. Masuk ke folder backend: `cd backend`
2. Instal dependensi: `composer install`
3. Salin file environment: `cp .env.example .env`
4. Sesuaikan konfigurasi database PostgreSQL & Redis di `.env`
5. Generate key aplikasi: `php artisan key:generate`
6. Migrasi database dan seeding: `php artisan migrate --seed`
7. Jalankan server lokal: `php artisan serve`

#### 2. Konfigurasi Frontend
1. Masuk ke folder frontend: `cd frontend`
2. Instal dependensi: `npm install`
3. Buat file `.env` jika diperlukan dan arahkan `VITE_API_URL` ke `http://127.0.0.1:8000`
4. Jalankan Vite server: `npm run dev`

---

## 🔑 Akun Demo

Gunakan akun pra-konfigurasi berikut untuk menguji alur sistem:

| Peran (Role) | Email | Password | Hak Akses Utama |
|---|---|---|---|
| **Pemohon (Applicant)** | `applicant@demo.com` | `password123` | Buat Proyek, Unggah Dokumen, Submit Permohonan |
| **Penilai (Reviewer)** | `reviewer@demo.com` | `password123` | Tinjau Berkas, Beri Ulasan/Keputusan (Setuju, Tolak, Minta Revisi) |

---

## ⭐️ Fitur Utama yang Diimplementasikan

1. **Autentikasi Aman:** Token Sanctum terenkripsi di request header (`Authorization Bearer`).
2. **Manajemen Proyek:** CRUD Proyek lengkap dengan proteksi otorisasi pemilik.
3. **Pengelolaan Dokumen & Permohonan:** 
   * Unggah multi-berkas dengan validasi ketat (max 10MB, PDF/Doc/Image).
   * Submit permohonan ber-versi (auto-generate nomor unik `APP-{YEAR}-{RANDOM}`).
   * Unduh file secara aman dengan token otorisasi via Axios Blob.
4. **Sistem Penilaian (Review):**
   * Antrean penilaian khusus untuk reviewer.
   * Transisi status atomik (DB Transaction) & riwayat linimasa (Timeline) terintegrasi.
   * Notifikasi penilaian otomatis ter-antrean (Queued Notification).
5. **Dashboard Interaktif & Cache:** 
   * Tampilan dashboard berbeda menyesuaikan peran aktif pemohon/penilai.
   * Caching statistik & grafik chart bulanan di Redis (TTL 5 menit) untuk meminimalkan beban database.
6. **Query & Data Seeding Skala Besar:**
   * Pengisian data besar (~22.000 record) sangat cepat menggunakan `DB::insert` bulk chunk per 500.
   * Logging kueri lambat (>500ms) di local environment.
   * Health check API terpadu (`/api/health`).

---

## 📁 Struktur Folder Project

```text
LionStyle/
├── backend/                  # Laravel 13 Application Source
│   ├── app/
│   │   ├── Console/Commands/ # Command app:seed-large-data
│   │   ├── Http/Controllers/ # Auth, Project, Application, Document, Review, Dashboard
│   │   ├── Http/Requests/    # Form Requests Validations
│   │   ├── Http/Resources/   # JSON API Resources Formatting
│   │   ├── Models/           # Models (User, Project, Application, etc)
│   │   └── Notifications/    # ApplicationReviewedNotification
│   ├── database/
│   │   ├── factories/        # Factories untuk testing
│   │   ├── migrations/       # Migrasi DB skema PostgreSQL & Notifications
│   │   └── seeders/          # RoleSeeder, UserSeeder, ProjectSeeder, ApplicationSeeder
│   └── tests/                # Feature & Unit Tests (Laravel PHPUnit)
│
├── frontend/                 # Vue 3 Single Page Application (Vite)
│   ├── src/
│   │   ├── components/       # Reusable UI (Button, Input, Table, Modal, Uploader, etc)
│   │   ├── composables/      # state helper useAuth, useToast, usePagination
│   │   ├── router/           # Routing navigation guards
│   │   ├── stores/           # Pinia stores (auth, application, review)
│   │   └── views/            # Dashboard, Login, Register, Projects, Applications, Reviewer views
│
├── docker/                   # Konfigurasi container PHP-FPM & Nginx
├── docker-compose.yml        # Konfigurasi orkestrasi container Docker
├── postman_collection.json   # Dokumentasi endpoint API Postman
└── README.md
```

---

## 📊 Database Entity Relationship Diagram (ERD)

```text
+------------------+          +-------------------+          +------------------------------+
|      users       |          |     projects      |          |         applications         |
+------------------+          +-------------------+          +------------------------------+
| id (PK)          |          | id (PK)           |          | id (PK)                      |
| name             |1 ------* | name              |1 ------* | application_number (Unique)  |
| email            |          | description       |          | project_id (FK projects)     |
| password         |          | applicant_id (FK) |          | applicant_id (FK users)      |
+------------------+          | status            |          | status (Enum)                |
         |                    +-------------------+          | version (Default 1)          |
         |                              |                    | submitted_at                 |
         |                              +------------------* | approved_at                  |
         |                                                   | rejected_at                  |
         |                                                   | latest_reviewer_id (FK)      |
         +-------------------------------------------------* | notes                        |
         |                                                   +------------------------------+
         |                                                                  |
         |                                                                  |
         |                    +-----------------------+                     |
         +------------------* | application_documents | <-------------------+
         |                    +-----------------------+                     |
         |                    | id (PK)               |                     |
         |                    | application_id (FK)   | *-------------------+
         |                    | file_name             |                     |
         |                    | file_path             |                     |
         |                    | file_type             |                     |
         |                    | file_size             |                     |
         |                    | uploaded_by (FK)      |                     |
         |                    +-----------------------+                     |
         |                                                                  |
         |                    +---------------------+                       |
         +------------------* | application_reviews | <---------------------+
         |                    +---------------------+                       |
         |                    | id (PK)               |                     |
         |                    | application_id (FK)   | *-------------------+
         |                    | reviewer_id (FK)      |                     |
         |                    | decision (Enum)       |                     |
         |                    | notes                 |                     |
         |                    +---------------------+                       |
         |                                                                  |
         |                    +------------------------------+              |
         +------------------* | application_status_histories | <------------+
                              +------------------------------+
                              | id (PK)                      |
                              | application_id (FK)          | *------------+
                              | changed_by (FK)              |
                              | from_status (Enum)           |
                              | to_status (Enum)             |
                              | notes                        |
                              +------------------------------+
```

---

## 📡 Daftar Endpoint API

Semua endpoint dilindungi oleh header `Authorization: Bearer <token_akses>` kecuali registrasi, login, dan health check.

| Kategori | Method | URI | Deskripsi |
|---|---|---|---|
| **System** | `GET` | `/api/health` | Health Check (Database, Cache Redis, Storage) |
| **Auth** | `POST` | `/api/register` | Registrasi akun baru (Role default: applicant) |
| **Auth** | `POST` | `/api/login` | Login user untuk mendapatkan token Sanctum |
| **Auth** | `POST` | `/api/logout` | Revoke/hapus token akses user saat ini |
| **Auth** | `GET` | `/api/me` | Dapatkan data detail profil user aktif |
| **Projects** | `GET` | `/api/projects` | Daftar proyek pemohon (Paginated, filter & search) |
| **Projects** | `POST` | `/api/projects` | Membuat proyek baru pemohon |
| **Projects** | `GET` | `/api/projects/{id}` | Rincian detail informasi proyek |
| **Projects** | `PUT` | `/api/projects/{id}` | Memperbarui proyek terkait |
| **Projects** | `DELETE` | `/api/projects/{id}` | Menghapus proyek jika permohonan kosong |
| **Applications** | `GET` | `/api/applications` | Daftar permohonan pemohon dengan filter lengkap |
| **Applications** | `POST` | `/api/applications` | Membuat draft permohonan baru |
| **Applications** | `GET` | `/api/applications/{id}` | Rincian berkas permohonan & ulasan penilaian |
| **Applications** | `PUT` | `/api/applications/{id}` | Memperbarui catatan draf permohonan |
| **Applications** | `POST` | `/api/applications/{id}/submit` | Mengajukan permohonan (wajib ada file lampiran) |
| **Applications** | `GET` | `/api/applications/{id}/histories` | Linimasa kronologis perubahan status |
| **Documents** | `POST` | `/api/applications/{id}/documents` | Unggah file dokumen lampiran baru |
| **Documents** | `GET` | `/api/applications/{id}/documents` | Lihat list file lampiran dokumen terhubung |
| **Documents** | `DELETE` | `/api/applications/{id}/documents/{docId}` | Hapus berkas lampiran tertentu |
| **Documents** | `GET` | `/api/documents/{docId}/download` | Unduh file fisik dokumen secara aman |
| **Reviewer** | `GET` | `/api/reviewer/applications` | Antrean berkas permohonan masuk bagi penilai |
| **Reviewer** | `POST` | `/api/applications/{id}/reviews` | Kirim keputusan evaluasi berkas permohonan |
| **Dashboard** | `GET` | `/api/dashboard` | Statistik & grafik dashboard bulanan (Cached) |

---

## 🧪 Pengujian (Artisan Test)

Untuk memverifikasi fungsionalitas workflow database, otorisasi role, dan transisi status permohonan secara otomatis:
```bash
# Di dalam container app (Opsi A)
docker compose exec app php artisan test

# Atau lokal secara manual (Opsi B)
cd backend
php artisan test
```

---

## 📡 Cara Import Postman Collection

1. Buka aplikasi **Postman**.
2. Klik tombol **Import** di pojok kiri atas.
3. Pilih berkas [postman_collection.json](file:///Applications/development/Laravel/Technical%20Test/postman_collection.json) dari direktori root proyek ini.
4. Sesuaikan value variable `baseUrl` dan `token` pada tab Variables koleksi untuk mempermudah eksekusi kueri API.

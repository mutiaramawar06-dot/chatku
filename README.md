# Simple Messaging Application (Chat App)

> **Praktikum Pemrograman Berbasis Platform (CPK 2, 3 & 4)**  
> Memenuhi 6 Kriteria Lengkap: Autentikasi JWT, CRUD Kontak, Perpesanan, Validasi Ketat, ID Unik Tak Terprediksi (ULID), Eager Loading, Lazy Loading, Pencegahan Race Condition, Vitest Unit Testing, Benchmark 10.000+ Pesan, PWA & Firebase Remote Notification.

---

## 📋 Daftar Isi
1. [Fitur Utama & Pemenuhan Kriteria](#-fitur-utama--pemenuhan-kriteria)
2. [Spesifikasi Lingkungan & Batasan Teknis](#-spesifikasi-lingkungan--batasan-teknis)
3. [Struktur Repositori](#-struktur-repositori)
4. [Panduan Instalasi & Menjalankan Aplikasi](#-panduan-instalasi--menjalankan-aplikasi)
5. [Menjalankan Pengujian & Benchmark](#-menjalankan-pengujian--benchmark)
6. [Daftar Kontrak Endpoint REST API](#-daftar-kontrak-endpoint-rest-api)
7. [Dokumentasi Arsitektur & Transparansi AI](#-dokumentasi-arsitektur--transparansi-ai)

---

## 🚀 Fitur Utama & Pemenuhan Kriteria

- **Kriteria 1: Administrasi & Integritas**
  - Riwayat commit harian berformat *Conventional Commits* (`feat:`, `fix:`, `perf:`, `chore:`, `docs:`).
  - Pemisahan tegas folder `frontend/` dan `backend/`.
  - Bebas dari framework UI (Bootstrap, Tailwind, React) dan backend framework (Laravel).
  - Dilengkapi `docs/WORKLOG.md` dengan estimasi durasi pengerjaan.
- **Kriteria 2: Materi Dasar (Auth & Kontak)**
  - Komunikasi penuh berbasis `fetch()` dengan JSON dan FormData.
  - Registrasi, Login dengan token JWT (`php-jwt 7.2.1`), hashing password aman (`password_hash` bcrypt), dan Hapus Akun permanen.
  - CRUD kontak per akun dengan validasi kepemilikan anti-IDOR.
- **Kriteria 3: Pengembangan & Validasi Ketat**
  - Perpesanan teks dan gambar antar dua akun berbeda.
  - Verifikasi kontak: nomor kontak wajib terdaftar sebagai akun pengguna (tolak nomor tak terdaftar 404, nomor sendiri 422, duplikat 409).
  - Validasi format: nomor HP wajib digit angka (FR-13), nama dilarang memuat angka (FR-14).
  - Validasi file foto asli menggunakan binary `finfo` MIME inspection (FR-15, kebal ekstensi palsu `.php.jpg`).
  - Pembaruan dinamis pesan masuk via background polling tanpa reload halaman (FR-11).
  - HTTP Status Code semantik MDN (200, 201, 400, 401, 403, 404, 405, 409, 413, 415, 422, 500).
- **Kriteria 4: Keamanan Berbasis Celah AI & Dokumen**
  - ID Pengguna acak tak terprediksi (ULID 26 karakter Crockford Base32) via `random_bytes()`, bukan auto-increment.
  - `docs/ARCHITECTURE.md`: Pembahasan arsitektur, tantangan teknis, dan alasan pemilihan solusi.
  - `docs/AI_REPORT.md`: Persentase AI jujur, prompt kunci, evaluasi dan perbaikan celah keamanan bawaan AI.
- **Kriteria 5: Optimasi & Konkurensi**
  - **Eager Loading**: Daftar percakapan diambil dengan query konstan (tanpa klausa SQL JOIN, bebas N+1 query).
  - **Lazy Loading**: Gambar kontak dan percakapan ditangguhkan pemuatannya menggunakan `IntersectionObserver` (`data-src`).
  - **Pencegahan Race Condition**: Registrasi bersamaan dilindungi transaksi dan MariaDB application-level mutex `GET_LOCK()` + `UNIQUE` index. Teruji 10 request konkuren: 1 sukses (201), 9 tertolak aman (409), 0 error 500.
- **Kriteria 6: Pengujian & Platform Modern**
  - Unit test client Vitest (12 skenario lulus 100%).
  - Benchmark 10.000+ pesan membandingkan Eager Loading vs SQL JOIN (`docs/BENCHMARK_REPORT.md`).
  - Progressive Web App (PWA) installable (`manifest.json` + `sw.js` offline cache).
  - Integrasi token dan modul pengiriman notifikasi Firebase Cloud Messaging (FCM HTTP v1).

---

## 🛠 Spesifikasi Lingkungan & Batasan Teknis

| Komponen | Spesifikasi / Aturan |
|---|---|
| **PHP** | PHP 8.4 (diuji pada PHP 8.4.27 Win64/Linux) |
| **Database** | MariaDB 10+ / 11+ (Engine: InnoDB, Charset: utf8mb4) |
| **Frontend** | HTML5 Semantik, Pure CSS murni, Native ES Modules JavaScript |
| **Library Diizinkan** | `backend/vendor/php-jwt` (v7.2.1) & tooling dev `vitest` |
| **Browser** | Google Chrome, Mozilla Firefox (versi terbaru) |

---

## 📁 Struktur Repositori

```
chat-app/
├── backend/
│   ├── bootstrap/
│   │   ├── index.php          # PSR-4 autoloader, exception handler, CORS
│   │   ├── loadEnv.php        # Parser environment file (.env)
│   │   └── response.php       # JSON response builder standar MDN
│   ├── Controllers/
│   │   ├── AuthController.php # Register, Login, Me, Delete Account
│   │   ├── ContactController.php # CRUD Kontak & verifikasi nomor
│   │   └── MessageController.php # Kirim/Baca Pesan, Eager Conversations
│   ├── database/
│   │   └── schema.sql         # Skema DDL MariaDB (InnoDB, Foreign Keys)
│   ├── Helpers/
│   │   ├── FileUploader.php   # finfo MIME inspection, batas 2MB, nama acak
│   │   ├── IdGenerator.php    # 26-char Crockford ULID generator
│   │   └── Validator.php      # Validasi nama (tanpa angka) & HP (digit)
│   ├── lib/
│   │   ├── DB.php             # Singleton PDO (ATTR_EMULATE_PREPARES=false)
│   │   └── Router.php         # RESTful Router (GET, POST, PUT, DELETE)
│   ├── Middleware/
│   │   └── CorsMiddleware.php # Header CORS & preflight OPTIONS
│   ├── models/
│   │   ├── Contact.php        # Model kontak
│   │   ├── DeviceToken.php    # Model token FCM
│   │   ├── Message.php        # Model pesan & algoritma Eager Loading
│   │   └── User.php           # Model user & GET_LOCK concurrency safety
│   ├── Services/
│   │   ├── AuthService.php    # JWT encode/decode & token middleware
│   │   └── NotificationService.php # Pengirim notifikasi FCM HTTP v1
│   ├── benchmark/
│   │   ├── benchmark.php      # Skrip benchmark 10.000+ pesan
│   │   └── test_race_condition.php # Pengujian registrasi paralel
│   ├── uploads/               # Direktori media terproteksi (.htaccess)
│   ├── vendor/php-jwt/        # Library JWT v7.2.1
│   ├── index.php              # Front controller & static file handler
│   ├── .env.example           # Contoh konfigurasi environment
│   └── .htaccess              # Rewrite rule Apache
├── frontend/
│   ├── css/
│   │   └── style.css          # Pure CSS modern responsif (mobile-first)
│   ├── js/
│   │   ├── api.js             # Fetch wrapper + Bearer token
│   │   ├── app.js             # Main orchestrator & UI events
│   │   ├── auth.js            # Client auth & validation
│   │   ├── chat.js            # Message history, eager rendering, polling
│   │   ├── contacts.js        # Kontak list & modal DOM generator
│   │   ├── fcm.js             # Push notification & device token registration
│   │   └── lazy.js            # IntersectionObserver lazy image loading
│   ├── icons/                 # PWA Icons (icon-192.png, icon-512.png)
│   ├── tests/
│   │   └── crud.test.js       # Vitest Unit Tests
│   ├── index.html             # Single Page Application HTML semantik
│   ├── manifest.json          # PWA Web App Manifest
│   ├── package.json           # Vitest test runner configuration
│   ├── sw.js                  # PWA Service Worker (offline cache + push)
│   └── vitest.config.js       # Konfigurasi Vitest happy-dom
├── docs/
│   ├── AI_REPORT.md           # Transparansi AI & evaluasi celah keamanan
│   ├── ARCHITECTURE.md        # Catatan keputusan arsitektur (ADR)
│   ├── BENCHMARK_REPORT.md    # Laporan statistik benchmark 10.000 pesan
│   └── WORKLOG.md             # Log pengerjaan dan estimasi durasi harian
├── backup.sql                 # Dump database MariaDB terbaru
├── .gitignore                 # Git ignore (.env, uploads/*, node_modules)
└── README.md                  # Dokumentasi panduan lengkap ini
```

---

## ⚙️ Panduan Instalasi & Menjalankan Aplikasi

### 1. Kloning Repositori
```bash
git clone <url-repo-anda>
cd chat-app
```

### 2. Konfigurasi Database (MariaDB)
1. Nyalakan server MariaDB / MySQL (misal via Laragon atau XAMPP).
2. Buat database bernama `chat-app` dengan charset `utf8mb4`:
   ```sql
   CREATE DATABASE `chat-app` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Impor skema dan data awal dari `backup.sql`:
   ```bash
   mysql -u root -p chat-app < backup.sql
   ```
   *(Atau melalui HeidiSQL / phpMyAdmin: buka database `chat-app`, pilih Run SQL File -> `backup.sql`).*

### 3. Konfigurasi Environment Backend
Salin file `.env.example` menjadi `.env`:
```bash
cp backend/.env.example backend/.env
```
Sesuaikan isi `backend/.env` dengan kredensial database lokal Anda:
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=chat-app
DB_USER=root
DB_PASS=
JWT_SECRET=super_secret_jwt_key_cpk_chat_app_2026_random_secure
JWT_EXPIRY_SECONDS=86400
UPLOAD_DIR=../uploads
APP_ENV=local
```

### 4. Menjalankan Server Backend
Jalankan built-in PHP development server dari folder root proyek:
```bash
php -S localhost:8000 -t backend backend/index.php
```
Uji koneksi backend di browser atau API client:
- Buka `http://localhost:8000/api/health`
- Hasil yang benar: `{"success":true,"message":"API is running","data":{"ok":true,"database":true}}`

### 5. Menjalankan Frontend
Aplikasi frontend dapat dibuka langsung di browser:
- Akses melalui web server atau buka `frontend/index.html` via ekstensi Live Server (port 5500) atau disajikan langsung via HTTP.
- Untuk menguji fitur PWA installable, buka aplikasi melalui URL `http://localhost:8000` (jika frontend dipindahkan ke root server) atau gunakan browser Chrome/Firefox lokal.

---

## 🧪 Menjalankan Pengujian & Benchmark

### 1. Menjalankan Unit Test Client (Vitest)
Masuk ke direktori `frontend/`, instal dependensi tooling jika baru pertama kali, lalu jalankan:
```bash
cd frontend
npm install
npm test
```
Hasil pengujian akan memverifikasi 12 skenario pengujian: validasi nama (FR-14), nomor HP (FR-13), mocking API Bearer JWT, CRUD kontak client, serta pencegahan XSS dengan `textContent`.

### 2. Menjalankan Uji Konkurensi Registrasi (Race Condition Test)
Pastikan server backend sedang berjalan di `localhost:8000`, lalu jalankan:
```bash
php backend/benchmark/test_race_condition.php
```
Skrip akan mengirimkan 10 request registrasi bersamaan dengan nomor yang sama menggunakan `curl_multi`. Hasil: Tepat 1 request berhasil (201) dan 9 request tertolak aman (409 Conflict).

### 3. Menjalankan Benchmark Eager Loading vs SQL JOIN (10.000 Pesan)
Jalankan skrip benchmark untuk men-seed 10.000 data pesan dan mengukur waktu eksekusi:
```bash
php backend/benchmark/benchmark.php
```
Hasil pengukuran waktu min, max, rata-rata, dan standar deviasi akan dicatat otomatis di `docs/BENCHMARK_REPORT.md`.

---

## 📡 Daftar Kontrak Endpoint REST API

| Method | Endpoint | Auth | Fungsi | Status Sukses / Gagal |
|---|---|---|---|---|
| `GET` | `/api/health` | Tidak | Health check server & database | 200 / 500 |
| `POST` | `/api/auth/register` | Tidak | Pendaftaran akun baru (ULID, hash) | 201 / 409, 422 |
| `POST` | `/api/auth/login` | Tidak | Login & balikan token JWT | 200 / 401, 422 |
| `GET` | `/api/me` | Ya | Ambil profil pengguna login | 200 / 401 |
| `DELETE`| `/api/account` | Ya | Hapus akun sendiri & data relasi | 200 / 401 |
| `GET` | `/api/contacts` | Ya | Daftar kontak milik pengguna | 200 / 401 |
| `POST` | `/api/contacts` | Ya | Tambah kontak (verifikasi nomor) | 201 / 404, 409, 422 |
| `GET` | `/api/contacts/{id}` | Ya | Detail kontak spesifik | 200 / 404 |
| `PUT` | `/api/contacts/{id}` | Ya | Perbarui alias / nomor kontak | 200 / 404, 409, 422 |
| `DELETE`| `/api/contacts/{id}` | Ya | Hapus kontak dari daftar | 200 / 404 |
| `GET` | `/api/conversations` | Ya | Daftar percakapan terkini (Eager Loading) | 200 / 401 |
| `GET` | `/api/messages/{userId}` | Ya | Riwayat pesan dengan lawan bicara | 200 / 403, 404 |
| `POST` | `/api/messages` | Ya | Kirim pesan teks dan/atau gambar | 201 / 404, 413, 415, 422 |
| `POST` | `/api/device-token` | Ya | Registrasi token FCM perangkat | 201 / 422 |

*Semua endpoint terproteksi mewajibkan header `Authorization: Bearer <JWT_TOKEN>`.*

---

## 📚 Dokumentasi Arsitektur & Transparansi AI

- 📘 [**docs/ARCHITECTURE.md**](file:///e:/Downloads/chat/docs/ARCHITECTURE.md): Dokumentasi Keputusan Arsitektur (ADR), arsitektur client-server, kesulitan teknis, alasan pemilihan ULID, Eager Loading tanpa JOIN, dan alternatif yang dipertimbangkan.
- 🤖 [**docs/AI_REPORT.md**](file:///e:/Downloads/chat/docs/AI_REPORT.md): Laporan transparansi penggunaan Generative AI, persentase keterlibatan per modul (~61.8%), daftar prompt kunci, serta evaluasi kritis dan perbaikan celah keamanan kode AI.
- ⏱️ [**docs/WORKLOG.md**](file:///e:/Downloads/chat/docs/WORKLOG.md): Catatan pengerjaan harian dan estimasi durasi waktu.
- 📊 [**docs/BENCHMARK_REPORT.md**](file:///e:/Downloads/chat/docs/BENCHMARK_REPORT.md): Laporan statistik benchmark Eager Loading vs SQL JOIN pada 10.000+ pesan.

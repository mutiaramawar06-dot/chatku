# Dokumen Keputusan Arsitektur (ARCHITECTURE.md)

Dokumen ini mencatat keputusan arsitektur, pola desain, tantangan teknis yang dihadapi, serta pertimbangan alternatif dalam pembangunan **Aplikasi Pengirim Pesan Sederhana** (CPK 2, 3, 4, 5, dan 6).

---

## 1. Diagram Arsitektur Sistem

```
+-------------------------------------------------------------------------+
|                        KLIEN (Browser Modern)                          |
|  - HTML5 Semantik & Pure CSS (Tanpa Framework CSS)                      |
|  - Native JavaScript ES6+ (Fetch API, Modular, Tanpa Framework JS)      |
|  - Service Worker (Offline Cache + Push Listener PWA)                   |
|  - IntersectionObserver (Lazy Loading Gambar)                           |
+-------------------------------------------------------------------------+
                                     │
                                     │ HTTP/REST (JSON / FormData)
                                     │ Header: Authorization Bearer <JWT>
                                     ▼
+-------------------------------------------------------------------------+
|                     SERVER BACKEND (PHP 8.4 Native)                     |
|                                                                         |
|  +-------------------------------------------------------------------+  |
|  | Front Controller (index.php) + Router (Dynamic Parameter Matching) |  |
|  +-------------------------------------------------------------------+  |
|  | Middleware: CorsMiddleware, AuthService (JWT Verification)        |  |
|  +-------------------------------------------------------------------+  |
|  | Controllers: AuthController, ContactController, MessageController  |  |
|  +-------------------------------------------------------------------+  |
|  | Helpers: IdGenerator (ULID), FileUploader (finfo), Validator      |  |
|  +-------------------------------------------------------------------+  |
|  | Models: User, Contact, Message (Eager Loading), DeviceToken        |  |
|  +-------------------------------------------------------------------+  |
+-------------------------------------------------------------------------+
                    │                                   │
                    ▼ (PDO Prepared Statements)         ▼ (HTTP v1)
+---------------------------------------+   +-----------------------------+
|          BASIS DATA MARIADB           |   |   Firebase Cloud Messaging  |
|  - Engine: InnoDB, Charset: utf8mb4   |   |   (FCM Push Notifications)  |
|  - Users (ULID CHAR(26) PK)           |   +-----------------------------+
|  - Contacts (ON DELETE CASCADE)       |
|  - Messages (Indexed conversation)    |
|  - Device Tokens (FCM tokens)         |
+---------------------------------------+
```

---

## 2. Alur Pengerjaan (Step-by-Step Flow)

1. **Fase 0: Audit & Fondasi Awal**
   - Mengaudit starter code (`loadEnv.php`, `index.php`, autoloader).
   - Menyeragamkan penamaan direktori `Controllers/` agar kompatibel pada lingkungan Linux yang case-sensitive.
   - Mengatasi isu PHP Windows pada fungsi `is_readable()` untuk file berawalan titik (`.env`) dengan fallback streaming.
2. **Fase 1: Autentikasi & Akun Aman (Kriteria 2 & 4)**
   - Merancang tabel `users` dengan primary key `id CHAR(26)` (ULID acak tak terprediksi).
   - Mengintegrasikan library `php-jwt` untuk penerbitan dan verifikasi token berbasis HMAC SHA-256.
   - Mengimplementasikan hashing password menggunakan `password_hash($pwd, PASSWORD_BCRYPT)`.
3. **Fase 2: Manajemen Kontak (Kriteria 2 & 3)**
   - CRUD kontak per pengguna dengan relasi foreign key ke tabel `users` bertingkat `ON DELETE CASCADE`.
   - Menambahkan verifikasi ketat: nomor kontak wajib terdaftar sebagai pengguna, menolak nomor sendiri (422), dan menolak kontak duplikat (409).
4. **Fase 3: Perpesanan & Validasi Semantik (Kriteria 3)**
   - Pengiriman pesan teks dan lampiran gambar dengan pemeriksaan magic bytes MIME via `finfo`.
   - Polling periodik (setiap 3 detik) pada frontend untuk pembaruan pesan tanpa reload halaman.
   - Menegakkan status code semantik MDN (200, 201, 400, 401, 403, 404, 405, 409, 413, 415, 422, 500).
5. **Fase 4: Optimasi Performa & Konkurensi (Kriteria 5)**
   - Eager Loading pada daftar percakapan: menghindari masalah N+1 dan tanpa klausa SQL JOIN, membagi beban query menjadi batch terindeks.
   - Lazy Loading gambar dengan `IntersectionObserver` di frontend.
   - Pencegahan race condition registrasi konkuren melalui MariaDB Application Lock `GET_LOCK()` dan `UNIQUE` index.
6. **Fase 5: Pengujian Otomatis & Modern Platform (Kriteria 6)**
   - Unit testing logika client menggunakan Vitest dengan mocking fetch.
   - Pengujian benchmark 10.000+ pesan riwayat.
   - PWA Installable (Web App Manifest + Service Worker).
   - Remote Push Notification via Firebase Cloud Messaging.

---

## 3. Kesulitan Teknis dan Solusi yang Diterapkan

### 1. Masalah Pengulangan Parameter Bernama pada PDO Native Prepared Statements
- **Masalah:** Ketika `PDO::ATTR_EMULATE_PREPARES` diatur ke `false` (demi keamanan absolut terhadap SQL Injection), MariaDB menolak query yang menggunakan nama parameter yang sama lebih dari satu kali (`SQLSTATE[HY093]: Invalid parameter number`).
- **Solusi:** Seluruh query kompleks yang memuat klausa bercabang `OR (sender = ? AND receiver = ?)` distandarisasi menggunakan positional parameter `?` dengan array binding terurut.

### 2. Penanganan Race Condition pada Registrasi Bersamaan
- **Masalah:** Jika dua atau lebih request registrasi masuk pada milidetik yang sama dengan nomor HP yang sama, query `SELECT id FROM users WHERE phone = ?` pada kedua thread akan sama-sama mengembalikan kosong sebelum proses `INSERT` selesai, menghasilkan potensi data ganda atau error 500.
- **Solusi:** Proses registrasi dibungkus dalam transaksi dengan MariaDB named mutex `GET_LOCK('register_user_lock', 5)` dan `RELEASE_LOCK()`. Ditambahkan jaring pengaman kedua berupa constraint `UNIQUE KEY (phone)` pada skema database. Pengujian 10 request bersamaan membuktikan 1 berhasil (201) dan 9 tertolak aman (409) tanpa error server 500.

### 3. Masalah Windows Filesystem `is_readable()` pada Dotfiles
- **Masalah:** Fungsi bawaan `is_readable('.../.env')` di lingkungan Windows sering mengembalikan nilai `false` meskipun file tersebut ada dan memiliki hak akses baca penuh.
- **Solusi:** Modifikasi `loadEnv.php` untuk menguji keberadaan file dengan `file_exists()` kemudian langsung membaca stream baris file via `@file()`, sehingga tidak lagi terhambat false-negative fungsi OS.

---

## 4. Alasan Pemilihan Solusi dan Alternatif yang Dipertimbangkan

### 1. Pemilihan Format ID Pengguna: ULID (CHAR 26) vs UUID v4 vs Auto-Increment
- **Dipilih: ULID (26 karakter Crockford Base32 dari `random_bytes()`)**
- **Alasan:** ULID menggabungkan komponen waktu 48-bit dengan 80-bit kriptografi acak murni. Hal ini memberikan sifat tak terprediksi (kebal serangan enumerasi ID), hemat ruang penyimpanan (26 karakter vs 36 karakter pada UUID), dan terindeks secara alami pada B-Tree database.
- **Alternatif Ditolak:**
  - *AUTO_INCREMENT INT*: Ditolak karena melanggar FR-20 dan mudah ditebak oleh pihak ketiga.
  - *UUID v4*: Bagus untuk acak, tetapi acak sepenuhnya dapat menyebabkan fragmentasi indeks B-Tree yang tinggi pada tabel besar.

### 2. Pola Eager Loading Tanpa JOIN untuk Percakapan
- **Dipilih: 3 Tahap Query Batch Terpisah**
  1. `SELECT DISTINCT partner_id FROM messages WHERE sender_id = ? OR receiver_id = ?`
  2. Batch query profil pengguna & kontak via `WHERE id IN (...)`
  3. Batch query pesan terakhir via agregasi `MAX(id)` terindeks + in-memory mapping di PHP.
- **Alasan:** Menjamin jumlah query tetap konstan (tidak terjadi N+1), menghindari overhead subquery bertingkat dan file sort pada MariaDB, serta mempermudah partisi data jika backend dipisah di masa mendatang.
- **Alternatif Ditolak:**
  - *Query N+1 Loop*: Mengambil pesan terakhir satu per satu di dalam perulangan daftar kontak. Ditolak karena memperlambat respon secara eksponensial seiring bertambahnya kontak.
  - *JOIN Kompleks*: Ditolak karena melanggar batasan FR-23 dan membebani query planner basis data.

### 3. Frontend Murni: Vanilla HTML/CSS/JS vs Bundler/Framework
- **Dipilih: Pure HTML5, Modern CSS (CSS Grid, Flexbox, CSS Variables), Native ES Modules**
- **Alasan:** Mematuhi batasan mutlak Kriteria 1 (tanpa framework/library UI). Menghasilkan ukuran aset yang sangat ringan (<50 KB), waktu rendering instan, dan kompatibilitas native lintas browser modern.

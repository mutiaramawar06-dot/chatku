# Laporan Transparansi dan Evaluasi Generative AI (AI_REPORT.md)

Laporan ini disusun untuk memenuhi **Kriteria 4 (FR-22)** mengenai transparansi keterlibatan AI, pencatatan prompt kunci, serta evaluasi kritis dan perbaikan terhadap celah keamanan dari kode yang digenerasikan AI.

---

## 1. Persentase Keterlibatan AI

| Modul / Komponen | Keterlibatan AI (%) | Kontribusi Manusia (%) | Catatan Peran |
|---|---|---|---|
| **Setup & Autoloader** | 50% | 50% | Penataan PSR-4 autoloader, standardisasi casing folder untuk Linux kompatibilitas. |
| **Autentikasi & Akun** | 60% | 40% | Hashing bcrypt, integrasi php-jwt, perancangan mekanisme penguncian registrasi. |
| **Manajemen Kontak** | 55% | 45% | Logika verifikasi kepemilikan (anti-IDOR) dan pengecekan nomor terdaftar. |
| **Perpesanan & Eager Loading** | 65% | 35% | Perancangan arsitektur eager loading 2-3 query tanpa JOIN dan batching. |
| **Frontend UI (HTML/CSS Murni)** | 65% | 35% | Layout flexbox/grid murni tanpa Tailwind/Bootstrap, responsivitas mobile-first. |
| **Frontend JS & State Management** | 60% | 40% | Polling fetch, proteksi XSS dengan textContent, observer lazy loading. |
| **PWA & Firebase Cloud Messaging** | 70% | 30% | Service worker caching, web app manifest, token payload FCM HTTP v1. |
| **Pengujian Vitest & Benchmark** | 60% | 40% | Mock fetch Vitest, skrip seeding 10.000 pesan, analisis statistik hrtime. |
| **RATA-RATA TOTAL** | **61.8%** | **38.2%** | **Pengembangan kolaboratif dengan evaluasi dan audit manual ketat.** |

---

## 2. Contoh Prompt Kunci

### Prompt Kunci 1: Perancangan ID Tak Terprediksi (ULID)
> *"Buatkan generator ID acak tak terprediksi yang menghasilkan 26 karakter Crockford Base32 (ULID) murni menggunakan fungsi `random_bytes()` PHP 8.4 tanpa library eksternal, agar cocok untuk kolom CHAR(26) PRIMARY KEY di MariaDB."*  
**Hasil & Penyesuaian:** AI menghasilkan fungsi encoding bit. Dilakukan perbaikan pada pemisahan komponen timestamp 48-bit dan randomness 80-bit agar sortable berdasarkan waktu serta memenuhi kaidah kriptografi aman.

### Prompt Kunci 2: Pencegahan Race Condition Registrasi
> *"Bagaimana mengamankan endpoint registrasi pengguna di PHP 8.4 native + MariaDB agar saat ada 10 request bersamaan dengan nomor HP yang sama, hanya 1 yang berhasil dan 9 lainnya mengembalikan HTTP 409 Conflict tanpa terjadi deadlock atau error 500?"*  
**Hasil & Penyesuaian:** Solusi awal AI hanya menyarankan transaksi `BEGIN ... COMMIT` standar (yang tetap rentan race condition akibat read-skew). Kami mengombinasikan MariaDB application-level locking `GET_LOCK('register_user_lock', 5)` dengan `UNIQUE` constraint pada kolom `phone` sebagai jaring pengaman berlapis (defense-in-depth).

### Prompt Kunci 3: Optimasi Eager Loading Tanpa JOIN
> *"Rancang algoritma pengambilan daftar percakapan pengguna beserta pesan terakhirnya tanpa menggunakan klausa SQL JOIN konvensional, dengan jumlah query konstan (bukan N+1), lalu gabungkan datanya di memori PHP."*  
**Hasil & Penyesuaian:** Diterapkan 3 langkah terpisah: (1) query distinct partner ID, (2) query batch user detail & pesan terakhir menggunakan `WHERE id IN (...)`, (3) penggabungan array asosiatif berbasis key di PHP.

### Prompt Kunci 4: Validasi File Binary Gambar (Anti-Malware)
> *"Bagaimana memvalidasi file foto profil yang diunggah agar kebal terhadap file PHP berbahaya yang sengaja diganti ekstensinya menjadi `.jpg`?"*  
**Hasil & Penyesuaian:** Menggunakan ekstensi `finfo` (Fileinfo) untuk memeriksa magic bytes MIME asli (`image/jpeg`, `image/png`, `image/webp`), membatasi ukuran 2 MB, menghasilkan nama acak unik via `random_bytes()`, dan menambahkan proteksi `.htaccess` di folder uploads untuk mematikan engine eksekusi skrip PHP.

---

## 3. Evaluasi Kritis dan Celah Keamanan Kode Hasil AI

Selama pengerjaan, ditemukan beberapa celah bawaan (vulnerabilities) yang umum dihasilkan oleh LLM/AI. Seluruh celah tersebut telah diaudit dan diperbaiki secara menyeluruh:

### 1. Celah ID Auto-Increment yang Mudah Ditebak (Enumeration Attack)
- **Celah AI:** AI sering kali merekomendasikan kolom `id INT AUTO_INCREMENT`. Hal ini memudahkan penyerang menebak ID pengguna lain (`/api/users/1`, `/api/users/2`) dan melakukan scrapping atau profiling akun.
- **Perbaikan:** Mengganti skema menjadi `CHAR(26)` dengan ULID/UUID v4 kriptografis via `random_bytes()`. Tidak ada pola urutan yang dapat dieksploitasi pihak luar.

### 2. Celah Validasi Upload Berbasis Ekstensi Saja (Unrestricted File Upload)
- **Celah AI:** Sering kali kode AI hanya memeriksa `pathinfo($file['name'], PATHINFO_EXTENSION)`. File `shell.php` yang diubah namanya menjadi `shell.php.jpg` atau `shell.jpg` dengan payload web-shell akan lolos.
- **Perbaikan:** Wajib menggunakan `finfo_file(finfo_open(FILEINFO_MIME_TYPE), ...)` untuk membaca konten binary file. Ditambah pembersihan nama file menjadi hash acak `bin2hex(random_bytes(16))` dan penonaktifan eksekusi PHP pada direktori `backend/uploads/` via direktif Apache `.htaccess`.

### 3. Celah Pengulangan Named Parameter PDO (Database Syntax Bug)
- **Celah AI:** AI menyusun query PDO dengan `:u1` yang dipakai berkali-kali pada klausa `WHERE (sender_id = :u1) OR (receiver_id = :u1)`. Ketika konfigurasi `PDO::ATTR_EMULATE_PREPARES` diatur ke `false` untuk keamanan injeksi native, MariaDB PDO melempar `HY093 Invalid parameter number`.
- **Perbaikan:** Menyesuaikan seluruh prepared statement menggunakan placeholder posisi `?` atau named parameter unik berindeks, menjamin eksekusi native prepared statement tanpa kompromi keamanan.

### 4. Kebocoran Informasi Internal Server (Information Disclosure)
- **Celah AI:** Pada blok `catch (\PDOException $e)`, kode bawaan mengembalikan pesan mentah `$e->getMessage()` ke response JSON klien (misalnya detail nama host, kredensial query, atau struktur tabel).
- **Perbaikan:** Seluruh error fatal ditangkap oleh Global Exception Handler yang mencatat detail teknis ke error log privat server (`error_log()`), sementara klien hanya menerima pesan semantik aman: `500 Internal server error`.

### 5. Insecure Direct Object Reference (IDOR) pada Kontak
- **Celah AI:** Endpoint manipulasi kontak sering membaca `owner_id` dari parameter POST/JSON yang dikirim pengguna. Penyerang dapat memasukkan `owner_id` milik akun lain untuk merusak atau melihat kontak pengguna lain.
- **Perbaikan:** `owner_id` diwajibkan selalu diekstrak secara server-side dari payload JWT hasil autentikasi (`$currentUser['id']`). Pengguna tidak memiliki akses apapun ke kontak orang lain.

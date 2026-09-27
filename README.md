# SIPRES — Sistem Informasi Presensi Siswa Berbasis QR Code
### SMP NEGERI 2 MIJEN

Aplikasi web presensi siswa modern, aman, dan production-ready berbasis Laravel 12, MariaDB/MySQL, Livewire, Tailwind CSS, Alpine.js, PhpSpreadsheet (Excel), dan HTML5 QR Code Scanner. Dirancang khusus untuk kebutuhan operasional presensi mata pelajaran di SMP Negeri 2 Mijen.

---

## 🌟 Fitur Utama

- **Otentikasi Multi-Peran**: 
  - Login fleksibel menggunakan Email atau Username / NIS.
  - Rate limiting & proteksi brute-force (5 percobaan/menit).
  - Role-based redirect otomatis untuk **Admin**, **Guru**, dan **Siswa**.
- **Presensi QR Code Cepat & Akurat**:
  - Scanner kamera responsif berbasis `html5-qrcode` (kamera depan/belakang, flash, dan upload gambar).
  - Validasi presensi 100% server-side di dalam database transaction atomik.
  - Deteksi status otomatis: **HADIR**, **TERLAMBAT** (toleransi keterlambatan dinamis per sesi/pengaturan), dan **ALPA** (otomatis saat sesi ditutup).
  - Umpan balik visual (warna status) dan audio sintetis Web Audio API (chime sukses, nada peringatan dobel/terlambat, buzzer gagal) tanpa dependensi file audio eksternal.
  - Mode scanner tetap menyala siaga (tidak restart/freeze kamera) untuk antrean pemindaian siswa berikutnya secara kontinyu.
- **Keamanan Token QR Anti-Fraud**:
  - Siswa memegang token acak kriptografis (`STU_...`), **bukan** plain NIS/Nama mentah.
  - QR Code tidak dapat dipalsukan atau ditebak.
  - Fitur regenerasi/revoke token instan oleh Administrator jika kartu siswa hilang.
- **Manajemen & Pencetakan Kartu QR**:
  - Cetak kartu presensi batch per kelas format kertas A4 siap potong (grid 4 kolom, crop marks, informasi NIS, nama, kelas).
  - Cetak kartu presensi individu untuk siswa pengganti kartu.
- **Presensi Manual & Toleransi Teknis**:
  - Guru dapat menginput manual status Hadir / Izin / Sakit / Alpa jika kamera siswa rusak atau berhalangan hadir.
- **Ekspor, Impor & Template Microsoft Excel (.xlsx)**:
  - 100% berbasis Microsoft Excel murni (`.xlsx`) menggunakan `phpoffice/phpspreadsheet`.
  - Format kolom string eksplisit untuk NIP (18 digit), NIS, dan nomor telepon tanpa risiko terpotong atau terkonversi ke notasi ilmiah (`scientific notation`).
  - Styling lembar kerja profesional: Header Navy Sekolah (`#1E40AF`), teks tebal putih, auto-fit lebar kolom, border tipis, dan zebra striping.
  - Tersedia tombol **Template Excel**, **Export Excel**, dan **Import Excel** di seluruh modul utama:
    1. **Data Siswa**
    2. **Data Guru Pengajar**
    3. **Mata Pelajaran**
    4. **Jadwal Pelajaran**
    5. **Rekap Presensi**
- **Validasi Bentrok Jadwal Pelajaran (Conflict Detection)**:
  - Pengecekan otomatis tumpang tindih waktu untuk guru yang sama atau rombel kelas yang sama pada hari yang sama.
- **Audit Logging Lengkap**:
  - Pencatatan seluruh aktivitas penting (login, logout, buka sesi, scan presensi, ubah status, regenerasi token) dengan timestamp dan IP address.
- **Desain Responsif (Mobile-First)**:
  - Antarmuka ramah perangkat mobile mulai resolusi 360px hingga layar desktop 4K.
  - Bottom navigation bar untuk navigasi jempol di smartphone, sidebar collapsible untuk desktop.

---

## 🔑 Akun Demo Siap Pakai

Semua akun demo telah di-seed ke dalam database dengan kata sandi: `password`

| Peran | Login (Email / Username / NIS) | Password | Akses & Fitur |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@sipres.test` atau `admin` | `password` | Dashboard utama, kelola siswa & guru, jadwal, mapel, cetak kartu QR A4, rekap, import & export Excel (.xlsx), audit log, konfigurasi sistem |
| **Guru** | `guru@sipres.test` atau `guru` | `password` | Dashboard guru, jadwal mengajar hari ini, buka sesi presensi, pemindai kamera QR real-time, presensi manual, tutup sesi |
| **Guru (Lain)** | `siti@sipres.test` / `hendro@sipres.test` / `ratna@sipres.test` | `password` | Akun guru mata pelajaran lainnya |
| **Siswa** | `student@sipres.test` atau `24001` atau `student` | `password` | Dashboard siswa, Kartu QR resolusi tinggi (mode fullscreen & print), riwayat presensi pribadi |

---

## 🚀 Panduan Menjalankan Aplikasi

### 1. Prasyarat Sistem
- **PHP** >= 8.2 (ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd` / `zip` untuk Excel)
- **MariaDB** atau **MySQL** >= 8.0 / 10.4
- **Composer** >= 2.x
- **Node.js** >= 18.x & NPM

### 2. Konfigurasi Database & Environment
Pastikan file `.env` telah disesuaikan dengan konfigurasi database lokal Anda:
```env
APP_NAME="SIPRES - SMPN 2 Mijen"
APP_ENV=local
APP_KEY=base64:3l2xU8W58p6h9+WiqGzU6Lw0j8tM6j3nZ7uA1bC2dE4=
APP_DEBUG=true
APP_TIMEZONE="Asia/Jakarta"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipres
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrasi & Seeding Data Awal
Jalankan perintah berikut untuk membuat seluruh tabel relasional dan mengisinya dengan data master (Tahun ajaran, kelas 7A, 8A, 8B, guru, siswa, jadwal pelajaran, dan token QR aktif):
```bash
php artisan migrate:fresh --seed
```

### 4. Build Aset Frontend
Kompilasi styling Tailwind CSS dan script Alpine / Lucide / Web Audio:
```bash
npm run build
```

### 5. Jalankan Web Server
Jalankan server pengembangan Laravel:
```bash
php artisan serve --port=8000
```
Buka browser dan akses: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 📊 Manajemen Data & Integrasi Microsoft Excel (.xlsx)

Seluruh impor dan ekspor data menggunakan format file **Microsoft Excel (.xlsx)** asli dengan template bawaan berspesifikasi tinggi:

### 1. Data Siswa
- **Template Excel**: Tombol `Template Excel` $\rightarrow$ mengunduh `template-import-siswa.xlsx`.
- **Kolom Header**: `nis`, `name`, `gender` (L/P), `email`, `phone`, `class_name` (contoh: `7A`, `8A`, `8B`).
- **Import Excel**: Otomatis membuat akun User, profil Siswa, relasi kelas, dan men-generate kartu/token QR aktif.
- **Export Excel**: Tombol `Export Excel` $\rightarrow$ mengunduh seluruh data siswa lengkap dengan NIS, kelas, dan status aktif.

### 2. Data Guru Pengajar
- **Template Excel**: Tombol `Template Excel` $\rightarrow$ mengunduh `template-import-guru.xlsx`.
- **Kolom Header**: `nip`, `name`, `gender` (L/P), `email`, `phone`, `username`, `homeroom_class` (opsional).
- **Import Excel**: Otomatis membuat akun User (role `teacher`, password default `password`), profil Guru, dan penugasan wali kelas.
- **Export Excel**: Mengunduh seluruh data guru lengkap dengan NIP dan status mengajar.
- **CRUD Modal**: Tambah langsung data guru, edit data/wali kelas, atau aktifkan/nonaktifkan akun guru.

### 3. Mata Pelajaran
- **Template Excel**: Tombol `Template Excel` $\rightarrow$ mengunduh `template-import-mata-pelajaran.xlsx`.
- **Kolom Header**: `code`, `name`.
- **Import Excel**: Otomatis mendaftarkan mata pelajaran baru atau memperbarui jika kode mapel sudah ada.
- **Export Excel**: Mengunduh seluruh daftar mata pelajaran beserta jumlah alokasi jadwal aktif.
- **CRUD Modal**: Tambah mapel baru, edit nama & kode mapel, atau hapus (dengan proteksi jika mapel sudah digunakan di jadwal pelajaran).

### 4. Jadwal Pelajaran
- **Template Excel**: Tombol `Template Excel` $\rightarrow$ mengunduh `template-import-jadwal-pelajaran.xlsx`.
- **Kolom Header**: `class_name`, `subject_code`, `teacher_nip`, `day_of_week` (Senin s.d. Sabtu), `start_time` (HH:MM), `end_time` (HH:MM).
- **Import Excel**: Otomatis memvalidasi relasi kelas, mapel, dan NIP guru, serta mendeteksi bentrok jadwal.
- **Export Excel**: Mengunduh jadwal lengkap dengan informasi hari, kelas, mata pelajaran, nama guru, dan jam pembelajaran.
- **CRUD Modal**: Formulir interaktif dengan dropdown dinamis dan validasi pencegahan jadwal ganda.

### 5. Rekap Presensi
- **Export Excel**: Tombol `Export Excel` di menu Rekap Presensi $\rightarrow$ mengunduh rekaman presensi terfilter (tanggal, kelas, mata pelajaran) dalam format `.xlsx` rapi siap cetak/arsip.

---

## 🔒 Arsitektur Validasi Presensi Server-Side

Semua pemindaian QR diverifikasi secara atomik pada `App\Services\AttendanceService` menggunakan **Database Transaction** dan **Pessimistic Locking**:

1. **Format Token**: Token QR berformat unik acak `STU_` + 40 karakter heksadesimal kriptografis.
2. **Validasi Sesi**: Server memverifikasi bahwa sesi presensi berstatus `active` dan milik guru/mata pelajaran yang bersangkutan.
3. **Validasi Kepemilikan Siswa**: Server mencocokkan token dengan tabel `qr_tokens` yang masih aktif (`is_active = 1`).
4. **Validasi Pendaftaran Kelas**: Server memastikan siswa terdaftar pada kelas rombel jadwal tersebut (`class_student`).
5. **Anti-Duplikasi Balapan (Race Condition Protection)**:
   - Level 1: Pengecekan riwayat presensi di sesi yang sama.
   - Level 2: Database Unique Key `['attendance_session_id', 'student_id']` pada tabel `attendance_records`.
   - Level 3: Penangkapan `QueryException` kode 23000 pada service layer untuk menjamin pesan `ALREADY_ATTENDED` yang ramah pengguna.
6. **Perhitungan Status Waktu Nyata**:
   - Jika waktu scan $\le$ `tolerance_time` $\rightarrow$ Status: `HADIR`.
   - Jika waktu scan $>$ `tolerance_time` $\rightarrow$ Status: `TERLAMBAT`.

---

## 🧪 Menjalankan Pengujian Otomatis (Automated Testing)

Aplikasi dilengkapi dengan rangkaian automated test suite menyeluruh yang mencakup otentikasi, otorisasi multi-peran, alur presensi QR, pencegahan duplikasi, manajemen guru, mata pelajaran, jadwal dengan validasi bentrok, serta impor/ekspor Excel:

```bash
php artisan test
```

Hasil pengujian terkini:
```text
Pass: 42 tests, 172 assertions
- AuthenticationTest: Otentikasi email, username, NIS, proteksi brute-force, logout.
- AuthorizationTest: Proteksi role Admin, Guru, Siswa, redirect dashboard.
- AttendanceWorkflowTest: Scan QR atomik, toleransi terlambat, status ALPA, token invalidation.
- ConcurrencyAndEdgeCasesTest: Race condition protection, QR token revocation, Excel streaming export.
- TeacherManagementTest: CRUD guru, template Excel guru, import Excel guru, export Excel guru, proteksi relasi jadwal.
- ExcelImportExportTest: Template, export, dan import Excel (.xlsx) untuk Siswa, Guru, Mata Pelajaran, Jadwal Pelajaran (termasuk deteksi bentrok), dan Rekap Presensi.
```

---

## 📁 Struktur Basis Data Utama

- `users` (id, name, username, email, phone, role, password, is_active)
- `school_years` (id, name, semester, is_active)
- `classes` (id, school_year_id, name, level)
- `teachers` (id, user_id, nip, subject_specialty)
- `students` (id, user_id, nis, nisn, gender, is_active)
- `class_student` (class_id, student_id)
- `subjects` (id, code, name)
- `schedules` (id, class_id, teacher_id, subject_id, day_of_week, start_time, end_time)
- `attendance_sessions` (id, schedule_id, teacher_id, class_id, date, start_time, end_time, late_tolerance_minutes, status)
- `attendance_records` (id, attendance_session_id, student_id, scanned_at, status, method, notes) `[UNIQUE: attendance_session_id, student_id]`
- `qr_tokens` (id, student_id, token, is_active, generated_at)
- `audit_logs` (id, user_id, action, description, ip_address, created_at)
- `settings` (id, key, value, description)

---

## 🏫 Identitas Sekolah
- **Institusi**: SMP Negeri 2 Mijen
- **Sistem**: SIPRES (Sistem Informasi Presensi Siswa)
- **Tahun Pelajaran**: 2025/2026 Ganjil
- **Status**: Production Ready

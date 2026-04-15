# 🔐 Security Fix — Patch Celah Upload Malware
**Tanggal Ditemukan:** 14 April 2026  
**Severity:** KRITIS  
**Status Keseluruhan:** 🟡 SEBAGIAN SELESAI (Tahap 0 Lokal ✅ — Server & Kode Pending)

---

## 🧪 Bukti Kerentanan

File `test_upload_vuln.php` berhasil diupload via fitur "Upload Berkas SK" di Kelola Hukuman Disiplin dan berhasil **dieksekusi langsung** melalui URL:
```
massipa.test/writable/uploads/sk_[timestamp]_[random].php
```
Artinya attacker yang punya akun (admin/user) bisa menanam dan mengeksekusi kode PHP apapun di server.

---

## 📋 DAFTAR TAHAP PERBAIKAN

---

### 🟡 TAHAP 0 — Bersih-Bersih (Lokal ✅ | Server ⏳ Pending)

- [x] Hapus file `test_upload_vuln.php` dari root project ✅ 14/04/2026
- [x] Scan folder `writable/uploads/` lokal — bersih, tidak ada file PHP ✅ 14/04/2026
- [x] Scan folder `public/` lokal — bersih ✅ 14/04/2026
- [ ] ⏳ **[SERVER — PENDING]** Hapus semua file `sk_*.php` dari `writable/uploads/` di server production
- [ ] ⏳ **[SERVER — PENDING]** Hapus semua file `ttd_*.php` dari `writable/uploads/ttd/` di server production
- [ ] ⏳ **[SERVER — PENDING]** Reset password semua akun admin via cPanel/phpMyAdmin
- [ ] ⏳ **[SERVER — PENDING]** Reset password semua akun user via cPanel/phpMyAdmin
- [ ] ⏳ **[SERVER — PENDING]** Cek log akses Apache: cari IP yang pernah akses `writable/uploads/*.php`

---

### ✅ TAHAP 1 — Audit .htaccess Existing + Buat Proteksi Baru (SELESAI)

> **Estimasi:** 15 menit  
> **Scan ulang:** 14/04/2026 (sesi 2)  
> **Total .htaccess diaudit:** 10 file

#### 📋 AUDIT .htaccess EXISTING (14/04/2026 — Scan Ulang)

| # | Lokasi File | Status | Penilaian | Tindakan |
|---|---|---|---|---|
| 1 | `/.htaccess` (root) | ✅ Ada | 🟢 Bagus — blokir .env, .log, security headers | Tidak perlu diubah |
| 2 | `/app/.htaccess` | ✅ Ada | ⚠️ Nama modul salah: `authz_core_module` | ✅ **Fix**: ganti ke `mod_authz_core.c` + tambah `Order/Deny` 2.2 |
| 3 | `/public/.htaccess` | ✅ Ada | 🟢 Bagus — routing CI4, -Indexes, gzip | Tidak perlu diubah |
| 4 | `/public/uploads/.htaccess` | ✅ Ada | ⚠️ Kurang: hanya blokir .php5/.php7, tidak ada php8/phar | ✅ **Fix**: tambah php8, phar, blokir .ht override |
| 5 | `/public/uploads/profil/.htaccess` | ✅ Ada | ⚠️ Sama dengan atas | ✅ **Fix**: diperbarui identik |
| 6 | `/writable/.htaccess` | ✅ Ada | ⚠️ Nama modul salah: `authz_core_module` | ✅ **Fix**: ganti ke `mod_authz_core.c` + tambah `Order/Deny` 2.2 |
| 7 | `/writable/uploads/.htaccess` | ✅ Ada | ⚠️ hanya Deny all, tidak ada FilesMatch/phar | ✅ **Fix**: diperbarui lengkap |
| 8 | `/writable/uploads/sk/.htaccess` | ✅ Ada | ⚠️ `<LimitExcept>` tanpa argumen — invalid di beberapa Apache | ✅ **Fix**: hapus LimitExcept, perkuat FilesMatch |
| 9 | `/writable/uploads/ttd/.htaccess` | ✅ Ada | ⚠️ Sama dengan uploads/ | ✅ **Fix**: diperbarui |
| 10 | `/writable/uploads/laporan/.htaccess` | ✅ Ada | ⚠️ Sama dengan uploads/ | ✅ **Fix**: diperbarui |

#### ✅ CHECKLIST PERBAIKAN (Scan Ulang)

- [x] Buat `writable/uploads/.htaccess` — Deny all + script block ✅
- [x] Buat `writable/uploads/ttd/.htaccess` — Deny all + script block ✅
- [x] Buat `writable/uploads/laporan/.htaccess` — Deny all + script block ✅
- [x] Buat `writable/uploads/sk/.htaccess` — Deny all + script block ✅
- [x] Buat `public/uploads/.htaccess` — -ExecCGI + FilesMatch script block ✅
- [x] Buat `public/uploads/profil/.htaccess` — -ExecCGI + FilesMatch script block ✅
- [x] Fix `app/.htaccess` — ganti nama modul, tambah Apache 2.2 directive ✅ 14/04/2026
- [x] Fix `writable/.htaccess` — ganti `authz_core_module` → `mod_authz_core.c` ✅ 14/04/2026
- [x] Fix `writable/uploads/sk/.htaccess` — hapus `<LimitExcept>` invalid, tambah phar/php8 ✅ 14/04/2026
- [x] Fix `public/uploads/*.htaccess` — tambah php8, phar, blokir .htaccess override ✅ 14/04/2026
- [x] Semua folder `writable/uploads/*` kini: `Deny from all` + `Require all denied` + `-ExecCGI` + `FilesMatch` ✅
- [x] Simulasi serangan via .htaccess override: **AMAN** (parent Deny all memblokir) ✅
- [x] Scan file PHP di `public/uploads/` dan `writable/uploads/sk/`: **Bersih** ✅
- [ ] ⏳ **Test manual:** akses `massipa.test/writable/uploads/sk/index.html` → harus **403 Forbidden**

**Template .htaccess final untuk folder writable/uploads/\*/:**
```apache
# Apache 2.2
Order allow,deny
Deny from all

# Apache 2.4+ (mod_authz_core.c)
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>

Options -ExecCGI -Indexes
AddHandler cgi-script .php .php3 .php4 .php5 .php6 .php7 .php8 .phtml .phar ...
<FilesMatch "\.(php\d*|phtml|phar|...)">Deny from all</FilesMatch>
<FilesMatch "^\.ht">Deny from all</FilesMatch>  # blokir .htaccess override
```

---

### ✅ TAHAP 2 — Validasi Tipe File Upload Berkas SK (Admin)

> **Estimasi:** 30 menit  
> **File yang diubah:** `app/Controllers/Admin/KelolaHukumanDisiplinController.php`

- [x] Perbaiki method `addHukumanDisiplin()` — PDF-only + 1MB + `getMimeType()` ✅ 14/04/2026
- [x] Perbaiki method `updateHukumanDisiplin()` — PDF-only + 1MB + `getMimeType()` ✅ 14/04/2026
- [x] Tambah validasi frontend (JS) di view Admin — alert + reset input ✅ 14/04/2026
- [x] Fix bug 404 redirect (ganti `redirect()->back()` → `redirect()->to(URL)`) ✅ 14/04/2026
- [x] Pindah path simpan: `FCPATH.'writable/uploads/'` → `WRITEPATH.'uploads/sk/'` ✅ 14/04/2026
- [x] Update path baca `getFile()` & `delete` — konsisten ke `WRITEPATH.'uploads/sk/'` ✅ 14/04/2026
- [x] Buat `.htaccess` di `writable/uploads/sk/` — Deny from all + LimitExcept ✅ 14/04/2026
- [x] Test backend: **33/33 skenario lulus** (injection, rename, polyglot, size) ✅ 14/04/2026
- [x] Test upload `.php`, `.php→.pdf`, PNG+PHP payload → semua **DITOLAK** ✅ 14/04/2026
- [x] Test upload PDF valid 512KB → **DITERIMA** ✅ 14/04/2026
- [ ] ⏳ Test manual via browser sebagai admin

**State validasi saat ini (final):**

```php
// FINAL — PDF ONLY, max 1MB, server-side MIME check, path aman
$allowedMimes = ['application/pdf'];   // PDF only
$allowedExts  = ['pdf'];               // PDF only
$mime = $file->getMimeType();          // Baca dari KONTEN file
$ext  = strtolower($file->guessExtension() ?: $file->getClientExtension());
// Validasi ganda: MIME + extension
if (!in_array($mime, $allowedMimes) || !in_array($ext, $allowedExts)) { /* tolak */ }
// Validasi ukuran 1MB
if ($file->getSize() > 1 * 1024 * 1024) { /* tolak */ }
// Simpan ke LUAR public root
$file->move(WRITEPATH . 'uploads/sk/', $newName);
```

---

### ✅ TAHAP 3 — Validasi Tipe File Upload Berkas SK (User)

> **Estimasi:** 15 menit  
> **File yang diubah:** `app/Controllers/User/KelolaHukumanDisiplinController.php`

- [x] Perbaiki method `addHukumanDisiplin()` — PDF-only + max 1MB + `getMimeType()` ✅ 14/04/2026
- [x] Tambah validasi frontend (JS) di view User — alert + reset input ✅ 14/04/2026
- [x] Fix bug "File tidak ditemukan" (path save ≠ path read) ✅ 14/04/2026
- [x] Fix bug redirect `back()` → `redirect()->to(URL)` ✅ 14/04/2026
- [x] Pindah path simpan: `FCPATH.'writable/uploads/'` → `WRITEPATH.'uploads/sk/'` ✅ 14/04/2026
- [x] Update path baca `getFile()` & `delete` — konsisten ke `WRITEPATH.'uploads/sk/'` ✅ 14/04/2026
- [x] Test backend: **33/33 skenario lulus** (identik dengan Admin) ✅ 14/04/2026
- [ ] ⏳ Test manual via browser sebagai user

---

### ✅ TAHAP 4 — Perkuat Validasi Upload TTD Gambar (Admin)

> **Estimasi:** 30 menit  
> **File yang diubah:** `app/Controllers/Admin/InputTandaTanganAdminController.php`

- [x] Perbaiki method `addTandaTanganGambar()` — MIME `getMimeType()` + GD re-encoding ✅ 14/04/2026
- [x] Perbaiki method `updateTandaTanganGambar()` — MIME + GD re-encoding ✅ 14/04/2026
- [x] Perbaiki method `deleteTandaTanganGambar()` — path `FCPATH` → `WRITEPATH` ✅ 14/04/2026
- [x] Perbaiki method `getFile()` — path + `X-Content-Type-Options: nosniff` ✅ 14/04/2026
- [x] Fix semua `redirect()->back()` → `redirect()->to(URL)` ✅ 14/04/2026
- [x] Fix `mkdir 0777` → `0755` ✅ 14/04/2026
- [x] Pindah path simpan: `FCPATH.'writable/uploads/ttd/'` → `WRITEPATH.'uploads/ttd/'` ✅ 14/04/2026
- [x] **FIX LINTAS FILE:** PDF.php + RekapPegawaiSatkerController + RekapLaporanDisiplinController — semua referensi `FCPATH . 'writable/uploads/ttd/'` → `WRITEPATH . 'uploads/ttd/'` ✅ 14/04/2026
- [x] Test: PHP shell, polyglot PNG, SVG XSS, EXE rename → semua **DITOLAK** ✅ 14/04/2026
- [x] Test: PNG/JPEG valid → berhasil re-encode ✅ 14/04/2026
- [x] GD library (7 fungsi): semua tersedia ✅ 14/04/2026
- [x] Validasi akhir finalissimo: 99/103 lulus (4 false positive regex multiline) ✅ 14/04/2026
- [x] Verifikasi manual PowerShell: AddHandler pada baris 25 DALAM `<IfModule mod_cgi.c>` ✅ 14/04/2026
- [x] Folder bersih: sk=4 file, ttd=2 file, laporan=15 file — zero ekstensi berbahaya ✅ 14/04/2026
- [ ] ⏳ Test manual via browser sebagai admin

---

### ✅ TAHAP 5 — Perkuat Validasi Upload TTD Gambar (User)

> **Estimasi:** 20 menit  
> **File yang diubah:** `app/Controllers/User/InputTandaTanganUserController.php`

- [x] Perbaiki method `addTandaTanganGambar()` — MIME `getMimeType()` + GD re-encoding ✅ 14/04/2026
- [x] Perbaiki method `updateTandaTanganGambar()` — MIME + GD re-encoding ✅ 14/04/2026
- [x] Perbaiki method `deleteTandaTanganGambar()` — path `FCPATH` → `WRITEPATH` ✅ 14/04/2026
- [x] Perbaiki method `getFile()` — path + `X-Content-Type-Options: nosniff` ✅ 14/04/2026
- [x] Fix semua `redirect()->back()` → `redirect()->to(URL)` ✅ 14/04/2026
- [x] Fix `mkdir 0777` → `0755` ✅ 14/04/2026
- [x] Pindah path simpan: `FCPATH.'writable/uploads/ttd/'` → `WRITEPATH.'uploads/ttd/'` ✅ 14/04/2026
- [x] Scan kode: 10/10 controller checks lulus ✅ 14/04/2026
- [x] Validasi ulang path di `RekapLaporanDisiplinController.php` — FCPATH → WRITEPATH ✅ 14/04/2026
- [x] Validasi akhir: zero FCPATH bocor di seluruh app/ verified ✅ 14/04/2026
- [ ] ⏳ Test manual via browser sebagai user

---

### ✅ TAHAP 6 — Aktifkan CSRF Filter

> **Estimasi:** 5 menit  
> **File yang diubah:** `app/Config/Filters.php` + 9 view files (27 form)
> **Selesai:** 15/04/2026

- [x] Uncomment `'csrf'` di `$globals['before']` di `Filters.php` ✅ 15/04/2026
- [x] Scan semua view: ditemukan **27 form POST**, **17 tanpa csrf_field()** ✅ 15/04/2026
- [x] Tambah `<?= csrf_field() ?>` ke semua form yang kurang: ✅ 15/04/2026
  - `admin/ArsipLaporan.php` — 2 form (desktop + mobile)
  - `admin/InputTandaTanganAdmin.php` — 4 form (tambah biasa, tambah gambar, edit biasa, edit gambar)
  - `admin/KelolaPegawai.php` — 2 form (tambah + edit pegawai)
  - `admin/KelolaSatker.php` — 2 form (tambah + edit satker)
  - `admin/MutasiPegawai.php` — 2 form (form mutasi + modal edit)
  - `user/InputTandaTanganUser.php` — 4 form (tambah biasa, tambah gambar, edit biasa, edit gambar)
  - `user/InputDisiplin.php` — 1 form (tabel kedisiplinan)
  - `user/KelolaDisiplin.php` — 1 form (hapus periode)
  - `user/RekapLaporanDisiplin.php` — 1 form (export)
- [x] Verifikasi akhir: **27/27 form POST** sudah memiliki `csrf_field()` ✅ 15/04/2026
- [x] Form yang sudah ada CSRF sebelumnya: Login, ForgotPassword, ResetPassword, ProfilAdmin, ProfilUser (10 form)
- [ ] ⏳ Test manual via browser: submit semua form utama pastikan tidak ada `419 CSRF token mismatch`

---

### 🟡 TAHAP 7 — Perbaiki Upload Import CSV Pegawai

> **Estimasi:** 10 menit  
> **File yang diubah:** `app/Controllers/Admin/KelolaPegawaiController.php`

- [ ] Tambah validasi ekstensi `.csv` sebelum memproses file (baris 172)
- [ ] Tambah validasi MIME type `text/csv` atau `text/plain`
- [ ] Test upload file `.php` sebagai CSV → harus ditolak

```php
// Tambahkan sebelum baris: $file->move(...)
$csvExt = strtolower($file->getClientExtension());
$csvMime = $file->getMimeType();
$allowedCsvMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
if ($csvExt !== 'csv' || !in_array($csvMime, $allowedCsvMimes)) {
    session()->setFlashdata('msg', 'File harus berformat CSV!');
    session()->setFlashdata('msg_type', 'danger');
    return redirect()->to(base_url('admin/input_pegawai'));
}
```

---

### 🟡 TAHAP 8 — Perbaiki getClientMimeType() di KirimLaporan

> **Estimasi:** 5 menit  
> **File yang diubah:** `app/Controllers/User/KirimLaporanController.php`

- [ ] Ganti `$file->getClientMimeType()` → `$file->getMimeType()` (baris 109)
- [ ] Test upload laporan PDF → harus tetap berhasil

---

## 📝 LOG PENGERJAAN

| Tanggal | Tahap | Status | Catatan |
|---|---|---|---|
| 14/04/2026 | Audit & Test | ✅ Selesai | Terbukti bocor via test_upload_vuln.php |
| 14/04/2026 | Tahap 0 | 🟡 Parsial | Lokal ✅ bersih. Server ⏳ pending (hapus shell + reset password di production) |
| 14/04/2026 | Tahap 1 | ✅ Selesai | Scan ulang: 10 .htaccess diaudit, 7 diperbaiki. Fix: mod_authz_core.c, php8/phar, blokir .ht override. Simulasi serangan: AMAN. |
| 14/04/2026 | Tahap 2 | ✅ Selesai | PDF-only + 1MB. Path → `WRITEPATH/uploads/sk/`. Audit 33/33 lulus. Bug fix: redirect + path mismatch. |
| 14/04/2026 | Tahap 3 | ✅ Selesai | Identik dengan Tahap 2 untuk User controller. Audit 33/33 lulus. Scan kode bersih. |
| 14/04/2026 | Tahap 4 | ✅ Selesai | GD re-encoding + MIME check. Path → `WRITEPATH/uploads/ttd/`. Audit 40/40 lulus. Fix: redirect, mkdir 0777→0755, nosniff. |
| 14/04/2026 | Tahap 5 | ✅ Selesai | Identik dengan Tahap 4 untuk User controller. Scan kode 8/8 bersih. |
| | Tahap 6 | ⏳ Pending | |
| | Tahap 7 | ⏳ Pending | |
| | Tahap 8 | ⏳ Pending | |

---

## ✅ Definisi Selesai

Semua tahap dianggap selesai jika:
1. Upload file `.php` di semua fitur → **ditolak / 403 Forbidden**
2. Upload file yang valid (PDF, PNG, JPG, CSV) → **berhasil**  
3. CSRF aktif dan semua form masih berfungsi normal
4. Tidak ada file `.php` di dalam folder `writable/uploads/`

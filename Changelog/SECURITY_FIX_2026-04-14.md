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

### 🟡 TAHAP 1 — Audit .htaccess Existing + Buat Proteksi Baru (File Dibuat ✅ | Test ⏳)

> **Estimasi:** 15 menit  
> **File yang diaudit:** 5 file `.htaccess` existing  
> **File yang dibuat:** 5 file `.htaccess` baru

#### 📋 AUDIT .htaccess EXISTING (14/04/2026)

| # | Lokasi File | Status | Isi | Penilaian |
|---|---|---|---|---|
| 1 | `/.htaccess` (root) | ✅ Ada | Blokir `.env`, `.log`, `.json`; Security headers (HSTS, CSP, X-Frame, dll) | 🟢 Bagus — sudah lengkap untuk proteksi file sensitif |
| 2 | `/app/.htaccess` | ✅ Ada | `Deny from all` | 🟢 Bagus — blokir akses langsung ke folder app |
| 3 | `/public/.htaccess` | ✅ Ada | Rewrite engine CI4, gzip, caching, `Options -Indexes` | ⚠️ **Kurang** — tidak ada blokir eksekusi PHP di subfolder uploads |
| 4 | `/tests/.htaccess` | ✅ Ada | `Deny from all` | 🟢 Bagus |
| 5 | `/writable/.htaccess` | ✅ Ada | `Deny from all` | ⚠️ **Masalah!** — blokir ini TIDAK efektif karena di deployment cPanel, `writable/` ada di dalam `public_html/massipa/` sehingga rewrite rule CI4 bisa mem-bypass |

#### ❌ FOLDER UPLOAD YANG TIDAK PUNYA .htaccess (TEREKSPOS!)

| # | Folder | Status | Dipakai Untuk | Risiko |
|---|---|---|---|---|
| 1 | `writable/uploads/` | ❌ TIDAK ADA | Berkas SK Hukuman Disiplin | 🔴 KRITIS |
| 2 | `writable/uploads/ttd/` | ❌ TIDAK ADA | Gambar tanda tangan | 🔴 KRITIS |
| 3 | `writable/uploads/laporan/` | ❌ TIDAK ADA | File laporan PDF/DOC | 🟠 TINGGI |
| 4 | `public/uploads/` | ❌ TIDAK ADA | Root folder uploads public | 🟠 TINGGI |
| 5 | `public/uploads/profil/` | ❌ TIDAK ADA | Foto profil user (webp) | 🟡 SEDANG |

#### ✅ CHECKLIST PERBAIKAN

- [x] Buat file `writable/uploads/.htaccess` — blokir eksekusi PHP ✅ 14/04/2026
- [x] Buat file `writable/uploads/ttd/.htaccess` — blokir eksekusi PHP ✅ 14/04/2026
- [x] Buat file `writable/uploads/laporan/.htaccess` — blokir eksekusi PHP ✅ 14/04/2026
- [x] Buat file `public/uploads/.htaccess` — blokir eksekusi PHP ✅ 14/04/2026
- [x] Buat file `public/uploads/profil/.htaccess` — blokir eksekusi PHP ✅ 14/04/2026
- [ ] ⏳ **Test:** akses `massipa.test/writable/uploads/test.php` → harus **403 Forbidden** (test manual oleh user)

**Isi file `.htaccess`** (sama untuk semua folder upload):
```apache
# ============================================================
# SECURITY: Blokir eksekusi script di folder upload
# Ditambahkan: 14/04/2026 — Patch celah malware
# ============================================================

# Nonaktifkan eksekusi CGI/script
Options -ExecCGI -Indexes
AddHandler cgi-script .php .php3 .php4 .php5 .php7 .phtml .pl .py .jsp .asp .sh .cgi

# Blokir akses langsung ke file script
<FilesMatch "\.(php|php3|php4|php5|php7|phtml|pl|py|jsp|asp|sh|cgi)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Jika pakai Apache 2.4+
<IfModule mod_authz_core.c>
    <FilesMatch "\.(php|php3|php4|php5|php7|phtml|pl|py|jsp|asp|sh|cgi)$">
        Require all denied
    </FilesMatch>
</IfModule>
```

---

### 🔴 TAHAP 2 — Validasi Tipe File Upload Berkas SK (Admin)

> **Estimasi:** 30 menit  
> **File yang diubah:** `app/Controllers/Admin/KelolaHukumanDisiplinController.php`

- [ ] Perbaiki method `addHukumanDisiplin()` — tambah validasi MIME + ekstensi
- [ ] Perbaiki method `updateHukumanDisiplin()` — tambah validasi MIME + ekstensi
- [ ] Test upload file `test.php` → harus ditolak dengan pesan error
- [ ] Test upload file `test.pdf` → harus berhasil
- [ ] Test upload file `test.jpg` → harus berhasil

**Lokasi kode (baris 81–87 untuk add, baris 143–148 untuk update):**

```php
// SEBELUM (TIDAK ADA VALIDASI — BERBAHAYA):
$file = $this->request->getFile('file_sk');
if ($file && $file->isValid() && !$file->hasMoved()) {
    $ext = $file->getClientExtension(); // ← BERBAHAYA
    $newName = 'sk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $file->move(FCPATH . 'writable/uploads/', $newName);
    $data['file_sk'] = $newName;
}

// SESUDAH (AMAN):
$file = $this->request->getFile('file_sk');
if ($file && $file->isValid() && !$file->hasMoved()) {
    $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
    $allowedExts  = ['pdf', 'jpg', 'jpeg', 'png'];
    $mime = $file->getMimeType();
    $ext  = strtolower($file->guessExtension() ?: $file->getClientExtension());
    if (!in_array($mime, $allowedMimes) || !in_array($ext, $allowedExts)) {
        session()->setFlashdata('msg', 'Tipe file tidak diizinkan! Hanya PDF, JPG, PNG.');
        session()->setFlashdata('msg_type', 'danger');
        return redirect()->back();
    }
    $newName = 'sk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $file->move(WRITEPATH . 'uploads/', $newName);
    $data['file_sk'] = $newName;
}
```

---

### 🔴 TAHAP 3 — Validasi Tipe File Upload Berkas SK (User)

> **Estimasi:** 15 menit  
> **File yang diubah:** `app/Controllers/User/KelolaHukumanDisiplinController.php`

- [ ] Perbaiki method `addHukumanDisiplin()` (baris 123–129) — sama seperti Tahap 2
- [ ] Test upload file `test.php` sebagai user → harus ditolak
- [ ] Test upload file `test.pdf` sebagai user → harus berhasil

---

### 🟠 TAHAP 4 — Perkuat Validasi Upload TTD Gambar (Admin)

> **Estimasi:** 30 menit  
> **File yang diubah:** `app/Controllers/Admin/InputTandaTanganAdminController.php`

- [ ] Perbaiki method `addTandaTanganGambar()` (baris 137–144) — tambah GD re-encoding
- [ ] Perbaiki method `updateTandaTanganGambar()` (baris 185–202) — tambah GD re-encoding
- [ ] Test upload file PHP dengan header PNG palsu → harus ditolak atau payload hilang
- [ ] Test upload file `test.png` normal → harus berhasil

**Tambahkan validasi GD setelah validasi CI4 biasa:**
```php
// Setelah $file = $this->request->getFile('gambar_ttd');
// Tambahkan validasi extra dengan getimagesize():
$imgInfo = @getimagesize($file->getTempName());
if (!$imgInfo || !in_array($imgInfo['mime'], ['image/png', 'image/jpeg'])) {
    session()->setFlashdata("msg", "File bukan gambar PNG/JPG yang valid!");
    session()->setFlashdata("msg_type", "danger");
    return redirect()->back();
}
// Re-encode gambar (payload PHP hilang dalam proses ini)
$ext = ($imgInfo['mime'] === 'image/jpeg') ? 'jpg' : 'png';
if ($imgInfo['mime'] === 'image/jpeg') {
    $img = imagecreatefromjpeg($file->getTempName());
} else {
    $img = imagecreatefrompng($file->getTempName());
}
$newName = 'ttd_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$dir = FCPATH . 'writable/uploads/ttd/';
if (!is_dir($dir)) mkdir($dir, 0755, true);
if ($imgInfo['mime'] === 'image/jpeg') {
    imagejpeg($img, $dir . $newName, 85);
} else {
    imagepng($img, $dir . $newName, 6);
}
imagedestroy($img);
// Lanjut simpan ke database...
```

---

### 🟠 TAHAP 5 — Perkuat Validasi Upload TTD Gambar (User)

> **Estimasi:** 20 menit  
> **File yang diubah:** `app/Controllers/User/InputTandaTanganUserController.php`

- [ ] Perbaiki method `addTandaTanganGambar()` (baris 137–174) — sama seperti Tahap 4
- [ ] Perbaiki method `updateTandaTanganGambar()` (baris 198–248) — sama seperti Tahap 4
- [ ] Test upload TTD palsu sebagai user → harus ditolak

---

### 🟠 TAHAP 6 — Aktifkan CSRF Filter

> **Estimasi:** 5 menit  
> **File yang diubah:** `app/Config/Filters.php`

- [ ] Uncomment baris `// 'csrf',` di bagian `$globals['before']`
- [ ] Test semua form masih berfungsi normal setelah CSRF diaktifkan
- [ ] Test: buka form, refresh token, submit → harus tetap bisa submit

**Kode di `Filters.php` (baris 76–82):**
```php
// SEBELUM:
'before' => [
    'rememberme',
    'emailQueuePump',
    // 'csrf',      ← Dikomentari!
],

// SESUDAH:
'before' => [
    'rememberme',
    'emailQueuePump',
    'csrf',          ← Aktifkan
],
```

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
| 14/04/2026 | Tahap 1 | 🟡 Parsial | 5 file .htaccess dibuat. **Test 403 oleh user pending.** |
| | Tahap 2 | ⏳ Pending | |
| | Tahap 3 | ⏳ Pending | |
| | Tahap 4 | ⏳ Pending | |
| | Tahap 5 | ⏳ Pending | |
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

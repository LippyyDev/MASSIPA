# Panduan Setup Cron Job Email Queue — MASSIPA

Cron job ini memastikan email notifikasi (laporan, hukuman disiplin, reset password)
terkirim secara otomatis setiap menit tanpa bergantung pada traffic website.

---

## ✅ Perintah Cron (Command)

Sesuaikan path dengan struktur folder project di server Anda:

```
[PATH_PHP] [PATH_SPARK] email:send-queue 20 >> [PATH_LOG] 2>&1
```

**Contoh jika project di `ci4_core/`:**
```
/usr/local/bin/ea-php81 /home/masn6643/ci4_core/spark email:send-queue 20 >> /home/masn6643/ci4_core/writable/logs/cron_email_queue.log 2>&1
```

**Contoh jika project di `public_html/`:**
```
/usr/local/bin/ea-php81 /home/masn6643/public_html/spark email:send-queue 20 >> /home/masn6643/public_html/writable/logs/cron_email_queue.log 2>&1
```

---

## 🔵 RumahWeb (cPanel)

### Path PHP (sesuaikan versi dengan MultiPHP Manager):
| Versi PHP | Path |
|-----------|------|
| PHP 8.2   | `/usr/local/bin/ea-php82` |
| PHP 8.1   | `/usr/local/bin/ea-php81` |
| PHP 8.0   | `/usr/local/bin/ea-php80` |
| PHP 7.4   | `/usr/local/bin/ea-php74` |

> Cek versi aktif: **cPanel → MultiPHP Manager**

### Langkah Setup:

1. Login **cPanel** → **Advanced** → **Cron Jobs**
2. Gulir ke bawah ke bagian **"Add New Cron Job"**
3. **Common Settings** → pilih `Once Per Minute(* * * * *)`
4. **Command** → isi dengan perintah dari bagian atas (sesuaikan path dan versi PHP)
5. Klik **Add New Cron Job**

### Verifikasi:
Tunggu 1–2 menit, lalu buka File Manager:
```
ci4_core/writable/logs/cron_email_queue.log
```
Isi normal:
```
[2026-04-17 08:00:01] Antrian kosong, tidak ada yang diproses.
[2026-04-17 08:01:01] Selesai. Terkirim: 2 email dalam 3.12s.
```

---

## 🟠 Hostinger (hPanel)

### Path PHP: `/usr/bin/php`

> **PERHATIAN:** hPanel Hostinger **tidak mendukung karakter `>>` dan `2>&1`**
> langsung di field Command. Wajib menggunakan file `.sh` perantara.

### Langkah Setup:

**Step 1 — Buat file `cron_email_hostinger.sh`** di root project via File Manager:
```sh
#!/bin/sh
/usr/bin/php /home/u123456789/domains/namadomain.com/public_html/spark email:send-queue 20 >> /home/u123456789/domains/namadomain.com/public_html/writable/logs/cron_email_queue.log 2>&1
```
> Sesuaikan path `/home/u[angka]/domains/[domain]/public_html/` dengan path aktual Anda.
> Path bisa dilihat di: **hPanel → File Manager** (lihat address bar saat membuka folder project).

**Step 2 — Di hPanel → Advanced → Cron Jobs:**
- **Type:** Custom
- **Schedule:** Every minute `* * * * *`
- **Command:**
```
/bin/sh /home/u123456789/domains/namadomain.com/public_html/cron_email_hostinger.sh
```

**Step 3 — Klik Save**

### Verifikasi:
Tunggu 1–2 menit, cek File Manager:
```
public_html/writable/logs/cron_email_queue.log
```

---

## 🔍 Troubleshooting

### Log kosong / tidak muncul setelah 2 menit
- Path `spark` salah → cek di File Manager, klik kanan file `spark` → **Copy Path**
- PHP path salah → cek di cPanel → MultiPHP Manager atau ketik `which php` di Terminal

### Error "Permission denied"
```bash
chmod 644 writable/logs/cron_email_queue.log
chmod 755 writable/logs/
```

### Email di database tidak terkirim (fail_count >= 5)
Jalankan query di phpMyAdmin untuk reset dan coba kirim ulang:
```sql
UPDATE email_queue SET fail_count = 0 WHERE fail_count >= 5 AND is_sent = 0;
```

### Cek status email di database
```sql
SELECT id, recipient, subject, is_sent, fail_count, last_error, created_at
FROM email_queue
ORDER BY id DESC
LIMIT 20;
```

| `is_sent` | Arti |
|-----------|------|
| `0` | ⏳ Pending — menunggu diproses cron |
| `1` | ✅ Terkirim |
| `2` | 🔄 Sedang diproses saat ini |
| `fail_count >= 5` | ❌ Gagal 5x — tidak akan diproses lagi |

---

## ⚙️ Pengaturan Cron (Referensi)

| Setting | Nilai | Keterangan |
|---------|-------|-----------|
| Interval cron | Setiap 1 menit | Delay maksimal 60 detik |
| Limit per run | 20 email | Aman untuk SMTP Gmail (~5 detik/email) |
| Lock file | `writable/email_queue.lock` | Cegah overlap jika proses belum selesai |
| Lock timeout | 5 menit | Auto-release jika proses crash |
| Retry maksimal | 5 kali | Setelah gagal 5x → dihentikan |
| Purge otomatis | 1 jam setelah terkirim | Bersihkan email lama dari tabel |

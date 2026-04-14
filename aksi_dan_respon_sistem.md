# Format Aksi User/Admin dan Respon Sistem - MASSIPA

## FITUR ADMIN

### 1. BERANDA

| Aksi Admin         | Respon Sistem                            |
| ------------------ | ---------------------------------------- |
| Login ke sistem    | Validasi kredensial, tampilkan dashboard |
| Melihat statistik  | Menampilkan grafik dan data statistik    |
| Melihat notifikasi | Menampilkan daftar notifikasi terbaru    |
| Refresh data       | Update data real-time                    |

### 2. PROFILE

| Aksi Admin           | Respon Sistem                                     |
| -------------------- | ------------------------------------------------- |
| Melihat data profile | Menampilkan informasi profile admin               |
| Edit data profile    | Menampilkan form edit dengan data existing        |
| Simpan perubahan     | Validasi data, update database, konfirmasi sukses |
| Upload foto          | Validasi file, simpan ke server, update database  |

### 3. KELOLA USER

| Aksi Admin          | Respon Sistem                                     |
| ------------------- | ------------------------------------------------- |
| Melihat daftar user | Menampilkan tabel user dengan pagination          |
| Tambah user baru    | Menampilkan form input user                       |
| Edit data user      | Menampilkan form edit dengan data existing        |
| Hapus user          | Konfirmasi penghapusan, hapus dari database       |
| Simpan perubahan    | Validasi data, update database, notifikasi sukses |

### 4. KELOLA PEGAWAI

| Aksi Admin             | Respon Sistem                                     |
| ---------------------- | ------------------------------------------------- |
| Melihat daftar pegawai | Menampilkan tabel pegawai dengan filter           |
| Tambah pegawai baru    | Menampilkan form input pegawai                    |
| Edit data pegawai      | Menampilkan form edit dengan data existing        |
| Hapus pegawai          | Konfirmasi penghapusan, hapus dari database       |
| Simpan perubahan       | Validasi data, update database, notifikasi sukses |

### 5. KELOLA SATKER

| Aksi Admin            | Respon Sistem                                     |
| --------------------- | ------------------------------------------------- |
| Melihat daftar satker | Menampilkan tabel satker                          |
| Tambah satker baru    | Menampilkan form input satker                     |
| Edit data satker      | Menampilkan form edit dengan data existing        |
| Hapus satker          | Konfirmasi penghapusan, hapus dari database       |
| Simpan perubahan      | Validasi data, update database, notifikasi sukses |

### 6. KELOLA DISIPLIN

| Aksi Admin              | Respon Sistem                                     |
| ----------------------- | ------------------------------------------------- |
| Melihat daftar disiplin | Menampilkan tabel data disiplin                   |
| Tambah data disiplin    | Menampilkan form input disiplin                   |
| Edit data disiplin      | Menampilkan form edit dengan data existing        |
| Hapus data disiplin     | Konfirmasi penghapusan, hapus dari database       |
| Simpan perubahan        | Validasi data, update database, notifikasi sukses |

### 7. TRACKING KEDISIPLIAN

| Aksi Admin                 | Respon Sistem                           |
| -------------------------- | --------------------------------------- |
| Melihat tracking pegawai   | Menampilkan data tracking dengan grafik |
| Filter berdasarkan periode | Menampilkan data sesuai filter          |
| Export data tracking       | Generate file Excel/PDF, download       |
| Refresh data               | Update data real-time                   |

### 8. INPUT TANDA TANGAN

| Aksi Admin               | Respon Sistem                      |
| ------------------------ | ---------------------------------- |
| Upload file tanda tangan | Validasi file, simpan ke server    |
| Verifikasi tanda tangan  | Proses validasi, tampilkan preview |
| Simpan tanda tangan      | Update database, konfirmasi sukses |
| Hapus tanda tangan       | Konfirmasi penghapusan, hapus file |

### 9. KELOLA LAPORAN

| Aksi Admin             | Respon Sistem                           |
| ---------------------- | --------------------------------------- |
| Melihat daftar laporan | Menampilkan tabel laporan dengan status |
| Verifikasi laporan     | Menampilkan detail laporan untuk review |
| Approve/Reject laporan | Update status, kirim notifikasi         |
| Download laporan       | Generate file, download                 |

### 10. KELOLA HUKUMAN

| Aksi Admin             | Respon Sistem                                     |
| ---------------------- | ------------------------------------------------- |
| Melihat daftar hukuman | Menampilkan tabel hukuman                         |
| Tambah data hukuman    | Menampilkan form input hukuman                    |
| Edit data hukuman      | Menampilkan form edit dengan data existing        |
| Hapus data hukuman     | Konfirmasi penghapusan, hapus dari database       |
| Simpan perubahan       | Validasi data, update database, notifikasi sukses |

### 11. ARSIP LAPORAN

| Aksi Admin            | Respon Sistem                          |
| --------------------- | -------------------------------------- |
| Melihat arsip laporan | Menampilkan daftar arsip dengan search |
| Search arsip          | Menampilkan hasil pencarian            |
| Download arsip        | Generate file, download                |
| Hapus arsip           | Konfirmasi penghapusan, hapus file     |

### 12. STATUS DISIPLIN

| Aksi Admin                      | Respon Sistem                              |
| ------------------------------- | ------------------------------------------ |
| Melihat status disiplin pegawai | Menampilkan status dengan grafik           |
| Update status                   | Menampilkan form update status             |
| Simpan perubahan                | Validasi data, update database, notifikasi |

### 13. REKAP PEGAWAI

| Aksi Admin                  | Respon Sistem                     |
| --------------------------- | --------------------------------- |
| Generate rekap pegawai      | Proses data, tampilkan preview    |
| Filter berdasarkan kriteria | Menampilkan data sesuai filter    |
| Export rekap                | Generate file Excel/PDF, download |

### 14. PENGATURAN

| Aksi Admin         | Respon Sistem                         |
| ------------------ | ------------------------------------- |
| Konfigurasi sistem | Menampilkan form pengaturan           |
| Update pengaturan  | Validasi input, update konfigurasi    |
| Simpan pengaturan  | Simpan ke database, konfirmasi sukses |

### 15. NOTIFIKASI

| Aksi Admin         | Respon Sistem                               |
| ------------------ | ------------------------------------------- |
| Melihat notifikasi | Menampilkan daftar notifikasi               |
| Mark as read       | Update status notifikasi                    |
| Hapus notifikasi   | Konfirmasi penghapusan, hapus dari database |

## FITUR USER

### 1. DASHBOARD

| Aksi User                  | Respon Sistem                                 |
| -------------------------- | --------------------------------------------- |
| Login ke sistem            | Validasi kredensial, tampilkan dashboard user |
| Melihat statistik personal | Menampilkan data personal user                |
| Melihat notifikasi         | Menampilkan notifikasi user                   |
| Melihat status terbaru     | Menampilkan status update terbaru             |

### 2. EDIT PROFIL

| Aksi User           | Respon Sistem                                     |
| ------------------- | ------------------------------------------------- |
| Melihat data profil | Menampilkan informasi profil user                 |
| Edit data profil    | Menampilkan form edit dengan data existing        |
| Upload foto profil  | Validasi file, simpan ke server                   |
| Simpan perubahan    | Validasi data, update database, konfirmasi sukses |

### 3. DAFTAR PEGAWAI

| Aksi User              | Respon Sistem                         |
| ---------------------- | ------------------------------------- |
| Melihat daftar pegawai | Menampilkan tabel pegawai (read-only) |
| Search pegawai         | Menampilkan hasil pencarian           |
| Filter pegawai         | Menampilkan data sesuai filter        |
| Lihat detail pegawai   | Menampilkan detail informasi pegawai  |

### 4. KELOLA DISIPLIN

| Aksi User             | Respon Sistem                                     |
| --------------------- | ------------------------------------------------- |
| Melihat data disiplin | Menampilkan data disiplin user                    |
| Input data disiplin   | Menampilkan form input disiplin                   |
| Edit data disiplin    | Menampilkan form edit dengan data existing        |
| Simpan perubahan      | Validasi data, update database, notifikasi sukses |

### 5. KELOLA HUKUMAN

| Aksi User              | Respon Sistem                                     |
| ---------------------- | ------------------------------------------------- |
| Melihat daftar hukuman | Menampilkan hukuman terkait user                  |
| Input data hukuman     | Menampilkan form input hukuman                    |
| Edit data hukuman      | Menampilkan form edit dengan data existing        |
| Simpan perubahan       | Validasi data, update database, notifikasi sukses |

### 6. KELOLA TANDA TANGAN

| Aksi User                | Respon Sistem                                     |
| ------------------------ | ------------------------------------------------- |
| Melihat tanda tangan     | Menampilkan tanda tangan user                     |
| Upload tanda tangan baru | Validasi file, simpan ke server                   |
| Edit tanda tangan        | Menampilkan form edit tanda tangan                |
| Simpan perubahan         | Validasi data, update database, konfirmasi sukses |

### 7. REKAP LAPORAN

| Aksi User                  | Respon Sistem                       |
| -------------------------- | ----------------------------------- |
| Generate rekap laporan     | Proses data user, tampilkan preview |
| Filter berdasarkan periode | Menampilkan data sesuai filter      |
| Export rekap               | Generate file Excel/PDF, download   |

### 8. KIRIM LAPORAN

| Aksi User                | Respon Sistem                                       |
| ------------------------ | --------------------------------------------------- |
| Buat laporan baru        | Menampilkan form input laporan                      |
| Upload dokumen pendukung | Validasi file, simpan ke server                     |
| Preview laporan          | Menampilkan preview laporan                         |
| Kirim laporan            | Validasi data, simpan ke database, kirim notifikasi |

### 9. NOTIFIKASI

| Aksi User          | Respon Sistem                               |
| ------------------ | ------------------------------------------- |
| Melihat notifikasi | Menampilkan daftar notifikasi user          |
| Mark as read       | Update status notifikasi                    |
| Hapus notifikasi   | Konfirmasi penghapusan, hapus dari database |

## SISTEM RESPON UMUM

| Aksi              | Respon Sistem                                           |
| ----------------- | ------------------------------------------------------- |
| Login             | Validasi kredensial, set session, redirect ke dashboard |
| Logout            | Clear session, redirect ke halaman login                |
| Error handling    | Tampilkan pesan error yang sesuai                       |
| Data validation   | Validasi input, tampilkan error jika tidak valid        |
| File upload       | Validasi file, simpan ke server, update database        |
| Export data       | Generate file sesuai format, download                   |
| Send notification | Kirim notifikasi ke user/admin terkait                  |


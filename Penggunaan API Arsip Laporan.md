# API Documentation - Sistem Kedisiplinan Hakim

## 📋 **Daftar Isi**

1. [Informasi Umum](#informasi-umum)
2. [Authentication](#authentication)
3. [Endpoint Overview](#endpoint-overview)
4. [Detail Endpoint](#detail-endpoint)
5. [Cara Penggunaan](#cara-penggunaan)
6. [Error Handling](#error-handling)
7. [Field Reference](#field-reference)

---

## 🔧 **Informasi Umum**

### Base URL

```
http://localhost:8080/api
```

### Format Response

Semua response menggunakan format JSON dengan struktur:

```json
{
  "status": "success|error",
  "message": "Pesan response",
  "data": { ... }
}
```

---

## 🔐 **Authentication**

### API Key Required

Semua endpoint **WAJIB** menyertakan API Key melalui header:

```
X-API-Key: your_api_key_here
```

### Cara Mendapatkan API Key

1. Login sebagai Admin
2. Buka menu "Pengaturan"
3. Pilih tab "API Key"
4. Generate API Key baru atau gunakan yang sudah ada

---

## 📊 **Endpoint Overview**

| No  | Method | Endpoint                        | Kategori | Deskripsi                               |
| --- | ------ | ------------------------------- | -------- | --------------------------------------- |
| 1   | GET    | `/laporan`                      | Data     | Ambil semua laporan                     |
| 2   | GET    | `/laporan/{id}`                 | Data     | Ambil detail laporan                    |
| 3   | GET    | `/laporan/file/{id}`            | File     | Download file laporan                   |
| 4   | GET    | `/laporan/disiplin`             | Data     | Laporan kategori Disiplin               |
| 5   | GET    | `/laporan/apel`                 | Data     | Laporan kategori Apel                   |
| 6   | GET    | `/laporan/{kategori}/file/{id}` | File     | Download file berdasarkan kategori      |
| 7   | GET    | `/laporan/{kategori}/link/{id}` | Link     | Redirect ke link drive (berkategori)    |
| 8   | GET    | `/laporan/link/{id}`            | Link     | Redirect ke link drive (semua kategori) |
| 9   | GET    | `/pegawai`                      | Data     | Ambil daftar pegawai & satker aktif     |
| 10  | GET    | `/pegawai/{nim}`                | Data     | Tracking kedisiplinan per pegawai       |

---

## 📝 **Detail Endpoint**

### 1. **Get All Laporan**

**GET** `/laporan`

**Deskripsi**: Mengambil semua laporan yang sudah diterima dengan pagination dan filter.

**Query Parameters**:

- `user_id` (optional): Filter berdasarkan ID pengirim
- `bulan` (optional): Filter berdasarkan bulan (1-12)
- `tahun` (optional): Filter berdasarkan tahun
- `status` (optional): Filter berdasarkan status
- `limit` (optional): Jumlah data per halaman (default: 10)
- `offset` (optional): Offset untuk pagination (default: 0)

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/laporan?limit=5&bulan=1&tahun=2024" \
  -H "X-API-Key: your_api_key_here"
```

**Response**:

```json
{
  "status": "success",
  "message": "Data laporan berhasil diambil",
  "data": {
    "laporan": [
      {
        "id": 1,
        "nama_laporan": "Laporan Kedisiplinan Januari 2024",
        "bulan": 1,
        "tahun": 2024,
        "keterangan": "Laporan kedisiplinan pegawai",
        "status": "diterima",
        "feedback": null,
        "file_path": "1749646786_8c856a5c67f38f92ba85.pdf",
        "kategori": "Laporan Disiplin",
        "link_drive": "https://drive.google.com/file/d/...",
        "pengirim": {
          "id": 1,
          "nama_lengkap": "John Doe",
          "satker_id": 1
        },
        "created_at": "2024-01-15 10:30:00",
        "updated_at": "2024-01-15 10:30:00",
        "file_url": "http://localhost:8080/api/laporan/file/1",
        "link_url": "http://localhost:8080/api/laporan/link/1"
      }
    ],
    "pagination": {
      "total": 50,
      "limit": 10,
      "offset": 0,
      "total_pages": 5
    }
  }
}
```

**Hasil**: List semua laporan dengan pagination dan informasi lengkap.

---

### 2. **Get Laporan by ID**

**GET** `/laporan/{id}`

**Deskripsi**: Mengambil detail lengkap satu laporan berdasarkan ID.

**Path Parameters**:

- `id`: ID laporan (required)

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/laporan/1" \
  -H "X-API-Key: your_api_key_here"
```

**Response**:

```json
{
  "status": "success",
  "message": "Detail laporan berhasil diambil",
  "data": {
    "id": 1,
    "nama_laporan": "Laporan Kedisiplinan Januari 2024",
    "bulan": 1,
    "tahun": 2024,
    "keterangan": "Laporan kedisiplinan pegawai",
    "status": "diterima",
    "feedback": null,
    "file_path": "1749646786_8c856a5c67f38f92ba85.pdf",
    "kategori": "Laporan Disiplin",
    "link_drive": "https://drive.google.com/file/d/...",
    "pengirim": {
      "id": 1,
      "nama_lengkap": "John Doe",
      "satker_id": 1
    },
    "created_at": "2024-01-15 10:30:00",
    "updated_at": "2024-01-15 10:30:00",
    "file_url": "http://localhost:8080/api/laporan/file/1",
    "link_url": "http://localhost:8080/api/laporan/link/1"
  }
}
```

**Hasil**: Detail lengkap satu laporan dengan semua field.

---

### 3. **Get Laporan File**

**GET** `/laporan/file/{id}`

**Deskripsi**: Download file laporan langsung (PDF, Excel, dll).

**Path Parameters**:

- `id`: ID laporan (required)

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/laporan/file/1" \
  -H "X-API-Key: your_api_key_here" \
  --output laporan.pdf
```

**Response**: File binary content dengan header:

- `Content-Type`: Sesuai tipe file (application/pdf, application/vnd.ms-excel, dll)
- `Content-Disposition`: inline; filename="nama_laporan.pdf"

**Hasil**: File laporan langsung download ke komputer.

---

### 4. **Get Laporan by Kategori - Disiplin**

**GET** `/laporan/disiplin`

**Deskripsi**: Mengambil semua laporan dengan kategori "Laporan Disiplin" saja.

**Query Parameters**:

- `user_id` (optional): Filter berdasarkan ID pengirim
- `bulan` (optional): Filter berdasarkan bulan (1-12)
- `tahun` (optional): Filter berdasarkan tahun
- `limit` (optional): Jumlah data per halaman (default: 10)
- `offset` (optional): Offset untuk pagination (default: 0)

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/laporan/disiplin?limit=5" \
  -H "X-API-Key: your_api_key_here"
```

**Response**: Format sama seperti endpoint `/laporan` tetapi hanya menampilkan laporan dengan kategori "Laporan Disiplin".

**Hasil**: List laporan kategori Disiplin dengan pagination.

---

### 5. **Get Laporan by Kategori - Apel**

**GET** `/laporan/apel`

**Deskripsi**: Mengambil semua laporan dengan kategori "Laporan Apel" saja.

**Query Parameters**: Sama seperti endpoint disiplin.

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/laporan/apel?limit=5" \
  -H "X-API-Key: your_api_key_here"
```

**Response**: Format sama seperti endpoint `/laporan` tetapi hanya menampilkan laporan dengan kategori "Laporan Apel".

**Hasil**: List laporan kategori Apel dengan pagination.

---

### 6. **Get File by Kategori**

**GET** `/laporan/{kategori}/file/{id}`

**Deskripsi**: Download file laporan berdasarkan kategori dan ID.

**Path Parameters**:

- `kategori`: "disiplin" atau "apel" (required)
- `id`: ID laporan (required)

**Contoh Request**:

```bash
# Download file laporan disiplin
curl -X GET "http://localhost:8080/api/laporan/disiplin/file/1" \
  -H "X-API-Key: your_api_key_here" \
  --output laporan_disiplin.pdf

# Download file laporan apel
curl -X GET "http://localhost:8080/api/laporan/apel/file/2" \
  -H "X-API-Key: your_api_key_here" \
  --output laporan_apel.pdf
```

**Response**: File binary content (sama seperti endpoint file biasa).

**Hasil**: File laporan langsung download dengan validasi kategori.

**Error Response** (404):

```json
{
  "status": "error",
  "message": "Kategori tidak valid"
}
```

---

### 7. **Get Link Drive by Kategori**

**GET** `/laporan/{kategori}/link/{id}`

**Deskripsi**: Redirect langsung ke link drive berdasarkan kategori dan ID.

**Path Parameters**:

- `kategori`: "disiplin" atau "apel" (required)
- `id`: ID laporan (required)

**Contoh Request**:

```bash
# Redirect ke link drive laporan disiplin
curl -I "http://localhost:8080/api/laporan/disiplin/link/1" \
  -H "X-API-Key: your_api_key_here"

# Redirect ke link drive laporan apel
curl -I "http://localhost:8080/api/laporan/apel/link/2" \
  -H "X-API-Key: your_api_key_here"
```

**Response**: HTTP 302 Redirect ke URL link drive.

**Hasil**: Browser langsung diarahkan ke link Google Drive/Dropbox dll.

**Error Response** (404):

```json
{
  "status": "error",
  "message": "Link drive tidak tersedia untuk laporan ini"
}
```

---

### 8. **Get Link Drive (All Categories)**

**GET** `/laporan/link/{id}`

**Deskripsi**: Redirect langsung ke link drive untuk semua kategori.

**Path Parameters**:

- `id`: ID laporan (required)

**Contoh Request**:

```bash
curl -I "http://localhost:8080/api/laporan/link/1" \
  -H "X-API-Key: your_api_key_here"
```

**Response**: HTTP 302 Redirect ke URL link drive.

**Hasil**: Browser langsung diarahkan ke link drive tanpa perlu menentukan kategori.

**Error Response** (404):

```json
{
  "status": "error",
  "message": "Link drive tidak tersedia untuk laporan ini"
}
```

---

### 9. **Get All Pegawai**

**GET** `/pegawai`

**Deskripsi**: Mengambil daftar pegawai beserta informasi satker, jabatan, dan tautan cepat untuk melihat tracking kedisiplinannya.

**Query Parameters**:

- `status` (optional, default: `aktif`): `aktif`, `pensiun`, atau `all`
- `search` (optional): Pencarian nama, NIP, atau jabatan
- `limit` (optional, default: 25, max: 100): Jumlah data per halaman
- `offset` (optional, default: 0): Offset untuk pagination
- `sort` (optional, default: `nama`): `nama`, `nip`, `jabatan`, `created_at`
- `order` (optional, default: `ASC`): `ASC` atau `DESC`

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/pegawai?limit=5&status=all" \
  -H "X-API-Key: your_api_key_here"
```

**Response**:

```json
{
  "status": "success",
  "message": "Data pegawai berhasil diambil",
  "data": {
    "pegawai": [
      {
        "id": 5225,
        "nama": "Dr. Drs. Khaeril  R, M.H.",
        "nip": "195912311986031038",
        "pangkat": "Pembina Utama",
        "golongan": "IV/e",
        "jabatan": "Ketua",
        "status": "aktif",
        "satker": {
          "id": 12,
          "nama": "Pengadilan Agama X",
          "riwayat_mutasi_id": 8812,
          "aktif_sejak": "2023-01-01"
        },
        "tracking_url": "http://localhost:8080/api/pegawai/195912311986031038",
        "created_at": "2025-08-07 12:08:23",
        "updated_at": "2025-08-07 12:08:23"
      }
    ],
    "pagination": {
      "total": 300,
      "limit": 5,
      "offset": 0,
      "total_pages": 60,
      "status_filter": "all",
      "search": null
    }
  }
}
```

**Hasil**: Daftar pegawai lengkap dengan metadata satker dan URL siap pakai untuk endpoint tracking.

---

### 10. **Get Tracking Pegawai by NIM**

**GET** `/pegawai/{nim}`

**Deskripsi**: Menampilkan riwayat kedisiplinan detail untuk satu pegawai berdasarkan NIM (gunakan NIP 18 digit). Tersedia alias endpoint `/pegwai/{nim}` bila dibutuhkan.

**Path Parameters**:

- `nim`: NIP pegawai (required)

**Query Parameters**:

- `tahun` (optional): Filter data tracking untuk tahun tertentu
- `limit` (optional, default: 12, max: 24): Jumlah record track per halaman
- `offset` (optional, default: 0): Offset pagination track record

**Contoh Request**:

```bash
curl -X GET "http://localhost:8080/api/pegawai/195912311986031038?tahun=2024&limit=6" \
  -H "X-API-Key: your_api_key_here"
```

**Response**:

```json
{
  "status": "success",
  "message": "Data tracking kedisiplinan pegawai berhasil diambil",
  "data": {
    "pegawai": {
      "id": 5225,
      "nama": "Dr. Drs. Khaeril  R, M.H.",
      "nip": "195912311986031038",
      "jabatan": "Ketua",
      "satker": {
        "id": 12,
        "nama": "Pengadilan Agama X",
        "aktif_sejak": "2023-01-01"
      },
      "tracking_url": "http://localhost:8080/api/pegawai/195912311986031038"
    },
    "statistik": {
      "terlambat": 5,
      "tidak_absen_masuk": 0,
      "pulang_awal": 2,
      "tidak_absen_pulang": 0,
      "keluar_tidak_izin": 0,
      "tidak_masuk_tanpa_ket": 0,
      "tidak_masuk_sakit": 1,
      "tidak_masuk_kerja": 0
    },
    "tahun_tersedia": [2024, 2023, 2022],
    "track_record": {
      "items": [
        {
          "id": 901,
          "bulan": 1,
          "tahun": 2024,
          "satker": "Pengadilan Agama X",
          "pegawai": {
            "nama": "Dr. Drs. Khaeril  R, M.H.",
            "nip": "195912311986031038",
            "jabatan": "Ketua"
          },
          "pelanggaran": {
            "terlambat": 2,
            "tidak_absen_masuk": 0,
            "pulang_awal": 1,
            "tidak_absen_pulang": 0,
            "keluar_tidak_izin": 0,
            "tidak_masuk_tanpa_ket": 0,
            "tidak_masuk_sakit": 0,
            "tidak_masuk_kerja": 0
          },
          "bentuk_pembinaan": "Pembinaan lisan",
          "keterangan": "Perlu evaluasi apel pagi"
        }
      ],
      "pagination": {
        "total": 18,
        "limit": 6,
        "offset": 0,
        "total_pages": 3,
        "tahun_filter": 2024
      }
    },
    "ringkasan": [
      {
        "bulan": 1,
        "tahun": 2024,
        "satker": "Pengadilan Agama X",
        "pelanggaran": {
          "terlambat": 2,
          "tidak_absen_masuk": 0,
          "pulang_awal": 1,
          "tidak_absen_pulang": 0,
          "keluar_tidak_izin": 0,
          "tidak_masuk_tanpa_ket": 0,
          "tidak_masuk_sakit": 0,
          "tidak_masuk_kerja": 0
        }
      }
    ]
  }
}
```

**Hasil**: Dashboard lengkap untuk memonitor kedisiplinan pegawai tertentu, termasuk rekap bulanan dan statistik agregat.

---

## 🚀 **Cara Penggunaan**

### **1. Menggunakan cURL**

#### **Ambil Semua Laporan**

```bash
curl -X GET "http://localhost:8080/api/laporan?limit=10" \
  -H "X-API-Key: your_api_key_here"
```

#### **Download File**

```bash
curl -X GET "http://localhost:8080/api/laporan/file/1" \
  -H "X-API-Key: your_api_key_here" \
  --output laporan.pdf
```

#### **Redirect ke Link Drive**

```bash
curl -I "http://localhost:8080/api/laporan/link/1" \
  -H "X-API-Key: your_api_key_here"
```

#### **Ambil Data Pegawai**

```bash
curl -X GET "http://localhost:8080/api/pegawai?limit=20&status=all" \
  -H "X-API-Key: your_api_key_here"
```

#### **Tracking Pegawai by NIM**

```bash
curl -X GET "http://localhost:8080/api/pegawai/195912311986031038?tahun=2024" \
  -H "X-API-Key: your_api_key_here"
```

### **2. Menggunakan JavaScript (Fetch)**

#### **Ambil Data Laporan**

```javascript
const response = await fetch("http://localhost:8080/api/laporan?limit=5", {
  headers: {
    "X-API-Key": "your_api_key_here",
  },
});
const data = await response.json();
console.log(data.data.laporan);
```

#### **Download File**

```javascript
const response = await fetch("http://localhost:8080/api/laporan/file/1", {
  headers: {
    "X-API-Key": "your_api_key_here",
  },
});
const blob = await response.blob();
const url = window.URL.createObjectURL(blob);
const a = document.createElement("a");
a.href = url;
a.download = "laporan.pdf";
a.click();
```

#### **Redirect ke Link Drive**

```javascript
window.open("http://localhost:8080/api/laporan/link/1", "_blank");
```

#### **Ambil Data Pegawai**

```javascript
const pegawaiResponse = await fetch(
  "http://localhost:8080/api/pegawai?limit=5",
  {
    headers: {
      "X-API-Key": "your_api_key_here",
    },
  }
);
const pegawaiData = await pegawaiResponse.json();
console.log(pegawaiData.data.pegawai);
```

#### **Tracking Pegawai by NIM**

```javascript
const trackingResponse = await fetch(
  "http://localhost:8080/api/pegawai/195912311986031038?tahun=2024",
  {
    headers: {
      "X-API-Key": "your_api_key_here",
    },
  }
);
const trackingData = await trackingResponse.json();
console.log(trackingData.data.track_record.items);
```

### **3. Menggunakan PHP (cURL)**

#### **Ambil Data Laporan**

```php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/api/laporan?limit=5');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: your_api_key_here']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data['data']['laporan']);
```

### **4. Menggunakan Python (requests)**

#### **Ambil Data Laporan**

```python
import requests

headers = {'X-API-Key': 'your_api_key_here'}
response = requests.get('http://localhost:8080/api/laporan?limit=5', headers=headers)
data = response.json()

for laporan in data['data']['laporan']:
    print(f"ID: {laporan['id']}")
    print(f"Nama: {laporan['nama_laporan']}")
    print(f"Kategori: {laporan['kategori']}")
    print(f"File URL: {laporan['file_url']}")
    print(f"Link URL: {laporan['link_url']}")
    print("---")
```

---

## ⚠️ **Error Handling**

### **400 Bad Request**

```json
{
  "status": "error",
  "message": "ID laporan diperlukan"
}
```

**Penyebab**: Parameter yang diperlukan tidak diberikan atau format salah.

### **401 Unauthorized**

```json
{
  "status": "error",
  "message": "API Key tidak valid"
}
```

**Penyebab**: API Key tidak ada, salah, atau tidak aktif.

### **403 Forbidden**

```json
{
  "status": "error",
  "message": "File hanya dapat diakses jika laporan sudah disetujui."
}
```

**Penyebab**: Mencoba mengakses file laporan yang belum disetujui.

### **404 Not Found**

```json
{
  "status": "error",
  "message": "Laporan tidak ditemukan"
}
```

**Penyebab**: ID laporan tidak ada atau kategori tidak valid.

### **500 Internal Server Error**

```json
{
  "status": "error",
  "message": "Terjadi kesalahan pada server"
}
```

**Penyebab**: Kesalahan internal server.

---

## 📋 **Field Reference**

### **Field Utama**

| Field          | Type    | Deskripsi            | Contoh                                |
| -------------- | ------- | -------------------- | ------------------------------------- |
| `id`           | Integer | ID unik laporan      | 1                                     |
| `nama_laporan` | String  | Nama/judul laporan   | "Laporan Kedisiplinan Januari 2024"   |
| `bulan`        | Integer | Bulan laporan (1-12) | 1                                     |
| `tahun`        | Integer | Tahun laporan        | 2024                                  |
| `keterangan`   | String  | Keterangan tambahan  | "Laporan kedisiplinan pegawai"        |
| `status`       | String  | Status laporan       | "diterima", "ditolak", "pending"      |
| `feedback`     | String  | Feedback dari admin  | "Laporan sudah sesuai"                |
| `file_path`    | String  | Path file di server  | "1749646786_8c856a5c67f38f92ba85.pdf" |
| `kategori`     | String  | Kategori laporan     | "Laporan Disiplin", "Laporan Apel"    |
| `link_drive`   | String  | URL link drive       | "https://drive.google.com/file/d/..." |

### **Field Pengirim**

| Field                   | Type    | Deskripsi             |
| ----------------------- | ------- | --------------------- |
| `pengirim.id`           | Integer | ID pengirim           |
| `pengirim.nama_lengkap` | String  | Nama lengkap pengirim |
| `pengirim.satker_id`    | Integer | ID satuan kerja       |

### **Field URL**

| Field      | Type   | Deskripsi                        | Contoh                                     |
| ---------- | ------ | -------------------------------- | ------------------------------------------ |
| `file_url` | String | URL untuk download file          | "http://localhost:8080/api/laporan/file/1" |
| `link_url` | String | URL untuk redirect ke link drive | "http://localhost:8080/api/laporan/link/1" |

### **Field Timestamp**

| Field        | Type     | Deskripsi                       |
| ------------ | -------- | ------------------------------- |
| `created_at` | DateTime | Waktu laporan dibuat            |
| `updated_at` | DateTime | Waktu laporan terakhir diupdate |

### **Field Pegawai**

| Field                | Type    | Deskripsi                          | Contoh                      |
| -------------------- | ------- | ---------------------------------- | --------------------------- |
| `id`                 | Integer | ID unik pegawai                    | 5225                        |
| `nama`               | String  | Nama lengkap pegawai               | "Dr. Drs. Khaeril R., M.H." |
| `nip`                | String  | NIP/NIM pegawai (gunakan 18 digit) | "195912311986031038"        |
| `jabatan`            | String  | Jabatan terakhir                   | "Ketua"                     |
| `pangkat`            | String  | Pangkat pegawai                    | "Pembina Utama"             |
| `golongan`           | String  | Golongan pegawai                   | "IV/e"                      |
| `status`             | String  | Status kepegawaian                 | "aktif"                     |
| `satker.id`          | Integer | ID satker terakhir                 | 12                          |
| `satker.nama`        | String  | Nama satker terakhir               | "Pengadilan Agama X"        |
| `satker.aktif_sejak` | Date    | Tanggal mulai penugasan            | "2023-01-01"                |
| `tracking_url`       | String  | Endpoint cepat untuk tracking      | "/api/pegawai/1959..."      |

### **Field Tracking Pegawai**

| Field                                                    | Type    | Deskripsi                                      |
| -------------------------------------------------------- | ------- | ---------------------------------------------- |
| `track_record.items[].bulan`                             | Integer | Bulan pelaporan (1-12)                         |
| `track_record.items[].tahun`                             | Integer | Tahun pelaporan                                |
| `track_record.items[].satker`                            | String  | Nama satker saat periode                       |
| `track_record.items[].pelanggaran.terlambat`             | Integer | Jumlah terlambat                               |
| `track_record.items[].pelanggaran.tidak_absen_masuk`     | Integer | Tidak absen masuk                              |
| `track_record.items[].pelanggaran.pulang_awal`           | Integer | Jumlah pulang awal                             |
| `track_record.items[].pelanggaran.tidak_absen_pulang`    | Integer | Tidak absen pulang                             |
| `track_record.items[].pelanggaran.keluar_tidak_izin`     | Integer | Keluar tanpa izin                              |
| `track_record.items[].pelanggaran.tidak_masuk_tanpa_ket` | Integer | Tidak masuk tanpa keterangan                   |
| `track_record.items[].pelanggaran.tidak_masuk_sakit`     | Integer | Tidak masuk (sakit)                            |
| `track_record.items[].pelanggaran.tidak_masuk_kerja`     | Integer | Tidak masuk (izin resmi)                       |
| `track_record.items[].bentuk_pembinaan`                  | String  | Bentuk pembinaan yang diterapkan               |
| `track_record.items[].keterangan`                        | String  | Catatan tambahan petugas                       |
| `track_record.pagination.limit`                          | Integer | Limit record per halaman                       |
| `ringkasan[].pelanggaran.*`                              | Integer | Jumlah pelanggaran per jenis per bulan         |
| `statistik.*`                                            | Integer | Total pelanggaran per jenis (akumulasi filter) |
| `tahun_tersedia[]`                                       | Integer | Tahun yang memiliki data tracking              |

---

## 📝 **Notes & Tips**

### **Keamanan**

- ✅ Selalu gunakan HTTPS di production
- ✅ Jangan expose API Key di frontend publik
- ✅ Rotasi API Key secara berkala
- ✅ Monitor penggunaan API

### **Performance**

- ✅ Gunakan pagination untuk data besar
- ✅ Cache response jika memungkinkan
- ✅ Gunakan filter untuk mengurangi data

### **Best Practices**

- ✅ Selalu handle error response
- ✅ Gunakan timeout yang reasonable
- ✅ Implement rate limiting jika diperlukan
- ✅ Log semua request untuk monitoring

### **Kategori yang Valid**

- `disiplin` → "Laporan Disiplin"
- `apel` → "Laporan Apel"

### **Identitas Pegawai**

- Gunakan parameter `nim` dengan nilai NIP 18 digit yang tercatat di tabel pegawai.
- Endpoint `/api/pegwai/...` tersedia sebagai alias bila terjadi salah ketik, namun gunakan `/api/pegawai/...` sebagai standard.
- Setiap record pegawai menyertakan `tracking_url` sehingga mudah berpindah ke data tracking kedisiplinan.

### **Status Laporan**

- `diterima` → Laporan sudah diterima admin
- `ditolak` → Laporan ditolak admin
- `pending` → Laporan menunggu review

---

## 🔗 **Quick Reference**

### **Endpoint File**

- Semua kategori: `/api/laporan/file/{id}`
- Berdasarkan kategori: `/api/laporan/{kategori}/file/{id}`

### **Endpoint Link**

- Semua kategori: `/api/laporan/link/{id}`
- Berdasarkan kategori: `/api/laporan/{kategori}/link/{id}`

### **Endpoint Data**

- Semua laporan: `/api/laporan`
- Berdasarkan kategori: `/api/laporan/{kategori}`
- Detail laporan: `/api/laporan/{id}`
- Arsip laporan: `/api/laporan/arsip`

### **Endpoint Pegawai**

- Daftar pegawai: `/api/pegawai`
- Tracking pegawai: `/api/pegawai/{nim}` (tersedia alias `/api/pegwai/{nim}`)

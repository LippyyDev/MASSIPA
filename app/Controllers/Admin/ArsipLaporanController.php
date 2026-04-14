<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\ArsipLaporanModel;
use App\Models\UserModel;
use App\Models\PegawaiModel;

class ArsipLaporanController extends BaseController
{
    protected $arsipModel;
    protected $userModel;
    protected $pegawaiModel;

    public function __construct()
    {
        $this->arsipModel = new ArsipLaporanModel();
        $this->userModel = new UserModel();
        $this->pegawaiModel = new PegawaiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function arsipLaporan()
    {
        $laporanFileModel = $this->arsipModel;
        $userModel = $this->userModel;
        $arsip = $laporanFileModel
            ->select('laporan_file.*, users.nama_lengkap')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->where('laporan_file.status', 'diterima')
            ->orderBy('laporan_file.created_at', 'DESC')
            ->findAll();
        $data = [
            'arsip' => $arsip
        ];
        return view('admin/ArsipLaporan', $data);
    }

    public function getFile($filename)
    {
        if (empty($filename)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Nama file tidak valid');
        }
        
        // Sanitasi filename untuk mencegah directory traversal
        $filename = basename(rawurldecode($filename));
        
        $file_path = WRITEPATH . 'uploads/laporan/' . $filename;
        if (!file_exists($file_path) || !is_file($file_path)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan');
        }
        
        $mime_type = mime_content_type($file_path);
        $file_size = filesize($file_path);
        
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: inline; filename="' . basename($filename) . '"');
        header('Cache-Control: public, max-age=86400');
        readfile($file_path);
        exit;
    }

    public function downloadArsipZip()
    {
        $ids_raw = $this->request->getVar('selected');
        // Pastikan selected adalah array
        if (empty($ids_raw)) {
            $ids = [];
        } elseif (is_array($ids_raw)) {
            $ids = $ids_raw;
        } else {
            // Jika bukan array, buat array dengan satu elemen
            $ids = [$ids_raw];
        }
        
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('msg', 'Tidak ada file yang dipilih!')->with('msg_type', 'danger');
        }
        
        $laporanFileModel = $this->arsipModel;
        $zipName = 'arsip_laporan_' . date('Ymd_His') . '.zip';
        $zipPath = WRITEPATH . 'uploads/' . $zipName;
        
        // Hapus file ZIP lama jika ada
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }
        
        $zip = new \ZipArchive();
        $openResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openResult !== TRUE) {
            return redirect()->back()->with('msg', 'Gagal membuat file ZIP! Error code: ' . $openResult)->with('msg_type', 'danger');
        }
        
        // Ambil data dengan join ke users untuk mendapatkan nama_lengkap
        $files = $laporanFileModel->select('laporan_file.*, users.nama_lengkap')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->whereIn('laporan_file.id', $ids)
            ->findAll();
        
        $filesAdded = 0;
        
        foreach ($files as $file) {
            // Tambahkan file fisik jika ada - gunakan addFromString untuk menghindari masalah path
            if (!empty($file['file_path'])) {
                $filePaths = explode(',', $file['file_path']);
                foreach ($filePaths as $filePath) {
                    $filePath = trim($filePath);
                    $fullPath = WRITEPATH . 'uploads/laporan/' . $filePath;
                    if (file_exists($fullPath) && is_readable($fullPath)) {
                        // Baca file content dan tambahkan ke ZIP (lebih reliable)
                        $fileContent = file_get_contents($fullPath);
                        if ($fileContent !== false) {
                            $zip->addFromString(basename($filePath), $fileContent);
                            $filesAdded++;
                        }
                    }
                }
            }
            
            // Tambahkan file TXT untuk link drive jika ada
            if (!empty($file['link_drive'])) {
                $linkContent = "Laporan: " . $file['nama_laporan'] . "\n";
                $linkContent .= "Bulan/Tahun: " . sprintf('%02d', $file['bulan']) . "/" . $file['tahun'] . "\n";
                $linkContent .= "Pengirim: " . ($file['nama_lengkap'] ?? 'Unknown') . "\n";
                $linkContent .= "Kategori: " . ($file['kategori'] ?? 'Laporan Disiplin') . "\n";
                $linkContent .= "Keterangan: " . ($file['keterangan'] ?? '-') . "\n";
                $linkContent .= "Link Drive: " . $file['link_drive'] . "\n";
                $linkContent .= "Tanggal Upload: " . date('d-m-Y H:i:s', strtotime($file['created_at'])) . "\n";
                $linkContent .= "\n" . str_repeat("=", 50) . "\n\n";
                
                $linkFileName = "Link_" . preg_replace('/[^a-zA-Z0-9\s]/', '', $file['nama_laporan']) . "_" . 
                               sprintf('%02d', $file['bulan']) . "_" . $file['tahun'] . ".txt";
                $linkFileName = str_replace(' ', '_', $linkFileName);
                
                $zip->addFromString($linkFileName, $linkContent);
                $filesAdded++;
            }
        }
        
        // Tutup ZIP
        $closeResult = $zip->close();
        
        // Pastikan file ZIP sudah ada dan valid
        if (!$closeResult || !file_exists($zipPath) || filesize($zipPath) == 0) {
            return redirect()->back()->with('msg', 'Gagal membuat file ZIP! Files added: ' . $filesAdded)->with('msg_type', 'danger');
        }
        
        // Simpan nama file ke session dan redirect ke URL dengan nama file .zip
        // Ini penting karena WebView Android menggunakan URLUtil.guessFileName() yang mengambil nama dari URL
        session()->set('pending_zip_download', $zipName);
        
        return redirect()->to(base_url('admin/arsip_laporan/serve_zip/' . $zipName));
    }
    
    /**
     * Serve file ZIP yang sudah dibuat
     * URL berakhiran .zip agar WebView Android bisa menebak nama file dengan benar
     */
    public function serveZip($filename = null)
    {
        // Validasi filename
        if (empty($filename) || !preg_match('/^arsip_laporan_\d{8}_\d{6}\.zip$/', $filename)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak valid');
        }
        
        $zipPath = WRITEPATH . 'uploads/' . $filename;
        
        if (!file_exists($zipPath)) {
            return redirect()->to(base_url('admin/arsip_laporan'))->with('msg', 'File ZIP tidak ditemukan atau sudah kedaluwarsa. Silakan coba lagi.')->with('msg_type', 'danger');
        }
        
        $fileSize = filesize($zipPath);

        // Bersihkan semua output buffer yang mungkin sudah ada (dari filter/view sebelumnya)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Kirim header binary download langsung — bypass CodeIgniter response pipeline
        // agar tidak ada karakter tambahan (CSP header, whitespace, dll) yang merusak binary ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Stream file langsung ke browser
        readfile($zipPath);

        // Hapus file ZIP sementara setelah selesai distream
        @unlink($zipPath);

        // Hapus session
        session()->remove('pending_zip_download');

        exit;
    }

    public function deleteArsipLaporan()
    {
        $ids = $this->request->getPost('selected') ?? [];
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('msg', 'Tidak ada file yang dipilih!')->with('msg_type', 'danger');
        }
        $laporanFileModel = $this->arsipModel;
        $files = $laporanFileModel->whereIn('id', $ids)->findAll();
        foreach ($files as $file) {
            if (!empty($file['file_path'])) {
                $filePaths = explode(',', $file['file_path']);
                foreach ($filePaths as $filePath) {
                    $fullPath = WRITEPATH . 'uploads/laporan/' . $filePath;
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
            $laporanFileModel->delete($file['id']);
        }
        return redirect()->back()->with('msg', 'File arsip berhasil dihapus!')->with('msg_type', 'success');
    }

    public function getArsipLaporanAjax()
    {
        $start = $this->request->getGet('start');
        $length = $this->request->getGet('length');
        $search = $this->request->getGet('search')['value'] ?? '';
        $draw = $this->request->getGet('draw');
        $model = $this->arsipModel;
        $query = $model->select('laporan_file.*, users.nama_lengkap')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->where('laporan_file.status', 'diterima');
        $pengirim = $this->request->getGet('pengirim');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $kategori = $this->request->getGet('kategori');
        if ($pengirim) {
            $query->where('users.nama_lengkap', $pengirim);
        }
        if ($bulan) {
            $query->where('laporan_file.bulan', $bulan);
        }
        if ($tahun) {
            $query->where('laporan_file.tahun', $tahun);
        }
        if ($kategori) {
            $query->where('laporan_file.kategori', $kategori);
        }
        if ($search) {
            $query = $query->groupStart()
                ->like('nama_laporan', $search)
                ->orLike('users.nama_lengkap', $search)
                ->orLike('keterangan', $search)
                ->groupEnd();
        }
        $total = $query->countAllResults(false);
        $filtered = $total;
        if ($length == -1) {
            $arsip = $query->orderBy('created_at', 'DESC')->findAll();
        } else {
            $arsip = $query->orderBy('created_at', 'DESC')->findAll($length, $start);
        }
        $data = [];
        $no = $start + 1;
        foreach ($arsip as $row) {
            $data[] = [
                '<input type="checkbox" name="selected[]" value="' . $row['id'] . '">',
                $no++, // No
                esc($row['nama_laporan']),
                sprintf('%02d', $row['bulan']) . '/' . $row['tahun'],
                '<span class="badge ' . (($row['kategori'] ?? 'Laporan Disiplin') == 'Laporan Apel' ? 'bg-danger' : 'bg-primary') . '">' . esc($row['kategori'] ?? 'Laporan Disiplin') . '</span>',
                esc($row['nama_lengkap'] ?? '-'),
                esc($row['keterangan'] ?: '-'),
                date('d-m-Y H:i', strtotime($row['created_at'])),
                '<div class="btn-action-group">' .
                (!empty($row['file_path']) ? '<a href="' . base_url('admin/arsip_laporan/getFile/' . urlencode(explode(',', $row['file_path'])[0])) . '" target="_blank" class="btn btn-info btn-sm" title="Lihat File"><i class="fas fa-eye"></i></a>' : '') .
                (!empty($row['link_drive']) ? '<a href="' . htmlspecialchars($row['link_drive']) . '" target="_blank" class="btn btn-success btn-sm" title="Link Drive"><i class="fas fa-link"></i></a>' : '') .
                '</div>'
            ];
        }
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    public function searchPegawaiAjax()
    {
        try {
            $pegawaiModel = $this->pegawaiModel;
            $search = $this->request->getGet('search') ?? '';
            $limit = 20;
            $pegawai = $pegawaiModel
                ->select('id, nama, nip, jabatan')
                ->where('status', 'aktif')
                ->groupStart()
                    ->like('nama', $search)
                    ->orLike('nip', $search)
                ->groupEnd()
                ->orderBy('nama', 'ASC')
                ->findAll($limit, 0);
            return $this->response->setJSON([
                'success' => true,
                'data' => $pegawai
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mencari pegawai: ' . $e->getMessage()
            ]);
        }
    }
} 
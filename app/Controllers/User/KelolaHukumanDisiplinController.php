<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\KelolaHukumanDisiplinUserModel;
use App\Models\NotifikasiModel;
use App\Models\PegawaiModel;
use App\Models\RiwayatMutasiModel;
use App\Libraries\PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class KelolaHukumanDisiplinController extends BaseController
{
    protected $hukumanModel;
    protected $pegawaiModel;
    protected $riwayatMutasiModel;

    public function __construct()
    {
        $this->hukumanModel = new KelolaHukumanDisiplinUserModel();
        $this->pegawaiModel = new PegawaiModel();
        $this->riwayatMutasiModel = new RiwayatMutasiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function index()
    {
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        // Ambil pegawai hanya dari satker user login (status aktif)
        $pegawai_ids = $this->riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_mulai <=', date('Y-m-d'))
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', date('Y-m-d'))
            ->groupEnd()
            ->findColumn('pegawai_id');
        $list_pegawai = [];
        if ($pegawai_ids) {
            $list_pegawai = $this->pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif')->orderBy('nama', 'ASC')->findAll();
        }
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        // Ambil user_id semua user di satker ini
        $user_ids_satker = $userModel->where('satker_id', $satker_id)->findColumn('id');
        // Ambil semua hukuman disiplin yang:
        // 1. Diajukan oleh user satker ini
        // 2. Atau pegawai yang pada tanggal mulai hukuman, mutasinya masih di satker ini
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $riwayatMutasiModel = $this->riwayatMutasiModel;
        $list_hukuman = $hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
            ->findAll();
        $filtered_hukuman = array_filter($list_hukuman, function($row) use ($user_ids_satker, $satker_id, $riwayatMutasiModel) {
            // 1. Diajukan oleh user satker ini
            if (in_array($row['user_id'], $user_ids_satker)) return true;
            // 2. Atau pegawai yang pada tanggal mulai hukuman, mutasinya masih di satker ini
            $mutasi = $riwayatMutasiModel
                ->where('pegawai_id', $row['pegawai_id'])
                ->where('tanggal_mulai <=', $row['tanggal_mulai'])
                ->groupStart()
                    ->where('tanggal_selesai IS NULL')
                    ->orWhere('tanggal_selesai >', $row['tanggal_mulai'])
                ->groupEnd()
                ->orderBy('tanggal_mulai', 'DESC')
                ->first();
            if ($mutasi && $mutasi['satker_id'] == $satker_id) return true;
            return false;
        });
        $data = [
            'list_pegawai' => $list_pegawai,
            'list_hukuman' => $filtered_hukuman,
            'active' => 'user/kelola_hukuman_disiplin',
        ];
        return view('user/KelolaHukumanDisiplin', $data);
    }

    public function addHukumanDisiplin()
    {
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $pegawai_id = $this->request->getPost('pegawai_id');
        // Validasi pegawai hanya dari satker user login
        $pegawai_ids = $this->riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_mulai <=', date('Y-m-d'))
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', date('Y-m-d'))
            ->groupEnd()
            ->findColumn('pegawai_id');
        if (!$pegawai_id || !in_array($pegawai_id, $pegawai_ids)) {
            $session->setFlashdata('msg', 'Pegawai tidak valid!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
        }
        $pegawai = $this->pegawaiModel->find($pegawai_id);
        if (!$pegawai) {
            $session->setFlashdata('msg', 'Pegawai tidak ditemukan!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
        }
        $data = [
            'pegawai_id' => $pegawai_id,
            'user_id' => $session->get('user_id'),
            'jabatan' => $pegawai['jabatan'],
            'no_sk' => $this->request->getPost('no_sk'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_berakhir' => $this->request->getPost('tanggal_berakhir'),
            'hukuman_dijatuhkan' => $this->request->getPost('hukuman_dijatuhkan'),
            'peraturan_dilanggar' => $this->request->getPost('peraturan_dilanggar'),
            'keterangan' => $this->request->getPost('keterangan'),
            'status' => 'pending',
        ];
        $file = $this->request->getFile('file_sk');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validasi keamanan: hanya PDF yang diizinkan untuk berkas SK
            $allowedMimes = ['application/pdf'];
            $allowedExts  = ['pdf'];
            $mime = $file->getMimeType();
            $ext  = strtolower($file->guessExtension() ?: $file->getClientExtension());

            // Cek MIME type (dari konten file, bukan dari klien)
            if (!in_array($mime, $allowedMimes) || !in_array($ext, $allowedExts)) {
                $session->setFlashdata('msg', 'Tipe file tidak diizinkan! Hanya file PDF yang diperbolehkan untuk berkas SK.');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
            }

            // Cek ukuran file max 1MB
            if ($file->getSize() > 1 * 1024 * 1024) {
                $session->setFlashdata('msg', 'Ukuran file terlalu besar! Maksimal 1MB.');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
            }

            $newName = 'sk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move(WRITEPATH . 'uploads/sk/', $newName);
            $data['file_sk'] = $newName;
        }
        $this->hukumanModel->insert($data);


        // Kirim notifikasi ke admin
        $notifikasiAdminModel = new \App\Models\Admin\NotifikasiAdminModel();
        // Ambil semua admin (misal user dengan role admin)
        $adminList = (new \App\Models\UserModel())->where('role', 'admin')->findAll();
        foreach ($adminList as $admin) {
            $judul = 'Pengajuan Hukuman Disiplin Baru';
            $pesan = 'Ada pengajuan hukuman disiplin baru dari' . ($user['nama_lengkap'] ?? '-');
            $jenis = 'laporan';
            $referensi_id = $this->hukumanModel->getInsertID();
            $notifikasiAdminModel->insertNotifikasi($admin['id'], $judul, $pesan, $jenis, $referensi_id);
        }
        $session->setFlashdata('msg', 'Data hukuman disiplin berhasil diajukan, menunggu persetujuan admin.');
        $session->setFlashdata('msg_type', 'success');
        return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
    }

    public function delete($id)
    {
        $session = session();
        $hukuman = $this->hukumanModel->find($id);
        // Hanya boleh hapus jika status pending atau rejected dan user yang mengajukan
        if ($hukuman && ($hukuman['status'] == 'pending' || $hukuman['status'] == 'rejected') && $hukuman['user_id'] == $session->get('user_id')) {
            // Hapus file jika ada
            if (!empty($hukuman['file_sk'])) {
                $file_path = WRITEPATH . 'uploads/sk/' . $hukuman['file_sk'];
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            $this->hukumanModel->delete($id);
            $session->setFlashdata('msg', 'Data hukuman disiplin berhasil dihapus.');
            $session->setFlashdata('msg_type', 'success');
        } else {
            $session->setFlashdata('msg', 'Tidak diizinkan menghapus data ini.');
            $session->setFlashdata('msg_type', 'danger');
        }
        return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
    }

    public function getFile($filename)
    {
        // Bersihkan output buffer untuk web view compatibility
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Decode dan bersihkan filename (prevent directory traversal)
        $filename = basename(rawurldecode($filename));

        $file_path = WRITEPATH . 'uploads/sk/' . $filename;
        if (!file_exists($file_path)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan');
        }

        $mime_type   = mime_content_type($file_path);
        $file_size   = filesize($file_path);
        $fileContent = file_get_contents($file_path);

        // Cari record hukuman untuk mendapatkan nama file yang lebih deskriptif
        $hukuman = $this->hukumanModel->where('file_sk', $filename)->first();
        $displayFilename = $filename; // default
        if ($hukuman && !empty($hukuman['no_sk'])) {
            $fileInfo      = pathinfo($file_path);
            $fileExtension = $fileInfo['extension'] ?? '';
            $displayFilename = preg_replace('/[<>:"|?*\x00-\x1f]/', '_', $hukuman['no_sk']);
            $displayFilename = str_replace(['\\', '/'], '_', $displayFilename);
            if ($fileExtension) {
                $displayFilename .= '.' . $fileExtension;
            }
        }

        $encodedFilename    = rawurlencode($displayFilename);
        $contentDisposition = 'inline; filename="' . addslashes($displayFilename) . '"; filename*=UTF-8\'\'' . $encodedFilename;

        return $this->response
            ->setContentType($mime_type)
            ->setHeader('Content-Length', (string) $file_size)
            ->setHeader('Content-Disposition', $contentDisposition)
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setHeader('Accept-Ranges', 'bytes')
            ->setBody($fileContent);
    }

    public function exportHukumanDisiplinPdfUser()
{
    // PENTING: Bersihkan semua output buffer di awal
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Gunakan getVar() untuk kompatibilitas GET dan POST (web view compatibility)
    $isPublic = $this->request->getVar('public');
    $session = session();
    $user = (new \App\Models\UserModel())->find($session->get("user_id"));
    $satker_id = $user['satker_id'];
    
    $satkerModel = new \App\Models\SatkerModel();
    $satker = $satkerModel->find($satker_id);
    $satker_nama = (is_array($satker) && isset($satker['nama'])) ? $satker['nama'] : '-';
    
    $hukumanModel = $this->hukumanModel;
    $pegawaiModel = $this->pegawaiModel;
    $riwayatMutasiModel = $this->riwayatMutasiModel;
    
    // Get user IDs in this satker
    $userModel = new \App\Models\UserModel();
    $user_ids_satker = $userModel->where('satker_id', $satker_id)->findColumn('id');
    
    // Get all hukuman disiplin data
    $list = $hukumanModel
        ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
        ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
        ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
        ->findAll();
    
    // Filter data based on satker and status approved only (sama seperti Word)
    $filtered = array_filter($list, function($row) use ($user_ids_satker, $satker_id, $riwayatMutasiModel) {
        // Hanya data dengan status approved
        if ($row['status'] !== 'approved') return false;
        
        // 1. Diajukan oleh user satker ini
        if (in_array($row['user_id'], $user_ids_satker)) return true;
        // 2. Atau pegawai yang pada tanggal mulai hukuman, mutasinya masih di satker ini
        $mutasi = $riwayatMutasiModel
            ->where('pegawai_id', $row['pegawai_id'])
            ->where('tanggal_mulai <=', $row['tanggal_mulai'])
            ->groupStart()
                ->where('tanggal_selesai IS NULL')
                ->orWhere('tanggal_selesai >', $row['tanggal_mulai'])
            ->groupEnd()
            ->orderBy('tanggal_mulai', 'DESC')
            ->first();
        if ($mutasi && $mutasi['satker_id'] == $satker_id) return true;
        return false;
    });
    
    $list = $filtered;
    
    try {
        // Inisialisasi PDF
        $pdf = new PDF();
        $pdf->AddPage('L');
        $pdf->drawHeaderHukumanDisiplinUser($satker_nama);
        
        if ($isPublic) {
            $pdf->drawComplexHeaderHukumanDisiplinPublic();
            $row_number = 1;
            foreach ($list as $row) {
                $row_height = 12;
                $pdf->checkCustomPageBreak($row_height);
                $pdf->drawTableRowHukumanDisiplinPublic([
                    $row_number++,
                    sensor_nama_publik($row['nama'] ?? '-'),
                    $row['jabatan'] ?? '-',
                    $row['hukuman_dijatuhkan'] ?? '-',
                    $row['peraturan_dilanggar'] ?? '-',
                    $row['keterangan'] ?? '-'
                ], $row_height);
            }
        } else {
            $pdf->drawComplexHeaderHukumanDisiplin();
            $row_number = 1;
            foreach ($list as $row) {
                $row_height = 12;
                $pdf->checkCustomPageBreak($row_height);
                $tanggal_periode = '';
                if (!empty($row['tanggal_mulai']) && !empty($row['tanggal_berakhir'])) {
                    $tanggal_periode = date('d-m-Y', strtotime($row['tanggal_mulai'])) . ' s/d ' . date('d-m-Y', strtotime($row['tanggal_berakhir']));
                }
                $pdf->drawTableRowHukumanDisiplin([
                    $row_number++,
                    $row['nama'] ?? '-',
                    $row['jabatan'] ?? '-',
                    $row['no_sk'] ?? '-',
                    $tanggal_periode,
                    $row['hukuman_dijatuhkan'] ?? '-',
                    $row['peraturan_dilanggar'] ?? '-',
                    $row['keterangan'] ?? '-'
                ], $row_height);
            }
        }
        
        $filename = "Hukuman_Disiplin_" . str_replace(' ', '_', $satker_nama) . "_" . date('Y-m-d') . ".pdf";
        
        // PERBAIKAN UTAMA: Untuk WebView Android compatibility, gunakan Output('S') lalu kirim via response object
        $pdfContent = $pdf->Output($filename, 'S');
        
        // Encode filename untuk web view compatibility
        $encodedFilename = rawurlencode($filename);
        
        // Set headers dengan sendHeaders() untuk memastikan header dikirim dengan benar
        $this->response->setContentType('application/pdf');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
        $this->response->setHeader('Content-Length', (string) strlen($pdfContent));
        $this->response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
        $this->response->setHeader('Pragma', 'public');
        $this->response->sendHeaders();
        
        // Output langsung body
        echo $pdfContent;
        exit;
        
    } catch (Exception $e) {
        // Handle error
        log_message('error', 'PDF Export Error (User): ' . $e->getMessage());
        
        // Redirect dengan error message
        $session->setFlashdata('msg', 'Gagal export PDF: ' . $e->getMessage());
        $session->setFlashdata('msg_type', 'danger');
        return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
    }
}

    public function exportHukumanDisiplinWordUser()
    {
        try {
            // Load helper
            helper('app');
            
            // Clean output buffer
            error_reporting(0);
            ini_set('display_errors', 0);
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            $session = session();
            $user = (new \App\Models\UserModel())->find($session->get("user_id"));
            $satker_id = $user['satker_id'];
            $satkerModel = new \App\Models\SatkerModel();
            $satker = $satkerModel->find($satker_id);
            $satker_nama = $satker ? $satker['nama'] : 'Unknown';
            
            // Gunakan getVar() untuk kompatibilitas GET dan POST (web view compatibility)
            $isPublic = $this->request->getVar('public') == '1';
            
            // Get user IDs in this satker
            $userModel = new \App\Models\UserModel();
            $user_ids_satker = $userModel->where('satker_id', $satker_id)->findColumn('id');
            
            // Get all hukuman disiplin data
            $list = $this->hukumanModel
                ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
                ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
                ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
                ->findAll();
            
            // Filter data based on satker and status approved only
            $filtered = array_filter($list, function($row) use ($user_ids_satker, $satker_id) {
                // Hanya data dengan status approved
                if ($row['status'] !== 'approved') return false;
                
                // 1. Diajukan oleh user satker ini
                if (in_array($row['user_id'], $user_ids_satker)) return true;
                // 2. Atau pegawai yang pada tanggal mulai hukuman, mutasinya masih di satker ini
                $mutasi = $this->riwayatMutasiModel
                    ->where('pegawai_id', $row['pegawai_id'])
                    ->where('tanggal_mulai <=', $row['tanggal_mulai'])
                    ->groupStart()
                        ->where('tanggal_selesai IS NULL')
                        ->orWhere('tanggal_selesai >', $row['tanggal_mulai'])
                    ->groupEnd()
                    ->orderBy('tanggal_mulai', 'DESC')
                    ->first();
                if ($mutasi && $mutasi['satker_id'] == $satker_id) return true;
                return false;
            });
            
            // Apply public filter if needed
            if ($isPublic) {
                $filtered = array_map(function($row) {
                    $row['nama'] = sensor_nama_publik($row['nama']);
                    return $row;
                }, $filtered);
            }
            
            // Initialize PHPWord
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(10);
            
            // Add section
            $section = $phpWord->addSection([
                'orientation' => 'landscape',
                'marginLeft' => 850,
                'marginRight' => 850,
                'marginTop' => 850,
                'marginBottom' => 100
            ]);
            
            // Header title
            $section->addText('DAFTAR HUKUMAN DISIPLIN HAKIM', [
                'bold' => true,
                'size' => 14,
                'name' => 'Times New Roman'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'spaceAfter' => 200
            ]);
            
            // Add satker info at left side
            $section->addText('SATKER: ' . strtoupper($satker_nama), [
                'bold' => true,
                'size' => 9,
                'name' => 'Times New Roman'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
                'spaceAfter' => 100
            ]);
            
            // Add table
            $table = $section->addTable([
                'borderSize' => 1,
                'borderColor' => '000000',
                'cellMargin' => 80,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED
            ]);
            
            // Table header - Total width: 15,134 twips (A4 landscape minus margins)
            $table->addRow();
            $table->addCell(800, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('No', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $table->addCell(2200, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Nama Pegawai', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $table->addCell(2000, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Jabatan', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            
            if (!$isPublic) {
                $table->addCell(1800, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('No SK', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(2000, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Periode', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(2200, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Hukuman Dijatuhkan', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(2500, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Peraturan Dilanggar', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(1634, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Keterangan', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            } else {
                // Mode publik - redistribusi lebar kolom (Total: 15,134 twips)
                $table->addCell(3000, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Hukuman Dijatuhkan', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(3500, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Peraturan Dilanggar', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(3634, ['valign' => 'center', 'bgColor' => 'E0E0E0'])->addText('Keterangan', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            }
            
            // Table data
            $row_number = 1;
            foreach ($filtered as $row) {
                $table->addRow();
                $table->addCell(800, ['valign' => 'center'])->addText($row_number++, ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(2200, ['valign' => 'center'])->addText($row['nama'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                $table->addCell(2000, ['valign' => 'center'])->addText($row['jabatan'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                
                if (!$isPublic) {
                    $table->addCell(1800, ['valign' => 'center'])->addText($row['no_sk'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    
                    $tanggal_periode = '';
                    if (!empty($row['tanggal_mulai']) && !empty($row['tanggal_berakhir'])) {
                        $tanggal_periode = date('d-m-Y', strtotime($row['tanggal_mulai'])) . ' s/d ' . date('d-m-Y', strtotime($row['tanggal_berakhir']));
                    }
                    $table->addCell(2000, ['valign' => 'center'])->addText($tanggal_periode, ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    
                    $table->addCell(2200, ['valign' => 'center'])->addText($row['hukuman_dijatuhkan'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    $table->addCell(2500, ['valign' => 'center'])->addText($row['peraturan_dilanggar'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    $table->addCell(1634, ['valign' => 'center'])->addText($row['keterangan'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                } else {
                    // Mode publik - redistribusi lebar kolom
                    $table->addCell(3000, ['valign' => 'center'])->addText($row['hukuman_dijatuhkan'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    $table->addCell(3500, ['valign' => 'center'])->addText($row['peraturan_dilanggar'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                    $table->addCell(3634, ['valign' => 'center'])->addText($row['keterangan'] ?? '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
                }
            }
            
            // Footer
            $section->addTextBreak(1);
            $section->addText('Dicetak pada: ' . date('d-m-Y H:i:s'), [
                'size' => 9,
                'name' => 'Times New Roman'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT
            ]);
            
            // Footer kosong di bagian bawah
            $section->addTextBreak(3);
            $section->addText('', [
                'size' => 9,
                'name' => 'Times New Roman'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
            
            // Generate filename
            $filename = "Hukuman_Disiplin_" . str_replace(' ', '_', $satker_nama) . "_" . date('Y-m-d') . ".docx";
            
            // Encode filename untuk web view compatibility
            $encodedFilename = rawurlencode($filename);
            
            // Set headers untuk WebView Android compatibility
            $this->response->setContentType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedFilename);
            $this->response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
            $this->response->setHeader('Pragma', 'public');
            $this->response->sendHeaders();
            
            // Output langsung ke php://output untuk web view compatibility
            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
            exit;
            
        } catch (Exception $e) {
            // Handle error
            log_message('error', 'Word Export Error (User): ' . $e->getMessage());
            
            // Redirect dengan error message
            $session->setFlashdata('msg', 'Gagal export Word: ' . $e->getMessage());
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
        }
    }

    public function searchPegawaiAjax()
    {
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $query = $this->request->getPost('search');
        $pegawai_ids = $this->riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_mulai <=', date('Y-m-d'))
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', date('Y-m-d'))
            ->groupEnd()
            ->findColumn('pegawai_id');
        $result = [];
        if ($pegawai_ids && $query) {
            $result = $this->pegawaiModel
                ->select('id, nama, nip, jabatan')
                ->whereIn('id', $pegawai_ids)
                ->groupStart()
                ->like('nama', $query)
                ->orLike('nip', $query)
                ->groupEnd()
                ->where('status', 'aktif')
                ->orderBy('nama', 'ASC')
                ->findAll(20);
        }
        return $this->response->setJSON([
            'success' => true,
            'data' => $result
        ]);
    }

    public function getHukumanDisiplinAjaxDataTables()
    {
        $session = session();
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $request = service('request');
        $draw    = $request->getPost('draw');
        $start   = $request->getPost('start');
        $length  = $request->getPost('length');
        $search  = $request->getPost('search')['value'] ?? '';
        $order   = $request->getPost('order');
        $columns = $request->getPost('columns');
        $user_ids_satker = $userModel->where('satker_id', $satker_id)->findColumn('id');
        $riwayatMutasiModel = new \App\Models\RiwayatMutasiModel();
        $base = $this->hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left');
        if ($search) {
            $base = $base->groupStart()
                ->like('pegawai.nama', $search)
                ->orLike('pegawai.jabatan', $search)
                ->orLike('no_sk', $search)
                ->orLike('hukuman_dijatuhkan', $search)
                ->orLike('peraturan_dilanggar', $search)
                ->orLike('keterangan', $search)
                ->groupEnd();
        }
        $all = $base->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')->findAll();
        $filtered = array_filter($all, function($row) use ($user_ids_satker, $satker_id, $riwayatMutasiModel) {
            if (in_array($row['user_id'], $user_ids_satker)) return true;
            $mutasi = $riwayatMutasiModel
                ->where('pegawai_id', $row['pegawai_id'])
                ->where('tanggal_mulai <=', $row['tanggal_mulai'])
                ->groupStart()
                    ->where('tanggal_selesai IS NULL')
                    ->orWhere('tanggal_selesai >', $row['tanggal_mulai'])
                ->groupEnd()
                ->orderBy('tanggal_mulai', 'DESC')
                ->first();
            if ($mutasi && $mutasi['satker_id'] == $satker_id) return true;
            return false;
        });
        $total = count($filtered);
        $data = array_slice(array_values($filtered), $start, $length);
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function approveHukumanDisiplinUser($id)
    {
        $hukuman = $this->hukumanModel->find($id);
        if (!$hukuman) {
            session()->setFlashdata('msg', 'Data tidak ditemukan!');
            session()->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
        }

        // Ambil nama pegawai
        $nama_pegawai = $hukuman['nama'] ?? null;
        if (! $nama_pegawai && ! empty($hukuman['pegawai_id'])) {
            $pegawai      = $this->pegawaiModel->find($hukuman['pegawai_id']);
            $nama_pegawai = $pegawai['nama'] ?? '-';
        }

        $this->hukumanModel->update($id, ['status' => 'approved']);

        // Kirim notifikasi + queue email ke user pengaju (via createNotification → queueEmailNotification)
        if (! empty($hukuman['user_id'])) {
            $notifikasiModel = new NotifikasiModel();
            $notifikasiModel->createNotification(
                $hukuman['user_id'],
                'Pengajuan Hukuman Disiplin Diterima',
                'Pengajuan hukuman disiplin untuk ' . $nama_pegawai . ' telah diterima.',
                'status',
                $id
            );
        }

        session()->setFlashdata('msg', 'Data berhasil di-approve dan notifikasi dikirim ke user.');
        session()->setFlashdata('msg_type', 'success');
        return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
    }

    public function rejectHukumanDisiplinUser($id)
    {
        $hukuman = $this->hukumanModel->find($id);
        if (!$hukuman) {
            session()->setFlashdata('msg', 'Data tidak ditemukan!');
            session()->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
        }

        // Ambil nama pegawai
        $nama_pegawai = $hukuman['nama'] ?? null;
        if (! $nama_pegawai && ! empty($hukuman['pegawai_id'])) {
            $pegawai      = $this->pegawaiModel->find($hukuman['pegawai_id']);
            $nama_pegawai = $pegawai['nama'] ?? '-';
        }

        $this->hukumanModel->update($id, ['status' => 'rejected']);

        // Kirim notifikasi + queue email ke user pengaju (via createNotification → queueEmailNotification)
        if (! empty($hukuman['user_id'])) {
            $notifikasiModel = new NotifikasiModel();
            $notifikasiModel->createNotification(
                $hukuman['user_id'],
                'Pengajuan Hukuman Disiplin Ditolak',
                'Pengajuan hukuman disiplin untuk ' . $nama_pegawai . ' telah ditolak.',
                'status',
                $id
            );
        }

        session()->setFlashdata('msg', 'Data berhasil di-reject dan notifikasi dikirim ke user.');
        session()->setFlashdata('msg_type', 'success');
        return redirect()->to(base_url('user/kelola_hukuman_disiplin'));
    }
} 
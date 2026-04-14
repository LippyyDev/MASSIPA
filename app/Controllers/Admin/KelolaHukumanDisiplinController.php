<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\KelolaHukumanDisiplinAdminModel;
use App\Models\PegawaiModel;
use App\Libraries\PDF;
use App\Models\NotifikasiModel;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class KelolaHukumanDisiplinController extends BaseController
{
    protected $hukumanModel;
    protected $pegawaiModel;

    public function __construct()
    {
        $this->hukumanModel = new KelolaHukumanDisiplinAdminModel();
        $this->pegawaiModel = new PegawaiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function kelolaHukumanDisiplin()
    {
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $session = session();
        $current_user_id = $session->get('user_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        // Ambil semua data untuk pemisahan di view
        $data['list_hukuman'] = $hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
            ->findAll($perPage, $offset);
            
        $total_hukuman = $hukumanModel->countAllResults();
        $data['total_pages'] = ceil($total_hukuman / $perPage);
        $data['current_page'] = $page;
        $data['list_pegawai'] = $pegawaiModel
            ->where('status', 'aktif')
            ->orderBy('nama', 'ASC')
            ->findAll(100, 0);
        $data['total_pegawai'] = $pegawaiModel->where('status', 'aktif')->countAllResults();
        $data['active'] = 'admin/kelola_hukuman_disiplin';
        return view('admin/KelolaHukumanDisiplin', $data);
    }

    public function addHukumanDisiplin()
    {
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $session = session();
        $pegawai_id = $this->request->getPost('pegawai_id');
        $pegawai = $pegawaiModel->find($pegawai_id);
        if (!$pegawai) {
            $session->setFlashdata('msg', 'Pegawai tidak ditemukan!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
        }
        // Cek apakah yang input admin atau user
        $user = (new \App\Models\UserModel())->find($session->get('user_id'));
        $is_admin = ($user && $user['role'] === 'admin');
        $data = [
            'pegawai_id' => $pegawai_id,
            'jabatan' => $pegawai['jabatan'],
            'no_sk' => $this->request->getPost('no_sk'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_berakhir' => $this->request->getPost('tanggal_berakhir'),
            'hukuman_dijatuhkan' => $this->request->getPost('hukuman_dijatuhkan'),
            'peraturan_dilanggar' => $this->request->getPost('peraturan_dilanggar'),
            'keterangan' => $this->request->getPost('keterangan'),
            'status' => $is_admin ? 'approved' : 'pending',
            'user_id' => $session->get('user_id'),
        ];
        $file = $this->request->getFile('file_sk');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getClientExtension();
            $newName = 'sk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move(FCPATH . 'writable/uploads/', $newName);
            $data['file_sk'] = $newName;
        }
        $hukumanModel->insert($data);
        $session->setFlashdata('msg', $is_admin ? 'Data hukuman disiplin berhasil ditambah!' : 'Data hukuman disiplin berhasil diajukan, menunggu persetujuan admin.');
        $session->setFlashdata('msg_type', 'success');
        return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
    }

    public function editHukumanDisiplin($id)
    {
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $data['hukuman'] = $hukumanModel->find($id);
        $data['list_pegawai'] = $pegawaiModel->where('status', 'aktif')->orderBy('nama', 'ASC')->findAll();
        $data['active'] = 'admin/kelola_hukuman_disiplin';
        if (!$data['hukuman']) {
            session()->setFlashdata('msg', 'Data tidak ditemukan!');
            session()->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
        }
        return view('admin/edit_hukuman_disiplin', $data);
    }

    public function updateHukumanDisiplin()
    {
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $session = session();
        $id = $this->request->getPost('id');
        $pegawai_id = $this->request->getPost('pegawai_id');
        $pegawai = $pegawaiModel->find($pegawai_id);
        
        if (!$pegawai) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Pegawai tidak ditemukan!'
                ]);
            } else {
                $session->setFlashdata('msg', 'Pegawai tidak ditemukan!');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
            }
        }
        
        $data = [
            'pegawai_id' => $pegawai_id,
            'jabatan' => $pegawai['jabatan'],
            'no_sk' => $this->request->getPost('no_sk'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tanggal_berakhir' => $this->request->getPost('tanggal_berakhir'),
            'hukuman_dijatuhkan' => $this->request->getPost('hukuman_dijatuhkan'),
            'peraturan_dilanggar' => $this->request->getPost('peraturan_dilanggar'),
            'keterangan' => $this->request->getPost('keterangan'),
        ];
        
        $file = $this->request->getFile('file_sk');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = $file->getClientExtension();
            $newName = 'sk_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $file->move(FCPATH . 'writable/uploads/', $newName);
            $data['file_sk'] = $newName;
        }
        
        try {
            $hukumanModel->update($id, $data);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Data hukuman disiplin berhasil diupdate!'
                ]);
            } else {
                $session->setFlashdata('msg', 'Data hukuman disiplin berhasil diupdate!');
                $session->setFlashdata('msg_type', 'success');
                return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal mengupdate data: ' . $e->getMessage()
                ]);
            } else {
                $session->setFlashdata('msg', 'Gagal mengupdate data!');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
            }
        }
    }

    public function deleteHukumanDisiplin($id)
    {
        $hukumanModel = $this->hukumanModel;
        $hukuman = $hukumanModel->find($id);
        if ($hukuman) {
            if (!empty($hukuman['file_sk'])) {
                $file_path = FCPATH . 'writable/uploads/' . $hukuman['file_sk'];
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            $hukumanModel->delete($id);
            session()->setFlashdata('msg', 'Data hukuman disiplin berhasil dihapus!');
            session()->setFlashdata('msg_type', 'success');
        } else {
            session()->setFlashdata('msg', 'Data tidak ditemukan!');
            session()->setFlashdata('msg_type', 'danger');
        }
        return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
    }

    public function getFile($filename)
    {
        // Bersihkan output buffer untuk web view compatibility
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Decode dan bersihkan filename (prevent directory traversal)
        $filename = basename(rawurldecode($filename));

        $file_path = FCPATH . 'writable/uploads/' . $filename;
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

    public function exportHukumanDisiplinPdf()
{
    // PENTING: Pastikan tidak ada output sebelum PDF
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Gunakan getVar() untuk kompatibilitas GET dan POST (web view compatibility)
    $isPublic = $this->request->getVar('public');
    $hukumanModel = $this->hukumanModel;
    $pegawaiModel = $this->pegawaiModel;
    $session = session();
    $userModel = new \App\Models\UserModel();
    
    $list = $hukumanModel
        ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
        ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
        ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
        ->findAll();
    
    // Filter: hanya status approved atau data buatan admin
    $filtered = array_filter($list, function($row) use ($userModel) {
        if ($row['status'] === 'approved') return true;
        if (!empty($row['user_id'])) {
            $user = $userModel->find($row['user_id']);
            if ($user && isset($user['role']) && $user['role'] === 'admin') return true;
        }
        return false;
    });
    
    try {
        // Inisialisasi PDF
        $pdf = new PDF();
        $pdf->AddPage('L');
        $pdf->drawHeaderHukumanDisiplin();
        
        if ($isPublic) {
            $pdf->drawComplexHeaderHukumanDisiplinPublic();
            $row_number = 1;
            foreach ($filtered as $row) {
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
            foreach ($filtered as $row) {
                $row_height = 12;
                $pdf->checkCustomPageBreak($row_height);
                $tanggal_periode = date('d-m-Y', strtotime($row['tanggal_mulai'])) . ' s/d ' . date('d-m-Y', strtotime($row['tanggal_berakhir']));
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
        
        $filename = "Hukuman_Disiplin_" . date('Y-m-d') . ".pdf";
        
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
        log_message('error', 'PDF Export Error: ' . $e->getMessage());
        
        // Redirect dengan error message
        session()->setFlashdata('msg', 'Gagal export PDF: ' . $e->getMessage());
        session()->setFlashdata('msg_type', 'danger');
        return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
    }
}

    public function exportHukumanDisiplinWord()
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
            
            $hukumanModel = $this->hukumanModel;
            // Gunakan getVar() untuk kompatibilitas GET dan POST (web view compatibility)
            $isPublic = $this->request->getVar('public') == '1';
            
            // Get all hukuman disiplin data
            $list = $hukumanModel
                ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
                ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
                ->where('hukuman_disiplin.status !=', 'pending')
                ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
                ->findAll();
            
            // Filter data based on public setting
            $filtered = $list;
            if ($isPublic) {
                $filtered = array_map(function($row) {
                    $row['nama'] = sensor_nama_publik($row['nama']);
                    return $row;
                }, $list);
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
            $filename = "Hukuman_Disiplin_" . date('Y-m-d') . ".docx";
            
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
            log_message('error', 'Word Export Error: ' . $e->getMessage());
            
            // Redirect dengan error message
            session()->setFlashdata('msg', 'Gagal export Word: ' . $e->getMessage());
            session()->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
        }
    }

    public function getHukumanDisiplinAjax()
    {
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $session = session();
        $current_user_id = $session->get('user_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('perPage') ?? 10);
        $offset = ($page - 1) * $perPage;
        $list = $hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->where('(hukuman_disiplin.status != "pending" OR (hukuman_disiplin.status = "pending" AND hukuman_disiplin.user_id = ' . $current_user_id . '))')
            ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
            ->findAll($perPage, $offset);
        $total = $hukumanModel->countAllResults();
        return $this->response->setJSON([
            'success' => true,
            'data' => $list,
            'total' => $total,
            'perPage' => $perPage,
            'page' => $page,
            'totalPages' => ceil($total / $perPage)
        ]);
    }

    public function getHukumanDisiplinDetailAjax($id)
    {
        $hukumanModel = $this->hukumanModel;
        $pegawaiModel = $this->pegawaiModel;
        $row = $hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->where('hukuman_disiplin.id', $id)
            ->first();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        $list_pegawai = $pegawaiModel->where('status', 'aktif')->orderBy('nama', 'ASC')->findAll(100, 0);
        return $this->response->setJSON([
            'success' => true,
            'data' => $row,
            'list_pegawai' => $list_pegawai
        ]);
    }

    public function approveHukumanDisiplin($id)
    {
        $hukuman = $this->hukumanModel->find($id);
        if (!$hukuman) {
            session()->setFlashdata('msg', 'Data tidak ditemukan!');
            session()->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
        }
        $this->hukumanModel->update($id, ['status' => 'approved']);
        // Kirim notifikasi ke user pengaju
        if (!empty($hukuman['user_id'])) {
            $notifikasiModel = new NotifikasiModel();
            $judul = 'Pengajuan Hukuman Disiplin Diterima';
            $nama_pegawai = $hukuman['nama'] ?? null;
            if (!$nama_pegawai && !empty($hukuman['pegawai_id'])) {
                $pegawaiModel = new \App\Models\PegawaiModel();
                $pegawai = $pegawaiModel->find($hukuman['pegawai_id']);
                $nama_pegawai = $pegawai['nama'] ?? '-';
            }
            $pesan = 'Pengajuan hukuman disiplin untuk ' . $nama_pegawai . ' telah disetujui admin.';
            $notifikasiModel->insert([
                'user_id' => $hukuman['user_id'],
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis' => 'status',
                'referensi_id' => $id,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        session()->setFlashdata('msg', 'Data berhasil di-approve dan notifikasi dikirim ke user.');
        session()->setFlashdata('msg_type', 'success');

        // Notifikasi ke admin sendiri (log aktivitas)
        $notifikasiAdminModel = new \App\Models\Admin\NotifikasiAdminModel();
        $admin_user_id = session()->get('user_id');
        $judul = 'Pengajuan Hukuman Disiplin Diterima';
        $nama_pegawai = $hukuman['nama'] ?? null;
        if (!$nama_pegawai && !empty($hukuman['pegawai_id'])) {
            $pegawaiModel = new \App\Models\PegawaiModel();
            $pegawai = $pegawaiModel->find($hukuman['pegawai_id']);
            $nama_pegawai = $pegawai['nama'] ?? '-';
        }
        $pesan = 'Anda baru saja menerima pengajuan hukuman disiplin untuk ' . $nama_pegawai . '.';
        $jenis = 'status';
        $referensi_id = $id;
        $notifikasiAdminModel->insertNotifikasi($admin_user_id, $judul, $pesan, $jenis, $referensi_id);
        return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
    }

    public function rejectHukumanDisiplin($id)
    {
        $hukuman = $this->hukumanModel->find($id);
        if (!$hukuman) {
            session()->setFlashdata('msg', 'Data tidak ditemukan!');
            session()->setFlashdata('msg_type', 'danger');
            return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
        }
        $this->hukumanModel->update($id, ['status' => 'rejected']);
        // Kirim notifikasi ke user pengaju
        if (!empty($hukuman['user_id'])) {
            $notifikasiModel = new NotifikasiModel();
            $judul = 'Pengajuan Hukuman Disiplin Ditolak';
            $nama_pegawai = $hukuman['nama'] ?? null;
            if (!$nama_pegawai && !empty($hukuman['pegawai_id'])) {
                $pegawaiModel = new \App\Models\PegawaiModel();
                $pegawai = $pegawaiModel->find($hukuman['pegawai_id']);
                $nama_pegawai = $pegawai['nama'] ?? '-';
            }
            $pesan = 'Pengajuan hukuman disiplin untuk ' . $nama_pegawai . ' ditolak admin.';
            $notifikasiModel->insert([
                'user_id' => $hukuman['user_id'],
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis' => 'status',
                'referensi_id' => $id,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        session()->setFlashdata('msg', 'Data berhasil di-reject dan notifikasi dikirim ke user.');
        session()->setFlashdata('msg_type', 'success');

        // Notifikasi ke admin sendiri (log aktivitas)
        $notifikasiAdminModel = new \App\Models\Admin\NotifikasiAdminModel();
        $admin_user_id = session()->get('user_id');
        $judul = 'Pengajuan Hukuman Disiplin Ditolak';
        $nama_pegawai = $hukuman['nama'] ?? null;
        if (!$nama_pegawai && !empty($hukuman['pegawai_id'])) {
            $pegawaiModel = new \App\Models\PegawaiModel();
            $pegawai = $pegawaiModel->find($hukuman['pegawai_id']);
            $nama_pegawai = $pegawai['nama'] ?? '-';
        }
        $pesan = 'Anda baru saja menolak pengajuan hukuman disiplin untuk ' . $nama_pegawai . '.';
        $jenis = 'status';
        $referensi_id = $id;
        $notifikasiAdminModel->insertNotifikasi($admin_user_id, $judul, $pesan, $jenis, $referensi_id);
        return redirect()->to(base_url('admin/kelola_hukuman_disiplin'));
    }

    public function getHukumanDisiplinAjaxDataTables()
    {
        $request = service('request');
        $draw = $request->getGet('draw');
        $start = $request->getGet('start');
        $length = $request->getGet('length');
        $search = $request->getGet('search')['value'] ?? '';
        $order = $request->getGet('order');
        $columns = $request->getGet('columns');
        $session = session();
        $current_user_id = $session->get('user_id');

        $base = $this->hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->where('(hukuman_disiplin.status != "pending" OR (hukuman_disiplin.status = "pending" AND hukuman_disiplin.user_id = ' . $current_user_id . '))');
        
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
        $total = $this->hukumanModel->countAllResults(false);
        if ($order && isset($columns[$order[0]['column']])) {
            $col = $columns[$order[0]['column']]['data'];
            $dir = $order[0]['dir'];
            $base = $base->orderBy($col, $dir);
        } else {
            $base = $base->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC');
        }
        $data = $base->findAll($length, $start);
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function getPengajuanHukumanDisiplinAjax()
    {
        $request = service('request');
        $draw = $request->getGet('draw');
        $start = $request->getGet('start');
        $length = $request->getGet('length');
        $search = $request->getGet('search')['value'] ?? '';
        $order = $request->getGet('order');
        $columns = $request->getGet('columns');

        $base = $this->hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->where('hukuman_disiplin.status', 'pending');
        
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
        $total = $this->hukumanModel->countAllResults(false);
        if ($order && isset($columns[$order[0]['column']])) {
            $col = $columns[$order[0]['column']]['data'];
            $dir = $order[0]['dir'];
            $base = $base->orderBy($col, $dir);
        } else {
            $base = $base->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC');
        }
        $data = $base->findAll($length, $start);
        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }
} 
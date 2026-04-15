<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\KirimLaporanModel;
use App\Models\NotifikasiModel;
use App\Models\UserModel;

class KirimLaporanController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'session', 'app']);
    }

    public function uploadFile()
    {
        $laporanFileModel = new KirimLaporanModel();
        $session = session();
        
        // Get filter parameters
        $filter_bulan = $this->request->getGet('bulan');
        $filter_tahun = $this->request->getGet('tahun');
        $filter_kategori = $this->request->getGet('kategori');
        
        // Build query
        $query = $laporanFileModel->where("created_by", $session->get("user_id"))
            ->where("is_hidden_by_user", 0);
        
        // Apply filters
        if (!empty($filter_bulan)) {
            $query->where('bulan', $filter_bulan);
        }
        if (!empty($filter_tahun)) {
            $query->where('tahun', $filter_tahun);
        }
        if (!empty($filter_kategori)) {
            $query->where('kategori', $filter_kategori);
        }
        
        $laporan_data = $query->orderBy("created_at", "DESC")->findAll();
        
        // Get available years for filter
        $tahun_tersedia = $laporanFileModel->select('DISTINCT(tahun) as tahun')
            ->where("created_by", $session->get("user_id"))
            ->where("is_hidden_by_user", 0)
            ->orderBy('tahun', 'DESC')
            ->findAll();
        $tahun_tersedia = array_column($tahun_tersedia, 'tahun');
        
        $data = [
            "laporan_data" => $laporan_data,
            "tahun_tersedia" => $tahun_tersedia,
            "filter_bulan" => $filter_bulan,
            "filter_tahun" => $filter_tahun,
            "filter_kategori" => $filter_kategori
        ];
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/KirimLaporan", $data);
    }

    public function addFile()
    {
        $laporanFileModel = new KirimLaporanModel();
        $session = session();
        $validation = \Config\Services::validation();
        $validation->setRules([
            'nama_laporan' => 'required|min_length[3]',
            'bulan' => 'required|numeric|greater_than[0]|less_than[13]',
            'tahun' => 'required|numeric|greater_than[2000]',
            'kategori' => 'required|in_list[Laporan Disiplin,Laporan Apel]'
        ]);
        if (!$validation->withRequest($this->request)->run()) {
            $session->setFlashdata('msg', 'Validasi gagal: ' . implode(' | ', $validation->getErrors()));
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back()->withInput();
        }
        $nama_laporan = $this->request->getPost('nama_laporan');
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $keterangan = $this->request->getPost('keterangan');
        $link_drive = $this->request->getPost('link_drive');
        $kategori = $this->request->getPost('kategori');
        $files = $this->request->getFileMultiple('files');
        $link_drive = $this->request->getPost('link_drive');
        
        // Validasi: minimal salah satu harus diisi (PDF atau Link Drive)
        if (empty($files) || count($files) === 0 || !$files[0]->isValid()) {
            if (empty($link_drive) || trim($link_drive) === '') {
                $session->setFlashdata('msg', 'Harus mengupload file PDF atau mengisi Link Drive!');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->back()->withInput();
            }
        }
        
        // Jika ada file yang diupload, validasi file
        if (!empty($files) && count($files) > 0 && $files[0]->isValid()) {
            if (count($files) !== 1) {
                $session->setFlashdata('msg', 'Hanya boleh upload 1 file!');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->back()->withInput();
            }
            
            // Validasi tipe file — getMimeType() baca dari KONTEN file (server-side, aman)
            $file = $files[0];
            $allowedMimes      = ['application/pdf'];
            $allowedExtensions = ['pdf'];

            if (!in_array($file->getMimeType(), $allowedMimes) || !in_array(strtolower($file->getClientExtension() ?: $file->getExtension()), $allowedExtensions)) {
                $session->setFlashdata('msg', 'Format file tidak didukung! Hanya file PDF yang diperbolehkan.');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->back()->withInput();
            }
            
            if ($file->getSize() > 1 * 1024 * 1024) { // 1MB dalam bytes
                $session->setFlashdata('msg', 'Ukuran file terlalu besar! Maksimal 1MB.');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->back()->withInput();
            }
        }
        $uploaded_files = [];
        $original_filenames = [];
        $userModel = new UserModel();
        $user = $userModel->find($session->get('user_id'));
        $nama_pengirim = $user['nama_lengkap'] ?? $user['username'];
        $existing_files = $laporanFileModel->where('created_by', $session->get('user_id'))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->countAllResults();
        $bagian = $existing_files + 1;
        
        // Proses upload file jika ada
        if (!empty($files) && count($files) > 0 && $files[0]->isValid()) {
            $file = $files[0];
            if ($file->isValid() && !$file->hasMoved()) {
                $original_filename = $file->getClientName();
                $original_filenames[] = $original_filename;
                $file_extension = $file->getExtension();
                $nama_file_bersih = preg_replace('/[^a-zA-Z0-9\s]/', '', $nama_laporan);
                $nama_file_bersih = str_replace(' ', '_', $nama_file_bersih);
                $nama_pengirim_bersih = preg_replace('/[^a-zA-Z0-9\s]/', '', $nama_pengirim);
                $nama_pengirim_bersih = str_replace(' ', '_', $nama_pengirim_bersih);
                $newName = $nama_file_bersih . '_' . $nama_pengirim_bersih . '_' . getBulanIndo($bulan) . '_' . $tahun . '_' . $bagian . 'm.' . $file_extension;
                $counter = 1;
                $originalName = $newName;
                $dir = WRITEPATH . 'uploads/laporan/';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                while (file_exists($dir . $newName)) {
                    $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newName = $nameWithoutExt . '_' . $counter . '.' . $ext;
                    $counter++;
                }
                $file->move($dir, $newName);
                $uploaded_files[] = $newName;
            }
        }
        
        // Validasi: minimal salah satu harus ada (file atau link drive)
        if (empty($uploaded_files) && (empty($link_drive) || trim($link_drive) === '')) {
            $session->setFlashdata('msg', 'Harus mengupload file atau mengisi Link Drive!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back()->withInput();
        }
        $data = [
            'nama_laporan' => $nama_laporan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'keterangan' => $keterangan,
            'link_drive' => $link_drive,
            'kategori' => $kategori,
            'status' => 'terkirim',
            'created_by' => $session->get('user_id')
        ];
        
        // Hanya tambahkan file_path dan original_filename jika ada file yang diupload
        if (!empty($uploaded_files)) {
            $data['file_path'] = $uploaded_files[0];
            $data['original_filename'] = $original_filenames[0];
        }
        if ($laporanFileModel->insert($data)) {
            $notifikasiModel = new NotifikasiModel();
            $notifikasiModel->createNotification(
                $session->get('user_id'),
                'Laporan Berhasil Diupload',
                'Laporan "' . $nama_laporan . '" untuk periode ' . getBulanIndo($bulan) . ' ' . $tahun . ' berhasil diupload dan menunggu review admin.',
                'laporan',
                $laporanFileModel->getInsertID()
            );
            $userModel = new UserModel();
            $admins = $userModel->where('role', 'admin')->findAll();
            foreach ($admins as $admin) {
                $notifikasiModel->createNotification(
                    $admin['id'],
                    'Laporan Masuk',
                    'Ada laporan baru "' . $nama_laporan . '" dari user ' . $session->get('username'),
                    'laporan',
                    $laporanFileModel->getInsertID()
                );
            }
            $session->setFlashdata('msg', 'Laporan berhasil diupload!');
            $session->setFlashdata('msg_type', 'success');
        } else {
            $session->setFlashdata('msg', 'Gagal mengupload laporan!');
            $session->setFlashdata('msg_type', 'danger');
        }
        return redirect()->to(base_url('user/kirimlaporan'));
    }

    public function deleteFile()
    {
        $laporanFileModel = new KirimLaporanModel();
        $session = session();
        $laporan_id = $this->request->getPost('laporan_id_to_delete');
        if (!$laporan_id) {
            $session->setFlashdata('msg', 'ID laporan tidak ditemukan!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back();
        }
        $laporan = $laporanFileModel->where('id', $laporan_id)
            ->where('created_by', $session->get('user_id'))
            ->first();
        if (!$laporan) {
            $session->setFlashdata('msg', 'Laporan tidak ditemukan!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back();
        }
        if ($laporan['status'] === 'diterima' || $laporan['status'] === 'disetujui') {
            $session->setFlashdata('msg', 'Laporan yang sudah disetujui tidak dapat dihapus! Silakan hubungi admin jika ada kesalahan.');
            $session->setFlashdata('msg_type', 'warning');
            return redirect()->back();
        }
        // Hapus file fisik jika ada
        if (!empty($laporan['file_path'])) {
            $files = explode(',', $laporan['file_path']);
            $dir = WRITEPATH . 'uploads/laporan/';
            foreach ($files as $file) {
                if (!empty($file)) {
                    $file_path = $dir . $file;
                    if (file_exists($file_path) && is_file($file_path)) {
                        unlink($file_path);
                    }
                }
            }
        }
        if ($laporanFileModel->delete($laporan_id)) {
            $session->setFlashdata('msg', 'Laporan berhasil dihapus!');
            $session->setFlashdata('msg_type', 'success');
        } else {
            $session->setFlashdata('msg', 'Gagal menghapus laporan!');
            $session->setFlashdata('msg_type', 'danger');
        }
        return redirect()->to(base_url('user/kirimlaporan'));
    }

    public function getFile($filename)
    {
        $dir = WRITEPATH . 'uploads/laporan/';
        $filename = basename(rawurldecode($filename));
        $file_path = $dir . $filename;
        if (!file_exists($file_path)) {
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

    public function hideLaporanFromUser()
    {
        $laporanFileModel = new KirimLaporanModel();
        $session = session();
        $laporan_id = $this->request->getPost('laporan_id_to_hide');
        $laporan = $laporanFileModel->where('id', $laporan_id)
            ->where('created_by', $session->get('user_id'))
            ->first();
        if ($laporan && in_array($laporan['status'], ['diterima', 'disetujui'])) {
            if (empty($laporan['is_hidden_by_user']) || $laporan['is_hidden_by_user'] == 0) {
                $laporanFileModel
                    ->set('is_hidden_by_user', 1)
                    ->where('id', $laporan_id)
                    ->where('created_by', $session->get('user_id'))
                    ->update();
                $session->setFlashdata('msg', 'Laporan berhasil dihapus dari daftar Anda.');
                $session->setFlashdata('msg_type', 'success');
            } else {
                $session->setFlashdata('msg', 'Laporan sudah tidak tampil di daftar Anda.');
                $session->setFlashdata('msg_type', 'info');
            }
        }
        return redirect()->to(base_url('user/kirimlaporan'));
    }

    public function getLaporanAjax()
    {
        try {
            $session = session();
            $user_id = $session->get('user_id');
            
            if (!$user_id) {
                return $this->response->setJSON([
                    'draw' => intval($this->request->getPost('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'rawData' => []
                ]);
            }
            
            $start           = intval($this->request->getPost('start')           ?? 0);
            $length          = intval($this->request->getPost('length')          ?? 10);
            $search          = $this->request->getPost('search[value]')          ?? '';
            $bulan_filter    = $this->request->getPost('bulan')    ?? '';
            $tahun_filter    = $this->request->getPost('tahun')    ?? '';
            $kategori_filter = $this->request->getPost('kategori') ?? '';
            
            $laporanModel = new KirimLaporanModel();
            
            // Get total records for this user
            $total = $laporanModel->where('created_by', $user_id)
                ->where('is_hidden_by_user', 0)
                ->countAllResults();
            
            list($data, $recordsFiltered) = $laporanModel->getFilteredLaporan(
                $user_id,
                $search, 
                $bulan_filter, 
                $tahun_filter, 
                $kategori_filter, 
                $start, 
                $length
            );
            
            // Format data untuk DataTables
            $formattedData = [];
            foreach ($data as $row) {
                $status_class = "";
                $status_display = $row['status'] ?? '';
                switch ($status_display) {
                    case "terkirim":
                        $status_class = "bg-warning";
                        $status_display = "Pending";
                        break;
                    case "dilihat":
                        $status_class = "bg-primary";
                        $status_display = "Dilihat";
                        break;
                    case "diterima":
                        $status_class = "bg-success";
                        $status_display = "Diterima";
                        break;
                    case "ditolak":
                        $status_class = "bg-danger";
                        $status_display = "Ditolak";
                        break;
                    default:
                        $status_class = "bg-secondary";
                        $status_display = ucfirst($status_display);
                        break;
                }
                
                $kategori_badge = '';
                if (!empty($row['kategori'])) {
                    if ($row['kategori'] == 'Laporan Apel') {
                        $kategori_badge = '<span class="badge bg-danger">' . htmlspecialchars($row['kategori']) . '</span>';
                    } else {
                        $kategori_badge = '<span class="badge bg-primary">' . htmlspecialchars($row['kategori']) . '</span>';
                    }
                } else {
                    $kategori_badge = '<span class="badge bg-primary">Laporan Disiplin</span>';
                }
                
                $actions = '';
                if (!empty($row['file_path'])) {
                    $actions .= '<a href="' . base_url('user/getFile/' . urlencode($row['file_path'])) . '" target="_blank" class="btn btn-info btn-sm btn-action" title="Lihat File"><i class="fas fa-eye"></i></a> ';
                }
                if (!empty($row['link_drive'])) {
                    $actions .= '<a href="' . htmlspecialchars($row['link_drive']) . '" target="_blank" class="btn btn-success btn-sm btn-action" title="Link Drive"><i class="fas fa-link"></i></a> ';
                }
                
                $hapusAction = '';
                $hapusType = '';
                if ($row['status'] === 'diterima' || $row['status'] === 'disetujui') {
                    $hapusAction = base_url('user/kirimlaporan/hide');
                    $hapusType = 'hide';
                } else {
                    $hapusAction = base_url('user/kirimlaporan/delete');
                    $hapusType = 'delete';
                }
                
                $actions .= '<form class="form-hapus-laporan d-inline" action="' . $hapusAction . '" method="POST">';
                $actions .= '<input type="hidden" name="' . csrf_token() . '" value="' . csrf_hash() . '">';
                $actions .= '<input type="hidden" name="laporan_id_to_' . $hapusType . '" value="' . $row['id'] . '">';
                $actions .= '<button type="button" class="btn btn-danger btn-sm btn-action btn-hapus-laporan" data-hapustype="' . $hapusType . '" data-nama="' . htmlspecialchars($row['nama_laporan']) . '"><i class="fas fa-trash"></i> Hapus</button>';
                $actions .= '</form>';
                
                $bulan_tahun = '';
                if (!empty($row['bulan']) && !empty($row['tahun'])) {
                    $bulan_tahun = getBulanIndo($row['bulan']) . ' ' . $row['tahun'];
                } else {
                    $bulan_tahun = '-';
                }
                
                // Format bulan_tahun untuk display (MM/YYYY)
                $bulan_tahun_display = '';
                if (!empty($row['bulan']) && !empty($row['tahun'])) {
                    $bulan_tahun_display = sprintf('%02d', $row['bulan']) . '/' . $row['tahun'];
                } else {
                    $bulan_tahun_display = '-';
                }
                
                $formattedData[] = [
                    null, // No akan diisi di createdRow
                    htmlspecialchars($row['nama_laporan'] ?? '-'),
                    $bulan_tahun_display,
                    $kategori_badge,
                    htmlspecialchars($row['keterangan'] ?? '-'),
                    '<span class="badge ' . $status_class . '">' . $status_display . '</span>',
                    htmlspecialchars($row['feedback'] ?? '-'),
                    !empty($row['created_at']) ? date('d-m-Y H:i', strtotime($row['created_at'])) : '-',
                    '<div class="d-flex flex-wrap gap-2">' . $actions . '</div>'
                ];
            }
            
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formattedData,
                'rawData' => $data // Tambahkan raw data untuk mobile cards
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getPost('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'rawData' => []
            ]);
        }
    }

    private function getNotifCount()
    {
        $notifikasiModel = new NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
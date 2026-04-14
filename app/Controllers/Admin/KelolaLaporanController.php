<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\KelolaLaporanModel;
use App\Models\UserModel;
use App\Models\NotifikasiModel;

class KelolaLaporanController extends BaseController
{
    protected $laporanModel;
    protected $userModel;
    protected $notifikasiModel;

    public function __construct()
    {
        $this->laporanModel = new KelolaLaporanModel();
        $this->userModel = new UserModel();
        $this->notifikasiModel = new NotifikasiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function kelolaLaporan()
    {
        $laporanFileModel = $this->laporanModel;
        $userModel = $this->userModel;
        $session = session();
        $data["users"] = $userModel->where("role", "user")->findAll();
        $tahun_tersedia_raw = $laporanFileModel->distinct()->select("tahun")->orderBy("tahun", "DESC")->findAll();
        $data["tahun_tersedia"] = array_column($tahun_tersedia_raw, "tahun");
        if (empty($data["tahun_tersedia"])) {
            $data["tahun_tersedia"][] = date("Y");
        }
        $filter_user = $this->request->getVar("user_id");
        $filter_bulan = $this->request->getVar("bulan");
        $filter_tahun = $this->request->getVar("tahun");
        $filter_kategori = $this->request->getVar("kategori");
        $laporan = $laporanFileModel->select("laporan_file.*, users.nama_lengkap")
            ->join("users", "users.id = laporan_file.created_by");
        if (!empty($filter_user)) {
            $laporan->where("laporan_file.created_by", $filter_user);
            $data["filter_user"] = $filter_user;
        }
        if (!empty($filter_bulan)) {
            $laporan->where("laporan_file.bulan", $filter_bulan);
            $data["filter_bulan"] = $filter_bulan;
        }
        if (!empty($filter_tahun)) {
            $laporan->where("laporan_file.tahun", $filter_tahun);
            $data["filter_tahun"] = $filter_tahun;
        }
        if (!empty($filter_kategori)) {
            $laporan->where("laporan_file.kategori", $filter_kategori);
            $data["filter_kategori"] = $filter_kategori;
        }
        $laporan->whereNotIn("laporan_file.status", ["diterima"]);
        $data["laporan"] = $laporan->orderBy("laporan_file.created_at", "DESC")->findAll();
        echo view("admin/KelolaLaporan", $data);
    }

    public function viewLaporan($id = null)
    {
        $laporanFileModel = $this->laporanModel;
        $notifikasiModel = $this->notifikasiModel;
        $session = session();
        $laporan = $laporanFileModel->find($id);
        if ($laporan) {
            if ($laporan["status"] == "terkirim") {
                if ($laporanFileModel->update($id, ["status" => "dilihat"])) {
                    $notifikasiModel->createNotification(
                        $laporan["created_by"],
                        "Laporan Dilihat",
                        "Laporan \"" . $laporan["nama_laporan"] . "\" telah dilihat oleh admin.",
                        "status",
                        $id
                    );
                } else {
                    $session->setFlashdata("msg", "Gagal mengupdate status laporan.");
                    $session->setFlashdata("msg_type", "danger");
                }
            }
            if (!empty($laporan["file_path"])) {
                // Redirect ke getFile() dengan file_path yang benar (seperti di KirimLaporanController)
                // Ini memastikan WebView Android menggunakan nama file yang benar dari URL
                return redirect()->to(base_url('admin/getFile/' . urlencode($laporan["file_path"])));
            } else {
                $session->setFlashdata("msg", "Laporan ini tidak memiliki file yang diupload. Cek link drive jika ada.");
                $session->setFlashdata("msg_type", "info");
            }
        } else {
            $session->setFlashdata("msg", "Laporan tidak ditemukan.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/kelola_laporan"));
    }

    public function viewLink($id = null)
    {
        $laporanFileModel = $this->laporanModel;
        $notifikasiModel = $this->notifikasiModel;
        $session = session();
        $laporan = $laporanFileModel->find($id);
        if ($laporan) {
            if ($laporan["status"] == "terkirim") {
                if ($laporanFileModel->update($id, ["status" => "dilihat"])) {
                    $notifikasiModel->createNotification(
                        $laporan["created_by"],
                        "Laporan Dilihat",
                        "Laporan \"" . $laporan["nama_laporan"] . "\" telah dilihat oleh admin.",
                        "status",
                        $id
                    );
                } else {
                    $session->setFlashdata("msg", "Gagal mengupdate status laporan.");
                    $session->setFlashdata("msg_type", "danger");
                }
            }
            if (!empty($laporan["link_drive"])) {
                // Validasi domain untuk mencegah open redirect
                $allowedDomains = ['drive.google.com', 'docs.google.com', 'www.google.com'];
                $parsed = parse_url($laporan["link_drive"]);
                $host = $parsed['host'] ?? '';
                if (in_array($host, $allowedDomains)) {
                    return redirect()->to($laporan["link_drive"]);
                } else {
                    $session->setFlashdata("msg", "Link drive tidak valid atau domain tidak diizinkan.");
                    $session->setFlashdata("msg_type", "danger");
                }
            } else {
                $session->setFlashdata("msg", "Laporan ini tidak memiliki link drive.");
                $session->setFlashdata("msg_type", "info");
            }
        } else {
            $session->setFlashdata("msg", "Laporan tidak ditemukan.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/kelola_laporan"));
    }

    public function approveLaporan()
    {
        $laporanFileModel = $this->laporanModel;
        $notifikasiModel = $this->notifikasiModel;
        $userModel = $this->userModel;
        $session = session();
        $laporan_id = $this->request->getPost('laporan_id');
        $feedback = $this->request->getPost('feedback');
        if ($laporanFileModel->update($laporan_id, ["status" => "diterima", "feedback" => $feedback])) {
            $laporan = $laporanFileModel->find($laporan_id);
            $user = $userModel->find($laporan["created_by"]);
            $username = $user ? $user["username"] : "User";
            $notifikasiModel->createNotification(
                $laporan["created_by"],
                "Laporan Diterima",
                "Laporan \"" . $laporan["nama_laporan"] . "\" telah diterima oleh admin. Feedback: " . $feedback,
                "status",
                $laporan_id
            );
            $notifikasiModel->createNotification(
                $session->get("user_id"),
                "Laporan Disetujui",
                "Anda telah menyetujui laporan \"" . $laporan["nama_laporan"] . "\" dari user " . $username,
                "status",
                $laporan_id
            );
            $session->setFlashdata("msg", "Laporan berhasil disetujui");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal menyetujui laporan.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/kelola_laporan"));
    }

    public function rejectLaporan()
    {
        $laporanFileModel = $this->laporanModel;
        $notifikasiModel = $this->notifikasiModel;
        $userModel = $this->userModel;
        $session = session();
        $laporan_id = $this->request->getPost('laporan_id');
        $feedback = $this->request->getPost('feedback');
        $laporan = $laporanFileModel->find($laporan_id);
        if ($laporan) {
            $laporanFileModel->update($laporan_id, [
                'status' => 'ditolak',
                'feedback' => $feedback,
                'rejected_at' => date('Y-m-d H:i:s')
            ]);
            $user = $userModel->find($laporan['created_by']);
            $username = $user ? $user['username'] : 'User';
            $notifikasiModel->createNotification(
                $laporan['created_by'],
                'Laporan Ditolak',
                'Laporan "' . $laporan['nama_laporan'] . '" telah ditolak oleh admin. Feedback: ' . $feedback,
                'status',
                $laporan_id
            );
            $notifikasiModel->createNotification(
                $session->get('user_id'),
                'Laporan Ditolak',
                'Anda telah menolak laporan "' . $laporan['nama_laporan'] . '" dari user ' . $username,
                'status',
                $laporan_id
            );
            $session->setFlashdata('msg', 'Laporan berhasil ditolak.');
            $session->setFlashdata('msg_type', 'success');
        } else {
            $session->setFlashdata('msg', 'Laporan tidak ditemukan.');
            $session->setFlashdata('msg_type', 'danger');
        }
        return redirect()->to(base_url('admin/kelola_laporan'));
    }

    public function deleteLaporan()
    {
        $laporanFileModel = $this->laporanModel;
        $session = session();
        $laporan_id = $this->request->getPost('laporan_id');
        
        if (!$laporan_id) {
            $session->setFlashdata("msg", "ID laporan tidak ditemukan.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/kelola_laporan"));
        }
        
        $laporan = $laporanFileModel->find($laporan_id);
        if ($laporan) {
            // Hapus file fisik jika ada
            if (!empty($laporan["file_path"])) {
                $files = explode(',', $laporan["file_path"]);
                $dir = WRITEPATH . 'uploads/laporan/';
                foreach ($files as $file) {
                    if (!empty($file)) {
                        $file_path = $dir . trim($file);
                        if (file_exists($file_path) && is_file($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
            
            // Hapus data dari database dengan where clause yang aman
            if ($laporanFileModel->where('id', $laporan_id)->delete()) {
                $session->setFlashdata("msg", "Laporan berhasil dihapus secara permanen.");
                $session->setFlashdata("msg_type", "success");
            } else {
                $session->setFlashdata("msg", "Gagal menghapus laporan.");
                $session->setFlashdata("msg_type", "danger");
            }
        } else {
            $session->setFlashdata("msg", "Laporan tidak ditemukan.");
            $session->setFlashdata("msg_type", "danger");
        }
        
        return redirect()->to(base_url("admin/kelola_laporan"));
    }

    public function getFile($filename)
    {
        // Bersihkan output buffer untuk web view compatibility
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Decode filename untuk handle URL encoding (web view compatibility)
        $filename = rawurldecode($filename);
        
        // Bersihkan path untuk keamanan (prevent directory traversal)
        $filename = basename($filename);
        
        $file_path = WRITEPATH . 'uploads/laporan/' . $filename;
        if (!file_exists($file_path)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan');
        }
        
        // Cari laporan berdasarkan file_path untuk mendapatkan nama file yang benar
        $laporan = $this->laporanModel->where('file_path', $filename)->first();
        
        $mime_type = mime_content_type($file_path);
        $file_size = filesize($file_path);
        $fileContent = file_get_contents($file_path);
        
        // Gunakan original_filename jika ada, jika tidak gunakan nama_laporan dengan extension
        $displayFilename = basename($filename); // default
        if ($laporan) {
            $fileInfo = pathinfo($file_path);
            $fileExtension = $fileInfo['extension'] ?? '';
            
            if (!empty($laporan["original_filename"])) {
                $displayFilename = $laporan["original_filename"];
            } else if (!empty($laporan["nama_laporan"])) {
                $displayFilename = $laporan["nama_laporan"];
                // Pastikan ada extension
                if (!empty($fileExtension) && !preg_match('/\.' . preg_quote($fileExtension, '/') . '$/i', $displayFilename)) {
                    $displayFilename .= '.' . $fileExtension;
                }
            }
            
            // Bersihkan karakter yang tidak valid untuk filename
            $displayFilename = preg_replace('/[<>:"|?*\x00-\x1f]/', '_', $displayFilename);
            $displayFilename = str_replace(['\\', '/'], '_', $displayFilename);
        }
        
        // Untuk WebView Android compatibility, gunakan response object
        // Encode filename untuk Content-Disposition header (RFC 5987 untuk non-ASCII)
        $encodedFilename = rawurlencode($displayFilename);
        $contentDisposition = 'inline; filename="' . addslashes($displayFilename) . '"; filename*=UTF-8\'\'' . $encodedFilename;
        
        return $this->response
            ->setContentType($mime_type)
            ->setHeader('Content-Length', (string)$file_size)
            ->setHeader('Content-Disposition', $contentDisposition)
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setHeader('Accept-Ranges', 'bytes')
            ->setBody($fileContent);
    }

    public function getLaporanAjax()
    {
        try {
            $start = intval($this->request->getGet('start') ?? 0);
            $length = intval($this->request->getGet('length') ?? 10);
            $search = $this->request->getGet('search[value]') ?? '';
            $user_filter = $this->request->getGet('user_id') ?? '';
            $bulan_filter = $this->request->getGet('bulan') ?? '';
            $tahun_filter = $this->request->getGet('tahun') ?? '';
            $kategori_filter = $this->request->getGet('kategori') ?? '';
            
            $total = $this->laporanModel->whereNotIn('status', ['diterima'])->countAllResults();
            
            list($data, $recordsFiltered) = $this->laporanModel->getFilteredLaporan(
                $search, 
                $user_filter, 
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
                switch ($row['status']) {
                    case "terkirim":
                        $status_class = "bg-warning";
                        break;
                    case "dilihat":
                        $status_class = "bg-primary";
                        break;
                    case "diterima":
                        $status_class = "bg-success";
                        break;
                    case "ditolak":
                        $status_class = "bg-danger";
                        break;
                    default:
                        $status_class = "bg-secondary";
                        break;
                }
                
                $status_display = $row['status'];
                if ($status_display == 'terkirim') {
                    $status_display = 'Pending';
                } else {
                    $status_display = ucfirst($status_display);
                }
                
                $kategori_badge = '';
                if (!empty($row['kategori'])) {
                    if ($row['kategori'] == 'Laporan Apel') {
                        $kategori_badge = '<span class="badge bg-danger">' . htmlspecialchars($row['kategori']) . '</span>';
                    } else {
                        $kategori_badge = '<span class="badge bg-primary">' . htmlspecialchars($row['kategori']) . '</span>';
                    }
                } else {
                    $kategori_badge = '<span class="badge bg-secondary">Laporan Disiplin</span>';
                }
                
                $actions = '';
                if (!empty($row['file_path'])) {
                    $actions .= '<a href="' . base_url('admin/kelola_laporan/view/' . $row['id']) . '" target="_blank" class="btn btn-info btn-sm btn-action" title="Lihat File"><i class="fas fa-eye"></i></a> ';
                }
                if (!empty($row['link_drive'])) {
                    $actions .= '<a href="' . base_url('admin/kelola_laporan/link/' . $row['id']) . '" target="_blank" class="btn btn-success btn-sm btn-action" title="Link Drive"><i class="fas fa-link"></i></a> ';
                }
                if ($row['status'] != 'diterima' && $row['status'] != 'ditolak') {
                    $actions .= '<button type="button" class="btn btn-success btn-sm btn-action btn-approve-laporan" data-id="' . $row['id'] . '" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="fas fa-check"></i> Approve</button> ';
                    $actions .= '<button type="button" class="btn btn-danger btn-sm btn-action btn-reject-laporan" data-id="' . $row['id'] . '" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times"></i> Reject</button> ';
                }
                $actions .= '<button type="button" class="btn btn-danger btn-sm btn-action btn-delete-laporan" data-id="' . $row['id'] . '" data-nama="' . esc($row['nama_laporan']) . '"><i class="fas fa-trash"></i> Hapus</button>';
                
                $formattedData[] = [
                    null, // No akan diisi di createdRow
                    $row['nama_laporan'],
                    sprintf('%02d', $row['bulan']) . '/' . $row['tahun'],
                    $kategori_badge,
                    $row['nama_lengkap'],
                    $row['keterangan'] ?: '-',
                    date('d-m-Y H:i', strtotime($row['created_at'])),
                    '<span class="badge ' . $status_class . '">' . $status_display . '</span>',
                    $row['feedback'] ?: '-',
                    '<div class="d-flex flex-wrap gap-2">' . $actions . '</div>'
                ];
            }
            
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formattedData,
                'rawData' => $data // Tambahkan raw data untuk mobile cards
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
    }
} 
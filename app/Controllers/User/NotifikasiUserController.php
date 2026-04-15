<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\NotifikasiUserModel;

class NotifikasiUserController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'session', 'app']);
    }

    public function notifikasi()
    {
        echo view("user/NotifikasiUser");
    }

    /**
     * AJAX POST - Load data notifikasi user
     */
    public function getNotifikasiAjax()
    {
        $notifikasiModel = new NotifikasiUserModel();
        $session         = session();
        $user_id         = $session->get("user_id");

        // Ambil semua notifikasi
        $notifikasi_list = $notifikasiModel
            ->where("user_id", $user_id)
            ->orderBy("created_at", "DESC")
            ->findAll();

        // Tandai semua sebagai sudah dibaca
        $notifikasiModel->where("user_id", $user_id)
            ->where("is_read", 0)
            ->set(["is_read" => 1])
            ->update();

        // Update session notif_count
        $session->set("notif_count", 0);

        // Bangun array hasil
        $result = [];
        foreach ($notifikasi_list as $row) {
            $link    = base_url("user/beranda_user");
            $jenis   = isset($row["jenis"]) ? $row["jenis"] : "sistem";
            $id_ref  = isset($row["referensi_id"]) ? $row["referensi_id"] : null;

            if (($jenis == "status" || $jenis == "feedback" || $jenis == "laporan") && !empty($id_ref)) {
                $link = base_url("user/kirimlaporan") . "#file-" . $id_ref;
            } elseif ($jenis == "sistem") {
                $link = base_url("user/beranda_user");
            }

            $created_at = new \DateTime($row["created_at"]);
            $now        = new \DateTime();
            $diff       = $now->diff($created_at);

            if ($diff->days > 0) {
                $time_display = $created_at->format('d M Y H:i');
            } elseif ($diff->h > 0) {
                $time_display = $diff->h . ' jam yang lalu';
            } elseif ($diff->i > 0) {
                $time_display = $diff->i . ' menit yang lalu';
            } else {
                $time_display = 'Baru saja';
            }

            // Status badge
            $status_class = 'status-system';
            $status_text  = 'Sistem';
            if (isset($row["jenis"])) {
                switch ($row["jenis"]) {
                    case 'laporan':
                        $status_class = 'status-uploaded';
                        $status_text  = 'Laporan Masuk';
                        break;
                    case 'status':
                        if (strpos(strtolower($row["judul"]), 'dilihat') !== false) {
                            $status_class = 'status-viewed';
                            $status_text  = 'Dilihat';
                        } elseif (strpos(strtolower($row["judul"]), 'diterima') !== false || strpos(strtolower($row["judul"]), 'disetujui') !== false) {
                            $status_class = 'status-approved';
                            $status_text  = 'Diterima';
                        } elseif (strpos(strtolower($row["judul"]), 'ditolak') !== false) {
                            $status_class = 'status-rejected';
                            $status_text  = 'Ditolak';
                        }
                        break;
                    case 'feedback':
                        $status_class = 'status-approved';
                        $status_text  = 'Feedback';
                        break;
                }
            }

            $result[] = [
                'id'           => $row['id'],
                'judul'        => esc($row['judul']),
                'pesan'        => esc($row['pesan']),
                'jenis'        => $jenis,
                'is_read'      => $row['is_read'],
                'link'         => $link,
                'time_display' => $time_display,
                'created_at'   => $created_at->format('d M Y H:i:s'),
                'status_class' => $status_class,
                'status_text'  => $status_text,
            ];
        }

        return $this->response->setJSON([
            'success'    => true,
            'notifikasi' => $result,
            'total'      => count($result),
        ]);
    }

    public function markNotificationAsRead()
    {
        $notifikasiModel = new NotifikasiUserModel();
        $session = session();
        $notif_id = $this->request->getPost('notif_id');
        if ($notif_id) {
            $notifikasiModel->where('id', $notif_id)
                ->where('user_id', $session->get('user_id'))
                ->set(['is_read' => 1])
                ->update();
        }
        return $this->response->setJSON(['success' => true]);
    }

    public function deleteAllNotifications()
    {
        $notifikasiModel = new NotifikasiUserModel();
        $session = session();
        try {
            $deleted = $notifikasiModel->where('user_id', $session->get('user_id'))->delete();
            if ($deleted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Semua notifikasi berhasil dihapus'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tidak ada notifikasi yang dihapus'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi: ' . $e->getMessage()
            ]);
        }
    }

    private function getNotifCount()
    {
        $notifikasiModel = new NotifikasiUserModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
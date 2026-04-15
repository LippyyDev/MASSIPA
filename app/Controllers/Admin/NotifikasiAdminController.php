<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\NotifikasiAdminModel;

class NotifikasiAdminController extends BaseController
{
    public function notifikasi()
    {
        echo view("admin/NotifikasiAdmin");
    }

    /**
     * AJAX POST - Load data notifikasi admin
     */
    public function getNotifikasiAjax()
    {
        $notifikasiModel = new NotifikasiAdminModel();
        $session         = session();
        $admin_user_id   = $session->get("user_id");

        // Hapus notifikasi lama (lebih dari 3 hari)
        $three_days_ago = date("Y-m-d H:i:s", strtotime("-3 days"));
        $notifikasiModel->where("user_id", $admin_user_id)
            ->where("created_at <", $three_days_ago)
            ->delete();

        // Ambil semua notifikasi admin
        $notifikasi = $notifikasiModel
            ->where("user_id", $admin_user_id)
            ->orderBy("created_at", "DESC")
            ->findAll();

        // Tandai semua sebagai sudah dibaca
        $notifikasiModel->where("user_id", $admin_user_id)
            ->where("is_read", 0)
            ->set(["is_read" => 1])
            ->update();

        // Update session notif_count
        $session->set("notif_count", 0);

        // Bangun array hasil dengan link dan waktu
        $result = [];
        foreach ($notifikasi as $row) {
            $link = base_url("admin/dashboard");

            if ($row["jenis"] == "laporan" && !empty($row["referensi_id"])) {
                $judul_lower = strtolower($row["judul"]);
                if (strpos($judul_lower, 'pengajuan hukuman') !== false) {
                    $link = base_url("admin/kelola_hukuman_disiplin?highlight=" . $row["referensi_id"]);
                } else {
                    $link = base_url("admin/kelola_laporan?highlight=" . $row["referensi_id"]);
                }
            } elseif ($row["jenis"] == "status" && !empty($row["referensi_id"])) {
                $judul_lower = strtolower($row["judul"]);
                if (strpos($judul_lower, 'laporan') !== false) {
                    $link = base_url("admin/kelola_laporan?highlight=" . $row["referensi_id"]);
                } else {
                    $link = base_url("admin/kelola_hukuman_disiplin?highlight=" . $row["referensi_id"]);
                }
            } elseif ($row["jenis"] == "feedback" && !empty($row["referensi_id"])) {
                $link = base_url("admin/kelola_laporan?highlight=" . $row["referensi_id"]);
            } elseif ($row["jenis"] == "sistem") {
                $link = base_url("admin/dashboard");
            }

            $created_at   = new \DateTime($row["created_at"]);
            $now          = new \DateTime();
            $diff         = $now->diff($created_at);

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
                if ($row["jenis"] == 'status') {
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
                } elseif ($row["jenis"] == 'laporan') {
                    $judul_lower = strtolower($row["judul"]);
                    if (strpos($judul_lower, 'pengajuan hukuman') !== false) {
                        $status_class = 'status-hukuman';
                        $status_text  = 'Laporan Hukuman Masuk';
                    } else {
                        $status_class = 'status-uploaded';
                        $status_text  = 'Laporan Kedisiplinan Masuk';
                    }
                }
            }

            $result[] = [
                'id'           => $row['id'],
                'judul'        => esc($row['judul']),
                'pesan'        => esc($row['pesan']),
                'jenis'        => $row['jenis'],
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

    public function deleteAllNotifications()
    {
        $notifikasiModel = new NotifikasiAdminModel();
        $session         = session();
        $admin_user_id   = $session->get("user_id");

        try {
            // Hapus semua notifikasi admin
            $deleted = $notifikasiModel->where('user_id', $admin_user_id)->delete();

            if ($deleted) {
                // Update session notif_count
                $session->set("notif_count", 0);

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
} 
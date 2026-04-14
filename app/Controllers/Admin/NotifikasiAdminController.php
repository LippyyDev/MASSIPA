<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\NotifikasiAdminModel;

class NotifikasiAdminController extends BaseController
{
    public function notifikasi()
    {
        $notifikasiModel = new NotifikasiAdminModel();
        $session = session();
        $admin_user_id = $session->get("user_id");

        // Delete old notifications (older than 3 days)
        $three_days_ago = date("Y-m-d H:i:s", strtotime("-3 days"));
        $notifikasiModel->where("user_id", $admin_user_id)
            ->where("created_at <", $three_days_ago)
            ->delete();

        // Get all notifications for the admin
        $data["notifikasi"] = $notifikasiModel->where("user_id", $admin_user_id)
            ->orderBy("created_at", "DESC")
            ->findAll();

        // Mark all displayed notifications as read
        $notifikasiModel->where("user_id", $admin_user_id)
            ->where("is_read", 0)
            ->set(["is_read" => 1])
            ->update();

        // Recalculate notif_count for sidebar after marking as read
        $session->set("notif_count", $notifikasiModel->where("user_id", $admin_user_id)->where("is_read", 0)->countAllResults());

        echo view("admin/NotifikasiAdmin", $data);
    }

    public function deleteAllNotifications()
    {
        $notifikasiModel = new NotifikasiAdminModel();
        $session = session();
        $admin_user_id = $session->get("user_id");

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
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
        $notifikasiModel = new NotifikasiUserModel();
        $session = session();
        $notifikasi_list = $notifikasiModel->where("user_id", $session->get("user_id"))
            ->orderBy("created_at", "DESC")
            ->findAll();
        $notifikasiModel->where("user_id", $session->get("user_id"))
            ->where("is_read", 0)
            ->set(["is_read" => 1])
            ->update();
        $data = [
            "notifikasi_list" => $notifikasi_list
        ];
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/NotifikasiUser", $data);
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
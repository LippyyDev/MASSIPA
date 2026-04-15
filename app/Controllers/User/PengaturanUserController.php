<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\LoginHistoryModel;

class PengaturanUserController extends BaseController
{
    /**
     * Tampilkan halaman Pengaturan User — berisi riwayat perangkat login milik sendiri.
     */
    public function pengaturan()
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $historyModel = new LoginHistoryModel();
        $riwayat      = $historyModel->getHistoryByUser($userId, 50);

        return view('user/Pengaturan', [
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * AJAX POST — load data riwayat login untuk user yang sedang aktif.
     */
    public function riwayatPerangkatAjax()
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $historyModel = new LoginHistoryModel();
        $riwayat      = $historyModel->getHistoryByUser($userId, 50);

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $riwayat,
        ]);
    }

    /**
     * Hapus SEMUA riwayat login milik user yang sedang aktif.
     */
    public function hapusSemuaRiwayat()
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $historyModel = new LoginHistoryModel();
        $historyModel->deleteByUser($userId);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Semua riwayat perangkat login berhasil dihapus.',
        ]);
    }
}

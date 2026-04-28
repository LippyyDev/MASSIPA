<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\LoginHistoryModel;
use App\Models\TwoFaModel;

class PengaturanUserController extends BaseController
{
    /**
     * Tampilkan halaman Pengaturan User — berisi riwayat perangkat login + 2FA settings.
     */
    public function pengaturan()
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $historyModel = new LoginHistoryModel();
        $twoFaModel   = new TwoFaModel();

        $riwayat   = $historyModel->getHistoryByUser($userId, 50);
        $isExempt  = $twoFaModel->isUserExempt($userId);
        $whitelist = $twoFaModel->getWhitelistByUser($userId);

        return view('user/Pengaturan', [
            'riwayat'  => $riwayat,
            'isExempt' => $isExempt,
            'whitelist' => $whitelist,
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

    // ═══════════════════════════════════════════════════════════════════
    // 2FA USER SELF-SERVICE
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Toggle exempt 2FA untuk user sendiri.
     */
    public function toggle2FaExempt()
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $twoFaModel = new TwoFaModel();
        $newVal     = $twoFaModel->toggleUserExempt($userId);

        return $this->response->setJSON([
            'status'  => 'success',
            'exempt'  => $newVal,
            'message' => $newVal
                ? 'Anda dikecualikan dari verifikasi 2FA.'
                : '2FA kembali aktif untuk akun Anda.',
        ]);
    }

    /**
     * AJAX POST — load whitelist milik user sendiri.
     */
    public function myWhitelistAjax()
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $twoFaModel = new TwoFaModel();
        $twoFaModel->purgeExpired();
        $data = $twoFaModel->getWhitelistByUser($userId);

        return $this->response->setJSON(['status' => 'success', 'data' => $data]);
    }

    /**
     * Hapus satu entry whitelist milik user sendiri (pencabutan perangkat tepercaya).
     */
    public function revokeMyWhitelist(int $id)
    {
        $session = session();
        $userId  = (int) $session->get('user_id');

        $twoFaModel = new TwoFaModel();
        $twoFaModel->revokeWhitelist($id, $userId); // ownerId memastikan hanya milik sendiri

        return redirect()->to(base_url('user/pengaturan') . '#perangkat-tepercaya')
            ->with('msg', 'Perangkat tepercaya berhasil dihapus.')
            ->with('msg_type', 'success');
    }
}

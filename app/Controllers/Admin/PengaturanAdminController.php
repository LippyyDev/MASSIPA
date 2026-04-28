<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\PengaturanAdminModel;
use App\Models\LoginHistoryModel;
use App\Models\TwoFaModel;
use App\Models\UserModel;

class PengaturanAdminController extends BaseController
{
    public function pengaturan()
    {
        $pengaturanModel = new PengaturanAdminModel();
        $historyModel    = new LoginHistoryModel();
        $twoFaModel      = new TwoFaModel();
        $userModel       = new UserModel();

        $keys    = $pengaturanModel->getAllApiKeys();
        $origins = $pengaturanModel->getAllOrigins();
        $riwayat = $historyModel->getAllHistory(100);

        // 2FA data
        $is2FaEnabled = $twoFaModel->isGlobal2FaEnabled();
        $exemptMap    = $twoFaModel->getAllExemptMap();
        $users        = $userModel->findAll();

        return view('admin/Pengaturan', [
            'keys'         => $keys,
            'origins'      => $origins,
            'riwayat'      => $riwayat,
            'is2FaEnabled' => $is2FaEnabled,
            'exemptMap'    => $exemptMap,
            'users'        => $users,
        ]);
    }

    public function apiKeyList()
    {
        return redirect()->to(base_url('admin/pengaturan'));
    }

    public function apiKeyAdd()
    {
        helper('text');
        $pengaturanModel = new PengaturanAdminModel();
        $label  = $this->request->getPost('label');
        $newKey = random_string('alnum', 32);
        $pengaturanModel->insertApiKey([
            'api_key'   => $newKey,
            'label'     => $label,
            'is_active' => 1,
        ]);
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', 'API Key berhasil dibuat!');
    }

    public function apiKeyDelete($id)
    {
        $pengaturanModel = new PengaturanAdminModel();
        $pengaturanModel->deleteApiKey($id);
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', 'API Key berhasil dihapus!');
    }

    public function apiKeyToggleActive($id)
    {
        $pengaturanModel = new PengaturanAdminModel();
        $result = $pengaturanModel->toggleApiKey($id);
        if ($result !== false) {
            $msg     = $result ? 'API Key diaktifkan!' : 'API Key dinonaktifkan!';
            $msgType = $result ? 'success' : 'warning';
        } else {
            $msg     = 'API Key tidak ditemukan!';
            $msgType = 'error';
        }
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', $msg)->with('msg_type', $msgType);
    }

    public function addOrigin()
    {
        $pengaturanModel = new PengaturanAdminModel();
        $origin = trim($this->request->getPost('origin'));
        if ($origin && filter_var($origin, FILTER_VALIDATE_URL)) {
            $pengaturanModel->insertOrigin(['origin' => $origin, 'is_active' => 1]);
            return redirect()->to(base_url('admin/pengaturan'))
                ->with('msg', 'Domain berhasil ditambahkan!')->with('msg_type', 'success');
        }
        return redirect()->to(base_url('admin/pengaturan'))
            ->with('msg', 'Domain tidak valid!')->with('msg_type', 'error');
    }

    public function deleteOrigin($id)
    {
        $pengaturanModel = new PengaturanAdminModel();
        $pengaturanModel->deleteOrigin($id);
        return redirect()->to(base_url('admin/pengaturan'))
            ->with('msg', 'Domain berhasil dihapus!')->with('msg_type', 'success');
    }

    public function toggleOrigin($id)
    {
        $pengaturanModel = new PengaturanAdminModel();
        $result = $pengaturanModel->toggleOrigin($id);
        if ($result !== false) {
            $msg     = $result ? 'Domain diaktifkan!' : 'Domain dinonaktifkan!';
            $msgType = $result ? 'success' : 'warning';
        } else {
            $msg     = 'Domain tidak ditemukan!';
            $msgType = 'error';
        }
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', $msg)->with('msg_type', $msgType);
    }

    /** AJAX POST — load semua riwayat login untuk DataTable admin. */
    public function riwayatPerangkatAjax()
    {
        $historyModel = new LoginHistoryModel();
        $riwayat      = $historyModel->getAllHistory(200);
        return $this->response->setJSON(['data' => $riwayat]);
    }

    /** Hapus satu record riwayat login (hak admin). */
    public function deleteRiwayat(int $id)
    {
        $historyModel = new LoginHistoryModel();
        $historyModel->deleteById($id);
        return redirect()->to(base_url('admin/pengaturan') . '#riwayat-perangkat')
            ->with('msg', 'Riwayat login berhasil dihapus.')
            ->with('msg_type', 'success');
    }

    // ═══════════════════════════════════════════════════════════════════
    // 2FA MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════

    /** Toggle 2FA global ON/OFF. */
    public function toggle2Fa()
    {
        $twoFaModel = new TwoFaModel();
        $current    = $twoFaModel->isGlobal2FaEnabled();
        $twoFaModel->setGlobal2Fa(! $current);

        $msg = ! $current
            ? '2FA berhasil diaktifkan. Semua user akan diminta OTP saat login dari perangkat baru.'
            : '2FA berhasil dinonaktifkan.';

        return redirect()->to(base_url('admin/pengaturan') . '#pengaturan-2fa')
            ->with('msg', $msg)
            ->with('msg_type', ! $current ? 'success' : 'warning');
    }

    /** Toggle exempt 2FA untuk satu user — dipanggil via AJAX POST. */
    public function toggleUserExempt(int $userId)
    {
        $twoFaModel = new TwoFaModel();
        $newVal     = $twoFaModel->toggleUserExempt($userId);

        return $this->response->setJSON([
            'status'  => 'success',
            'exempt'  => $newVal,
            'message' => $newVal
                ? 'User dikecualikan dari 2FA.'
                : '2FA kembali aktif untuk user ini.',
        ]);
    }

    /** AJAX POST — load semua whitelist 2FA. */
    public function whitelist2FaAjax()
    {
        $twoFaModel = new TwoFaModel();
        $twoFaModel->purgeExpired();
        $data = $twoFaModel->getAllWhitelist();
        return $this->response->setJSON(['data' => $data]);
    }

    /** Hapus satu entry whitelist 2FA (admin bisa hapus siapa saja). */
    public function revokeWhitelist(int $id)
    {
        $twoFaModel = new TwoFaModel();
        $twoFaModel->revokeWhitelist($id);

        return redirect()->to(base_url('admin/pengaturan') . '#pengaturan-2fa')
            ->with('msg', 'Whitelist perangkat berhasil dihapus. User akan diminta OTP saat login ulang.')
            ->with('msg_type', 'success');
    }
}
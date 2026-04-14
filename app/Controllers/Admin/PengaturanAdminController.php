<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\PengaturanAdminModel;

class PengaturanAdminController extends BaseController
{
    public function pengaturan()
    {
        $pengaturanModel = new PengaturanAdminModel();
        $keys = $pengaturanModel->getAllApiKeys();
        $origins = $pengaturanModel->getAllOrigins();
        return view('admin/Pengaturan', ['keys' => $keys, 'origins' => $origins]);
    }

    public function apiKeyList()
    {
        return redirect()->to(base_url('admin/pengaturan'));
    }

    public function apiKeyAdd()
    {
        helper('text');
        $pengaturanModel = new PengaturanAdminModel();
        $label = $this->request->getPost('label');
        $newKey = random_string('alnum', 32);
        $pengaturanModel->insertApiKey([
            'api_key' => $newKey,
            'label' => $label,
            'is_active' => 1
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
            $msg = $result ? 'API Key diaktifkan!' : 'API Key dinonaktifkan!';
            $msgType = $result ? 'success' : 'warning';
        } else {
            $msg = 'API Key tidak ditemukan!';
            $msgType = 'error';
        }
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', $msg)->with('msg_type', $msgType);
    }

    public function addOrigin()
    {
        $pengaturanModel = new PengaturanAdminModel();
        $origin = trim($this->request->getPost('origin'));
        if ($origin && filter_var($origin, FILTER_VALIDATE_URL)) {
            $pengaturanModel->insertOrigin([
                'origin' => $origin,
                'is_active' => 1
            ]);
            return redirect()->to(base_url('admin/pengaturan'))->with('msg', 'Domain berhasil ditambahkan!')->with('msg_type', 'success');
        }
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', 'Domain tidak valid!')->with('msg_type', 'error');
    }

    public function deleteOrigin($id)
    {
        $pengaturanModel = new PengaturanAdminModel();
        $pengaturanModel->deleteOrigin($id);
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', 'Domain berhasil dihapus!')->with('msg_type', 'success');
    }

    public function toggleOrigin($id)
    {
        $pengaturanModel = new PengaturanAdminModel();
        $result = $pengaturanModel->toggleOrigin($id);
        if ($result !== false) {
            $msg = $result ? 'Domain diaktifkan!' : 'Domain dinonaktifkan!';
            $msgType = $result ? 'success' : 'warning';
        } else {
            $msg = 'Domain tidak ditemukan!';
            $msgType = 'error';
        }
        return redirect()->to(base_url('admin/pengaturan'))->with('msg', $msg)->with('msg_type', $msgType);
    }
} 
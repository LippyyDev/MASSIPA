<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\ProfilUserModel;

class ProfilUserController extends BaseController
{
    public function profil()
    {
        $userModel = new ProfilUserModel();
        $session = session();
        $user_data = $userModel->getUserById($session->get("user_id"));
        if (!$user_data) {
            $session->setFlashdata("msg", "Data user tidak ditemukan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("user/dashboard"));
        }
        $data = ["user_data" => $user_data];
        // Notifikasi count jika perlu, bisa diambil dari helper lama
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/ProfilUser", $data);
    }

    public function updateProfil()
    {
        $userModel = new ProfilUserModel();
        $session = session();
        $user_id = $session->get("user_id");
        $nama_lengkap = $this->request->getPost("nama_lengkap");
        $email = $this->request->getPost("email");
        $username = $this->request->getPost("username");
        if (empty($nama_lengkap) || empty($email) || empty($username)) {
            $session->setFlashdata("msg", "Semua field profil wajib diisi!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->setFlashdata("msg", "Format email tidak valid!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        $user_data = $userModel->getUserById($user_id);
        if (!$user_data) {
            $session->setFlashdata("msg", "Data user tidak ditemukan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        $existing_user = $userModel->where("username", $username)->where("id !=", $user_id)->first();
        if ($existing_user) {
            $session->setFlashdata("msg", "Username sudah digunakan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        $existing_email = $userModel->where("email", $email)->where("id !=", $user_id)->first();
        if ($existing_email) {
            $session->setFlashdata("msg", "Email sudah digunakan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if ($userModel->updateProfil($user_id, ["nama_lengkap" => $nama_lengkap, "email" => $email, "username" => $username])) {
            $session->set([
                "nama_lengkap" => $nama_lengkap,
                "email" => $email,
                "username" => $username
            ]);
            $session->setFlashdata("msg", "Profil berhasil diperbarui!");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal memperbarui profil!");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/profil_user"));
    }

    public function updateFotoProfil()
    {
        $userModel = new ProfilUserModel();
        $session = session();
        $user_id = $session->get("user_id");
        $foto = $this->request->getFile("foto_profil");
        if (!$foto->isValid() || $foto->hasMoved()) {
            $session->setFlashdata("msg", "File foto tidak valid!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if ($foto->getSize() > 1 * 1024 * 1024) {
            $session->setFlashdata("msg", "Ukuran foto profil terlalu besar! Maksimal 1MB.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        
        // Validasi format file - tolak GIF
        $ext = strtolower($foto->getExtension());
        $mimeType = $foto->getMimeType();
        
        if ($ext === 'gif' || $mimeType === 'image/gif') {
            $session->setFlashdata("msg", "File GIF tidak diperbolehkan! Gunakan format gambar lain (JPG, PNG, WEBP, BMP, SVG, TIFF, ICO).");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/bmp', 'image/svg+xml', 'image/tiff', 'image/x-icon', 'image/x-ico'];
        if (!in_array($mimeType, $allowed_types)) {
            $session->setFlashdata("msg", "Tipe file tidak didukung! Gunakan JPG, PNG, WEBP, BMP, SVG, TIFF, atau ICO (kecuali GIF).");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        
        $user = $userModel->getUserById($user_id);
        $oldFoto = $user['foto_profil'] ?? null;
        $img = null;
        
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $img = imagecreatefromjpeg($foto->getTempName());
        } elseif ($ext === 'png') {
            $img = imagecreatefrompng($foto->getTempName());
        } elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
            $img = imagecreatefromwebp($foto->getTempName());
        } elseif ($ext === 'bmp' && function_exists('imagecreatefrombmp')) {
            $img = imagecreatefrombmp($foto->getTempName());
        } else {
            // Untuk format lain (SVG, TIFF, ICO) - tolak dengan pesan yang sesuai
            $session->setFlashdata("msg", "Format file tidak didukung untuk diproses! Gunakan JPG, PNG, atau WEBP.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        
        if (!$img) {
            $session->setFlashdata("msg", "Gagal membaca gambar! Pastikan file adalah gambar yang valid (JPG, PNG, WEBP, atau BMP).");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        $newName = 'profil_' . $user_id . '_' . time() . '.webp';
        $dir = FCPATH . 'uploads/profil/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $savePath = $dir . $newName;
        imagewebp($img, $savePath, 75);
        imagedestroy($img);
        if ($oldFoto && $oldFoto !== 'default.png' && file_exists($dir . $oldFoto)) {
            @unlink($dir . $oldFoto);
        }
        if ($userModel->updateFotoProfil($user_id, $newName)) {
            $session->set("foto_profil", $newName);
            $session->setFlashdata("msg", "Foto profil berhasil diperbarui!");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal memperbarui foto profil!");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/profil_user"));
    }

    public function updatePassword()
    {
        $userModel = new ProfilUserModel();
        $session = session();
        $user_id = $session->get("user_id");
        $password_lama = $this->request->getPost("password_lama");
        $password_baru = $this->request->getPost("password_baru");
        $konfirmasi_password = $this->request->getPost("konfirmasi_password");
        $user_data = $userModel->getUserById($user_id);
        if (!$user_data) {
            $session->setFlashdata("msg", "Data user tidak ditemukan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $session->setFlashdata("msg", "Semua field password wajib diisi!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if (!password_verify($password_lama, $user_data["password"])) {
            $session->setFlashdata("msg", "Password lama tidak sesuai!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if (strlen($password_baru) < 6) {
            $session->setFlashdata("msg", "Password baru minimal 6 karakter!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if ($password_baru !== $konfirmasi_password) {
            $session->setFlashdata("msg", "Konfirmasi password tidak sesuai!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }
        if ($userModel->updatePassword($user_id, password_hash($password_baru, PASSWORD_DEFAULT))) {
            $session->setFlashdata("msg", "Password berhasil diubah!");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal mengubah password!");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/profil_user"));
    }

    private function getNotifCount()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
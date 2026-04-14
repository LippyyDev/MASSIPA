<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\ProfilAdminModel;
use App\Models\NotifikasiModel;

class ProfilAdminController extends BaseController
{
    protected $profileModel;
    protected $notifikasiModel;

    public function __construct()
    {
        $this->profileModel = new ProfilAdminModel();
        $this->notifikasiModel = new NotifikasiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function profil()
    {
        $session = session();
        $user_id = $session->get("user_id");

        $data["user"] = $this->profileModel->getUserById($user_id);
        $data["notif_count"] = $this->notifikasiModel->where("user_id", $user_id)->where("is_read", 0)->countAllResults();
        $data["active"] = 'admin/profil';

        echo view("admin/ProfilAdmin", $data);
    }

    public function updateProfil()
    {
        $session = session();
        $user_id = $session->get("user_id");

        $username = $this->request->getVar("username");
        $nama_lengkap = $this->request->getVar("nama_lengkap");
        $email = $this->request->getVar("email");

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

        if (!$this->profileModel->isUsernameUnique($username, $user_id)) {
            $session->setFlashdata("msg", "Username sudah digunakan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }

        if (!$this->profileModel->isEmailUnique($email, $user_id)) {
            $session->setFlashdata("msg", "Email sudah digunakan!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back();
        }

        $data = [
            "username" => $username,
            "nama_lengkap" => $nama_lengkap,
            "email" => $email,
        ];

        if ($this->profileModel->updateProfile($user_id, $data)) {
            $session->set([
                "username" => $username,
                "nama_lengkap" => $nama_lengkap,
            ]);
            $session->setFlashdata("msg", "Profil berhasil diperbarui!");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal memperbarui profil!");
            $session->setFlashdata("msg_type", "danger");
        }
        
        return redirect()->to(base_url("admin/profil"));
    }

    public function updatePassword()
    {
        $session = session();
        $user_id = $session->get("user_id");

        $password_lama = $this->request->getVar("password_lama");
        $password_baru = $this->request->getVar("password_baru");
        $konfirmasi_password = $this->request->getVar("konfirmasi_password");

        // Validasi field password
        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
            $session->setFlashdata("msg", "Semua field password wajib diisi!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/profil"));
        }

        // Validasi password lama
        if (!$this->profileModel->verifyPassword($user_id, $password_lama)) {
            $session->setFlashdata("msg", "Password lama salah");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/profil"));
        }

        // Validasi password baru minimal 6 karakter
        if (strlen($password_baru) < 6) {
            $session->setFlashdata("msg", "Password baru minimal 6 karakter!");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/profil"));
        }

        // Validasi konfirmasi password
        if ($password_baru != $konfirmasi_password) {
            $session->setFlashdata("msg", "Konfirmasi password tidak sesuai");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/profil"));
        }

        // Update password
        $data = ["password" => $this->profileModel->hashPassword($password_baru)];
        
        if ($this->profileModel->updateProfile($user_id, $data)) {
            $session->setFlashdata("msg", "Password berhasil diubah!");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal mengubah password!");
            $session->setFlashdata("msg_type", "danger");
        }
        
        return redirect()->to(base_url("admin/profil"));
    }

    public function updateFotoProfil()
    {
        $session = session();
        $user_id = $session->get("user_id");

        $file = $this->request->getFile("foto_profil");

        if ($file->isValid() && !$file->hasMoved()) {
            // Validasi format file - tolak GIF
            $ext = strtolower($file->getExtension());
            $mimeType = $file->getMimeType();
            
            if ($ext === 'gif' || $mimeType === 'image/gif') {
                $session->setFlashdata("msg", "File GIF tidak diperbolehkan! Gunakan format gambar lain (JPG, PNG, WEBP, BMP, SVG, TIFF, ICO).");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->back();
            }
            
            // Validasi ukuran file maksimal 1MB
            if ($file->getSize() > 1 * 1024 * 1024) {
                $session->setFlashdata("msg", "Ukuran foto profil terlalu besar! Maksimal 1MB.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->back();
            }
            
            // Ambil data user lama untuk hapus file lama
            $user = $this->profileModel->getUserById($user_id);
            $oldFoto = $user['foto_profil'] ?? null;

            // Proses kompresi dan konversi ke webp
            $img = null;
            if ($ext === 'jpg' || $ext === 'jpeg') {
                $img = imagecreatefromjpeg($file->getTempName());
            } elseif ($ext === 'png') {
                $img = imagecreatefrompng($file->getTempName());
            } elseif ($ext === 'webp' && function_exists('imagecreatefromwebp')) {
                $img = imagecreatefromwebp($file->getTempName());
            } elseif ($ext === 'bmp' && function_exists('imagecreatefrombmp')) {
                $img = imagecreatefrombmp($file->getTempName());
            } else {
                // Untuk format lain (SVG, TIFF, ICO) - coba convert dengan GD jika memungkinkan
                // Atau tolak dengan pesan yang sesuai
                $session->setFlashdata("msg", "Format file tidak didukung untuk diproses! Gunakan JPG, PNG, atau WEBP.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->back();
            }
            
            if (!$img) {
                $session->setFlashdata("msg", "Gagal membaca gambar! Pastikan file adalah gambar yang valid (JPG, PNG, WEBP, atau BMP).");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->back();
            }
            // Nama file baru webp
            $newName = 'profil_' . $user_id . '_' . time() . '.webp';
            $dir = FCPATH . 'uploads/profil/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $savePath = $dir . $newName;
            // Kompres dan simpan ke webp (kualitas 75)
            imagewebp($img, $savePath, 75);
            imagedestroy($img);

            // Hapus file lama jika ada dan bukan default
            if ($oldFoto && $oldFoto !== 'default.png' && file_exists($dir . $oldFoto)) {
                @unlink($dir . $oldFoto);
            }

            $this->profileModel->updateProfilePhoto($user_id, $newName);
            $session->set("foto_profil", $newName);
            $session->setFlashdata("msg", "Foto profil berhasil diperbarui");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal mengupload file: " . $file->getErrorString());
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/profil"));
    }
} 
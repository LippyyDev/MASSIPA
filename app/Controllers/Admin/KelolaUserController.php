<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\KelolaUserModel;
use App\Models\SatkerModel;

class KelolaUserController extends BaseController
{
    protected $userModel;
    protected $satkerModel;

    public function __construct()
    {
        $this->userModel = new KelolaUserModel();
        $this->satkerModel = new SatkerModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function kelolaUser()
    {
        $data["users"]       = $this->userModel->getAllUsersWithSatker();
        $data["list_satker"] = $this->satkerModel->orderBy('nama', 'ASC')->findAll();
        $data["admin_count"] = $this->userModel->countAdmins();
        echo view("admin/KelolaUser", $data);
    }

    public function addUser()
    {
        $session = session();
        $role = $this->request->getVar("role");
        $satker_id = $this->request->getVar("satker_id");

        // Satker hanya wajib untuk role 'user', admin tidak terikat satker
        if ($role === 'user') {
            if (empty($satker_id)) {
                $session->setFlashdata("msg", "Satuan Kerja (Satker) wajib dipilih untuk role User!");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("admin/kelola_user"));
            }
            $satker = $this->satkerModel->find($satker_id);
            if (!$satker) {
                $session->setFlashdata("msg", "Satuan Kerja (Satker) tidak valid!");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("admin/kelola_user"));
            }
        } else {
            // Admin tidak memiliki satker
            $satker_id = null;
        }

        $rules = [
            "username" => [
                "rules" => "required|min_length[3]|max_length[20]|is_unique[users.username]",
                "errors" => [
                    "required" => "Username wajib diisi!",
                    "min_length" => "Username minimal {param} karakter!",
                    "max_length" => "Username maksimal {param} karakter!",
                    "is_unique" => "Username sudah digunakan oleh pengguna lain!"
                ]
            ],
            "password" => [
                "rules" => "required|min_length[6]",
                "errors" => [
                    "required" => "Password wajib diisi!",
                    "min_length" => "Password minimal {param} karakter!"
                ]
            ],
            "nama_lengkap" => [
                "rules" => "required|min_length[3]|max_length[100]",
                "errors" => [
                    "required" => "Nama lengkap wajib diisi!",
                    "min_length" => "Nama lengkap minimal {param} karakter!",
                    "max_length" => "Nama lengkap maksimal {param} karakter!"
                ]
            ],
            "email" => [
                "rules" => "required|valid_email|is_unique[users.email]",
                "errors" => [
                    "required" => "Email wajib diisi!",
                    "valid_email" => "Format email tidak valid!",
                    "is_unique" => "Email sudah terdaftar pada akun lain!"
                ]
            ],
            "role" => [
                "rules" => "required|in_list[admin,user]",
                "errors" => [
                    "required" => "Role (Peran) wajib dipilih!",
                    "in_list" => "Role tidak valid!"
                ]
            ],
        ];

        if ($this->validate($rules)) {
            $this->userModel->addUser([
                "username"    => $this->request->getVar("username"),
                "password"    => $this->userModel->hashPassword($this->request->getVar("password")),
                "nama_lengkap" => $this->request->getVar("nama_lengkap"),
                "email"       => $this->request->getVar("email"),
                "role"        => $role,
                "satker_id"   => $satker_id,
            ]);
            $session->setFlashdata("msg", "User berhasil ditambahkan");
            $session->setFlashdata("msg_type", "success");
        } else {
            // Get the first error message as plain text instead of HTML list
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            $session->setFlashdata("msg", $firstError);
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/kelola_user"));
    }

    public function updateUser()
    {
        $session = session();
        $user_id = $this->request->getVar("user_id");
        $old_user = $this->userModel->getUserById($user_id);
        $rules = [
            "username" => [
                "rules" => "required|min_length[3]|max_length[20]|is_unique[users.username,id,{$user_id}]",
                "errors" => [
                    "required" => "Username wajib diisi!",
                    "min_length" => "Username minimal {param} karakter!",
                    "max_length" => "Username maksimal {param} karakter!",
                    "is_unique" => "Username sudah digunakan oleh pengguna lain!"
                 ]
            ],
            "nama_lengkap" => [
                "rules" => "required|min_length[3]|max_length[100]",
                "errors" => [
                    "required" => "Nama lengkap wajib diisi!",
                    "min_length" => "Nama lengkap minimal {param} karakter!",
                    "max_length" => "Nama lengkap maksimal {param} karakter!"
                ]
            ],
            "email" => [
                "rules" => "required|valid_email|is_unique[users.email,id,{$user_id}]",
                "errors" => [
                    "required" => "Email wajib diisi!",
                    "valid_email" => "Format email tidak valid!",
                    "is_unique" => "Email sudah terdaftar pada akun lain!"
                ]
            ],
            "role" => [
                "rules" => "required",
                "errors" => [
                    "required" => "Role (Peran) wajib dipilih!"
                ]
            ],
            "satker_id" => "permit_empty|is_natural_no_zero"
        ];
        if ($this->request->getVar("password")) {
            $rules["password"] = [
                "rules" => "min_length[6]",
                "errors" => [
                    "min_length" => "Password minimal {param} karakter!"
                ]
            ];
        }
        $satker_id = $this->request->getVar("satker_id");
        // Convert empty string to null for admin users who don't need satker
        if (empty($satker_id)) {
            $satker_id = null;
        }
        if ($satker_id) {
            $user_satker = $this->userModel->getUserBySatkerId($satker_id, $user_id);
            if ($user_satker) {
                $session->setFlashdata("msg", "Satker sudah digunakan oleh user: <b>" . esc($user_satker["nama_lengkap"]) . "</b> (" . esc($user_satker["username"]) . ")");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("admin/kelola_user"));
            }
        }
        if ($this->validate($rules)) {
            $data = [
                "username" => $this->request->getVar("username"),
                "nama_lengkap" => $this->request->getVar("nama_lengkap"),
                "email" => $this->request->getVar("email"),
                "role" => $this->request->getVar("role"),
                "satker_id" => $satker_id,
            ];
            if ($this->request->getVar("password")) {
                $data["password"] = $this->userModel->hashPassword($this->request->getVar("password"));
            }
            $this->userModel->updateUser($user_id, $data);
            $session->setFlashdata("msg", "User berhasil diperbarui");
            $session->setFlashdata("msg_type", "success");
        } else {
            // Get the first error message as plain text instead of HTML list
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            $session->setFlashdata("msg", $firstError);
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/kelola_user"));
    }

    public function deleteUser()
    {
        $session = session();
        if ($session->get("role") !== "admin") {
            $session->setFlashdata("msg", "Akses ditolak. Hanya admin yang dapat menghapus user.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/kelola_user"));
        }
        $id   = $this->request->getVar("user_id");
        $user = $this->userModel->getUserById($id);

        // Proteksi: tidak bisa hapus admin terakhir
        if ($user && $user['role'] === 'admin' && $this->userModel->countAdmins() <= 1) {
            $session->setFlashdata("msg", "Tidak dapat menghapus admin terakhir! Minimal harus ada 1 akun admin.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/kelola_user"));
        }

        if ($id == $session->get("user_id")) {
            $session->setFlashdata("msg", "Tidak dapat menghapus akun yang sedang login.");
            $session->setFlashdata("msg_type", "danger");
        } else {
            if ($user) {
                $this->userModel->deleteUser($id);
                $session->setFlashdata("msg", "User berhasil dihapus");
                $session->setFlashdata("msg_type", "success");
            } else {
                $session->setFlashdata("msg", "User tidak ditemukan");
                $session->setFlashdata("msg_type", "danger");
            }
        }
        return redirect()->to(base_url("admin/kelola_user"));
    }
} 
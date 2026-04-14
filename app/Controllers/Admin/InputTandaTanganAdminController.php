<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\InputTandaTanganAdminModel;
use App\Models\Admin\KelolaPegawaiModel;

class InputTandaTanganAdminController extends BaseController
{
    protected $ttdModel;
    protected $ttdGambarModel;

    public function __construct()
    {
        $this->ttdModel = new InputTandaTanganAdminModel();
        $this->ttdModel->setTableTandaTangan();
        $this->ttdGambarModel = new InputTandaTanganAdminModel();
        $this->ttdGambarModel->setTableTandaTanganGambar();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function inputTandaTangan()
    {
        $session = session();
        $user_id = $session->get('user_id');
        $data["tanda_tangan_data"] = $this->ttdModel->where('user_id', $user_id)->orderBy("id", "DESC")->findAll();
        $data["tanda_tangan_gambar_data"] = $this->ttdGambarModel->where('user_id', $user_id)->orderBy("id", "DESC")->findAll();
        $data['notif_count'] = null; // opsional
        echo view("admin/InputTandaTanganAdmin", $data);
    }

    public function editTandaTangan($id = null)
    {
        $session = session();
        $data["tanda_tangan"] = $this->ttdModel->find($id);
        $data["tanda_tangan_data"] = $this->ttdModel->where("user_id", $session->get("user_id"))->orderBy("id", "DESC")->findAll();
        $data["notif_count"] = null;
        $data["active"] = 'admin/input_tanda_tangan';
        if (!$data["tanda_tangan"] || $data["tanda_tangan"]["user_id"] != $session->get("user_id")) {
            $session->setFlashdata("msg", "Data tanda tangan tidak ditemukan atau Anda tidak memiliki izin.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/input_tanda_tangan"));
        }
        echo view("admin/InputTandaTanganAdmin", $data);
    }

    public function updateTandaTangan()
    {
        $session = session();
        $tanda_tangan_id = $this->request->getVar("tanda_tangan_id");
        $rules = [
            "lokasi" => "required",
            "tanggal" => "required|valid_date",
            "nama_jabatan" => "required",
            "nama_penandatangan" => "required",
            "nip_penandatangan" => "required",
        ];
        if ($this->validate($rules)) {
            $data = [
                "lokasi" => $this->request->getVar("lokasi"),
                "tanggal" => $this->request->getVar("tanggal"),
                "nama_jabatan" => $this->request->getVar("nama_jabatan"),
                "nama_penandatangan" => $this->request->getVar("nama_penandatangan"),
                "nip_penandatangan" => $this->request->getVar("nip_penandatangan"),
            ];
            $this->ttdModel->update($tanda_tangan_id, $data);
            $session->setFlashdata("msg", "Data tanda tangan berhasil diperbarui");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", $this->validator->listErrors());
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function deleteTandaTangan($id = null)
    {
        $session = session();
        $tanda_tangan = $this->ttdModel->find($id);
        if ($tanda_tangan && $tanda_tangan["user_id"] == $session->get("user_id")) {
            $this->ttdModel->delete($id);
            $session->setFlashdata("msg", "Data tanda tangan berhasil dihapus");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Data tanda tangan tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function addTandaTangan()
    {
        $tandaTanganModel = new \App\Models\TandaTanganModel();
        $session = session();
        $user_id = $session->get('user_id');
        $rules = [
            "lokasi" => "required",
            "tanggal" => "required|valid_date",
            "nama_jabatan" => "required",
            "nama_penandatangan" => "required",
            "nip_penandatangan" => "required",
        ];
        if (!$this->validate($rules)) {
            $session->setFlashdata("msg", $this->validator->listErrors());
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/input_tanda_tangan"));
        }
        $tandaTanganModel->save([
            'lokasi' => $this->request->getVar('lokasi'),
            'tanggal' => $this->request->getVar('tanggal'),
            'nama_jabatan' => $this->request->getVar('nama_jabatan'),
            'nama_penandatangan' => $this->request->getVar('nama_penandatangan'),
            'nip_penandatangan' => $this->request->getVar('nip_penandatangan'),
            'is_aktif' => 0,
            'user_id' => $user_id
        ]);
        $session->setFlashdata("msg", "Tanda tangan berhasil ditambahkan");
        $session->setFlashdata("msg_type", "success");
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function addTandaTanganGambar()
    {
        $session = session();
        $user_id = $session->get('user_id');
        $rules = [
            "tempat" => "required",
            "tanggal" => "required|valid_date",
            "gambar_ttd" => "uploaded[gambar_ttd]|is_image[gambar_ttd]|mime_in[gambar_ttd,image/png,image/jpeg]|max_size[gambar_ttd,2048]"
        ];
        if (!$this->validate($rules)) {
            $session->setFlashdata("msg", $this->validator->listErrors());
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/input_tanda_tangan"));
        }
        $file = $this->request->getFile('gambar_ttd');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = 'ttd_' . time() . '_' . $file->getRandomName();
            $dir = FCPATH . 'writable/uploads/ttd/';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $file->move($dir, $newName);
            $this->ttdGambarModel->save([
                'tempat' => $this->request->getVar('tempat'),
                'tanggal' => $this->request->getVar('tanggal'),
                'file_path' => $newName,
                'is_aktif' => 0,
                'user_id' => $user_id
            ]);
            $session->setFlashdata("msg", "Tanda tangan gambar berhasil ditambahkan");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Gagal upload file gambar");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function updateTandaTanganGambar()
    {
        $session = session();
        $id = $this->request->getVar('id');
        $ttd = $this->ttdGambarModel->find($id);
        if (!$ttd) {
            $session->setFlashdata("msg", "Data tanda tangan gambar tidak ditemukan.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("admin/input_tanda_tangan"));
        }
        $rules = [
            "tempat" => "required",
            "tanggal" => "required|valid_date",
        ];
        if (!$this->validate($rules)) {
            $session->setFlashdata("msg", $this->validator->listErrors());
            $session->setFlashdata("msg_type", "danger");
            return redirect()->back()->withInput();
        }
        $file = $this->request->getFile('gambar_ttd');
        $updateData = [
            'tempat' => $this->request->getVar('tempat'),
            'tanggal' => $this->request->getVar('tanggal'),
        ];
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!in_array($file->getMimeType(), ['image/png', 'image/jpeg'])) {
                $session->setFlashdata("msg", "File harus berupa gambar PNG atau JPG.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->back()->withInput();
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                $session->setFlashdata("msg", "Ukuran file maksimal 2MB.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->back()->withInput();
            }
            $old_path = FCPATH . 'writable/uploads/ttd/' . $ttd['file_path'];
            if (file_exists($old_path)) {
                unlink($old_path);
            }
            $newName = 'ttd_' . time() . '_' . $file->getRandomName();
            $file->move(FCPATH . 'writable/uploads/ttd/', $newName);
            $updateData['file_path'] = $newName;
        }
        $this->ttdGambarModel->update($id, $updateData);
        $session->setFlashdata("msg", "Data tanda tangan gambar berhasil diperbarui");
        $session->setFlashdata("msg_type", "success");
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function deleteTandaTanganGambar($id = null)
    {
        $session = session();
        $ttd = $this->ttdGambarModel->find($id);
        if ($ttd) {
            $old_path = FCPATH . 'writable/uploads/ttd/' . $ttd['file_path'];
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
            $this->ttdGambarModel->delete($id);
            $session->setFlashdata("msg", "Tanda tangan gambar berhasil dihapus");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Data tidak ditemukan");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function setAktifTandaTangan($tipe, $id)
    {
        $session = session();
        $user_id = $session->get('user_id');
        // Cek status sekarang
        if ($tipe === 'manual') {
            $current = $this->ttdModel->where('id', $id)->where('user_id', $user_id)->first();
            if ($current && $current['is_aktif']) {
                $this->ttdModel->update($id, ['is_aktif' => 0]);
                $session->setFlashdata("msg", "Tanda tangan berhasil dinonaktifkan");
                $session->setFlashdata("msg_type", "success");
                return redirect()->to(base_url("admin/input_tanda_tangan"));
            }
        } else if ($tipe === 'gambar') {
            $current = $this->ttdGambarModel->where('id', $id)->where('user_id', $user_id)->first();
            if ($current && $current['is_aktif']) {
                $this->ttdGambarModel->update($id, ['is_aktif' => 0]);
                $session->setFlashdata("msg", "Tanda tangan gambar berhasil dinonaktifkan");
                $session->setFlashdata("msg_type", "success");
                return redirect()->to(base_url("admin/input_tanda_tangan"));
            }
        }
        // Jika belum aktif, nonaktifkan semua lalu aktifkan yang dipilih
        $this->ttdModel->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        $this->ttdGambarModel->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        if ($tipe === 'manual') {
            $this->ttdModel->update($id, ['is_aktif' => 1]);
        } else if ($tipe === 'gambar') {
            $this->ttdGambarModel->update($id, ['is_aktif' => 1]);
        }
        $session->setFlashdata("msg", "Tanda tangan aktif berhasil diubah");
        $session->setFlashdata("msg_type", "success");
        return redirect()->to(base_url("admin/input_tanda_tangan"));
    }

    public function getFile($filename)
    {
        while (ob_get_level() > 0) ob_end_clean();
        $filename = basename(rawurldecode($filename));
        $file_path = FCPATH . 'writable/uploads/ttd/' . $filename;
        if (!file_exists($file_path)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('File tidak ditemukan');
        }
        $mime_type   = mime_content_type($file_path);
        $file_size   = filesize($file_path);
        $fileContent = file_get_contents($file_path);
        $encodedName = rawurlencode($filename);
        return $this->response
            ->setContentType($mime_type)
            ->setHeader('Content-Length', (string) $file_size)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedName)
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody($fileContent);
    }

    /**
     * AJAX: Pencarian pegawai untuk autocomplete (semua satker)
     */
    public function searchPegawaiAjax()
    {
        try {
            $pegawaiModel = new KelolaPegawaiModel();
            $search = $this->request->getGet('q') ?? $this->request->getGet('search') ?? '';
            $limit = intval($this->request->getGet('limit') ?? 20);

            if (empty($search) || strlen($search) < 2) {
                return $this->response->setJSON([]);
            }

            $pegawai = $pegawaiModel
                ->select('pegawai.id, pegawai.nama, pegawai.nip, pegawai.jabatan')
                ->where('pegawai.status', 'aktif')
                ->groupStart()
                    ->like('pegawai.nama', $search)
                    ->orLike('pegawai.nip', $search)
                ->groupEnd()
                ->orderBy('pegawai.nama', 'ASC')
                ->findAll($limit, 0);

            return $this->response->setJSON($pegawai);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Terjadi error: ' . $e->getMessage()
            ]);
        }
    }
} 
<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\InputTandaTanganUserModel;
use App\Models\User\InputTandaTanganGambarUserModel;

class InputTandaTanganUserController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'session', 'app']);
    }

    public function inputTandaTangan()
    {
        $tandaTanganModel = new InputTandaTanganUserModel();
        $tandaTanganGambarModel = new InputTandaTanganGambarUserModel();
        $session = session();
        $user_id = $session->get('user_id');
        $data["tanda_tangan_data"] = $tandaTanganModel->getTandaTanganByUser($user_id);
        $data["tanda_tangan_gambar_data"] = $tandaTanganGambarModel->getTandaTanganGambarByUser($user_id);
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/InputTandaTanganUser", $data);
    }

    public function addTandaTangan()
    {
        $tandaTanganModel = new InputTandaTanganUserModel();
        $session = session();
        $rules = [
            "lokasi" => "required",
            "tanggal" => "required|valid_date",
            "nama_jabatan" => "required",
            "nama_penandatangan" => "required",
            "nip_penandatangan" => "required",
        ];
        $user_id = $session->get("user_id");
        if ($this->validate($rules)) {
            $tandaTanganModel->save([
                "lokasi" => $this->request->getVar("lokasi"),
                "tanggal" => $this->request->getVar("tanggal"),
                "nama_jabatan" => $this->request->getVar("nama_jabatan"),
                "nama_penandatangan" => $this->request->getVar("nama_penandatangan"),
                "nip_penandatangan" => $this->request->getVar("nip_penandatangan"),
                "user_id" => (int) $user_id,
            ]);
            $session->setFlashdata("msg", "Data tanda tangan berhasil ditambahkan");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", implode(' | ', $this->validator->getErrors()));
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function updateTandaTangan()
    {
        $tandaTanganModel = new InputTandaTanganUserModel();
        $session = session();
        $id = $this->request->getVar("tanda_tangan_id");
        $rules = [
            "lokasi" => "required",
            "tanggal" => "required|valid_date",
            "nama_jabatan" => "required",
            "nama_penandatangan" => "required",
            "nip_penandatangan" => "required",
        ];
        if ($this->validate($rules)) {
            $tandaTanganModel->update($id, [
                "lokasi" => $this->request->getVar("lokasi"),
                "tanggal" => $this->request->getVar("tanggal"),
                "nama_jabatan" => $this->request->getVar("nama_jabatan"),
                "nama_penandatangan" => $this->request->getVar("nama_penandatangan"),
                "nip_penandatangan" => $this->request->getVar("nip_penandatangan"),
            ]);
            $session->setFlashdata("msg", "Data tanda tangan berhasil diperbarui");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", implode(' | ', $this->validator->getErrors()));
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function deleteTandaTangan($id = null)
    {
        $tandaTanganModel = new InputTandaTanganUserModel();
        $session = session();
        $tanda_tangan = $tandaTanganModel->find($id);
        if ($tanda_tangan && $tanda_tangan["user_id"] == $session->get("user_id")) {
            $tandaTanganModel->delete($id);
            $session->setFlashdata("msg", "Data tanda tangan berhasil dihapus");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Data tanda tangan tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function setAktifTandaTangan($tipe, $id)
    {
        $session = session();
        $user_id = $session->get("user_id");
        $tandaTanganModel = new InputTandaTanganUserModel();
        $tandaTanganGambarModel = new InputTandaTanganGambarUserModel();
        if ($tipe === 'manual') {
            $current = $tandaTanganModel->where('id', $id)->where('user_id', $user_id)->first();
            if ($current && $current['is_aktif']) {
                $tandaTanganModel->update($id, ['is_aktif' => 0]);
                $session->setFlashdata("msg", "Tanda tangan berhasil dinonaktifkan");
                $session->setFlashdata("msg_type", "success");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
        } else if ($tipe === 'gambar') {
            $current = $tandaTanganGambarModel->where('id', $id)->where('user_id', $user_id)->first();
            if ($current && $current['is_aktif']) {
                $tandaTanganGambarModel->update($id, ['is_aktif' => 0]);
                $session->setFlashdata("msg", "Tanda tangan gambar berhasil dinonaktifkan");
                $session->setFlashdata("msg_type", "success");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
        }
        $tandaTanganModel->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        $tandaTanganGambarModel->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        if ($tipe === 'manual') {
            $tandaTanganModel->update($id, ['is_aktif' => 1]);
        } else if ($tipe === 'gambar') {
            $tandaTanganGambarModel->update($id, ['is_aktif' => 1]);
        }
        $session->setFlashdata("msg", "Tanda tangan aktif berhasil diubah");
        $session->setFlashdata("msg_type", "success");
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function addTandaTanganGambar()
    {
        $tandaTanganGambarModel = new InputTandaTanganGambarUserModel();
        $session = session();
        $user_id = $session->get('user_id');
        $rules = [
            "tempat" => "required",
            "tanggal" => "required|valid_date",
            "gambar_ttd" => "uploaded[gambar_ttd]|is_image[gambar_ttd]|mime_in[gambar_ttd,image/png,image/jpeg]|max_size[gambar_ttd,2048]"
        ];
        if (!$this->validate($rules)) {
            $session->setFlashdata("msg", implode(' | ', $this->validator->getErrors()));
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("user/inputtandatanganuser"));
        }
        $file = $this->request->getFile('gambar_ttd');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validasi keamanan: server-side MIME check + GD re-encoding
            $allowedMimes = ['image/png', 'image/jpeg'];
            $mime = $file->getMimeType();
            if (!in_array($mime, $allowedMimes)) {
                $session->setFlashdata("msg", "File harus berupa gambar PNG atau JPG yang valid!");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                $session->setFlashdata("msg", "Ukuran file maksimal 2MB.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            // GD re-encoding: hapus semua payload tersembunyi dalam gambar
            $imgInfo = @getimagesize($file->getTempName());
            if (!$imgInfo || !in_array($imgInfo['mime'], $allowedMimes)) {
                $session->setFlashdata("msg", "File bukan gambar PNG/JPG yang valid!");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            $ext = ($imgInfo['mime'] === 'image/jpeg') ? 'jpg' : 'png';
            if ($imgInfo['mime'] === 'image/jpeg') {
                $img = @imagecreatefromjpeg($file->getTempName());
            } else {
                $img = @imagecreatefrompng($file->getTempName());
            }
            if (!$img) {
                $session->setFlashdata("msg", "Gambar rusak atau tidak bisa diproses.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            $newName = 'ttd_' . bin2hex(random_bytes(16)) . '.' . $ext;
            $dir = WRITEPATH . 'uploads/ttd/';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            if ($imgInfo['mime'] === 'image/jpeg') {
                imagejpeg($img, $dir . $newName, 85);
            } else {
                imagesavealpha($img, true);
                imagepng($img, $dir . $newName, 6);
            }
            imagedestroy($img);
            $tandaTanganGambarModel->save([
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
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function deleteTandaTanganGambar($id = null)
    {
        $tandaTanganGambarModel = new InputTandaTanganGambarUserModel();
        $session = session();
        $user_id = $session->get('user_id');
        $ttd = $tandaTanganGambarModel->where('id', $id)->where('user_id', $user_id)->first();
        if ($ttd) {
            $file_path = WRITEPATH . 'uploads/ttd/' . $ttd['file_path'];
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
            $tandaTanganGambarModel->delete($id);
            $session->setFlashdata("msg", "Tanda tangan gambar berhasil dihapus");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", "Data tidak ditemukan");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function updateTandaTanganGambar()
    {
        $tandaTanganGambarModel = new InputTandaTanganGambarUserModel();
        $session = session();
        $user_id = $session->get("user_id");
        $id = $this->request->getVar('id');
        $ttd = $tandaTanganGambarModel->where('id', $id)->where('user_id', $user_id)->first();
        if (!$ttd) {
            $session->setFlashdata("msg", "Data tanda tangan gambar tidak ditemukan atau Anda tidak memiliki izin.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("user/inputtandatanganuser"));
        }
        $rules = [
            "tempat" => "required",
            "tanggal" => "required|valid_date",
        ];
        if (!$this->validate($rules)) {
            $session->setFlashdata("msg", implode(' | ', $this->validator->getErrors()));
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("user/inputtandatanganuser"));
        }
        $file = $this->request->getFile('gambar_ttd');
        $updateData = [
            'tempat' => $this->request->getVar('tempat'),
            'tanggal' => $this->request->getVar('tanggal'),
        ];
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validasi keamanan: server-side MIME check
            $allowedMimes = ['image/png', 'image/jpeg'];
            $mime = $file->getMimeType();
            if (!in_array($mime, $allowedMimes)) {
                $session->setFlashdata("msg", "File harus berupa gambar PNG atau JPG yang valid!");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            if ($file->getSize() > 2 * 1024 * 1024) {
                $session->setFlashdata("msg", "Ukuran file maksimal 2MB.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            // GD re-encoding: hapus payload tersembunyi
            $imgInfo = @getimagesize($file->getTempName());
            if (!$imgInfo || !in_array($imgInfo['mime'], $allowedMimes)) {
                $session->setFlashdata("msg", "File bukan gambar PNG/JPG yang valid!");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            $ext = ($imgInfo['mime'] === 'image/jpeg') ? 'jpg' : 'png';
            if ($imgInfo['mime'] === 'image/jpeg') {
                $img = @imagecreatefromjpeg($file->getTempName());
            } else {
                $img = @imagecreatefrompng($file->getTempName());
            }
            if (!$img) {
                $session->setFlashdata("msg", "Gambar rusak atau tidak bisa diproses.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/inputtandatanganuser"));
            }
            // Hapus file lama
            $old_path = WRITEPATH . 'uploads/ttd/' . $ttd['file_path'];
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
            // Simpan gambar baru (re-encoded, payload hilang)
            $newName = 'ttd_' . bin2hex(random_bytes(16)) . '.' . $ext;
            $dir = WRITEPATH . 'uploads/ttd/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            if ($imgInfo['mime'] === 'image/jpeg') {
                imagejpeg($img, $dir . $newName, 85);
            } else {
                imagesavealpha($img, true);
                imagepng($img, $dir . $newName, 6);
            }
            imagedestroy($img);
            $updateData['file_path'] = $newName;
        }
        $tandaTanganGambarModel->update($id, $updateData);
        $session->setFlashdata("msg", "Data tanda tangan gambar berhasil diperbarui");
        $session->setFlashdata("msg_type", "success");
        return redirect()->to(base_url("user/inputtandatanganuser"));
    }

    public function editTandaTanganGambar($id)
    {
        $tandaTanganGambarModel = new \App\Models\TandaTanganGambarModel();
        $session = session();
        $user_id = $session->get("user_id");
        $ttd = $tandaTanganGambarModel->where('id', $id)->where('user_id', $user_id)->first();
        if (!$ttd) {
            $session->setFlashdata("msg", "Data tanda tangan gambar tidak ditemukan atau Anda tidak memiliki izin.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("user/inputtandatanganuser"));
        }
        $data = [
            'tanda_tangan_gambar' => $ttd,
            'notif_count' => $this->getNotifCount(),
        ];
        echo view("user/edit_tanda_tangan_gambar", $data);
    }

    public function getFile($filename)
    {
        while (ob_get_level() > 0) ob_end_clean();
        $filename = basename(rawurldecode($filename));
        $file_path = WRITEPATH . 'uploads/ttd/' . $filename;
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
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody($fileContent);
    }

    private function getNotifCount()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
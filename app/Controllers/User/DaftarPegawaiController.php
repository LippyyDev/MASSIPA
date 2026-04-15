<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\DaftarPegawaiModel;
use App\Models\SatkerModel;
use App\Models\RiwayatMutasiModel;

class DaftarPegawaiController extends BaseController
{
    public function inputPegawai()
    {
        $pegawaiModel = new DaftarPegawaiModel();
        $satkerModel = new SatkerModel();
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $user = (new \App\Models\UserModel())->find(session()->get("user_id"));
        $satker_id = $user['satker_id'];
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_selesai', null)
            ->findColumn('pegawai_id');
        $pegawai = [];
        if ($pegawai_ids) {
            $pegawai = $pegawaiModel->getPegawaiByIds($pegawai_ids);
            foreach ($pegawai as &$p) {
                $mutasi = $riwayatMutasiModel->where('pegawai_id', $p['id'])->where('tanggal_selesai', null)->first();
                $p['satker'] = $mutasi ? $satkerModel->find($mutasi['satker_id'])['nama'] : '-';
            }
        }
        $list_satker = $satkerModel->findAll();
        return view('user/DaftarPegawai', [
            'pegawai' => $pegawai,
            'list_satker' => $list_satker
        ]);
    }

    public function importPegawai()
    {
        $pegawaiModel = new DaftarPegawaiModel();
        $session = session();
        if ($this->request->getFile('file_csv')->isValid()) {
            $file = $this->request->getFile('file_csv');
            $file->move(WRITEPATH . 'uploads', $file->getRandomName());
            $filePath = WRITEPATH . 'uploads/' . $file->getName();
            $csvData = array_map('str_getcsv', file($filePath));
            log_message('debug', 'CSV Data: ' . print_r($csvData, true));
            unlink($filePath);
            $header = array_map(function ($h) { return strtolower(trim($h)); }, $csvData[0]);
            $headerMapping = [
                'gol' => 'golongan',
                'nm' => 'nama',
                'no_induk' => 'nip',
                'posisi' => 'jabatan',
            ];
            foreach ($headerMapping as $csvHeader => $expectedHeader) {
                $key = array_search($csvHeader, $header);
                if ($key !== false) {
                    $header[$key] = $expectedHeader;
                }
            }
            $requiredHeaders = ['nama', 'nip', 'jabatan', 'golongan'];
            $missingHeaders = array_diff($requiredHeaders, $header);
            if (!empty($missingHeaders)) {
                $session->setFlashdata("msg", "Format CSV tidak sesuai. Kolom yang hilang: " . implode(', ', $missingHeaders));
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/DaftarPegawai"));
            }
            $userModel = new \App\Models\UserModel();
            $satker = $userModel->where("id", $session->get("user_id"))->first();
            if (!$satker) {
                $session->setFlashdata("msg", "Tidak ada satuan kerja yang tersedia untuk user ini.");
                $session->setFlashdata("msg_type", "danger");
                return redirect()->to(base_url("user/DaftarPegawai"));
            }
            $satker_id = $satker['id'];
            $successCount = 0;
            $errorMessages = [];
            foreach (array_slice($csvData, 1) as $index => $row) {
                $row = array_map('trim', $row);
                if (count($row) !== count($header)) {
                    $errorMessages[] = "Baris " . ($index + 2) . ": Jumlah kolom tidak sesuai dengan header";
                    continue;
                }
                $data = array_combine($header, $row);
                if (empty($data['nama']) || empty($data['nip']) || empty($data['jabatan']) || empty($data['golongan'])) {
                    $errorMessages[] = "Baris " . ($index + 2) . ": Ada data yang kosong";
                    continue;
                }
                $existingPegawai = $pegawaiModel->getPegawaiByNip($data['nip']);
                if ($existingPegawai) {
                    $errorMessages[] = "Baris " . ($index + 2) . ": NIP " . $data['nip'] . " sudah ada";
                    continue;
                }
                $validationErrors = [];
                if (empty($data['nama']) || strlen($data['nama']) < 2) {
                    $validationErrors[] = "Nama tidak valid";
                }
                if (empty($data['nip']) || strlen($data['nip']) < 16 || strlen($data['nip']) > 18 || !is_numeric($data['nip'])) {
                    $validationErrors[] = "NIP harus 16-18 digit angka";
                }
                if (empty($data['jabatan']) || strlen($data['jabatan']) < 2) {
                    $validationErrors[] = "Jabatan tidak valid";
                }
                if (empty($data['golongan']) || strlen($data['golongan']) < 2) {
                    $validationErrors[] = "Golongan tidak valid";
                }
                if (!empty($validationErrors)) {
                    $errorMessages[] = "Baris " . ($index + 2) . ": " . implode(", ", $validationErrors);
                    continue;
                }
                try {
                    $saveData = [
                        "nama" => $data['nama'],
                        "nip" => $data['nip'],
                        "jabatan" => $data['jabatan'],
                        "golongan" => $data['golongan'],
                        "satker_id" => $satker_id,
                        "created_by" => $session->get("user_id"),
                    ];
                    $saveResult = $pegawaiModel->addPegawai($saveData);
                    if ($saveResult) {
                        $successCount++;
                    } else {
                        $modelErrors = $pegawaiModel->errors();
                        $errorMessages[] = "Baris " . ($index + 2) . ": Gagal menyimpan - " . implode(", ", $modelErrors);
                    }
                } catch (\Exception $e) {
                    $errorMessages[] = "Baris " . ($index + 2) . ": Error - " . $e->getMessage();
                }
            }
            if ($successCount > 0) {
                $message = "$successCount pegawai berhasil diimpor.";
                if (!empty($errorMessages)) {
                    $message .= " Namun ada " . count($errorMessages) . " baris yang gagal.";
                }
                $session->setFlashdata("msg", $message);
                $session->setFlashdata("msg_type", "success");
            } else {
                $message = "Tidak ada pegawai yang berhasil diimpor.";
                if (!empty($errorMessages)) {
                    $message .= " Detail error: " . implode("; ", array_slice($errorMessages, 0, 5));
                    if (count($errorMessages) > 5) {
                        $message .= "... dan " . (count($errorMessages) - 5) . " error lainnya.";
                    }
                }
                $session->setFlashdata("msg", $message);
                $session->setFlashdata("msg_type", "danger");
            }
        } else {
            $session->setFlashdata("msg", "File CSV tidak valid.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/DaftarPegawai"));
    }

    public function mutasiPegawai($id)
    {
        $pegawaiModel = new DaftarPegawaiModel();
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $pegawai = $pegawaiModel->find($id);
        $riwayat = $riwayatMutasiModel->where('pegawai_id', $id)->orderBy('tanggal_mulai', 'ASC')->findAll();
        return view('user/mutasi_pegawai', [
            'pegawai' => $pegawai,
            'riwayat' => $riwayat
        ]);
    }

    public function prosesMutasiPegawai()
    {
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $pegawai_id = $this->request->getPost('pegawai_id');
        $satker_id = $this->request->getPost('satker_id');
        $tanggal_mutasi = $this->request->getPost('tanggal_mutasi');
        $riwayatMutasiModel->where('pegawai_id', $pegawai_id)->where('tanggal_selesai', null)->set(['tanggal_selesai' => $tanggal_mutasi])->update();
        $riwayatMutasiModel->insert([
            'pegawai_id' => $pegawai_id,
            'satker_id' => $satker_id,
            'tanggal_mulai' => $tanggal_mutasi
        ]);
        return redirect()->to(base_url('user/DaftarPegawai'))->with('msg', 'Mutasi pegawai berhasil!')->with('msg_type', 'success');
    }

    public function getPegawaiAjax()
    {
        try {
            $pegawaiModel = new DaftarPegawaiModel();
            $riwayatMutasiModel = new RiwayatMutasiModel();
            $user = (new \App\Models\UserModel())->find(session()->get("user_id"));
            $satker_id = $user['satker_id'];
            $pegawai_ids = $riwayatMutasiModel
                ->where('satker_id', $satker_id)
                ->where('tanggal_selesai', null)
                ->findColumn('pegawai_id');
            if (!$pegawai_ids)
                $pegawai_ids = [0];
            $start           = intval($this->request->getPost('start') ?? 0);
            $length          = intval($this->request->getPost('length') ?? 10);
            $search          = $this->request->getPost('search[value]') ?? '';
            $golongan_filter = $this->request->getPost('golongan') ?? '';
            $jabatan_filter  = $this->request->getPost('jabatan')  ?? '';
            $total = $pegawaiModel->whereIn('id', $pegawai_ids)->countAllResults();
            $filteredBuilder = $pegawaiModel->select('id, nama, nip, pangkat, golongan, jabatan, status')
                ->whereIn('id', $pegawai_ids);
            if (!empty($search)) {
                $filteredBuilder->groupStart()
                    ->like('nama', $search)
                    ->orLike('nip', $search)
                    ->groupEnd();
            }
            if (!empty($golongan_filter)) {
                $filteredBuilder->where('golongan', $golongan_filter);
            }
            if (!empty($jabatan_filter)) {
                $filteredBuilder->where('jabatan', $jabatan_filter);
            }
            $recordsFiltered = $filteredBuilder->countAllResults(false);
            $data = $filteredBuilder->limit($length, $start)->findAll();
            return $this->response->setJSON([
                'draw'            => intval($this->request->getPost('draw')),
                'recordsTotal'    => $total,
                'recordsFiltered' => $recordsFiltered,
                'data'            => $data
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'draw'            => intval($this->request->getPost('draw')),
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $e->getMessage()
            ]);
        }
    }

    public function searchPegawaiAjax()
    {
        try {
            $pegawaiModel = new DaftarPegawaiModel();
            $riwayatMutasiModel = new RiwayatMutasiModel();
            $user = (new \App\Models\UserModel())->find(session()->get("user_id"));
            if (!$user || !isset($user['satker_id']) || empty($user['satker_id'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'error' => 'User tidak valid atau tidak memiliki satker_id.'
                ]);
            }
            $satker_id = $user['satker_id'];
            $pegawai_ids = $riwayatMutasiModel
                ->where('satker_id', $satker_id)
                ->where('tanggal_selesai', null)
                ->findColumn('pegawai_id');
            if (!$pegawai_ids) $pegawai_ids = [0];
            $search = $this->request->getGet('q') ?? $this->request->getGet('search') ?? '';
            $limit = intval($this->request->getGet('limit') ?? 20);
            $pegawai = $pegawaiModel
                ->select('id, nama, nip, jabatan')
                ->whereIn('id', $pegawai_ids)
                ->where('status', 'aktif')
                ->groupStart()
                    ->like('nama', $search)
                    ->orLike('nip', $search)
                ->groupEnd()
                ->orderBy('nama', 'ASC')
                ->findAll($limit, 0);
            return $this->response->setJSON($pegawai);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Terjadi error: ' . $e->getMessage()
            ]);
        }
    }
} 
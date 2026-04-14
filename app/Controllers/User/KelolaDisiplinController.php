<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\KelolaDisiplinModel;
use App\Models\PegawaiModel;
use App\Models\RiwayatMutasiModel;

class KelolaDisiplinController extends BaseController
{
    public function inputKedisiplinan()
    {
        helper('app');
        $pegawaiModel = new PegawaiModel();
        $kedisiplinanModel = new KelolaDisiplinModel();
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $filter_tahun = $this->request->getVar("tahun") ?? date("Y");
        $filter_bulan = $this->request->getVar("bulan") ?? date("n");
        $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_mulai <=', $periode_akhir)
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', $periode_akhir)
            ->groupEnd()
            ->findColumn('pegawai_id');
        $data["pegawai_list"] = [];
        if ($pegawai_ids) {
            $data["pegawai_list"] = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif')->orderBy("nama", "ASC")->findAll();
        }
        $pegawai_ids = array_column($data["pegawai_list"], "id");
        $tahun_tersedia_raw = [];
        if (!empty($pegawai_ids)) {
            $tahun_tersedia_raw = $kedisiplinanModel->distinct()->select("tahun")->whereIn("pegawai_id", $pegawai_ids)->orderBy("tahun", "DESC")->findAll();
        }
        $data["tahun_tersedia"] = array_column($tahun_tersedia_raw, "tahun");
        if (empty($data["tahun_tersedia"])) {
            $data["tahun_tersedia"][] = date("Y");
        }
        $filter_tahun = $this->request->getVar("tahun") ?? (empty($data["tahun_tersedia"]) ? date("Y") : $data["tahun_tersedia"][0]);
        $data["filter_tahun"] = $filter_tahun;
        $rekap_periode = [];
        $bulan_ada_data = [];
        if (!empty($pegawai_ids)) {
            $bulan_ada_data_raw = $kedisiplinanModel->distinct()->select('bulan')->whereIn('pegawai_id', $pegawai_ids)->where('tahun', $filter_tahun)->orderBy('bulan', 'ASC')->findAll();
            $bulan_ada_data = array_column($bulan_ada_data_raw, 'bulan');
        }
        foreach ($bulan_ada_data as $bulan) {
            $tanggal_awal_bulan = date("Y-m-01", strtotime("$filter_tahun-$bulan-01"));
            $tanggal_akhir_bulan = date("Y-m-t", strtotime("$filter_tahun-$bulan-01"));
            $pegawai_ids_aktif = $riwayatMutasiModel
                ->where('satker_id', $satker_id)
                ->where('tanggal_mulai <=', $tanggal_akhir_bulan)
                ->groupStart()
                ->where('tanggal_selesai', null)
                ->orWhere('tanggal_selesai >=', $tanggal_awal_bulan)
                ->groupEnd()
                ->findColumn('pegawai_id');
            if (!$pegawai_ids_aktif)
                $pegawai_ids_aktif = [0];
            $rekap = $this->getRekapKedisiplinanPeriode($bulan, $filter_tahun, $pegawai_ids_aktif);
            $rekap["bulan"] = $bulan;
            $rekap["tahun"] = $filter_tahun;
            $rekap_periode[] = $rekap;
        }
        $data["rekap_periode"] = $rekap_periode;
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/KelolaDisiplin", $data);
    }

    private function getRekapKedisiplinanPeriode($filter_bulan, $filter_tahun, $pegawai_ids = [])
    {
        $kedisiplinanModel = new KelolaDisiplinModel();
        if (empty($pegawai_ids)) {
            return [
                'terlambat' => 0,
                'tidak_absen_masuk' => 0,
                'pulang_awal' => 0,
                'tidak_absen_pulang' => 0,
                'keluar_tidak_izin' => 0,
                'tidak_masuk_tanpa_ket' => 0,
                'tidak_masuk_sakit' => 0,
                'tidak_masuk_kerja' => 0,
                'bentuk_pembinaan' => '-',
                'keterangan' => '-',
            ];
        }
        $all = $kedisiplinanModel
            ->whereIn('pegawai_id', $pegawai_ids)
            ->where('bulan', $filter_bulan)
            ->where('tahun', $filter_tahun)
            ->findAll();
        $total = [
            'terlambat' => 0,
            'tidak_absen_masuk' => 0,
            'pulang_awal' => 0,
            'tidak_absen_pulang' => 0,
            'keluar_tidak_izin' => 0,
            'tidak_masuk_tanpa_ket' => 0,
            'tidak_masuk_sakit' => 0,
            'tidak_masuk_kerja' => 0,
        ];
        $bp = [];
        $ket = [];
        foreach ($all as $row) {
            $total['terlambat'] += (int) $row['terlambat'];
            $total['tidak_absen_masuk'] += (int) $row['tidak_absen_masuk'];
            $total['pulang_awal'] += (int) $row['pulang_awal'];
            $total['tidak_absen_pulang'] += (int) $row['tidak_absen_pulang'];
            $total['keluar_tidak_izin'] += (int) $row['keluar_tidak_izin'];
            $total['tidak_masuk_tanpa_ket'] += (int) $row['tidak_masuk_tanpa_ket'];
            $total['tidak_masuk_sakit'] += (int) $row['tidak_masuk_sakit'];
            $total['tidak_masuk_kerja'] += (int) $row['tidak_masuk_kerja'];
            if (!empty($row['bentuk_pembinaan']))
                $bp[] = trim($row['bentuk_pembinaan']);
            if (!empty($row['keterangan']))
                $ket[] = trim($row['keterangan']);
        }
        $bp = array_unique($bp);
        $ket = array_unique($ket);
        $total['bentuk_pembinaan'] = $bp ? implode(', ', $bp) : '-';
        $total['keterangan'] = $ket ? implode(', ', $ket) : '-';
        return $total;
    }

    public function addKedisiplinan()
    {
        $kedisiplinanModel = new KelolaDisiplinModel();
        $session = session();
        $rules = [
            "pegawai_id" => "required",
            "bulan" => "required",
            "tahun" => "required",
            "terlambat" => "required|numeric",
            "tidak_absen_masuk" => "required|numeric",
            "pulang_awal" => "required|numeric",
            "tidak_absen_pulang" => "required|numeric",
            "keluar_tidak_izin" => "required|numeric",
            "tidak_masuk_tanpa_ket" => "required|numeric",
            "tidak_masuk_sakit" => "required|numeric",
            "tidak_masuk_kerja" => "required|numeric",
            "bentuk_pembinaan" => "permit_empty",
            "keterangan" => "permit_empty",
        ];
        $pegawai_id = $this->request->getVar("pegawai_id");
        $bulan = $this->request->getVar("bulan");
        $tahun = $this->request->getVar("tahun");
        $existing_data = $kedisiplinanModel->where(["pegawai_id" => $pegawai_id, "bulan" => $bulan, "tahun" => $tahun])->first();
        if ($existing_data) {
            $session->setFlashdata("msg", "Data kedisiplinan untuk pegawai, bulan, dan tahun tersebut sudah ada.");
            $session->setFlashdata("msg_type", "danger");
            return redirect()->to(base_url("user/input_kedisiplinan"));
        }
        if ($this->validate($rules)) {
            // Dapatkan riwayat_mutasi_id yang aktif pada periode tersebut
            $riwayatMutasiModel = new \App\Models\RiwayatMutasiModel();
            $riwayat_mutasi = $riwayatMutasiModel->where('pegawai_id', $pegawai_id)
                ->where('tanggal_mulai <=', $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01')
                ->groupStart()
                    ->where('tanggal_selesai IS NULL')
                    ->orWhere('tanggal_selesai >=', $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . date('t', mktime(0, 0, 0, $bulan, 1, $tahun)))
                ->groupEnd()
                ->orderBy('tanggal_mulai', 'DESC')
                ->first();
            
            $riwayat_mutasi_id = $riwayat_mutasi ? $riwayat_mutasi['id'] : null;
            
            $kedisiplinanModel->addKedisiplinan([
                "pegawai_id" => $pegawai_id,
                "riwayat_mutasi_id" => $riwayat_mutasi_id,
                "bulan" => $bulan,
                "tahun" => $tahun,
                "terlambat" => $this->request->getVar("terlambat"),
                "tidak_absen_masuk" => $this->request->getVar("tidak_absen_masuk"),
                "pulang_awal" => $this->request->getVar("pulang_awal"),
                "tidak_absen_pulang" => $this->request->getVar("tidak_absen_pulang"),
                "keluar_tidak_izin" => $this->request->getVar("keluar_tidak_izin"),
                "tidak_masuk_tanpa_ket" => $this->request->getVar("tidak_masuk_tanpa_ket"),
                "tidak_masuk_sakit" => $this->request->getVar("tidak_masuk_sakit"),
                "tidak_masuk_kerja" => $this->request->getVar("tidak_masuk_kerja"),
                "bentuk_pembinaan" => $this->request->getVar("bentuk_pembinaan"),
                "keterangan" => $this->request->getVar("keterangan"),
                "created_by" => $session->get("user_id"),
            ]);
            $session->setFlashdata("msg", "Data kedisiplinan berhasil ditambahkan");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", implode(' | ', $this->validator->getErrors()));
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/input_kedisiplinan"));
    }

    public function updateKedisiplinan()
    {
        $kedisiplinanModel = new KelolaDisiplinModel();
        $session = session();
        $kedisiplinan_id = $this->request->getVar("kedisiplinan_id");
        $rules = [
            "terlambat" => "required|numeric",
            "tidak_absen_masuk" => "required|numeric",
            "pulang_awal" => "required|numeric",
            "tidak_absen_pulang" => "required|numeric",
            "keluar_tidak_izin" => "required|numeric",
            "tidak_masuk_tanpa_ket" => "required|numeric",
            "tidak_masuk_sakit" => "required|numeric",
            "tidak_masuk_kerja" => "required|numeric",
            "bentuk_pembinaan" => "permit_empty",
            "keterangan" => "permit_empty",
        ];
        if ($this->validate($rules)) {
            $data = [
                "terlambat" => $this->request->getVar("terlambat"),
                "tidak_absen_masuk" => $this->request->getVar("tidak_absen_masuk"),
                "pulang_awal" => $this->request->getVar("pulang_awal"),
                "tidak_absen_pulang" => $this->request->getVar("tidak_absen_pulang"),
                "keluar_tidak_izin" => $this->request->getVar("keluar_tidak_izin"),
                "tidak_masuk_tanpa_ket" => $this->request->getVar("tidak_masuk_tanpa_ket"),
                "tidak_masuk_sakit" => $this->request->getVar("tidak_masuk_sakit"),
                "tidak_masuk_kerja" => $this->request->getVar("tidak_masuk_kerja"),
                "bentuk_pembinaan" => $this->request->getVar("bentuk_pembinaan"),
                "keterangan" => $this->request->getVar("keterangan"),
            ];
            $kedisiplinanModel->updateKedisiplinan($kedisiplinan_id, $data);
            $session->setFlashdata("msg", "Data kedisiplinan berhasil diperbarui");
            $session->setFlashdata("msg_type", "success");
        } else {
            $session->setFlashdata("msg", implode(' | ', $this->validator->getErrors()));
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/input_kedisiplinan"));
    }

    public function deleteKedisiplinan($id = null)
    {
        $kedisiplinanModel = new KelolaDisiplinModel();
        $session = session();
        $kedisiplinan = $kedisiplinanModel->getKedisiplinanById($id);
        if ($kedisiplinan && $kedisiplinan["created_by"] == $session->get("user_id")) {
            $kedisiplinanModel->deleteKedisiplinan($id);
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(["success" => true]);
            }
            $session->setFlashdata("msg", "Data kedisiplinan berhasil dihapus");
            $session->setFlashdata("msg_type", "success");
        } else {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(["success" => false, "message" => "Data tidak ditemukan atau tidak ada izin."]);
            }
            $session->setFlashdata("msg", "Data kedisiplinan tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.");
            $session->setFlashdata("msg_type", "danger");
        }
        return redirect()->to(base_url("user/inputdisiplin"));
    }

    public function hapusKedisiplinanPeriode()
    {
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $user_id = session()->get('user_id');
        if (!$bulan || !$tahun) {
            return redirect()->back()->with('msg', 'Parameter tidak valid!')->with('msg_type', 'danger');
        }
        $kedisiplinanModel = new KelolaDisiplinModel();
        $deleted = $kedisiplinanModel->deleteKedisiplinanPeriode($bulan, $tahun, $user_id);
        if ($deleted) {
            return redirect()->back()->with('msg', 'Data kedisiplinan bulan tersebut berhasil dihapus.')->with('msg_type', 'success');
        } else {
            return redirect()->back()->with('msg', 'Tidak ada data yang dihapus atau terjadi kesalahan.')->with('msg_type', 'danger');
        }
    }

    public function saveKedisiplinanBatch()
    {
        $kedisiplinanModel = new \App\Models\KedisiplinanModel();
        $session = session();
        $data_json = $this->request->getPost('data_json');
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $user_id = $session->get('user_id');

        if (!$data_json || !$bulan || !$tahun) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak lengkap!']);
        }

        $dataArr = json_decode($data_json, true);
        if (!is_array($dataArr)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Format data tidak valid!']);
        }

        foreach ($dataArr as $i => $row) {
            if (empty($row['pegawai_id']))
                continue;
            $data = [
                'pegawai_id' => $row['pegawai_id'],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'terlambat' => $row['terlambat'] ?? 0,
                'tidak_absen_masuk' => $row['tidak_absen_masuk'] ?? 0,
                'pulang_awal' => $row['pulang_awal'] ?? 0,
                'tidak_absen_pulang' => $row['tidak_absen_pulang'] ?? 0,
                'keluar_tidak_izin' => $row['keluar_tidak_izin'] ?? 0,
                'tidak_masuk_tanpa_ket' => $row['tidak_masuk_tanpa_ket'] ?? 0,
                'tidak_masuk_sakit' => $row['tidak_masuk_sakit'] ?? 0,
                'tidak_masuk_kerja' => $row['tidak_masuk_kerja'] ?? 0,
                'bentuk_pembinaan' => $row['bentuk_pembinaan'] ?? '',
                'keterangan' => $row['keterangan'] ?? '',
                'created_by' => $user_id,
            ];
            $existing = $kedisiplinanModel->where([
                'pegawai_id' => $row['pegawai_id'],
                'bulan' => $bulan,
                'tahun' => $tahun
            ])->first();
            if ($existing) {
                $kedisiplinanModel->update($existing['id'], $data);
            } else {
                $kedisiplinanModel->insert($data);
            }
        }
        return $this->response->setJSON(['success' => true]);
    }

    private function getNotifCount()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
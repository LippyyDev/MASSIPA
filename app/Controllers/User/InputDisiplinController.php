<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\InputDisiplinModel;
use App\Models\PegawaiModel;
use App\Models\RiwayatMutasiModel;

class InputDisiplinController extends BaseController
{
    public function inputKedisiplinanTabel()
    {
        helper('app');
        $pegawaiModel = new PegawaiModel();
        $kedisiplinanModel = new InputDisiplinModel();
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $filter_bulan = $this->request->getVar('bulan') ?? date('n');
        $filter_tahun = $this->request->getVar('tahun') ?? date('Y');
        $filter_jabatan = $this->request->getVar('jabatan') ?? [];
        if (!is_array($filter_jabatan)) {
            $filter_jabatan = [$filter_jabatan];
        }
        $filter_jabatan = array_filter($filter_jabatan); // Hapus nilai kosong
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
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
        $total_pegawai = 0;
        if ($pegawai_ids) {
            $total_pegawai = count($pegawai_ids);
            $pegawai_query = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif');
            
            // Filter berdasarkan jabatan jika ada
            if (!empty($filter_jabatan)) {
                $pegawai_query->whereIn('jabatan', $filter_jabatan);
            }
            
            $data["pegawai_list"] = $pegawai_query->orderBy('nama', 'ASC')->findAll();
        }
        $total_page = $total_pegawai > 0 ? ceil($total_pegawai / $perPage) : 1;
        $data['page'] = $page;
        $data['total_page'] = $total_page;
        $pegawai_ids = array_column($data["pegawai_list"], 'id');
        $kedisiplinan_map = [];
        if (!empty($pegawai_ids)) {
            $kedisiplinan = $kedisiplinanModel->getKedisiplinanByPegawaiIds($pegawai_ids, $filter_bulan, $filter_tahun);
            foreach ($kedisiplinan as $row) {
                $kedisiplinan_map[$row['pegawai_id']] = $row;
            }
        }
        // Ambil daftar jabatan yang tersedia untuk filter
        $jabatan_list = [];
        if ($pegawai_ids) {
            $jabatan_list = $pegawaiModel->select('jabatan')
                ->whereIn('id', $pegawai_ids)
                ->where('status', 'aktif')
                ->distinct()
                ->orderBy('jabatan', 'ASC')
                ->findColumn('jabatan');
            $jabatan_list = array_filter($jabatan_list); // Hapus nilai kosong
        }
        
        $data = [
            'pegawai_list' => $data["pegawai_list"],
            'filter_bulan' => $filter_bulan,
            'filter_tahun' => $filter_tahun,
            'filter_jabatan' => $filter_jabatan,
            'jabatan_list' => $jabatan_list,
            'kedisiplinan_map' => $kedisiplinan_map,
            'page' => $page,
            'total_page' => $total_page,
        ];
        $data['notif_count'] = $this->getNotifCount();
        $data['active'] = 'user/kedisiplinan';
        echo view('user/InputDisiplin', $data);
    }

    public function saveKedisiplinanTabel()
    {
        $kedisiplinanModel = new InputDisiplinModel();
        $session = session();
        $pegawai_ids = $this->request->getPost('pegawai_id');
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $terlambat = $this->request->getPost('terlambat');
        $tidak_absen_masuk = $this->request->getPost('tidak_absen_masuk');
        $pulang_awal = $this->request->getPost('pulang_awal');
        $tidak_absen_pulang = $this->request->getPost('tidak_absen_pulang');
        $keluar_tidak_izin = $this->request->getPost('keluar_tidak_izin');
        $tidak_masuk_tanpa_ket = $this->request->getPost('tidak_masuk_tanpa_ket');
        $tidak_masuk_sakit = $this->request->getPost('tidak_masuk_sakit');
        $tidak_masuk_kerja = $this->request->getPost('tidak_masuk_kerja');
        $bentuk_pembinaan = $this->request->getPost('bentuk_pembinaan');
        $keterangan = $this->request->getPost('keterangan');
        $count = count($pegawai_ids);
        for ($i = 0; $i < $count; $i++) {
            $data = [
                'pegawai_id' => $pegawai_ids[$i],
                'bulan' => $bulan,
                'tahun' => $tahun,
                'terlambat' => $terlambat[$i] ?? 0,
                'tidak_absen_masuk' => $tidak_absen_masuk[$i] ?? 0,
                'pulang_awal' => $pulang_awal[$i] ?? 0,
                'tidak_absen_pulang' => $tidak_absen_pulang[$i] ?? 0,
                'keluar_tidak_izin' => $keluar_tidak_izin[$i] ?? 0,
                'tidak_masuk_tanpa_ket' => $tidak_masuk_tanpa_ket[$i] ?? 0,
                'tidak_masuk_sakit' => $tidak_masuk_sakit[$i] ?? 0,
                'tidak_masuk_kerja' => $tidak_masuk_kerja[$i] ?? 0,
                'bentuk_pembinaan' => $bentuk_pembinaan[$i] ?? '',
                'keterangan' => $keterangan[$i] ?? '',
                'created_by' => $session->get('user_id'),
            ];
            $kedisiplinanModel->saveKedisiplinanTabel($data);
        }
        $session->setFlashdata('msg', 'Data kedisiplinan berhasil disimpan!');
        $session->setFlashdata('msg_type', 'success');
        return redirect()->to(base_url('user/inputdisiplin?bulan=' . $bulan . '&tahun=' . $tahun));
    }

    public function saveKedisiplinanTabelSemua()
    {
        $kedisiplinanModel = new InputDisiplinModel();
        $session = session();
        $data_json = $this->request->getPost('data_json');
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun');
        $data_array = json_decode($data_json, true);
        if (!$data_array || !is_array($data_array)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid!']);
        }
        $kedisiplinanModel->saveKedisiplinanTabelBatch($data_array, $bulan, $tahun, $session->get('user_id'));
        return $this->response->setJSON(['success' => true]);
    }

    public function getPegawaiKedisiplinanAjax()
    {
        $pegawaiModel = new PegawaiModel();
        $kedisiplinanModel = new InputDisiplinModel();
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $filter_bulan = $this->request->getGet('bulan') ?? date('n');
        $filter_tahun = $this->request->getGet('tahun') ?? date('Y');
        $filter_jabatan = $this->request->getGet('jabatan') ?? [];
        if (!is_array($filter_jabatan)) {
            $filter_jabatan = [$filter_jabatan];
        }
        $filter_jabatan = array_filter($filter_jabatan); // Hapus nilai kosong
        
        $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_mulai <=', $periode_akhir)
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', $periode_akhir)
            ->groupEnd()
            ->findColumn('pegawai_id');
        $pegawai_list = [];
        if ($pegawai_ids) {
            $pegawai_query = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif');
            
            // Filter berdasarkan jabatan jika ada
            if (!empty($filter_jabatan)) {
                $pegawai_query->whereIn('jabatan', $filter_jabatan);
            }
            
            $pegawai_list = $pegawai_query->orderBy('nama', 'ASC')->findAll();
        }
        $pegawai_ids = array_column($pegawai_list, 'id');
        $kedisiplinan_map = [];
        if (!empty($pegawai_ids)) {
            $kedisiplinan = $kedisiplinanModel->getKedisiplinanByPegawaiIds($pegawai_ids, $filter_bulan, $filter_tahun);
            foreach ($kedisiplinan as $row) {
                $kedisiplinan_map[$row['pegawai_id']] = $row;
            }
        }
        return $this->response->setJSON([
            'pegawai_list' => $pegawai_list,
            'kedisiplinan_map' => $kedisiplinan_map
        ]);
    }

    private function getNotifCount()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
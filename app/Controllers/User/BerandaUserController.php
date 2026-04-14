<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\BerandaUserModel;
use App\Models\PegawaiModel;
use App\Models\KedisiplinanModel;
use App\Models\LaporanFileModel;
use App\Models\NotifikasiModel;
use App\Models\SatkerModel;
use App\Models\RiwayatMutasiModel;
use App\Models\User\KelolaHukumanDisiplinUserModel;

class BerandaUserController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'session', 'app']);
    }

    public function dashboard()
    {
        $pegawaiModel = new PegawaiModel();
        $kedisiplinanModel = new KedisiplinanModel();
        $laporanFileModel = new LaporanFileModel();
        $notifikasiModel = new NotifikasiModel();
        $user = (new \App\Models\UserModel())->find(session()->get("user_id"));
        $satker_id = $user['satker_id'];
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->where('tanggal_selesai', null)
            ->findColumn('pegawai_id');
        $pegawai_count = 0;
        if ($pegawai_ids) {
            $pegawai_count = $pegawaiModel->whereIn('id', $pegawai_ids)->countAllResults();
        }
        $data["pegawai_count"] = $pegawai_count;
        $data["kedisiplinan_count"] = $kedisiplinanModel->where("created_by", session()->get("user_id"))->countAllResults();
        $data["laporan_count"] = $laporanFileModel->where("created_by", session()->get("user_id"))->where("is_hidden_by_user", 0)->countAllResults();
        
        // Get hukuman count for the current user's satker
        $hukumanModel = new KelolaHukumanDisiplinUserModel();
        $hukuman_count = 0;
        if ($pegawai_ids) {
            $hukuman_count = $hukumanModel->whereIn('pegawai_id', $pegawai_ids)->countAllResults();
        }
        $data["hukuman_count"] = $hukuman_count;
        $data["notif_count"] = $notifikasiModel->where("user_id", session()->get("user_id"))->where("is_read", 0)->countAllResults();
        
        // Data tren realtime untuk chart (12 bulan terakhir)
        $pegawai_trend = [];
        $kedisiplinan_trend = [];
        $laporan_trend = [];
        $hukuman_trend = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $bulan = date('m', strtotime("-{$i} months"));
            $tahun = date('Y', strtotime("-{$i} months"));
            
            // Pegawai trend (total pegawai aktif per bulan)
            $pegawai_trend[] = $pegawai_ids ? $pegawaiModel->whereIn('id', $pegawai_ids)->countAllResults() : 0;
            
            // Kedisiplinan trend (data kedisiplinan per bulan)
            $kedisiplinan_trend[] = $kedisiplinanModel
                ->where("created_by", session()->get("user_id"))
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->countAllResults();
            
            // Laporan trend (laporan per bulan)
            $laporan_trend[] = $laporanFileModel
                ->where("created_by", session()->get("user_id"))
                ->where("is_hidden_by_user", 0)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->countAllResults();
            
            // Hukuman trend (hukuman per bulan)
            $hukuman_trend[] = $pegawai_ids ? $hukumanModel
                ->whereIn('pegawai_id', $pegawai_ids)
                ->where("DATE_FORMAT(tanggal_mulai, '%m')", $bulan)
                ->where("DATE_FORMAT(tanggal_mulai, '%Y')", $tahun)
                ->countAllResults() : 0;
        }
        
        $data["pegawai_trend"] = $pegawai_trend;
        $data["kedisiplinan_trend"] = $kedisiplinan_trend;
        $data["laporan_trend"] = $laporan_trend;
        $data["hukuman_trend"] = $hukuman_trend;
        $laporan_terbaru = $laporanFileModel->where("created_by", session()->get("user_id"))
            ->where("is_hidden_by_user", 0)
            ->orderBy("created_at", "DESC")
            ->limit(5)
            ->findAll();
        $data["laporan_terbaru"] = $laporan_terbaru;
        $perPage = 5;
        $currentPage = $this->request->getGet('page') ? (int) $this->request->getGet('page') : 1;
        $offset = ($currentPage - 1) * $perPage;
        $pegawai_list = [];
        if ($pegawai_ids) {
            $pegawai_list = $pegawaiModel->select("id, nama, jabatan")
                ->whereIn("id", $pegawai_ids)
                ->orderBy("nama", "ASC")
                ->limit($perPage, $offset)
                ->findAll();
        }
        $data["pegawai_list"] = $pegawai_list;
        $totalPegawai = $pegawai_ids ? count($pegawai_ids) : 0;
        $data["total_pages"] = $perPage > 0 ? ceil($totalPegawai / $perPage) : 1;
        $data["current_page"] = $currentPage;
        $data['notif_count'] = $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
        
        // Ambil data hukuman disiplin untuk user satker ini
        $userModel = new \App\Models\UserModel();
        $user_ids_satker = $userModel->where('satker_id', $satker_id)->findColumn('id');
        
        // Ambil semua hukuman disiplin yang:
        // 1. Diajukan oleh user satker ini
        // 2. Atau pegawai yang pada tanggal mulai hukuman, mutasinya masih di satker ini
        $list_hukuman = $hukumanModel
            ->select('hukuman_disiplin.*, pegawai.nama, pegawai.jabatan')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->orderBy('hukuman_disiplin.tanggal_mulai', 'DESC')
            ->findAll();
            
        $filtered_hukuman = array_filter($list_hukuman, function($row) use ($user_ids_satker, $satker_id, $riwayatMutasiModel) {
            // 1. Diajukan oleh user satker ini
            if (in_array($row['user_id'], $user_ids_satker)) return true;
            // 2. Atau pegawai yang pada tanggal mulai hukuman, mutasinya masih di satker ini
            $mutasi = $riwayatMutasiModel
                ->where('pegawai_id', $row['pegawai_id'])
                ->where('tanggal_mulai <=', $row['tanggal_mulai'])
                ->groupStart()
                    ->where('tanggal_selesai IS NULL')
                    ->orWhere('tanggal_selesai >', $row['tanggal_mulai'])
                ->groupEnd()
                ->orderBy('tanggal_mulai', 'DESC')
                ->first();
            if ($mutasi && $mutasi['satker_id'] == $satker_id) return true;
            return false;
        });
        
        $data['list_hukuman'] = array_values($filtered_hukuman);
        $tahun = date('Y');
        $mapping = [
            't'   => 'terlambat',
            'tam' => 'tidak_absen_masuk',
            'pa'  => 'pulang_awal',
            'tap' => 'tidak_absen_pulang',
            'kti' => 'keluar_tidak_izin',
            'tk'  => 'tidak_masuk_tanpa_ket',
            'tms' => 'tidak_masuk_sakit',
            'tmk' => 'tidak_masuk_kerja',
        ];
        $grafik_pegawai_kategori = [
            'labels' => ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
        ];
        
        // Data untuk chart bar (total pegawai tidak patuh per bulan)
        $total_pegawai_tidak_patuh_per_bulan = [];
        
        foreach ($mapping as $kategori => $kolom) {
            $grafik_pegawai_kategori[$kategori] = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                // Query untuk data real dari database
                $builder = $kedisiplinanModel
                    ->where('created_by', session()->get("user_id"))
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->where("$kolom >", 0);
                $jumlah = $builder->countAllResults();
                $grafik_pegawai_kategori[$kategori][] = $jumlah;
                
                // Menambahkan ke total per bulan
                if (!isset($total_pegawai_tidak_patuh_per_bulan[$bulan - 1])) {
                    $total_pegawai_tidak_patuh_per_bulan[$bulan - 1] = 0;
                }
                $total_pegawai_tidak_patuh_per_bulan[$bulan - 1] += $jumlah;
            }
        }
        
        // Hanya gunakan data real, tidak ada data dummy
        
        $data['total_pegawai_tidak_patuh_per_bulan'] = $total_pegawai_tidak_patuh_per_bulan;
        $data['grafik_pegawai_kategori'] = $grafik_pegawai_kategori;
        
        // Ambil data status disiplin bulan ini untuk user satker ini
        $bulan_ini = date('n'); // Bulan saat ini (1-12)
        $tahun_ini = date('Y');
        
        // Ambil semua pegawai aktif di satker ini
        $pegawai_aktif = [];
        if ($pegawai_ids) {
            $pegawai_aktif = $pegawaiModel->select('id, nama, nip, pangkat, golongan, jabatan')
                ->whereIn('id', $pegawai_ids)
                ->where('status', 'aktif')
                ->orderBy('nama', 'ASC')
                ->findAll();
        }
        
        // Ambil data kedisiplinan bulan ini
        $status_disiplin_bulan_ini = [];
        foreach ($pegawai_aktif as $pegawai) {
            $kedisiplinan = $kedisiplinanModel
                ->where('pegawai_id', $pegawai['id'])
                ->where('bulan', $bulan_ini)
                ->where('tahun', $tahun_ini)
                ->first();
            
            $status_disiplin_bulan_ini[] = [
                'nama' => $pegawai['nama'],
                'nip' => $pegawai['nip'],
                'jabatan' => $pegawai['jabatan'],
                'has_disiplin' => !empty($kedisiplinan),
                'status' => !empty($kedisiplinan) ? '✓' : '-'
            ];
        }
        
        $data['status_disiplin_bulan_ini'] = $status_disiplin_bulan_ini;
        

        echo view("user/BerandaUser", $data);
    }
} 
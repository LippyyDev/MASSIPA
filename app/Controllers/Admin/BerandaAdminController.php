<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\BerandaAdminModel;
use App\Models\UserModel;
use App\Models\PegawaiModel;
use App\Models\LaporanFileModel;
use App\Models\NotifikasiModel;
use App\Models\SatkerModel;
use App\Models\Admin\KelolaHukumanDisiplinAdminModel;
use App\Models\Admin\KelolaLaporanModel;

class BerandaAdminController extends BaseController
{
    public function dashboard()
    {
        helper('app');
        $userModel = new UserModel();
        $pegawaiModel = new PegawaiModel();
        $laporanFileModel = new LaporanFileModel();
        $notifikasiModel = new NotifikasiModel();
        $satkerModel = new SatkerModel();

        $data["user_count"] = $userModel->countAllResults();
        $data["pegawai_count"] = $pegawaiModel->countAllResults();
        // Total pending: hitung laporan dengan status 'terkirim' atau 'dilihat' (belum di-approve atau di-reject)
        $data["laporan_count"] = $laporanFileModel->whereIn('status', ['terkirim', 'dilihat'])->countAllResults();
        $data["notif_count"] = $notifikasiModel->where("user_id", session()->get("user_id"))->where("is_read", 0)->countAllResults();
        $data["satker_count"] = $satkerModel->countAllResults();
        $data["arsip_count"] = $laporanFileModel->where('status', 'diterima')->countAllResults();

        $laporan_terbaru = $laporanFileModel->select("laporan_file.*, users.nama_lengkap")
            ->join("users", "users.id = laporan_file.created_by")
            ->whereIn('laporan_file.status', ['terkirim', 'dilihat'])
            ->orderBy("created_at", "DESC")
            ->limit(5)
            ->findAll();
        $data["laporan_terbaru"] = $laporan_terbaru;

        $status_counts_raw = $laporanFileModel->select("status, COUNT(*) as count")
            ->groupBy("status")
            ->findAll();
        $status_counts = [];
        foreach ($status_counts_raw as $row) {
            $status_counts[$row["status"]] = $row["count"];
        }
        $data["status_counts"] = $status_counts;

        // Ambil 10 hukuman disiplin terbaru untuk dashboard
        $hukumanModel = new KelolaHukumanDisiplinAdminModel();
        $list_hukuman = $hukumanModel
            ->select('hukuman_disiplin.id, pegawai.nama, pegawai.jabatan, hukuman_disiplin.tanggal_mulai, hukuman_disiplin.tanggal_berakhir, hukuman_disiplin.status')
            ->join('pegawai', 'pegawai.id = hukuman_disiplin.pegawai_id', 'left')
            ->where('tanggal_berakhir > tanggal_mulai')
            ->orderBy('tanggal_berakhir', 'DESC')
            ->findAll(10, 0);
        $data['list_hukuman'] = $list_hukuman;

        // Statistik bulanan 12 bulan terakhir
        $bulan_labels = [];
        $satker_trend = [];
        $pegawai_trend = [];
        $pending_trend = [];
        $arsip_trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = date('m', strtotime("-{$i} months"));
            $tahun = date('Y', strtotime("-{$i} months"));
            $bulan_labels[] = date('M', strtotime("$tahun-$bulan-01"));
            // Satker: total satker per bulan (asumsi tidak ada histori, pakai total saat ini)
            $satker_trend[] = $satkerModel->countAllResults();
            // Pegawai: total pegawai aktif per bulan (asumsi tidak ada histori, pakai total saat ini)
            $pegawai_trend[] = $pegawaiModel->where('status', 'aktif')->countAllResults();
            // Laporan menunggu approve per bulan
            $pending_trend[] = $laporanFileModel->where('status !=', 'diterima')->where('bulan', $bulan)->where('tahun', $tahun)->countAllResults();
            // Arsip per bulan
            $arsip_trend[] = $laporanFileModel->where('status', 'diterima')->where('bulan', $bulan)->where('tahun', $tahun)->countAllResults();
        }
        $data['bulan_labels'] = $bulan_labels;
        $data['satker_trend'] = $satker_trend;
        $data['pegawai_trend'] = $pegawai_trend;
        $data['pending_trend'] = $pending_trend;
        $data['arsip_trend'] = $arsip_trend;

        // Chart laporan masuk per bulan tahun ini
        $tahun_ini = date('Y');
        $laporan_per_bulan = [];
        $result = $laporanFileModel
            ->select('bulan, COUNT(*) as jumlah')
            ->where('tahun', $tahun_ini)
            ->groupBy('bulan')
            ->orderBy('bulan', 'ASC')
            ->findAll();
        foreach ($result as $row) {
            $laporan_per_bulan[(int)$row['bulan']] = (int)$row['jumlah'];
        }
        $data['laporan_per_bulan'] = $laporan_per_bulan;

        // Status Disiplin Bulan Ini
        $bulan_ini = date('m');
        $tahun_ini = date('Y');
        
        // Ambil semua satker
        $all_satker = $satkerModel->select('id, nama')->findAll();
        $status_disiplin_bulan_ini = [];
        
        foreach ($all_satker as $satker) {
            // Ambil user yang terkait dengan satker ini
            $users_in_satker = $userModel->where('satker_id', $satker['id'])->findAll();
            
            $has_disiplin_approved = false;
            $has_apel_approved = false;
            $has_disiplin_pending = false;
            $has_apel_pending = false;
            
            foreach ($users_in_satker as $user) {
                // Cek laporan untuk bulan ini
                $laporan_bulan_ini = $laporanFileModel
                    ->where('created_by', $user['id'])
                    ->where('bulan', $bulan_ini)
                    ->where('tahun', $tahun_ini)
                    ->findAll();
                
                foreach ($laporan_bulan_ini as $laporan) {
                    $status = $laporan['status'];
                    $kategori = $laporan['kategori'];
                    
                    if ($kategori == 'Laporan Disiplin') {
                        if ($status == 'diterima') {
                            $has_disiplin_approved = true;
                        } elseif ($status == 'dilihat' || $status == 'terkirim') {
                            $has_disiplin_pending = true;
                        }
                    } elseif ($kategori == 'Laporan Apel') {
                        if ($status == 'diterima') {
                            $has_apel_approved = true;
                        } elseif ($status == 'dilihat' || $status == 'terkirim') {
                            $has_apel_pending = true;
                        }
                    }
                }
            }
            
            $status_disiplin_bulan_ini[] = [
                'nama_satker' => $satker['nama'],
                'has_disiplin_approved' => $has_disiplin_approved,
                'has_apel_approved' => $has_apel_approved,
                'has_disiplin_pending' => $has_disiplin_pending,
                'has_apel_pending' => $has_apel_pending
            ];
        }
        
        $data['status_disiplin_bulan_ini'] = $status_disiplin_bulan_ini;

        echo view("admin/BerandaAdmin", $data);
    }
} 
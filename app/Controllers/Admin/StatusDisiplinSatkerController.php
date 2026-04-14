<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\StatusDisiplinSatkerModel;
use App\Models\SatkerModel;
use App\Models\UserModel;

class StatusDisiplinSatkerController extends BaseController
{
    protected $laporanModel;
    protected $satkerModel;
    protected $userModel;

    public function __construct()
    {
        $this->laporanModel = new StatusDisiplinSatkerModel();
        $this->satkerModel = new SatkerModel();
        $this->userModel = new UserModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function rekapKedisiplinan()
    {
        $laporanFileModel = $this->laporanModel;
        $satkerModel = $this->satkerModel;
        $userModel = $this->userModel;
        $session = session();
        $tahun_dipilih = $this->request->getGet('tahun') ?? date('Y');
        $filter_user = $this->request->getGet('user_id');
        $filter_satker = $this->request->getGet('satker_id');
        $daftar_tahun_raw = $laporanFileModel->distinct()->select('tahun')->orderBy('tahun', 'DESC')->findAll();
        $daftar_tahun = array_column($daftar_tahun_raw, 'tahun');
        if (!in_array(date('Y'), $daftar_tahun)) {
            array_unshift($daftar_tahun, date('Y'));
        }
        $satker_list = $satkerModel->orderBy('nama', 'ASC')->findAll();
        $user_satker = $userModel->where('role', 'user')->where('satker_id IS NOT NULL')->findAll();
        $satker_to_user = [];
        foreach ($user_satker as $u) {
            $satker_to_user[$u['satker_id']] = $u['id'];
        }
        $users_data = [];
        foreach ($satker_list as $satker) {
            if ($filter_user && $satker['id'] != $filter_user) {
                continue;
            }
            if ($filter_satker && $satker['id'] != $filter_satker) {
                continue;
            }
            $users_data[] = [
                'satker_id' => $satker['id'],
                'nama_satker' => $satker['nama'],
                'user_nama' => $satker['nama'],
                'user_id' => isset($satker_to_user[$satker['id']]) ? $satker_to_user[$satker['id']] : null
            ];
        }
        $laporan_raw = $laporanFileModel->select('id, created_by, bulan, status, kategori, file_path, link_drive, nama_laporan')
            ->where('tahun', $tahun_dipilih)
            ->findAll();
        $laporan_data = [];
        foreach ($laporan_raw as $row) {
            $user_id = $row['created_by'];
            $bulan = $row['bulan'];
            $status = $row['status'];
            $kategori = $row['kategori'];
            
            // Initialize array if not exists
            if (!isset($laporan_data[$user_id][$bulan])) {
                $laporan_data[$user_id][$bulan] = [
                    'reports' => []
                ];
            }
            
            // Add report with its status and category, file, and link
            $laporan_data[$user_id][$bulan]['reports'][] = [
                'id' => $row['id'],
                'status' => $status,
                'kategori' => $kategori,
                'file_path' => $row['file_path'],
                'link_drive' => $row['link_drive'],
                'nama_laporan' => $row['nama_laporan']
            ];
        }
        $nama_bulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        $data = [
            'satker_list' => $satker_list,
            'daftar_tahun' => $daftar_tahun,
            'tahun_dipilih' => $tahun_dipilih,
            'users_data' => $users_data,
            'laporan_data' => $laporan_data,
            'filter_user' => $filter_user,
            'filter_satker' => $filter_satker,
            'nama_bulan' => $nama_bulan,
        ];
        return view('admin/StatusDisiplinSatker', $data);
    }
} 
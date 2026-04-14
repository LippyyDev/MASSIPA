<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\KelolaDisiplinModel;

class KelolaDisiplinController extends BaseController
{
    protected $kelolaDisiplinModel;

    public function __construct()
    {
        $this->kelolaDisiplinModel = new KelolaDisiplinModel();
        helper(['form', 'url', 'session', 'app_helper']);
    }

    /**
     * Halaman utama Kelola Disiplin
     */
    public function index()
    {
        $tahun_dipilih = $this->request->getGet('tahun') ?? date('Y');
        $bulan_dipilih = $this->request->getGet('bulan') ?? date('m');
        $satker_dipilih = $this->request->getGet('satker') ?? '';
        $jenis_pelanggaran_dipilih = $this->request->getGet('pelanggaran') ?? '';

        $daftar_tahun_raw = $this->kelolaDisiplinModel->getDistinctYears();
        $daftar_tahun = array_column($daftar_tahun_raw, 'tahun');
        if (!in_array(date('Y'), $daftar_tahun)) {
            array_unshift($daftar_tahun, date('Y'));
        }
        sort($daftar_tahun); // Ensure years are sorted ascending

        $nama_bulan = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        // Ambil daftar satker
        $daftar_satker = $this->kelolaDisiplinModel->getDistinctSatkers();

        $data = [
            'tahun_dipilih' => $tahun_dipilih,
            'bulan_dipilih' => $bulan_dipilih,
            'satker_dipilih' => $satker_dipilih,
            'jenis_pelanggaran_dipilih' => $jenis_pelanggaran_dipilih,
            'daftar_tahun' => $daftar_tahun,
            'daftar_satker' => $daftar_satker,
            'nama_bulan' => $nama_bulan,
        ];

        return view('admin/KelolaDisiplin', $data);
    }

    /**
     * AJAX endpoint untuk DataTables
     */
    public function getDataAjax()
    {
        // Validasi request AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access denied']);
        }

        // Ambil parameter dari DataTables
        $draw = $this->request->getPost('draw') ?? 1;
        $start = $this->request->getPost('start') ?? 0;
        $length = $this->request->getPost('length') ?? 10;
        $search = $this->request->getPost('search');
        $searchValue = (!empty($search) && isset($search['value'])) ? $search['value'] : '';
        
        // Ambil parameter filter
        $bulan = $this->request->getPost('bulan');
        $tahun = $this->request->getPost('tahun') ?? date('Y');
        $satker = $this->request->getPost('satker');
        $jenisPelanggaran = $this->request->getPost('pelanggaran');
        
        // Pastikan bulan null jika empty
        $bulan = !empty($bulan) ? (int)$bulan : null;
        
        // Pastikan tahun adalah integer
        $tahun = !empty($tahun) ? (int)$tahun : (int)date('Y');
        
        // Pastikan satker dan jenis pelanggaran null jika empty
        $satker = !empty($satker) ? $satker : null;
        $jenisPelanggaran = !empty($jenisPelanggaran) ? $jenisPelanggaran : null;
        
        // Ambil parameter ordering
        $order = $this->request->getPost('order');
        $orderColumnIndex = (!empty($order) && isset($order[0]['column'])) ? $order[0]['column'] : 0;
        $orderDirection = (!empty($order) && isset($order[0]['dir'])) ? $order[0]['dir'] : 'asc';
        
        // Map kolom untuk ordering
        $columns = [
            0 => 'p.nama',           // Nama
            1 => 'p.jabatan',        // Jabatan  
            2 => 's.nama',           // Satker
            3 => 'k.terlambat',      // T
            4 => 'k.tidak_absen_masuk', // TAM
            5 => 'k.pulang_awal',    // PA
            6 => 'k.tidak_absen_pulang', // TAP
            7 => 'k.keluar_tidak_izin',  // KTI
            8 => 'k.tidak_masuk_tanpa_ket', // TK
            9 => 'k.tidak_masuk_sakit',     // TMS
            10 => 'k.tidak_masuk_kerja',    // TMK
            11 => 'total_pelanggaran'       // Total
        ];
        
        $orderBy = $columns[$orderColumnIndex] ?? 'p.nama';
        
        try {
            // Ambil data dari model
            $result = $this->kelolaDisiplinModel->getDisiplinData(
                $bulan,
                $tahun,
                $start,
                $length,
                $searchValue,
                $orderBy,
                $orderDirection,
                $satker,
                $jenisPelanggaran
            );

            // Format data untuk DataTables
            $data = [];
            $no = $start + 1;
            
            foreach ($result['data'] as $row) {
                $data[] = [
                    'no' => $no++,
                    'nama_pegawai' => esc($row['nama_pegawai']),
                    'jabatan' => esc($row['jabatan'] ?? '-'),
                    'nip' => esc($row['nip'] ?? '-'),
                    'pangkat' => esc($row['pangkat'] ?? '-'),
                    'nama_satker' => esc($row['nama_satker']),
                    'T' => (int)$row['T'],
                    'TAM' => (int)$row['TAM'],
                    'PA' => (int)$row['PA'],
                    'TAP' => (int)$row['TAP'],
                    'KTI' => (int)$row['KTI'],
                    'TK' => (int)$row['TK'],
                    'TMS' => (int)$row['TMS'],
                    'TMK' => (int)$row['TMK'],
                    'total_pelanggaran' => (int)$row['total_pelanggaran']
                ];
            }

            // Response untuk DataTables
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => $data
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaDisiplinController::getDataAjax: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data'
            ]);
        }
    }
}

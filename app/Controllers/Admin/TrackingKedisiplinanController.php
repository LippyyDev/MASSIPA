<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\TrackingKedisiplinanModel;
use CodeIgniter\HTTP\ResponseInterface;

class TrackingKedisiplinanController extends BaseController
{
    protected $trackingModel;

    public function __construct()
    {
        $this->trackingModel = new TrackingKedisiplinanModel();
    }

    /**
     * Halaman utama tracking kedisiplinan
     */
    public function index()
    {
        $data = [
            'title' => 'Tracking Kedisiplinan Pegawai',
            'page_title' => 'Tracking Kedisiplinan Pegawai',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin/dashboard'],
                ['title' => 'Tracking Kedisiplinan', 'url' => '']
            ]
        ];

        return view('admin/TrackingKedisiplinan', $data);
    }

    /**
     * AJAX: Pencarian pegawai untuk autocomplete
     */
    public function searchPegawaiAjax()
    {
        $query = $this->request->getGet('q');
        $limit = $this->request->getGet('limit') ?? 10;

        if (empty($query) || strlen($query) < 2) {
            return $this->response->setJSON([]);
        }

        try {
            $results = $this->trackingModel->searchPegawai($query, $limit);
            return $this->response->setJSON($results);
        } catch (\Exception $e) {
            log_message('error', 'Error searching pegawai: ' . $e->getMessage());
            return $this->response->setJSON([]);
        }
    }

    /**
     * AJAX: Mendapatkan track record pegawai
     */
    public function getTrackRecordAjax()
    {
        $pegawai_id = $this->request->getGet('pegawai_id');
        $tahun = $this->request->getGet('tahun');

        if (empty($pegawai_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID pegawai tidak valid'
            ]);
        }

        try {
            // Ambil data pegawai
            $pegawai = $this->trackingModel->getPegawaiById($pegawai_id);
            if (!$pegawai) {
                log_message('error', 'Pegawai tidak ditemukan dengan ID: ' . $pegawai_id);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Pegawai tidak ditemukan'
                ]);
            }

            // Ambil track record
            $trackRecord = $this->trackingModel->getTrackRecordPegawai($pegawai_id, $tahun);
            
            // Ambil ringkasan pelanggaran
            $ringkasan = $this->trackingModel->getRingkasanPelanggaran($pegawai_id, $tahun);
            
            // Ambil statistik pelanggaran
            $statistik = $this->trackingModel->getStatistikPelanggaran($pegawai_id, $tahun);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'pegawai' => $pegawai,
                    'track_record' => $trackRecord,
                    'ringkasan' => $ringkasan,
                    'statistik' => $statistik
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting track record: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Mendapatkan daftar tahun tersedia
     */
    public function getTahunTersediaAjax()
    {
        $pegawai_id = $this->request->getGet('pegawai_id');

        try {
            $tahun = $this->trackingModel->getTahunTersedia($pegawai_id);
            return $this->response->setJSON([
                'success' => true,
                'data' => $tahun
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting tahun tersedia: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tahun'
            ]);
        }
    }

    /**
     * AJAX: Export data tracking ke PDF
     */
    public function exportPdfAjax()
    {
        $pegawai_id = $this->request->getVar('pegawai_id');
        $tahun = $this->request->getVar('tahun');

        // Validasi parameter wajib
        if (empty($pegawai_id)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID pegawai wajib diisi!'
            ]);
        }

        try {
            // Ambil data pegawai
            $pegawai = $this->trackingModel->getPegawaiById($pegawai_id);
            if (!$pegawai) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Pegawai tidak ditemukan'
                ]);
            }

            // Ambil track record
            $trackRecord = $this->trackingModel->getTrackRecordPegawai($pegawai_id, $tahun);
            $ringkasan = $this->trackingModel->getRingkasanPelanggaran($pegawai_id, $tahun);
            $statistik = $this->trackingModel->getStatistikPelanggaran($pegawai_id, $tahun);

            // Load library PDF
            $pdf = new \App\Libraries\PDF();
            
            $data = [
                'pegawai' => $pegawai,
                'track_record' => $trackRecord,
                'ringkasan' => $ringkasan,
                'statistik' => $statistik,
                'tahun' => $tahun
            ];

            $filename = 'tracking_kedisiplinan_' . $pegawai['nip'] . '_' . ($tahun ?: 'all') . '.pdf';
            $pdf->generateTrackingKedisiplinan($data, $filename);

            return $this->response->setJSON([
                'success' => true,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error exporting PDF: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengexport PDF'
            ]);
        }
    }
}

<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\Admin\TrackingKedisiplinanModel;
use App\Models\LaporanFileModel;
use App\Models\PegawaiModel;
use App\Models\UserModel;
use App\Controllers\BaseController;
use App\Models\NotifikasiModel;

class ApiController extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        helper(['form', 'url', 'session']);
    }

    public function getLaporan()
    {
        $laporanFileModel = new LaporanFileModel();
        $userModel = new UserModel();

        // Get filter parameters
        $user_id = $this->request->getGet('user_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $status = $this->request->getGet('status');
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        // Build query
        $query = $laporanFileModel
            ->select('laporan_file.*, users.nama_lengkap, users.satker_id')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->where('laporan_file.status', 'diterima');

        if ($user_id) {
            $query->where('laporan_file.created_by', $user_id);
        }
        if ($bulan) {
            $query->where('laporan_file.bulan', $bulan);
        }
        if ($tahun) {
            $query->where('laporan_file.tahun', $tahun);
        }

        $total = $query->countAllResults(false);
        $laporan = $query->orderBy('laporan_file.created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();

        // Format response
        $formatted_laporan = [];
        foreach ($laporan as $row) {
            $formatted_laporan[] = [
                'id' => $row['id'],
                'nama_laporan' => $row['nama_laporan'],
                'bulan' => $row['bulan'],
                'tahun' => $row['tahun'],
                'keterangan' => $row['keterangan'],
                'status' => $row['status'],
                'feedback' => $row['feedback'],
                'file_path' => $row['file_path'],
                'kategori' => $row['kategori'] ?? 'Laporan Disiplin',
                'link_drive' => $row['link_drive'] ?? null,
                'pengirim' => [
                    'id' => $row['created_by'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'satker_id' => $row['satker_id']
                ],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'file_url' => base_url('api/laporan/file/' . $row['id']),
                'link_url' => base_url('api/laporan/link/' . $row['id'])
            ];
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Data laporan berhasil diambil',
            'data' => [
                'laporan' => $formatted_laporan,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'total_pages' => ceil($total / $limit)
                ]
            ]
        ]);
    }

    public function getLaporanById($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID laporan diperlukan');
        }

        $laporanFileModel = new LaporanFileModel();
        $laporan = $laporanFileModel
            ->select('laporan_file.*, users.nama_lengkap, users.satker_id')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->find($id);

        if (!$laporan) {
            return $this->failNotFound('Laporan tidak ditemukan');
        }

        $formatted_laporan = [
            'id' => $laporan['id'],
            'nama_laporan' => $laporan['nama_laporan'],
            'bulan' => $laporan['bulan'],
            'tahun' => $laporan['tahun'],
            'keterangan' => $laporan['keterangan'],
            'status' => $laporan['status'],
            'feedback' => $laporan['feedback'],
            'file_path' => $laporan['file_path'],
            'kategori' => $laporan['kategori'] ?? 'Laporan Disiplin',
            'link_drive' => $laporan['link_drive'] ?? null,
            'pengirim' => [
                'id' => $laporan['created_by'],
                'nama_lengkap' => $laporan['nama_lengkap'],
                'satker_id' => $laporan['satker_id']
            ],
            'created_at' => $laporan['created_at'],
            'updated_at' => $laporan['updated_at'],
            'file_url' => base_url('api/laporan/file/' . $laporan['id']),
            'link_url' => base_url('api/laporan/link/' . $laporan['id'])
        ];

        return $this->respond([
            'status' => 'success',
            'message' => 'Detail laporan berhasil diambil',
            'data' => $formatted_laporan
        ]);
    }

    public function getLaporanFile($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID laporan diperlukan');
        }

        $laporanFileModel = new LaporanFileModel();
        $laporan = $laporanFileModel->find($id);

        if (!$laporan) {
            return $this->failNotFound('Laporan tidak ditemukan');
        }

        // Validasi status laporan
        if (!in_array($laporan['status'], ['diterima', 'disetujui'])) {
            return $this->failForbidden('File hanya dapat diakses jika laporan sudah disetujui.');
        }

        $filePath = WRITEPATH . 'uploads/laporan/' . $laporan['file_path'];

        if (!file_exists($filePath)) {
            return $this->failNotFound('File tidak ditemukan');
        }

        $fileInfo = pathinfo($filePath);
        $mimeType = mime_content_type($filePath);
        $filename = $laporan['nama_laporan'] . '.' . $fileInfo['extension'];
        $fileContent = file_get_contents($filePath);

        return $this->response
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setContentType($mimeType)
            ->setBody($fileContent);
    }

    // API untuk laporan berdasarkan kategori
    public function getLaporanByKategori($kategori = null)
    {
        if (!$kategori) {
            return $this->failValidationError('Kategori diperlukan');
        }

        // Validasi kategori
        if (!in_array($kategori, ['disiplin', 'apel'])) {
            return $this->failNotFound('Kategori tidak valid');
        }

        $kategoriValue = ($kategori == 'disiplin') ? 'Laporan Disiplin' : 'Laporan Apel';

        $laporanFileModel = new LaporanFileModel();
        $userModel = new UserModel();

        // Get filter parameters
        $user_id = $this->request->getGet('user_id');
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        // Build query
        $query = $laporanFileModel
            ->select('laporan_file.*, users.nama_lengkap, users.satker_id')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->where('laporan_file.status', 'diterima')
            ->where('laporan_file.kategori', $kategoriValue);

        if ($user_id) {
            $query->where('laporan_file.created_by', $user_id);
        }
        if ($bulan) {
            $query->where('laporan_file.bulan', $bulan);
        }
        if ($tahun) {
            $query->where('laporan_file.tahun', $tahun);
        }

        $total = $query->countAllResults(false);
        $laporan = $query->orderBy('laporan_file.created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();

        // Format response
        $formatted_laporan = [];
        foreach ($laporan as $row) {
            $formatted_laporan[] = [
                'id' => $row['id'],
                'nama_laporan' => $row['nama_laporan'],
                'bulan' => $row['bulan'],
                'tahun' => $row['tahun'],
                'keterangan' => $row['keterangan'],
                'status' => $row['status'],
                'feedback' => $row['feedback'],
                'file_path' => $row['file_path'],
                'kategori' => $row['kategori'] ?? 'Laporan Disiplin',
                'link_drive' => $row['link_drive'] ?? null,
                'pengirim' => [
                    'id' => $row['created_by'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'satker_id' => $row['satker_id']
                ],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'file_url' => base_url('api/laporan/' . $kategori . '/file/' . $row['id']),
                'link_url' => base_url('api/laporan/' . $kategori . '/link/' . $row['id'])
            ];
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Data laporan ' . $kategoriValue . ' berhasil diambil',
            'data' => [
                'laporan' => $formatted_laporan,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'total_pages' => ceil($total / $limit)
                ]
            ]
        ]);
    }

    // API untuk file berdasarkan kategori
    public function getLaporanFileByKategori($kategori = null, $id = null)
    {
        if (!$kategori || !$id) {
            return $this->failValidationError('Kategori dan ID laporan diperlukan');
        }

        // Validasi kategori
        if (!in_array($kategori, ['disiplin', 'apel'])) {
            return $this->failNotFound('Kategori tidak valid');
        }

        $kategoriValue = ($kategori == 'disiplin') ? 'Laporan Disiplin' : 'Laporan Apel';

        $laporanFileModel = new LaporanFileModel();
        $laporan = $laporanFileModel
            ->where('id', $id)
            ->where('kategori', $kategoriValue)
            ->where('status', 'diterima')
            ->first();

        if (!$laporan) {
            return $this->failNotFound('Laporan tidak ditemukan');
        }

        if (empty($laporan['file_path'])) {
            return $this->failNotFound('File tidak tersedia untuk laporan ini');
        }

        $filePath = WRITEPATH . 'uploads/laporan/' . $laporan['file_path'];

        if (!file_exists($filePath)) {
            return $this->failNotFound('File tidak ditemukan');
        }

        // Download file langsung
        $fileInfo = pathinfo($filePath);
        $mimeType = mime_content_type($filePath);
        $filename = $laporan['nama_laporan'] . '.' . $fileInfo['extension'];
        $fileContent = file_get_contents($filePath);

        return $this->response
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setContentType($mimeType)
            ->setBody($fileContent);
    }

    // API untuk link berdasarkan kategori
    public function getLaporanLinkByKategori($kategori = null, $id = null)
    {
        if (!$kategori || !$id) {
            return $this->failValidationError('Kategori dan ID laporan diperlukan');
        }

        // Validasi kategori
        if (!in_array($kategori, ['disiplin', 'apel'])) {
            return $this->failNotFound('Kategori tidak valid');
        }

        $kategoriValue = ($kategori == 'disiplin') ? 'Laporan Disiplin' : 'Laporan Apel';

        $laporanFileModel = new LaporanFileModel();
        $laporan = $laporanFileModel
            ->where('id', $id)
            ->where('kategori', $kategoriValue)
            ->where('status', 'diterima')
            ->first();

        if (!$laporan) {
            return $this->failNotFound('Laporan tidak ditemukan');
        }

        if (empty($laporan['link_drive'])) {
            return $this->failNotFound('Link drive tidak tersedia untuk laporan ini');
        }

        // Redirect langsung ke link drive
        return redirect()->to($laporan['link_drive']);
    }

    // API untuk link tanpa kategori (semua kategori)
    public function getLaporanLink($id = null)
    {
        if (!$id) {
            return $this->failValidationError('ID laporan diperlukan');
        }

        $laporanFileModel = new LaporanFileModel();
        $laporan = $laporanFileModel
            ->where('id', $id)
            ->where('status', 'diterima')
            ->first();

        if (!$laporan) {
            return $this->failNotFound('Laporan tidak ditemukan');
        }

        if (empty($laporan['link_drive'])) {
            return $this->failNotFound('Link drive tidak tersedia untuk laporan ini');
        }

        // Redirect langsung ke link drive
        return redirect()->to($laporan['link_drive']);
    }

    public function getArsipLaporan()
    {
        $laporanFileModel = new LaporanFileModel();

        // Get filter parameters
        $bulan = $this->request->getGet('bulan');
        $tahun = $this->request->getGet('tahun');
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        // Build query for approved reports only
        $query = $laporanFileModel
            ->select('laporan_file.*, users.nama_lengkap, users.satker_id')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->where('laporan_file.status', 'diterima');

        if ($bulan) {
            $query->where('laporan_file.bulan', $bulan);
        }
        if ($tahun) {
            $query->where('laporan_file.tahun', $tahun);
        }

        $total = $query->countAllResults(false);
        $arsip = $query->orderBy('laporan_file.created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();

        // Format response
        $formatted_arsip = [];
        foreach ($arsip as $row) {
            $formatted_arsip[] = [
                'id' => $row['id'],
                'nama_laporan' => $row['nama_laporan'],
                'bulan' => $row['bulan'],
                'tahun' => $row['tahun'],
                'keterangan' => $row['keterangan'],
                'feedback' => $row['feedback'],
                'file_path' => $row['file_path'],
                'kategori' => $row['kategori'] ?? 'Laporan Disiplin',
                'link_drive' => $row['link_drive'] ?? null,
                'pengirim' => [
                    'id' => $row['created_by'],
                    'nama_lengkap' => $row['nama_lengkap'],
                    'satker_id' => $row['satker_id']
                ],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'file_url' => base_url('api/laporan/file/' . $row['id']),
                'link_url' => base_url('api/laporan/link/' . $row['id'])
            ];
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Data arsip laporan berhasil diambil',
            'data' => [
                'arsip' => $formatted_arsip,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'total_pages' => ceil($total / $limit)
                ]
            ]
        ]);
    }

    public function getPegawai()
    {
        $pegawaiModel = new PegawaiModel();

        $limit = (int) ($this->request->getGet('limit') ?? 25);
        $limit = $limit > 0 ? min($limit, 100) : 25;
        $offset = max((int) ($this->request->getGet('offset') ?? 0), 0);
        $status = $this->request->getGet('status') ?? 'aktif';
        $search = $this->request->getGet('search');
        $sort = $this->request->getGet('sort') ?? 'nama';
        $order = strtoupper($this->request->getGet('order') ?? 'ASC');

        $allowedSorts = [
            'nama' => 'pegawai.nama',
            'nip' => 'pegawai.nip',
            'jabatan' => 'pegawai.jabatan',
            'created_at' => 'pegawai.created_at'
        ];

        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'nama';
        }

        $order = $order === 'DESC' ? 'DESC' : 'ASC';

        $builder = $pegawaiModel
            ->select('pegawai.*, rm.id as riwayat_mutasi_id, rm.tanggal_mulai, rm.tanggal_selesai, s.id as satker_id, s.nama as satker_nama')
            ->join('riwayat_mutasi rm', 'rm.pegawai_id = pegawai.id AND rm.tanggal_selesai IS NULL', 'left')
            ->join('satker s', 's.id = rm.satker_id', 'left');

        if ($status && $status !== 'all') {
            $builder->where('pegawai.status', $status);
        }

        if ($search) {
            $builder->groupStart()
                ->like('pegawai.nama', $search)
                ->orLike('pegawai.nip', $search)
                ->orLike('pegawai.jabatan', $search)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $pegawai = $builder->orderBy($allowedSorts[$sort], $order)
            ->limit($limit, $offset)
            ->findAll();

        $formatted = array_map(fn ($row) => $this->formatPegawaiRecord($row), $pegawai);

        return $this->respond([
            'status' => 'success',
            'message' => 'Data pegawai berhasil diambil',
            'data' => [
                'pegawai' => $formatted,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'total_pages' => $limit > 0 ? ceil($total / $limit) : 1,
                    'status_filter' => $status,
                    'search' => $search
                ]
            ]
        ]);
    }

    public function getPegawaiTracking($nim = null)
    {
        if (!$nim) {
            return $this->failValidationError('NIM pegawai diperlukan');
        }

        $tahun = $this->request->getGet('tahun');
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        $limit = $limit > 0 ? min($limit, 24) : 12;
        $offset = max((int) ($this->request->getGet('offset') ?? 0), 0);

        $pegawaiModel = new PegawaiModel();
        $pegawai = $pegawaiModel
            ->select('pegawai.*, rm.id as riwayat_mutasi_id, rm.tanggal_mulai, rm.tanggal_selesai, s.id as satker_id, s.nama as satker_nama')
            ->join('riwayat_mutasi rm', 'rm.pegawai_id = pegawai.id AND rm.tanggal_selesai IS NULL', 'left')
            ->join('satker s', 's.id = rm.satker_id', 'left')
            ->where('pegawai.nip', $nim)
            ->first();

        if (!$pegawai) {
            return $this->failNotFound('Pegawai tidak ditemukan');
        }

        $trackingModel = new TrackingKedisiplinanModel();
        $trackRecord = $trackingModel->getTrackRecordPegawai($pegawai['id'], $tahun);
        $ringkasan = $trackingModel->getRingkasanPelanggaran($pegawai['id'], $tahun);
        $statistik = $trackingModel->getStatistikPelanggaran($pegawai['id'], $tahun);
        $tahunTersedia = $trackingModel->getTahunTersedia($pegawai['id']);

        $totalTrack = count($trackRecord);
        $paginatedTrack = array_slice($trackRecord, $offset, $limit);

        return $this->respond([
            'status' => 'success',
            'message' => 'Data tracking kedisiplinan pegawai berhasil diambil',
            'data' => [
                'pegawai' => $this->formatPegawaiRecord($pegawai),
                'statistik' => $this->formatStatistikPelanggaran($statistik),
                'tahun_tersedia' => array_map(
                    fn ($item) => isset($item['tahun']) ? (int) $item['tahun'] : null,
                    $tahunTersedia
                ),
                'track_record' => [
                    'items' => $this->formatTrackRecord($paginatedTrack),
                    'pagination' => [
                        'total' => $totalTrack,
                        'limit' => $limit,
                        'offset' => $offset,
                        'total_pages' => $limit > 0 ? ceil($totalTrack / $limit) : 1,
                        'tahun_filter' => $tahun
                    ]
                ],
                'ringkasan' => $this->formatRingkasanPelanggaran($ringkasan)
            ]
        ]);
    }

    /**
     * Get notification count for realtime updates
     */
    public function getNotificationCount()
    {
        $session = session();
        $user_id = $session->get('user_id');
        $user_role = $session->get('role');

        if (!$user_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not authenticated'
            ]);
        }

        try {
            // Gunakan model yang sama untuk user dan admin karena menggunakan tabel yang sama
            $notifikasiModel = new NotifikasiModel();

            $count = $notifikasiModel->where('user_id', $user_id)
                                   ->where('is_read', 0)
                                   ->countAllResults();

            return $this->response->setJSON([
                'success' => true,
                'count' => $count,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting notification count: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get latest notifications for realtime updates
     */
    public function getLatestNotifications()
    {
        $session = session();
        $user_id = $session->get('user_id');
        $user_role = $session->get('role');

        if (!$user_id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not authenticated'
            ]);
        }

        try {
            // Gunakan model yang sama untuk user dan admin karena menggunakan tabel yang sama
            $notifikasiModel = new NotifikasiModel();

            $notifications = $notifikasiModel->where('user_id', $user_id)
                                           ->where('is_read', 0)
                                           ->orderBy('created_at', 'DESC')
                                           ->limit(5)
                                           ->findAll();

            // Format notifications for display
            $formattedNotifications = [];
            foreach ($notifications as $notif) {
                $created_at = new \DateTime($notif['created_at']);
                $now = new \DateTime();
                $diff = $now->diff($created_at);

                if ($diff->days > 0) {
                    $time_display = $created_at->format('d M Y H:i');
                } elseif ($diff->h > 0) {
                    $time_display = $diff->h . ' jam yang lalu';
                } elseif ($diff->i > 0) {
                    $time_display = $diff->i . ' menit yang lalu';
                } else {
                    $time_display = 'Baru saja';
                }

                $formattedNotifications[] = [
                    'id' => $notif['id'],
                    'judul' => $notif['judul'],
                    'pesan' => $notif['pesan'],
                    'jenis' => $notif['jenis'],
                    'time_display' => $time_display,
                    'created_at' => $notif['created_at']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'notifications' => $formattedNotifications,
                'count' => count($formattedNotifications),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting notifications: ' . $e->getMessage()
            ]);
        }
    }

    private function formatPegawaiRecord(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'nama' => $row['nama'] ?? null,
            'nip' => $row['nip'] ?? null,
            'pangkat' => $row['pangkat'] ?? null,
            'golongan' => $row['golongan'] ?? null,
            'jabatan' => $row['jabatan'] ?? null,
            'status' => $row['status'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'satker' => [
                'id' => isset($row['satker_id']) ? (int) $row['satker_id'] : null,
                'nama' => $row['satker_nama'] ?? null,
                'riwayat_mutasi_id' => isset($row['riwayat_mutasi_id']) ? (int) $row['riwayat_mutasi_id'] : null,
                'aktif_sejak' => $row['tanggal_mulai'] ?? null,
            ],
            'tracking_url' => isset($row['nip']) ? base_url('api/pegawai/' . $row['nip']) : null
        ];
    }

    private function formatTrackRecord(array $records): array
    {
        return array_map(function ($row) {
            return [
                'id' => isset($row['id']) ? (int) $row['id'] : null,
                'bulan' => isset($row['bulan']) ? (int) $row['bulan'] : null,
                'tahun' => isset($row['tahun']) ? (int) $row['tahun'] : null,
                'satker' => $row['satker_nama'] ?? null,
                'pegawai' => [
                    'nama' => $row['pegawai_nama'] ?? null,
                    'nip' => $row['pegawai_nip'] ?? null,
                    'jabatan' => $row['pegawai_jabatan'] ?? null,
                ],
                'pelanggaran' => [
                    'terlambat' => (int) ($row['terlambat'] ?? 0),
                    'tidak_absen_masuk' => (int) ($row['tidak_absen_masuk'] ?? 0),
                    'pulang_awal' => (int) ($row['pulang_awal'] ?? 0),
                    'tidak_absen_pulang' => (int) ($row['tidak_absen_pulang'] ?? 0),
                    'keluar_tidak_izin' => (int) ($row['keluar_tidak_izin'] ?? 0),
                    'tidak_masuk_tanpa_ket' => (int) ($row['tidak_masuk_tanpa_ket'] ?? 0),
                    'tidak_masuk_sakit' => (int) ($row['tidak_masuk_sakit'] ?? 0),
                    'tidak_masuk_kerja' => (int) ($row['tidak_masuk_kerja'] ?? 0),
                ],
                'bentuk_pembinaan' => $row['bentuk_pembinaan'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
            ];
        }, $records);
    }

    private function formatRingkasanPelanggaran(array $records): array
    {
        return array_map(function ($row) {
            return [
                'bulan' => isset($row['bulan']) ? (int) $row['bulan'] : null,
                'tahun' => isset($row['tahun']) ? (int) $row['tahun'] : null,
                'satker' => $row['satker_nama'] ?? null,
                'pelanggaran' => [
                    'terlambat' => (int) ($row['total_terlambat'] ?? 0),
                    'tidak_absen_masuk' => (int) ($row['total_tidak_absen_masuk'] ?? 0),
                    'pulang_awal' => (int) ($row['total_pulang_awal'] ?? 0),
                    'tidak_absen_pulang' => (int) ($row['total_tidak_absen_pulang'] ?? 0),
                    'keluar_tidak_izin' => (int) ($row['total_keluar_tidak_izin'] ?? 0),
                    'tidak_masuk_tanpa_ket' => (int) ($row['total_tidak_masuk_tanpa_ket'] ?? 0),
                    'tidak_masuk_sakit' => (int) ($row['total_tidak_masuk_sakit'] ?? 0),
                    'tidak_masuk_kerja' => (int) ($row['total_tidak_masuk_kerja'] ?? 0),
                ]
            ];
        }, $records);
    }

    private function formatStatistikPelanggaran(?array $statistik): array
    {
        if (!$statistik) {
            return [
                'terlambat' => 0,
                'tidak_absen_masuk' => 0,
                'pulang_awal' => 0,
                'tidak_absen_pulang' => 0,
                'keluar_tidak_izin' => 0,
                'tidak_masuk_tanpa_ket' => 0,
                'tidak_masuk_sakit' => 0,
                'tidak_masuk_kerja' => 0,
            ];
        }

        return [
            'terlambat' => (int) ($statistik['terlambat'] ?? 0),
            'tidak_absen_masuk' => (int) ($statistik['tidak_absen_masuk'] ?? 0),
            'pulang_awal' => (int) ($statistik['pulang_awal'] ?? 0),
            'tidak_absen_pulang' => (int) ($statistik['tidak_absen_pulang'] ?? 0),
            'keluar_tidak_izin' => (int) ($statistik['keluar_tidak_izin'] ?? 0),
            'tidak_masuk_tanpa_ket' => (int) ($statistik['tidak_masuk_tanpa_ket'] ?? 0),
            'tidak_masuk_sakit' => (int) ($statistik['tidak_masuk_sakit'] ?? 0),
            'tidak_masuk_kerja' => (int) ($statistik['tidak_masuk_kerja'] ?? 0),
        ];
    }
}
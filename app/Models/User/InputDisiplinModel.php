<?php
namespace App\Models\User;

use CodeIgniter\Model;

class InputDisiplinModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'bulan', 'tahun', 'terlambat', 'tidak_absen_masuk', 'pulang_awal', 'tidak_absen_pulang', 'keluar_tidak_izin', 'tidak_masuk_tanpa_ket', 'tidak_masuk_sakit', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan', 'created_by'
    ];

    public function getKedisiplinanByPegawaiIds($pegawai_ids, $bulan, $tahun)
    {
        return $this->whereIn('pegawai_id', $pegawai_ids)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->findAll();
    }

    public function saveKedisiplinanTabel($data)
    {
        // Cek apakah data sudah ada
        $existing = $this->where([
            'pegawai_id' => $data['pegawai_id'],
            'bulan' => $data['bulan'],
            'tahun' => $data['tahun']
        ])->first();
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    public function saveKedisiplinanTabelBatch($dataArr, $bulan, $tahun, $user_id)
    {
        foreach ($dataArr as $row) {
            if (empty($row['pegawai_id'])) continue;
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
            $this->saveKedisiplinanTabel($data);
        }
    }
} 
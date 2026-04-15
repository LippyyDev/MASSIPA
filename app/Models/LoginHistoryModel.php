<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginHistoryModel extends Model
{
    protected $table         = 'login_history';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false; // pakai kolom created_at manual

    protected $allowedFields = [
        'user_id',
        'username',
        'ip_address',
        'user_agent',
        'device_type',
        'device_os',
        'browser',
        'location_country',
        'location_region',
        'location_city',
        'created_at',
    ];

    /**
     * Simpan riwayat login baru (IP berbeda dari sebelumnya).
     */
    public function insertHistory(array $data): bool
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data, false);
    }

    /**
     * Cek apakah user sudah pernah login dari IP yang sama.
     * Digunakan untuk logika upsert: IP sama → update, IP beda → insert baru.
     */
    public function getByUserAndIp(int $userId, string $ip): ?array
    {
        return $this->where('user_id', $userId)
            ->where('ip_address', $ip)
            ->first();
    }

    /**
     * Update waktu login & info device untuk record yang sudah ada (IP sama).
     */
    public function updateLoginEntry(int $id, array $data): bool
    {
        return $this->where('id', $id)->set($data)->update();
    }

    /**
     * Ambil riwayat login untuk satu user, terurut dari terbaru (untuk halaman user).
     */
    public function getHistoryByUser(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Ambil semua riwayat login semua user dengan join ke tabel users (untuk admin).
     */
    public function getAllHistory(int $limit = 200): array
    {
        return $this->db->table('login_history lh')
            ->select('lh.*, u.nama_lengkap, u.role')
            ->join('users u', 'u.id = lh.user_id', 'left')
            ->orderBy('lh.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Hapus semua riwayat login milik satu user (action user dari halaman pengaturan).
     */
    public function deleteByUser(int $userId): bool
    {
        return $this->where('user_id', $userId)->delete();
    }

    /**
     * Hapus record login yang sudah lebih dari 90 hari (auto-cleanup saat login).
     */
    public function cleanupOldHistory(int $userId): void
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-90 days'));
        $this->where('user_id', $userId)
            ->where('created_at <', $cutoff)
            ->delete();
    }

    /**
     * Hapus satu record spesifik (aksi admin).
     */
    public function deleteById(int $id): bool
    {
        return $this->where('id', $id)->delete();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * TwoFaModel
 *
 * Mengelola pengaturan 2FA global, flag exempt per user,
 * dan whitelist IP yang sudah lulus verifikasi OTP.
 */
class TwoFaModel extends Model
{
    protected $table      = 'two_fa_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // ─── GLOBAL 2FA SETTING ───────────────────────────────────────────────────

    /**
     * Cek apakah 2FA global sedang aktif.
     */
    public function isGlobal2FaEnabled(): bool
    {
        $row = $this->db->table('two_fa_settings')
            ->where('user_id IS NULL', null, false)
            ->where('setting_key', 'global_2fa_enabled')
            ->get()
            ->getRowArray();

        return isset($row['setting_value']) && $row['setting_value'] === '1';
    }

    /**
     * Aktifkan atau nonaktifkan 2FA global.
     */
    public function setGlobal2Fa(bool $enabled): void
    {
        $value = $enabled ? '1' : '0';
        $existing = $this->db->table('two_fa_settings')
            ->where('user_id IS NULL', null, false)
            ->where('setting_key', 'global_2fa_enabled')
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('two_fa_settings')
                ->where('id', $existing['id'])
                ->update(['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->db->table('two_fa_settings')->insert([
                'user_id'       => null,
                'setting_key'   => 'global_2fa_enabled',
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ─── USER EXEMPT ──────────────────────────────────────────────────────────

    /**
     * Cek apakah user dikecualikan dari 2FA.
     */
    public function isUserExempt(int $userId): bool
    {
        $row = $this->db->table('two_fa_settings')
            ->where('user_id', $userId)
            ->where('setting_key', '2fa_exempt')
            ->get()
            ->getRowArray();

        return isset($row['setting_value']) && $row['setting_value'] === '1';
    }

    /**
     * Aktifkan atau nonaktifkan exempt 2FA untuk satu user.
     */
    public function setUserExempt(int $userId, bool $exempt): void
    {
        $value = $exempt ? '1' : '0';
        $existing = $this->db->table('two_fa_settings')
            ->where('user_id', $userId)
            ->where('setting_key', '2fa_exempt')
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('two_fa_settings')
                ->where('id', $existing['id'])
                ->update(['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->db->table('two_fa_settings')->insert([
                'user_id'       => $userId,
                'setting_key'   => '2fa_exempt',
                'setting_value' => $value,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Toggle exempt user — jika sudah exempt, nonaktifkan; sebaliknya aktifkan.
     * Return nilai baru (true = exempt, false = tidak exempt).
     */
    public function toggleUserExempt(int $userId): bool
    {
        $current = $this->isUserExempt($userId);
        $this->setUserExempt($userId, !$current);
        return !$current;
    }

    /**
     * Ambil status exempt semua user (untuk admin view).
     * Return: array ['user_id' => exemptBool, ...]
     */
    public function getAllExemptMap(): array
    {
        $rows = $this->db->table('two_fa_settings')
            ->where('setting_key', '2fa_exempt')
            ->where('user_id IS NOT NULL', null, false)
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['user_id']] = $row['setting_value'] === '1';
        }
        return $map;
    }

    // ─── IP WHITELIST ─────────────────────────────────────────────────────────

    /**
     * Cek apakah IP user ada di whitelist yang masih aktif (belum expire).
     */
    public function isIpWhitelisted(int $userId, string $ip): bool
    {
        $count = $this->db->table('two_fa_whitelist')
            ->where('user_id', $userId)
            ->where('ip_address', $ip)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Tambahkan IP ke whitelist selama 7 hari.
     * Jika sudah ada entry dengan user+IP yang sama, hapus dulu (refresh expiry).
     */
    public function addToWhitelist(int $userId, string $ip): void
    {
        // Hapus entry lama untuk user+IP yang sama (refresh)
        $this->db->table('two_fa_whitelist')
            ->where('user_id', $userId)
            ->where('ip_address', $ip)
            ->delete();

        $this->db->table('two_fa_whitelist')->insert([
            'user_id'    => $userId,
            'ip_address' => $ip,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil whitelist milik satu user.
     */
    public function getWhitelistByUser(int $userId): array
    {
        return $this->db->table('two_fa_whitelist')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil semua whitelist join dengan data user (untuk admin).
     */
    public function getAllWhitelist(): array
    {
        return $this->db->table('two_fa_whitelist w')
            ->select('w.*, u.username, u.nama_lengkap, u.role')
            ->join('users u', 'u.id = w.user_id', 'left')
            ->orderBy('w.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Hapus satu entry whitelist berdasarkan ID.
     * $ownerId = jika diisi, pastikan entry milik user tersebut (keamanan).
     */
    public function revokeWhitelist(int $id, ?int $ownerId = null): bool
    {
        $builder = $this->db->table('two_fa_whitelist')->where('id', $id);
        if ($ownerId !== null) {
            $builder->where('user_id', $ownerId);
        }
        return $builder->delete() && $this->db->affectedRows() > 0;
    }

    /**
     * Hapus semua whitelist milik satu user.
     */
    public function revokeAllForUser(int $userId): void
    {
        $this->db->table('two_fa_whitelist')->where('user_id', $userId)->delete();
    }

    /**
     * Bersihkan whitelist yang sudah expire.
     */
    public function purgeExpired(): int
    {
        $this->db->table('two_fa_whitelist')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();
        return (int) $this->db->affectedRows();
    }
}

<?php

namespace App\Controllers\Guest;

use App\Libraries\EmailQueueService;
use App\Models\TwoFaModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * TwoFaController
 *
 * Menangani halaman input OTP dan verifikasi OTP untuk 2FA.
 * Dipanggil setelah login berhasil (password benar) namun IP belum di-whitelist.
 */
class TwoFaController extends Controller
{
    private const OTP_EXPIRE_SECONDS   = 300;  // 5 menit
    private const RESEND_COOLDOWN_SECS = 60;   // 1 menit cooldown kirim ulang
    private const MAX_ATTEMPTS         = 3;    // Maks 3x salah
    private const BLOCK_DURATION_SECS  = 300;  // Blokir 5 menit setelah gagal 3x

    public function showForm()
    {
        helper(['form', 'url']);
        $session = session();
        $cache   = \Config\Services::cache();

        // Pastikan ada pending_2fa di session
        if (! $session->get('pending_2fa')) {
            return redirect()->to(base_url('login'));
        }

        $pending = $session->get('pending_2fa');

        // Cek OTP expire
        if ($this->isExpired($pending)) {
            $session->remove('pending_2fa');
            $session->setFlashdata('msg', 'Kode OTP telah kedaluwarsa. Silakan login ulang.');
            $session->setFlashdata('msg_type', 'error');
            return redirect()->to(base_url('login'));
        }

        // Cek apakah masih diblokir
        $blockKey = 'otp_block_' . ($pending['user_id'] ?? 0);
        $blockedUntil = $cache->get($blockKey);
        if ($blockedUntil && $blockedUntil > time()) {
            $session->remove('pending_2fa');
            $remaining = ceil(($blockedUntil - time()) / 60);
            $session->setFlashdata('msg', "Akun diblokir sementara karena terlalu banyak percobaan OTP salah. Coba lagi dalam {$remaining} menit.");
            $session->setFlashdata('msg_type', 'error');
            return redirect()->to(base_url('login'));
        }

        return view('guest/VerifyOtp', [
            'cooldownRemaining' => $this->getCooldownRemaining($pending),
            'expiresRemaining'  => $this->getExpiresRemaining($pending),
        ]);
    }

    public function verify()
    {
        helper(['url']);
        $session  = session();
        $pending  = $session->get('pending_2fa');

        if (! $pending) {
            $session->setFlashdata('msg_error', 'Sesi verifikasi tidak ditemukan. Silakan login ulang.');
            return redirect()->to(base_url('login'));
        }

        // Cek expire
        if ($this->isExpired($pending)) {
            $session->remove('pending_2fa');
            $session->setFlashdata('msg_error', 'Kode OTP telah kedaluwarsa. Silakan login ulang untuk mendapatkan kode baru.');
            return redirect()->to(base_url('login'));
        }

        $inputOtp = trim((string) $this->request->getVar('otp'));
        $cache    = \Config\Services::cache();
        $blockKey = 'otp_block_' . ($pending['user_id'] ?? 0);

        // Cek blokir aktif
        $blockedUntil = $cache->get($blockKey);
        if ($blockedUntil && $blockedUntil > time()) {
            $session->remove('pending_2fa');
            $remaining = ceil(($blockedUntil - time()) / 60);
            $session->setFlashdata('msg', "Akun diblokir sementara. Coba lagi dalam {$remaining} menit.");
            $session->setFlashdata('msg_type', 'error');
            return redirect()->to(base_url('login'));
        }

        // Validasi format
        if ($inputOtp === '' || ! ctype_digit($inputOtp) || strlen($inputOtp) !== 6) {
            $session->setFlashdata('msg_error', 'Kode OTP harus 6 digit angka.');
            return redirect()->to(base_url('verify-otp'));
        }

        // Verifikasi OTP
        if (! password_verify($inputOtp, $pending['otp'])) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            $session->set('pending_2fa', $pending);

            if ($pending['attempts'] >= self::MAX_ATTEMPTS) {
                // Blokir selama 5 menit via cache
                $cache->save($blockKey, time() + self::BLOCK_DURATION_SECS, self::BLOCK_DURATION_SECS);
                $session->remove('pending_2fa');
                $session->setFlashdata('msg', 'Akun Anda diblokir sementara selama 5 menit karena terlalu banyak percobaan OTP yang salah.');
                $session->setFlashdata('msg_type', 'error');
                return redirect()->to(base_url('login'));
            }

            $sisa = self::MAX_ATTEMPTS - $pending['attempts'];
            $session->setFlashdata('msg_error', "Kode OTP tidak sesuai. Sisa percobaan: {$sisa} kali.");
            return redirect()->to(base_url('verify-otp'));
        }

        // OTP benar ✓ — tambahkan IP ke whitelist
        $twoFaModel = new TwoFaModel();
        $twoFaModel->addToWhitelist((int) $pending['user_id'], $pending['ip']);

        // Catat riwayat perangkat login & kirim notifikasi email
        // (dipanggil di sini, bukan di HalamanLoginController, karena login baru benar-benar
        //  dianggap berhasil setelah OTP diverifikasi)
        try {
            \App\Libraries\DeviceHistoryService::record(
                (int) $pending['user_id'],
                (string) ($pending['username'] ?? $pending['session_data']['username'] ?? ''),
                \Config\Services::request()
            );
        } catch (\Throwable $e) {
            log_message('error', '[TwoFaController] DeviceHistoryService gagal: ' . $e->getMessage());
        }

        // Set session lengkap (sama persis seperti di HalamanLoginController)
        $session->regenerate(true);
        $session->set($pending['session_data']);
        $session->remove('pending_2fa');

        // Arahkan ke dashboard sesuai role
        $role = $pending['session_data']['role'] ?? 'user';
        return redirect()->to(
            $role === 'admin'
                ? base_url('admin/dashboard')
                : base_url('user/beranda_user')
        );
    }

    public function resend()
    {
        helper(['url']);
        $session = session();
        $pending = $session->get('pending_2fa');

        if (! $pending) {
            $session->setFlashdata('msg_error', 'Sesi tidak ditemukan. Silakan login ulang.');
            return redirect()->to(base_url('login'));
        }

        // Cek cooldown
        $cooldown = $this->getCooldownRemaining($pending);
        if ($cooldown > 0) {
            $session->setFlashdata('msg_error', "Tunggu {$cooldown} detik sebelum mengirim ulang kode.");
            return redirect()->to(base_url('verify-otp'));
        }

        // Generate OTP baru (hash sebelum disimpan)
        $newOtp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $pending['otp']        = password_hash($newOtp, PASSWORD_BCRYPT);
        $pending['expires_at'] = time() + self::OTP_EXPIRE_SECONDS;
        $pending['last_send']  = time();
        // Sengaja TIDAK reset $pending['attempts'] — mencegah bypass limit via resend
        $session->set('pending_2fa', $pending);

        // Kirim ulang email
        $userModel = new UserModel();
        $user      = $userModel->find((int) $pending['user_id']);
        if ($user && ! empty($user['email'])) {
            $this->sendOtpEmail($user['email'], $newOtp, $user['nama_lengkap'] ?? $user['username'] ?? '');
            $session->setFlashdata('msg_success', 'Kode OTP baru telah dikirim ke email Anda.');
        } else {
            $session->setFlashdata('msg_error', 'Gagal mengirim ulang kode. Email tidak terdaftar.');
        }

        return redirect()->to(base_url('verify-otp'));
    }

    /**
     * Kirim email OTP — dipanggil oleh HalamanLoginController juga.
     */
    public static function queueOtpEmail(string $email, string $otp, string $namaLengkap): bool
    {
        try {
            $body = view('emails/otp', [
                'recipient' => $namaLengkap ?: $email,
                'otp'       => $otp,
            ]);

            return EmailQueueService::queueRaw(
                $email,
                'Kode Verifikasi Login MASSIPA',
                $body
            );
        } catch (\Throwable $th) {
            log_message('error', '[TwoFaController] Gagal queue email OTP: ' . $th->getMessage());
            return false;
        }
    }

    private function sendOtpEmail(string $email, string $otp, string $namaLengkap): bool
    {
        return self::queueOtpEmail($email, $otp, $namaLengkap);
    }

    private function getCooldownRemaining(array $pending): int
    {
        $lastSend  = $pending['last_send'] ?? 0;
        $remaining = ($lastSend + self::RESEND_COOLDOWN_SECS) - time();
        return $remaining > 0 ? $remaining : 0;
    }

    private function getExpiresRemaining(array $pending): int
    {
        $expires   = $pending['expires_at'] ?? 0;
        $remaining = $expires - time();
        return $remaining > 0 ? $remaining : 0;
    }

    private function isExpired(array $pending): bool
    {
        return time() > ($pending['expires_at'] ?? 0);
    }
}

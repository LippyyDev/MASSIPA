<?php

namespace App\Controllers\Guest;

use App\Libraries\EmailQueueService;
use App\Models\UserModel;
use CodeIgniter\Controller;

class ForgotPasswordController extends Controller
{
    private const CODE_EXPIRE_SECONDS = 3600;
    private const RESEND_COOLDOWN_SECONDS = 60;

    public function index()
    {
        helper(['form', 'url']);
        $session = session();
        $resetData = $session->get('reset_password') ?? [];

        return view('guest/ForgotPassword', [
            'email'              => $resetData['email'] ?? '',
            'cooldownRemaining'  => $this->getCooldownRemaining($resetData),
            'expiresRemaining'   => $this->getExpiresRemaining($resetData),
            'hasRequest'         => !empty($resetData),
        ]);
    }

    public function sendCode()
    {
        helper(['url']);
        $session  = session();
        $cache    = \Config\Services::cache();
        $email    = trim((string) $this->request->getVar('email'));
        $isResend = (bool) $this->request->getVar('resend');

        // Rate limiting per-IP: maks 5 percobaan per 10 menit
        $ipHash       = md5($this->request->getIPAddress());
        $rateLimitKey = "forgot_password_rate_{$ipHash}";
        $requestCount = $cache->get($rateLimitKey) ?: 0;

        if ($requestCount >= 5) {
            $session->setFlashdata('error', 'Terlalu banyak permintaan. Silakan coba lagi dalam beberapa menit.');
            return redirect()->back()->withInput();
        }

        // Increment counter sebelum proses (mencegah bypass via race condition)
        $cache->save($rateLimitKey, $requestCount + 1, 600); // Reset setelah 10 menit

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $session->setFlashdata('error', 'Alamat email tidak valid.');
            return redirect()->back()->withInput();
        }

        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)->first();

        if (!$user) {
            $session->setFlashdata('error', 'Email tidak terdaftar.');
            return redirect()->back()->withInput();
        }

        $existing           = $session->get('reset_password') ?? [];
        $cooldownRemaining  = $this->getCooldownRemaining($existing);
        if ($cooldownRemaining > 0) {
            $session->setFlashdata('error', 'Tunggu ' . $cooldownRemaining . ' detik untuk kirim ulang kode.');
            return redirect()->back()->withInput();
        }

        $code    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token   = bin2hex(random_bytes(16));
        $payload = [
            'email'      => $email,
            'code'       => $code,
            'token'      => $token,
            'expires_at' => time() + self::CODE_EXPIRE_SECONDS,
            'last_send'  => time(),
            'verified'   => false,
        ];
        $session->set('reset_password', $payload);

        if ($this->sendResetEmail($email, $code, $token, $user['nama_lengkap'] ?? '')) {
            $session->setFlashdata('success', $isResend ? 'Kode baru telah dikirim.' : 'Kode reset telah dikirim ke email.');
        } else {
            $session->setFlashdata('error', 'Gagal mengirim email. Periksa konfigurasi email.');
        }

        return redirect()->to(base_url('forgot-password'));
    }

    public function verifyCode()
    {
        helper(['url']);
        $session   = session();
        $inputCode = trim((string) $this->request->getVar('verification_code'));
        $data      = $session->get('reset_password') ?? [];

        if (!$data) {
            $session->setFlashdata('error', 'Silakan minta kode reset terlebih dahulu.');
            return redirect()->to(base_url('forgot-password'));
        }

        if ($this->isExpired($data)) {
            $session->remove('reset_password');
            $session->setFlashdata('error', 'Kode telah kedaluwarsa. Silakan kirim ulang.');
            return redirect()->to(base_url('forgot-password'));
        }

        if ($inputCode === '' || !ctype_digit($inputCode) || strlen($inputCode) !== 6) {
            $session->setFlashdata('error', 'Kode harus 6 digit angka.');
            return redirect()->back()->withInput();
        }

        if (!hash_equals($data['code'], $inputCode)) {
            $session->setFlashdata('error', 'Kode tidak sesuai.');
            return redirect()->back()->withInput();
        }

        $data['verified'] = true;
        $session->set('reset_password', $data);
        $session->setFlashdata('success', 'Kode benar. Silakan atur ulang kata sandi.');

        return redirect()->to(base_url('reset-password?token=' . $data['token']));
    }

    public function resetForm()
    {
        helper(['url']);
        $session = session();
        $data    = $session->get('reset_password') ?? [];

        if (!$data) {
            $session->setFlashdata('error', 'Silakan minta kode reset terlebih dahulu.');
            return redirect()->to(base_url('forgot-password'));
        }

        if ($this->isExpired($data)) {
            $session->remove('reset_password');
            $session->setFlashdata('error', 'Kode telah kedaluwarsa. Silakan kirim ulang.');
            return redirect()->to(base_url('forgot-password'));
        }

        $tokenParam = (string) $this->request->getGet('token');
        if ($tokenParam !== '' && hash_equals($data['token'], $tokenParam)) {
            $data['verified'] = true;
            $session->set('reset_password', $data);
        }

        if (empty($data['verified'])) {
            $session->setFlashdata('error', 'Masukkan kode verifikasi terlebih dahulu.');
            return redirect()->to(base_url('forgot-password'));
        }

        return view('guest/ResetPassword', [
            'email' => $data['email'] ?? '',
            'token' => $data['token'] ?? '',
        ]);
    }

    public function updatePassword()
    {
        helper(['form', 'url']);
        $session = session();
        $data    = $session->get('reset_password') ?? [];

        if (!$data || empty($data['verified'])) {
            $session->setFlashdata('error', 'Sesi reset tidak valid. Silakan ulangi proses.');
            return redirect()->to(base_url('forgot-password'));
        }

        if ($this->isExpired($data)) {
            $session->remove('reset_password');
            $session->setFlashdata('error', 'Kode telah kedaluwarsa. Silakan kirim ulang.');
            return redirect()->to(base_url('forgot-password'));
        }

        $tokenParam = (string) $this->request->getVar('token');
        if ($tokenParam === '' || !hash_equals($data['token'], $tokenParam)) {
            $session->setFlashdata('error', 'Token reset tidak valid.');
            return redirect()->to(base_url('forgot-password'));
        }

        $rules = [
            'password'          => 'required|min_length[8]',
            'password_confirm'  => 'required|matches[password]',
        ];

        $messages = [
            'password' => [
                'required'   => 'Password baru harus diisi.',
                'min_length' => 'Password baru minimal 8 karakter.',
            ],
            'password_confirm' => [
                'required' => 'Konfirmasi password harus diisi.',
                'matches'  => 'Konfirmasi password tidak sesuai dengan password baru.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $user      = $userModel->where('email', $data['email'])->first();

        if (! $user) {
            $session->setFlashdata('error', 'Pengguna tidak ditemukan.');
            return redirect()->to(base_url('forgot-password'));
        }

        $userModel->update($user['id'], [
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
        ]);

        $session->remove('reset_password');
        $session->setFlashdata('msg_success', 'Password berhasil direset. Silakan login kembali.');

        return redirect()->to(base_url('login'));
    }

    /**
     * Masukkan email reset password ke antrean pengiriman.
     * Menggunakan EmailQueueService::queueRaw() agar konsisten dengan
     * mekanisme email queue yang dipakai seluruh sistem (fire-and-forget,
     * dikirim oleh EmailQueuePumpFilter di request berikutnya).
     */
    private function sendResetEmail(string $email, string $code, string $token, string $fullName = ''): bool
    {
        $resetLink = base_url('reset-password?token=' . $token);
        $logoUrl   = 'https://image2url.com/images/1765888884783-6cee7d44-4b71-4c6b-92c5-952557da5156.png';
        $recipient = $fullName !== '' ? $fullName : $email;

        try {
            $body = view('emails/reset_password', [
                'code'      => $code,
                'resetLink' => $resetLink,
                'recipient' => $recipient,
                'logoUrl'   => $logoUrl,
            ]);

            return EmailQueueService::queueRaw(
                $email,
                'Kode Reset Password MASSIPA',
                $body
            );
        } catch (\Throwable $th) {
            log_message('error', 'Gagal memasukkan email reset password ke antrian: ' . $th->getMessage());
            return false;
        }
    }

    private function getCooldownRemaining(array $data): int
    {
        $lastSend  = $data['last_send'] ?? 0;
        $remaining = ($lastSend + self::RESEND_COOLDOWN_SECONDS) - time();

        return $remaining > 0 ? $remaining : 0;
    }

    private function getExpiresRemaining(array $data): int
    {
        $expires   = $data['expires_at'] ?? 0;
        $remaining = $expires - time();

        return $remaining > 0 ? $remaining : 0;
    }

    private function isExpired(array $data): bool
    {
        return time() > ($data['expires_at'] ?? 0);
    }
}


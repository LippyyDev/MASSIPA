<?php

namespace App\Controllers\Guest;

use App\Libraries\DeviceHistoryService;
use App\Models\TwoFaModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class HalamanLoginController extends Controller
{
    public function index()
    {
        helper(["form", "url"]);
        $session = session();

        // Jika sudah login, langsung arahkan ke beranda sesuai role
        if ($session->get('isLoggedIn')) {
            return $session->get('role') === 'admin'
                ? redirect()->to(base_url("admin/dashboard"))
                : redirect()->to(base_url("user/beranda_user"));
        }

        // Cek status blocked untuk IP saat ini
        $cache = \Config\Services::cache();
        $ipAddress = $this->request->getIPAddress();
        $ipHash = $this->sanitizeIpForCache($ipAddress);
        $blockedKey = "login_blocked_{$ipHash}";
        $blockedUntil = $cache->get($blockedKey);
        
        $data['isBlocked'] = false;
        $data['blockedUntil'] = null;
        
        if ($blockedUntil && $blockedUntil > time()) {
            $data['isBlocked'] = true;
            $data['blockedUntil'] = $blockedUntil;
        }

        echo view('guest/HalamanLogin', $data);
    }

    public function auth()
    {
        helper(["form", "url"]);
        $session = session();
        $cache = \Config\Services::cache();
        $ipAddress = $this->request->getIPAddress();
        $ipHash = $this->sanitizeIpForCache($ipAddress);
        
        // Cek apakah IP sudah di-block
        $blockedKey = "login_blocked_{$ipHash}";
        $blockedUntil = $cache->get($blockedKey);
        
        if ($blockedUntil && $blockedUntil > time()) {
            $remainingSeconds = $blockedUntil - time();
            $remainingMinutes = ceil($remainingSeconds / 60);
            $session->setFlashdata("msg_blocked", "Terlalu banyak percobaan login gagal. Silakan coba lagi dalam {$remainingMinutes} menit.");
            return redirect()->to(base_url("login"));
        }

        // Atur masa hidup sesi berdasarkan pilihan "ingat saya"
        $remember = (bool) $this->request->getVar("remember");
        $sessionConfig = config('Session');
        $sessionConfig->expiration = $remember ? 604800 : 7200; // 7 hari atau 2 jam

        $model = new UserModel();
        $username = $this->request->getVar("username");
        $password = $this->request->getVar("password");

        $data = $model->where("username", $username)->first();

        if ($data) {
            $pass = $data["password"];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                // Login berhasil - reset counter failed attempts
                $attemptsKey = "login_attempts_{$ipHash}";
                $cache->delete($attemptsKey);
                $cache->delete($blockedKey);

                // Regenerasi session ID untuk mencegah Session Fixation
                $session->regenerate(true);

                $ses_data = [
                    "user_id"       => $data["id"],
                    "username"      => $data["username"],
                    "nama_lengkap"  => $data["nama_lengkap"],
                    "email"         => $data["email"],
                    "role"          => $data["role"],
                    "foto_profil"   => $data["foto_profil"],
                        "isLoggedIn"    => TRUE,
                        "remember_me"   => $remember,
                ];
                // JANGAN set session dulu — simpan dulu, cek 2FA terlebih dahulu
                // Cookie "ingat saya"
                $cookieExpire = $remember ? 60 * 60 * 24 * 7 : -3600;
                $this->response->setCookie(
                    'remember_me',
                    $remember ? '1' : '',
                    $cookieExpire,
                    '',
                    '/',
                    '',
                    $this->request->isSecure(),
                    true,
                    'Lax'
                );


                // ── Cek 2FA ──────────────────────────────────────────────────
                $twoFaModel = new TwoFaModel();
                $needOtp    = false;

                if ($twoFaModel->isGlobal2FaEnabled()) {
                    // Cek apakah user dikecualikan
                    if (! $twoFaModel->isUserExempt((int) $data['id'])) {
                        // Cek apakah IP sudah di whitelist
                        if (! $twoFaModel->isIpWhitelisted((int) $data['id'], $ipAddress)) {
                            $needOtp = true;
                        }
                    }
                }

                if ($needOtp) {
                    // Cek email tersedia
                    if (empty($data['email'])) {
                        // Tidak ada email, tidak bisa kirim OTP — paksa logout dan tampilkan error
                        $session->setFlashdata('msg', 'Akun Anda tidak memiliki alamat email terdaftar. Hubungi administrator untuk mengaktifkan 2FA.');
                        $session->setFlashdata('msg_type', 'error');
                        return redirect()->to(base_url('login'));
                    }

                    // Generate OTP, hash sebelum disimpan di session (keamanan)
                    $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                    $session->set('pending_2fa', [
                        'user_id'      => $data['id'],
                        'username'     => $data['username'],
                        'ip'           => $ipAddress,
                        'otp'          => password_hash($otp, PASSWORD_BCRYPT), // disimpan sebagai hash
                        'expires_at'   => time() + 300, // 5 menit
                        'last_send'    => time(),
                        'attempts'     => 0,
                        'session_data' => $ses_data,
                        'user_agent'   => $this->request->getHeaderLine('User-Agent'),
                    ]);

                    // Kirim email OTP
                    TwoFaController::queueOtpEmail(
                        $data['email'],
                        $otp,
                        $data['nama_lengkap'] ?? $data['username'] ?? ''
                    );

                    // JANGAN set session isLoggedIn — arahkan ke verifikasi OTP
                    // DeviceHistoryService::record() akan dipanggil di TwoFaController::verify() setelah OTP berhasil
                    return redirect()->to(base_url('verify-otp'));
                }

                // ── Tidak perlu OTP — set session dan login langsung ─────────
                // Catat riwayat perangkat login (hanya di sini karena tidak butuh OTP)
                DeviceHistoryService::record(
                    (int) $data['id'],
                    (string) $data['username'],
                    $this->request
                );
                $session->set($ses_data);
                if ($data["role"] == "admin") {
                    return redirect()->to(base_url("admin/dashboard"));
                } else {
                    return redirect()->to(base_url("user/beranda_user"));
                }
            } else {
                // Password salah - increment failed attempts
                $attemptsKey = "login_attempts_{$ipHash}";
                $currentAttempts = $cache->get($attemptsKey) ?: 0;
                $this->incrementFailedAttempts($ipHash, $cache);
                $remainingAttempts = 3 - ($currentAttempts + 1);
                if ($remainingAttempts > 0) {
                    $session->setFlashdata("msg", "Username atau password salah. Sisa percobaan: {$remainingAttempts} kali.");
                } else {
                    $session->setFlashdata("msg", "Username atau password salah. Akses akan diblokir.");
                }
                return redirect()->to(base_url("login"));
            }
        } else {
            // Username tidak ditemukan - increment failed attempts
            $attemptsKey = "login_attempts_{$ipHash}";
            $currentAttempts = $cache->get($attemptsKey) ?: 0;
            $this->incrementFailedAttempts($ipHash, $cache);
            $remainingAttempts = 3 - ($currentAttempts + 1);
            if ($remainingAttempts > 0) {
                $session->setFlashdata("msg", "Username atau password salah. Sisa percobaan: {$remainingAttempts} kali.");
            } else {
                $session->setFlashdata("msg", "Username atau password salah. Akses akan diblokir.");
            }
            return redirect()->to(base_url("login"));
        }
    }

    /**
     * Sanitize IP address untuk digunakan sebagai cache key
     * Menggunakan md5 untuk menghindari reserved characters
     */
    private function sanitizeIpForCache($ipAddress)
    {
        return md5($ipAddress);
    }

    /**
     * Increment failed login attempts dan block jika sudah 3x
     */
    private function incrementFailedAttempts($ipHash, $cache)
    {
        $attemptsKey = "login_attempts_{$ipHash}";
        $blockedKey = "login_blocked_{$ipHash}";
        
        // Get current attempts
        $attempts = $cache->get($attemptsKey) ?: 0;
        $attempts++;
        
        // Simpan attempts dengan TTL 5 menit (untuk reset otomatis)
        $cache->save($attemptsKey, $attempts, 300);
        
        // Jika sudah 3x gagal, block selama 120 detik
        if ($attempts >= 3) {
            $blockedUntil = time() + 120; // Block selama 120 detik
            $cache->save($blockedKey, $blockedUntil, 120);
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();

        // Hapus cookie pengingat saat logout
        $this->response->setCookie(
            'remember_me',
            '',
            -3600,
            '',
            '/',
            '',
            $this->request->isSecure(),
            true,
            'Lax'
        );
        return redirect()->to(base_url("login"));
    }
} 
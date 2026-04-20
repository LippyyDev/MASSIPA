<?php

namespace App\Libraries;

use App\Models\LoginHistoryModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * DeviceHistoryService
 *
 * Mencatat riwayat login berhasil ke tabel login_history.
 * - Jika user login dari IP yang SAMA → update waktu login saja (tidak buat record baru)
 * - Jika IP BERBEDA → buat record baru
 * - Menggunakan regex native PHP untuk parsing User-Agent (termasuk Android WebView)
 * - Menggunakan ip-api.com untuk geolocation dengan fallback lengkap
 */
class DeviceHistoryService
{
    /**
     * Entry point utama — dipanggil setelah login berhasil.
     */
    public static function record(int $userId, string $username, IncomingRequest $request): void
    {
        try {
            $model = new LoginHistoryModel();

            // Cleanup data lama (> 90 hari) untuk user ini
            $model->cleanupOldHistory($userId);

            $ip        = $request->getIPAddress();
            $userAgent = $request->getUserAgent()->getAgentString();

            // Parse device info dari User-Agent string
            $deviceInfo = self::parseDevice($userAgent);

            // Cek apakah user+IP yang sama sudah ada — jika iya, update waktunya saja
            $existing = $model->getByUserAndIp($userId, $ip);

            if ($existing) {
                // IP sama → update waktu login + refresh info device (browser bisa berubah)
                $model->updateLoginEntry($existing['id'], [
                    'user_agent'  => $userAgent,
                    'device_type' => $deviceInfo['type'],
                    'device_os'   => $deviceInfo['os'],
                    'browser'     => $deviceInfo['browser'],
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            } else {
                // IP berbeda → tambah record baru + lookup geo
                $geoInfo = self::lookupGeo($ip);

                $model->insertHistory([
                    'user_id'          => $userId,
                    'username'         => $username,
                    'ip_address'       => $ip,
                    'user_agent'       => $userAgent,
                    'device_type'      => $deviceInfo['type'],
                    'device_os'        => $deviceInfo['os'],
                    'browser'          => $deviceInfo['browser'],
                    'location_country' => $geoInfo['country'],
                    'location_region'  => $geoInfo['region'],
                    'location_city'    => $geoInfo['city'],
                ]);
            }
        } catch (\Throwable $e) {
            log_message('error', '[DeviceHistoryService] Gagal mencatat riwayat login: ' . $e->getMessage());
        }
    }

    /**
     * Parse User-Agent string untuk mendapatkan info device, OS, dan browser.
     *
     * Urutan deteksi browser PENTING:
     *   Android WebView → Edge → Opera → Samsung → UC → Firefox → Chrome → Safari → IE
     *
     * Android WebView dikenali dari:
     *   - Mengandung "wv)" di UA string (marker resmi Google)
     *   - ATAU mengandung "Version/x.x" sebelum "Chrome" di Android (cara lama)
     */
    public static function parseDevice(string $ua): array
    {
        $type    = 'Desktop';
        $os      = 'Tidak diketahui';
        $browser = 'Tidak diketahui';

        if (empty($ua)) {
            return ['type' => $type, 'os' => $os, 'browser' => $browser];
        }

        // ─── Deteksi Tipe Perangkat ───────────────────────────────────────
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            $type = 'Tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone|opera mini|iemobile/i', $ua)) {
            $type = 'Mobile';
        } else {
            $type = 'Desktop';
        }

        // ─── Deteksi Sistem Operasi ───────────────────────────────────────
        if (preg_match('/windows nt 10\.0/i', $ua))          $os = 'Windows 10/11';
        elseif (preg_match('/windows nt 6\.3/i', $ua))       $os = 'Windows 8.1';
        elseif (preg_match('/windows nt 6\.2/i', $ua))       $os = 'Windows 8';
        elseif (preg_match('/windows nt 6\.1/i', $ua))       $os = 'Windows 7';
        elseif (preg_match('/windows/i', $ua))                $os = 'Windows';
        elseif (preg_match('/android/i', $ua))                $os = 'Android';
        elseif (preg_match('/iphone/i', $ua))                 $os = 'iOS';
        elseif (preg_match('/ipad/i', $ua))                   $os = 'iPadOS';
        elseif (preg_match('/mac os x\s([\d_]+)/i', $ua, $m)) {
            $os = 'macOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/linux/i', $ua))                $os = 'Linux';
        elseif (preg_match('/ubuntu/i', $ua))                 $os = 'Ubuntu';

        // ─── Deteksi Browser ──────────────────────────────────────────────
        // PENTING: Android WebView HARUS dicek SEBELUM Chrome karena UA-nya mirip
        if (preg_match('/android/i', $ua) && (
            preg_match('/;\s*wv\)/i', $ua) ||   // Marker resmi WebView: "; wv)"
            preg_match('/version\/[\d.]+ chrome/i', $ua) // Cara lama: "Version/x.x Chrome/..."
        )) {
            $browser = 'Android WebView';
        } elseif (preg_match('/edg\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Microsoft Edge ' . $m[1];
        } elseif (preg_match('/opr\/([\d.]+)/i', $ua, $m) || preg_match('/opera\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Opera ' . $m[1];
        } elseif (preg_match('/samsung.*browser\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Samsung Browser ' . $m[1];
        } elseif (preg_match('/ucbrowser\/([\d.]+)/i', $ua, $m)) {
            $browser = 'UC Browser ' . $m[1];
        } elseif (preg_match('/firefox\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Firefox ' . $m[1];
        } elseif (preg_match('/chrome\/([\d.]+)/i', $ua, $m)) {
            $browser = 'Chrome ' . $m[1];
        } elseif (preg_match('/safari\/([\d.]+)/i', $ua, $m) && preg_match('/version\/([\d.]+)/i', $ua, $mv)) {
            $browser = 'Safari ' . $mv[1];
        } elseif (preg_match('/msie\s([\d.]+)/i', $ua, $m) || preg_match('/trident.*rv:([\d.]+)/i', $ua, $m)) {
            $browser = 'Internet Explorer ' . $m[1];
        }

        return [
            'type'    => $type,
            'os'      => $os,
            'browser' => $browser,
        ];
    }

    /**
     * Lookup geolocation dari IP menggunakan ip-api.com.
     * Limit gratis: 45 request/menit — lebih dari cukup untuk skala MASSIPA.
     *
     * Fallback otomatis jika:
     *  - IP adalah jaringan lokal → tampil "Jaringan Lokal"
     *  - API gagal/timeout → tampil null (ditampilkan "Tidak tersedia" di UI)
     */
    public static function lookupGeo(string $ip): array
    {
        $default = ['country' => null, 'region' => null, 'city' => null];

        if (empty($ip)) {
            return $default;
        }

        // IP lokal/private → skip API call
        if (self::isPrivateIp($ip)) {
            return ['country' => 'Jaringan Lokal', 'region' => null, 'city' => null];
        }

        try {
            $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city&lang=id";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 2, // 2 detik timeout agar tidak memperlambat login
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_FOLLOWLOCATION => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                return $default;
            }

            $data = json_decode($response, true);

            if (!$data || ($data['status'] ?? '') !== 'success') {
                return $default;
            }

            return [
                'country' => $data['country']    ?? null,
                'region'  => $data['regionName'] ?? null,
                'city'    => $data['city']        ?? null,
            ];
        } catch (\Throwable $e) {
            log_message('warning', '[DeviceHistoryService] Geolocation gagal untuk IP ' . $ip . ': ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Cek apakah IP termasuk jaringan privat/lokal.
     * Filter FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE akan return false
     * jika IP adalah private/reserved (artinya isPrivate = true).
     */
    private static function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}

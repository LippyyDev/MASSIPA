<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;

class CloudflareTurnstile
{
    private $secretKey;
    private $siteKey;
    private $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        // Ambil dari environment variables
        $this->secretKey = getenv('CLOUDFLARE_TURNSTILE_SECRET_KEY') ?: '';
        $this->siteKey = getenv('CLOUDFLARE_TURNSTILE_SITE_KEY') ?: '';
    }

    /**
     * Verifikasi token Turnstile dari client
     * 
     * @param string $token Token dari Turnstile widget
     * @param string $remoteIp IP address pengguna (optional)
     * @return array ['success' => bool, 'errors' => array]
     */
    public function verify(string $token, string $remoteIp = ''): array
    {
        if (empty($token)) {
            return [
                'success' => false,
                'errors' => ['Token Turnstile tidak ditemukan']
            ];
        }

        if (empty($this->secretKey)) {
            return [
                'success' => false,
                'errors' => ['Secret key Cloudflare Turnstile tidak dikonfigurasi']
            ];
        }

        $client = \Config\Services::curlrequest([
            'timeout' => 10,
            'verify' => true
        ]);

        $data = [
            'secret' => $this->secretKey,
            'response' => $token
        ];

        if (!empty($remoteIp)) {
            $data['remoteip'] = $remoteIp;
        }

        try {
            $response = $client->post($this->verifyUrl, [
                'form_params' => $data,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            $body = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'errors' => ['Gagal memparse response dari Cloudflare']
                ];
            }

            if (isset($body['success']) && $body['success'] === true) {
                return [
                    'success' => true,
                    'errors' => []
                ];
            } else {
                $errors = $body['error-codes'] ?? ['Verifikasi gagal'];
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Cloudflare Turnstile verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['Gagal menghubungi server Cloudflare']
            ];
        }
    }

    /**
     * Get site key untuk digunakan di frontend
     * 
     * @return string
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }
}






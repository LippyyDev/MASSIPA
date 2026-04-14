<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SecurityHeadersFilter
 *
 * Menambahkan HTTP security headers pada setiap response untuk meningkatkan
 * keamanan aplikasi terhadap berbagai serangan seperti clickjacking,
 * MIME-sniffing, dan lainnya.
 */
class SecurityHeadersFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Tidak ada tindakan sebelum request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // HTTP Strict Transport Security (HSTS)
        // Memaksa browser menggunakan HTTPS selama 1 tahun, termasuk subdomain
        $response->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // X-Frame-Options
        // Mencegah halaman dimuat dalam <iframe> dari domain lain (anti-clickjacking)
        $response->setHeader('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options
        // Mencegah browser melakukan MIME-sniffing terhadap content-type yang dideklarasikan
        $response->setHeader('X-Content-Type-Options', 'nosniff');

        // Referrer-Policy
        // Hanya kirim referrer untuk same-origin, untuk halaman lain kirim tanpa referrer
        $response->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy
        // Membatasi fitur browser yang dapat digunakan oleh halaman
        $response->setHeader('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');

        // Content-Security-Policy (CSP)
        // Menentukan sumber resource yang diizinkan (script, style, font, gambar, dll.)
        // Berdasarkan scan lengkap seluruh views (guest, user, admin, components)
        $csp = implode('; ', [
            // Default: hanya dari domain sendiri
            "default-src 'self'",

            // Script: self + CDN yang digunakan (jsdelivr, jquery, datatables, cdnjs)
            // 'unsafe-inline' diperlukan karena banyak view menggunakan <script> inline
            // 'unsafe-eval' diperlukan oleh beberapa library (Select2, DataTables)
            "script-src 'self' cdn.jsdelivr.net code.jquery.com cdn.datatables.net cdnjs.cloudflare.com 'unsafe-inline' 'unsafe-eval'",

            // Style: self + CDN CSS (jsdelivr, google fonts, cdnjs/font-awesome, datatables)
            // 'unsafe-inline' diperlukan karena banyak view menggunakan style="" inline
            "style-src 'self' cdn.jsdelivr.net fonts.googleapis.com cdnjs.cloudflare.com cdn.datatables.net 'unsafe-inline'",

            // Font: self + Google Fonts files, jsdelivr (Bootstrap Icons), cdnjs (Font Awesome)
            // data: diperlukan oleh beberapa icon font yang embed font dalam CSS
            "font-src 'self' fonts.gstatic.com cdn.jsdelivr.net cdnjs.cloudflare.com data:",

            // Gambar: self + data URI (untuk base64 image) + blob (untuk object URL)
            "img-src 'self' data: blob:",

            // Koneksi AJAX/Fetch + source map CDN (diunduh oleh browser dev tools)
            "connect-src 'self' cdn.jsdelivr.net cdn.datatables.net",

            // Frame/iframe: self + Google Maps (digunakan di HalamanAwal)
            "frame-src 'self' www.google.com",

            // Mencegah semua plugin (Flash, dll.)
            "object-src 'none'",

            // Batasi base URI ke domain sendiri
            "base-uri 'self'",

            // Batasi form action ke domain sendiri
            "form-action 'self'",

            // Sama dengan X-Frame-Options SAMEORIGIN (versi CSP modern)
            "frame-ancestors 'self'",
        ]);
        $response->setHeader('Content-Security-Policy', $csp);

        return $response;
    }
}

<?php

if (!function_exists('getBulanIndo')) {
    function getBulanIndo($bulan)
    {
        $nama_bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $bulan = (int)$bulan;
        return $nama_bulan[$bulan] ?? 'Tidak Diketahui';
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'tahun',
            'm' => 'bulan',
            'w' => 'minggu',
            'd' => 'hari',
            'h' => 'jam',
            'i' => 'menit',
            's' => 'detik',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
            } else {
                unset($v);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
    }
}

if (!function_exists("tanggalIndo")) {
    function tanggalIndo($tanggal)
    {
        // Validate input
        if (empty($tanggal) || !is_string($tanggal)) {
            return 'Tanggal tidak valid';
        }

        $bulan = array(
            1 => "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember"
        );

        $pecahkan = explode("-", $tanggal);
        // Check if the array has at least 3 elements
        if (count($pecahkan) !== 3) {
            return 'Format tanggal tidak valid';
        }

        $day = $pecahkan[2];
        $month = (int)$pecahkan[1];
        $year = $pecahkan[0];

        // Validate numeric values
        if (!is_numeric($day) || !is_numeric($month) || !is_numeric($year)) {
            return 'Tanggal tidak valid';
        }

        return $day . " " . ($bulan[$month] ?? 'Bulan tidak valid') . " " . $year;
    }
}

if (!function_exists("getStatusBadgeColor")) {
    function getStatusBadgeColor($status)
    {
        switch ($status) {
            case "terkirim":
                return "warning";
            case "dilihat":
                return "info";
            case "diterima":
                return "success";
            case "ditolak":
                return "danger";
            default:
                return "secondary";
        }
    }
}

if (!function_exists("getStatusIndo")) {
    function getStatusIndo($status)
    {
        switch ($status) {
            case "terkirim":
                return "Pending";
            case "dilihat":
                return "Dilihat";
            case "diterima":
                return "Diterima";
            case "ditolak":
                return "Ditolak";
            default:
                return "Tidak Diketahui";
        }
    }
}

if (!function_exists('sensor_nama_publik')) {
    function sensor_nama_publik($nama) {
        $parts = explode(',', $nama, 2);
        $nama_utama = trim($parts[0]);
        $gelar = isset($parts[1]) ? ',' . $parts[1] : '';
        $inisial = implode(' ', array_map(function($word) {
            return mb_substr($word, 0, 1, 'UTF-8');
        }, preg_split('/\s+/', $nama_utama)));
        return $inisial . $gelar;
    }
}
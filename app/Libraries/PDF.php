<?php
namespace App\Libraries;

require_once ROOTPATH . 'vendor/tecnickcom/tcpdf/tcpdf.php';

// Load helper functions
helper('app');

class PDF extends \TCPDF {
    public $current_bulan;
    public $current_tahun;
    public $current_satker_name;
    public $column_widths;
    public $header_height1 = 12;
    public $header_height2 = 6;

    public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format);
        $this->column_widths = [
            'default' => [8, 45, 25, 30, 30, 10, 10, 10, 10, 10, 10, 10, 10, 35, 15],
            'user_satker' => [8, 50, 50, 15, 10, 10, 10, 10, 10, 10, 10, 10, 40, 20],
            'rekap_kedisiplinan' => [8, 35, 35, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8, 8],
            // Lebar kolom khusus untuk export hukuman disiplin:
            'hukuman_disiplin' => [8, 40, 35, 30, 32, 50, 50, 50]
        ];
        $this->SetMargins(10, 10, 10);
        $this->SetFont('dejavusans', '', 7);
        $this->header_height1 = 10;
        $this->header_height2 = 5;
    }

    /**
     * Header untuk Laporan Disiplin Hakim
     * @param int|null $bulan
     * @param int|null $tahun
     * @param string|null $satker
     */
    public function drawHeaderLaporanDisiplin($bulan = null, $tahun = null, $satker = null)
    {
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 5, 'LAPORAN DISIPLIN HAKIM', 0, 1, 'C');
        $this->SetFont('dejavusans', 'B', 8);
        $this->Cell(0, 5, 'YANG TIDAK MEMATUHI KETENTUAN JAM KERJA SESUAI DENGAN PERMA NO 7 TAHUN 2016', 0, 1, 'C');
        $this->SetFont('dejavusans', 'B', 9);
        $bulan = $bulan ?? date('n');
        $tahun = $tahun ?? date('Y');
        $satker = $satker ?? 'SATKER';
        $this->Ln(5);
        
        // Hitung posisi untuk alignment dengan tabel
        $column_widths = $this->column_widths['default'];
        $table_width = array_sum($column_widths);
        $page_width = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
        $x_start = $this->getMargins()['left'] + ($page_width - $table_width) / 2;
        
        // SATKER di kiri (sejajar dengan awal tabel)
        $this->SetX($x_start);
        $this->Cell(0, 5, 'SATKER: ' . strtoupper($satker), 0, 0, 'L');
        
        // BULAN di kanan (sejajar dengan akhir tabel)
        $bulan_text = 'BULAN: ' . strtoupper(getBulanIndo($bulan) . ' ' . $tahun);
        $bulan_width = $this->GetStringWidth($bulan_text) + 2; // Tambah sedikit margin
        $this->SetX($x_start + $table_width - $bulan_width);
        $this->Cell($bulan_width, 5, $bulan_text, 0, 1, 'R');
        
        $this->Ln(2);
    }

    /**
     * Header untuk Hukuman Disiplin Hakim dan Pegawai (Admin)
     */
    public function drawHeaderHukumanDisiplin()
    {
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 5, 'HUKUMAN DISIPLIN HAKIM DAN PEGAWAI', 0, 1, 'C');
        $this->SetFont('dejavusans', 'B', 8);
        $this->Cell(0, 5, 'WILAYAH SATUAN KERJA PENGADILAN TINGGI AGAMA MAKASSAR', 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('dejavusans', '', 9);
        $this->Cell(0, 5, 'Tanggal Cetak: ' . date('d-m-Y H:i'), 0, 1, 'R');
        $this->Ln(2);
    }

    /**
     * Header untuk Hukuman Disiplin Hakim dan Pegawai (User)
     */
    public function drawHeaderHukumanDisiplinUser($satker_nama = '')
    {
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 5, 'HUKUMAN DISIPLIN HAKIM DAN PEGAWAI', 0, 1, 'C');
        
        $this->SetFont('dejavusans', '', 9);
        $this->Cell(0, 5, 'Tanggal Cetak: ' . date('d-m-Y H:i'), 0, 1, 'R');
        
        // SATKER di kiri sejajar dengan tabel - tanpa spacing tambahan
        $this->SetFont('dejavusans', 'B', 9);
        $this->Cell(0, 5, 'SATKER: ' . strtoupper($satker_nama), 0, 1, 'L');
    }

    public function drawComplexHeader($type = 'default') {
        $this->SetFont('dejavusans', 'B', 7);
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);

        $column_widths = $this->column_widths[$type] ?? $this->column_widths['default'];
        $table_width = array_sum($column_widths);
        $page_width = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
        $x_start = $this->getMargins()['left'] + ($page_width - $table_width) / 2;
        $y_start = $this->GetY();
        $total_header_height = $this->header_height1 + $this->header_height2;

        if ($type === 'rekap_kedisiplinan') {
            $span_headers = [
                ["NO", $column_widths[0]],
                ["USER", $column_widths[1]],
                ["SATKER", $column_widths[2]],
            ];
            $bulan_headers = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

            $current_x = $x_start;
            for ($i = 0; $i < 3; $i++) {
                $this->MultiCell($span_headers[$i][1], $total_header_height, $span_headers[$i][0], 1, 'C', true, 0, $current_x, $y_start, true, 0, false, true, $total_header_height, 'M');
                $current_x += $span_headers[$i][1];
            }

            $bulan_width = array_sum(array_slice($column_widths, 3, 12));
            $bulan_x = $current_x;
            $this->MultiCell($bulan_width, $this->header_height1, "BULAN", 1, 'C', true, 0, $bulan_x, $y_start, true, 0, false, true, $this->header_height1, 'M');

            $subheader_y = $y_start + $this->header_height1;
            $current_x_sub = $bulan_x;
            for ($i = 0; $i < 12; $i++) {
                $sub_width = $column_widths[3 + $i];
                $this->MultiCell($sub_width, $this->header_height2, $bulan_headers[$i], 1, 'C', true, 0, $current_x_sub, $subheader_y, true, 0, false, true, $this->header_height2, 'M');
                $current_x_sub += $sub_width;
            }
        } else {
            if ($type === 'user_satker') {
                $span_headers = [
                    ["NO", $column_widths[0]],
                    ["SATKER", $column_widths[1]],
                    ["ALAMAT", $column_widths[2]],
                    ["TOTAL PEGAWAI", $column_widths[3]],
                    ["BENTUK\nPEMBINAAN", $column_widths[12]],
                    ["KET", $column_widths[13]],
                ];

                $current_x = $x_start;
                for ($i = 0; $i < 4; $i++) {
                    $this->MultiCell($span_headers[$i][1], $total_header_height, $span_headers[$i][0], 1, 'C', true, 0, $current_x, $y_start, true, 0, false, true, $total_header_height, 'M');
                    $current_x += $span_headers[$i][1];
                }

                $uraian_width = array_sum(array_slice($column_widths, 4, 8));
                $uraian_x = $current_x;
                $this->MultiCell($uraian_width, $this->header_height1, "URAIAN AKUMULASI TIDAK\nDIPATUHKAN", 1, 'C', true, 0, $uraian_x, $y_start, true, 0, false, true, $this->header_height1, 'M');

                $subheader_y = $y_start + $this->header_height1;
                $subheaders = ["t", "kti", "tam", "tk", "pa", "tms", "tap", "tmk"];
                $current_x_sub = $uraian_x;
                for ($i = 0; $i < 8; $i++) {
                    $sub_width = $column_widths[4 + $i];
                    $this->MultiCell($sub_width, $this->header_height2, $subheaders[$i], 1, 'C', true, 0, $current_x_sub, $subheader_y, true, 0, false, true, $this->header_height2, 'M');
                    $current_x_sub += $sub_width;
                }

                $current_x = $uraian_x + $uraian_width;
                for ($i = 4; $i < count($span_headers); $i++) {
                    $this->MultiCell($span_headers[$i][1], $total_header_height, $span_headers[$i][0], 1, 'C', true, 0, $current_x, $y_start, true, 0, false, true, $total_header_height, 'M');
                    $current_x += $span_headers[$i][1];
                }
            } else {
                $span_headers = [
                    ["NO", $column_widths[0]],
                    ["NAMA/NIP", $column_widths[1]],
                    ["PANGKAT/\nGOL. RUANG", $column_widths[2]],
                    ["JABATAN", $column_widths[3]],
                    ["SATUAN KERJA", $column_widths[4]],
                    ["BENTUK\nPEMBINAAN", $column_widths[13]],
                    ["KET", $column_widths[14]],
                ];

                $current_x = $x_start;
                for ($i = 0; $i < 5; $i++) {
                    $this->MultiCell($span_headers[$i][1], $total_header_height, $span_headers[$i][0], 1, 'C', true, 0, $current_x, $y_start, true, 0, false, true, $total_header_height, 'M');
                    $current_x += $span_headers[$i][1];
                }

                $uraian_width = array_sum(array_slice($column_widths, 5, 8));
                $uraian_x = $current_x;
                $this->MultiCell($uraian_width, $this->header_height1, "URAIAN AKUMULASI TIDAK\nDIPATUHKAN", 1, 'C', true, 0, $uraian_x, $y_start, true, 0, false, true, $this->header_height1, 'M');

                $subheader_y = $y_start + $this->header_height1;
                $subheaders = ["t", "kti", "tam", "tk", "pa", "tms", "tap", "tmk"];
                $current_x_sub = $uraian_x;
                for ($i = 0; $i < 8; $i++) {
                    $sub_width = $column_widths[5 + $i];
                    $this->MultiCell($sub_width, $this->header_height2, $subheaders[$i], 1, 'C', true, 0, $current_x_sub, $subheader_y, true, 0, false, true, $this->header_height2, 'M');
                    $current_x_sub += $sub_width;
                }

                $current_x = $uraian_x + $uraian_width;
                for ($i = 5; $i < count($span_headers); $i++) {
                    $this->MultiCell($span_headers[$i][1], $total_header_height, $span_headers[$i][0], 1, 'C', true, 0, $current_x, $y_start, true, 0, false, true, $total_header_height, 'M');
                    $current_x += $span_headers[$i][1];
                }
            }
        }

        $this->SetY($y_start + $total_header_height);
    }

    public function drawComplexHeaderHukumanDisiplin() {
        $this->SetFont('dejavusans', 'B', 8);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);
    
        // Fixed columns dan flexible columns
        $fixed = [10, 40, 35, 30, 40]; // NO, NAMA, JABATAN, NO SK, PERIODE
        $page_width = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
        $fixed_total = array_sum($fixed);
        $flex_count = 3; // HUKUMAN, PERATURAN, KETERANGAN
        $flex_width = ($page_width - $fixed_total) / $flex_count;
        
        $column_widths = [
            $fixed[0], $fixed[1], $fixed[2], $fixed[3], $fixed[4],
            $flex_width, $flex_width, $flex_width
        ];
    
        $start_x = $this->getMargins()['left'];
        $y_start = $this->GetY();
        
        $headers = [
            'NO',
            'NAMA PEGAWAI',
            'JABATAN',
            'NO SK',
            'PERIODE\n(MULAI S/D BERAKHIR)',
            'HUKUMAN DISIPLIN\nYANG DIJATUHKAN',
            'PERATURAN\nYANG DILANGGAR',
            'KETERANGAN'
        ];
    
        // Hitung tinggi header maksimal
        $max_height = 0;
        for ($i = 0; $i < count($headers); $i++) {
            $cell_height = $this->getStringHeight($column_widths[$i], $headers[$i], false, true, '', 1, 'C');
            if ($cell_height > $max_height) $max_height = $cell_height;
        }
    
        // Pastikan minimal height untuk header
        $max_height = max($max_height, 12);
    
        // Render header satu per satu dengan posisi yang tepat
        $x = $start_x;
        for ($i = 0; $i < count($headers); $i++) {
            $this->MultiCell(
                $column_widths[$i],
                $max_height,
                $headers[$i],
                1, // Border - ini akan membuat garis
                'C',
                true,
                0,
                $x,
                $y_start,
                true,
                0,
                false,
                true,
                $max_height,
                'M'
            );
            $x += $column_widths[$i];
        }
        
        // Update column_widths untuk digunakan di drawTableRow
        $this->column_widths['hukuman_disiplin'] = $column_widths;
        
        // PERBAIKAN: Pastikan posisi Y di-update dengan benar
        $this->SetY($y_start + $max_height);
    }

    public function drawComplexHeaderHukumanDisiplinPublic() {
        $this->SetFont('dejavusans', 'B', 8);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);
    
        // Kolom publik: NO, NAMA, JABATAN, HUKUMAN, PERATURAN, KETERANGAN
        $fixed = [10, 40, 35]; // NO, NAMA, JABATAN
        $page_width = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
        $fixed_total = array_sum($fixed);
        $flex_count = 3; // HUKUMAN, PERATURAN, KETERANGAN
        $flex_width = ($page_width - $fixed_total) / $flex_count;
        
        $column_widths = [
            $fixed[0], $fixed[1], $fixed[2],
            $flex_width, $flex_width, $flex_width
        ];
    
        $start_x = $this->getMargins()['left'];
        $y_start = $this->GetY();
        
        $headers = [
            'NO',
            'NAMA',
            'JABATAN',
            'HUKUMAN DISIPLIN\nYANG DIJATUHKAN',
            'PERATURAN\nYANG DILANGGAR',
            'KETERANGAN'
        ];
    
        // Hitung tinggi header maksimal
        $max_height = 0;
        for ($i = 0; $i < count($headers); $i++) {
            $cell_height = $this->getStringHeight($column_widths[$i], $headers[$i], false, true, '', 1, 'C');
            if ($cell_height > $max_height) $max_height = $cell_height;
        }
    
        // Pastikan minimal height untuk header
        $max_height = max($max_height, 12);
    
        // Render header satu per satu dengan posisi yang tepat
        $x = $start_x;
        for ($i = 0; $i < count($headers); $i++) {
            $this->MultiCell(
                $column_widths[$i],
                $max_height,
                $headers[$i],
                1, // Border - ini akan membuat garis
                'C',
                true,
                0,
                $x,
                $y_start,
                true,
                0,
                false,
                true,
                $max_height,
                'M'
            );
            $x += $column_widths[$i];
        }
        
        // Update column_widths untuk digunakan di drawTableRowHukumanDisiplinPublic
        $this->column_widths['hukuman_disiplin_public'] = $column_widths;
        $this->SetY($y_start + $max_height);
    }

    

    // Tambahkan method khusus untuk hukuman disiplin
public function drawTableRowHukumanDisiplin($data, $row_height = 12) {
    $this->SetFont('dejavusans', '', 7);
    $this->SetFillColor(255, 255, 255);
    $this->SetTextColor(0);
    $this->SetDrawColor(0);
    $this->SetLineWidth(0.2);

    $column_widths = $this->column_widths['hukuman_disiplin'];
    $start_x = $this->getMargins()['left'];
    $y_start = $this->GetY();
    
    // Hitung tinggi maksimal untuk semua cell dalam row ini
    $max_height = 0;
    for ($i = 0; $i < count($data); $i++) {
        $align = ($i == 0) ? 'C' : 'L';
        $cell_height = $this->getStringHeight($column_widths[$i], $data[$i], false, true, '', 1, $align);
        if ($cell_height > $max_height) $max_height = $cell_height;
    }
    
    // Pastikan minimal height
    $max_height = max($max_height, $row_height);
    
    // Render setiap cell dengan tinggi yang sama
    $x = $start_x;
    for ($i = 0; $i < count($data); $i++) {
        $align = ($i == 0) ? 'C' : 'L';
        $this->MultiCell(
            $column_widths[$i],
            $max_height,
            $data[$i],
            1, // Border - ini akan membuat garis
            $align,
            true,
            0,
            $x,
            $y_start,
            true,
            0,
            false,
            true,
            $max_height,
            'M'
        );
        $x += $column_widths[$i];
    }
    
    $this->SetY($y_start + $max_height);
}
    
    // Fixed drawTableRow method
    public function drawTableRow($data, $row_height = 7, $type = 'default', $sync_height = false) {
        $font_size = ($type === 'rekap_kedisiplinan') ? 6 : 7;
        $this->SetFont('dejavusans', '', $font_size);
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);
    
        // Khusus untuk hukuman_disiplin dengan sync height
        if ($type === 'hukuman_disiplin' && $sync_height) {
            $column_widths = $this->column_widths['hukuman_disiplin'];
            $start_x = $this->getMargins()['left'];
            $y_start = $this->GetY();
            
            // Hitung tinggi maksimal untuk semua cell dalam row ini
            $max_height = 0;
            for ($i = 0; $i < count($data); $i++) {
                // Semua kolom data menggunakan align left kecuali kolom pertama (NO)
                $align = ($i == 0) ? 'C' : 'L';
                $cell_height = $this->getStringHeight($column_widths[$i], $data[$i], false, true, '', 1, $align);
                if ($cell_height > $max_height) $max_height = $cell_height;
            }
            
            // Pastikan minimal height
            $max_height = max($max_height, $row_height);
            
            // Render setiap cell dengan tinggi yang sama
            $x = $start_x;
            for ($i = 0; $i < count($data); $i++) {
                // Kolom NO tetap center, yang lain left
                $align = ($i == 0) ? 'C' : 'L';
                $this->SetXY($x, $y_start);
                $this->MultiCell(
                    $column_widths[$i],
                    $max_height,
                    $data[$i],
                    1, // Border - ini akan membuat garis
                    $align,
                    true,
                    0,
                    $x,
                    $y_start,
                    true,
                    0,
                    false,
                    true,
                    $max_height,
                    'M'
                );
                $x += $column_widths[$i];
            }
            
            $this->SetY($y_start + $max_height);
            return;
        }
    
        // Logic untuk type lainnya (default, user_satker, rekap_kedisiplinan)
        $column_widths = $this->column_widths[$type] ?? $this->column_widths['default'];
        $table_width = array_sum($column_widths);
        $page_width = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
        $start_x = $this->getMargins()['left'] + ($page_width - $table_width) / 2;
        $y_start = $this->GetY();
    
        for ($i = 0; $i < count($data); $i++) {
            $width = isset($column_widths[$i]) ? $column_widths[$i] : end($column_widths);
            $content = $data[$i];
            $align = 'C';
            $this->MultiCell(
                $width,
                $row_height,
                $content,
                1, // Border
                $align,
                true,
                0,
                $start_x,
                $y_start,
                true,
                0,
                false,
                true,
                $row_height,
                'M'
            );
            $start_x += $width;
        }
        $this->SetY($y_start + $row_height);
    }

    public function drawKeterangan() {
        $this->Ln(5);
        $this->SetFont('helvetica', 'B', 8);
        $this->Cell(0, 5, 'KETERANGAN:', 0, 1, 'L');
        $this->SetFont('helvetica', '', 7);

        // Keterangan disusun ke bawah dengan jarak antar item
        $keterangan_items = [
            't = TERLAMBAT',
            'tam = TIDAK ABSEN MASUK',
            'pa = PULANG AWAL',
            'tap = TIDAK ABSEN PULANG',
            'kti = KELUAR KANTOR TIDAK IZIN ATASAN',
            'tk = TIDAK MASUK TANPA KETERANGAN',
            'tms = TIDAK MASUK KARENA SAKIT TANPA MENGAJUKAN CUTI SAKIT',
            'tmk = TIDAK MASUK KERJA'
        ];

        foreach ($keterangan_items as $item) {
            $this->Cell(0, 4, $item, 0, 1, 'L');
        }
    }

    public function drawKeteranganRekapKedisiplinan() {
        $this->Ln(5);
        $this->SetFont('dejavusans', 'B', 7);
        $this->Cell(0, 5, 'KETERANGAN:', 0, 1, 'L');
        $this->SetFont('dejavusans', '', 6);

        $keterangan = [
            ['✔ = Laporan Diterima', 'P = Laporan Terkirim/Dilihat'],
            ['- = Tidak Ada Laporan', '']
        ];

        $col_width = ($this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right']) / 2;
        foreach ($keterangan as $ket) {
            $y_before = $this->GetY();
            $this->MultiCell($col_width, 4, $ket[0], 0, 'L', false, 0, $this->getMargins()['left'], $y_before);
            $y_after1 = $this->GetY();
            $this->MultiCell($col_width, 4, $ket[1], 0, 'L', false, 1, $this->getMargins()['left'] + $col_width, $y_before);
            $y_after2 = $this->GetY();
            $this->SetY(max($y_after1, $y_after2));
        }
    }

    public function drawTandaTangan($tanda_tangan, $is_tanda_tangan_gambar = false) {
        if ($tanda_tangan) {
            $bottom_margin = $this->getBreakMargin();
            $current_y = $this->GetY();
            $page_height = $this->getPageHeight();
            $desired_y = $page_height - $bottom_margin - 35; // Kembalikan ke posisi semula

            if ($current_y > ($page_height - $bottom_margin - 40)) { // Kembalikan threshold ke semula
                $this->AddPage();
                $this->drawComplexHeader('rekap_kedisiplinan');
                $current_y = $this->GetY();
            } else {
                $this->SetY($desired_y);
            }

            $this->SetFont('dejavusans', '', 8);
            $ttd_width = 80;
            $left_margin_ttd = $this->getPageWidth() - $this->getMargins()['right'] - $ttd_width;

            if ($is_tanda_tangan_gambar) {
                // Tanda tangan gambar: tempat, tanggal, lalu gambar
                $this->SetX($left_margin_ttd);
                $this->Cell($ttd_width, 5, $tanda_tangan['tempat'] . ', ' . tanggalIndo($tanda_tangan['tanggal']), 0, 1, 'C');
                $this->Ln(2); // Kembalikan ke jarak semula
                // Gambar PNG/JPG
                $img_path = WRITEPATH . 'uploads/ttd/' . $tanda_tangan['file_path'];
                if (file_exists($img_path)) {
                    list($img_w, $img_h) = getimagesize($img_path);
                    $max_w = 65; // mm
                    $max_h = 32; // mm
                    $ratio = min($max_w / $img_w, $max_h / $img_h);
                    $w = $img_w * $ratio;
                    $h = $img_h * $ratio;
                    $x = $this->getPageWidth() - $this->getMargins()['right'] - $w;
                    $y = $this->GetY();
                    $this->Image($img_path, $x, $y, $w, $h, '', '', '', false, 300, '', false, false, 0, false, false, false);
                    $this->Ln($h + 2);
                } else {
                    $this->SetX($left_margin_ttd);
                    $this->Cell($ttd_width, 5, '[Gambar tidak ditemukan]', 0, 1, 'C');
                }
            } else {
                // Tanda tangan manual (teks)
                $this->SetX($left_margin_ttd);
                $this->Cell($ttd_width, 5, $tanda_tangan['lokasi'] . ', ' . tanggalIndo($tanda_tangan['tanggal']), 0, 1, 'C');
                $this->SetX($left_margin_ttd);
                $this->Cell($ttd_width, 5, $tanda_tangan['nama_jabatan'], 0, 1, 'C');
                $this->Ln(10); // Kembalikan ke jarak semula
                $this->SetX($left_margin_ttd);
                $this->SetFont('dejavusans', 'B', 8);
                $this->Cell($ttd_width, 5, $tanda_tangan['nama_penandatangan'], 0, 1, 'C');
                $this->SetFont('dejavusans', '', 8);
                $this->SetX($left_margin_ttd);
                $this->Cell($ttd_width, 5, 'NIP. ' . $tanda_tangan['nip_penandatangan'], 0, 1, 'C');
            }
        } else {
            $current_y = $this->GetY();
            $page_height = $this->getPageHeight();
            $bottom_margin = $this->getBreakMargin();
            if ($current_y > ($page_height - $bottom_margin - 20)) {
                $this->AddPage();
                $this->drawComplexHeader('rekap_kedisiplinan');
            }
            $this->Ln(10);
            $this->SetFont('dejavusans', '', 8);
            $this->Cell(0, 5, 'Tanda tangan belum tersedia.', 0, 1, 'C');
        }
    }

    // Override Header() agar garis horizontal default TCPDF tidak muncul
    public function Header() {}

    // Ganti nama agar tidak bentrok dengan bawaan TCPDF
    public function checkCustomPageBreak($row_height) {
        $buffer_bawah = 20; // mm, jarak aman dari bawah sebelum page break
        if ($this->GetY() + $row_height > ($this->getPageHeight() - $this->getBreakMargin() - $buffer_bawah)) {
            $this->drawBottomTableLine();
            $this->AddPage('L');
            $this->drawHeaderHukumanDisiplin();
            $this->drawComplexHeaderHukumanDisiplin();
        }
    }

    // Fungsi untuk menggambar garis horizontal di bawah tabel
    public function drawBottomTableLine() {
        $column_widths = $this->column_widths['hukuman_disiplin'];
        $start_x = $this->getMargins()['left'];
        $y = $this->GetY();
        $width = array_sum($column_widths);
        $this->Line($start_x, $y, $start_x + $width, $y);
    }

    // Tambahkan method khusus merge cell pesan kosong rekap user satker
    public function drawTableRowUserSatkerKosong($row_number, $satker_nama, $alamat, $total_pegawai, $pesan, $row_height = 10) {
        $type = 'user_satker';
        $column_widths = $this->column_widths[$type];
        $table_width = array_sum($column_widths);
        $page_width = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
        $start_x = $this->getMargins()['left'] + ($page_width - $table_width) / 2;
        $y_start = $this->GetY();
        $this->SetFont('dejavusans', '', 7);
        $this->SetFillColor(255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);
        // Kolom 1-4
        $this->SetXY($start_x, $y_start);
        $this->MultiCell($column_widths[0], $row_height, $row_number, 1, 'C', true, 0, $start_x, $y_start, true, 0, false, true, $row_height, 'M');
        $x = $start_x + $column_widths[0];
        $this->SetXY($x, $y_start);
        $this->MultiCell($column_widths[1], $row_height, $satker_nama, 1, 'C', true, 0, $x, $y_start, true, 0, false, true, $row_height, 'M');
        $x += $column_widths[1];
        $this->SetXY($x, $y_start);
        $this->MultiCell($column_widths[2], $row_height, $alamat, 1, 'C', true, 0, $x, $y_start, true, 0, false, true, $row_height, 'M');
        $x += $column_widths[2];
        $this->SetXY($x, $y_start);
        $this->MultiCell($column_widths[3], $row_height, $total_pegawai, 1, 'C', true, 0, $x, $y_start, true, 0, false, true, $row_height, 'M');
        $x += $column_widths[3];
        // Kolom 5-14 (merge)
        $merge_width = 0;
        for ($i = 4; $i < count($column_widths); $i++) $merge_width += $column_widths[$i];
        $this->SetXY($x, $y_start);
        $this->MultiCell($merge_width, $row_height, $pesan, 1, 'C', true, 0, $x, $y_start, true, 0, false, true, $row_height, 'M');
        $this->SetY($y_start + $row_height);
    }

    public function drawTableRowHukumanDisiplinPublic($data, $row_height = 12) {
        $this->SetFont('dejavusans', '', 7);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(0.2);

        $column_widths = $this->column_widths['hukuman_disiplin_public'];
        $start_x = $this->getMargins()['left'];
        $y_start = $this->GetY();
        
        // Hitung tinggi maksimal untuk semua cell dalam row ini
        $max_height = 0;
        for ($i = 0; $i < count($data); $i++) {
            $align = ($i == 0) ? 'C' : 'L';
            $cell_height = $this->getStringHeight($column_widths[$i], $data[$i], false, true, '', 1, $align);
            if ($cell_height > $max_height) $max_height = $cell_height;
        }
        $max_height = max($max_height, $row_height);
        $x = $start_x;
        for ($i = 0; $i < count($data); $i++) {
            $align = ($i == 0) ? 'C' : 'L';
            $this->MultiCell(
                $column_widths[$i],
                $max_height,
                $data[$i],
                1, // Border - ini akan membuat garis
                $align,
                true,
                0,
                $x,
                $y_start,
                true,
                0,
                false,
                true,
                $max_height,
                'M'
            );
            $x += $column_widths[$i];
        }
        $this->SetY($y_start + $max_height);
    }

    /**
     * Generate PDF untuk tracking kedisiplinan pegawai
     */
    public function generateTrackingKedisiplinan($data, $filename = 'tracking_kedisiplinan.pdf')
    {
        $this->AddPage();
        
        // Header
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'TRACKING KEDISIPLINAN PEGAWAI', 0, 1, 'C');
        $this->Ln(5);
        
        // Informasi Pegawai
        $pegawai = $data['pegawai'];
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'INFORMASI PEGAWAI', 0, 1, 'L');
        $this->SetFont('Arial', '', 10);
        
        $this->Cell(30, 6, 'Nama', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'L');
        $this->Cell(0, 6, $pegawai['nama'], 0, 1, 'L');
        
        $this->Cell(30, 6, 'NIP', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'L');
        $this->Cell(0, 6, $pegawai['nip'], 0, 1, 'L');
        
        $this->Cell(30, 6, 'Jabatan', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'L');
        $this->Cell(0, 6, $pegawai['jabatan'] ?? 'Tidak tersedia', 0, 1, 'L');
        
        $this->Cell(30, 6, 'Pangkat/Gol', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'L');
        $this->Cell(0, 6, ($pegawai['pangkat'] ?? 'Tidak tersedia') . ' ' . ($pegawai['golongan'] ?? ''), 0, 1, 'L');
        
        $this->Cell(30, 6, 'Satker', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'L');
        $this->Cell(0, 6, $pegawai['satker_nama'] ?? 'Tidak tersedia', 0, 1, 'L');
        
        $this->Cell(30, 6, 'Status', 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'L');
        $this->Cell(0, 6, ucfirst($pegawai['status']), 0, 1, 'L');
        
        $this->Ln(10);
        
        // Statistik Pelanggaran
        $statistik = $data['statistik'];
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'RINGKASAN PELANGGARAN', 0, 1, 'L');
        $this->SetFont('Arial', '', 10);
        
        $stats = [
            'Terlambat' => $statistik['terlambat'] ?? 0,
            'Tidak Absen Masuk' => $statistik['tidak_absen_masuk'] ?? 0,
            'Pulang Awal' => $statistik['pulang_awal'] ?? 0,
            'Tidak Absen Pulang' => $statistik['tidak_absen_pulang'] ?? 0,
            'Keluar Tidak Izin' => $statistik['keluar_tidak_izin'] ?? 0,
            'Tidak Masuk Tanpa Keterangan' => $statistik['tidak_masuk_tanpa_ket'] ?? 0,
            'Tidak Masuk Sakit' => $statistik['tidak_masuk_sakit'] ?? 0,
            'Tidak Masuk Kerja' => $statistik['tidak_masuk_kerja'] ?? 0
        ];
        
        $col1_width = 80;
        $col2_width = 20;
        
        foreach ($stats as $label => $value) {
            $this->Cell($col1_width, 6, $label, 0, 0, 'L');
            $this->Cell($col2_width, 6, ': ' . $value, 0, 1, 'L');
        }
        
        $this->Ln(10);
        
        // Tabel Track Record
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'TRACK RECORD KEDISIPLINAN', 0, 1, 'L');
        
        // Header tabel
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(200, 200, 200);
        
        $headers = ['No', 'Bulan/Tahun', 'Satker', 'T', 'TAM', 'PA', 'TAP', 'KTI', 'TMK', 'TMS', 'TMK', 'Pembinaan'];
        $widths = [10, 25, 40, 8, 8, 8, 8, 8, 8, 8, 8, 30];
        
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Data tabel
        $this->SetFont('Arial', '', 7);
        $trackRecord = $data['track_record'];
        
        if (!empty($trackRecord)) {
            $no = 1;
            foreach ($trackRecord as $record) {
                $bulanTahun = $this->getBulanName($record['bulan']) . ' ' . $record['tahun'];
                
                $this->Cell($widths[0], 6, $no, 1, 0, 'C');
                $this->Cell($widths[1], 6, $bulanTahun, 1, 0, 'C');
                $this->Cell($widths[2], 6, substr($record['satker_nama'] ?? 'Tidak tersedia', 0, 25), 1, 0, 'L');
                $this->Cell($widths[3], 6, $record['terlambat'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[4], 6, $record['tidak_absen_masuk'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[5], 6, $record['pulang_awal'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[6], 6, $record['tidak_absen_pulang'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[7], 6, $record['keluar_tidak_izin'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[8], 6, $record['tidak_masuk_tanpa_ket'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[9], 6, $record['tidak_masuk_sakit'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[10], 6, $record['tidak_masuk_kerja'] ?? 0, 1, 0, 'C');
                $this->Cell($widths[11], 6, substr($record['bentuk_pembinaan'] ?? '-', 0, 20), 1, 1, 'L');
                
                $no++;
            }
        } else {
            $this->Cell(array_sum($widths), 6, 'Tidak ada data kedisiplinan', 1, 1, 'C');
        }
        
        // Footer
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Dibuat pada: ' . date('d/m/Y H:i:s'), 0, 0, 'C');
        
        // Simpan file
        $filepath = WRITEPATH . 'uploads/' . $filename;
        $this->Output('F', $filepath);
        
        return $filepath;
    }
    
    /**
     * Helper function untuk mendapatkan nama bulan
     */
    private function getBulanName($bulan)
    {
        $bulanNames = [
            '', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];
        return $bulanNames[$bulan] ?? 'Tidak valid';
    }
}
<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\RekapPegawaiSatkerModel;
use App\Models\PegawaiModel;
use App\Models\SatkerModel;
use App\Models\NotifikasiModel;
use App\Models\TandaTanganModel;
use App\Models\TandaTanganGambarModel;
use App\Libraries\PDF;

class RekapPegawaiSatkerController extends BaseController
{
    public function rekapUserSatker()
    {
        helper('app');
        $kedisiplinanModel = new RekapPegawaiSatkerModel();
        $pegawaiModel = new PegawaiModel();
        $satkerModel = new SatkerModel();
        $notifikasiModel = new NotifikasiModel();
        $session = session();
        $filter_bulan = $this->request->getVar('bulan') ?? date('m');
        $filter_tahun = $this->request->getVar('tahun') ?? date('Y');
        // Simpan filter terakhir ke session untuk dipakai export (WebView kadang mengirim tanpa body)
        $session->set('last_rekap_user_satker_bulan', $filter_bulan);
        $session->set('last_rekap_user_satker_tahun', $filter_tahun);
        $satker_list = $satkerModel->orderBy('nama', 'ASC')->findAll();
        $rekap_data = [];
        foreach ($satker_list as $satker) {
            $riwayatMutasiModel = new \App\Models\RiwayatMutasiModel();
            $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
            $pegawai_ids = $riwayatMutasiModel
                ->where('satker_id', $satker['id'])
                ->where('tanggal_mulai <=', $periode_akhir)
                ->groupStart()
                ->where('tanggal_selesai IS NULL')
                ->orWhere('tanggal_selesai >', $periode_akhir)
                ->groupEnd()
                ->findColumn('pegawai_id');
            if ($pegawai_ids && count($pegawai_ids) > 0) {
                $pegawai_ids = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif')->findColumn('id');
            }
            $total_pegawai = $pegawai_ids && count($pegawai_ids) > 0 ? count($pegawai_ids) : 0;
            $ada_data_kedisiplinan = false;
            if ($total_pegawai > 0) {
                $cek_data = $kedisiplinanModel->whereIn('pegawai_id', $pegawai_ids)
                    ->where('bulan', $filter_bulan)
                    ->where('tahun', $filter_tahun)
                    ->countAllResults();
                $ada_data_kedisiplinan = $cek_data > 0;
            }
            if ($total_pegawai == 0 || !$ada_data_kedisiplinan) {
                $rekap_data[] = [
                    'satker_id' => $satker['id'],
                    'nama_satker' => $satker['nama'],
                    'alamat' => $satker['alamat'] ?? '',
                    'total_pegawai' => $total_pegawai,
                    't' => null,
                    'kti' => null,
                    'tam' => null,
                    'tk' => null,
                    'pa' => null,
                    'tms' => null,
                    'tap' => null,
                    'tmk' => null,
                    'bentuk_pembinaan' => null,
                    'keterangan' => null,
                    'belum_ada_data' => true
                ];
            } else {
                $kedisiplinan = $kedisiplinanModel
                    ->select(
                        "COUNT(DISTINCT CASE WHEN terlambat > 0 THEN pegawai_id END) as total_t,
                        COUNT(DISTINCT CASE WHEN keluar_tidak_izin > 0 THEN pegawai_id END) as total_kti,
                        COUNT(DISTINCT CASE WHEN tidak_absen_masuk > 0 THEN pegawai_id END) as total_tam,
                        COUNT(DISTINCT CASE WHEN tidak_masuk_tanpa_ket > 0 THEN pegawai_id END) as total_tk,
                        COUNT(DISTINCT CASE WHEN pulang_awal > 0 THEN pegawai_id END) as total_pa,
                        COUNT(DISTINCT CASE WHEN tidak_masuk_sakit > 0 THEN pegawai_id END) as total_tms,
                        COUNT(DISTINCT CASE WHEN tidak_absen_pulang > 0 THEN pegawai_id END) as total_tap,
                        COUNT(DISTINCT CASE WHEN tidak_masuk_kerja > 0 THEN pegawai_id END) as total_tmk,
                        GROUP_CONCAT(DISTINCT bentuk_pembinaan) as bentuk_pembinaan,
                        GROUP_CONCAT(DISTINCT keterangan) as keterangan"
                    )
                    ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
                    ->whereIn("pegawai.id", $pegawai_ids)
                    ->where("kedisiplinan.bulan", $filter_bulan)
                    ->where("kedisiplinan.tahun", $filter_tahun)
                    ->first();
                $rekap_data[] = [
                    'satker_id' => $satker['id'],
                    'nama_satker' => $satker['nama'],
                    'alamat' => $satker['alamat'] ?? '',
                    'total_pegawai' => $total_pegawai,
                    't' => $kedisiplinan['total_t'] ?? 0,
                    'kti' => $kedisiplinan['total_kti'] ?? 0,
                    'tam' => $kedisiplinan['total_tam'] ?? 0,
                    'tk' => $kedisiplinan['total_tk'] ?? 0,
                    'pa' => $kedisiplinan['total_pa'] ?? 0,
                    'tms' => $kedisiplinan['total_tms'] ?? 0,
                    'tap' => $kedisiplinan['total_tap'] ?? 0,
                    'tmk' => $kedisiplinan['total_tmk'] ?? 0,
                    'bentuk_pembinaan' => $kedisiplinan['bentuk_pembinaan'] ?? '',
                    'keterangan' => $kedisiplinan['keterangan'] ?? '',
                    'belum_ada_data' => false
                ];
            }
        }
        $data = [
            'rekap_data' => $rekap_data,
            'filter_bulan' => $filter_bulan,
            'filter_tahun' => $filter_tahun,
            'tahun_tersedia' => $kedisiplinanModel->distinct()->select('tahun')->orderBy('tahun', 'DESC')->findAll(),
            'notif_count' => $notifikasiModel->where('user_id', $session->get('user_id'))->where('is_read', 0)->countAllResults(),
            'active' => 'admin/rekap_user_satker'
        ];
        return view('admin/RekapPegawaiSatker', $data);
    }

    public function exportUserSatkerPdf()
    {
        // Load helper
        helper('app');
        
        // Pastikan tidak ada output sebelum header
        while (ob_get_level() > 0) { ob_end_clean(); }
        
        $kedisiplinanModel = new RekapPegawaiSatkerModel();
        $pegawaiModel = new PegawaiModel();
        $tandaTanganModel = new TandaTanganModel();
        $satkerModel = new SatkerModel();
        $tandaTanganGambarModel = new TandaTanganGambarModel();
        $session = session();
        $filter_bulan = $this->request->getVar("bulan");
        $filter_tahun = $this->request->getVar("tahun");
        // Fallback ke session jika WebView mengirim tanpa body (GET/POST kosong)
        if (empty($filter_bulan)) {
            $filter_bulan = $session->get('last_rekap_user_satker_bulan');
        }
        if (empty($filter_tahun)) {
            $filter_tahun = $session->get('last_rekap_user_satker_tahun');
        }
        if (empty($filter_bulan)) {
            $filter_bulan = date("m");
        }
        if (empty($filter_tahun)) {
            $filter_tahun = date("Y");
        }
        
        // Validasi parameter wajib
        if (empty($filter_bulan) || empty($filter_tahun)) {
            $session->setFlashdata('msg', 'Parameter bulan dan tahun wajib diisi!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back();
        }
        
        $pdf = new PDF();
        $pdf->AddPage('L');
        $pdf->drawHeaderLaporanDisiplin($filter_bulan, $filter_tahun, 'SEMUA SATKER');
        $pdf->drawComplexHeader('user_satker');
        $pdf->current_bulan = $filter_bulan;
        $pdf->current_tahun = $filter_tahun;
        $pdf->current_satker_name = 'SEMUA SATKER';
        $satker_list = $satkerModel->orderBy('nama', 'ASC')->findAll();
        $row_number = 1;
        $rows_per_page = 6;
        $current_row = 0;
        foreach ($satker_list as $satker) {
            $riwayatMutasiModel = new \App\Models\RiwayatMutasiModel();
            $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
            $pegawai_ids = $riwayatMutasiModel
                ->where('satker_id', $satker['id'])
                ->where('tanggal_mulai <=', $periode_akhir)
                ->groupStart()
                ->where('tanggal_selesai IS NULL')
                ->orWhere('tanggal_selesai >', $periode_akhir)
                ->groupEnd()
                ->findColumn('pegawai_id');
            if ($pegawai_ids && count($pegawai_ids) > 0) {
                $pegawai_ids = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif')->findColumn('id');
            }
            $total_pegawai = $pegawai_ids && count($pegawai_ids) > 0 ? count($pegawai_ids) : 0;
            $ada_data_kedisiplinan = false;
            if ($total_pegawai > 0) {
                $cek_data = $kedisiplinanModel->whereIn('pegawai_id', $pegawai_ids)
                    ->where('bulan', $filter_bulan)
                    ->where('tahun', $filter_tahun)
                    ->countAllResults();
                $ada_data_kedisiplinan = $cek_data > 0;
            }
            if ($current_row >= $rows_per_page) {
                $pdf->drawKeterangan();
                $tanda_tangan_gambar_aktif = $tandaTanganGambarModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
                $tanda_tangan_manual_aktif = $tandaTanganModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
                $tanda_tangan = $tanda_tangan_gambar_aktif ? $tanda_tangan_gambar_aktif : $tanda_tangan_manual_aktif;
                $is_tanda_tangan_gambar = $tanda_tangan_gambar_aktif ? true : false;
                $pdf->drawTandaTangan($tanda_tangan, $is_tanda_tangan_gambar);
                $pdf->AddPage('L');
                $pdf->drawHeaderLaporanDisiplin($filter_bulan, $filter_tahun, 'SEMUA SATKER');
                $pdf->drawComplexHeader('user_satker');
                $current_row = 0;
            }
            if ($total_pegawai == 0 || !$ada_data_kedisiplinan) {
                $pdf->drawTableRowUserSatkerKosong(
                    $row_number++, $satker["nama"], $satker["alamat"], $total_pegawai,
                    'Belum ada data kedisiplinan untuk satker ini.', 10
                );
                $current_row++;
                continue;
            }
            $kedisiplinan = $kedisiplinanModel
                ->select(
                    "COUNT(DISTINCT CASE WHEN terlambat > 0 THEN pegawai_id END) as total_t,
                    COUNT(DISTINCT CASE WHEN keluar_tidak_izin > 0 THEN pegawai_id END) as total_kti,
                    COUNT(DISTINCT CASE WHEN tidak_absen_masuk > 0 THEN pegawai_id END) as total_tam,
                    COUNT(DISTINCT CASE WHEN tidak_masuk_tanpa_ket > 0 THEN pegawai_id END) as total_tk,
                    COUNT(DISTINCT CASE WHEN pulang_awal > 0 THEN pegawai_id END) as total_pa,
                    COUNT(DISTINCT CASE WHEN tidak_masuk_sakit > 0 THEN pegawai_id END) as total_tms,
                    COUNT(DISTINCT CASE WHEN tidak_absen_pulang > 0 THEN pegawai_id END) as total_tap,
                    COUNT(DISTINCT CASE WHEN tidak_masuk_kerja > 0 THEN pegawai_id END) as total_tmk,
                    GROUP_CONCAT(DISTINCT bentuk_pembinaan) as bentuk_pembinaan,
                    GROUP_CONCAT(DISTINCT keterangan) as keterangan"
                )
                ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
                ->whereIn("pegawai.id", $pegawai_ids)
                ->where("kedisiplinan.bulan", $filter_bulan)
                ->where("kedisiplinan.tahun", $filter_tahun)
                ->first();
            $pdf->drawTableRow([
                $row_number++,
                $satker["nama"],
                $satker["alamat"],
                $total_pegawai,
                $kedisiplinan["total_t"] ?? '-',
                $kedisiplinan["total_kti"] ?? '-',
                $kedisiplinan["total_tam"] ?? '-',
                $kedisiplinan["total_tk"] ?? '-',
                $kedisiplinan["total_pa"] ?? '-',
                $kedisiplinan["total_tms"] ?? '-',
                $kedisiplinan["total_tap"] ?? '-',
                $kedisiplinan["total_tmk"] ?? '-',
                $kedisiplinan["bentuk_pembinaan"] ?? '-',
                $kedisiplinan["keterangan"] ?? '-',
            ], 10, 'user_satker', true);
            $current_row++;
        }
        $pdf->drawKeterangan();
        $tanda_tangan_gambar_aktif = $tandaTanganGambarModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
        $tanda_tangan_manual_aktif = $tandaTanganModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
        $tanda_tangan = $tanda_tangan_gambar_aktif ? $tanda_tangan_gambar_aktif : $tanda_tangan_manual_aktif;
        $is_tanda_tangan_gambar = $tanda_tangan_gambar_aktif ? true : false;
        $pdf->drawTandaTangan($tanda_tangan, $is_tanda_tangan_gambar);
        $filename = "Rekap_User_Satker_{$filter_bulan}_{$filter_tahun}.pdf";
        $pdfContent = $pdf->Output($filename, 'S');
        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($pdfContent);
    }

    public function exportUserSatkerWord()
    {
        // Load helper
        helper('app');
        
        error_reporting(0);
        ini_set('display_errors', 0);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $kedisiplinanModel = new RekapPegawaiSatkerModel();
        $pegawaiModel = new PegawaiModel();
        $tandaTanganModel = new TandaTanganModel();
        $satkerModel = new SatkerModel();
        $tandaTanganGambarModel = new TandaTanganGambarModel();
        $session = session();
        $filter_bulan = $this->request->getVar("bulan");
        $filter_tahun = $this->request->getVar("tahun");
        // Fallback ke session jika WebView mengirim tanpa body (GET/POST kosong)
        if (empty($filter_bulan)) {
            $filter_bulan = $session->get('last_rekap_user_satker_bulan');
        }
        if (empty($filter_tahun)) {
            $filter_tahun = $session->get('last_rekap_user_satker_tahun');
        }
        if (empty($filter_bulan)) {
            $filter_bulan = date("m");
        }
        if (empty($filter_tahun)) {
            $filter_tahun = date("Y");
        }
        
        // Validasi parameter wajib
        if (empty($filter_bulan) || empty($filter_tahun)) {
            $session->setFlashdata('msg', 'Parameter bulan dan tahun wajib diisi!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back();
        }
        
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(7);
        $satker_list = $satkerModel->orderBy('nama', 'ASC')->findAll();
        $rows_per_page = 5;
        $row_count = 0;
        $no = 1;
        $section = null;
        $table = null;
        foreach ($satker_list as $idx => $satker) {
            if ($row_count % $rows_per_page == 0) {
                if ($section !== null) {
                    $section->addTextBreak(0);
                    $section->addText('KETERANGAN:', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman']);
                    $tableKeterangan = $section->addTable(['cellMargin' => 30]);
                    $tableKeterangan->addRow();
                    $tableKeterangan->addCell(2500)->addText('t = TERLAMBAT', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addCell(4000)->addText('kti = KELUAR KANTOR TIDAK IZIN ATASAN', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addRow();
                    $tableKeterangan->addCell(2500)->addText('tam = TIDAK ABSEN MASUK', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addCell(4000)->addText('tk = TIDAK MASUK TANPA KETERANGAN', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addRow();
                    $tableKeterangan->addCell(2500)->addText('pa = PULANG AWAL', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addCell(4000)->addText('tms = TIDAK MASUK KARENA SAKIT TANPA MENGAJUKAN CUTI SAKIT', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addRow();
                    $tableKeterangan->addCell(2500)->addText('tap = TIDAK ABSEN PULANG', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tableKeterangan->addCell(4000)->addText('tmk = TIDAK MASUK KERJA', ['size' => 7, 'name' => 'Times New Roman'], ['lineHeight' => 1.0]);
                    $tanda_tangan_gambar_aktif = $tandaTanganGambarModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
                    $tanda_tangan_manual_aktif = $tandaTanganModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
                    $tanda_tangan = $tanda_tangan_gambar_aktif ? $tanda_tangan_gambar_aktif : $tanda_tangan_manual_aktif;
                    $is_tanda_tangan_gambar = $tanda_tangan_gambar_aktif ? true : false;
                    if ($tanda_tangan) {
                        $section->addTextBreak(0);
                        if ($is_tanda_tangan_gambar) {
                            $section->addText($tanda_tangan['tempat'] . ', ' . tanggalIndo($tanda_tangan['tanggal']), [
                                'size' => 7,
                                'name' => 'Times New Roman'
                            ], [
                                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                                'lineHeight' => 1.0
                            ]);
                            $img_path = FCPATH . 'writable/uploads/ttd/' . $tanda_tangan['file_path'];
                            if (file_exists($img_path)) {
                                list($img_w, $img_h) = getimagesize($img_path);
                                $max_w = 180;
                                $max_h = 90;
                                $ratio = min($max_w / $img_w, $max_h / $img_h, 1);
                                $w = $img_w * $ratio;
                                $h = $img_h * $ratio;
                                $section->addImage($img_path, [
                                    'width' => $w,
                                    'height' => $h,
                                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT
                                ]);
                            } else {
                                $section->addText('[Gambar tidak ditemukan]', [
                                    'size' => 7,
                                    'name' => 'Times New Roman'
                                ], [
                                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                                    'lineHeight' => 1.0
                                ]);
                            }
                        } else {
                            $section->addText($tanda_tangan['lokasi'] . ', ' . tanggalIndo($tanda_tangan['tanggal']), [
                                'size' => 7,
                                'name' => 'Times New Roman'
                            ], [
                                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                                'lineHeight' => 1.0
                            ]);
                            $section->addText($tanda_tangan['nama_jabatan'], [
                                'size' => 7,
                                'name' => 'Times New Roman'
                            ], [
                                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                                'lineHeight' => 1.0
                            ]);
                            $section->addTextBreak(2);
                            $section->addText($tanda_tangan['nama_penandatangan'], [
                                'bold' => true,
                                'size' => 7,
                                'name' => 'Times New Roman'
                            ], [
                                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                                'lineHeight' => 1.0
                            ]);
                            $section->addText('NIP. ' . $tanda_tangan['nip_penandatangan'], [
                                'size' => 7,
                                'name' => 'Times New Roman'
                            ], [
                                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                                'lineHeight' => 1.0
                            ]);
                        }
                    }
                }
                $section = $phpWord->addSection([
                    'orientation' => 'landscape',
                    'marginLeft' => 850,
                    'marginRight' => 850,
                    'marginTop' => 850,
                    'marginBottom' => 100
                ]);

                // Header Judul - dipastikan di tengah
                $section->addText('REKAPITULASI LAPORAN DISIPLIN HAKIM', [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                    'spaceAfter' => 0
                ]);
                $section->addText('YANG TIDAK MEMATUHI KETENTUAN JAM KERJA SESUAI PERMA NO 7 TAHUN 2016', [
                    'bold' => true,
                    'size' => 10,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                    'spaceAfter' => 200
                ]);

                // Tabel SATKER dan BULAN tanpa border dan sejajar dengan tabel utama
                $headerInfoTable = $section->addTable([
                    'borderSize' => 0,
                    'borderColor' => 'FFFFFF',
                    'cellMargin' => 30,
                    'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED
                ]);
                $headerInfoTable->addRow();
                
                // SATKER (kolom kiri) - gabungan lebar kolom NO sampai TOTAL PEGAWAI di tabel utama
                $satkerCellWidth = 500 + 2500 + 2500 + 1500; // Total: 7000
                $headerInfoTable->addCell($satkerCellWidth, [
                    'valign' => 'top',
                    'borderSize' => 0,
                    'borderColor' => 'FFFFFF'
                ])->addText('SATKER: SEMUA SATKER', [
                    'bold' => true,
                    'size' => 10,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
                    'spaceAfter' => 0,
                    'lineHeight' => 1.0
                ]);

                // BULAN (kolom kanan) - gabungan lebar kolom URAIAN sampai KETERANGAN
                $bulanCellWidth = 4800 + 2000 + 1500; // Total: 8300
                $bulanText = 'BULAN: ' . strtoupper(getBulanIndo($filter_bulan) . ' ' . $filter_tahun);
                $headerInfoTable->addCell($bulanCellWidth, [
                    'valign' => 'top',
                    'borderSize' => 0,
                    'borderColor' => 'FFFFFF'
                ])->addText($bulanText, [
                    'bold' => true,
                    'size' => 10,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                    'spaceAfter' => 0,
                    'lineHeight' => 1.0
                ]);

                // Jarak antara header info dan tabel utama
                $section->addTextBreak(0);
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 30]);
                $table->addRow();
                $table->addCell(500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('NO', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(2500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('SATKER', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(2500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('ALAMAT', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(1500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('TOTAL PEGAWAI', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(4800, ['gridSpan' => 8, 'valign' => 'center'])->addText('JUMLAH PEGAWAI DENGAN AKUMULASI TIDAK DIPATUHKAN', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(2000, ['vMerge' => 'restart', 'valign' => 'center'])->addText('BENTUK PEMBINAAN', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(1500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('KETERANGAN', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addRow();
                $table->addCell(500, ['vMerge' => 'continue']);
                $table->addCell(2500, ['vMerge' => 'continue']);
                $table->addCell(2500, ['vMerge' => 'continue']);
                $table->addCell(1500, ['vMerge' => 'continue']);
                $table->addCell(600, ['valign' => 'center'])->addText('t', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('kti', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('tam', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('tk', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('pa', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('tms', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('tap', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText('tmk', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(2000, ['vMerge' => 'continue']);
                $table->addCell(1500, ['vMerge' => 'continue']);
            }
            $riwayatMutasiModel = new \App\Models\RiwayatMutasiModel();
            $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
            $pegawai_ids = $riwayatMutasiModel
                ->where('satker_id', $satker['id'])
                ->where('tanggal_mulai <=', $periode_akhir)
                ->groupStart()
                ->where('tanggal_selesai IS NULL')
                ->orWhere('tanggal_selesai >', $periode_akhir)
                ->groupEnd()
                ->findColumn('pegawai_id');
            if ($pegawai_ids && count($pegawai_ids) > 0) {
                $pegawai_ids = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif')->findColumn('id');
            }
            $total_pegawai = $pegawai_ids && count($pegawai_ids) > 0 ? count($pegawai_ids) : 0;
            $ada_data_kedisiplinan = false;
            if ($total_pegawai > 0) {
                $cek_data = $kedisiplinanModel->whereIn('pegawai_id', $pegawai_ids)
                    ->where('bulan', $filter_bulan)
                    ->where('tahun', $filter_tahun)
                    ->countAllResults();
                $ada_data_kedisiplinan = $cek_data > 0;
            }
            $table->addRow();
            $table->addCell(500, ['valign' => 'center'])->addText($no++, ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(2500, ['valign' => 'center'])->addText($satker["nama"], ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(2500, ['valign' => 'center'])->addText($satker["alamat"], ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(1500, ['valign' => 'center'])->addText($total_pegawai, ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            if ($total_pegawai == 0 || !$ada_data_kedisiplinan) {
                $table->addCell(8700, ['gridSpan' => 10, 'valign' => 'center'])->addText('Belum ada data kedisiplinan untuk satker ini.', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            } else {
                $kedisiplinan_data = $kedisiplinanModel->select('
                    SUM(CASE WHEN terlambat > 0 THEN 1 ELSE 0 END) as total_t,
                    SUM(CASE WHEN keluar_tidak_izin > 0 THEN 1 ELSE 0 END) as total_kti,
                    SUM(CASE WHEN tidak_absen_masuk > 0 THEN 1 ELSE 0 END) as total_tam,
                    SUM(CASE WHEN tidak_masuk_tanpa_ket > 0 THEN 1 ELSE 0 END) as total_tk,
                    SUM(CASE WHEN pulang_awal > 0 THEN 1 ELSE 0 END) as total_pa,
                    SUM(CASE WHEN tidak_masuk_sakit > 0 THEN 1 ELSE 0 END) as total_tms,
                    SUM(CASE WHEN tidak_absen_pulang > 0 THEN 1 ELSE 0 END) as total_tap,
                    SUM(CASE WHEN tidak_masuk_kerja > 0 THEN 1 ELSE 0 END) as total_tmk,
                    GROUP_CONCAT(DISTINCT bentuk_pembinaan SEPARATOR ", ") as bentuk_pembinaan,
                    GROUP_CONCAT(DISTINCT keterangan SEPARATOR ", ") as keterangan
                ')
                    ->whereIn('pegawai_id', $pegawai_ids)
                    ->where('bulan', $filter_bulan)
                    ->where('tahun', $filter_tahun)
                    ->first();
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_t"] > 0 ? $kedisiplinan_data["total_t"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_kti"] > 0 ? $kedisiplinan_data["total_kti"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_tam"] > 0 ? $kedisiplinan_data["total_tam"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_tk"] > 0 ? $kedisiplinan_data["total_tk"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_pa"] > 0 ? $kedisiplinan_data["total_pa"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_tms"] > 0 ? $kedisiplinan_data["total_tms"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_tap"] > 0 ? $kedisiplinan_data["total_tap"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(600, ['valign' => 'center'])->addText(($kedisiplinan_data["total_tmk"] > 0 ? $kedisiplinan_data["total_tmk"] : '-'), ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(2000, ['valign' => 'center'])->addText(!empty($kedisiplinan_data["bentuk_pembinaan"]) ? $kedisiplinan_data["bentuk_pembinaan"] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
                $table->addCell(1500, ['valign' => 'center'])->addText(!empty($kedisiplinan_data["keterangan"]) ? $kedisiplinan_data["keterangan"] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            }
            $row_count++;
        }
        if ($section !== null) {
            // KETERANGAN di bawah - dengan jarak antar item
            $section->addTextBreak(1);
            $section->addText('KETERANGAN:', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.0]);
            $section->addText('t = TERLAMBAT', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('tam = TIDAK ABSEN MASUK', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('pa = PULANG AWAL', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('tap = TIDAK ABSEN PULANG', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('kti = KELUAR KANTOR TIDAK IZIN ATASAN', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('tk = TIDAK MASUK TANPA KETERANGAN', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('tms = TIDAK MASUK KARENA SAKIT TANPA MENGAJUKAN CUTI SAKIT', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $section->addText('tmk = TIDAK MASUK KERJA', ['size' => 6, 'name' => 'Times New Roman'], ['spaceAfter' => 0, 'lineHeight' => 1.2]);
            $tanda_tangan_gambar_aktif = $tandaTanganGambarModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
            $tanda_tangan_manual_aktif = $tandaTanganModel->where('user_id', session()->get('user_id'))->where('is_aktif', 1)->first();
            $tanda_tangan = $tanda_tangan_gambar_aktif ? $tanda_tangan_gambar_aktif : $tanda_tangan_manual_aktif;
            $is_tanda_tangan_gambar = $tanda_tangan_gambar_aktif ? true : false;
            if ($tanda_tangan) {
                $section->addTextBreak(0);
                if ($is_tanda_tangan_gambar) {
                    $section->addText($tanda_tangan['tempat'] . ', ' . tanggalIndo($tanda_tangan['tanggal']), [
                        'size' => 7,
                        'name' => 'Times New Roman'
                    ], [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                        'lineHeight' => 1.0
                    ]);
                    $img_path = FCPATH . 'writable/uploads/ttd/' . $tanda_tangan['file_path'];
                    if (file_exists($img_path)) {
                        list($img_w, $img_h) = getimagesize($img_path);
                        $max_w = 180;
                        $max_h = 90;
                        $ratio = min($max_w / $img_w, $max_h / $img_h, 1);
                        $w = $img_w * $ratio;
                        $h = $img_h * $ratio;
                        $section->addImage($img_path, [
                            'width' => $w,
                            'height' => $h,
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT
                        ]);
                    } else {
                        $section->addText('[Gambar tidak ditemukan]', [
                            'size' => 7,
                            'name' => 'Times New Roman'
                        ], [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                            'lineHeight' => 1.0
                        ]);
                    }
                } else {
                    $section->addText($tanda_tangan['lokasi'] . ', ' . tanggalIndo($tanda_tangan['tanggal']), [
                        'size' => 7,
                        'name' => 'Times New Roman'
                    ], [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                        'lineHeight' => 1.0
                    ]);
                    $section->addText($tanda_tangan['nama_jabatan'], [
                        'size' => 7,
                        'name' => 'Times New Roman'
                    ], [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                        'lineHeight' => 1.0
                    ]);
                    $section->addTextBreak(2);
                    $section->addText($tanda_tangan['nama_penandatangan'], [
                        'bold' => true,
                        'size' => 7,
                        'name' => 'Times New Roman'
                    ], [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                        'lineHeight' => 1.0
                    ]);
                    $section->addText('NIP. ' . $tanda_tangan['nip_penandatangan'], [
                        'size' => 7,
                        'name' => 'Times New Roman'
                    ], [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                        'lineHeight' => 1.0
                    ]);
                }
            }
        }
        $filename = 'Rekap_User_Satker_' . getBulanIndo($filter_bulan) . '_' . $filter_tahun . '.docx';
        
        // Set headers untuk WebView Android compatibility
        $this->response->setContentType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
        $this->response->setHeader('Pragma', 'public');
        $this->response->sendHeaders();
        
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit;
    }
} 
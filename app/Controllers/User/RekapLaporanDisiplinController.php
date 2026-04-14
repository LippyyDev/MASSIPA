<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\RekapLaporanDisiplinModel;
use App\Models\TandaTanganModel;
use App\Models\TandaTanganGambarModel;
use App\Models\UserModel;
use App\Models\SatkerModel;
use App\Models\RiwayatMutasiModel;
use App\Libraries\PDF;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class RekapLaporanDisiplinController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'session', 'app']);
    }

    public function rekapLaporan()
    {
        $kedisiplinanModel = new RekapLaporanDisiplinModel();
        $tandaTanganModel = new TandaTanganModel();
        $session = session();

        $user_id = $session->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($user_id);
        $satkerModel = new SatkerModel();
        $satker = null;
        if (!empty($user['satker_id'])) {
            $satker = $satkerModel->find($user['satker_id']);
        }
        $nama_satker = $satker ? $satker['nama'] : '-';
        $data["filter_satker_name"] = $nama_satker;

        // Ambil tahun unik dari database untuk filter
        $tahun_tersedia_raw = $kedisiplinanModel->distinct()->select("tahun")->where("created_by", $session->get("user_id"))->orderBy("tahun", "DESC")->findAll();
        $data["tahun_tersedia"] = array_column($tahun_tersedia_raw, "tahun");
        if (empty($data["tahun_tersedia"])) {
            $data["tahun_tersedia"][] = date("Y");
        }

        // Ambil filter dari request
        $filter_bulan = $this->request->getVar("bulan") ?? date("n");
        $filter_tahun = $this->request->getVar("tahun") ?? (empty($data["tahun_tersedia"]) ? date("Y") : $data["tahun_tersedia"][0]);
        // Simpan terakhir dipakai agar export di WebView tetap mengambil filter yang sama
        $session->set('last_rekap_laporan_bulan', $filter_bulan);
        $session->set('last_rekap_laporan_tahun', $filter_tahun);
        // Ambil pegawai yang mutasinya aktif di satker user pada bulan/tahun rekap
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $user['satker_id'])
            ->where('tanggal_mulai <=', $periode_akhir)
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', $periode_akhir)
            ->groupEnd()
            ->findColumn('pegawai_id');
        if (!$pegawai_ids || count($pegawai_ids) === 0) {
            $data["kedisiplinan_data"] = [];
        } else {
            $kedisiplinan_query = $kedisiplinanModel->select("kedisiplinan.*, pegawai.nama, pegawai.nip, pegawai.pangkat, pegawai.golongan, pegawai.jabatan, satker.nama as nama_satker")
                ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
                ->join("riwayat_mutasi", "riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.satker_id = {$user['satker_id']} AND riwayat_mutasi.tanggal_mulai <= '$periode_akhir' AND (riwayat_mutasi.tanggal_selesai IS NULL OR riwayat_mutasi.tanggal_selesai > '$periode_akhir')", "left")
                ->join("satker", "satker.id = riwayat_mutasi.satker_id", "left")
                ->whereIn('kedisiplinan.pegawai_id', $pegawai_ids)
                ->where("kedisiplinan.created_by", $session->get("user_id"))
                ->where("kedisiplinan.bulan", $filter_bulan)
                ->where("kedisiplinan.tahun", $filter_tahun);
            $raw_data = $kedisiplinan_query->findAll();
            $data["kedisiplinan_data"] = $this->sortByJabatanPriority($raw_data);
        }
        $data["filter_bulan"] = $filter_bulan;
        $data["filter_tahun"] = $filter_tahun;
        $data["filter_satker_name"] = $nama_satker;
        $data["tanda_tangan"] = $tandaTanganModel->where("user_id", $session->get("user_id"))->orderBy("id", "DESC")->first();
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/RekapLaporanDisiplin", $data);
    }

    public function exportPdf()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        $kedisiplinanModel = new RekapLaporanDisiplinModel();
        $tandaTanganModel = new TandaTanganModel();
        $tandaTanganGambarModel = new TandaTanganGambarModel();
        $session = session();
        $user_id = $session->get('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($user_id);
        $satkerModel = new SatkerModel();
        $satker = null;
        if (!empty($user['satker_id'])) {
            $satker = $satkerModel->find($user['satker_id']);
        }
        $nama_satker = $satker ? $satker['nama'] : '-';
        $data["filter_satker_name"] = $nama_satker;
        $filter_bulan = $this->request->getVar("bulan");
        $filter_tahun = $this->request->getVar("tahun");
        if (empty($filter_bulan)) {
            $filter_bulan = $session->get('last_rekap_laporan_bulan') ?? date("n");
        }
        if (empty($filter_tahun)) {
            $filter_tahun = $session->get('last_rekap_laporan_tahun') ?? date("Y");
        }
        $filter_bulan = (int) $filter_bulan;
        // Perbarui nilai terakhir agar konsisten antar permintaan
        $session->set('last_rekap_laporan_bulan', $filter_bulan);
        $session->set('last_rekap_laporan_tahun', $filter_tahun);
        
        // Validasi parameter wajib
        if (empty($filter_bulan) || empty($filter_tahun)) {
            $session->setFlashdata('msg', 'Parameter bulan dan tahun wajib diisi!');
            $session->setFlashdata('msg_type', 'danger');
            return redirect()->back();
        }
        
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $user['satker_id'])
            ->where('tanggal_mulai <=', $periode_akhir)
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('tanggal_selesai >', $periode_akhir)
            ->groupEnd()
            ->findColumn('pegawai_id');
        $selected_raw = $this->request->getVar('selected');
        // Pastikan selected adalah array
        if (empty($selected_raw)) {
            $selected = null;
        } elseif (is_array($selected_raw)) {
            $selected = $selected_raw;
        } else {
            // Jika bukan array, buat array dengan satu elemen
            $selected = [$selected_raw];
        }
        if ($selected && is_array($selected)) {
            $kedisiplinan_query = $kedisiplinanModel->select("kedisiplinan.*, pegawai.nama, pegawai.nip, pegawai.pangkat, pegawai.golongan, pegawai.jabatan, satker.nama as nama_satker")
                ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
                ->join("riwayat_mutasi", "riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.satker_id = {$user['satker_id']} AND riwayat_mutasi.tanggal_mulai <= '$periode_akhir' AND (riwayat_mutasi.tanggal_selesai IS NULL OR riwayat_mutasi.tanggal_selesai > '$periode_akhir')", "left")
                ->join("satker", "satker.id = riwayat_mutasi.satker_id", "left")
                ->whereIn('kedisiplinan.id', $selected)
                ->where("kedisiplinan.created_by", $session->get("user_id"))
                ->where("kedisiplinan.bulan", $filter_bulan)
                ->where("kedisiplinan.tahun", $filter_tahun);
        } else {
            $kedisiplinan_query = $kedisiplinanModel->select("kedisiplinan.*, pegawai.nama, pegawai.nip, pegawai.pangkat, pegawai.golongan, pegawai.jabatan, satker.nama as nama_satker")
                ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
                ->join("riwayat_mutasi", "riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.satker_id = {$user['satker_id']} AND riwayat_mutasi.tanggal_mulai <= '$periode_akhir' AND (riwayat_mutasi.tanggal_selesai IS NULL OR riwayat_mutasi.tanggal_selesai > '$periode_akhir')", "left")
                ->join("satker", "satker.id = riwayat_mutasi.satker_id", "left")
                ->whereIn('kedisiplinan.pegawai_id', $pegawai_ids)
                ->where("kedisiplinan.created_by", $session->get("user_id"))
                ->where("kedisiplinan.bulan", $filter_bulan)
                ->where("kedisiplinan.tahun", $filter_tahun);
        }
        $raw_data = $kedisiplinan_query->findAll();
        $data["kedisiplinan_data"] = $this->sortByJabatanPriority($raw_data);
        $data["filter_bulan"] = $filter_bulan;
        $data["filter_tahun"] = $filter_tahun;
        $tanda_tangan_gambar_aktif = $tandaTanganGambarModel->where("user_id", $session->get("user_id"))->where("is_aktif", 1)->first();
        $tanda_tangan_manual_aktif = $tandaTanganModel->where("user_id", $session->get("user_id"))->where("is_aktif", 1)->first();
        $tanda_tangan = $tanda_tangan_gambar_aktif ? $tanda_tangan_gambar_aktif : $tanda_tangan_manual_aktif;
        $is_tanda_tangan_gambar = $tanda_tangan_gambar_aktif ? true : false;
        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->SetCreator('Sistem Manajemen Disiplin Hakim');
        $pdf->SetTitle('Rekap Laporan Disiplin Hakim');
        $pdf->setHeaderFont(array('helvetica', '', 10));
        $pdf->setFooterFont(array('helvetica', '', 8));
        $pdf->SetDefaultMonospacedFont('helvetica');
        $pdf->SetMargins(10, 10, 20);
        $pdf->SetAutoPageBreak(true, 15);
        $max_rows_per_page = 6;
        $total_rows = count($data["kedisiplinan_data"]);
        $pages = ceil($total_rows / $max_rows_per_page);
        for ($page = 0; $page < $pages; $page++) {
            $pdf->AddPage();
            $pdf->drawHeaderLaporanDisiplin($filter_bulan, $filter_tahun, $data["filter_satker_name"]);
            $pdf->SetFont('helvetica', '', 7);
            $pdf->drawComplexHeader('default');
            $start_row = $page * $max_rows_per_page;
            $end_row = min(($page + 1) * $max_rows_per_page, $total_rows);
            $row_number = $start_row + 1;
            for ($i = $start_row; $i < $end_row; $i++) {
                $row = $data["kedisiplinan_data"][$i];
                $nama_nip = $row['nama'] . "\nNIP. " . $row['nip'];
                $pangkat_gol = $row['pangkat'] . "\n" . $row['golongan'];
                $row_data = [
                    $row_number++, $nama_nip, $pangkat_gol, $row['jabatan'], $row['nama_satker'],
                    $row['terlambat'] > 0 ? $row['terlambat'] : '-',
                    $row['keluar_tidak_izin'] > 0 ? $row['keluar_tidak_izin'] : '-',
                    $row['tidak_absen_masuk'] > 0 ? $row['tidak_absen_masuk'] : '-',
                    $row['tidak_masuk_tanpa_ket'] > 0 ? $row['tidak_masuk_tanpa_ket'] : '-',
                    $row['pulang_awal'] > 0 ? $row['pulang_awal'] : '-',
                    $row['tidak_masuk_sakit'] > 0 ? $row['tidak_masuk_sakit'] : '-',
                    $row['tidak_absen_pulang'] > 0 ? $row['tidak_absen_pulang'] : '-',
                    $row['tidak_masuk_kerja'] > 0 ? $row['tidak_masuk_kerja'] : '-',
                    !empty($row['bentuk_pembinaan']) ? $row['bentuk_pembinaan'] : '-',
                    !empty($row['keterangan']) ? $row['keterangan'] : '-'
                ];
                $pdf->drawTableRow($row_data, 10);
            }
            $pdf->drawKeterangan();
            if ($tanda_tangan) {
                $pdf->drawTandaTangan($tanda_tangan, $is_tanda_tangan_gambar);
            }
        }
        $filename = 'Rekap_Laporan_Disiplin_Hakim_' . getBulanIndo($filter_bulan) . '_' . $filter_tahun . '.pdf';
        
        // Untuk WebView Android compatibility, gunakan Output('S') lalu kirim via response object
        $pdfContent = $pdf->Output($filename, 'S');
        
        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
            ->setHeader('Pragma', 'public')
            ->setBody($pdfContent);
    }

    public function exportWord()
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    $kedisiplinanModel = new RekapLaporanDisiplinModel();
    $tandaTanganModel = new TandaTanganModel();
    $tandaTanganGambarModel = new TandaTanganGambarModel();
    $session = session();
    $user_id = $session->get('user_id');
    $userModel = new UserModel();
    $user = $userModel->find($user_id);
    $satkerModel = new SatkerModel();
    $satker = null;
    if (!empty($user['satker_id'])) {
        $satker = $satkerModel->find($user['satker_id']);
    }
    $nama_satker = $satker ? $satker['nama'] : '-';
    $data["filter_satker_name"] = $nama_satker;
    $filter_bulan = $this->request->getVar("bulan");
    $filter_tahun = $this->request->getVar("tahun");
    if (empty($filter_bulan)) {
        $filter_bulan = $session->get('last_rekap_laporan_bulan') ?? date("n");
    }
    if (empty($filter_tahun)) {
        $filter_tahun = $session->get('last_rekap_laporan_tahun') ?? date("Y");
    }
    $filter_bulan = (int) $filter_bulan;
    $session->set('last_rekap_laporan_bulan', $filter_bulan);
    $session->set('last_rekap_laporan_tahun', $filter_tahun);
    
    // Validasi parameter wajib
    if (empty($filter_bulan) || empty($filter_tahun)) {
        $session->setFlashdata('msg', 'Parameter bulan dan tahun wajib diisi!');
        $session->setFlashdata('msg_type', 'danger');
        return redirect()->back();
    }
    
    $riwayatMutasiModel = new RiwayatMutasiModel();
    $periode_akhir = date('Y-m-t', strtotime($filter_tahun . '-' . str_pad($filter_bulan, 2, '0', STR_PAD_LEFT) . '-01'));
    $pegawai_ids = $riwayatMutasiModel
        ->where('satker_id', $user['satker_id'])
        ->where('tanggal_mulai <=', $periode_akhir)
        ->groupStart()
        ->where('tanggal_selesai IS NULL')
        ->orWhere('tanggal_selesai >', $periode_akhir)
        ->groupEnd()
        ->findColumn('pegawai_id');
    $selected_raw = $this->request->getVar('selected');
    // Pastikan selected adalah array
    if (empty($selected_raw)) {
        $selected = null;
    } elseif (is_array($selected_raw)) {
        $selected = $selected_raw;
    } else {
        // Jika bukan array, buat array dengan satu elemen
        $selected = [$selected_raw];
    }
    if ($selected && is_array($selected)) {
        $kedisiplinan_query = $kedisiplinanModel->select("kedisiplinan.*, pegawai.nama, pegawai.nip, pegawai.pangkat, pegawai.golongan, pegawai.jabatan, satker.nama as nama_satker")
            ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
            ->join("riwayat_mutasi", "riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.satker_id = {$user['satker_id']} AND riwayat_mutasi.tanggal_mulai <= '$periode_akhir' AND (riwayat_mutasi.tanggal_selesai IS NULL OR riwayat_mutasi.tanggal_selesai > '$periode_akhir')", "left")
            ->join("satker", "satker.id = riwayat_mutasi.satker_id", "left")
            ->whereIn('kedisiplinan.id', $selected)
            ->where("kedisiplinan.created_by", $session->get("user_id"))
            ->where("kedisiplinan.bulan", $filter_bulan)
            ->where("kedisiplinan.tahun", $filter_tahun);
    } else {
        $kedisiplinan_query = $kedisiplinanModel->select("kedisiplinan.*, pegawai.nama, pegawai.nip, pegawai.pangkat, pegawai.golongan, pegawai.jabatan, satker.nama as nama_satker")
            ->join("pegawai", "kedisiplinan.pegawai_id = pegawai.id")
            ->join("riwayat_mutasi", "riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.satker_id = {$user['satker_id']} AND riwayat_mutasi.tanggal_mulai <= '$periode_akhir' AND (riwayat_mutasi.tanggal_selesai IS NULL OR riwayat_mutasi.tanggal_selesai > '$periode_akhir')", "left")
            ->join("satker", "satker.id = riwayat_mutasi.satker_id", "left")
            ->whereIn('kedisiplinan.pegawai_id', $pegawai_ids)
            ->where("kedisiplinan.created_by", $session->get("user_id"))
            ->where("kedisiplinan.bulan", $filter_bulan)
            ->where("kedisiplinan.tahun", $filter_tahun);
    }
    $raw_data = $kedisiplinan_query->findAll();
    $kedisiplinan_data = $this->sortByJabatanPriority($raw_data);
    $data["kedisiplinan_data"] = $kedisiplinan_data;
    $data["filter_bulan"] = $filter_bulan;
    $data["filter_tahun"] = $filter_tahun;
    $tanda_tangan_gambar_aktif = $tandaTanganGambarModel->where("user_id", $session->get("user_id"))->where("is_aktif", 1)->first();
    $tanda_tangan_manual_aktif = $tandaTanganModel->where("user_id", $session->get("user_id"))->where("is_aktif", 1)->first();
    $data["tanda_tangan"] = $tanda_tangan_gambar_aktif ? $tanda_tangan_gambar_aktif : $tanda_tangan_manual_aktif;
    $data["is_tanda_tangan_gambar"] = $tanda_tangan_gambar_aktif ? true : false;
    $phpWord = new PhpWord();
    $phpWord->setDefaultFontName('Times New Roman');
    $phpWord->setDefaultFontSize(7);
    $rows_per_page = 5;
    $row_count = 0;
    $no = 1;
    $section = null;
    $table = null;
    foreach ($kedisiplinan_data as $idx => $item) {
        if ($row_count % $rows_per_page == 0) {
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
                if ($data["tanda_tangan"]) {
                    if ($data["is_tanda_tangan_gambar"]) {
                        $section->addText($data["tanda_tangan"]['tempat'] . ', ' . tanggalIndo($data["tanda_tangan"]['tanggal']), [
                            'size' => 7,
                            'name' => 'Times New Roman'
                        ], [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                            'lineHeight' => 1.0
                        ]);
                        $img_path = WRITEPATH . 'uploads/ttd/' . $data["tanda_tangan"]['file_path'];
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
                        $section->addText($data["tanda_tangan"]['lokasi'] . ', ' . tanggalIndo($data["tanda_tangan"]['tanggal']), [
                            'size' => 7,
                            'name' => 'Times New Roman'
                        ], [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                            'lineHeight' => 1.0
                        ]);
                        $section->addText($data["tanda_tangan"]['nama_jabatan'], [
                            'size' => 7,
                            'name' => 'Times New Roman'
                        ], [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                            'lineHeight' => 1.0
                        ]);
                        $section->addTextBreak(2);
                        $section->addText($data["tanda_tangan"]['nama_penandatangan'], [
                            'bold' => true,
                            'size' => 7,
                            'name' => 'Times New Roman'
                        ], [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                            'lineHeight' => 1.0
                        ]);
                        $section->addText('NIP. ' . $data["tanda_tangan"]['nip_penandatangan'], [
                            'size' => 7,
                            'name' => 'Times New Roman'
                        ], [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                            'lineHeight' => 1.0
                        ]);
                    }
                }
            }
            
            // Buat section baru
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
                'name' => 'Arial'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'spaceAfter' => 0
            ]);
            $section->addText('YANG TIDAK MEMATUHI KETENTUAN JAM KERJA SESUAI PERMA NO 7 TAHUN 2016', [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial'
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
            
            // SATKER (kolom kiri) - gabungan lebar kolom NO sampai SATKER di tabel utama
            $satkerCellWidth = 500 + 2000 + 1500 + 1500 + 2000; // Total: 7500
            $headerInfoTable->addCell($satkerCellWidth, [
                'valign' => 'top',
                'borderSize' => 0,
                'borderColor' => 'FFFFFF'
            ])->addText('SATKER: ' . strtoupper($data["filter_satker_name"]), [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
                'spaceAfter' => 0
            ]);

            // BULAN (kolom kanan) - gabungan lebar kolom URAIAN sampai KET
            $bulanCellWidth = 4800 + 1500 + 1000; // Total: 7300
            $bulanText = 'BULAN: ' . strtoupper(getBulanIndo($filter_bulan) . ' ' . $filter_tahun);
            $headerInfoTable->addCell($bulanCellWidth, [
                'valign' => 'top',
                'borderSize' => 0,
                'borderColor' => 'FFFFFF'
            ])->addText($bulanText, [
                'bold' => true,
                'size' => 10,
                'name' => 'Arial'
            ], [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                'spaceAfter' => 0
            ]);

            // Jarak antara header info dan tabel utama
            $section->addTextBreak(0);
            
            // Tabel utama
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 30]);
            $table->addRow();
            $table->addCell(500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('NO', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(2000, ['vMerge' => 'restart', 'valign' => 'center'])->addText('NAMA/NIP', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(1500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('PANGKAT/GOL. RUANG', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(1500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('JABATAN', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(2000, ['vMerge' => 'restart', 'valign' => 'center'])->addText('SATKER', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(4800, ['gridSpan' => 8, 'valign' => 'center'])->addText('URAIAN AKUMULASI TIDAK DIPATUHKAN', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(1500, ['vMerge' => 'restart', 'valign' => 'center'])->addText('BENTUK PEMBINAAN', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(1000, ['vMerge' => 'restart', 'valign' => 'center'])->addText('KET', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addRow();
            $table->addCell(500, ['vMerge' => 'continue']);
            $table->addCell(2000, ['vMerge' => 'continue']);
            $table->addCell(1500, ['vMerge' => 'continue']);
            $table->addCell(1500, ['vMerge' => 'continue']);
            $table->addCell(2000, ['vMerge' => 'continue']);
            $table->addCell(600, ['valign' => 'center'])->addText('t', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('kti', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('tam', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('tk', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('pa', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('tms', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('tap', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(600, ['valign' => 'center'])->addText('tmk', ['bold' => true, 'size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
            $table->addCell(1500, ['vMerge' => 'continue']);
            $table->addCell(1000, ['vMerge' => 'continue']);
        }
        
        // Tambah baris data
        $table->addRow();
        $table->addCell(500, ['valign' => 'center'])->addText($no++, ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(2000, ['valign' => 'center'])->addText($item['nama'] . "\nNIP. " . $item['nip'], ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(1500, ['valign' => 'center'])->addText($item['pangkat'] . ' ' . $item['golongan'], ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(1500, ['valign' => 'center'])->addText($item['jabatan'], ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(2000, ['valign' => 'center'])->addText($item['nama_satker'], ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['terlambat'] > 0 ? $item['terlambat'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['keluar_tidak_izin'] > 0 ? $item['keluar_tidak_izin'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['tidak_absen_masuk'] > 0 ? $item['tidak_absen_masuk'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['tidak_masuk_tanpa_ket'] > 0 ? $item['tidak_masuk_tanpa_ket'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['pulang_awal'] > 0 ? $item['pulang_awal'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['tidak_masuk_sakit'] > 0 ? $item['tidak_masuk_sakit'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['tidak_absen_pulang'] > 0 ? $item['tidak_absen_pulang'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(600, ['valign' => 'center'])->addText($item['tidak_masuk_kerja'] > 0 ? $item['tidak_masuk_kerja'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(1500, ['valign' => 'center'])->addText(!empty($item['bentuk_pembinaan']) ? $item['bentuk_pembinaan'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $table->addCell(1000, ['valign' => 'center'])->addText(!empty($item['keterangan']) ? $item['keterangan'] : '-', ['size' => 7, 'name' => 'Times New Roman'], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'lineHeight' => 1.0]);
        $row_count++;
    }
    
    // Footer terakhir
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
        
        if ($data["tanda_tangan"]) {
            if ($data["is_tanda_tangan_gambar"]) {
                $section->addText($data["tanda_tangan"]['tempat'] . ', ' . tanggalIndo($data["tanda_tangan"]['tanggal']), [
                    'size' => 7,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                    'lineHeight' => 1.0
                ]);
                $img_path = WRITEPATH . 'uploads/ttd/' . $data["tanda_tangan"]['file_path'];
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
                $section->addText($data["tanda_tangan"]['lokasi'] . ', ' . tanggalIndo($data["tanda_tangan"]['tanggal']), [
                    'size' => 7,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                    'lineHeight' => 1.0
                ]);
                $section->addText($data["tanda_tangan"]['nama_jabatan'], [
                    'size' => 7,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                    'lineHeight' => 1.0
                ]);
                $section->addTextBreak(2);
                $section->addText($data["tanda_tangan"]['nama_penandatangan'], [
                    'bold' => true,
                    'size' => 7,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                    'lineHeight' => 1.0
                ]);
                $section->addText('NIP. ' . $data["tanda_tangan"]['nip_penandatangan'], [
                    'size' => 7,
                    'name' => 'Times New Roman'
                ], [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT,
                    'lineHeight' => 1.0
                ]);
            }
        }
    }
    
    $filename = 'Laporan_Disiplin_Hakim_' . getBulanIndo($filter_bulan) . '_' . $filter_tahun . '.docx';
    
    // Set headers untuk WebView Android compatibility
    $this->response->setContentType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    $this->response->setHeader('Cache-Control', 'private, max-age=0, must-revalidate');
    $this->response->setHeader('Pragma', 'public');
    $this->response->sendHeaders();
    
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save('php://output');
    exit;
}

    private function getNotifCount()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }

    /**
     * Mengurutkan data berdasarkan prioritas jabatan
     * 1. Ketua (No 1)
     * 2. Wakil Ketua (No 2)
     * 3. Hakim (berdasarkan golongan tertinggi)
     * 4. Jabatan lainnya (bebas urutan)
     */
    private function sortByJabatanPriority($data)
    {
        // Definisikan prioritas jabatan
        $jabatan_priority = [
            'KETUA' => 1,
            'WAKIL KETUA' => 2,
            'HAKIM' => 3,
            'HAKIM TINGGI' => 3,
            'PANITERA' => 4,
            'WAKIL PANITERA' => 5,
            'SEKRETARIS' => 6,
            'WAKIL SEKRETARIS' => 7,
            'PANITERA MUDA' => 8,
            'PANITERA MUDA HUKUM' => 9,
            'PANITERA MUDA PERDATA' => 10,
            'PANITERA MUDA PIDANA' => 11,
            'PANITERA PENGGANTI' => 12,
            'JURU SITA' => 13,
            'JURU SITA PENGGANTI' => 14,
            'STAFF' => 15,
            'PEGAWAI' => 16
        ];

        // Definisikan urutan golongan (dari tertinggi ke terendah)
        $golongan_order = [
            'IV/e' => 1, 'IV/d' => 2, 'IV/c' => 3, 'IV/b' => 4, 'IV/a' => 5,
            'III/d' => 6, 'III/c' => 7, 'III/b' => 8, 'III/a' => 9,
            'II/d' => 10, 'II/c' => 11, 'II/b' => 12, 'II/a' => 13,
            'I/d' => 14, 'I/c' => 15, 'I/b' => 16, 'I/a' => 17
        ];

        usort($data, function($a, $b) use ($jabatan_priority, $golongan_order) {
            $jabatan_a = strtoupper(trim($a['jabatan']));
            $jabatan_b = strtoupper(trim($b['jabatan']));
            $golongan_a = strtoupper(trim($a['golongan']));
            $golongan_b = strtoupper(trim($b['golongan']));

            // Cek apakah jabatan ada dalam prioritas
            $priority_a = $jabatan_priority[$jabatan_a] ?? 999;
            $priority_b = $jabatan_priority[$jabatan_b] ?? 999;

            // Jika prioritas berbeda, urutkan berdasarkan prioritas
            if ($priority_a !== $priority_b) {
                return $priority_a - $priority_b;
            }

            // Jika sama-sama Hakim (prioritas 3), urutkan berdasarkan golongan
            if ($priority_a === 3 && $priority_b === 3) {
                $gol_priority_a = $golongan_order[$golongan_a] ?? 999;
                $gol_priority_b = $golongan_order[$golongan_b] ?? 999;
                
                if ($gol_priority_a !== $gol_priority_b) {
                    return $gol_priority_a - $gol_priority_b;
                }
            }

            // Jika masih sama, urutkan berdasarkan nama
            return strcmp($a['nama'], $b['nama']);
        });

        return $data;
    }
} 
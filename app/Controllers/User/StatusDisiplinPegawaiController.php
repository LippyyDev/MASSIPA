<?php
namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\StatusDisiplinPegawaiModel;
use App\Models\PegawaiModel;
use App\Models\SatkerModel;
use App\Models\RiwayatMutasiModel;

class StatusDisiplinPegawaiController extends BaseController
{
    public function rekapBulanan()
    {
        $kedisiplinanModel = new StatusDisiplinPegawaiModel();
        $pegawaiModel = new PegawaiModel();
        $satkerModel = new SatkerModel();
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $session = session();
        $user = (new \App\Models\UserModel())->find($session->get("user_id"));
        $satker_id = $user['satker_id'];
        $tahun_tersedia_raw = $kedisiplinanModel->distinct()->select("tahun")->where("created_by", $session->get("user_id"))->orderBy("tahun", "DESC")->findAll();
        $daftar_tahun = array_column($tahun_tersedia_raw, "tahun");
        if (empty($daftar_tahun)) {
            $daftar_tahun[] = date("Y");
        }
        $tahun_dipilih = $this->request->getGet("tahun") ?? (empty($daftar_tahun) ? date("Y") : $daftar_tahun[0]);
        $nama_bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->groupStart()
            ->where('YEAR(tanggal_mulai) <=', $tahun_dipilih)
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('YEAR(tanggal_selesai) >=', $tahun_dipilih)
            ->groupEnd()
            ->groupEnd()
            ->findColumn('pegawai_id');
        $pegawai_list = [];
        if ($pegawai_ids) {
            $pegawai_list = $pegawaiModel->whereIn('id', $pegawai_ids)->where('status', 'aktif')->orderBy('nama', 'ASC')->findAll();
        }
        $kedisiplinan_data = $kedisiplinanModel->getKedisiplinanByTahun($session->get("user_id"), $tahun_dipilih);
        $rekap_bulanan = [];
        foreach ($pegawai_list as $pegawai) {
            $riwayat = $riwayatMutasiModel->where('pegawai_id', $pegawai['id'])->orderBy('tanggal_mulai', 'ASC')->findAll();
            $mutasi_bulan = null;
            $mutasi_tahun = null;
            $satker_baru_id = null;
            $satker_baru_nama = null;
            $bulan_terakhir_satker_lama = null;
            $keterangan_mutasi = null;
            $keterangan_sebelum_mutasi = null;
            $bulan_sebelum_mutasi = null;
            $satker_lama_nama = null;
            foreach ($riwayat as $idx => $mutasi) {
                if ($idx > 0) {
                    $tahun_mutasi = (int) date('Y', strtotime($mutasi['tanggal_mulai']));
                    $bulan_mutasi = (int) date('n', strtotime($mutasi['tanggal_mulai']));
                    if ($tahun_mutasi == $tahun_dipilih && $riwayat[$idx - 1]['satker_id'] == $satker_id) {
                        $mutasi_bulan = $bulan_mutasi;
                        $mutasi_tahun = $tahun_mutasi;
                        $satker_baru_id = $mutasi['satker_id'];
                        $satker_baru = $satkerModel->find($satker_baru_id);
                        $satker_baru_nama = $satker_baru ? $satker_baru['nama'] : '-';
                        $bulan_terakhir_satker_lama = $mutasi_bulan - 1;
                        $keterangan_mutasi = "Pegawai telah bermutasi ke Satker: <b>" . htmlspecialchars($satker_baru_nama) . "</b> mulai bulan " . $nama_bulan[$mutasi_bulan] . " " . $mutasi_tahun;
                        break;
                    }
                    if ($tahun_mutasi == $tahun_dipilih && $mutasi['satker_id'] == $satker_id) {
                        $mutasi_bulan = $bulan_mutasi;
                        $mutasi_tahun = $tahun_mutasi;
                        $satker_lama_id = $riwayat[$idx - 1]['satker_id'];
                        $satker_lama = $satkerModel->find($satker_lama_id);
                        $satker_lama_nama = $satker_lama ? $satker_lama['nama'] : '-';
                        $bulan_sebelum_mutasi = $mutasi_bulan - 1;
                        $keterangan_sebelum_mutasi = "Data sebelum mutasi ada di Satker:<br><b>" . htmlspecialchars($satker_lama_nama) . "</b>";
                        break;
                    }
                }
            }
            $kedisiplinan_per_bulan = [];
            foreach ($nama_bulan as $bulan_num => $nama) {
                $kedisiplinan_per_bulan[$bulan_num] = null;
                foreach ($kedisiplinan_data as $kedisiplinan) {
                    if ($kedisiplinan["pegawai_id"] == $pegawai["id"] && $kedisiplinan["bulan"] == $bulan_num) {
                        $kedisiplinan_per_bulan[$bulan_num] = $kedisiplinan;
                        break;
                    }
                }
            }
            $rekap_row = [
                "pegawai" => $pegawai,
                "kedisiplinan" => $kedisiplinan_per_bulan,
                "bulan_terakhir_satker_lama" => $bulan_terakhir_satker_lama,
                "keterangan_mutasi" => $keterangan_mutasi,
                "mutasi_bulan" => $mutasi_bulan,
                "mutasi_tahun" => $mutasi_tahun,
                "satker_baru_nama" => $satker_baru_nama,
                "keterangan_sebelum_mutasi" => $keterangan_sebelum_mutasi,
                "bulan_sebelum_mutasi" => $bulan_sebelum_mutasi
            ];
            $rekap_bulanan[] = $rekap_row;
        }
        $data = [
            "daftar_tahun" => $daftar_tahun,
            "tahun_dipilih" => $tahun_dipilih,
            "nama_bulan" => $nama_bulan,
            "rekap_bulanan" => $rekap_bulanan,
            "satkerModel" => $satkerModel
        ];
        $data['notif_count'] = $this->getNotifCount();
        echo view("user/StatusDisiplinPegawai", $data);
    }

    public function getRekapBulananAjax()
    {
        $tahun  = $this->request->getPost('tahun');
        $start  = intval($this->request->getPost('start')  ?? 0);
        $length = intval($this->request->getPost('length') ?? 10);
        $search = $this->request->getPost('search')['value'] ?? '';
        $draw   = intval($this->request->getPost('draw')   ?? 1);
        $kedisiplinanModel = new StatusDisiplinPegawaiModel();
        $pegawaiModel = new PegawaiModel();
        $satkerModel = new SatkerModel();
        $riwayatMutasiModel = new RiwayatMutasiModel();
        $user = (new \App\Models\UserModel())->find(session()->get("user_id"));
        if (!$user) {
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Session habis, silakan login ulang.'
            ]);
        }
        $satker_id = $user['satker_id'];
        $nama_bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $pegawai_ids = $riwayatMutasiModel
            ->where('satker_id', $satker_id)
            ->groupStart()
            ->where('YEAR(tanggal_mulai) <=', $tahun)
            ->groupStart()
            ->where('tanggal_selesai IS NULL')
            ->orWhere('YEAR(tanggal_selesai) >=', $tahun)
            ->groupEnd()
            ->groupEnd()
            ->findColumn('pegawai_id');
        $pegawai_list = [];
        if ($pegawai_ids) {
            $pegawai_list = $pegawaiModel->whereIn('id', $pegawai_ids)->orderBy('nama', 'ASC')->findAll();
        }
        $kedisiplinan_data = $kedisiplinanModel->getKedisiplinanByTahunAll($tahun);
        if ($search) {
            $pegawai_list = array_filter($pegawai_list, function ($row) use ($search) {
                return stripos($row['nama'], $search) !== false || stripos($row['nip'], $search) !== false;
            });
        }
        $total = count($pegawai_list);
        $filtered = $total;
        $pegawai_page = array_slice(array_values($pegawai_list), $start, $length);
        $data = [];
        foreach ($pegawai_page as $pegawai) {
            $riwayat = $riwayatMutasiModel->where('pegawai_id', $pegawai['id'])->orderBy('tanggal_mulai', 'ASC')->findAll();
            $mutasi_bulan = null;
            $mutasi_tahun = null;
            $satker_baru_id = null;
            $satker_baru_nama = null;
            $bulan_terakhir_satker_lama = null;
            $keterangan_mutasi = null;
            $keterangan_sebelum_mutasi = null;
            $bulan_sebelum_mutasi = null;
            $satker_lama_nama = null;
            foreach ($riwayat as $idx => $mutasi) {
                if ($idx > 0) {
                    $tahun_mutasi = (int) date('Y', strtotime($mutasi['tanggal_mulai']));
                    $bulan_mutasi = (int) date('n', strtotime($mutasi['tanggal_mulai']));
                    if ($tahun_mutasi == $tahun && $riwayat[$idx - 1]['satker_id'] == $satker_id) {
                        $mutasi_bulan = $bulan_mutasi;
                        $mutasi_tahun = $tahun_mutasi;
                        $satker_baru_id = $mutasi['satker_id'];
                        $satker_baru = $satkerModel->find($satker_baru_id);
                        $satker_baru_nama = $satker_baru ? $satker_baru['nama'] : '-';
                        $bulan_terakhir_satker_lama = $mutasi_bulan - 1;
                        $keterangan_mutasi = "Pegawai telah bermutasi ke Satker: <b>" . htmlspecialchars($satker_baru_nama) . "</b> mulai bulan " . $nama_bulan[$mutasi_bulan] . " " . $mutasi_tahun;
                        break;
                    }
                    if ($tahun_mutasi == $tahun && $mutasi['satker_id'] == $satker_id) {
                        $mutasi_bulan = $bulan_mutasi;
                        $mutasi_tahun = $tahun_mutasi;
                        $satker_lama_id = $riwayat[$idx - 1]['satker_id'];
                        $satker_lama = $satkerModel->find($satker_lama_id);
                        $satker_lama_nama = $satker_lama ? $satker_lama['nama'] : '-';
                        $bulan_sebelum_mutasi = $mutasi_bulan - 1;
                        $keterangan_sebelum_mutasi = "Data sebelum mutasi ada di Satker:<br><b>" . htmlspecialchars($satker_lama_nama) . "</b>";
                        break;
                    }
                }
            }
            $kedisiplinan_per_bulan = [];
            foreach ($nama_bulan as $bulan_num => $nama) {
                $kedisiplinan_per_bulan[$bulan_num] = null;
                foreach ($kedisiplinan_data as $kedisiplinan) {
                    if ($kedisiplinan["pegawai_id"] == $pegawai["id"] && $kedisiplinan["bulan"] == $bulan_num) {
                        $kedisiplinan_per_bulan[$bulan_num] = $kedisiplinan;
                        break;
                    }
                }
            }
            $data[] = [
                'nama_nip' => $pegawai['nama'] . '<br><small class="text-muted">NIP: ' . $pegawai['nip'] . '</small>',
                'pangkat_golongan' => $pegawai['pangkat'] . '<br><small class="text-muted">' . $pegawai['golongan'] . '</small>',
                'jabatan' => $pegawai['jabatan'],
                'kedisiplinan_per_bulan' => $kedisiplinan_per_bulan,
                'mutasi_bulan' => $mutasi_bulan,
                'bulan_terakhir_satker_lama' => $bulan_terakhir_satker_lama,
                'keterangan_mutasi' => $keterangan_mutasi,
                'keterangan_sebelum_mutasi' => $keterangan_sebelum_mutasi,
                'bulan_sebelum_mutasi' => $bulan_sebelum_mutasi
            ];
        }
        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    private function getNotifCount()
    {
        $notifikasiModel = new \App\Models\NotifikasiModel();
        return $notifikasiModel->where('user_id', session()->get('user_id'))->where('is_read', 0)->countAllResults();
    }
} 
<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\KelolaPegawaiModel;
use App\Models\SatkerModel;
use App\Models\RiwayatMutasiModel;

class KelolaPegawaiController extends BaseController
{
    protected $pegawaiModel;
    protected $satkerModel;
    protected $riwayatMutasiModel;

    public function __construct()
    {
        $this->pegawaiModel = new KelolaPegawaiModel();
        $this->satkerModel = new SatkerModel();
        $this->riwayatMutasiModel = new RiwayatMutasiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function inputPegawai()
    {
        $pegawai = $this->pegawaiModel->getAllWithSatker();
        $list_satker = $this->satkerModel->orderBy('nama', 'ASC')->findAll();
        return view('admin/KelolaPegawai', [
            'pegawai' => $pegawai,
            'list_satker' => $list_satker
        ]);
    }

    public function getPegawaiAjax()
    {
        try {
            $start = intval($this->request->getGet('start') ?? 0);
            $length = intval($this->request->getGet('length') ?? 10);
            $search = $this->request->getGet('search[value]') ?? '';
            $satker_filter = $this->request->getGet('satker') ?? '';
            $golongan_filter = $this->request->getGet('golongan') ?? '';
            $jabatan_filter = $this->request->getGet('jabatan') ?? '';
            $total = $this->pegawaiModel->countAll();
            list($data, $recordsFiltered) = $this->pegawaiModel->getFilteredPegawai($search, $satker_filter, $golongan_filter, $jabatan_filter, $start, $length);
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'draw' => intval($this->request->getGet('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function addPegawai()
    {
        $satker_id = $this->request->getPost('satker_id');
        $tanggal_mulai = $this->request->getPost('tanggal_mulai');
        $rules = [
            'nip' => [
                'rules' => 'required|is_unique[pegawai.nip]',
                'errors' => [
                    'required' => 'NIP wajib diisi!',
                    'is_unique' => 'NIP sudah digunakan oleh pegawai lain!'
                ]
            ],
            'nama' => 'required',
            'satker_id' => 'required'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            return redirect()->back()->with('msg', $firstError)->with('msg_type', 'danger')->withInput();
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nip' => $this->request->getPost('nip'),
            'pangkat' => $this->request->getPost('pangkat'),
            'golongan' => $this->request->getPost('golongan'),
            'jabatan' => $this->request->getPost('jabatan'),
            'status' => 'aktif',
        ];

        $this->pegawaiModel->insert($data);
        $pegawai_id = $this->pegawaiModel->getInsertID();
        $this->riwayatMutasiModel->insert([
            'pegawai_id' => $pegawai_id,
            'satker_id' => $satker_id,
            'tanggal_mulai' => $tanggal_mulai,
        ]);
        return redirect()->to(base_url('admin/input_pegawai'))->with('msg', 'Pegawai berhasil ditambah!')->with('msg_type', 'success');
    }

    public function editPegawai($id)
    {
        $pegawai = $this->pegawaiModel->find($id);
        return view('admin/edit_pegawai', ['pegawai' => $pegawai]);
    }

    public function updatePegawai($id = null)
    {
        $id = $id ?? $this->request->getPost('id');
        $rules = [
            'nip' => [
                'rules' => "required|is_unique[pegawai.nip,id,{$id}]",
                'errors' => [
                    'required' => 'NIP wajib diisi!',
                    'is_unique' => 'NIP sudah digunakan oleh pegawai lain!'
                ]
            ],
            'nama' => 'required'
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            $firstError = reset($errors);
            return redirect()->back()->with('msg', $firstError)->with('msg_type', 'danger')->withInput();
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'nip' => $this->request->getPost('nip'),
            'pangkat' => $this->request->getPost('pangkat'),
            'golongan' => $this->request->getPost('golongan'),
            'jabatan' => $this->request->getPost('jabatan'),
        ];
        $this->pegawaiModel->update($id, $data);
        $tanggal_mulai = $this->request->getPost('tanggal_mulai');
        if ($tanggal_mulai) {
            $this->riwayatMutasiModel->where('pegawai_id', $id)->where('tanggal_selesai', null)->set(['tanggal_mulai' => $tanggal_mulai])->update();
        }
        return redirect()->to(base_url('admin/input_pegawai'))->with('msg', 'Data pegawai berhasil diupdate!')->with('msg_type', 'success');
    }

    public function deletePegawai($id = null)
    {
        $pegawai = $this->pegawaiModel->find($id);
        if ($pegawai) {
            $this->pegawaiModel->delete($id);
            session()->setFlashdata('msg', 'Pegawai berhasil dihapus');
            session()->setFlashdata('msg_type', 'success');
        } else {
            session()->setFlashdata('msg', 'Pegawai tidak ditemukan.');
            session()->setFlashdata('msg_type', 'danger');
        }
        return redirect()->to(base_url('admin/input_pegawai'));
    }

    public function toggleStatusPegawai($id)
    {
        $pegawai = $this->pegawaiModel->find($id);
        $statusBaru = $pegawai['status'] === 'aktif' ? 'pensiun' : 'aktif';
        $this->pegawaiModel->update($id, ['status' => $statusBaru]);
        return redirect()->to(base_url('admin/input_pegawai'))->with('msg', 'Status pegawai diubah!')->with('msg_type', 'success');
    }

    public function importPegawai()
    {
        $satkerModel = $this->satkerModel;
        $riwayatMutasiModel = $this->riwayatMutasiModel;
        $session = session();
        
        if ($this->request->getFile('file_csv')->isValid()) {
            $file = $this->request->getFile('file_csv');
            $file->move(WRITEPATH . 'uploads', $file->getRandomName());
            $filePath = WRITEPATH . 'uploads/' . $file->getName();
            $lines = file($filePath);
            unlink($filePath);
            
            $headerRow = null;
            $headerIndex = null;
            
            // Deteksi header
            foreach ($lines as $i => $line) {
                $cols = array_map('trim', str_getcsv($line, ','));
                if (in_array('N A M A', $cols) && in_array('GOL', $cols) && in_array('SATKER', $cols)) {
                    $headerRow = $cols;
                    $headerIndex = $i;
                    break;
                }
            }
            
            if (!$headerRow) {
                $session->setFlashdata('msg', 'Header CSV tidak ditemukan atau format tidak sesuai. Pastikan ada kolom N A M A, GOL, dan SATKER.');
                $session->setFlashdata('msg_type', 'danger');
                return redirect()->to(base_url('admin/input_pegawai'));
            }
            
            $map = [
                'nama' => array_search('N A M A', $headerRow),
                'nip' => array_search('NIP/NRP', $headerRow),
                'jabatan' => array_search('JABATAN', $headerRow),
                'golongan' => array_search('GOL', $headerRow),
                'satker' => array_search('SATKER', $headerRow),
                'tmt_mutasi' => array_search('TMT MUTASI', $headerRow),
            ];
            
            foreach ($map as $k => $v) {
                if ($v === false && $k !== 'tmt_mutasi') {
                    $session->setFlashdata('msg', 'Kolom ' . $k . ' tidak ditemukan di header CSV.');
                    $session->setFlashdata('msg_type', 'danger');
                    return redirect()->to(base_url('admin/input_pegawai'));
                }
            }
            
            $golonganPangkat = [
                'I/a' => 'Juru Muda',
                'I/b' => 'Juru Muda Tk.I',
                'I/c' => 'Juru',
                'I/d' => 'Juru Tk.I',
                'II/a' => 'Pengatur Muda',
                'II/b' => 'Pengatur Muda Tk.I',
                'II/c' => 'Pengatur',
                'II/d' => 'Pengatur Tk.I',
                'III/a' => 'Penata Muda',
                'III/b' => 'Penata Muda Tingkat I',
                'III/c' => 'Penata',
                'III/d' => 'Penata Tingkat I',
                'IV/a' => 'Pembina',
                'IV/b' => 'Pembina Tingkat I',
                'IV/c' => 'Pembina Utama Muda',
                'IV/d' => 'Pembina Utama Madya',
                'IV/e' => 'Pembina Utama',
            ];
            
            $successCount = 0;
            $updateCount = 0;
            $newCount = 0;
            $mutasiCount = 0;
            $skipCount = 0;
            $errorMessages = [];
            $processedData = [];
            
            // Proses setiap baris data
            for ($i = $headerIndex + 1; $i < count($lines); $i++) {
                $cols = array_map('trim', str_getcsv($lines[$i], ','));
                
                if (count($cols) < max(array_filter($map)) + 1 || 
                    empty($cols[$map['nama']]) || 
                    empty($cols[$map['nip']]) || 
                    empty($cols[$map['jabatan']]) || 
                    empty($cols[$map['golongan']]) || 
                    empty($cols[$map['satker']])) {
                    continue;
                }
                
                $nama = $cols[$map['nama']];
                // Tangani NIP yang disimpan Excel sebagai scientific notation (misal: 1.96E+17)
                $nipRaw = trim($cols[$map['nip']]);
                if (stripos($nipRaw, 'E') !== false || stripos($nipRaw, 'e') !== false) {
                    $nip = sprintf('%.0f', (float)$nipRaw);
                } else {
                    $nip = preg_replace('/\D/', '', $nipRaw);
                }
                $jabatan = $cols[$map['jabatan']];
                $golongan = $cols[$map['golongan']];
                $satker_nama = $cols[$map['satker']];
                
                // Proses tanggal TMT mutasi
                $tmt_mutasi = '';
                if (isset($map['tmt_mutasi']) && $map['tmt_mutasi'] !== false && 
                    isset($cols[$map['tmt_mutasi']]) && !empty($cols[$map['tmt_mutasi']])) {
                    $tmt_mutasi = date('Y-m-d', strtotime(str_replace('/', '-', $cols[$map['tmt_mutasi']])));
                } else {
                    $tmt_mutasi = date('Y-m-d');
                }
                
                $pangkat = isset($golonganPangkat[$golongan]) ? $golonganPangkat[$golongan] : '';
                
                // Cek apakah pegawai sudah ada
                $existingPegawai = $this->pegawaiModel->where('nip', $nip)->first();
                
                // Cek satker
                $satker = $satkerModel->where('nama', $satker_nama)->first();
                if (!$satker) {
                    $errorMessages[] = 'Baris ' . ($i + 1) . ': Satker "' . $satker_nama . '" tidak ditemukan di database.';
                    continue;
                }
                
                if ($existingPegawai) {
                    // CEK PERUBAHAN DATA
                    $changes = [];
                    $oldData = [
                        'golongan' => $existingPegawai['golongan'],
                        'jabatan' => $existingPegawai['jabatan'],
                        'satker_id' => null
                    ];
                    
                    // Cek satker current
                    $currentMutasi = $riwayatMutasiModel->where('pegawai_id', $existingPegawai['id'])
                                                       ->where('tanggal_selesai', null)
                                                       ->first();
                    if ($currentMutasi) {
                        $oldData['satker_id'] = $currentMutasi['satker_id'];
                        $oldData['satker'] = $satkerModel->find($currentMutasi['satker_id'])['nama'];
                    } else {
                        $oldData['satker'] = 'Tidak ada data';
                    }
                    
                    // Deteksi perubahan
                    if ($oldData['golongan'] != $golongan) $changes[] = 'golongan';
                    if ($oldData['jabatan'] != $jabatan) $changes[] = 'jabatan';
                    if ($oldData['satker_id'] != $satker['id']) $changes[] = 'satker';
                    
                    if (empty($changes)) {
                        // SKIP - tidak ada perubahan
                        $skipCount++;
                        $processedData[] = [
                            'nama' => $nama,
                            'nip' => $nip,
                            'action' => 'skip',
                            'old_data' => $oldData,
                            'new_data' => [
                                'golongan' => $golongan,
                                'jabatan' => $jabatan,
                                'satker' => $satker_nama
                            ]
                        ];
                    } else {
                        // UPDATE - ada perubahan
                        $updateData = [
                            'nama' => $nama,
                            'pangkat' => $pangkat,
                            'golongan' => $golongan,
                            'jabatan' => $jabatan,
                        ];
                        
                        $this->pegawaiModel->update($existingPegawai['id'], $updateData);
                        
                        if (in_array('satker', $changes)) {
                            // Ada perubahan satker - buat mutasi baru
                            $riwayatMutasiModel->where('pegawai_id', $existingPegawai['id'])
                                              ->where('tanggal_selesai', null)
                                              ->set(['tanggal_selesai' => $tmt_mutasi])
                                              ->update();
                            
                            $riwayatMutasiModel->insert([
                                'pegawai_id' => $existingPegawai['id'],
                                'satker_id' => $satker['id'],
                                'tanggal_mulai' => $tmt_mutasi,
                            ]);
                            
                            // Cek apakah ada perubahan gol/jabatan juga
                            $golJabatanChanges = array_intersect($changes, ['golongan', 'jabatan']);
                            $hasGolJabatanChanges = !empty($golJabatanChanges);
                            
                            if ($hasGolJabatanChanges) {
                                // Update + Mutasi
                                $updateCount++;
                                $mutasiCount++;
                                $processedData[] = [
                                    'nama' => $nama,
                                    'nip' => $nip,
                                    'action' => 'updated_mutasi',
                                    'old_data' => $oldData,
                                    'new_data' => [
                                        'golongan' => $golongan,
                                        'jabatan' => $jabatan,
                                        'satker' => $satker_nama
                                    ],
                                    'changes' => $changes,
                                    'satker_lama' => $satkerModel->find($oldData['satker_id'])['nama'],
                                    'satker_baru' => $satker_nama
                                ];
                            } else {
                                // Mutasi saja
                                $mutasiCount++;
                                $processedData[] = [
                                    'nama' => $nama,
                                    'nip' => $nip,
                                    'action' => 'mutasi',
                                    'old_data' => $oldData,
                                    'new_data' => [
                                        'golongan' => $golongan,
                                        'jabatan' => $jabatan,
                                        'satker' => $satker_nama
                                    ],
                                    'satker_lama' => $satkerModel->find($oldData['satker_id'])['nama'],
                                    'satker_baru' => $satker_nama
                                ];
                            }
                        } else {
                            // Update data saja (gol/jabatan berubah)
                            $updateCount++;
                            $processedData[] = [
                                'nama' => $nama,
                                'nip' => $nip,
                                'action' => 'updated',
                                'old_data' => $oldData,
                                'new_data' => [
                                    'golongan' => $golongan,
                                    'jabatan' => $jabatan,
                                    'satker' => $satker_nama
                                ],
                                'changes' => $changes
                            ];
                        }
                    }
                    
                    $successCount++;
                    
                } else {
                    // INSERT DATA BARU
                    $saveData = [
                        'nama' => $nama,
                        'nip' => $nip,
                        'pangkat' => $pangkat,
                        'golongan' => $golongan,
                        'jabatan' => $jabatan,
                        'status' => 'aktif',
                    ];
                    
                    $this->pegawaiModel->insert($saveData);
                    $pegawai_id = $this->pegawaiModel->getInsertID();
                    
                    $riwayatMutasiModel->insert([
                        'pegawai_id' => $pegawai_id,
                        'satker_id' => $satker['id'],
                        'tanggal_mulai' => $tmt_mutasi,
                    ]);
                    
                    $newCount++;
                    $successCount++;
                    $processedData[] = [
                        'nama' => $nama,
                        'nip' => $nip,
                        'action' => 'new'
                    ];
                }
            }
            
            // Simpan data proses ke session untuk ditampilkan di progress
            $session->set('import_progress', [
                'total' => count($lines) - $headerIndex - 1,
                'processed' => $successCount,
                'updated' => $updateCount,
                'new' => $newCount,
                'mutasi' => $mutasiCount,
                'skip' => $skipCount,
                'errors' => count($errorMessages),
                'processed_data' => $processedData,
                'error_messages' => $errorMessages
            ]);
            
            if ($successCount > 0) {
                $message = "$successCount pegawai diproses (Update: $updateCount, Baru: $newCount, Mutasi: $mutasiCount, Skip: $skipCount)";
                if (!empty($errorMessages)) {
                    $message .= ". " . count($errorMessages) . " baris gagal.";
                }
                $session->setFlashdata('msg', $message);
                $session->setFlashdata('msg_type', 'success');
            } else {
                $message = 'Tidak ada pegawai yang berhasil diproses.';
                if (!empty($errorMessages)) {
                    $message .= ' Detail error: ' . implode('; ', array_slice($errorMessages, 0, 5));
                    if (count($errorMessages) > 5) {
                        $message .= '... dan ' . (count($errorMessages) - 5) . ' error lainnya.';
                    }
                }
                $session->setFlashdata('msg', $message);
                $session->setFlashdata('msg_type', 'danger');
            }
        } else {
            $session->setFlashdata('msg', 'File CSV tidak valid.');
            $session->setFlashdata('msg_type', 'danger');
        }
        
        return redirect()->to(base_url('admin/input_pegawai'));
    }

    // Method untuk AJAX progress tracking
    public function getImportProgress()
    {
        $progress = session()->get('import_progress');
        if ($progress) {
            return $this->response->setJSON($progress);
        }
        return $this->response->setJSON(['status' => 'no_data']);
    }
} 
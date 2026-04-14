<?php
namespace App\Controllers\Guest;

use App\Models\LandingPageModel;
use App\Controllers\BaseController;

class LandingPageController extends BaseController
{
    public function index()
    {
        $model = new LandingPageModel();
        $stat = $model->getStatistik();
        return view('guest/HalamanAwal', [
            'stat_satker' => $stat['satker'],
            'stat_pegawai' => $stat['pegawai'],
            'stat_laporan' => $stat['laporan'],
            'stat_mutasi' => $stat['mutasi'],
        ]);
    }
} 
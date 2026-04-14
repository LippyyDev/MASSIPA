<?php

namespace App\Controllers\Guest;

use CodeIgniter\Controller;

class Home extends Controller
{
    public function index()
    {
        return view('guest/HalamanAwal');
    }
} 
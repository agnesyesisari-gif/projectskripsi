<?php

namespace App\Controllers;

use App\Models\JadwalIbadahModel;
use App\Models\KomisiModel;
use App\Models\PengumumanModel;
use App\Models\ProgramKerjaModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $jadwalModel   = new JadwalIbadahModel();
        $komisiModel   = new KomisiModel();
        $programModel  = new ProgramKerjaModel();
        $pengumumModel = new PengumumanModel();
        $userModel     = new UserModel();

        $data = [
            'pageTitle'  => 'Dashboard',
            'activePage' => 'dashboard',
            'stats' => [
                'jadwal'        => $jadwalModel->countAll(),
                'jadwal_minggu' => $jadwalModel->countMingguIni(),
                'komisi'        => $komisiModel->countAll(),
                'program'       => $programModel->countAll(),
                'prog_aktif'    => $programModel->countAktif(),
                'pengumuman'    => $pengumumModel->countAll(),
                'user'          => $userModel->countAll(),
            ],
        ];

        return $this->render('dashboard/index', $data);
    }
}

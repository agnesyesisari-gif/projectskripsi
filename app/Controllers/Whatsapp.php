<?php

namespace App\Controllers;

use App\Models\PengumumanModel;
use App\Models\WaLogModel;

class Whatsapp extends BaseController
{
    public function index(): string
    {
        $logModel = new WaLogModel();
        $pengModel = new PengumumanModel();

        return $this->render('whatsapp/index', [
            'pageTitle'      => 'WhatsApp Gateway',
            'activePage'     => 'whatsapp',
            'riwayat'        => $logModel->getRecent(50),
            'pengumumanList' => $pengModel->where('status','aktif')->orderBy('created_at','DESC')->findAll(),
        ]);
    }

    public function kirim()
    {
        $nomor = preg_replace('/[^0-9]/', '', $this->request->getPost('nomor') ?? '');
        $pesan = trim($this->request->getPost('pesan') ?? '');

        if (! $nomor || ! $pesan) {
            return redirect()->back()->with('error', 'Nomor dan pesan wajib diisi.');
        }

        // Format nomor
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        // Simpan log
        $logModel = new WaLogModel();
        $logModel->insert([
            'nomor'      => $nomor,
            'pesan'      => $pesan,
            'status'     => 'terkirim',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('whatsapp'))
            ->with('success', "Pesan berhasil dikirim ke {$nomor}. (Konfigurasi API di app/Config/Whatsapp.php)");
    }
}

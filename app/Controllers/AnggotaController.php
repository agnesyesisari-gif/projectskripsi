<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AnggotaModel;
use App\Models\PelayananModel;
use Config\Services;

class AnggotaController extends BaseController
{
    protected $anggotaModel;
    protected $pelayananModel;
    protected $validation;

    public function __construct()
    {
        $this->anggotaModel = new AnggotaModel();
        $this->pelayananModel = new PelayananModel();
        $this->validation = Services::validation();
        
        helper(['form', 'url', 'auth']);
    }

    /**
     * Menampilkan daftar anggota
     */
    public function index()
    {
        // Cek apakah user sudah login (sesuaikan dengan sistem auth Anda)
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Data Anggota Jemaat',
            'anggota' => $this->anggotaModel->getAllWithPelayanan(),
            'pager' => $this->anggotaModel->pager,
            'validation' => $this->validation
        ];

        return view('admin/anggota/index', $data);
    }

    /**
     * Menampilkan form tambah anggota
     */
    public function create()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        // Ambil data pelayanan untuk dropdown
        $dataPelayanan = $this->pelayananModel->findAll();

        $data = [
            'title' => 'Tambah Anggota Baru',
            'validation' => $this->validation,
            'data_pelayanan' => $dataPelayanan
        ];

        return view('admin/anggota/create', $data);
    }

    /**
     * Menyimpan data anggota baru
     */
    public function store()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        // Validasi input
        $rules = [
            'nama' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Nama harus diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'max_length' => 'Nama maksimal 100 karakter'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[anggota.email]',
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'telepon' => [
                'rules' => 'required|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor telepon harus diisi',
                    'min_length' => 'Telepon minimal 10 digit',
                    'max_length' => 'Telepon maksimal 15 digit'
                ]
            ],
            'alamat' => [
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Alamat harus diisi',
                    'min_length' => 'Alamat minimal 10 karakter'
                ]
            ],
            'tanggal_lahir' => [
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'Tanggal lahir harus diisi',
                    'valid_date' => 'Format tanggal tidak valid'
                ]
            ],
            'jenis_kelamin' => [
                'rules' => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin harus dipilih',
                    'in_list' => 'Jenis kelamin tidak valid'
                ]
            ],
            'status_anggota' => [
                'rules' => 'required|in_list[Aktif,Tidak Aktif,Berkunjung]',
                'errors' => [
                    'required' => 'Status anggota harus dipilih',
                    'in_list' => 'Status tidak valid'
                ]
            ],
            'foto' => [
                'rules' => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran foto maksimal 2MB',
                    'is_image' => 'File harus berupa gambar',
                    'mime_in' => 'Format gambar harus JPG, JPEG, atau PNG'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle upload foto
        $fotoName = 'default.png';
        $foto = $this->request->getFile('foto');
        
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $fotoName = $foto->getRandomName();
            $foto->move('uploads/anggota', $fotoName);
            
            // Resize gambar jika perlu
            $this->resizeImage('uploads/anggota/' . $fotoName, 300, 300);
        }

        // Data untuk disimpan
        $data = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'telepon' => $this->request->getPost('telepon'),
            'alamat' => $this->request->getPost('alamat'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'status_anggota' => $this->request->getPost('status_anggota'),
            'foto' => $fotoName,
            'tanggal_bergabung' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Simpan data anggota
        if ($this->anggotaModel->save($data)) {
            $anggotaId = $this->anggotaModel->getInsertID();
            
            // Simpan pelayanan anggota (jika ada)
            $pelayananIds = $this->request->getPost('pelayanan');
            if ($pelayananIds) {
                foreach ($pelayananIds as $pelayananId) {
                    $this->anggotaModel->addPelayanan($anggotaId, $pelayananId);
                }
            }
            
            session()->setFlashdata('success', 'Data anggota berhasil ditambahkan');
            return redirect()->to('/anggota');
        } else {
            session()->setFlashdata('error', 'Gagal menambahkan data anggota');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menampilkan detail anggota
     */
    public function show($id)
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $anggota = $this->anggotaModel->getWithPelayanan($id);
        
        if (!$anggota) {
            session()->setFlashdata('error', 'Data anggota tidak ditemukan');
            return redirect()->to('/anggota');
        }

        $data = [
            'title' => 'Detail Anggota',
            'anggota' => $anggota
        ];

        return view('admin/anggota/show', $data);
    }

    /**
     * Menampilkan form edit anggota
     */
    public function edit($id)
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $anggota = $this->anggotaModel->getWithPelayanan($id);
        
        if (!$anggota) {
            session()->setFlashdata('error', 'Data anggota tidak ditemukan');
            return redirect()->to('/anggota');
        }

        $dataPelayanan = $this->pelayananModel->findAll();
        $anggotaPelayanan = $this->anggotaModel->getPelayananIds($id);

        $data = [
            'title' => 'Edit Data Anggota',
            'anggota' => $anggota,
            'validation' => $this->validation,
            'data_pelayanan' => $dataPelayanan,
            'anggota_pelayanan' => $anggotaPelayanan
        ];

        return view('admin/anggota/edit', $data);
    }

    /**
     * Mengupdate data anggota
     */
    public function update($id)
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        // Cek apakah data ada
        $anggota = $this->anggotaModel->find($id);
        if (!$anggota) {
            session()->setFlashdata('error', 'Data anggota tidak ditemukan');
            return redirect()->to('/anggota');
        }

        // Validasi input
        $rules = [
            'nama' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Nama lengkap harus diisi',
                    'min_length' => 'Nama minimal 3 karakter',
                    'max_length' => 'Nama maksimal 100 karakter'
                ]
            ],
            'email' => [
                'rules' => "required|valid_email|is_unique[anggota.email,id,{$id}]",
                'errors' => [
                    'required' => 'Email harus diisi',
                    'valid_email' => 'Email tidak valid',
                    'is_unique' => 'Email sudah terdaftar'
                ]
            ],
            'telepon' => [
                'rules' => 'required|min_length[10]|max_length[15]',
                'errors' => [
                    'required' => 'Nomor telepon harus diisi',
                    'min_length' => 'Telepon minimal 10 digit',
                    'max_length' => 'Telepon maksimal 15 digit'
                ]
            ],
            'alamat' => [
                'rules' => 'required|min_length[10]',
                'errors' => [
                    'required' => 'Alamat harus diisi',
                    'min_length' => 'Alamat minimal 10 karakter'
                ]
            ],
            'tanggal_lahir' => [
                'rules' => 'required|valid_date',
                'errors' => [
                    'required' => 'Tanggal lahir harus diisi',
                    'valid_date' => 'Format tanggal tidak valid'
                ]
            ],
            'jenis_kelamin' => [
                'rules' => 'required|in_list[L,P]',
                'errors' => [
                    'required' => 'Jenis kelamin harus dipilih',
                    'in_list' => 'Jenis kelamin tidak valid'
                ]
            ],
            'status_anggota' => [
                'rules' => 'required|in_list[Aktif,Tidak Aktif,Berkunjung]',
                'errors' => [
                    'required' => 'Status anggota harus dipilih',
                    'in_list' => 'Status tidak valid'
                ]
            ],
            'foto' => [
                'rules' => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran foto maksimal 2MB',
                    'is_image' => 'File harus berupa gambar',
                    'mime_in' => 'Format gambar harus JPG, JPEG, atau PNG'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle upload foto baru
        $fotoName = $anggota['foto'];
        $foto = $this->request->getFile('foto');
        
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            // Hapus foto lama jika bukan default
            if ($fotoName !== 'default.png') {
                $oldPath = 'uploads/anggota/' . $fotoName;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $fotoName = $foto->getRandomName();
            $foto->move('uploads/anggota', $fotoName);
            
            // Resize gambar
            $this->resizeImage('uploads/anggota/' . $fotoName, 300, 300);
        }

        // Data untuk diupdate
        $data = [
            'id' => $id,
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'telepon' => $this->request->getPost('telepon'),
            'alamat' => $this->request->getPost('alamat'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'status_anggota' => $this->request->getPost('status_anggota'),
            'foto' => $fotoName,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Update data anggota
        if ($this->anggotaModel->save($data)) {
            // Update pelayanan anggota
            $this->anggotaModel->removeAllPelayanan($id);
            
            $pelayananIds = $this->request->getPost('pelayanan');
            if ($pelayananIds) {
                foreach ($pelayananIds as $pelayananId) {
                    $this->anggotaModel->addPelayanan($id, $pelayananId);
                }
            }
            
            session()->setFlashdata('success', 'Data anggota berhasil diupdate');
            return redirect()->to('/anggota');
        } else {
            session()->setFlashdata('error', 'Gagal mengupdate data anggota');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Menghapus data anggota
     */
    public function delete($id)
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $anggota = $this->anggotaModel->find($id);
        
        if (!$anggota) {
            session()->setFlashdata('error', 'Data anggota tidak ditemukan');
            return redirect()->to('/anggota');
        }

        // Hapus foto jika bukan default
        if ($anggota['foto'] !== 'default.png') {
            $fotoPath = 'uploads/anggota/' . $anggota['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        // Hapus relasi pelayanan
        $this->anggotaModel->removeAllPelayanan($id);
        
        // Hapus data anggota
        if ($this->anggotaModel->delete($id)) {
            session()->setFlashdata('success', 'Data anggota berhasil dihapus');
        } else {
            session()->setFlashdata('error', 'Gagal menghapus data anggota');
        }

        return redirect()->to('/anggota');
    }

    /**
     * Menampilkan laporan anggota
     */
    public function report()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $filter = [
            'status' => $this->request->getGet('status'),
            'jenis_kelamin' => $this->request->getGet('jenis_kelamin'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date')
        ];

        $data = [
            'title' => 'Laporan Data Anggota',
            'anggota' => $this->anggotaModel->getReport($filter),
            'filter' => $filter
        ];

        return view('admin/anggota/report', $data);
    }

    /**
     * Export data anggota ke PDF
     */
    public function exportPDF()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $filter = [
            'status' => $this->request->getGet('status'),
            'jenis_kelamin' => $this->request->getGet('jenis_kelamin')
        ];

        $data = [
            'title' => 'Laporan Data Anggota',
            'anggota' => $this->anggotaModel->getReport($filter),
            'filter' => $filter,
            'tanggal_cetak' => date('d F Y')
        ];

        // Load library PDF (misal: Dompdf)
        $dompdf = new \Dompdf\Dompdf();
        $html = view('admin/anggota/export_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('laporan-anggota-' . date('Ymd') . '.pdf', ['Attachment' => true]);
    }

    /**
     * Export data anggota ke Excel
     */
    public function exportExcel()
    {
        if (!is_logged_in()) {
            return redirect()->to('/login');
        }

        $filter = [
            'status' => $this->request->getGet('status'),
            'jenis_kelamin' => $this->request->getGet('jenis_kelamin')
        ];

        $anggota = $this->anggotaModel->getReport($filter);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'LAPORAN DATA ANGGOTA');
        $sheet->setCellValue('A2', 'Tanggal Cetak: ' . date('d F Y'));
        $sheet->setCellValue('A4', 'NO');
        $sheet->setCellValue('B4', 'NAMA');
        $sheet->setCellValue('C4', 'EMAIL');
        $sheet->setCellValue('D4', 'TELEPON');
        $sheet->setCellValue('E4', 'ALAMAT');
        $sheet->setCellValue('F4', 'TANGGAL LAHIR');
        $sheet->setCellValue('G4', 'JENIS KELAMIN');
        $sheet->setCellValue('H4', 'STATUS ANGGOTA');
        $sheet->setCellValue('I4', 'TANGGAL BERGABUNG');

        // Data
        $row = 5;
        $no = 1;
        foreach ($anggota as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item['nama']);
            $sheet->setCellValue('C' . $row, $item['email']);
            $sheet->setCellValue('D' . $row, $item['telepon']);
            $sheet->setCellValue('E' . $row, $item['alamat']);
            $sheet->setCellValue('F' . $row, $item['tanggal_lahir']);
            $sheet->setCellValue('G' . $row, $item['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan');
            $sheet->setCellValue('H' . $row, $item['status_anggota']);
            $sheet->setCellValue('I' . $row, $item['tanggal_bergabung']);
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'laporan-anggota-' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit();
    }

    /**
     * Helper function untuk resize gambar
     */
    private function resizeImage($path, $width, $height)
    {
        $image = Services::image();
        $image->withFile($path)
              ->resize($width, $height, true, 'height')
              ->save($path);
    }

    /**
     * API untuk mendapatkan data anggota (jika diperlukan)
     */
    public function apiGetAnggota()
    {
        if (!is_logged_in()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized'
            ])->setStatusCode(401);
        }

        $id = $this->request->getGet('id');
        
        if ($id) {
            $anggota = $this->anggotaModel->getWithPelayanan($id);
        } else {
            $anggota = $this->anggotaModel->findAll();
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $anggota
        ]);
    }
}
<?php

namespace App\Controllers;

use App\Models\CalendarModel;
use App\Models\ServiceModel;
use App\Models\ProgramModel;

class CalendarController extends BaseController
{
    protected $calendarModel;
    protected $serviceModel;
    protected $programModel;
    protected $helpers = ['form', 'url'];

    public function __construct()
    {
        $this->calendarModel = new CalendarModel();
        $this->serviceModel = new ServiceModel();
        $this->programModel = new ProgramModel();
    }

    /**
     * Menampilkan kalender utama dengan semua kegiatan
     */
    public function index()
    {
        $data = [
            'title' => 'Kalender Kegiatan Gereja',
            'activeMenu' => 'calendar',
            'month' => $this->request->getGet('month') ?? date('n'),
            'year' => $this->request->getGet('year') ?? date('Y'),
            'validation' => \Config\Services::validation()
        ];

        return view('calendar/index', $data);
    }

    /**
     * Mendapatkan data kalender dalam format JSON (untuk FullCalendar.js)
     */
    public function getEvents()
    {
        $start = $this->request->getGet('start');
        $end = $this->request->getGet('end');

        $events = $this->calendarModel->getEventsBetween($start, $end);

        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date ?? $event->start_date,
                'color' => $this->getEventColor($event->type),
                'description' => $event->description,
                'type' => $event->type,
                'location' => $event->location,
                'extendedProps' => [
                    'type' => $event->type,
                    'category' => $event->category,
                    'contact_person' => $event->contact_person,
                    'phone' => $event->contact_phone
                ]
            ];
        }

        return $this->response->setJSON($formattedEvents);
    }

    /**
     * Menampilkan detail kegiatan
     */
    public function detail($id)
    {
        $event = $this->calendarModel->find($id);

        if (!$event) {
            return redirect()->to('/calendar')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $data = [
            'title' => 'Detail Kegiatan',
            'activeMenu' => 'calendar',
            'event' => $event,
            'relatedEvents' => $this->calendarModel->getRelatedEvents($event->category)
        ];

        return view('calendar/detail', $data);
    }

    /**
     * Menampilkan form tambah kegiatan
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Kegiatan Baru',
            'activeMenu' => 'calendar',
            'validation' => \Config\Services::validation(),
            'serviceTypes' => $this->serviceModel->getAllTypes(),
            'programCategories' => $this->programModel->getAllCategories()
        ];

        return view('calendar/create', $data);
    }

    /**
     * Menyimpan data kegiatan baru
     */
    public function store()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'type' => 'required|in_list[service,program,meeting,social]',
            'category' => 'required',
            'start_date' => 'required|valid_date',
            'end_date' => 'permit_empty|valid_date',
            'start_time' => 'permit_empty|valid_time',
            'end_time' => 'permit_empty|valid_time',
            'location' => 'required|max_length[255]',
            'description' => 'required|min_length[10]',
            'contact_person' => 'required|max_length[100]',
            'contact_phone' => 'required|max_length[20]',
            'status' => 'required|in_list[active,cancelled,postponed]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Gabungkan tanggal dan waktu
        $startDateTime = $this->request->getPost('start_date');
        if ($this->request->getPost('start_time')) {
            $startDateTime .= ' ' . $this->request->getPost('start_time');
        }

        $endDateTime = $this->request->getPost('end_date') ?? $this->request->getPost('start_date');
        if ($this->request->getPost('end_time')) {
            $endDateTime .= ' ' . $this->request->getPost('end_time');
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'type' => $this->request->getPost('type'),
            'category' => $this->request->getPost('category'),
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'location' => $this->request->getPost('location'),
            'description' => $this->request->getPost('description'),
            'contact_person' => $this->request->getPost('contact_person'),
            'contact_phone' => $this->request->getPost('contact_phone'),
            'status' => $this->request->getPost('status'),
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($this->calendarModel->insert($data)) {
            return redirect()->to('/calendar')->with('success', 'Kegiatan berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan kegiatan.');
        }
    }

    /**
     * Menampilkan form edit kegiatan
     */
    public function edit($id)
    {
        $event = $this->calendarModel->find($id);

        if (!$event) {
            return redirect()->to('/calendar')->with('error', 'Kegiatan tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Kegiatan',
            'activeMenu' => 'calendar',
            'event' => $event,
            'validation' => \Config\Services::validation(),
            'serviceTypes' => $this->serviceModel->getAllTypes(),
            'programCategories' => $this->programModel->getAllCategories()
        ];

        return view('calendar/edit', $data);
    }

    /**
     * Mengupdate data kegiatan
     */
    public function update($id)
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'type' => 'required|in_list[service,program,meeting,social]',
            'category' => 'required',
            'start_date' => 'required|valid_date',
            'end_date' => 'permit_empty|valid_date',
            'location' => 'required|max_length[255]',
            'description' => 'required|min_length[10]',
            'status' => 'required|in_list[active,cancelled,postponed]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'type' => $this->request->getPost('type'),
            'category' => $this->request->getPost('category'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'location' => $this->request->getPost('location'),
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status'),
            'updated_by' => session()->get('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->calendarModel->update($id, $data)) {
            return redirect()->to('/calendar/detail/' . $id)->with('success', 'Kegiatan berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui kegiatan.');
        }
    }

    /**
     * Menghapus kegiatan
     */
    public function delete($id)
    {
        $event = $this->calendarModel->find($id);

        if (!$event) {
            return redirect()->to('/calendar')->with('error', 'Kegiatan tidak ditemukan.');
        }

        if ($this->calendarModel->delete($id)) {
            return redirect()->to('/calendar')->with('success', 'Kegiatan berhasil dihapus.');
        } else {
            return redirect()->to('/calendar')->with('error', 'Gagal menghapus kegiatan.');
        }
    }

    /**
     * Ekspor jadwal ke PDF
     */
    public function exportPDF($month = null, $year = null)
    {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');

        $events = $this->calendarModel->getEventsByMonth($month, $year);

        $data = [
            'events' => $events,
            'month' => $month,
            'year' => $year,
            'monthName' => $this->getMonthName($month)
        ];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('calendar/export_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "jadwal-ibadah-{$month}-{$year}.pdf";
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Ekspor jadwal ke Excel
     */
    public function exportExcel($month = null, $year = null)
    {
        $month = $month ?? date('n');
        $year = $year ?? date('Y');

        $events = $this->calendarModel->getEventsByMonth($month, $year);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'JADWAL KEGIATAN GEREJA');
        $sheet->setCellValue('A2', 'Bulan: ' . $this->getMonthName($month) . ' ' . $year);
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        // Column headers
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Tanggal');
        $sheet->setCellValue('C4', 'Waktu');
        $sheet->setCellValue('D4', 'Kegiatan');
        $sheet->setCellValue('E4', 'Jenis');
        $sheet->setCellValue('F4', 'Lokasi');
        $sheet->setCellValue('G4', 'Penanggung Jawab');
        $sheet->setCellValue('H4', 'Status');

        // Data
        $row = 5;
        $no = 1;
        foreach ($events as $event) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($event->start_date)));
            $sheet->setCellValue('C' . $row, date('H:i', strtotime($event->start_date)));
            $sheet->setCellValue('D' . $row, $event->title);
            $sheet->setCellValue('E' . $row, $this->getTypeName($event->type));
            $sheet->setCellValue('F' . $row, $event->location);
            $sheet->setCellValue('G' . $row, $event->contact_person);
            $sheet->setCellValue('H' . $row, $event->status);
            $row++;
        }

        // Style
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:H' . ($row - 1))->applyFromArray($styleArray);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = "jadwal-ibadah-{$month}-{$year}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    /**
     * Mendapatkan warna berdasarkan jenis kegiatan
     */
    private function getEventColor($type)
    {
        $colors = [
            'service' => '#3498db',    // Biru untuk ibadah
            'program' => '#2ecc71',    // Hijau untuk program kerja
            'meeting' => '#9b59b6',    // Ungu untuk rapat
            'social' => '#e74c3c'      // Merah untuk kegiatan sosial
        ];

        return $colors[$type] ?? '#95a5a6';
    }

    /**
     * Mendapatkan nama bulan
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $months[$month] ?? 'Unknown';
    }

    /**
     * Mendapatkan nama jenis kegiatan
     */
    private function getTypeName($type)
    {
        $types = [
            'service' => 'Ibadah',
            'program' => 'Program Kerja',
            'meeting' => 'Rapat',
            'social' => 'Kegiatan Sosial'
        ];

        return $types[$type] ?? 'Lainnya';
    }

    /**
     * API untuk mendapatkan jadwal minggu ini
     */
    public function apiThisWeek()
    {
        $events = $this->calendarModel->getThisWeekEvents();

        return $this->response->setJSON([
            'success' => true,
            'data' => $events,
            'count' => count($events)
        ]);
    }

    /**
     * API untuk mendapatkan jadwal hari ini
     */
    public function apiToday()
    {
        $events = $this->calendarModel->getTodayEvents();

        return $this->response->setJSON([
            'success' => true,
            'data' => $events,
            'count' => count($events)
        ]);
    }

    /**
     * Menampilkan jadwal berdasarkan kategori
     */
    public function byCategory($category)
    {
        $events = $this->calendarModel->getEventsByCategory($category);

        $data = [
            'title' => 'Jadwal ' . ucfirst($category),
            'activeMenu' => 'calendar',
            'events' => $events,
            'category' => $category
        ];

        return view('calendar/by_category', $data);
    }

    /**
     * Mencari kegiatan berdasarkan keyword
     */
    public function search()
    {
        $keyword = $this->request->getGet('q');

        if (empty($keyword)) {
            return redirect()->to('/calendar');
        }

        $events = $this->calendarModel->searchEvents($keyword);

        $data = [
            'title' => 'Hasil Pencarian: ' . $keyword,
            'activeMenu' => 'calendar',
            'events' => $events,
            'keyword' => $keyword,
            'count' => count($events)
        ];

        return view('calendar/search', $data);
    }
}
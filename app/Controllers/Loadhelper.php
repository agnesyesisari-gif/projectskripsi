<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Loadhelper extends BaseController
{
    /**
     * Constructor.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param LoggerInterface   $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Load helpers yang akan digunakan di semua controller
        $this->loadGlobalHelpers();
    }
    
    /**
     * Load helpers yang digunakan secara global
     */
    protected function loadGlobalHelpers()
    {
        // Helpers dasar CodeIgniter
        helper(['url', 'form', 'cookie', 'security', 'text']);
        
        // Helpers custom untuk sistem gereja
        helper(['auth_helper', 'date_helper', 'church_helper', 'permission_helper']);
    }
    
    /**
     * Function untuk load helpers tambahan sesuai kebutuhan controller
     */
    protected function loadAdditionalHelpers($helpers = [])
    {
        if (!empty($helpers)) {
            helper($helpers);
        }
    }
    
    /**
     * Function untuk memeriksa apakah user sudah login
     */
    protected function checkLogin()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }
        
        return true;
    }
    
    /**
     * Function untuk memeriksa role user
     */
    protected function checkRole($allowedRoles = [])
    {
        $session = session();
        $userRole = $session->get('role');
        
        if (!in_array($userRole, $allowedRoles)) {
            session()->setFlashdata('error', 'Anda tidak memiliki akses ke halaman ini.');
            return redirect()->back();
        }
        
        return true;
    }
    
    /**
     * Function untuk mendapatkan data user yang login
     */
    protected function getUserData()
    {
        $session = session();
        
        return [
            'user_id' => $session->get('user_id'),
            'username' => $session->get('username'),
            'nama_lengkap' => $session->get('nama_lengkap'),
            'role' => $session->get('role'),
            'jabatan' => $session->get('jabatan'),
            'foto' => $session->get('foto')
        ];
    }
    
    /**
     * Function untuk menyiapkan data view umum
     */
    protected function getViewData($additionalData = [])
    {
        $data = [
            'title' => 'Sistem Informasi Pelayanan Gereja',
            'app_name' => 'SIP Gereja',
            'version' => '1.0.0',
            'year' => date('Y'),
            'user' => $this->getUserData(),
            'current_uri' => current_url(),
            'is_logged_in' => session()->get('logged_in') ?? false
        ];
        
        // Tambahkan data tambahan jika ada
        if (!empty($additionalData)) {
            $data = array_merge($data, $additionalData);
        }
        
        return $data;
    }
    
    /**
     * Function untuk validasi akses berdasarkan permission
     */
    protected function hasPermission($permission)
    {
        $session = session();
        $userPermissions = $session->get('permissions') ?? [];
        
        return in_array($permission, $userPermissions);
    }
    
    /**
     * Function untuk mengirim response JSON
     */
    protected function jsonResponse($data, $success = true, $message = '', $statusCode = 200)
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/json')
            ->setJSON($response);
    }
    
    /**
     * Function untuk upload file
     */
    protected function uploadFile($fieldName, $config = [])
    {
        $defaultConfig = [
            'upload_path'   => WRITEPATH . 'uploads/',
            'allowed_types' => 'jpg|jpeg|png|gif|pdf|doc|docx',
            'max_size'      => 2048, // 2MB
            'encrypt_name'  => true
        ];
        
        $config = array_merge($defaultConfig, $config);
        
        $file = $this->request->getFile($fieldName);
        
        if (!$file->isValid()) {
            return [
                'success' => false,
                'error' => $file->getErrorString()
            ];
        }
        
        if ($file->hasMoved()) {
            return [
                'success' => false,
                'error' => 'File sudah diupload sebelumnya.'
            ];
        }
        
        $newName = $file->getRandomName();
        if ($file->move($config['upload_path'], $newName)) {
            return [
                'success' => true,
                'file_name' => $newName,
                'original_name' => $file->getClientName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_path' => $config['upload_path'] . $newName
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Gagal mengupload file.'
            ];
        }
    }
    
    /**
     * Function untuk mendapatkan setting gereja
     */
    protected function getChurchSettings()
    {
        // Load model settings
        $settingsModel = model('SettingsModel');
        
        $settings = $settingsModel->getAllSettings();
        $organizedSettings = [];
        
        foreach ($settings as $setting) {
            $organizedSettings[$setting->key] = $setting->value;
        }
        
        return $organizedSettings;
    }
    
    /**
     * Function untuk log aktivitas
     */
    protected function logActivity($activity, $module, $userId = null)
    {
        if (is_null($userId)) {
            $userData = $this->getUserData();
            $userId = $userData['user_id'];
        }
        
        $logData = [
            'user_id' => $userId,
            'activity' => $activity,
            'module' => $module,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $activityModel = model('ActivityLogModel');
        return $activityModel->insert($logData);
    }
    
    /**
     * Function untuk format tanggal Indonesia
     */
    protected function indonesianDate($date, $withDay = false)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        
        $timestamp = strtotime($date);
        $dayNum = date('w', $timestamp);
        $day = $days[$dayNum];
        $dateNum = date('j', $timestamp);
        $month = $months[date('n', $timestamp)];
        $year = date('Y', $timestamp);
        
        if ($withDay) {
            return "$day, $dateNum $month $year";
        }
        
        return "$dateNum $month $year";
    }
    
    /**
     * Function untuk format waktu ibadah
     */
    protected function formatWorshipTime($time)
    {
        return date('H:i', strtotime($time));
    }
    
    /**
     * Function untuk generate kode kegiatan
     */
    protected function generateActivityCode($type = 'KEG')
    {
        $prefix = $type;
        $year = date('Y');
        $month = date('m');
        
        $activityModel = model('ActivityModel');
        $lastCode = $activityModel->getLastCode($prefix, $year, $month);
        
        if ($lastCode) {
            $lastNumber = (int) substr($lastCode, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}

// Controller Base yang extend Loadhelper
class BaseChurchController extends Loadhelper
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Function untuk render view dengan template gereja
     */
    protected function renderView($view, $data = [], $template = 'template/church_template')
    {
        $viewData = $this->getViewData($data);
        
        return view($template, [
            'content' => view($view, $viewData),
            'view_data' => $viewData
        ]);
    }
    
    /**
     * Function untuk halaman dashboard
     */
    public function dashboard()
    {
        if (!$this->checkLogin()) {
            return;
        }
        
        // Load model yang diperlukan
        $worshipModel = model('WorshipScheduleModel');
        $activityModel = model('ChurchActivityModel');
        $memberModel = model('MemberModel');
        
        // Data untuk dashboard
        $data = [
            'total_members' => $memberModel->countAll(),
            'upcoming_worship' => $worshipModel->getUpcomingWorship(5),
            'recent_activities' => $activityModel->getRecentActivities(5),
            'today_schedule' => $worshipModel->getTodaySchedule(),
            'monthly_stats' => $this->getMonthlyStats()
        ];
        
        return $this->renderView('dashboard/index', $data);
    }
    
    /**
     * Function untuk mendapatkan statistik bulanan
     */
    protected function getMonthlyStats()
    {
        $attendanceModel = model('AttendanceModel');
        $month = date('m');
        $year = date('Y');
        
        return $attendanceModel->getMonthlyStats($month, $year);
    }
}

// Controller untuk jadwal ibadah
class WorshipSchedule extends BaseChurchController
{
    public function __construct()
    {
        parent::__construct();
        $this->loadAdditionalHelpers(['date']);
    }
    
    public function index()
    {
        if (!$this->checkLogin()) {
            return;
        }
        
        $model = model('WorshipScheduleModel');
        $data['schedules'] = $model->getAllSchedules();
        
        return $this->renderView('worship/index', $data);
    }
    
    public function create()
    {
        if (!$this->checkLogin() || !$this->checkRole(['admin', 'pastor'])) {
            return;
        }
        
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'jenis_ibadah' => 'required',
                'tanggal' => 'required|valid_date',
                'waktu' => 'required',
                'tempat' => 'required',
                'pemimpin_ibadah' => 'required',
                'keterangan' => 'required',
            ];
            
            if ($this->validate($rules)) {
                $model = model('WorshipScheduleModel');
                $postData = $this->request->getPost();
                
                $saveData = [
                    'jenis_ibadah' => $postData['jenis_ibadah'],
                    'tanggal' => $postData['tanggal'],
                    'waktu' => $postData['waktu'],
                    'tempat' => $postData['tempat'],
                    'pemimpin_ibadah' => $postData['pemimpin_ibadah'],
                    'keterangan' => $postData['keterangan'] ?? null,
                    'created_by' => session()->get('user_id')
                ];
                
                if ($model->save($saveData)) {
                    $this->logActivity('Menambah jadwal ibadah: ' . $postData['nama_ibadah'], 'Worship Schedule');
                    session()->setFlashdata('success', 'Jadwal ibadah berhasil ditambahkan.');
                    return redirect()->to('/worship');
                } else {
                    session()->setFlashdata('error', 'Gagal menambah jadwal ibadah.');
                }
            }
        }
        
        return $this->renderView('worship/create');
    }
    
    public function calendar()
    {
        if (!$this->checkLogin()) {
            return;
        }
        
        $model = model('WorshipScheduleModel');
        $data['events'] = $model->getCalendarEvents();
        
        return $this->renderView('worship/calendar', $data);
    }
}

// Controller untuk program kerja
class WorkProgram extends BaseChurchController
{
    public function __construct()
    {
        parent::__construct();
        $this->loadAdditionalHelpers(['form', 'text']);
    }
    
    public function index()
    {
        if (!$this->checkLogin()) {
            return;
        }
        
        $model = model('WorkProgramModel');
        $data['programs'] = $model->getAllPrograms();
        
        return $this->renderView('workprogram/index', $data);
    }
    
    public function detail($id)
    {
        if (!$this->checkLogin()) {
            return;
        }
        
        $model = model('WorkProgramModel');
        $program = $model->find($id);
        
        if (!$program) {
            session()->setFlashdata('error', 'Program kerja tidak ditemukan.');
            return redirect()->back();
        }
        
        $data['program'] = $program;
        $data['activities'] = $model->getProgramActivities($id);
        
        return $this->renderView('workprogram/detail', $data);
    }
    
    public function create()
    {
        if (!$this->checkLogin() || !$this->checkRole(['admin', 'pastor', 'department_head'])) {
            return;
        }
        
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'nama_kegiatan' => 'required',
                'komisi_id' => 'required',
                'tahun' => 'required|numeric',
                'tanggal_mulai' => 'required|valid_date',
                'tanggal_selesai' => 'required|valid_date',
                'anggaran' => 'required|numeric',
                'penanggung_jawab_id' => 'required|numeric'
            ];
            
            if ($this->validate($rules)) {
                $model = model('WorkProgramModel');
                $postData = $this->request->getPost();
                
                $saveData = [
                    'kode_kegiatan' => $this->generateActivityCode('PROG'),
                    'nama_kegiatan' => $postData['nama_kegiatan'],
                    'komisi_id' => $postData['komisi_id'],
                    'tahun' => $postData['tahun'],
                    'tanggal_mulai' => $postData['tanggal_mulai'],
                    'tanggal_selesai' => $postData['tanggal_selesai'],
                    'anggaran' => $postData['anggaran'],
                    'deskripsi' => $postData['deskripsi'] ?? null,
                    'penanggung_jawab_id' => $this->request->getPost('penanggung_jawab_id'),
                    'status' => 'draft',
                    'created_by' => session()->get('user_id')
                ];
                
                if ($model->save($saveData)) {
                    $this->logActivity('Membuat program kerja: ' . $postData['nama_program'], 'Work Program');
                    session()->setFlashdata('success', 'Program kerja berhasil dibuat.');
                    return redirect()->to('/workprogram');
                } else {
                    session()->setFlashdata('error', 'Gagal membuat program kerja.');
                }
            }
        }
        
        $komsModel = model('KomisiModel');
        $data['koms'] = $komsModel->findAll();
        
        return $this->renderView('workprogram/create', $data);
    }
}

// Controller untuk laporan
class Report extends BaseChurchController
{
    public function __construct()
    {
        parent::__construct();
        $this->loadAdditionalHelpers(['download', 'number']);
    }
    
    public function worshipAttendance()
    {
        if (!$this->checkLogin() || !$this->checkRole(['admin', 'pastor', 'secretary'])) {
            return;
        }
        
        $model = model('AttendanceModel');
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $data['attendance'] = $model->getAttendanceReport($startDate, $endDate);
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        
        return $this->renderView('report/worship_attendance', $data);
    }
    
    public function exportAttendance()
    {
        if (!$this->checkLogin() || !$this->checkRole(['admin', 'pastor', 'secretary'])) {
            return;
        }
        
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');
        
        $model = model('AttendanceModel');
        $data = $model->getAttendanceReport($startDate, $endDate);
        
        // Generate PDF 
        // Implementasi ekspor sesuai kebutuhan
        
        $this->logActivity('Mengekspor laporan kehadiran', 'Report');
        
        return $this->response->download(
            'laporan_kehadiran_' . date('Ymd_His') . '.pdf',
            $pdfContent
        );
    }
}
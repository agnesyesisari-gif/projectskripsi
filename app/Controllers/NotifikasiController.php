<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotifikasiModel;
use App\Models\UserModel;
use App\Models\AnggotaModel;
use App\Models\IbadahModel;
use App\Models\ProgramKerjaModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Email\Email;

class NotifikasiController extends BaseController
{
    use ResponseTrait;

    protected $notifikasiModel;
    protected $userModel;
    protected $anggotaModel;
    protected $ibadahModel;
    protected $programKerjaModel;
    protected $email;

    public function __construct()
    {
        $this->notifikasiModel = new NotifikasiModel();
        $this->userModel = new UserModel();
        $this->anggotaModel = new AnggotaModel();
        $this->ibadahModel = new IbadahModel();
        $this->programKerjaModel = new ProgramKerjaModel();
        $this->email = \Config\Services::email();
        
        helper(['form', 'url', 'date', 'text']);
    }

    /**
     * Dashboard Notifikasi
     */
    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role');
        
        $data = [
            'title' => 'Manajemen Notifikasi',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Notifikasi', 'url' => '/notifikasi']
            ],
            'unreadCount' => $this->notifikasiModel->getUnreadCount($userId),
            'recentNotifications' => $this->notifikasiModel->getRecentNotifications($userId, 10),
            'notificationStats' => $this->notifikasiModel->getNotificationStats(),
            'notificationTypes' => $this->notifikasiModel->getNotificationTypes(),
            'userRole' => $userRole,
            'userId' => $userId
        ];

        return view('admin/notifikasi/dashboard', $data);
    }

    /**
     * Daftar Notifikasi
     */
    public function listNotifications()
    {
        $userId = session()->get('user_id');
        $type = $this->request->getGet('type');
        $status = $this->request->getGet('status');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 20;

        $filters = [
            'user_id' => $userId,
            'type' => $type,
            'status' => $status,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $notifications = $this->notifikasiModel->getNotifications($filters, $perPage, $page);
        $pager = $this->notifikasiModel->pager;

        $data = [
            'title' => 'Daftar Notifikasi',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Notifikasi', 'url' => '/notifikasi'],
                ['title' => 'Daftar', 'url' => '/notifikasi/list']
            ],
            'notifications' => $notifications,
            'pager' => $pager,
            'filters' => $filters,
            'types' => [
                'sistem' => 'Sistem',
                'ibadah' => 'Ibadah',
                'program' => 'Program Kerja',
                'kegiatan' => 'Kegiatan',
                'reminder' => 'Pengingat',
                'approval' => 'Persetujuan',
                'lainnya' => 'Lainnya'
            ],
            'statuses' => [
                'unread' => 'Belum Dibaca',
                'read' => 'Sudah Dibaca',
                'archived' => 'Diarsipkan'
            ]
        ];

        return view('admin/notifikasi/list', $data);
    }

    /**
     * Buat Notifikasi Baru
     */
    public function create()
    {
        // Hanya admin dan pengelola notifikasi yang bisa membuat
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'super_admin', 'notifikasi_manager'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk membuat notifikasi.');
        }

        $data = [
            'title' => 'Buat Notifikasi Baru',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Notifikasi', 'url' => '/notifikasi'],
                ['title' => 'Buat Baru', 'url' => '/notifikasi/create']
            ],
            'targetOptions' => [
                'all' => 'Semua Pengguna',
                'role' => 'Berdasarkan Peran',
                'user' => 'Pengguna Tertentu',
                'anggota' => 'Anggota Jemaat',
                'pelayanan' => 'Berdasarkan Pelayanan'
            ],
            'typeOptions' => [
                'sistem' => 'Sistem',
                'ibadah' => 'Ibadah',
                'program' => 'Program Kerja',
                'kegiatan' => 'Kegiatan',
            ],
            'priorityOptions' => [
                'low' => 'Rendah',
                'normal' => 'Normal',
                'high' => 'Tinggi',
                'urgent' => 'Mendesak'
            ],
            'userRoles' => $this->userModel->getDistinctRoles(),
            'users' => $this->userModel->findAll(),
            'anggota' => $this->anggotaModel->where('status_anggota', 'active')->findAll(),
            'validation' => \Config\Services::validation()
        ];

        return view('admin/notifikasi/create', $data);
    }

    /**
     * Simpan Notifikasi Baru
     */
    public function store()
    {
        // Validasi
        $rules = [
            'judul' => 'required|min_length[5]|max_length[200]',
            'pesan' => 'required|min_length[10]|max_length[1000]',
            'tipe' => 'required',
            'prioritas' => 'required',
            'expired_at' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $targetTipe = $this->request->getPost('target_tipe');
        $targetIds = [];

        // Tentukan target berdasarkan tipe
        switch ($targetTipe) {
            case 'all':
                $targetIds = $this->getAllUserIds();
                break;
                
            case 'role':
                $roles = $this->request->getPost('target_roles');
                if ($roles) {
                    $targetIds = $this->userModel->getUserIdsByRoles($roles);
                }
                break;
                
            case 'user':
                $userIds = $this->request->getPost('target_user_ids');
                if ($userIds) {
                    $targetIds = $userIds;
                }
                break;
                
            case 'anggota':
                $anggotaIds = $this->request->getPost('target_anggota_ids');
                if ($anggotaIds) {
                    $targetIds = $this->userModel->getUserIdsByAnggotaIds($anggotaIds);
                }
                break;
                
            case 'pelayanan':
                $pelayananIds = $this->request->getPost('target_pelayanan_ids');
                if ($pelayananIds) {
                    $targetIds = $this->userModel->getUserIdsByPelayanan($pelayananIds);
                }
                break;
        }

        if (empty($targetIds)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada target yang dipilih.');
        }

        $jadwalKirim = $this->request->getPost('jadwal_kirim');
        $expiredAt = $this->request->getPost('expired_at');

        // Data notifikasi utama
        $notificationData = [
            'judul' => $this->request->getPost('judul'),
            'pesan' => $this->request->getPost('pesan'),
            'tipe' => $this->request->getPost('tipe'),
            'prioritas' => $this->request->getPost('prioritas'),
            'metadata' => json_encode([
                'created_by' => session()->get('user_id'),
                'attachment' => $this->request->getPost('attachment'),
                'link' => $this->request->getPost('link'),
                'action_text' => $this->request->getPost('action_text'),
                'action_url' => $this->request->getPost('action_url')
            ]),
            'status' => $jadwalKirim ? 'scheduled' : 'pending',
            'expired_at' => $expiredAt,
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Mulai transaksi
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Simpan notifikasi utama
            $notificationId = $this->notifikasiModel->insert($notificationData);

            // Buat notifikasi untuk setiap target
            $batchData = [];
            foreach ($targetIds as $userId) {
                $batchData[] = [
                    'notifikasi_id' => $notificationId,
                    'user_id' => $userId,
                    'status' => 'unread',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            // Insert batch
            if (!empty($batchData)) {
                $this->notifikasiModel->insertUserNotifications($batchData);
            }

            // Jika immediate, kirim notifikasi
            if (!$jadwalKirim) {
                $this->sendImmediateNotifications($notificationId, $targetIds);
            }

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Gagal menyimpan notifikasi.');
            }

            return redirect()->to('/notifikasi')->with('success', 
                'Notifikasi berhasil dibuat dan akan dikirim ke ' . count($targetIds) . ' penerima.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Detail Notifikasi
     */
    public function show($id)
    {
        $userId = session()->get('user_id');
        
        $notification = $this->notifikasiModel->getNotificationWithDetails($id, $userId);
        
        if (!$notification) {
            return redirect()->to('/notifikasi')->with('error', 'Notifikasi tidak ditemukan.');
        }

        // Tandai sebagai dibaca jika belum
        if ($notification['user_status'] == 'unread') {
            $this->notifikasiModel->markAsRead($id, $userId);
        }

        $data = [
            'title' => 'Detail Notifikasi: ' . $notification['judul'],
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Notifikasi', 'url' => '/notifikasi'],
                ['title' => 'Detail', 'url' => '/notifikasi/show/' . $id]
            ],
            'notification' => $notification,
            'deliveryStats' => $this->notifikasiModel->getDeliveryStats($id),
            'userReadStatus' => $this->notifikasiModel->getUserReadStatus($id),
            'canManage' => $this->canManageNotification($notification)
        ];

        return view('admin/notifikasi/show', $data);
    }

    /**
     * API untuk mendapatkan notifikasi terbaru
     */
    public function apiGetNotifications()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->failUnauthorized('Silakan login terlebih dahulu.');
        }

        $limit = $this->request->getGet('limit') ?? 10;
        $offset = $this->request->getGet('offset') ?? 0;

        $notifications = $this->notifikasiModel->getUserNotifications($userId, $limit, $offset);
        $unreadCount = $this->notifikasiModel->getUnreadCount($userId);

        return $this->respond([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'total' => count($notifications)
            ]
        ]);
    }

    /**
     * API untuk menandai notifikasi sebagai dibaca
     */
    public function apiMarkAsRead()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $userId = session()->get('user_id');
        $notificationId = $this->request->getPost('notification_id');

        if (!$notificationId) {
            return $this->fail('Notification ID diperlukan.', 400);
        }

        try {
            $this->notifikasiModel->markAsRead($notificationId, $userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Notifikasi ditandai sebagai dibaca.',
                'unread_count' => $this->notifikasiModel->getUnreadCount($userId)
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * API untuk menandai semua notifikasi sebagai dibaca
     */
    public function apiMarkAllAsRead()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $userId = session()->get('user_id');

        try {
            $this->notifikasiModel->markAllAsRead($userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Semua notifikasi ditandai sebagai dibaca.'
            ]);
            
        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * API untuk menghapus notifikasi
     */
    public function apiDeleteNotification()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $userId = session()->get('user_id');
        $notificationId = $this->request->getPost('notification_id');

        if (!$notificationId) {
            return $this->fail('Notification ID diperlukan.', 400);
        }

        try {
            $deleted = $this->notifikasiModel->deleteUserNotification($notificationId, $userId);
            
            if ($deleted) {
                return $this->respond([
                    'status' => 'success',
                    'message' => 'Notifikasi berhasil dihapus.',
                    'unread_count' => $this->notifikasiModel->getUnreadCount($userId)
                ]);
            } else {
                return $this->fail('Gagal menghapus notifikasi.', 400);
            }
            
        } catch (\Exception $e) {
            return $this->failServerError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim Notifikasi Ibadah
     */
    public function sendIbadahNotification($ibadahId)
    {
        $ibadah = $this->ibadahModel->find($ibadahId);
        if (!$ibadah) {
            return redirect()->back()->with('error', 'Ibadah tidak ditemukan.');
        }

        // Tentukan target (semua anggota aktif)
        $targetIds = $this->getActiveAnggotaUserIds();

        // Buat notifikasi
        $notificationData = [
            'judul' => 'Pengingat Ibadah: ' . $ibadah['nama_ibadah'],
            'pesan' => "Jangan lupa ibadah {$ibadah['nama_ibadah']} pada " . 
                      date('d/m/Y', strtotime($ibadah['tanggal'])) . 
                      " pukul " . date('H:i', strtotime($ibadah['jam_mulai'])) . 
                      " di {$ibadah['lokasi']}",
            'tipe' => 'ibadah',
            'prioritas' => 'high',
            'metadata' => json_encode([
                'ibadah_id' => $ibadahId,
                'tanggal' => $ibadah['tanggal'],
                'jam' => $ibadah['jam'],
                'tempat' => $ibadah['tempat'],
            ]),
            'status' => 'pending',
            'expired_at' => $ibadah['tanggal'] . ' ' . $ibadah['jam_mulai'],
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $notificationId = $this->notifikasiModel->insert($notificationData);

            // Buat notifikasi untuk setiap target
            $batchData = [];
            foreach ($targetIds as $userId) {
                $batchData[] = [
                    'notifikasi_id' => $notificationId,
                    'user_id' => $userId,
                    'status' => 'unread',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($batchData)) {
                $this->notifikasiModel->insertUserNotifications($batchData);
            }

            // Kirim notifikasi
            $this->sendImmediateNotifications($notificationId, $targetIds);

            $db->transComplete();

            return redirect()->back()->with('success', 
                'Notifikasi ibadah berhasil dikirim ke ' . count($targetIds) . ' anggota.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim Notifikasi Program Kerja
     */
    public function sendProgramNotification($programId)
    {
        $program = $this->programKerjaModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'Program kerja tidak ditemukan.');
        }

        // Tentukan target berdasarkan penanggung jawab dan tim
        $targetIds = [];
        
        // Tambah penanggung jawab
        if ($program['penanggung_jawab_id']) {
            $targetIds[] = $program['penanggung_jawab_id'];
        }

        // Tambah anggota tim jika ada
        if ($program['anggota_tim_ids']) {
            $timIds = json_decode($program['anggota_tim_ids'], true);
            $targetIds = array_merge($targetIds, $timIds);
        }

        $targetIds = array_unique($targetIds);

        // Buat notifikasi
        $notificationData = [
            'judul' => 'Update Program Kerja: ' . $program['nama_program'],
            'pesan' => "Program {$program['nama_program']} memiliki update baru. " . 
                      "Deadline: " . date('d/m/Y', strtotime($program['deadline'])) . 
                      " - Status: " . $this->getStatusLabel($program['status']),
            'tipe' => 'program',
            'prioritas' => $program['prioritas'],
            'metadata' => json_encode([
                'program_id' => $programId,
                'status' => $program['status'],
                'deadline' => $program['deadline'],
                'link' => base_url('program-kerja/detail/' . $programId)
            ]),
            'status' => 'pending',
            'jadwal_kirim' => date('Y-m-d H:i:s'),
            'expired_at' => $program['deadline'],
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $notificationId = $this->notifikasiModel->insert($notificationData);

            // Buat notifikasi untuk setiap target
            $batchData = [];
            foreach ($targetIds as $userId) {
                $batchData[] = [
                    'notifikasi_id' => $notificationId,
                    'user_id' => $userId,
                    'status' => 'unread',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($batchData)) {
                $this->notifikasiModel->insertUserNotifications($batchData);
            }

            // Kirim notifikasi
            $this->sendImmediateNotifications($notificationId, $targetIds);

            $db->transComplete();

            return redirect()->back()->with('success', 
                'Notifikasi program kerja berhasil dikirim ke ' . count($targetIds) . ' penerima.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim Notifikasi Pengingat
     */
    public function sendReminderNotification()
    {
        // Validasi
        $rules = [
            'reminder_title' => 'required|min_length[5]|max_length[200]',
            'reminder_message' => 'required|min_length[10]|max_length[500]',
            'reminder_date' => 'required|valid_date',
            'reminder_time' => 'required',
            'target_type' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $targetType = $this->request->getPost('target_type');
        $targetIds = [];

        switch ($targetType) {
            case 'all_anggota':
                $targetIds = $this->getActiveAnggotaUserIds();
                break;
                
            case 'pelayanan':
                $pelayananId = $this->request->getPost('pelayanan_id');
                if ($pelayananId) {
                    $targetIds = $this->userModel->getUserIdsByPelayanan([$pelayananId]);
                }
                break;
                
            case 'specific':
                $specificIds = $this->request->getPost('specific_user_ids');
                if ($specificIds) {
                    $targetIds = $specificIds;
                }
                break;
        }

        if (empty($targetIds)) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada target yang dipilih.');
        }

        $reminderDateTime = $this->request->getPost('reminder_date') . ' ' . $this->request->getPost('reminder_time');
        $jadwalKirim = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($reminderDateTime)));

        // Buat notifikasi
        $notificationData = [
            'judul' => $this->request->getPost('reminder_title'),
            'pesan' => $this->request->getPost('reminder_message'),
            'tipe' => 'reminder',
            'prioritas' => 'high',
            'metadata' => json_encode([
                'reminder_datetime' => $reminderDateTime,
                'location' => $this->request->getPost('reminder_location'),
                'link' => $this->request->getPost('reminder_link')
            ]),
            'status' => 'scheduled',
            'jadwal_kirim' => $jadwalKirim,
            'expired_at' => $reminderDateTime,
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $notificationId = $this->notifikasiModel->insert($notificationData);

            // Buat notifikasi untuk setiap target
            $batchData = [];
            foreach ($targetIds as $userId) {
                $batchData[] = [
                    'notifikasi_id' => $notificationId,
                    'user_id' => $userId,
                    'status' => 'unread',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($batchData)) {
                $this->notifikasiModel->insertUserNotifications($batchData);
            }

            $db->transComplete();

            return redirect()->to('/notifikasi')->with('success', 
                'Pengingat berhasil dijadwalkan untuk ' . count($targetIds) . ' penerima.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Proses Notifikasi Terjadwal
     */
    public function processScheduledNotifications()
    {
        // Endpoint ini biasanya dipanggil oleh cron job
        $scheduledNotifications = $this->notifikasiModel->getScheduledNotifications();

        $processed = 0;
        $errors = [];

        foreach ($scheduledNotifications as $notification) {
            try {
                $targetIds = json_decode($notification['target_ids'], true);
                
                if (empty($targetIds)) {
                    continue;
                }

                // Kirim notifikasi
                $this->sendImmediateNotifications($notification['id'], $targetIds);

                // Update status
                $this->notifikasiModel->update($notification['id'], [
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);

                $processed++;

            } catch (\Exception $e) {
                $errors[] = "Notifikasi ID {$notification['id']}: " . $e->getMessage();
            }
        }

        return $this->respond([
            'status' => 'success',
            'message' => "Diproses: $processed, Error: " . count($errors),
            'errors' => $errors
        ]);
    }

    /**
     * Kirim Notifikasi Mendesak (Emergency)
     */
    public function sendEmergencyNotification()
    {
        // Hanya admin dan emergency manager
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'super_admin', 'emergency_manager'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengirim notifikasi darurat.');
        }

        // Validasi
        $rules = [
            'emergency_title' => 'required|min_length[5]|max_length[200]',
            'emergency_message' => 'required|min_length[10]|max_length[1000]',
            'emergency_type' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Target: semua user aktif
        $targetIds = $this->getAllUserIds();

        // Buat notifikasi
        $notificationData = [
            'judul' => '[DARURAT] ' . $this->request->getPost('emergency_title'),
            'pesan' => $this->request->getPost('emergency_message'),
            'tipe' => 'emergency',
            'prioritas' => 'urgent',
            'metadata' => json_encode([
                'emergency_type' => $this->request->getPost('emergency_type'),
                'contact_person' => $this->request->getPost('contact_person'),
                'contact_number' => $this->request->getPost('contact_number')
            ]),
            'status' => 'pending',
            'jadwal_kirim' => date('Y-m-d H:i:s'),
            'expired_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'created_by' => session()->get('user_id'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $notificationId = $this->notifikasiModel->insert($notificationData);

            // Buat notifikasi untuk setiap target
            $batchData = [];
            foreach ($targetIds as $userId) {
                $batchData[] = [
                    'notifikasi_id' => $notificationId,
                    'user_id' => $userId,
                    'status' => 'unread',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($batchData)) {
                $this->notifikasiModel->insertUserNotifications($batchData);
            }

            // Kirim notifikasi segera + email + SMS jika perlu
            $this->sendEmergencyNotifications($notificationId, $targetIds);

            $db->transComplete();

            return redirect()->to('/notifikasi')->with('success', 
                'Notifikasi darurat berhasil dikirim ke ' . count($targetIds) . ' penerima.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Pengaturan Notifikasi User
     */
    public function userSettings()
    {
        $userId = session()->get('user_id');
        
        $data = [
            'title' => 'Pengaturan Notifikasi',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Pengaturan', 'url' => '/settings'],
                ['title' => 'Notifikasi', 'url' => '/notifikasi/settings']
            ],
            'userSettings' => $this->notifikasiModel->getUserNotificationSettings($userId),
            'notificationTypes' => $this->notifikasiModel->getNotificationTypes(),
            'channels' => ['web', 'email', 'push', 'sms'],
            'validation' => \Config\Services::validation()
        ];

        return view('notifikasi/settings', $data);
    }

    /**
     * Simpan Pengaturan Notifikasi User
     */
    public function saveUserSettings()
    {
        $userId = session()->get('user_id');
        
        $settings = [
            'enable_email' => $this->request->getPost('enable_email') ? 1 : 0,
            'enable_push' => $this->request->getPost('enable_push') ? 1 : 0,
            'quiet_hours_start' => $this->request->getPost('quiet_hours_start'),
            'quiet_hours_end' => $this->request->getPost('quiet_hours_end'),
            'notification_types' => json_encode($this->request->getPost('notification_types') ?? []),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->notifikasiModel->saveUserSettings($userId, $settings)) {
            return redirect()->back()->with('success', 'Pengaturan notifikasi berhasil disimpan.');
        }

        return redirect()->back()->with('error', 'Gagal menyimpan pengaturan notifikasi.');
    }

    /**
     * Helper Methods
     */
    /**
     * Kirim notifikasi darurat
     */
    private function sendEmergencyNotifications($notificationId, $userIds)
    {
        $notification = $this->notifikasiModel->find($notificationId);
        
        if (!$notification) {
            return false;
        }

        foreach ($userIds as $userId) {
            try {
                // Kirim semua channel untuk notifikasi darurat
                $this->sendWebNotification($userId, $notification);
                $this->sendEmailNotification($userId, $notification);
                $this->sendSMSNotification($userId, $notification);
                $this->sendPushNotification($userId, $notification);

            } catch (\Exception $e) {
                log_message('error', 'Gagal mengirim notifikasi darurat ke user ' . $userId . ': ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
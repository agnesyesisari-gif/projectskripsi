<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LogActivityModel;
use App\Libraries\ActivityLogger;

class LogActivity extends BaseController
{
    protected $logModel;
    protected $activityLogger;
    protected $helpers = ['auth', 'form', 'url'];

    public function __construct()
    {
        $this->logModel = new LogActivityModel();
        $this->activityLogger = new ActivityLogger();
        
        // Middleware: Hanya admin yang bisa mengakses
        if (!auth()->user() || !auth()->user()->inGroup('admin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    /**
     * Menampilkan daftar log activity
     */
    public function index()
    {
        $data = [
            'title' => 'Log Aktivitas Sistem',
            'subtitle' => 'Catatan Aktivitas Pengguna',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => '/admin'],
                ['name' => 'Log Aktivitas', 'url' => '/logactivity']
            ],
        ];

        // Pagination
        $perPage = $this->request->getGet('per_page') ?: 20;
        $currentPage = $this->request->getGet('page') ?: 1;
        
        // Filter
        $filters = [
            'user_id' => $this->request->getGet('user_id'),
            'activity_type' => $this->request->getGet('activity_type'),
            'module' => $this->request->getGet('module'),
        ];

        // Get data dengan filter dan pagination
        $logs = $this->logModel->getFilteredLogs($filters, $perPage, $currentPage);
        $total = $this->logModel->countFilteredLogs($filters);

        $data['logs'] = $logs;
        $data['pager'] = $this->logModel->pager;
        $data['total'] = $total;
        $data['current_page'] = $currentPage;
        $data['per_page'] = $perPage;
        $data['filters'] = $filters;

        // Stats untuk dashboard
        $data['stats'] = [
            'total_today' => $this->logModel->countTodayLogs(),
            'total_users' => $this->logModel->countActiveUsers(),
            'top_activities' => $this->logModel->getTopActivities(5),
        ];

        return view('admin/log_activity/index', $data);
    }

    /**
     * Menampilkan detail log activity
     */
    public function detail($id)
    {
        $log = $this->logModel->find($id);
        
        if (!$log) {
            session()->setFlashdata('error', 'Log aktivitas tidak ditemukan.');
            return redirect()->to('/logactivity');
        }

        $data = [
            'title' => 'Detail Log Aktivitas',
            'subtitle' => 'Rincian Aktivitas',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => '/admin'],
                ['name' => 'Log Aktivitas', 'url' => '/logactivity'],
                ['name' => 'Detail', 'url' => '']
            ],
            'log' => $log,
            'related_logs' => $this->logModel->getRelatedLogs($log['user_id'], $log['module'], 5),
        ];

        return view('admin/log_activity/detail', $data);
    }

    /**
     * API untuk mendapatkan log activity (JSON)
     */
    public function apiGetLogs()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Forbidden'
            ]);
        }

        $filters = [
            'user_id' => $this->request->getGet('user_id'),
            'activity_type' => $this->request->getGet('activity_type'),
            'module' => $this->request->getGet('module'),
        ];

        $perPage = $this->request->getGet('per_page') ?: 10;
        $page = $this->request->getGet('page') ?: 1;

        $logs = $this->logModel->getFilteredLogs($filters, $perPage, $page);
        $total = $this->logModel->countFilteredLogs($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $logs,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * Mendapatkan label untuk tipe aktivitas
     */
    private function getActivityTypeLabel($type)
    {
        $labels = [
            'create' => 'Tambah Data',
            'update' => 'Ubah Data',
            'delete' => 'Hapus Data',
            'login' => 'Login',
            'logout' => 'Logout',
            'view' => 'Lihat Data',
            'export' => 'Export Data',
            'approve' => 'Approve',
            'reject' => 'Reject',
            'download' => 'Download',
            'upload' => 'Upload',
        ];

        return $labels[$type] ?? $type;
    }

    /**
     * Dashboard statistics untuk log activity
     */
    public function dashboardStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Forbidden'
            ]);
        }

        $period = $this->request->getGet('period') ?: 'today';
        
        $stats = $this->logModel->getDashboardStats($period);

        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Search log activity
     */
    public function search()
    {
        $keyword = $this->request->getGet('q');
        
        if (empty($keyword)) {
            return redirect()->to('/logactivity');
        }

        $logs = $this->logModel->searchLogs($keyword, 20);

        $data = [
            'title' => 'Pencarian Log Aktivitas',
            'subtitle' => 'Hasil pencarian: ' . $keyword,
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => '/admin'],
                ['name' => 'Log Aktivitas', 'url' => '/logactivity'],
                ['name' => 'Pencarian', 'url' => '']
            ],
            'logs' => $logs,
            'keyword' => $keyword,
            'total' => count($logs),
        ];

        return view('admin/log_activity/search', $data);
    }
}
<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ApprovalModel;
use App\Models\IbadahModel;
use App\Models\ProgramKerjaModel;
use App\Models\AnggotaModel;
use App\Models\NotificationModel;
use CodeIgniter\API\ResponseTrait;

class ApprovalController extends BaseController
{
    use ResponseTrait;

    protected $approvalModel;
    protected $ibadahModel;
    protected $programKerjaModel;
    protected $anggotaModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->approvalModel = new ApprovalModel();
        $this->ibadahModel = new IbadahModel();
        $this->programKerjaModel = new ProgramKerjaModel();
        $this->anggotaModel = new AnggotaModel();
        $this->notificationModel = new NotificationModel();
        
        // Middleware untuk autentikasi dan hak akses
        // $this->middleware = ['auth', 'permission:manage_approvals'];
    }

    /**
     * Dashboard approval untuk administrator
     */
    public function index()
    {
        $userRole = session()->get('role');
        $userId = session()->get('user_id');
        
        $data = [
            'title' => 'Dashboard Persetujuan',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Persetujuan', 'url' => '/approval']
            ],
            'pendingCount' => $this->approvalModel->getPendingCount($userRole, $userId),
            'pendingIbadah' => $this->approvalModel->getPendingIbadah($userRole, $userId),
            'pendingProgram' => $this->approvalModel->getPendingProgram($userRole, $userId),
            'recentApprovals' => $this->approvalModel->getRecentApprovals(10),
            'stats' => $this->approvalModel->getApprovalStats($userRole, $userId),
            'userRole' => $userRole
        ];

        return view('admin/approval/dashboard', $data);
    }

    /**
     * Daftar semua permintaan persetujuan berdasarkan tipe
     */
    public function listRequests($type = null)
    {
        $userRole = session()->get('role');
        $userId = session()->get('user_id');
        $status = $this->request->getGet('status');
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 20;

        $filters = [
            'type' => $type,
            'status' => $status,
            'user_role' => $userRole,
            'user_id' => $userId
        ];

        $requests = $this->approvalModel->getApprovalRequests($filters, $perPage, $page);
        $pager = $this->approvalModel->pager;

        $data = [
            'title' => 'Permintaan Persetujuan',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Persetujuan', 'url' => '/approval'],
                ['title' => 'Daftar Permintaan', 'url' => '/approval/list']
            ],
            'requests' => $requests,
            'pager' => $pager,
            'type' => $type,
            'status' => $status,
            'filters' => $filters,
            'types' => [
                'ibadah' => 'Jadwal Ibadah',
                'program' => 'Program Kerja',
                'kegiatan' => 'Kegiatan',
                'anggaran' => 'Pengajuan Anggaran',
                'lainnya' => 'Lainnya'
            ],
            'statuses' => [
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'revised' => 'Perlu Revisi'
            ]
        ];

        return view('admin/approval/list', $data);
    }

    /**
     * Detail permintaan persetujuan
     */
    public function show($id)
    {
        $approval = $this->approvalModel->getApprovalWithDetails($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->isAuthorized($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melihat permintaan ini.');
        }

        $data = [
            'title' => 'Detail Persetujuan: ' . $approval['title'],
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Persetujuan', 'url' => '/approval'],
                ['title' => 'Detail', 'url' => '/approval/show/' . $id]
            ],
            'approval' => $approval,
            'approvalHistory' => $this->approvalModel->getApprovalHistory($id),
            'relatedDocuments' => $this->getRelatedDocuments($approval),
            'canApprove' => $this->canApprove($approval),
            'canReject' => $this->canReject($approval),
            'canRevise' => $this->canRevise($approval)
        ];

        return view('admin/approval/show', $data);
    }

    /**
     * Form untuk memberikan persetujuan
     */
    public function approveForm($id)
    {
        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->canApprove($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menyetujui permintaan ini.');
        }

        $data = [
            'title' => 'Form Persetujuan',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Persetujuan', 'url' => '/approval'],
                ['title' => 'Detail', 'url' => '/approval/show/' . $id],
                ['title' => 'Setujui', 'url' => '/approval/approve/' . $id]
            ],
            'approval' => $approval,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/approval/approve_form', $data);
    }

    /**
     * Proses persetujuan
     */
    public function approve($id)
    {
        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->canApprove($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menyetujui permintaan ini.');
        }

        // Validasi
        $rules = [
            'approval_notes' => 'permit_empty|max_length[500]',
            'effective_date' => 'permit_empty|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $approverId = session()->get('user_id');
        $approverName = session()->get('fullname');
        $notes = $this->request->getPost('approval_notes');
        $effectiveDate = $this->request->getPost('effective_date');

        // Mulai transaksi database
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update status approval
            $approvalData = [
                'status' => 'approved',
                'approved_by' => $approverId,
                'approved_at' => date('Y-m-d H:i:s'),
                'approval_notes' => $notes,
                'effective_date' => $effectiveDate ?: null,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->approvalModel->update($id, $approvalData);

            // Update status data terkait berdasarkan tipe
            $this->updateRelatedData($approval, 'approved');

            // Tambahkan ke history
            $historyData = [
                'approval_id' => $id,
                'action' => 'approved',
                'action_by' => $approverId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->approvalModel->addHistory($historyData);

            // Kirim notifikasi
            $this->sendNotification($approval, 'approved', $approverName, $notes);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Gagal menyimpan persetujuan.');
            }

            return redirect()->to('/approval/show/' . $id)->with('success', 'Permintaan berhasil disetujui.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Form untuk penolakan
     */
    public function rejectForm($id)
    {
        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->canReject($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menolak permintaan ini.');
        }

        $data = [
            'title' => 'Form Penolakan',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Persetujuan', 'url' => '/approval'],
                ['title' => 'Detail', 'url' => '/approval/show/' . $id],
                ['title' => 'Tolak', 'url' => '/approval/reject/' . $id]
            ],
            'approval' => $approval,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/approval/reject_form', $data);
    }

    /**
     * Proses penolakan
     */
    public function reject($id)
    {
        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->canReject($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menolak permintaan ini.');
        }

        // Validasi
        $rules = [
            'rejection_reason' => 'required|min_length[10]|max_length[500]'
        ];

        $messages = [
            'rejection_reason' => [
                'required' => 'Alasan penolakan wajib diisi',
                'min_length' => 'Alasan penolakan minimal 10 karakter',
                'max_length' => 'Alasan penolakan maksimal 500 karakter'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $approverId = session()->get('user_id');
        $approverName = session()->get('fullname');
        $reason = $this->request->getPost('rejection_reason');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update status approval
            $approvalData = [
                'status' => 'rejected',
                'rejected_by' => $approverId,
                'rejected_at' => date('Y-m-d H:i:s'),
                'rejection_reason' => $reason,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->approvalModel->update($id, $approvalData);

            // Update status data terkait
            $this->updateRelatedData($approval, 'rejected');

            // Tambahkan ke history
            $historyData = [
                'approval_id' => $id,
                'action' => 'rejected',
                'action_by' => $approverId,
                'notes' => $reason,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->approvalModel->addHistory($historyData);

            // Kirim notifikasi
            $this->sendNotification($approval, 'rejected', $approverName, $reason);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Gagal menyimpan penolakan.');
            }

            return redirect()->to('/approval/show/' . $id)->with('success', 'Permintaan berhasil ditolak.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Form untuk permintaan revisi
     */
    public function reviseForm($id)
    {
        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->canRevise($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk meminta revisi.');
        }

        $data = [
            'title' => 'Form Permintaan Revisi',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Persetujuan', 'url' => '/approval'],
                ['title' => 'Detail', 'url' => '/approval/show/' . $id],
                ['title' => 'Revisi', 'url' => '/approval/revise/' . $id]
            ],
            'approval' => $approval,
            'validation' => \Config\Services::validation()
        ];

        return view('admin/approval/revise_form', $data);
    }

    /**
     * Proses permintaan revisi
     */
    public function revise($id)
    {
        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return redirect()->to('/approval')->with('error', 'Permintaan persetujuan tidak ditemukan.');
        }

        // Cek otorisasi
        if (!$this->canRevise($approval)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk meminta revisi.');
        }

        // Validasi
        $rules = [
            'revision_notes' => 'required|min_length[10]|max_length[500]',
            'deadline_date' => 'required|valid_date'
        ];

        $messages = [
            'revision_notes' => [
                'required' => 'Catatan revisi wajib diisi',
                'min_length' => 'Catatan revisi minimal 10 karakter',
                'max_length' => 'Catatan revisi maksimal 500 karakter'
            ],
            'deadline_date' => [
                'required' => 'Tanggal deadline revisi wajib diisi',
                'valid_date' => 'Format tanggal tidak valid'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $approverId = session()->get('user_id');
        $approverName = session()->get('fullname');
        $notes = $this->request->getPost('revision_notes');
        $deadline = $this->request->getPost('deadline_date');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update status approval
            $approvalData = [
                'status' => 'revised',
                'revised_by' => $approverId,
                'revised_at' => date('Y-m-d H:i:s'),
                'revision_notes' => $notes,
                'revision_deadline' => $deadline,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->approvalModel->update($id, $approvalData);

            // Tambahkan ke history
            $historyData = [
                'approval_id' => $id,
                'action' => 'revised',
                'action_by' => $approverId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->approvalModel->addHistory($historyData);

            // Kirim notifikasi
            $this->sendNotification($approval, 'revised', $approverName, $notes);

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Gagal menyimpan permintaan revisi.');
            }

            return redirect()->to('/approval/show/' . $id)->with('success', 'Permintaan revisi berhasil dikirim.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * API untuk mendapatkan data approval
     */
    public function apiList()
    {
        $type = $this->request->getGet('type');
        $status = $this->request->getGet('status');
        $userRole = session()->get('role');
        $userId = session()->get('user_id');

        $filters = [
            'type' => $type,
            'status' => $status,
            'user_role' => $userRole,
            'user_id' => $userId
        ];

        $requests = $this->approvalModel->getApprovalRequests($filters, 50, 1);

        return $this->respond([
            'status' => 'success',
            'data' => $requests,
            'total' => count($requests)
        ]);
    }

    /**
     * API untuk mendapatkan statistik approval
     */
    public function apiStats()
    {
        $userRole = session()->get('role');
        $userId = session()->get('user_id');
        
        $stats = $this->approvalModel->getApprovalStats($userRole, $userId);

        return $this->respond([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * API untuk aksi cepat (quick action)
     */
    public function apiQuickAction()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $id = $this->request->getPost('id');
        $action = $this->request->getPost('action');
        $notes = $this->request->getPost('notes');

        $approval = $this->approvalModel->find($id);
        
        if (!$approval) {
            return $this->failNotFound('Permintaan tidak ditemukan.');
        }

        // Cek otorisasi berdasarkan action
        switch ($action) {
            case 'approve':
                if (!$this->canApprove($approval)) {
                    return $this->failForbidden('Tidak memiliki izin untuk menyetujui.');
                }
                break;
            case 'reject':
                if (!$this->canReject($approval)) {
                    return $this->failForbidden('Tidak memiliki izin untuk menolak.');
                }
                break;
            default:
                return $this->fail('Aksi tidak valid.');
        }

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $updateData = [
                'status' => $action == 'approve' ? 'approved' : 'rejected',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($action == 'approve') {
                $updateData['approved_by'] = session()->get('user_id');
                $updateData['approved_at'] = date('Y-m-d H:i:s');
                $updateData['approval_notes'] = $notes;
            } else {
                $updateData['rejected_by'] = session()->get('user_id');
                $updateData['rejected_at'] = date('Y-m-d H:i:s');
                $updateData['rejection_reason'] = $notes;
            }

            $this->approvalModel->update($id, $updateData);
            $this->updateRelatedData($approval, $action == 'approve' ? 'approved' : 'rejected');

            // History
            $historyData = [
                'approval_id' => $id,
                'action' => $action == 'approve' ? 'approved' : 'rejected',
                'action_by' => session()->get('user_id'),
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->approvalModel->addHistory($historyData);

            // Notifikasi
            $this->sendNotification($approval, 
                $action == 'approve' ? 'approved' : 'rejected',
                session()->get('fullname'),
                $notes
            );

            $db->transComplete();

            return $this->respond([
                'status' => 'success',
                'message' => 'Aksi berhasil dilakukan',
                'data' => ['id' => $id, 'action' => $action]
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->failServerError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Export data approval ke Excel
     */
    public function export()
    {
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $type = $this->request->getGet('type');
        $status = $this->request->getGet('status');

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'type' => $type,
            'status' => $status
        ];

        $approvals = $this->approvalModel->getExportData($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'LAPORAN PERSETUJUAN');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A2', 'Periode: ' . ($startDate ?? '-') . ' s/d ' . ($endDate ?? '-'));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Header tabel
        $headers = [
            'No', 'ID', 'Judul', 'Tipe', 'Pengaju', 'Status',
            'Tanggal Diajukan', 'Tanggal Disetujui/Ditolak', 'Catatan'
        ];

        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '4', $header);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }

        // Data
        $row = 5;
        $no = 1;
        
        foreach ($approvals as $approval) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $approval['id']);
            $sheet->setCellValue('C' . $row, $approval['title']);
            $sheet->setCellValue('D' . $row, $this->getTypeLabel($approval['type']));
            $sheet->setCellValue('E' . $row, $approval['requester_name']);
            $sheet->setCellValue('F' . $row, $this->getStatusLabel($approval['status']));
            $sheet->setCellValue('G' . $row, $approval['created_at']);
            $sheet->setCellValue('H' . $row, $approval['approved_at'] ?? $approval['rejected_at'] ?? '-');
            $sheet->setCellValue('I' . $row, $approval['approval_notes'] ?? $approval['rejection_reason'] ?? '-');
            
            $row++;
        }

        // Styling
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:I' . ($row-1))->applyFromArray($styleArray);

        // Write file
        $writer = new Xlsx($spreadsheet);
        $filename = 'approval_report_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Helper method untuk cek otorisasi
     */
    private function isAuthorized($approval)
    {
        $userRole = session()->get('role');
        $userId = session()->get('user_id');

        // Administrator memiliki akses ke semua
        if ($userRole == 'admin' || $userRole == 'super_admin') {
            return true;
        }

        // Penatua bisa melihat yang terkait wilayahnya
        if ($userRole == 'penatua') {
            // Logika berdasarkan wilayah atau departemen
            return $this->checkRegionalAccess($approval, $userId);
        }

        // Pengaju bisa melihat miliknya sendiri
        if ($approval['requester_id'] == $userId) {
            return true;
        }

        // Anggota tim approval
        if (in_array($userId, explode(',', $approval['approval_team'] ?? ''))) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa menyetujui
     */
    private function canApprove($approval)
    {
        $userRole = session()->get('role');
        $userId = session()->get('user_id');

        // Hanya status pending yang bisa disetujui
        if ($approval['status'] != 'pending') {
            return false;
        }

        // Administrator bisa menyetujui semua
        if ($userRole == 'admin' || $userRole == 'super_admin') {
            return true;
        }

        // Cek berdasarkan hierarki approval
        $approvalLevel = $this->getUserApprovalLevel($userRole, $userId);
        $requiredLevel = $approval['required_approval_level'] ?? 1;

        return $approvalLevel >= $requiredLevel;
    }

    /**
     * Cek apakah user bisa menolak
     */
    private function canReject($approval)
    {
        // Sama dengan canApprove untuk sekarang
        return $this->canApprove($approval);
    }

    /**
     * Cek apakah user bisa meminta revisi
     */
    private function canRevise($approval)
    {
        return $this->canApprove($approval);
    }

    /**
     * Update data terkait berdasarkan status approval
     */
    private function updateRelatedData($approval, $status)
    {
        switch ($approval['type']) {
            case 'ibadah':
                $this->ibadahModel->update($approval['related_id'], [
                    'approval_status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                break;

            case 'program':
                $this->programKerjaModel->update($approval['related_id'], [
                    'approval_status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                break;

            // Tambahkan tipe lainnya sesuai kebutuhan
        }
    }

    /**
     * Kirim notifikasi
     */
    private function sendNotification($approval, $action, $actorName, $notes = null)
    {
        $notificationData = [
            'user_id' => $approval['requester_id'],
            'title' => 'Status Persetujuan Diperbarui',
            'message' => $this->getNotificationMessage($approval, $action, $actorName, $notes),
            'type' => 'approval',
            'related_id' => $approval['id'],
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->notificationModel->insert($notificationData);

        // Juga kirim email jika perlu
        $this->sendEmailNotification($approval, $action, $actorName, $notes);
    }

    /**
     * Buat pesan notifikasi
     */
    private function getNotificationMessage($approval, $action, $actorName, $notes)
    {
        $actionLabels = [
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            'revised' => 'memerlukan revisi'
        ];

        $typeLabels = [
            'ibadah' => 'jadwal ibadah',
            'program' => 'program kerja',
            'kegiatan' => 'kegiatan'
        ];

        $message = "Pengajuan {$typeLabels[$approval['type']] ?? $approval['type']} \"{$approval['title']}\" ";
        $message .= "telah {$actionLabels[$action]} oleh {$actorName}.";

        if ($notes) {
            $message .= " Catatan: {$notes}";
        }

        return $message;
    }

    /**
     * Kirim email notifikasi
     */
    private function sendEmailNotification($approval, $action, $actorName, $notes)
    {
        // Implementasi email (gunakan library email CodeIgniter)
        // Contoh sederhana:
        /*
        $email = \Config\Services::email();
        $requester = $this->anggotaModel->find($approval['requester_id']);
        
        if ($requester && $requester['email']) {
            $email->setTo($requester['email']);
            $email->setSubject('Update Status Persetujuan');
            $email->setMessage($this->getNotificationMessage($approval, $action, $actorName, $notes));
            $email->send();
        }
        */
    }

    /**
     * Dapatkan dokumen terkait
     */
    private function getRelatedDocuments($approval)
    {
        // Implementasi untuk mendapatkan dokumen terkait
        // Misalnya: proposal, budget, dll.
        return [];
    }

    /**
     * Cek akses berdasarkan wilayah
     */
    private function checkRegionalAccess($approval, $userId)
    {
        // Implementasi logika berdasarkan wilayah
        // Misalnya: penatua hanya bisa akses approval di wilayahnya
        return true; // Sementara return true
    }

    /**
     * Dapatkan level approval user
     */
    private function getUserApprovalLevel($userRole, $userId)
    {
        $levels = [
            'super_admin' => 5,
            'admin' => 4,
            'penatua' => 3,
            'diaken' => 2,
            'koordinator' => 1,
            'anggota' => 0
        ];

        return $levels[$userRole] ?? 0;
    }

    /**
     * Get label untuk tipe
     */
    private function getTypeLabel($type)
    {
        $types = [
            'ibadah' => 'Jadwal Ibadah',
            'program' => 'Program Kerja',
            'kegiatan' => 'Kegiatan',
            'anggaran' => 'Pengajuan Anggaran',
            'lainnya' => 'Lainnya'
        ];

        return $types[$type] ?? $type;
    }

    /**
     * Get label untuk status
     */
    private function getStatusLabel($status)
    {
        $statuses = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revised' => 'Perlu Revisi'
        ];

        return $statuses[$status] ?? $status;
    }
}
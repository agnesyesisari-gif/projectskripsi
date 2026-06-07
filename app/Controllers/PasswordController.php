<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PasswordHistoryModel;
use App\Models\PasswordResetModel;
use App\Libraries\PasswordPolicy;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Email\Email;

class PasswordController extends BaseController
{
    use ResponseTrait;

    protected $userModel;
    protected $passwordHistoryModel;
    protected $passwordResetModel;
    protected $passwordPolicy;
    protected $email;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->passwordHistoryModel = new PasswordHistoryModel();
        $this->passwordResetModel = new PasswordResetModel();
        $this->passwordPolicy = new PasswordPolicy();
        $this->email = \Config\Services::email();
        
        helper(['form', 'url', 'session']);
    }

    /**
     * Form Ganti Password
     */
    public function changePassword()
    {
        // Cek jika user sudah login
        if (!session()->has('user_id')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Ganti Password',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Profil', 'url' => '/profile'],
                ['title' => 'Ganti Password', 'url' => '/password/change']
            ],
            'user' => $user,
            'passwordPolicy' => $this->passwordPolicy->getPolicy(),
            'lastChanged' => $this->passwordHistoryModel->getLastChange($userId),
            'validation' => \Config\Services::validation()
        ];

        return view('password/change', $data);
    }

    /**
     * Proses Ganti Password
     */
    public function processChangePassword()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Method tidak diizinkan.');
        }

        $userId = session()->get('user_id');
        
        if (!$userId) {
            return $this->failUnauthorized('Silakan login terlebih dahulu.');
        }

        // Validasi input
        $rules = [
            'current_password' => 'required',
            'new_password' => [
                'rules' => 'required|min_length[8]|max_length[32]',
                'errors' => [
                    'min_length' => 'Password minimal 8 karakter',
                    'max_length' => 'Password maksimal 32 karakter'
                ]
            ],
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Dapatkan data user
        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->failNotFound('User tidak ditemukan.');
        }

        // Verifikasi password saat ini
        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return $this->fail('Password saat ini salah.', 400);
        }

        // Validasi policy password
        $newPassword = $this->request->getPost('new_password');
        $policyCheck = $this->passwordPolicy->validatePassword($newPassword, $userId);
        
        if (!$policyCheck['valid']) {
            return $this->fail($policyCheck['message'], 400);
        }

        // Cek apakah password sama dengan yang lama
        if (password_verify($newPassword, $user['password'])) {
            return $this->fail('Password baru tidak boleh sama dengan password lama.', 400);
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Mulai transaksi
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update password di tabel users
            $updateData = [
                'password' => $hashedPassword,
                'password_changed_at' => date('Y-m-d H:i:s'),
                'password_expires_at' => date('Y-m-d H:i:s', strtotime('+90 days')),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->userModel->update($userId, $updateData);

            // Simpan ke history
            $historyData = [
                'user_id' => $userId,
                'password_hash' => $hashedPassword,
                'changed_at' => date('Y-m-d H:i:s'),
                'changed_by' => $userId,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ];

            $this->passwordHistoryModel->insert($historyData);

            // Kirim notifikasi email
            $this->sendPasswordChangeNotification($user, $this->request->getIPAddress());

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Gagal mengubah password.');
            }

        } catch (\Exception $e) {
            $db->transRollback();
            return $this->failServerError('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Form Lupa Password
     */
    public function forgotPassword()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Lupa Password',
            'breadcrumb' => [
                ['title' => 'Login', 'url' => '/login'],
                ['title' => 'Lupa Password', 'url' => '/password/forgot']
            ],
            'validation' => \Config\Services::validation()
        ];

        return view('password/forgot', $data);
    }

    /**
     * Proses Permintaan Reset Password
     */
    public function processForgotPassword()
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user = $this->userModel->where('email', $email)->first();

        // Selalu beri respons yang sama untuk mencegah email enumeration
        if (!$user) {
            // Tetap tampilkan sukses untuk keamanan
            return redirect()->to('/password/forgot')->with('success', 
                'Jika email terdaftar, kami telah mengirim instruksi reset password.');
        }

        return redirect()->to('/password/forgot')->with('success', 
            'Jika email terdaftar, kami telah mengirim instruksi reset password.');
    }

    /**
     * Proses Reset Password
     */
    public function processResetPassword()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => [
                'rules' => 'required|min_length[8]|max_length[32]',
                'errors' => [
                    'min_length' => 'Password minimal 8 karakter',
                    'max_length' => 'Password maksimal 32 karakter'
                ]
            ],
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $newPassword = $this->request->getPost('password');

        // Verifikasi email
        $user = $this->userModel->where('email', $email)->first();
        if (!$user || $user['id'] != $resetRequest['user_id']) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid.');
        }

        // Validasi policy password
        $policyCheck = $this->passwordPolicy->validatePassword($newPassword, $user['id']);
        if (!$policyCheck['valid']) {
            return redirect()->back()->withInput()->with('error', $policyCheck['message']);
        }

        // Mulai transaksi
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $updateData = [
                'password' => $hashedPassword,
                'password_changed_at' => date('Y-m-d H:i:s'),
                'password_expires_at' => date('Y-m-d H:i:s', strtotime('+90 days')),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->userModel->update($user['id'], $updateData);

            // Simpan ke history
            $historyData = [
                'user_id' => $user['id'],
                'password_hash' => $hashedPassword,
                'changed_at' => date('Y-m-d H:i:s'),
                'changed_by' => $user['id'],
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString()
            ];

            $this->passwordHistoryModel->insert($historyData);

            // Kirim notifikasi
            $this->sendPasswordResetNotification($user, $this->request->getIPAddress());

            $db->transComplete();

            if ($db->transStatus() === FALSE) {
                throw new \Exception('Gagal reset password.');
            }

            return redirect()->to('/login')->with('success', 
                'Password berhasil direset. Silakan login dengan password baru.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Validasi Kekuatan Password (API)
     */
    public function validatePasswordStrength()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Method not allowed', 405);
        }

        $password = $this->request->getPost('password');
        $userId = session()->get('user_id') ?? 0;

        $result = $this->passwordPolicy->validatePassword($password, $userId);

        return $this->respond([
            'status' => $result['valid'] ? 'success' : 'error',
            'valid' => $result['valid'],
            'score' => $result['score'],
            'strength' => $result['strength'],
            'message' => $result['message'],
            'requirements' => $result['requirements']
        ]);
    }

    /**
     * Force Change Password (untuk admin)
     */
    public function forceChangePassword($userId = null)
    {
        // Hanya admin yang bisa akses
        if (session()->get('role') != 'admin' && session()->get('role') != 'super_admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if (!$userId) {
            return redirect()->back()->with('error', 'User ID diperlukan.');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        // Generate temporary password
        $tempPassword = $this->generateTemporaryPassword();
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update password
            $updateData = [
                'password' => $hashedPassword,
                'password_changed_at' => date('Y-m-d H:i:s'),
                'password_expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')), // Expire dalam 1 hari
                'force_password_change' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->userModel->update($userId, $updateData);

            // Simpan ke history
            $historyData = [
                'user_id' => $userId,
                'password_hash' => $hashedPassword,
                'changed_at' => date('Y-m-d H:i:s'),
                'changed_by' => session()->get('user_id'),
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'is_forced_change' => 1
            ];

            $this->passwordHistoryModel->insert($historyData);

            // Kirim email dengan password sementara
            $this->sendTemporaryPasswordEmail($user, $tempPassword);

            $db->transComplete();

            return redirect()->back()->with('success', 
                'Password berhasil direset. Password sementara telah dikirim ke email user.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * History Perubahan Password
     */
    public function passwordHistory($userId = null)
    {
        // User hanya bisa melihat history sendiri, admin bisa melihat semua
        $currentUserId = session()->get('user_id');
        $currentUserRole = session()->get('role');
        
        if (!$userId) {
            $userId = $currentUserId;
        } elseif ($userId != $currentUserId && 
                 $currentUserRole != 'admin' && 
                 $currentUserRole != 'super_admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 20;

        $history = $this->passwordHistoryModel->getUserHistory($userId, $perPage, $page);
        $pager = $this->passwordHistoryModel->pager;

        $data = [
            'title' => 'History Perubahan Password',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/dashboard'],
                ['title' => 'Pengaturan', 'url' => '/settings'],
                ['title' => 'History Password', 'url' => '/password/history']
            ],
            'history' => $history,
            'pager' => $pager,
            'user' => $user,
            'currentUserId' => $currentUserId,
            'canViewAll' => ($currentUserRole == 'admin' || $currentUserRole == 'super_admin')
        ];

        return view('password/history', $data);
    }

    /**
     * Generate Temporary Password
     */
    private function generateTemporaryPassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }

    /**
     * Send Password Change Notification Email
     */
    private function sendPasswordChangeNotification($user, $ipAddress)
    {
        $data = [
            'to' => $user['email'],
            'subject' => 'Notifikasi Perubahan Password - ' . getenv('app.name'),
            'message' => view('emails/password_changed', [
                'user' => $user,
                'ipAddress' => $ipAddress,
                'time' => date('d/m/Y H:i:s'),
                'browser' => $this->request->getUserAgent()->getBrowser(),
                'platform' => $this->request->getUserAgent()->getPlatform()
            ])
        ];

        $this->sendEmail($data);
    }

    /**
     * Send Reset Password Email
     */
    private function sendResetPasswordEmail($user, $token)
    {
        $resetLink = base_url('password/reset/' . $token);

        $data = [
            'to' => $user['email'],
            'subject' => 'Reset Password - ' . getenv('app.name'),
            'message' => view('emails/password_reset', [
                'user' => $user,
                'resetLink' => $resetLink,
                'expiryTime' => '1 jam'
            ])
        ];

        $this->sendEmail($data);
    }

    /**
     * Send Password Reset Notification Email
     */
    private function sendPasswordResetNotification($user, $ipAddress)
    {
        $data = [
            'to' => $user['email'],
            'subject' => 'Password Berhasil Direset - ' . getenv('app.name'),
            'message' => view('emails/password_reset_success', [
                'user' => $user,
                'ipAddress' => $ipAddress,
                'time' => date('d/m/Y H:i:s')
            ])
        ];

        $this->sendEmail($data);
    }

    /**
     * Send Temporary Password Email
     */
    private function sendTemporaryPasswordEmail($user, $tempPassword)
    {
        $data = [
            'to' => $user['email'],
            'subject' => 'Password Sementara - ' . getenv('app.name'),
            'message' => view('emails/temporary_password', [
                'user' => $user,
                'tempPassword' => $tempPassword,
                'loginLink' => base_url('login')
            ])
        ];

        $this->sendEmail($data);
    }

    /**
     * Helper untuk mengirim email
     */
    private function sendEmail($data)
    {
        try {
            $this->email->setTo($data['to']);
            $this->email->setSubject($data['subject']);
            $this->email->setMessage($data['message']);
            
            if (!$this->email->send()) {
                log_message('error', 'Gagal mengirim email: ' . $this->email->printDebugger(['headers']));
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * API untuk cek expired password
     */
    public function apiCheckExpiry()
    {
        if (!session()->has('user_id')) {
            return $this->failUnauthorized('Silakan login terlebih dahulu.');
        }

        $userId = session()->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('User tidak ditemukan.');
        }

        $expiryDate = strtotime($user['password_expires_at'] ?? date('Y-m-d H:i:s'));
        $currentDate = time();
        $daysRemaining = floor(($expiryDate - $currentDate) / (60 * 60 * 24));

        $response = [
            'expired' => $daysRemaining <= 0,
            'days_remaining' => $daysRemaining,
            'expiry_date' => $user['password_expires_at'],
            'force_change' => $user['force_password_change'] ?? 0
        ];

        return $this->respond([
            'status' => 'success',
            'data' => $response
        ]);
    }

    /**
     * Middleware untuk check password expiry
     */
    public function passwordExpiryMiddleware()
    {
        $this->checkPasswordExpiry();
    }
}
<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LogActivityModel;
use App\Libraries\AuthLib;

class AuthController extends BaseController
{
    protected $userModel;
    protected $logModel;
    protected $authLib;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logModel = new LogActivityModel();
        $this->authLib = new AuthLib();
        $this->session = \Config\Services::session();
        
        helper(['form', 'url', 'security']);
    }

    /**
     * Halaman Login
     */
    public function login()
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->authLib->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Login - Sistem Informasi Gereja',
            'config' => config('App'),
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Proses Login
     */
    public function processLogin()
    {
        if (!$this->validate($this->getLoginRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Cari user berdasarkan email
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            // Log aktivitas gagal login
            $this->logAktivitas('LOGIN_GAGAL', 'User tidak ditemukan', $email);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password salah');
        }

        // Verifikasi password
        if (!password_verify($password, $user['password'])) {
            // Log aktivitas gagal login
            $this->logAktivitas('LOGIN_GAGAL', 'Password salah', $user['id']);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau password salah');
        }

        // Cek status akun
        if ($user['status'] !== 'active') {
            $statusMessage = $user['status'] === 'inactive' ? 'Akun belum diaktifkan' : 'Akun diblokir';
            
            $this->logAktivitas('LOGIN_GAGAL', 'Akun ' . $user['status'], $user['id']);
            
            return redirect()->back()
                ->withInput()
                ->with('error', $statusMessage . '. Hubungi administrator.');
        }

        // Set session data
        $sessionData = [
            'user_id'       => $user['id'],
            'username'      => $user['username'],
            'email'         => $user['email'],
            'nama_lengkap'  => $user['nama_lengkap'],
            'role'          => $user['role'],
            'jabatan_gereja'=> $user['jabatan_gereja'],
            'logged_in'     => true
        ];

        $this->session->set($sessionData);

        // Jika remember me dicentang
        if ($remember) {
            $this->setRememberMe($user['id']);
        }

        // Update last login
        $this->userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        // Log aktivitas berhasil login
        $this->logAktivitas('LOGIN', 'Login berhasil', $user['id']);

        // Redirect berdasarkan role
        return $this->redirectByRole($user['role']);
    }

    /**
     * Halaman Register
     */
    public function register()
    {
        // Jika sudah login, redirect ke dashboard
        if ($this->authLib->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Registrasi - Sistem Informasi Gereja',
            'config' => config('App'),
            'validation' => \Config\Services::validation()
        ];

        return view('auth/register', $data);
    }

    /**
     * Proses Registrasi
     */
    public function processRegister()
    {
        if (!$this->validate($this->getRegisterRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Generate kode aktivasi
        $activationCode = bin2hex(random_bytes(16));

        $userData = [
            'username'      => $this->request->getPost('username'),
            'email'         => $this->request->getPost('email'),
            'password'      => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'nama_lengkap'  => $this->request->getPost('nama_lengkap'),
            'telepon'       => $this->request->getPost('telepon'),
            'alamat'        => $this->request->getPost('alamat'),
            'jabatan_gereja'=> $this->request->getPost('jabatan_gereja'),
            'role'          => 'jemaat', // Default role
            'status'        => 'inactive', // Menunggu aktivasi
            'activation_code' => $activationCode,
            'created_at'    => date('Y-m-d H:i:s')
        ];

        try {
            // Simpan user
            $userId = $this->userModel->insert($userData);
            
            // Kirim email aktivasi
            $this->sendActivationEmail($userData['email'], $userData['nama_lengkap'], $activationCode);
            
            // Log aktivitas
            $this->logAktivitas('REGISTRASI', 'Registrasi berhasil', $userId);
            
            return redirect()->to('/login')
                ->with('success', 'Registrasi berhasil! Silakan cek email untuk aktivasi akun.');
                
        } catch (\Exception $e) {
            // Log error
            log_message('error', 'Registrasi gagal: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Registrasi gagal. Silakan coba lagi.');
        }
    }

    /**
     * Aktivasi Akun
     */
    public function activate($code = null)
    {
        if (empty($code)) {
            return redirect()->to('/login')->with('error', 'Kode aktivasi tidak valid');
        }

        $user = $this->userModel->where('activation_code', $code)->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Kode aktivasi tidak valid atau sudah digunakan');
        }

        // Aktifkan akun
        $this->userModel->update($user['id'], [
            'status' => 'active',
            'activation_code' => null,
            'activated_at' => date('Y-m-d H:i:s')
        ]);

        // Log aktivitas
        $this->logAktivitas('AKTIVASI', 'Aktivasi akun berhasil', $user['id']);

        return redirect()->to('/login')
            ->with('success', 'Akun berhasil diaktifkan! Silakan login.');
    }

    /**
     * Halaman Forgot Password
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Lupa Password - Sistem Informasi Gereja',
            'config' => config('App'),
            'validation' => \Config\Services::validation()
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Proses Forgot Password
     */
    public function processForgotPassword()
    {
        if (!$this->validate([
            'email' => 'required|valid_email'
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Simpan token
            $this->userModel->update($user['id'], [
                'reset_token' => $resetToken,
                'reset_expires' => $expires
            ]);
            
            // Kirim email reset password
            $this->sendResetPasswordEmail($user['email'], $user['nama_lengkap'], $resetToken);
            
            // Log aktivitas
            $this->logAktivitas('FORGOT_PASSWORD', 'Permintaan reset password', $user['id']);
        }

        // Always show success message for security
        return redirect()->to('/login')
            ->with('success', 'Jika email terdaftar, instruksi reset password akan dikirim.');
    }

    /**
     * Halaman Reset Password
     */
    public function resetPassword($token = null)
    {
        if (empty($token)) {
            return redirect()->to('/login')->with('error', 'Token tidak valid');
        }

        $user = $this->userModel->where('reset_token', $token)
            ->where('reset_expires >', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau sudah kadaluarsa');
        }

        $data = [
            'title' => 'Reset Password - Sistem Informasi Gereja',
            'token' => $token,
            'validation' => \Config\Services::validation()
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Proses Reset Password
     */
    public function processResetPassword()
    {
        $token = $this->request->getPost('token');
        
        if (!$this->validate($this->getResetPasswordRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->where('reset_token', $token)
            ->where('reset_expires >', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Token tidak valid atau sudah kadaluarsa');
        }

        $newPassword = $this->request->getPost('new_password');
        
        // Update password
        $this->userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log aktivitas
        $this->logAktivitas('RESET_PASSWORD', 'Reset password berhasil', $user['id']);

        return redirect()->to('/login')
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }

    /**
     * Logout
     */
    public function logout()
    {
        $userId = $this->session->get('user_id');
        
        // Log aktivitas
        $this->logAktivitas('LOGOUT', 'Logout berhasil', $userId);
        
        // Hapus session
        $this->session->destroy();
        
        // Hapus cookie remember me
        $this->response->deleteCookie('remember_token');
        
        return redirect()->to('/login')->with('success', 'Anda telah logout.');
    }

    /**
     * Profile User
     */
    public function profile()
    {
        if (!$this->authLib->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        $data = [
            'title' => 'Profil Pengguna',
            'user' => $user,
            'validation' => \Config\Services::validation()
        ];

        return view('auth/profile', $data);
    }

    /**
     * Update Profile
     */
    public function updateProfile()
    {
        if (!$this->authLib->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('user_id');
        
        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'telepon' => 'required|min_length[10]|max_length[15]',
            'alamat' => 'required|min_length[5]|max_length[255]',
            'jabatan_gereja' => 'required|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'telepon' => $this->request->getPost('telepon'),
            'alamat' => $this->request->getPost('alamat'),
            'jabatan_gereja' => $this->request->getPost('jabatan_gereja'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->userModel->update($userId, $data);
        
        // Update session
        $this->session->set('nama_lengkap', $data['nama_lengkap']);
        $this->session->set('jabatan_gereja', $data['jabatan_gereja']);

        // Log aktivitas
        $this->logAktivitas('UPDATE_PROFILE', 'Update profil berhasil', $userId);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Change Password
     */
    public function changePassword()
    {
        if (!$this->authLib->isLoggedIn()) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|strong_password',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Password saat ini salah');
        }

        // Update password
        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log aktivitas
        $this->logAktivitas('CHANGE_PASSWORD', 'Ubah password berhasil', $userId);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }

    /**
     * Rules untuk Login
     */
    private function getLoginRules()
    {
        return [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];
    }

    /**
     * Rules untuk Registrasi
     */
    private function getRegisterRules()
    {
        return [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]|strong_password',
            'confirm_password' => 'required|matches[password]',
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'telepon' => 'required|min_length[10]|max_length[15]',
            'jabatan_gereja' => 'required|max_length[100]'
        ];
    }

    /**
     * Rules untuk Reset Password
     */
    private function getResetPasswordRules()
    {
        return [
            'new_password' => 'required|min_length[8]|strong_password',
            'confirm_password' => 'required|matches[new_password]'
        ];
    }

    /**
     * Set Remember Me Cookie
     */
    private function setRememberMe($userId)
    {
        $token = bin2hex(random_bytes(32));
        $expire = time() + (86400 * 30); // 30 hari
        
        // Simpan token di database
        $this->userModel->update($userId, [
            'remember_token' => $token,
            'remember_expires' => date('Y-m-d H:i:s', $expire)
        ]);
        
        // Set cookie
        $this->response->setCookie('remember_token', $token, $expire);
    }

    /**
     * Redirect berdasarkan Role
     */
    private function redirectByRole($role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'pendeta':
                return redirect()->to('/pendeta/dashboard');
            case 'pengurus':
                return redirect()->to('/pengurus/dashboard');
            case 'jemaat':
            default:
                return redirect()->to('/dashboard');
        }
    }

    /**
     * Log Aktivitas
     */
    private function logAktivitas($tipe, $deskripsi, $userId = null)
    {
        $logData = [
            'user_id' => $userId ?? $this->session->get('user_id'),
            'tipe_aktivitas' => $tipe,
            'deskripsi' => $deskripsi,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logModel->insert($logData);
    }

    /**
     * Kirim Email Aktivasi
     */
    private function sendActivationEmail($email, $nama, $activationCode)
    {
        $emailService = \Config\Services::email();
        
        $activationLink = base_url('auth/activate/' . $activationCode);
        
        $message = view('emails/activation_email', [
            'nama' => $nama,
            'activationLink' => $activationLink
        ]);
        
        $emailService->setTo($email);
        $emailService->setSubject('Aktivasi Akun - Sistem Informasi Gereja');
        $emailService->setMessage($message);
        
        return $emailService->send();
    }

    /**
     * Kirim Email Reset Password
     */
    private function sendResetPasswordEmail($email, $nama, $resetToken)
    {
        $emailService = \Config\Services::email();
        
        $resetLink = base_url('auth/reset-password/' . $resetToken);
        
        $message = view('emails/reset_password_email', [
            'nama' => $nama,
            'resetLink' => $resetLink
        ]);
        
        $emailService->setTo($email);
        $emailService->setSubject('Reset Password - Sistem Informasi Gereja');
        $emailService->setMessage($message);
        
        return $emailService->send();
    }

    /**
     * Cek Ketersediaan Username (AJAX)
     */
    public function checkUsername()
    {
        $username = $this->request->getGet('username');
        $exists = $this->userModel->where('username', $username)->first();
        
        return $this->response->setJSON([
            'available' => !$exists
        ]);
    }

    /**
     * Cek Ketersediaan Email (AJAX)
     */
    public function checkEmail()
    {
        $email = $this->request->getGet('email');
        $exists = $this->userModel->where('email', $email)->first();
        
        return $this->response->setJSON([
            'available' => !$exists
        ]);
    }
}
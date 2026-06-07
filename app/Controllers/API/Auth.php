<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class AuthApi extends BaseController
{
    use ResponseTrait;

    protected $userModel;
    protected $validation;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->validation = \Config\Services::validation();
        
        helper(['form', 'url']);
    }

    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register()
    {
        // Validate input
        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[255]',
            'email'        => 'required|valid_email|is_unique[users.email]',
            'password'     => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'nomor_telepon' => 'permit_empty|min_length[10]|max_length[20]',
            'role'         => 'permit_empty|in_list[admin,pengurus,jemaat,pendeta,majelis]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Prepare user data
        $userData = [
            'nama_lengkap'  => $this->request->getVar('nama_lengkap'),
            'email'         => $this->request->getVar('email'),
            'password'      => $this->request->getVar('password'),
            'role'          => $this->request->getVar('role') ?? 'jemaat',
            'nomor_telepon' => $this->request->getVar('nomor_telepon'),
            'alamat'        => $this->request->getVar('alamat'),
            'tanggal_lahir' => $this->request->getVar('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getVar('jenis_kelamin'),
            'is_active'     => 1
        ];

        // Insert user
        if ($this->userModel->save($userData)) {
            $userId = $this->userModel->getInsertID();
            
            // Get created user without password
            $user = $this->userModel->find($userId);
            unset($user['password']);
            
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => $user,
                    ]
                ]
            ]);
        }

        return $this->failServerError('Gagal melakukan registrasi');
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login()
    {
        // Validate input
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        // Get user by email
        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            return $this->failUnauthorized('Email atau password salah');
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            return $this->failUnauthorized('Email atau password salah');
        }

        // Check if user is active
        if (!$user['is_active']) {
            return $this->failForbidden('Akun tidak aktif. Silakan hubungi admin.');
        }

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Remove password from response
        unset($user['password']);

        return $this->respond([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                ]
            ]
        ]);
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
    
        return $this->fail('Gagal logout', 400);
    }

    /**
     * Get current user profile
     * GET /api/auth/profile
     */
    public function profile()
    {
        // This method requires authentication via JWTAuth filter
        $user = $this->request->user;
        unset($user['password']);

        return $this->respond([
            'status' => 'success',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Update user profile
     * PUT /api/auth/profile
     */
    public function updateProfile()
    {
        $userId = $this->request->userId;
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->failNotFound('Pengguna tidak ditemukan');
        }

        // Validation rules
        $rules = [
            'nama_lengkap' => 'permit_empty|min_length[3]|max_length[255]',
            'email'        => "permit_empty|valid_email|is_unique[users.email,id,{$userId}]",
            'nomor_telepon' => 'permit_empty|min_length[10]|max_length[20]',
            'tanggal_lahir' => 'permit_empty|valid_date',
            'jenis_kelamin' => 'permit_empty|in_list[L,P]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Collect update data
        $updateData = [];
        $fields = ['nama_lengkap', 'email', 'nomor_telepon', 'alamat', 'tanggal_lahir', 'jenis_kelamin'];
        
        foreach ($fields as $field) {
            if ($this->request->getVar($field) !== null) {
                $updateData[$field] = $this->request->getVar($field);
            }
        }

        // Handle password update
        $newPassword = $this->request->getVar('new_password');
        if ($newPassword) {
            $currentPassword = $this->request->getVar('current_password');
            
            if (!$currentPassword) {
                return $this->failValidationErrors(['current_password' => 'Password saat ini diperlukan']);
            }
            
            if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
                return $this->failUnauthorized('Password saat ini salah');
            }
            
            $updateData['password'] = $newPassword;
        }

        // Handle profile picture upload
        $profilePicture = $this->request->getFile('foto_profil');
        if ($profilePicture && $profilePicture->isValid() && !$profilePicture->hasMoved()) {
            $newName = $profilePicture->getRandomName();
            $profilePicture->move(WRITEPATH . 'uploads/profiles', $newName);
            $updateData['foto_profil'] = $newName;
            
            // Delete old profile picture if exists
            if ($user['foto_profil']) {
                $oldPath = WRITEPATH . 'uploads/profiles/' . $user['foto_profil'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        }

        // Update user
        if ($this->userModel->update($userId, $updateData)) {
            $updatedUser = $this->userModel->find($userId);
            unset($updatedUser['password']);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'user' => $updatedUser
                ]
            ]);
        }

        return $this->failServerError('Gagal memperbarui profil');
    }

    /**
     * Forgot password - Send reset link
     * POST /api/auth/forgot-password
     */
    public function forgotPassword()
    {
        $email = $this->request->getVar('email');

        if (!$email) {
            return $this->failValidationErrors(['email' => 'Email diperlukan']);
        }

        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            // Return success even if email not found for security
            return $this->respond([
                'status' => 'success',
                'message' => 'Jika email terdaftar, link reset password akan dikirim'
            ]);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Link reset password telah dikirim ke email Anda',
            'data' => [
                'reset_link' => $resetLink    // Remove this in production
            ]
        ]);
    }

    /**
     * Change password (authenticated)
     * POST /api/auth/change-password
     */
    public function changePassword()
    {
        $userId = $this->request->userId;

        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $user = $this->userModel->find($userId);
        $currentPassword = $this->request->getVar('current_password');
        $newPassword = $this->request->getVar('new_password');

        // Verify current password
        if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            return $this->failUnauthorized('Password saat ini salah');
        }

        // Update to new password
        if ($this->userModel->update($userId, ['password' => $newPassword])) {
            // Revoke all tokens for security
            $this->tokenModel->revokeAllUserTokens($userId);
            
            return $this->respond([
                'status' => 'success',
                'message' => 'Password berhasil diubah. Silakan login kembali.'
            ]);
        }

        return $this->failServerError('Gagal mengubah password');
    }

    /**
     * Check if email exists
     * POST /api/auth/check-email
     */
    public function checkEmail()
    {
        $email = $this->request->getVar('email');

        if (!$email) {
            return $this->failValidationErrors(['email' => 'Email diperlukan']);
        }

        $user = $this->userModel->getUserByEmail($email);

        return $this->respond([
            'status' => 'success',
            'data' => [
                'exists' => $user ? true : false,
                'is_active' => $user ? (bool)$user['is_active'] : false
            ]
        ]);
    }
}
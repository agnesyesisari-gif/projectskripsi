<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Paths;

class SystemController extends BaseController
{
    public function paths()
    {
        $paths = new Paths();
        
        $data = [
            'title' => 'System Paths Configuration',
            'paths' => [
                'System Directory' => $paths->systemDirectory,
                'App Directory' => $paths->appDirectory,
                'Writable Directory' => $paths->writableDirectory,
                'View Directory' => $paths->viewDirectory,
                'Jadwal Upload' => $paths->getJadwalUploadPath(),
                'Program Upload' => $paths->getProgramUploadPath(),
                'Pengumuman Upload' => $paths->getPengumumanUploadPath(),
                'Backup Path' => $paths->getBackupPath(),
                'Laporan Path' => $paths->getLaporanPath(),
            ],
            'upload_paths' => $paths->getUploadPaths(),
            'permissions' => $paths->checkDirectoryPermissions(),
        ];
        
        return view('admin/system/paths', $data);
    }
    
    public function createDirectories()
    {
        $paths = new Paths();
        $paths->createDirectories();
        
        return redirect()->back()->with('success', 'All directories created successfully!');
    }
    
    public function downloadPathsConfig()
    {
        $paths = new Paths();
        $config = [
            'directories' => $paths->checkDirectoryPermissions(),
            'upload_paths' => $paths->getUploadPaths(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
        
        return $this->response->setJSON($config);
    }
}
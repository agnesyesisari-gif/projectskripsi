<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-folder"></i> System Paths Configuration</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Action Buttons -->
                        <div class="mb-4">
                            <a href="<?= site_url('admin/system/create-directories') ?>" class="btn btn-success">
                                <i class="bi bi-folder-plus"></i> Create All Directories
                            </a>
                            <button onclick="downloadConfig()" class="btn btn-info">
                                <i class="bi bi-download"></i> Download Config
                            </button>
                        </div>
                        
                        <!-- System Paths -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">System Paths</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30%">Path Type</th>
                                            <th>Absolute Path</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($paths as $name => $path): ?>
                                        <tr>
                                            <td><strong><?= $name ?></strong></td>
                                            <td><code><?= $path ?></code></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Upload Paths Configuration -->
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">Upload Paths Configuration</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Type</th>
                                            <th>Absolute Path</th>
                                            <th>URL</th>
                                            <th>Max Size</th>
                                            <th>Allowed Types</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upload_paths as $type => $config): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= strtoupper($type) ?></span></td>
                                            <td><code><?= $config['absolute'] ?></code></td>
                                            <td><code><?= $config['url'] ?></code></td>
                                            <td><?= format_bytes($config['max_size']) ?></td>
                                            <td>
                                                <?php foreach ($config['allowed_types'] as $ext): ?>
                                                    <span class="badge bg-secondary"><?= $ext ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Directory Permissions -->
                        <div>
                            <h5 class="border-bottom pb-2">Directory Permissions</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Directory</th>
                                            <th>Path</th>
                                            <th>Exists</th>
                                            <th>Writable</th>
                                            <th>Permission</th>
                                            <th>Owner</th>
                                            <th>Group</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($permissions as $name => $info): ?>
                                        <tr>
                                            <td><strong><?= ucfirst(str_replace('_', ' ', $name)) ?></strong></td>
                                            <td><code><?= $info['path'] ?></code></td>
                                            <td>
                                                <span class="badge bg-<?= $info['exists'] ? 'success' : 'danger' ?>">
                                                    <?= $info['exists'] ? 'Yes' : 'No' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $info['writable'] ? 'success' : 'danger' ?>">
                                                    <?= $info['writable'] ? 'Yes' : 'No' ?>
                                                </span>
                                            </td>
                                            <td><code><?= $info['permission'] ?></code></td>
                                            <td><?= $info['owner'] ?></td>
                                            <td><?= $info['group'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function downloadConfig() {
        window.location.href = '<?= site_url('admin/system/download-paths-config') ?>';
    }
    
    // Helper untuk format bytes
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    </script>
</body>
</html>
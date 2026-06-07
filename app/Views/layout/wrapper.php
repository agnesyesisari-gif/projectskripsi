<?php echo view('layout/Header', ['title' => $title ?? 'Sistem Informasi Gereja']); ?>
<?php echo view($content, $data ?? []); ?>
<?php echo view('layout/Footer'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Laporan Kegiatan Gereja</h3>
                </div>
                <div class="card-body">
                    <!-- Laporan Jadwal Ibadah -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0">Laporan Jadwal Ibadah</h4>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('laporan/ibadah') ?>" method="GET" target="_blank">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tahun_ibadah">Tahun</label>
                                                    <select class="form-control" id="tahun_ibadah" name="tahun" required>
                                                        <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                                                            <option value="<?= $i ?>" <?= $i == $tahun ? 'selected' : '' ?>>
                                                                <?= $i ?>
                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="bulan_ibadah">Bulan</label>
                                                    <select class="form-control" id="bulan_ibadah" name="bulan" required>
                                                        <?php 
                                                        $bulan_arr = [
                                                            '01'=>'Januari', '02'=>'Februari', '03'=>'Maret',
                                                            '04'=>'April', '05'=>'Mei', '06'=>'Juni',
                                                            '07'=>'Juli', '08'=>'Agustus', '09'=>'September',
                                                            '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
                                                        ];
                                                        foreach($bulan_arr as $key => $value): ?>
                                                            <option value="<?= $key ?>" <?= $key == $bulan ? 'selected' : '' ?>>
                                                                <?= $value ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group" style="margin-top: 32px;">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-print"></i> Cetak Laporan
                                                    </button>
                                                    <a href="#" class="btn btn-danger cetak-pdf-ibadah">
                                                        <i class="fas fa-file-pdf"></i> Export PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Laporan Program Kerja -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h4 class="card-title mb-0">Laporan Program Kerja</h4>
                                </div>
                                <div class="card-body">
                                    <form action="<?= base_url('laporan/program_kerja') ?>" method="GET" target="_blank">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="tahun_program">Tahun</label>
                                                    <select class="form-control" id="tahun_program" name="tahun" required>
                                                        <?php for($i = date('Y'); $i >= 2020; $i--): ?>
                                                            <option value="<?= $i ?>" <?= $i == $tahun ? 'selected' : '' ?>>
                                                                <?= $i ?>
                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="status_program">Status</label>
                                                    <select class="form-control" id="status_program" name="status">
                                                        <option value="">Semua Status</option>
                                                        <option value="rencana">Rencana</option>
                                                        <option value="berjalan">Berjalan</option>
                                                        <option value="selesai">Selesai</option>
                                                        <option value="ditunda">Ditunda</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group" style="margin-top: 32px;">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fas fa-print"></i> Cetak Laporan
                                                    </button>
                                                    <a href="#" class="btn btn-danger cetak-pdf-program">
                                                        <i class="fas fa-file-pdf"></i> Export PDF
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Cetak PDF Ibadah
    $('.cetak-pdf-ibadah').click(function(e) {
        e.preventDefault();
        var tahun = $('#tahun_ibadah').val();
        var bulan = $('#bulan_ibadah').val();
        window.open('<?= base_url('laporan/cetak_pdf_ibadah') ?>?tahun=' + tahun + '&bulan=' + bulan, '_blank');
    });

    // Cetak PDF Program
    $('.cetak-pdf-program').click(function(e) {
        e.preventDefault();
        var tahun = $('#tahun_program').val();
        var status = $('#status_program').val();
        window.open('<?= base_url('laporan/cetak_pdf_program') ?>?tahun=' + tahun + '&status=' + status, '_blank');
    });
});
</script>
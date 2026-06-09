<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="box box-primary">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-database"></i> Backup Database</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Backup akan mengunduh file <code>.sql</code> yang berisi seluruh data database
                    <strong>gkj_penaruban</strong>.
                    Simpan file ini di tempat yang aman.
                </div>

                <div class="well" style="background:#f9f9f9;">
                    <h5><i class="fa fa-server"></i> Informasi Database</h5>
                    <table class="table table-condensed" style="margin:0;font-size:13px;">
                        <tr>
                            <td width="120"><strong>Database</strong></td>
                            <td>gkj_penaruban</td>
                        </tr>
                        <tr>
                            <td><strong>Host</strong></td>
                            <td>localhost</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal</strong></td>
                            <td><?= date('d F Y, H:i') ?> WIB</td>
                        </tr>
                    </table>
                </div>

                <form action="<?= base_url('backup/proses') ?>" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fa fa-download"></i>&nbsp; Download Backup SQL
                    </button>
                </form>

                <div style="margin-top:15px;" class="alert alert-warning" style="font-size:12px;">
                    <i class="fa fa-warning"></i>
                    <strong>Tips:</strong> Lakukan backup secara rutin, minimal seminggu sekali.
                </div>
            </div>
        </div>
    </div>
</div>

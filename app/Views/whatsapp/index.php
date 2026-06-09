<div class="row">
    <!-- Form kirim -->
    <div class="col-md-5">
        <div class="box box-success">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-whatsapp"></i> Kirim Pesan WhatsApp</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-info" style="font-size:12px;">
                    <i class="fa fa-info-circle"></i>
                    Integrasi dengan <strong>Fonnte / WA-Web / CallMeBot</strong>.
                    Konfigurasi API key di <code>app/Config/Whatsapp.php</code>.
                </div>

                <form action="<?= base_url('whatsapp/kirim') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Nomor Tujuan</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                            <input type="text" name="nomor" class="form-control"
                                   placeholder="08xxxxxxxxxx / 628xxxxxxxxx" required>
                        </div>
                        <span class="help-block" style="font-size:11px;">Format: 08xxx atau 628xxx</span>
                    </div>
                    <div class="form-group">
                        <label>Pesan</label>
                        <textarea name="pesan" class="form-control" rows="5"
                                  placeholder="Ketik pesan di sini..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fa fa-send"></i> Kirim Pesan
                    </button>
                </form>

                <?php if (! empty($pengumumanList)): ?>
                    <hr>
                    <h5><i class="fa fa-bullhorn"></i> Kirim Pengumuman Massal</h5>
                    <p class="text-muted" style="font-size:12px;">
                        Fitur ini akan mengirim pengumuman ke semua jemaat yang terdaftar.
                    </p>
                    <div class="form-group">
                        <select id="selPengumuman" class="form-control input-sm">
                            <option value="">-- Pilih Pengumuman --</option>
                            <?php foreach ($pengumumanList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= esc($p['judul']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="<?= base_url('whatsapp') ?>" class="btn btn-warning btn-block btn-sm">
                        <i class="fa fa-paper-plane"></i> Kirim Massal (Coming Soon)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Riwayat -->
    <div class="col-md-7">
        <div class="box box-info">
            <div class="box-header">
                <h3 class="box-title"><i class="fa fa-history"></i> Riwayat Pengiriman</h3>
            </div>
            <div class="box-body" style="padding:0;">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" style="font-size:12px;margin:0;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor</th>
                                <th>Pesan</th>
                                <th>Status</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($riwayat)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada riwayat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat as $i => $r): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= esc($r['nomor']) ?></td>
                                        <td><?= esc(mb_strimwidth($r['pesan'], 0, 50, '...')) ?></td>
                                        <td>
                                            <span class="label label-<?= $r['status'] === 'terkirim' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($r['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

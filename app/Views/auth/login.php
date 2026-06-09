<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GKJ PENARUBAN</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: #222d32;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            margin: 0;
        }
        .login-box {
            width: 360px; background: #fff;
            border-radius: 6px; padding: 35px 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,.35);
        }
        .login-logo { text-align: center; margin-bottom: 25px; }
        .logo-circle {
            width: 70px; height: 70px; border-radius: 50%;
            background: #3c8dbc; color: #fff;
            font-size: 28px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 12px;
        }
        .login-logo h4 { color: #222d32; font-weight: 700; margin: 0 0 3px; font-size: 17px; }
        .login-logo small { color: #888; font-size: 12px; }
        .input-group-addon { background: #3c8dbc; color: #fff; border-color: #3c8dbc; }
        .btn-login {
            background: #3c8dbc; color: #fff; border: none;
            width: 100%; padding: 10px; border-radius: 4px;
            font-size: 15px; font-weight: 600; margin-top: 8px;
            transition: background .2s;
        }
        .btn-login:hover { background: #337ab7; color: #fff; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="login-logo">
        <div class="logo-circle">G</div>
        <h4>GKJ PENARUBAN</h4>
        <small>Sistem Informasi Kegiatan Pelayanan Gereja</small>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <i class="fa fa-warning"></i> <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('login') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Username</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user"></i></span>
                <input type="text" name="username" class="form-control"
                       placeholder="Username" required autofocus>
            </div>
        </div>
        <div class="form-group">
            <label>Password</label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                <input type="password" name="password" class="form-control"
                       placeholder="Password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-login">
            <i class="fa fa-sign-in"></i> Masuk
        </button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>

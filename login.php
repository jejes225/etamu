<?php
// login.php - Login Admin DPRD Provinsi Lampung

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// Jika sudah login
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
    header("Location: admin.php");
    exit();
}

$error = "";

// LOGIN ADMIN
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'dprd2026');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {

        $error = "Username dan password wajib diisi!";
    } elseif ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {

        $_SESSION['admin_login'] = true;
        $_SESSION['admin_name']  = 'Administrator';
        $_SESSION['login_time']  = time();

        header("Location: admin.php");
        exit();
    } else {

        $error = "Username atau password salah!";
    }
}
?>

<!doctype html>
<html lang="id">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin - DPRD Provinsi Lampung</title>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --gold: #C49A22;
            --gold-dark: #8B6B0F;
            --gold-light: #E8C96A;
            --gold-pale: #FAF3E0;

            --cream: #F9F5ED;

            --ink: #1A1208;
            --ink-soft: #7A6A4A;

            --border: rgba(196, 154, 34, 0.22);
            --border-strong: rgba(196, 154, 34, 0.55);
        }

        html {
            scroll-behavior: smooth;
        }

        body {

            min-height: 100vh;

            font-family: 'Outfit', sans-serif;

            background:
                linear-gradient(rgba(10, 10, 10, .76), rgba(10, 10, 10, .76)),
                url('img/gedung.jpg');

            background-size: cover;
            background-position: center;
            background-attachment: fixed;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow-x: hidden;
            overflow-y: auto;

            color: white;

            position: relative;

            padding: 40px 0;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;

            background:
                radial-gradient(circle at top right, rgba(196, 154, 34, .18), transparent 30%),
                radial-gradient(circle at bottom left, rgba(196, 154, 34, .12), transparent 30%);

            pointer-events: none;
        }

        /* TOP ORNAMENT */
        .tapis-top {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;

            background: repeating-linear-gradient(90deg,
                    var(--gold) 0,
                    var(--gold) 10px,
                    var(--gold-dark) 10px,
                    var(--gold-dark) 18px,
                    var(--gold-light) 18px,
                    var(--gold-light) 24px);

            z-index: 99;
        }

        .tapis-bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;

            background: repeating-linear-gradient(90deg,
                    var(--gold-dark) 0,
                    var(--gold-dark) 8px,
                    var(--gold) 8px,
                    var(--gold) 16px);

            z-index: 99;
        }

        /* CONTAINER */
        .login-container {

            width: 100%;
            max-width: 450px;

            padding: 20px;

            position: relative;
            z-index: 5;

            display: flex;
            align-items: center;
            justify-content: center;

            min-height: 100%;
        }

        /* CARD */
        .login-card {

            width: 100%;

            position: relative;

            background: rgba(255, 255, 255, .08);

            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);

            border: 1px solid rgba(255, 255, 255, .12);

            border-radius: 30px;

            overflow: hidden;

            box-shadow:
                0 10px 40px rgba(0, 0, 0, .35),
                inset 0 1px 0 rgba(255, 255, 255, .08);

            animation: fadeUp .8s ease;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;

            background: linear-gradient(90deg,
                    transparent,
                    var(--gold),
                    var(--gold-light),
                    var(--gold),
                    transparent);
        }

        .card-content {
            padding: 42px 34px 34px;
        }

        /* LOGO */
        .logo-wrap {

            width: 110px;
            height: 110px;

            margin: 0 auto 24px;

            border-radius: 50%;

            background: rgba(255, 255, 255, .10);

            border: 1px solid rgba(255, 255, 255, .18);

            display: flex;
            align-items: center;
            justify-content: center;

            position: relative;
        }

        .logo-wrap::before {
            content: '';
            position: absolute;
            inset: 7px;
            border-radius: 50%;
            border: 1px dashed rgba(255, 255, 255, .18);
        }

        .logo-wrap img {
            width: 68px;
            height: 68px;
            object-fit: contain;
            position: relative;
            z-index: 2;
        }

        /* TYPOGRAPHY */
        .eyebrow {
            text-align: center;
            font-size: 11px;
            letter-spacing: .28em;
            text-transform: uppercase;
            color: var(--gold-light);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .title {
            text-align: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 700;
            line-height: 1.1;
        }

        .title span {
            color: var(--gold-light);
            font-style: italic;
        }

        .subtitle {
            margin-top: 14px;
            text-align: center;
            font-size: 14px;
            line-height: 1.7;
            color: rgba(255, 255, 255, .76);
        }

        .divider {
            width: 70px;
            height: 1px;

            background: linear-gradient(90deg,
                    transparent,
                    var(--gold-light),
                    transparent);

            margin: 24px auto;
        }

        /* ALERT */
        .alert {

            margin-bottom: 18px;

            padding: 14px 16px;

            border-radius: 16px;

            font-size: 13px;

            display: flex;
            gap: 10px;
            align-items: flex-start;

            line-height: 1.5;
        }

        .alert-error {
            background: rgba(239, 68, 68, .12);
            border: 1px solid rgba(239, 68, 68, .28);
            color: #ffd6d6;
        }

        /* INPUT */
        .input-group {
            margin-bottom: 18px;
        }

        .input-label {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: rgba(255, 255, 255, .7);
            font-weight: 600;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            opacity: .65;
        }

        .form-input {

            width: 100%;
            height: 58px;

            border-radius: 18px;

            border: 1px solid rgba(255, 255, 255, .12);

            background: rgba(255, 255, 255, .06);

            color: white;

            padding: 0 52px;

            font-size: 14px;

            outline: none;

            transition: .25s;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, .4);
        }

        .form-input:focus {
            border-color: var(--gold);
            background: rgba(255, 255, 255, .08);

            box-shadow:
                0 0 0 4px rgba(196, 154, 34, .12);
        }

        /* TOGGLE */
        .toggle-password {

            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);

            background: none;
            border: none;

            color: rgba(255, 255, 255, .65);

            cursor: pointer;

            font-size: 16px;
        }

        /* BUTTON */
        .login-btn {

            width: 100%;
            height: 58px;

            border: none;

            border-radius: 18px;

            background: linear-gradient(135deg,
                    var(--gold-dark),
                    var(--gold),
                    #d9b03f);

            color: white;

            font-size: 15px;
            font-weight: 700;

            letter-spacing: .08em;

            cursor: pointer;

            transition: .3s;

            margin-top: 10px;

            box-shadow:
                0 10px 30px rgba(196, 154, 34, .25);
        }

        .login-btn:hover {
            transform: translateY(-2px);

            box-shadow:
                0 14px 34px rgba(196, 154, 34, .38);
        }

        /* FOOTER */
        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: rgba(255, 255, 255, .55);
            line-height: 1.7;
        }

        /* ANIMATION */
        @keyframes fadeUp {

            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */
        @media(max-width:768px) {

            body {
                align-items: flex-start;
                padding: 24px 0 40px;
            }

            .login-container {
                padding: 16px;
            }

            .login-card {
                border-radius: 24px;
            }

            .card-content {
                padding: 34px 24px 26px;
            }

            .logo-wrap {
                width: 95px;
                height: 95px;
            }

            .logo-wrap img {
                width: 58px;
                height: 58px;
            }

            .title {
                font-size: 36px;
            }

            .subtitle {
                font-size: 13px;
            }

            .form-input {
                height: 54px;
            }

            .login-btn {
                height: 54px;
                font-size: 14px;
            }
        }

        @media(max-width:480px) {

            body {
                background-attachment: scroll;
            }

            .card-content {
                padding: 30px 20px 22px;
            }

            .title {
                font-size: 30px;
            }

            .subtitle {
                line-height: 1.6;
            }

            .form-input {
                padding: 0 48px;
                font-size: 13px;
            }

            .login-btn {
                font-size: 13px;
                letter-spacing: .06em;
            }
        }
    </style>

</head>

<body>

    <div class="tapis-top"></div>

    <div class="login-container">

        <div class="login-card">

            <div class="card-content">

                <!-- LOGO -->
                <div class="logo-wrap">
                    <img src="img/logodrpd.jpg" alt="Logo DPRD">
                </div>

                <!-- HEADER -->
                <div class="eyebrow">
                    Sistem Informasi Digital
                </div>

                <h1 class="title">
                    Login <span>Admin</span>
                </h1>

                <p class="subtitle">
                    Sistem Buku Tamu Digital<br>
                    Sekretariat DPRD Provinsi Lampung
                </p>

                <div class="divider"></div>

                <!-- ERROR -->
                <?php if ($error): ?>

                    <div class="alert alert-error">

                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z" />
                        </svg>

                        <div>
                            <?= htmlspecialchars($error) ?>
                        </div>

                    </div>

                <?php endif; ?>

                <!-- FORM -->
                <form method="POST">

                    <!-- USERNAME -->
                    <div class="input-group">

                        <label class="input-label">
                            Username
                        </label>

                        <div class="input-wrap">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>

                            <input
                                type="text"
                                name="username"
                                class="form-input"
                                placeholder="Masukkan username"
                                required
                                autocomplete="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">

                        </div>

                    </div>

                    <!-- PASSWORD -->
                    <div class="input-group">

                        <label class="input-label">
                            Password
                        </label>

                        <div class="input-wrap">

                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2h-1V9a5 5 0 00-10 0v2H6a2 2 0 00-2 2v6a2 2 0 002 2zm3-10V9a3 3 0 016 0v2H9z" />
                            </svg>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-input"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password">

                            <button type="button"
                                class="toggle-password"
                                onclick="togglePassword()">

                                👁

                            </button>

                        </div>

                    </div>

                    <!-- BUTTON -->
                    <button type="submit" class="login-btn">
                        MASUK KE DASHBOARD
                    </button>

                </form>

                <!-- FOOTER -->
                <div class="footer">
                    © 2026 Sekretariat DPRD Provinsi Lampung<br>
                    e-Tamu Digital System
                </div>

            </div>

        </div>

    </div>

    <div class="tapis-bottom"></div>

    <script>
        function togglePassword() {

            const input = document.getElementById('password');

            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
    </script>

</body>

</html>
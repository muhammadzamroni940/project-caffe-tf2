<?php
require __DIR__ . '/../session_boot.php';
session_start();

if (isset($_POST['login'])) {
    include 'koneksi.php';
    $email=$_POST['email'];
    $password=md5($_POST['password']);

    $query=mysqli_query($koneksi, "SELECT*FROM users WHERE email='$email' AND password='$password'");
    $user=mysqli_fetch_assoc($query);

    if ($user) {
        $_SESSION['id_user']=$user['id_user'];
        $_SESSION['nama']=$user['nama'];
        $_SESSION['email']=$user['email'];
        $_SESSION['role']=$user['role'];

        if ($user['role'] === 'customer') {
            header("Location: ../customer/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }

    else {
        header("Location: login.php?error=1");
        exit();
    }
}

?><html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - RESTO UTM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-void: #170f19;
            --panel: #241726;
            --panel-2: #2f1b2f;
            --gold: #e3a83a;
            --gold-soft: #f0c674;
            --wine: #8a2846;
            --wine-soft: #b23a5c;
            --cream: #f5ead9;
            --muted: #a4919a;
            --danger: #e0587a;
            --success: #5cc99a;
        }

        /* Matikan background putih bawaan autofill Chrome/Edge/Safari */
        .field input:-webkit-autofill,
        .field input:-webkit-autofill:hover,
        .field input:-webkit-autofill:focus,
        .field input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px var(--panel) inset !important;
            -webkit-text-fill-color: var(--cream) !important;
            caret-color: var(--cream) !important;
            transition: background-color 9999s ease-in-out 0s;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', sans-serif;
            background: var(--bg-void);
            color: var(--cream);
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient drifting light blobs */
        body::before,
        body::after {
            content: "";
            position: fixed;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.35;
            z-index: 0;
            animation: drift 18s ease-in-out infinite alternate;
        }

        body::before {
            background: var(--wine);
            top: -120px;
            left: -100px;
        }

        body::after {
            background: var(--gold);
            bottom: -140px;
            right: -100px;
            opacity: 0.18;
            animation-delay: -6s;
        }

        @keyframes drift {
            0% {
                transform: translate(0, 0) scale(1);
            }

            100% {
                transform: translate(60px, 40px) scale(1.15);
            }
        }

        .stage {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-shell {
            width: 100%;
            max-width: 920px;
            display: flex;
            border-radius: 26px;
            overflow: hidden;
            background: var(--panel);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.55);
            opacity: 0;
            transform: translateY(24px);
            animation: shellIn 0.7s cubic-bezier(.2, .8, .2, 1) forwards;
        }

        @keyframes shellIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-shell.shake {
            animation: shellIn 0.7s cubic-bezier(.2, .8, .2, 1) forwards, shake 0.5s ease 0.05s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            20%,
            60% {
                transform: translateX(-8px);
            }

            40%,
            80% {
                transform: translateX(8px);
            }
        }

        /* ---- Brand panel ---- */
        .brand-pane {
            flex: 0 0 40%;
            position: relative;
            padding: 48px 36px;
            background: linear-gradient(165deg, var(--panel-2) 0%, var(--wine) 140%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
        }

        .ember {
            position: absolute;
            bottom: -10px;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--gold-soft);
            box-shadow: 0 0 8px 2px var(--gold);
            opacity: 0;
            animation: rise 5.5s ease-in infinite;
        }

        .ember:nth-child(1) {
            left: 18%;
            animation-delay: 0s;
        }

        .ember:nth-child(2) {
            left: 42%;
            animation-delay: 1.2s;
            width: 4px;
            height: 4px;
        }

        .ember:nth-child(3) {
            left: 65%;
            animation-delay: 2.4s;
        }

        .ember:nth-child(4) {
            left: 80%;
            animation-delay: 0.6s;
            width: 5px;
            height: 5px;
        }

        .ember:nth-child(5) {
            left: 30%;
            animation-delay: 3.4s;
            width: 3px;
            height: 3px;
        }

        @keyframes rise {
            0% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }

            15% {
                opacity: 0.9;
            }

            100% {
                transform: translateY(-320px) translateX(14px);
                opacity: 0;
            }
        }

        .logo-badge {
            width: 66px;
            height: 66px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            color: var(--wine);
            background: linear-gradient(150deg, var(--gold-soft), var(--gold));
            box-shadow: 0 0 0 6px rgba(227, 168, 58, 0.12), 0 12px 24px rgba(0, 0, 0, 0.35);
            animation: pulseGlow 3s ease-in-out infinite;
            position: relative;
            z-index: 2;
        }

        @keyframes pulseGlow {

            0%,
            100% {
                box-shadow: 0 0 0 6px rgba(227, 168, 58, 0.12), 0 12px 24px rgba(0, 0, 0, 0.35);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(227, 168, 58, 0.20), 0 12px 28px rgba(0, 0, 0, 0.4);
            }
        }

        .brand-title {
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-weight: 700;
            font-size: 2.3rem;
            margin: 20px 0 10px;
            color: var(--cream);
            position: relative;
            z-index: 2;
            letter-spacing: 0.5px;
        }

        .brand-tagline {
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.6;
            max-width: 280px;
            position: relative;
            z-index: 2;
        }

        .steam-line {
            position: absolute;
            top: 30px;
            right: 30px;
            opacity: 0.5;
        }

        .steam-line path {
            fill: none;
            stroke: var(--gold-soft);
            stroke-width: 2;
            stroke-linecap: round;
            animation: sway 4s ease-in-out infinite;
            transform-origin: bottom center;
        }

        .steam-line path:nth-child(2) {
            animation-delay: 0.6s;
        }

        .steam-line path:nth-child(3) {
            animation-delay: 1.2s;
        }

        @keyframes sway {

            0%,
            100% {
                transform: rotate(-4deg) translateY(0);
                opacity: 0.4;
            }

            50% {
                transform: rotate(4deg) translateY(-4px);
                opacity: 0.8;
            }
        }

        /* ---- Form panel ---- */
        .form-pane {
            flex: 1;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-heading {
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 4px;
        }

        .form-sub {
            color: var(--muted);
            font-size: 0.88rem;
            margin-bottom: 30px;
        }

        .field {
            position: relative;
            margin-bottom: 26px;
            opacity: 0;
            transform: translateY(10px);
            animation: fieldIn 0.55s ease forwards;
        }

        .field:nth-of-type(1) {
            animation-delay: 0.15s;
        }

        .field:nth-of-type(2) {
            animation-delay: 0.28s;
        }

        @keyframes fieldIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .field-icon {
            position: absolute;
            left: 2px;
            top: 14px;
            color: var(--muted);
            font-size: 1.05rem;
            transition: color 0.25s ease;
            pointer-events: none;
        }

        .field input {
            width: 100%;
            background: transparent;
            border: none;
            border-bottom: 1.5px solid rgba(164, 145, 154, 0.35);
            color: var(--cream);
            font-size: 1rem;
            font-family: 'Manrope', sans-serif;
            padding: 14px 34px 10px 30px;
            outline: none;
        }

        .field input::placeholder {
            color: transparent;
        }

        .field label {
            position: absolute;
            left: 30px;
            top: 14px;
            color: var(--muted);
            font-size: 1rem;
            pointer-events: none;
            transition: all 0.22s ease;
        }

        .field input:focus+label,
        .field input:not(:placeholder-shown)+label {
            top: -12px;
            left: 30px;
            font-size: 0.74rem;
            color: var(--gold-soft);
            letter-spacing: 0.03em;
        }

        .field input:focus~.field-icon {
            color: var(--gold-soft);
        }

        .field-underline {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 1.5px;
            width: 100%;
            background: linear-gradient(90deg, var(--gold), var(--wine-soft));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .field input:focus~.field-underline {
            transform: scaleX(1);
        }

        .toggle-eye {
            position: absolute;
            right: 2px;
            top: 12px;
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 1.05rem;
            padding: 4px;
            transition: color 0.2s ease;
        }

        .toggle-eye:hover {
            color: var(--gold-soft);
        }

        .check-mark {
            position: absolute;
            right: 30px;
            top: 14px;
            color: var(--success);
            font-size: 1rem;
            opacity: 0;
            transform: scale(0.6);
            transition: all 0.25s ease;
        }

        .check-mark.show {
            opacity: 1;
            transform: scale(1);
        }

        .caps-warning {
            display: none;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            font-size: 0.76rem;
            color: var(--gold-soft);
        }

        .caps-warning.show {
            display: flex;
        }

        .btn-cook {
            position: relative;
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 14px;
            margin-top: 6px;
            font-family: 'Manrope', sans-serif;
            font-weight: 700;
            font-size: 0.98rem;
            color: var(--panel-2);
            background: linear-gradient(120deg, var(--gold), var(--gold-soft));
            cursor: pointer;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            opacity: 0;
            animation: fieldIn 0.55s ease forwards;
            animation-delay: 0.4s;
        }

        .btn-cook:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(227, 168, 58, 0.28);
        }

        .btn-cook:active {
            transform: translateY(0);
        }

        .btn-cook::before {
            content: "";
            position: absolute;
            top: 0;
            left: -60%;
            width: 40%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.55), transparent);
            transform: skewX(-20deg);
            transition: left 0.6s ease;
        }

        .btn-cook:hover::before {
            left: 130%;
        }

        .btn-cook .btn-label,
        .btn-cook .btn-loading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
        }

        .btn-cook .btn-loading {
            display: none;
        }

        .btn-cook.is-loading .btn-label {
            display: none;
        }

        .btn-cook.is-loading .btn-loading {
            display: inline-flex;
        }

        .btn-cook.is-loading {
            pointer-events: none;
            opacity: 0.9;
        }

        .spinner {
            width: 15px;
            height: 15px;
            border: 2px solid rgba(36, 23, 38, 0.3);
            border-top-color: var(--panel-2);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ---- Divider + Google button ---- */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            opacity: 0;
            animation: fieldIn 0.55s ease forwards;
            animation-delay: 0.48s;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(164, 145, 154, 0.28);
        }

        .divider span {
            color: var(--muted);
            font-size: 0.78rem;
            letter-spacing: 0.04em;
        }

        .btn-google {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 1.5px solid rgba(164, 145, 154, 0.35);
            border-radius: 12px;
            padding: 13px;
            font-family: 'Manrope', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--cream);
            background: rgba(245, 234, 217, 0.04);
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
            opacity: 0;
            animation: fieldIn 0.55s ease forwards;
            animation-delay: 0.56s;
        }

        .btn-google:hover {
            transform: translateY(-2px);
            background: rgba(245, 234, 217, 0.08);
            border-color: var(--gold-soft);
            color: var(--cream);
        }

        .btn-google i {
            font-size: 1.05rem;
            color: var(--gold-soft);
        }

        /* Toast */
        .toast-error {
            position: fixed;
            top: 24px;
            left: 50%;
            transform: translate(-50%, -140%);
            background: var(--panel-2);
            border: 1px solid rgba(224, 88, 122, 0.4);
            color: var(--cream);
            padding: 14px 20px;
            border-radius: 12px;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            z-index: 10;
            transition: transform 0.45s cubic-bezier(.2, .8, .2, 1);
        }

        .toast-error.show {
            transform: translate(-50%, 0);
        }

        .toast-error i {
            color: var(--danger);
            font-size: 1.1rem;
        }

        @media (max-width: 760px) {
            .auth-shell {
                flex-direction: column;
            }

            .brand-pane {
                flex: none;
                padding: 32px 28px;
            }

            .brand-tagline {
                max-width: 100%;
            }

            .form-pane {
                padding: 36px 28px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="toast-error" id="toastError"><i class="bi bi-exclamation-circle"></i><span>Email Atau Password Yang Anda
            Masukkan Salah. Coba Lagi Yaa.</span></div>
    <div class="stage">
        <div class="auth-shell" id="authShell">
            <div class="brand-pane"><span class="ember"></span><span class="ember"></span><span
                    class="ember"></span><span class="ember"></span><span class="ember"></span><svg class="steam-line"
                    width="40" height="40" viewBox="0 0 40 40">
                    <path d="M8 40 C8 28, 16 28, 16 18 C16 10, 8 10, 8 2" />
                    <path d="M20 40 C20 28, 28 28, 28 18 C28 10, 20 10, 20 2" />
                    <path d="M32 40 C32 30, 24 30, 24 22" /></svg>
                <div class="logo-badge"><i class="bi bi-egg-fried"></i></div>
                <h1 class="brand-title">RESTO UTM</h1>
                <p class="brand-tagline">Kelola pesanan,
                    dapur,
                    dan meja dari satu tempat. Masuk untuk melanjutkan shift Anda.</p>
            </div>
            <div class="form-pane">
                <h2 class="form-heading">Selamat datang kembali</h2>
                <p class="form-sub">Masuk ke akun RESTO UTM Anda</p>
                <form action="login.php" method="POST" id="loginForm">
                    <div class="field"><i class="bi bi-envelope field-icon"></i><input type="email" id="email"
                            name="email" required placeholder=" " autocomplete="email"><label
                            for="email">Email</label><i class="bi bi-check-circle-fill check-mark"
                            id="emailCheck"></i><span class="field-underline"></span></div>
                    <div class="field"><i class="bi bi-lock field-icon"></i><input type="password" id="password"
                            name="password" required placeholder=" " autocomplete="current-password"><label
                            for="password">Password</label><button type="button" class="toggle-eye" id="togglePw"
                            aria-label="Tampilkan password"><i class="bi bi-eye"></i></button><span
                            class="field-underline"></span>
                        <div class="caps-warning" id="capsWarning"><i class="bi bi-arrow-up-square"></i>Caps Lock aktif
                        </div>
                    </div><button type="submit" name="login" class="btn-cook" id="submitBtn"><span class="btn-label"><i
                                class="bi bi-shop"></i>Masuk</span><span class="btn-loading"><span
                                class="spinner"></span>Membuka dapur...</span></button>
                </form>
                <div class="divider"><span>atau</span></div>
                <a href="../google-login.php" class="btn-google">
                    <i class="bi bi-google"></i>Masuk dengan Google
                </a>
            </div>
        </div>
    </div>
    <script>
        // Toggle password visibility
        const pwInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePw');

        toggleBtn.addEventListener('click', () => {
                const isHidden = pwInput.type === 'password';
                pwInput.type = isHidden ? 'text' : 'password';
                toggleBtn.innerHTML = isHidden ? '<i class="bi bi-eye-slash"></i>' :
                    '<i class="bi bi-eye"></i>';
                toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            }

        );

        // Caps Lock detection
        const capsWarning = document.getElementById('capsWarning');

        pwInput.addEventListener('keyup', (e) => {
                const caps = e.getModifierState && e.getModifierState('CapsLock');
                capsWarning.classList.toggle('show', !!caps);
            }

        );
        pwInput.addEventListener('blur', () => capsWarning.classList.remove('show'));

        // Real-time email validation
        const emailInput = document.getElementById('email');
        const emailCheck = document.getElementById('emailCheck');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        emailInput.addEventListener('input', () => {
                emailCheck.classList.toggle('show', emailPattern.test(emailInput.value));
            }

        );

        // Loading state on submit (native submit still proceeds)
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', () => {
                submitBtn.classList.add('is-loading');
            }

        );

        // Error toast from ?error=1
        const params = new URLSearchParams(window.location.search);

        if (params.get('error') === '1') {
            const toast = document.getElementById('toastError');
            const shell = document.getElementById('authShell');

            setTimeout(() => {
                    toast.classList.add('show');
                    shell.classList.add('shake');
                }

                , 300);
            setTimeout(() => toast.classList.remove('show'), 4300);

            // bersihkan query string agar toast tidak muncul lagi saat refresh
            window.history.replaceState({}

                , document.title, window.location.pathname);
        }
    </script>
</body>

</html>
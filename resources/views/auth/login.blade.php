<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg: #eceff5;
            --panel-blue: #5f7eea;
            --panel-blue-dark: #5774dd;
            --ink: #232738;
            --muted: #8289a3;
            --line: #e3e6f1;
            --button: #6784ec;
            --button-hover: #5978e5;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Poppins, sans-serif;
            color: var(--ink);
            background: var(--bg);
            overflow: hidden;
            position: relative;
        }

        .bg-orb {
            position: fixed;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
        }

        .bg-orb.left {
            width: 560px;
            height: 560px;
            left: -170px;
            top: -140px;
            background: radial-gradient(circle at 35% 35%, #84a0ff 0%, #6b86ee 60%, #5f7eea 100%);
        }

        .bg-orb.right-top {
            width: 240px;
            height: 240px;
            right: -90px;
            top: -40px;
            background: #95aff4;
            opacity: 0.8;
        }

        .bg-orb.right-bottom {
            width: 330px;
            height: 330px;
            right: -120px;
            bottom: -130px;
            border: 28px solid #86a3f1;
            background: transparent;
            opacity: 0.65;
        }

        .page {
            height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(10px, 2.2vh, 20px);
            position: relative;
            z-index: 1;
        }

        .auth-shell {
            width: 100%;
            max-width: 1140px;
            height: min(650px, 100%);
            max-height: 100%;
            border-radius: 18px;
            overflow: hidden;
            background: #f0f2f8;
            box-shadow: 0 18px 45px rgba(49, 68, 131, 0.16);
            display: grid;
            grid-template-columns: 1fr 1.12fr;
        }

        .left-panel {
            position: relative;
            background: linear-gradient(180deg, var(--panel-blue) 0%, var(--panel-blue-dark) 100%);
            color: #fff;
            padding: clamp(28px, 5vh, 56px) clamp(26px, 4vw, 52px);
        }

        .left-title {
            margin: clamp(80px, 16vh, 180px) 0 0;
            font-size: clamp(44px, 4.8vw, 66px);
            font-weight: 800;
            line-height: 0.98;
            letter-spacing: 0.1px;
        }

        .left-subtitle {
            margin-top: clamp(14px, 2vh, 24px);
            max-width: 360px;
            font-size: clamp(20px, 2.4vw, 32px);
            line-height: 1.15;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.92);
        }

        .deco-grid {
            position: absolute;
            width: 88px;
            height: 58px;
            background-image: radial-gradient(rgba(255, 255, 255, 0.72) 1.7px, transparent 1.7px);
            background-size: 11px 11px;
        }

        .deco-grid.top {
            left: 52px;
            top: 74px;
        }

        .deco-grid.bottom {
            left: 58px;
            bottom: 48px;
        }

        .deco-pill {
            position: absolute;
            border-radius: 999px;
            background: rgba(198, 215, 255, 0.45);
        }

        .deco-pill.a {
            width: 24px;
            height: 84px;
            left: 180px;
            top: 28px;
        }

        .deco-pill.b {
            width: 24px;
            height: 66px;
            left: 210px;
            top: 45px;
        }

        .deco-ring {
            position: absolute;
            width: 168px;
            height: 168px;
            right: -28px;
            bottom: -36px;
            border-radius: 999px;
            border: 8px solid rgba(255, 255, 255, 0.86);
        }

        .deco-ring::before {
            content: "";
            position: absolute;
            width: 86px;
            height: 86px;
            left: 48px;
            top: 48px;
            border-radius: 999px;
            background: radial-gradient(circle at 30% 30%, #71dcf5, #5ca4f0);
        }

        .deco-ball {
            position: absolute;
            width: 28px;
            height: 28px;
            right: 8px;
            bottom: 82px;
            border-radius: 999px;
            background: radial-gradient(circle at 35% 35%, #8eefff, #6cccf6);
        }

        .deco-cross {
            position: absolute;
            left: 250px;
            bottom: 60px;
            width: 16px;
            height: 16px;
        }

        .deco-cross::before,
        .deco-cross::after {
            content: "";
            position: absolute;
            left: 7px;
            top: 0;
            width: 2px;
            height: 16px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.9);
        }

        .deco-cross::before {
            transform: rotate(45deg);
        }

        .deco-cross::after {
            transform: rotate(-45deg);
        }

        .right-panel {
            position: relative;
            padding: clamp(18px, 3vh, 34px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(248, 249, 253, 0.95);
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #f7f8fc;
            border-radius: 14px;
            padding: clamp(18px, 2.4vh, 30px);
            box-shadow: inset 0 0 0 1px rgba(211, 216, 230, 0.6);
        }

        .brand-box {
            width: 72px;
            height: 72px;
            margin: 0 auto 10px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 14px rgba(96, 123, 204, 0.16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 800;
            color: #5f7eea;
        }

        .title {
            text-align: center;
            margin: 0;
            font-size: clamp(22px, 2.1vw, 30px);
            font-weight: 600;
            color: #2b3040;
        }

        .field {
            margin-top: clamp(10px, 1.4vh, 16px);
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 500;
            color: #4f556d;
        }

        .input {
            width: 100%;
            height: 44px;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 0 14px;
            font-size: 14px;
            font-family: inherit;
            color: #30364b;
            background: #fff;
            outline: none;
        }

        .input:focus {
            border-color: #8ba3f3;
            box-shadow: 0 0 0 3px rgba(121, 148, 239, 0.18);
        }

        .helper-row {
            margin-top: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #616984;
        }

        .remember input {
            width: 15px;
            height: 15px;
            accent-color: #617fe9;
        }

        .forgot {
            color: #6a84e9;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot:hover {
            color: #5571db;
        }

        .btn-login {
            margin-top: 10px;
            width: 100%;
            height: 46px;
            border: 0;
            border-radius: 6px;
            font-family: inherit;
            font-weight: 600;
            font-size: 16px;
            color: #fff;
            background: var(--button);
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-login:hover {
            background: var(--button-hover);
        }

        .footer-text {
            margin-top: 12px;
            text-align: center;
            font-size: 13px;
            color: #6a718b;
        }

        .footer-text a {
            color: #5f7eea;
            text-decoration: none;
            font-weight: 600;
        }

        .notice {
            margin-top: 10px;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .notice.success {
            border: 1px solid #b8ebd0;
            background: #ebfbf2;
            color: #17764d;
        }

        .notice.error {
            border: 1px solid #f0c2c2;
            background: #fff2f2;
            color: #a73e3e;
        }

        @media (max-width: 980px) {
            .auth-shell {
                min-height: auto;
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 28px 18px;
            }

            .login-card {
                padding: 24px 20px;
            }

            .title {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="bg-orb left"></div>
    <div class="bg-orb right-top"></div>
    <div class="bg-orb right-bottom"></div>

    <main class="page">
        <section class="auth-shell">
            <aside class="left-panel">
                <div class="deco-grid top"></div>
                <div class="deco-grid bottom"></div>
                <div class="deco-pill a"></div>
                <div class="deco-pill b"></div>
                <div class="deco-ring"></div>
                <div class="deco-ball"></div>
                <div class="deco-cross"></div>

                <h2 class="left-title">Sistem Informasi PKL</h2>
                <p class="left-subtitle">Siap Bekerja, Siap Belajar, Siap Berkarya</p>
            </aside>

            <section class="right-panel">
                <div class="login-card">
                    <div class="brand-box">SIPKL</div>
                    <h1 class="title">Halo ! Selamat Datang</h1>

                    <x-auth-session-status class="notice success" :status="session('status')" />

                    @if ($errors->any())
                        <div class="notice error">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="field">
                            <label for="login">Username / Email</label>
                            <input id="login" class="input" type="text" name="login"
                                value="{{ old('login') }}" required autofocus autocomplete="username"
                                placeholder="Masukkan username atau email">
                            <x-input-error :messages="$errors->get('login')" class="mt-2" />
                        </div>

                        <div class="field">
                            <label for="password">Password</label>
                            <input id="password" class="input" type="password" name="password" required
                                autocomplete="current-password" placeholder="............">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="helper-row">
                            <label class="remember" for="remember_me">
                                <input id="remember_me" type="checkbox" name="remember">
                                <span>Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="forgot" href="{{ route('password.request') }}">Reset Password!</a>
                            @endif
                        </div>

                        <button class="btn-login" type="submit">Login</button>
                    </form>

                    <p class="footer-text">
                        Dont Have an account?
                        <a href="{{ route('register') }}">Create Account</a>
                    </p>
                </div>
            </section>
        </section>
        </div>
</body>

</html>

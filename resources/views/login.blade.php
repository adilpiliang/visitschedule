<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | {{ config('app.name', 'MySchedule') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
</head>

<body>
    <div class="auth-shell">
        <div class="auth-card">
            <header>
                <div class="auth-brand">
                    My Schedule
                </div>
                <h1>Masuk ke Akun</h1>
                <p>Kelola jadwal dan tugas harian Anda dalam satu tempat.</p>
            </header>
            @if (session('status'))
            <p class="field-help" role="status">{{ session('status') }}</p>
            @endif
            <form class="form-stack" action="{{ route('login.attempt') }}" method="POST">
                @csrf
                <div class="field-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="nama@perusahaan.com" required>
                </div>
                <div class="field-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required>
                </div>
                <div class="form-meta">
                    <label class="checkbox" for="remember">
                        <input type="checkbox" id="remember" name="remember">
                        <span>Ingat saya</span>
                    </label>
                    <a class="link" href="#">Lupa kata sandi?</a>
                </div>
                <button type="submit" class="primary-button">
                    Masuk
                </button>
            </form>
            <div class="auth-footer">
                <a href="#">Hubungi Admin</a>,
                untuk meminta akun
            </div>
        </div>
    </div>
</body>

</html>
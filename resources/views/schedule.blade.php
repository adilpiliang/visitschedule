<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Dashboard') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/global.css') }}">
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    </head>
    <body>
        <div class="layout">
            @include('partials.sidebar')
            <main class="content-area">
                <header class="header">
                    <div>
                        <h1>Selamat datang, {{ auth()->user()->name ?? 'Pengguna' }}!</h1>
                        <p>Mari kita awali dengan basmalah</p>
                    </div>
                    <!-- <button class="cta-button">
                        + Tambah Jadwal
                    </button> -->
                </header>
            </main>
        </div>
    </body>
</html>
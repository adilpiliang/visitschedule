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
                    <h1>Data Sekolah</h1>
                    <p>ini adalah data sekolah di sekitar kampus</p>
                </div>
                <!-- <button class="cta-button">
                        + Tambah Jadwal
                    </button> -->
            </header>
            <section class="cards">
                <article class="card">
                    <h3>Kota Bogor</h3>
                    <strong>6 Kecamatan</strong>
                    <span>SMA, SMK, sederajat</span>
                </article>
                <article class="card">
                    <h3>Kabupaten Bogor</h3>
                    <strong>40 Kecamatan</strong>
                    <span>SMA, SMK, sederajat</span>
                </article>
                <article class="card">
                    <h3>Kota Lain</h3>
                    <strong>-</strong>
                    <span>SMA, SMK, sederajat</span>
                </article>
               
            </section>
        </main>
    </div>
</body>

</html>
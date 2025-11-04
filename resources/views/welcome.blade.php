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

                <section class="cards">
                    <article class="card">
                        <h3>Jadwal Hari Ini</h3>
                        <strong>4</strong>
                        <span>2 sudah selesai</span>
                    </article>
                    <article class="card">
                        <h3>Tugas Tertunda</h3>
                        <strong>6</strong>
                        <span>3 mendekati deadline</span>
                    </article>
                    <article class="card">
                        <h3>Agenda Minggu Ini</h3>
                        <strong>12</strong>
                        <span>+2 dibanding minggu lalu</span>
                    </article>
                    <article class="card">
                        <h3>Progress Tugas</h3>
                        <strong>68%</strong>
                        <span>Target 80% tercapai Jumat</span>
                    </article>
                </section>

                <section class="panels">
                    <article class="panel">
                        <h2>Jadwal Terdekat</h2>
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Agenda</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>08:30</td>
                                    <td>Daily Standup Tim</td>
                                    <td><span class="status ongoing">Berlangsung</span></td>
                                </tr>
                                <tr>
                                    <td>10:00</td>
                                    <td>Review Desain Aplikasi</td>
                                    <td><span class="status upcoming">Segera</span></td>
                                </tr>
                                <tr>
                                    <td>13:30</td>
                                    <td>Presentasi ke Klien</td>
                                    <td><span class="status upcoming">Segera</span></td>
                                </tr>
                                <tr>
                                    <td>16:00</td>
                                    <td>Persiapan Sprint Berikutnya</td>
                                    <td><span class="status finished">Selesai</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </article>
                    <article class="panel">
                        <h2>Tugas Prioritas</h2>
                        <div class="task-list">
                            <div class="task">
                                <div class="info">
                                    <strong>Update Roadmap Produk</strong>
                                    <span>Deadline: 09:00 - 15 Maret 2024</span>
                                </div>
                                <button>Selesai</button>
                            </div>
                            <div class="task">
                                <div class="info">
                                    <strong>Riset Kompetitor</strong>
                                    <span>Deadline: 17:00 - 16 Maret 2024</span>
                                </div>
                                <button>Detail</button>
                            </div>
                            <div class="task">
                                <div class="info">
                                    <strong>Pembagian Tugas Sprint</strong>
                                    <span>Deadline: 18:00 - 16 Maret 2024</span>
                                </div>
                                <button>Tandai</button>
                            </div>
                        </div>
                    </article>
                </section>
            </main>
        </div>
    </body>
</html>

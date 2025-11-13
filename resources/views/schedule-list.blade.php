<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Dashboard') }} — Jadwal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>

<body>
    @php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;
    @endphp
    <div class="layout">
        @include('partials.sidebar')
        <main class="content-area">
            <header class="header">
                <div>
                    <h1>Daftar Jadwal</h1>
                    <p>Ringkasan jadwal kunjungan terbaru</p>
                </div>
            </header>
            <section class="cards cards-selectable">
                <article class="card card-selectable">
                    <h3>Semua Jadwal</h3>
                    <strong>24</strong>
                    <span>4 sudah selesai</span>
                </article>
                <article class="card card-selectable">
                    <h3>Jadwal Mendatang</h3>
                    <strong>15</strong>
                    <span>3 dijadwalkan minggu ini</span>
                </article>
                <article class="card card-selectable">
                    <h3>Jadwal Selesai</h3>
                    <strong>9</strong>
                    <span>2 diselesaikan bulan ini</span>
                </article>
            </section>
            <section class="table-section">
                <div class="table-toolbar">
                    <div class="toolbar-heading">
                        <h3>Jadwal</h3>
                        <span>Jadwal kunjungan sekolah</span>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sekolah</th>
                            <th>Penanggung Jawab</th>
                            <th>Kota/Kabupaten</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $index => $schedule)
                        <tr>
                            <td>{{ $schedules->firstItem() + $index }}</td>
                            <td>{{ $schedule->school->name ?? '-' }}</td>
                            <td>{{ $schedule->school->pic ?? '-' }}</td>
                            <td>{{ $schedule->school->kota ?? '-' }}</td>
                            <td>{{ $schedule->visit_date ? Carbon::parse($schedule->visit_date)->translatedFormat('d F Y') : '-' }}</td>
                            <td>{{ $schedule->visit_time ? Str::substr($schedule->visit_time, 0, 5) : '-' }}</td>
                            <td>{{ $schedule->notes ?? '-' }}</td>
                            <td>
                                @if ($schedule->status !== 'completed')
                                <form method="POST" action="{{ route('schedule.complete', $schedule) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="button-primary button-small">Selesaikan Tugas</button>
                                </form>
                                @else
                                <span class="badge badge-status badge-status-completed">Selesai</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="empty-state">Belum ada jadwal.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="pagination-wrapper">
                    {{ $schedules->links() }}
                </div>
            </section>
        </main>
    </div>
</body>

</html>


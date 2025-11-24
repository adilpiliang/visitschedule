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

    $activeSection = request()->get('all_page')
        ? 'all'
        : (request()->get('week_page')
            ? 'week'
            : (request()->get('completed_page') ? 'completed' : 'all'));

    $sections = [
        [
            'key' => 'all',
            'title' => 'Semua Jadwal (belum selesai)',
            'subtitle' => 'Diurutkan dari jadwal terdekat',
            'rows' => $allSchedules ?? collect(),
            'showAction' => true,
            'empty' => 'Belum ada jadwal aktif.',
        ],
        [
            'key' => 'week',
            'title' => 'Dijadwalkan Minggu Ini',
            'subtitle' => 'Minggu berjalan, terdekat ke terjauh',
            'rows' => $weekSchedules ?? collect(),
            'showAction' => true,
            'empty' => 'Belum ada jadwal pada minggu ini.',
        ],
        [
            'key' => 'completed',
            'title' => 'Jadwal Selesai',
            'subtitle' => 'Urut terbaru diselesaikan',
            'rows' => $completedSchedules ?? collect(),
            'showAction' => false,
            'empty' => 'Belum ada jadwal yang selesai.',
        ],
    ];
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
                <article class="card card-selectable {{ $activeSection === 'all' ? 'is-active' : '' }}"
                    role="button"
                    tabindex="0"
                    aria-pressed="{{ $activeSection === 'all' ? 'true' : 'false' }}"
                    data-section-card="all">
                    <h3>Semua Jadwal</h3>
                    <strong>{{ $stats['all'] ?? 0 }}</strong>
                    <span>Belum selesai</span>
                </article>
                <article class="card card-selectable {{ $activeSection === 'week' ? 'is-active' : '' }}"
                    role="button"
                    tabindex="0"
                    aria-pressed="{{ $activeSection === 'week' ? 'true' : 'false' }}"
                    data-section-card="week">
                    <h3>Jadwal Minggu Ini</h3>
                    <strong>{{ $stats['week'] ?? 0 }}</strong>
                    <span>Terdekat ke terjauh</span>
                </article>
                <article class="card card-selectable {{ $activeSection === 'completed' ? 'is-active' : '' }}"
                    role="button"
                    tabindex="0"
                    aria-pressed="{{ $activeSection === 'completed' ? 'true' : 'false' }}"
                    data-section-card="completed">
                    <h3>Jadwal Selesai</h3>
                    <strong>{{ $stats['completed'] ?? 0 }}</strong>
                    <span>Urut selesai terbaru</span>
                </article>
            </section>
            @foreach ($sections as $section)
                <section class="table-section"
                    aria-labelledby="section-{{ $section['key'] }}"
                    data-section-panel="{{ $section['key'] }}"
                    @if ($activeSection !== $section['key']) hidden @endif>
                    <div class="table-toolbar">
                        <div class="toolbar-heading">
                            <h3 id="section-{{ $section['key'] }}">{{ $section['title'] }}</h3>
                            <span>{{ $section['subtitle'] }}</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Penanggung Jawab</th>
                                    <th>Kota/Kabupaten</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Catatan</th>
                                    <th>Status</th>
                                    @if ($section['showAction'])
                                        <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($section['rows'] as $index => $schedule)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $schedule->school->name ?? '-' }}</td>
                                        <td>{{ $schedule->school->pic ?? '-' }}</td>
                                        <td>{{ $schedule->school->kota ?? '-' }}</td>
                                        <td>{{ $schedule->visit_date ? Carbon::parse($schedule->visit_date)->translatedFormat('d F Y') : '-' }}</td>
                                        <td>{{ $schedule->visit_time ? Str::substr($schedule->visit_time, 0, 5) : '-' }}</td>
                                        <td>{{ $schedule->notes ?? '-' }}</td>
                                        <td>
                                            @if ($schedule->status === 'completed')
                                                <span class="badge badge-status badge-status-completed">Selesai</span>
                                            @else
                                                <span class="badge badge-status">{{ ucfirst($schedule->status) }}</span>
                                            @endif
                                        </td>
                                        @if ($section['showAction'])
                                            <td>
                                                @if ($schedule->status !== 'completed')
                                                    <form method="POST" action="{{ route('schedule.complete', $schedule) }}" data-complete-confirm>
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="button-primary button-small">Selesaikan Tugas</button>
                                                    </form>
                                                @else
                                                    <span class="badge badge-status badge-status-completed">Selesai</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $section['showAction'] ? 9 : 8 }}" class="empty-state">{{ $section['empty'] }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-complete-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (!window.confirm('Apakah kamu yakin tugas sudah terselesaikan?')) {
                        event.preventDefault();
                    }
                });
            });

            const cards = document.querySelectorAll('[data-section-card]');
            const panels = document.querySelectorAll('[data-section-panel]');
            const setActive = (key) => {
                cards.forEach((card) => {
                    const isActive = card.dataset.sectionCard === key;
                    card.classList.toggle('is-active', isActive);
                    card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
                panels.forEach((panel) => {
                    const shouldShow = panel.dataset.sectionPanel === key;
                    panel.hidden = !shouldShow;
                });
            };

            cards.forEach((card) => {
                card.addEventListener('click', () => setActive(card.dataset.sectionCard));
                card.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        setActive(card.dataset.sectionCard);
                    }
                });
            });
        });
    </script>
</body>

</html>

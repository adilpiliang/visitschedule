@php
$navItems = [
[
'label' => 'Dashboard',
'route' => 'dashboard',
'match' => ['dashboard'],
'icon' => <<<'SVG'
    <svg viewBox="0 0 24 24" fill="none">
    <path d="M4 6h16M4 12h16M4 18h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
    </svg>
    SVG,
    ],
    [
    'label' => 'Kalender',
    'route' => 'schedule',
    'href' => null,
    'match' => ['schedule'],
    'icon' => <<<'SVG'
        <svg viewBox="0 0 24 24" fill="none">
        <path d="M7 4h10a2 2 0 0 1 2 2v12l-7-3-7 3V6a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
        </svg>
        SVG,
        ],
        [
        'label' => 'Jadwal',
        'route' => 'schedule.list',
        'match' => ['schedule.list'],
        'icon' => <<<'SVG'
            <svg viewBox="0 0 24 24" fill="none">
            <path d="M5 5h14v14H5zM5 9h14M9 5v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            SVG,
            ],
            [
            'label' => 'Sekolah',
            'route' => 'schools.index',
            'match' => ['schools.*'],
            'icon' => <<<'SVG'
                <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
                SVG,
                ],
                [
                'label' => 'Penanggung Jawab',
                'route' => null,
                'href' => '#',
                'match' => ['settings', 'settings.*'],
                'icon' => <<<'SVG'
                    <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="1.8" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 8.6 15a1.65 1.65 0 0 0-1.82-.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 15.4 9a1.65 1.65 0 0 0 1.82.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 15z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    SVG,
                    ],
                    ];
                    @endphp

                    <aside class="sidebar">
                        <div class="brand">
                            SchoolVisit<br><span>Schedule</span>
                        </div>
                        <div class="nav-group">
                            <div class="nav-label">Menu</div>
                            @foreach ($navItems as $item)
                            @php
                            $patterns = (array) ($item['match'] ?? []);
                            $isActive = false;
                            foreach ($patterns as $pattern) {
                            if (request()->routeIs($pattern)) {
                            $isActive = true;
                            break;
                            }
                            }
                            $href = $item['route'] ? route($item['route']) : ($item['href'] ?? '#');
                            @endphp
                            <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $href }}">
                                {!! $item['icon'] !!}
                                {{ $item['label'] }}
                            </a>
                            @endforeach
                        </div>
                        <div class="sidebar-footer">
                            Masuk sebagai <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong>
                            <br>
                            Terakhir diperbarui {{ now()->format('d M Y') }}
                        </div>
                    </aside>
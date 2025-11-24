@php
$navItems = [
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
        'route' => 'schedule.pic',
        'href' => null,
        'match' => ['schedule.pic'],
        'icon' => <<<'SVG'
            <svg viewBox="0 0 24 24" fill="none">
                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" stroke="currentColor" stroke-width="1.8" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 8.6 15a1.65 1.65 0 0 0-1.82-.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 15.4 9a1.65 1.65 0 0 0 1.82.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 15z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        SVG,
    ],
];

$currentUser = session('user');
@endphp

<button type="button" class="sidebar-toggle" aria-label="Buka menu" data-sidebar-toggle>
    <span></span>
    <span></span>
    <span></span>
</button>
<aside class="sidebar" data-sidebar>
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
        Masuk sebagai <strong>{{ $currentUser['username'] ?? 'Pengguna' }}</strong>
        <br>
        Terakhir diperbarui {{ now()->format('d M Y') }}
        <form method="POST" action="{{ route('logout') }}" style="margin-top: 12px;">
            @csrf
            <button type="submit" class="button-secondary" style="width: 100%;">Keluar</button>
        </form>
        <div style="margin-top: 12px; font-size: 12px; color: #6b7280;">
            &copy; fadil - 24 November 2025
        </div>
    </div>
</aside>
<div class="sidebar-overlay" data-sidebar-overlay></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('[data-sidebar]');
        const toggle = document.querySelector('[data-sidebar-toggle]');
        const overlay = document.querySelector('[data-sidebar-overlay]');

        if (!sidebar || !toggle) {
            return;
        }

        const closeOnMobile = () => window.innerWidth <= 1024;

        const setOpenState = (isOpen) => {
            sidebar.classList.toggle('is-open', isOpen);
            toggle.classList.toggle('is-active', isOpen);
            if (overlay) {
                overlay.classList.toggle('is-visible', isOpen);
            }
            document.body.classList.toggle('sidebar-open', isOpen);
        };

        toggle.addEventListener('click', () => {
            const shouldOpen = !sidebar.classList.contains('is-open');
            setOpenState(shouldOpen);
        });

        if (overlay) {
            overlay.addEventListener('click', () => setOpenState(false));
        }

        sidebar.querySelectorAll('.nav-link').forEach((link) => {
            link.addEventListener('click', () => {
                if (closeOnMobile()) {
                    setOpenState(false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
                setOpenState(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024 && sidebar.classList.contains('is-open')) {
                setOpenState(false);
            }
        });
    });
</script>

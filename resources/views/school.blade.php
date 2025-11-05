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
            @php
                $filters = $filters ?? ['scope' => 'kota-bogor', 'search' => '', 'status' => '', 'kecamatan' => ''];
                $currentScope = $filters['scope'] ?? 'kota-bogor';
                $selectedKecamatan = $filters['kecamatan'] ?? '';
                $kecamatanOptions = isset($kecamatanOptions) ? collect($kecamatanOptions) : collect();
                $showDropdown = $showKecamatanDropdown ?? false;
                $queryBuilder = function (array $overrides = []) use ($filters) {
                    $base = array_merge(['scope' => $filters['scope'] ?? 'kota-bogor'], $filters);
                    $query = array_merge($base, $overrides);
                    return array_filter($query, fn ($value) => !is_null($value) && $value !== '');
                };
            @endphp
            <header class="header">
                <div>
                    <h1>Data Sekolah</h1>
                    <p>ini adalah data sekolah di sekitar kampus</p>
                </div>
                <!-- <button class="cta-button">
                        + Tambah Jadwal
                    </button> -->
            </header>
            <section class="cards cards-selectable">
                @foreach (($cardStats ?? []) as $card)
                    <a href="{{ $card['href'] }}" class="card card-selectable {{ $card['active'] ? 'is-active' : '' }}">
                        <h3>{{ $card['label'] }}</h3>
                        <strong>{{ $card['stat'] }} {{ $card['stat_label'] }}</strong>
                        <span>{{ $card['description'] }}</span>
                    </a>
                @endforeach
                <article class="card card-summary">
                    <h3>Total Sekolah</h3>
                    <strong>{{ $totalSchools ?? $schools->count() }}</strong>
                    <span>data sekolah yang sudah terdata</span>
                </article>
            </section>
            <section class="table-section">
                <section class="table-toolbar">
                    <div class="toolbar-heading">
                        <h3>Daftar Sekolah — {{ $currentScopeLabel ?? 'Kota Bogor' }}</h3>
                        <span class="toolbar-subtitle">{{ $tableDescription ?? '' }}</span>
                    </div>
                    @if ($showDropdown)
                        <div class="toolbar-controls">
                            <div class="dropdown" data-dropdown>
                                <button class="dropdown-button" type="button" data-dropdown-trigger aria-haspopup="true" aria-expanded="false">
                                    <span class="dropdown-label">
                                        {{ $selectedKecamatan !== '' ? $selectedKecamatan : 'Semua Kecamatan' }}
                                    </span>
                                    <svg viewBox="0 0 24 24" fill="none" class="dropdown-icon" aria-hidden="true">
                                        <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <div class="dropdown-menu" role="menu" data-dropdown-menu>
                                    <a href="{{ route('schools.index', $queryBuilder(['kecamatan' => ''])) }}" class="dropdown-item {{ $selectedKecamatan === '' ? 'is-active' : '' }}" role="menuitem">
                                        Semua Kecamatan
                                    </a>
                                    @forelse ($kecamatanOptions as $option)
                                        <a href="{{ route('schools.index', $queryBuilder(['kecamatan' => $option])) }}"
                                            class="dropdown-item {{ strcasecmp($selectedKecamatan, $option) === 0 ? 'is-active' : '' }}"
                                            role="menuitem">
                                            {{ $option }}
                                        </a>
                                    @empty
                                        <span class="dropdown-item is-disabled">Data kecamatan belum tersedia</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                </section>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kota</th>
                            <th>Kecamatan</th>
                            <th>Kelurahan</th>
                            <th>Alamat</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>PIC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schools as $index => $school)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $school->name }}</td>
                            <td>{{ $school->kota ?? '-' }}</td>
                            <td>{{ $school->kecamatan ?? '-' }}</td>
                            <td>{{ $school->kelurahan ?? '-' }}</td>
                            <td>{{ $school->address ?? '-' }}</td>
                            <td>
                                @if ($school->contact)
                                <a href="mailto:{{ $school->contact }}" class="contact-link">
                                    {{ $school->contact }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td><span class="badge badge-status">{{ $school->status ?? '-' }}</span></td>
                            <td>{{ $school->pic ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="empty-state">Belum ada data sekolah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdowns = document.querySelectorAll('[data-dropdown]');

            dropdowns.forEach((dropdown) => {
                const trigger = dropdown.querySelector('[data-dropdown-trigger]');
                const menu = dropdown.querySelector('[data-dropdown-menu]');

                if (!trigger || !menu) {
                    return;
                }

                const closeMenu = () => {
                    dropdown.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                };

                const openMenu = () => {
                    dropdown.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                };

                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    const isOpen = dropdown.classList.contains('is-open');
                    if (isOpen) {
                        closeMenu();
                    } else {
                        dropdowns.forEach((other) => {
                            if (other !== dropdown) {
                                other.classList.remove('is-open');
                                const otherTrigger = other.querySelector('[data-dropdown-trigger]');
                                if (otherTrigger) {
                                    otherTrigger.setAttribute('aria-expanded', 'false');
                                }
                            }
                        });
                        openMenu();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!dropdown.contains(event.target)) {
                        closeMenu();
                    }
                });

                dropdown.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMenu();
                        trigger.focus();
                    }
                });
            });
        });
    </script>
</body>

</html>

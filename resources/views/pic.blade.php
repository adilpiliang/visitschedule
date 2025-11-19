@php
$picCards = collect($picCards ?? []);
$cardLabels = $picCards->mapWithKeys(fn ($card) => [$card['key'] => $card['label']])->all();
$firstCard = $picCards->first();
$firstCardKey = is_array($firstCard) ? ($firstCard['key'] ?? null) : null;
$firstCardLabel = is_array($firstCard) ? ($firstCard['label'] ?? null) : null;
$defaultPicKey = $defaultPic ?? $firstCardKey;
$defaultCardLabel = $defaultPicKey && isset($cardLabels[$defaultPicKey])
    ? $cardLabels[$defaultPicKey]
    : ($firstCardLabel ?? 'Penanggung Jawab');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Dashboard') }} — Penanggung Jawab</title>
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
                    <h1>Penanggung Jawab Kunjungan</h1>
                    <p>Pilih tim PIC untuk melihat daftar kunjungan sekolah yang mereka tangani.</p>
                </div>
            </header>

            <section class="cards cards-selectable">
                @if ($picCards->isEmpty())
                <p class="empty-state" style="width:100%;">Belum ada penanggung jawab yang memiliki jadwal.</p>
                @else
                @foreach ($picCards as $card)
                <article class="card card-selectable" data-pic-card data-pic-key="{{ $card['key'] }}">
                    <h3>{{ $card['label'] }}</h3>
                    <strong>{{ $card['count'] }}</strong>
                    <span>{{ $card['count'] }} jadwal aktif</span>
                </article>
                @endforeach
                @endif
            </section>

            <section class="table-section">
                <div class="table-toolbar">
                    <div class="toolbar-heading">
                        <h3 data-pic-table-title>Jadwal {{ $defaultCardLabel }}</h3>
                        <span data-pic-table-subtitle>Pilih tim untuk menampilkan jadwal mereka.</span>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sekolah</th>
                            <th>Kota/Kabupaten</th>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody data-pic-table-body>
                        <tr>
                            <td colspan="7" class="empty-state">Pilih tim pada kartu di atas untuk melihat jadwal.</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('[data-pic-card]');
            const tableBody = document.querySelector('[data-pic-table-body]');
            const titleEl = document.querySelector('[data-pic-table-title]');
            const subtitleEl = document.querySelector('[data-pic-table-subtitle]');
            const cardLabels = @json($cardLabels);
            const scheduleData = @json($scheduleData);
            const defaultPicKey = @json($defaultPicKey);
            const schedulesByPic = scheduleData.reduce((acc, schedule) => {
                const key = schedule.pic;
                if (!acc[key]) {
                    acc[key] = [];
                }
                acc[key].push(schedule);
                return acc;
            }, {});

            const statusBadgeClass = {
                pending: 'badge-status-pending',
                confirmed: 'badge-status-scheduled',
                completed: 'badge-status-completed',
            };

            const escapeHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            };

            const renderTable = (picKey) => {
                const rows = schedulesByPic[picKey] || [];
                const label = cardLabels[picKey] || 'Penanggung Jawab';
                titleEl.textContent = `Jadwal ${label}`;
                subtitleEl.textContent = rows.length ?
                    `${rows.length} jadwal aktif untuk ${label}.` :
                    'Belum ada jadwal untuk tim ini.';

                if (rows.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="7" class="empty-state">Belum ada jadwal untuk tim ini.</td></tr>`;
                    return;
                }

                tableBody.innerHTML = rows.map((row, index) => {
                    const badgeClass = statusBadgeClass[row.status] || '';
                    const badge = `<span class="badge ${badgeClass}">${escapeHtml(row.status_label)}</span>`;

                    return `<tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(row.school)}</td>
                        <td>${escapeHtml(row.city)}</td>
                        <td>${escapeHtml(row.visit_date)}</td>
                        <td>${escapeHtml(row.visit_time)}</td>
                        <td>${badge}</td>
                        <td>${escapeHtml(row.notes)}</td>
                    </tr>`;
                }).join('');
            };

            const setActiveCard = (picKey) => {
                cards.forEach((card) => {
                    const isActive = card.dataset.picKey === picKey;
                    card.classList.toggle('is-active', isActive);
                });
            };

            cards.forEach((card) => {
                card.addEventListener('click', () => {
                    const picKey = card.dataset.picKey;
                    setActiveCard(picKey);
                    renderTable(picKey);
                });
            });

            if (cards.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="7" class="empty-state">Belum ada jadwal yang dapat ditampilkan.</td></tr>`;
                subtitleEl.textContent = 'Tambah jadwal terlebih dahulu untuk melihat data berdasarkan PIC.';
                return;
            }

            const initialCard = (defaultPicKey && cardLabels[defaultPicKey])
                ? Array.from(cards).find((card) => card.dataset.picKey === defaultPicKey)
                : cards[0];

            if (initialCard) {
                const initialKey = initialCard.dataset.picKey;
                setActiveCard(initialKey);
                renderTable(initialKey);
            }
        });
    </script>
</body>

</html>

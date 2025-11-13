<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
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
                        <h1>Kalender Kunjungan</h1>
                        <p>Pantau agenda kunjungan sekolah dalam tampilan bulanan</p>
                    </div>
                    <div class="calendar-actions">
                        <button class="calendar-pill-btn" data-calendar-action="today">Hari Ini</button>
                        <div class="calendar-nav">
                            <button class="calendar-icon-btn" data-calendar-action="prev" aria-label="Bulan sebelumnya">
                                <span aria-hidden="true">&lsaquo;</span>
                            </button>
                            <button class="calendar-icon-btn" data-calendar-action="next" aria-label="Bulan selanjutnya">
                                <span aria-hidden="true">&rsaquo;</span>
                            </button>
                        </div>
                    </div>
                </header>

                <section class="calendar-app" id="calendar-app">
                    <div class="calendar-toolbar">
                        <div class="calendar-toolbar-title" data-calendar-current-month>Februari 2025</div>
                        <div class="calendar-toolbar-subtitle" data-calendar-range-label></div>
                    </div>
                    <div class="calendar-view">
                        <div class="calendar-weekdays" data-calendar-weekdays></div>
                        <div class="calendar-grid" data-calendar-grid></div>
                    </div>
                </section>
            </main>
        </div>

        <div class="modal-overlay" data-calendar-detail-modal>
            <div class="modal-window modal-calendar-window" role="dialog" aria-modal="true" aria-labelledby="calendar-detail-title">
                <div class="modal-header">
                    <div>
                        <p class="modal-label">Jadwal tanggal</p>
                        <h2 id="calendar-detail-title" class="modal-title" data-calendar-detail-title>--</h2>
                    </div>
                    <button class="modal-close" type="button" data-calendar-detail-close aria-label="Tutup">&times;</button>
                </div>
                <div class="calendar-detail-list" data-calendar-detail-body>
                    <p class="empty-state">Tidak ada jadwal pada tanggal ini.</p>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const app = document.getElementById('calendar-app');
                if (!app) {
                    return;
                }

                const currentMonthEl = app.querySelector('[data-calendar-current-month]');
                const rangeLabelEl = app.querySelector('[data-calendar-range-label]');
                const weekdaysEl = app.querySelector('[data-calendar-weekdays]');
                const gridEl = app.querySelector('[data-calendar-grid]');
                const actionButtons = document.querySelectorAll('[data-calendar-action]');

                const events = @json($events ?? []);
                const csrfToken = '{{ csrf_token() }}';
                const detailModal = document.querySelector('[data-calendar-detail-modal]');
                const detailBody = detailModal ? detailModal.querySelector('[data-calendar-detail-body]') : null;
                const detailTitle = detailModal ? detailModal.querySelector('[data-calendar-detail-title]') : null;

                const weekdayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                let viewDate = new Date();

                const formatDateKey = (date) => {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                const formatMonthLabel = (date) =>
                    date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });

                const formatRangeLabel = (start, end) => {
                    const options = { day: 'numeric', month: 'short' };
                    const startLabel = start.toLocaleDateString('id-ID', options);
                    const endLabel = end.toLocaleDateString('id-ID', options);
                    if (start.getMonth() === end.getMonth()) {
                        return `${startLabel} – ${endLabel}`;
                    }
                    return `${startLabel} – ${endLabel}`;
                };

                const extractTimeLabel = (datetime) => {
                    const date = new Date(datetime);
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                };

                const fullDateLabel = (dateKey) => {
                    const date = new Date(dateKey);
                    return date.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    });
                };

                const eventsByDate = events.reduce((acc, event) => {
                    const key = event.start.split('T')[0];
                    const bucket = acc.get(key) ?? [];
                    bucket.push(event);
                    bucket.sort((a, b) => new Date(a.start) - new Date(b.start));
                    acc.set(key, bucket);
                    return acc;
                }, new Map());

                const renderDetailItems = (dateKey, dayEvents) => {
                    if (!detailBody || !detailModal || !detailTitle) {
                        return;
                    }

                    if (!dayEvents.length) {
                        detailBody.innerHTML = '<p class="empty-state">Tidak ada jadwal pada tanggal ini.</p>';
                    } else {
                        detailBody.innerHTML = dayEvents.map((event) => {
                            const timeLabel = extractTimeLabel(event.start);
                            const notes = event.notes ? `<p class="calendar-detail-notes">${event.notes}</p>` : '';
                            const picValue = typeof event.pic === 'string' ? event.pic.trim() : '';
                            const picInfo = `<p class="calendar-detail-meta">Penanggung Jawab: ${picValue !== '' ? picValue : '-'}</p>`;
                            const action = event.status === 'completed'
                                ? '<span class="badge badge-status badge-status-completed">Selesai</span>'
                                : `
                                    <form method="POST" action="${event.complete_url}" class="calendar-complete-form">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="PATCH">
                                        <button type="submit" class="button-primary button-small">Selesaikan</button>
                                    </form>
                                  `;

                            return `
                                <article class="calendar-detail-item">
                                    <div class="calendar-detail-info">
                                        <h3>${event.school}</h3>
                                        <p>${timeLabel} • ${event.title}</p>
                                        ${picInfo}
                                        ${notes}
                                    </div>
                                    <div class="calendar-detail-action">
                                        ${action}
                                    </div>
                                </article>
                            `;
                        }).join('');
                    }

                    detailTitle.textContent = fullDateLabel(dateKey);
                    detailModal.classList.add('is-visible');
                };

                const closeDetailModal = () => {
                    if (detailModal) {
                        detailModal.classList.remove('is-visible');
                    }
                };

                if (detailModal) {
                    detailModal.addEventListener('click', (event) => {
                        if (event.target === detailModal || event.target.closest('[data-calendar-detail-close]')) {
                            closeDetailModal();
                        }
                    });

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape' && detailModal.classList.contains('is-visible')) {
                            closeDetailModal();
                        }
                    });
                }

                const renderWeekdays = () => {
                    weekdaysEl.innerHTML = weekdayNames
                        .map((name) => `<div class="calendar-weekday">${name}</div>`)
                        .join('');
                };

                const renderMonth = () => {
                    const year = viewDate.getFullYear();
                    const month = viewDate.getMonth();
                    const firstDayOfMonth = new Date(year, month, 1);
                    const lastDayOfMonth = new Date(year, month + 1, 0);

                    const offsetToMonday = (firstDayOfMonth.getDay() + 6) % 7;
                    const startDate = new Date(firstDayOfMonth);
                    startDate.setDate(firstDayOfMonth.getDate() - offsetToMonday);

                    currentMonthEl.textContent = formatMonthLabel(viewDate);
                    rangeLabelEl.textContent = formatRangeLabel(startDate, lastDayOfMonth);

                    const todayKey = formatDateKey(new Date());
                    gridEl.innerHTML = '';

                    for (let index = 0; index < 42; index++) {
                        const cellDate = new Date(startDate);
                        cellDate.setDate(startDate.getDate() + index);
                        const cellKey = formatDateKey(cellDate);
                        const isOtherMonth = cellDate.getMonth() !== month;
                        const isToday = cellKey === todayKey;
                        const dayNumber = cellDate.getDate();

                        const cell = document.createElement('div');
                        cell.className = 'calendar-cell';
                        if (isOtherMonth) {
                            cell.classList.add('is-outside');
                        }
                        if (isToday) {
                            cell.classList.add('is-today');
                        }

                        const header = document.createElement('div');
                        header.className = 'calendar-cell-header';
                        header.innerHTML = `<span class="calendar-cell-day">${dayNumber}</span>`;
                        cell.appendChild(header);

                        const eventWrapper = document.createElement('div');
                        eventWrapper.className = 'calendar-events';

                        const dayEvents = eventsByDate.get(cellKey) ?? [];
                        dayEvents.slice(0, 3).forEach((event) => {
                            const eventEl = document.createElement('div');
                            eventEl.className = 'calendar-event';
                            eventEl.style.setProperty('--event-color', event.color);
                            eventEl.innerHTML = `
                                <span class="calendar-event-dot" style="background: var(--event-color);"></span>
                                <span class="calendar-event-title">
                                    ${extractTimeLabel(event.start)} • ${event.title}
                                </span>
                            `;
                            eventWrapper.appendChild(eventEl);
                        });

                        if (dayEvents.length > 3) {
                            const moreCount = dayEvents.length - 3;
                            const moreEl = document.createElement('div');
                            moreEl.className = 'calendar-event-more';
                            moreEl.textContent = `+${moreCount} lainnya`;
                            eventWrapper.appendChild(moreEl);
                        }

                        if (!dayEvents.length) {
                            const placeholder = document.createElement('div');
                            placeholder.className = 'calendar-event-placeholder';
                            eventWrapper.appendChild(placeholder);
                        } else {
                            cell.classList.add('has-events');
                            cell.addEventListener('click', () => renderDetailItems(cellKey, dayEvents));
                        }

                        cell.appendChild(eventWrapper);
                        gridEl.appendChild(cell);
                    }
                };

                actionButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const action = button.getAttribute('data-calendar-action');
                        if (action === 'prev') {
                            viewDate.setMonth(viewDate.getMonth() - 1);
                        } else if (action === 'next') {
                            viewDate.setMonth(viewDate.getMonth() + 1);
                        } else if (action === 'today') {
                            viewDate = new Date();
                        }
                        renderMonth();
                    });
                });

                renderWeekdays();
                renderMonth();
            });
        </script>
    </body>
</html>


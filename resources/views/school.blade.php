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
                @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
                @endif
            </header>
            <section class="cards cards-selectable">
                @foreach (($cardStats ?? []) as $card)
                <a href="{{ $card['href'] }}" class="card card-selectable {{ $card['active'] ? 'is-active' : '' }}">
                    <h3>{{ $card['label'] }}</h3>
                    <strong>{{ $card['stat'] }} {{ $card['stat_label'] }}</strong>
                    <span>{{ $card['description'] }}</span>
                </a>
                @endforeach
                <article class="card card-selectable">
                    <h3>Terjadwal</h3>
                    <strong>34</strong>
                    <span>jadwal kunjungan yang telah dibuat</span>
                </article>
                <article class="card card-summary">
                    <h3>Total Sekolah</h3>
                    <strong>{{ $totalSchools ?? $schools->count() }}</strong>
                    <span>data sekolah yang sudah terdata</span>
                </article>
            </section>
            <section class="search-section">
                <form method="GET" action="{{ route('schools.index') }}" class="search-form">
                    <div class="search-input-group">
                        <input id="search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari sekolah..." aria-label="Cari sekolah">
                        <button type="submit" class="search-button" aria-label="Cari">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" />
                                <path d="M16.6569 16.6569L20.0001 20.0001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                </form>
                <button type="button" class="button-primary add-school-button" data-add-school-button>
                    + Tambah Sekolah
                </button>
            </section>
            <section class="table-section">
                <section class="table-toolbar">
                    <div class="toolbar-heading">
                        <h3>Daftar Sekolah — {{ $currentScopeLabel ?? 'Kota Bogor' }}</h3>
                        <span class="toolbar-subtitle">{{ $tableDescription ?? '' }}</span>
                    </div>
                    @if ($showKecamatanDropdown ?? false)
                    <div class="toolbar-controls">
                        <div class="dropdown" data-dropdown>
                            <button class="dropdown-button" type="button" data-dropdown-trigger aria-haspopup="true" aria-expanded="false">
                                <span class="dropdown-label">
                                    {{ ($selectedKecamatan ?? '') !== '' ? $selectedKecamatan : 'Semua Kecamatan' }}
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" class="dropdown-icon" aria-hidden="true">
                                    <path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <div class="dropdown-menu" role="menu" data-dropdown-menu>
                                <a href="{{ route('schools.index', $routeBuilder(['kecamatan' => ''])) }}" class="dropdown-item {{ ($selectedKecamatan ?? '') === '' ? 'is-active' : '' }}" role="menuitem">
                                    Semua Kecamatan
                                </a>
                                @forelse (($kecamatanOptions ?? collect()) as $option)
                                <a href="{{ route('schools.index', $routeBuilder(['kecamatan' => $option])) }}"
                                    class="dropdown-item {{ strcasecmp($selectedKecamatan ?? '', $option) === 0 ? 'is-active' : '' }}"
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
                <div class="table-responsive">
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
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Aksi</th>
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
                            <td>
                                @if (!empty($school->maps) && !empty($school->address))
                                <a class="contact-link" href="{{ $school->maps }}" target="_blank" rel="noopener noreferrer">
                                    {{ $school->address }}
                                </a>
                                @else
                                {{ $school->address ?? '-' }}
                                @endif
                            </td>
                            <td>
                                @if ($school->contact)
                                <a href="https://wa.me/{{ $school->contact }}"
                                    class="contact-link"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                    {{ $school->contact }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                            <td class="schedule-cell">
                                @if (($school->schedules_count ?? 0) === 0)
                                <button
                                    type="button"
                                    class="schedule-button"
                                    data-school-id="{{ $school->id }}"
                                    data-school-name="{{ $school->name }}"
                                    data-school-pic="{{ $school->pic ?? '' }}"
                                    aria-label="Tambah jadwal untuk {{ $school->name }}">
                                    +
                                </button>
                                @else
                                @if ($school->latest_schedule_payload)
                                <button type="button"
                                    class="schedule-detail-button"
                                    data-schedule-detail='@json($school->latest_schedule_payload)'
                                    aria-label="Lihat detail jadwal {{ $school->name }}">
                                    Lihat Detail
                                </button>
                                @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $school->status_badge_class }}">{{ $school->status ?? '-' }}</span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('schools.destroy', $school) }}" data-school-delete-form data-school-name="{{ $school->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button-danger button-small">Hapus</button>
                                </form>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="empty-state">Belum ada data sekolah.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </section>
        </main>
    </div>

    <div class="modal-overlay {{ $errors->any() ? 'is-visible' : '' }}" data-schedule-modal @if ($errors->any()) data-open-on-load="true" @endif>
        <div class="modal-window" role="dialog" aria-modal="true" aria-labelledby="schedule-modal-title">
            <div class="modal-header">
                <div>
                    <p class="modal-label">Tambah Jadwal</p>
                    <h2 id="schedule-modal-title" class="modal-title" data-modal-school>{{ $oldSchoolName ?? 'Pilih sekolah' }}</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Tutup" data-modal-close>&times;</button>
            </div>
            <form method="POST" action="{{ route('schedule.store') }}" class="modal-form">
                @csrf
                <input type="hidden" name="school_id" value="{{ old('school_id') }}">

                <div class="form-group">
                    <label for="visit_date">Tanggal Kunjungan</label>
                    <div class="input-field input-field--icon">
                        <input id="visit_date" type="date" name="visit_date" value="{{ old('visit_date') }}" placeholder="dd/mm/yyyy" required>
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                                <rect x="3" y="5" width="18" height="16" rx="3" stroke="currentColor" stroke-width="1.5" />
                                <path d="M16 3v4M8 3v4M3 11h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                        </span>
                    </div>
                    @error('visit_date')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="visit_time">Waktu (opsional)</label>
                    <div class="input-field input-field--icon">
                        <input id="visit_time" type="time" name="visit_time" value="{{ old('visit_time') }}" placeholder="--:--">
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5" />
                                <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                    @error('visit_time')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pic">Penanggung Jawab</label>
                    <div class="input-field input-field--icon select-field">
                        <select id="pic" name="pic" required>
                            <option value="">Pilih Penanggung Jawab</option>
                            @foreach ($picOptions as $option)
                            <option value="{{ $option }}" {{ old('pic') === $option ? 'selected' : '' }}>
                                {{ $option }}
                            </option>
                            @endforeach
                        </select>
                        <span class="input-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" width="18" height="18">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                    </div>
                    @error('pic')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Contoh: Sosialisasi kurikulum, membawa materi brosur.">{{ old('notes') }}</textarea>
                    @error('notes')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                @error('school_id')
                <span class="form-error">{{ $message }}</span>
                @enderror

                <div class="modal-actions">
                    <button type="button" class="button-secondary" data-modal-close>Batal</button>
                    <button type="submit" class="button-primary">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay {{ $openCreateSchoolModal ? 'is-visible' : '' }}" data-create-school-modal @if ($openCreateSchoolModal) data-open-on-load="true" @endif>
        <div class="modal-window" role="dialog" aria-modal="true" aria-labelledby="school-create-modal-title">
            <div class="modal-header">
                <div>
                    <p class="modal-label">Tambah Sekolah</p>
                    <h2 id="school-create-modal-title" class="modal-title">Data Sekolah Baru</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Tutup" data-school-modal-close>&times;</button>
            </div>
            <form method="POST" action="{{ route('schools.store') }}" class="modal-form">
                @csrf
                <div class="form-group">
                    <label for="school_name">Nama Sekolah</label>
                    <input id="school_name" type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: MAN 2 Bogor" required>
                    @error('name', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_city">Kota/Kabupaten</label>
                    <input id="school_city" type="text" name="kota" value="{{ old('kota') }}" placeholder="Contoh: KOTA BOGOR" required>
                    @error('kota', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_district">Kecamatan</label>
                    <input id="school_district" type="text" name="kecamatan" value="{{ old('kecamatan') }}" placeholder="Contoh: TANAH SAREAL" required>
                    @error('kecamatan', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_kelurahan">Kelurahan (opsional)</label>
                    <input id="school_kelurahan" type="text" name="kelurahan" value="{{ old('kelurahan') }}" placeholder="Contoh: Kebon Pedes">
                    @error('kelurahan', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_address">Alamat</label>
                    <textarea id="school_address" name="address" rows="3" placeholder="Cantumkan alamat lengkap sekolah" required>{{ old('address') }}</textarea>
                    @error('address', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_contact">Nomor Telepon (opsional)</label>
                    <input id="school_contact" type="tel" name="contact" value="{{ old('contact') }}" placeholder="Contoh: 081234567890">
                    <p class="form-hint">Nomor akan otomatis dikonversi ke format 62...</p>
                    @error('contact', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="school_maps">Link Maps (opsional)</label>
                    <input id="school_maps" type="url" name="maps" value="{{ old('maps') }}" placeholder="cantumkan link google maps">
                    @error('maps', 'schoolStore')
                    <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" class="button-secondary" data-school-modal-close>Batal</button>
                    <button type="submit" class="button-primary">Simpan Sekolah</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" data-schedule-detail-modal>
        <div class="modal-window" role="dialog" aria-modal="true" aria-labelledby="schedule-detail-modal-title">
            <div class="modal-header">
                <div>
                    <p class="modal-label">Detail Jadwal</p>
                    <h2 id="schedule-detail-modal-title" class="modal-title" data-detail-school>Nama Sekolah</h2>
                </div>
                <button class="modal-close" type="button" aria-label="Tutup" data-detail-close>&times;</button>
            </div>
            <div class="modal-details">
                <div class="detail-row">
                    <span class="detail-label">Tanggal</span>
                    <span class="detail-value" data-detail-date>-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Waktu</span>
                    <span class="detail-value" data-detail-time>-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Penanggung Jawab</span>
                    <span class="detail-value" data-detail-pic>-</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Catatan</span>
                    <span class="detail-value" data-detail-notes>-</span>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="button-secondary" data-detail-close>Tutup</button>
                <button type="button" class="button-primary" data-detail-edit>Edit Jadwal</button>
                <button type="button" class="button-danger" data-detail-delete>Hapus Jadwal</button>
            </div>
        </div>
    </div>

    <form method="POST" action="" data-detail-delete-form hidden>
        @csrf
        @method('DELETE')
    </form>

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

            const scheduleModal = document.querySelector('[data-schedule-modal]');
            if (scheduleModal) {
                const schoolInput = scheduleModal.querySelector('input[name="school_id"]');
                const schoolNameLabel = scheduleModal.querySelector('[data-modal-school]');
                const visitDateInput = scheduleModal.querySelector('input[name="visit_date"]');
                const visitTimeInput = scheduleModal.querySelector('input[name="visit_time"]');
                const picSelect = scheduleModal.querySelector('select[name="pic"]');
                const notesInput = scheduleModal.querySelector('textarea[name="notes"]');
                const openButtons = document.querySelectorAll('.schedule-button');
                const hasValidationErrors = scheduleModal.dataset.openOnLoad === 'true';
                let shouldResetFields = !hasValidationErrors;
                const setPicValue = (value) => {
                    if (!picSelect) {
                        return;
                    }
                    const normalized = typeof value === 'string' ? value.trim() : '';
                    if (normalized !== '') {
                        picSelect.value = normalized;
                        if (picSelect.value !== normalized) {
                            picSelect.selectedIndex = 0;
                        }
                    } else {
                        picSelect.selectedIndex = 0;
                    }
                };

                const openModal = (schoolId, schoolName, schoolPic) => {
                    if (shouldResetFields) {
                        visitDateInput.value = '';
                        visitTimeInput.value = '';
                        if (picSelect) {
                            picSelect.selectedIndex = 0;
                        }
                        notesInput.value = '';
                    }
                    schoolInput.value = schoolId;
                    schoolNameLabel.textContent = schoolName;
                    setPicValue(schoolPic);
                    scheduleModal.classList.add('is-visible');
                    visitDateInput.focus();
                    shouldResetFields = true;
                };

                const closeModal = () => {
                    scheduleModal.classList.remove('is-visible');
                };

                openButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        openModal(
                            button.dataset.schoolId,
                            button.dataset.schoolName,
                            button.dataset.schoolPic || ''
                        );
                    });
                });

                scheduleModal.addEventListener('click', (event) => {
                    if (event.target === scheduleModal || event.target.closest('[data-modal-close]')) {
                        closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && scheduleModal.classList.contains('is-visible')) {
                        closeModal();
                    }
                });

                if (hasValidationErrors) {
                    scheduleModal.classList.add('is-visible');
                }
            }

            const createSchoolModal = document.querySelector('[data-create-school-modal]');
            if (createSchoolModal) {
                const openButton = document.querySelector('[data-add-school-button]');
                const nameInput = createSchoolModal.querySelector('input[name="name"]');
                const shouldOpenOnLoad = createSchoolModal.dataset.openOnLoad === 'true';

                const openCreateModal = () => {
                    createSchoolModal.classList.add('is-visible');
                    if (nameInput) {
                        nameInput.focus();
                    }
                };

                const closeCreateModal = () => {
                    createSchoolModal.classList.remove('is-visible');
                };

                if (openButton) {
                    openButton.addEventListener('click', () => {
                        openCreateModal();
                    });
                }

                createSchoolModal.addEventListener('click', (event) => {
                    if (event.target === createSchoolModal || event.target.closest('[data-school-modal-close]')) {
                        closeCreateModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && createSchoolModal.classList.contains('is-visible')) {
                        closeCreateModal();
                    }
                });

                if (shouldOpenOnLoad) {
                    createSchoolModal.classList.add('is-visible');
                }
            }

            const detailModal = document.querySelector('[data-schedule-detail-modal]');
            if (detailModal) {
                const nameEl = detailModal.querySelector('[data-detail-school]');
                const dateEl = detailModal.querySelector('[data-detail-date]');
                const timeEl = detailModal.querySelector('[data-detail-time]');
                const notesEl = detailModal.querySelector('[data-detail-notes]');
                const picEl = detailModal.querySelector('[data-detail-pic]');
                const openDetailButtons = document.querySelectorAll('.schedule-detail-button');
                const deleteButton = detailModal.querySelector('[data-detail-delete]');
                const deleteForm = document.querySelector('[data-detail-delete-form]');
                let currentDetailPayload = null;

                if (deleteButton) {
                    deleteButton.disabled = true;
                }

                const formatDate = (date) => {
                    if (!date) {
                        return '-';
                    }
                    const formatter = new Intl.DateTimeFormat('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                    });
                    return formatter.format(new Date(date));
                };

                const formatTime = (time) => {
                    if (!time) {
                        return 'Belum ditentukan';
                    }
                    return time.slice(0, 5);
                };

                const formatPic = (pic) => {
                    if (typeof pic !== 'string') {
                        return '-';
                    }
                    const trimmed = pic.trim();
                    return trimmed !== '' ? trimmed : '-';
                };

                const openDetailModal = (payload) => {
                    currentDetailPayload = payload;
                    nameEl.textContent = payload.school || 'Nama sekolah tidak tersedia';
                    dateEl.textContent = formatDate(payload.visit_date);
                    timeEl.textContent = formatTime(payload.visit_time);
                    notesEl.textContent = payload.notes && payload.notes.trim() !== '' ? payload.notes : '-';
                    if (picEl) {
                        picEl.textContent = formatPic(payload.pic);
                    }
                    if (deleteForm) {
                        deleteForm.action = payload.delete_url || '';
                    }
                    if (deleteButton) {
                        deleteButton.disabled = !payload.delete_url;
                    }
                    detailModal.classList.add('is-visible');
                };

                openDetailButtons.forEach((btn) => {
                    const payload = btn.dataset.scheduleDetail ? JSON.parse(btn.dataset.scheduleDetail) : {};
                    btn.addEventListener('click', () => openDetailModal(payload));
                });

                const closeDetailModal = () => {
                    currentDetailPayload = null;
                    if (deleteForm) {
                        deleteForm.action = '';
                    }
                    if (deleteButton) {
                        deleteButton.disabled = true;
                    }
                    detailModal.classList.remove('is-visible');
                };

                detailModal.addEventListener('click', (event) => {
                    if (event.target === detailModal || event.target.closest('[data-detail-close]')) {
                        closeDetailModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && detailModal.classList.contains('is-visible')) {
                        closeDetailModal();
                    }
                });

                if (deleteButton && deleteForm) {
                    deleteButton.addEventListener('click', () => {
                        if (!currentDetailPayload || !currentDetailPayload.delete_url) {
                            return;
                        }
                        const confirmMessage = currentDetailPayload.school
                            ? `Hapus jadwal kunjungan ke ${currentDetailPayload.school}?`
                            : 'Hapus jadwal ini?';
                        if (window.confirm(confirmMessage)) {
                            deleteForm.submit();
                        }
                    });
                }
            }

            document.querySelectorAll('[data-school-delete-form]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const schoolName = form.dataset.schoolName || 'sekolah ini';
                    const message = `Hapus data ${schoolName}? Seluruh jadwal yang terkait juga akan dihapus.`;
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
</body>

</html>

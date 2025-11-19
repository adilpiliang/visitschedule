<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    private const PIC_TEAMS = [
        'Tim Hasan' => 'Tim Pak Hasan',
        'Tim Dwie' => 'Tim Pak Dwie',
        'Tim Fahmi' => 'Tim Pak Fahmi',
        'Tim Gabungan' => 'Tim Gabungan',
    ];

    /**
     * Show the calendar view with existing schedules.
     */
    public function index(): View
    {
        $statusColors = [
            'pending' => '#f59e0b',
            'confirmed' => '#2563eb',
            'completed' => '#22c55e',
        ];

        $events = Schedule::query()
            ->with('school:id,name,pic')
            ->orderBy('visit_date')
            ->orderBy('visit_time')
            ->get()
            ->map(function (Schedule $schedule) use ($statusColors) {
                $start = Carbon::parse($schedule->visit_date . ' ' . ($schedule->visit_time ?? '08:00'));
                $end = (clone $start)->addHours(2);

                return [
                    'id' => $schedule->id,
                    'title' => $schedule->notes ?: 'Kunjungan ' . ($schedule->school->name ?? 'Sekolah'),
                    'school' => $schedule->school->name ?? 'Sekolah',
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                    'color' => $statusColors[$schedule->status] ?? '#6366f1',
                    'status' => $schedule->status,
                    'notes' => $schedule->notes,
                    'pic' => $schedule->school->pic,
                    'complete_url' => route('schedule.complete', $schedule),
                ];
            });

        return view('schedule', [
            'events' => $events,
        ]);
    }

    /**
     * Display schedules in list format.
     */
    public function list(): View
    {
        $schedules = Schedule::query()
            ->with('school:id,name,kota,pic')
            ->orderByDesc('visit_date')
            ->orderByDesc('visit_time')
            ->paginate(15);

        return view('schedule-list', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Store a new schedule entry.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'visit_date' => ['required', 'date'],
            'visit_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
            'pic' => ['required', Rule::in(array_keys(self::PIC_TEAMS))],
        ]);

        $pic = trim($data['pic']);
        unset($data['pic']);

        $data['status'] = 'pending';
        $data['notes'] = $this->normalizeNullable($data['notes'] ?? null);
        $data['visit_time'] = $this->normalizeNullable($data['visit_time'] ?? null);

        $schedule = Schedule::create($data);

        $school = School::find($schedule->school_id);
        if ($school) {
            $school->pic = $pic;
            if ($school->status !== 'selesai') {
                $school->status = 'terjadwal';
            }
            $school->save();
        }

        return back()->with('status', 'Jadwal baru berhasil ditambahkan.');
    }

    /**
     * Update an existing schedule entry.
     */
    public function update(Request $request, Schedule $schedule): RedirectResponse
    {
        $data = $request->validate([
            'school_id' => ['required', 'integer', Rule::in([$schedule->school_id])],
            'visit_date' => ['required', 'date'],
            'visit_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
            'pic' => ['required', Rule::in(array_keys(self::PIC_TEAMS))],
        ]);

        $pic = trim($data['pic']);
        unset($data['pic']);

        $updates = [
            'visit_date' => $data['visit_date'],
            'visit_time' => $this->normalizeNullable($data['visit_time'] ?? null),
            'notes' => $this->normalizeNullable($data['notes'] ?? null),
        ];

        $schedule->update($updates);
        $schedule->load('school');

        if ($schedule->school) {
            $schedule->school->pic = $pic;
            if ($schedule->status !== 'completed') {
                $schedule->school->status = 'terjadwal';
            }
            $schedule->school->save();
        }

        return back()->with('status', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Mark a schedule as completed.
     */
    public function complete(Request $request, Schedule $schedule): RedirectResponse
    {
        if ($schedule->status === 'completed') {
            return back()->with('status', 'Jadwal sudah ditandai selesai.');
        }

        $schedule->update([
            'status' => 'completed',
        ]);

        $schedule->load('school');

        if ($schedule->school) {
            $hasPending = $schedule->school
                ->schedules()
                ->where('status', '!=', 'completed')
                ->exists();

            if (! $hasPending) {
                $schedule->school->update(['status' => 'selesai']);
            }
        }

        return back()->with('status', 'Jadwal berhasil ditandai selesai.');
    }

    /**
     * Remove a schedule entry.
     */
    public function destroy(Schedule $schedule): RedirectResponse
    {
        $schedule->load('school');
        $school = $schedule->school;

        $schedule->delete();

        if ($school) {
            $this->refreshSchoolStatus($school);
        }

        return back()->with('status', 'Jadwal berhasil dihapus.');
    }

    /**
     * Show schedules grouped by PIC/team.
     */
    public function pic(): View
    {
        $teamKeys = array_keys(self::PIC_TEAMS);

        $scheduleData = Schedule::query()
            ->with('school:id,name,kota,pic')
            ->orderBy('visit_date')
            ->orderBy('visit_time')
            ->get()
            ->map(function (Schedule $schedule) use ($teamKeys) {
                $pic = $schedule->school->pic ?? null;
                if (! in_array($pic, $teamKeys, true)) {
                    return null;
                }

                return [
                    'id' => $schedule->id,
                    'pic' => $pic,
                    'school' => $schedule->school->name ?? '-',
                    'city' => $schedule->school->kota ?? '-',
                    'visit_date' => $schedule->visit_date
                        ? Carbon::parse($schedule->visit_date)->translatedFormat('d F Y')
                        : '-',
                    'visit_time' => $schedule->visit_time
                        ? Str::substr($schedule->visit_time, 0, 5)
                        : '-',
                    'status' => $schedule->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $schedule->status)),
                    'notes' => $schedule->notes ?? '-',
                ];
            })
            ->filter()
            ->values();

        $picCounts = $scheduleData
            ->groupBy('pic')
            ->map(fn ($items) => $items->count());

        $cards = collect(self::PIC_TEAMS)->map(function (string $label, string $picKey) use ($picCounts) {
            return [
                'key' => $picKey,
                'label' => $label,
                'count' => $picCounts->get($picKey, 0),
            ];
        })->values();

        return view('pic', [
            'picCards' => $cards,
            'scheduleData' => $scheduleData,
            'defaultPic' => array_key_first(self::PIC_TEAMS),
        ]);
    }

    private function normalizeNullable(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function refreshSchoolStatus(School $school): void
    {
        $school->loadCount([
            'schedules',
            'schedules as pending_schedules_count' => function ($query) {
                $query->where('status', '!=', 'completed');
            },
        ]);

        if ($school->pending_schedules_count > 0) {
            $school->status = 'terjadwal';
        } elseif ($school->schedules_count > 0) {
            $school->status = 'selesai';
        } else {
            $school->status = 'baru';
        }

        $school->save();
    }
}

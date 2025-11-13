<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
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
            'pic' => ['required', Rule::in(['Tim Hasan', 'Tim Dwie', 'Tim Fahmi', 'Tim Gabungan'])],
        ]);

        $pic = trim($data['pic']);
        unset($data['pic']);

        $data['status'] = 'pending';

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
}

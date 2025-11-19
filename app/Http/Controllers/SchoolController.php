<?php

namespace App\Http\Controllers;

use App\Models\School;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    private const DEFAULT_SCOPE = 'kota-bogor';
    private const PIC_OPTIONS = ['Tim Hasan', 'Tim Dwie', 'Tim Fahmi','Tim Gabungan'];

    private const SCOPE_CONFIG = [
        'kota-bogor' => [
            'label' => 'Kota Bogor',
            'description' => 'Pilih kecamatan untuk melihat data sekolah',
            'table_description' => 'Gunakan filter kecamatan untuk melihat data spesifik.',
            'stat_label' => 'Kecamatan',
            'kota_matches' => ['kota bogor'],
            'show_dropdown' => true,
        ],
        'kabupaten-bogor' => [
            'label' => 'Kabupaten Bogor',
            'description' => 'Pilih kecamatan untuk melihat data sekolah',
            'table_description' => 'Gunakan filter kecamatan untuk melihat data spesifik.',
            'stat_label' => 'Kecamatan',
            'kota_matches' => ['kabupaten bogor'],
            'show_dropdown' => true,
        ],
        'others' => [
            'label' => 'Kota/Kabupaten lain',
            'description' => 'Data sekolah di luar Kota dan Kabupaten Bogor',
            'table_description' => 'Menampilkan seluruh sekolah selain dari Kota/Kabupaten Bogor.',
            'stat_label' => 'Kota/Kab',
            'kota_matches' => [],
            'show_dropdown' => false,
        ],
    ];

    /**
     * Display a listing of schools with optional filters.
     */
    public function index(Request $request)
    {
        $scope = Str::lower((string) $request->query('scope', self::DEFAULT_SCOPE));
        if (!array_key_exists($scope, self::SCOPE_CONFIG)) {
            $scope = self::DEFAULT_SCOPE;
        }

        $scopeConfig = self::SCOPE_CONFIG[$scope];

        $filters = [
            'scope' => $scope,
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
            'kecamatan' => trim((string) $request->query('kecamatan', '')),
        ];

        if (!($scopeConfig['show_dropdown'] ?? false)) {
            $filters['kecamatan'] = '';
        }

        $query = School::query()
            ->withCount('schedules')
            ->with('latestSchedule');
        $this->applyScope($query, $scope);

        if ($filters['search'] !== '') {
            $search = Str::lower($filters['search']);
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->whereRaw('LOWER(TRIM(name)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(TRIM(kota)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(TRIM(kecamatan)) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(TRIM(kelurahan)) LIKE ?', [$like]);
            });
        }

        if ($filters['status'] !== '') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', [Str::lower($filters['status'])]);
        }

        if ($filters['kecamatan'] !== '') {
            $query->whereRaw('LOWER(TRIM(kecamatan)) = ?', [Str::lower($filters['kecamatan'])]);
        }

        $schools = $this->enrichSchools($query->orderBy('name')->get());
        $kecamatanOptions = $scopeConfig['show_dropdown'] ? $this->buildKecamatanOptions($scope) : collect();

        if ($request->expectsJson()) {
            return $this->toJsonResponse($schools);
        }

        $cards = $this->buildCardStats($scope, $filters);
        $routeBuilder = $this->makeRouteBuilder($filters);

        return view('school', [
            'schools' => $schools,
            'filters' => $filters,
            'kecamatanOptions' => $kecamatanOptions,
            'cardStats' => $cards,
            'totalSchools' => School::query()->count(),
            'currentScope' => $scope,
            'currentScopeLabel' => $scopeConfig['label'],
            'tableDescription' => $scopeConfig['table_description'],
            'showKecamatanDropdown' => $scopeConfig['show_dropdown'],
            'selectedKecamatan' => $filters['kecamatan'] ?? '',
            'routeBuilder' => $routeBuilder,
            'picOptions' => self::PIC_OPTIONS,
            'oldSchoolName' => $this->resolveOldSchoolName($request, $schools),
            'openCreateSchoolModal' => $this->shouldOpenCreateSchoolModal(),
        ]);
    }

    /**
     * Store a newly created school record.
     */
    public function store(Request $request)
    {
        $data = $request->validateWithBag('schoolStore', [
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'kota' => ['required', 'string', 'max:255'],
            'kecamatan' => ['required', 'string', 'max:255'],
            'kelurahan' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'contact' => ['nullable', 'string', 'max:30'],
            'maps' => ['nullable', 'string', 'max:500', 'url'],
        ]);

        School::create([
            'name' => $this->uppercaseValue($data['name']),
            'kota' => $this->uppercaseValue($data['kota']),
            'kecamatan' => $this->uppercaseValue($data['kecamatan']),
            'kelurahan' => $this->sanitizeNullableString($data['kelurahan'] ?? null),
            'address' => trim($data['address']),
            'maps' => $this->sanitizeNullableString($data['maps'] ?? null),
            'contact' => $this->formatContactNumber($data['contact'] ?? null),
            'status' => 'baru',
        ]);

        return back()->with('status', 'Sekolah baru berhasil ditambahkan.');
    }

    /**
     * Display a single school record.
     */
    public function show(Request $request, School $school)
    {
        $scope = Str::lower((string) $request->query('scope', self::DEFAULT_SCOPE));
        if (!array_key_exists($scope, self::SCOPE_CONFIG)) {
            $scope = self::DEFAULT_SCOPE;
        }

        $scopeConfig = self::SCOPE_CONFIG[$scope];

        $filters = [
            'scope' => $scope,
            'search' => trim((string) $request->query('search', '')),
            'status' => trim((string) $request->query('status', '')),
            'kecamatan' => trim((string) $request->query('kecamatan', '')),
        ];

        if (!($scopeConfig['show_dropdown'] ?? false)) {
            $filters['kecamatan'] = '';
        }

        if ($request->expectsJson()) {
            return $this->toJsonResponse($school);
        }

        $school->loadCount('schedules');
        $school->loadCount('schedules')->load('latestSchedule');
        $schools = $this->enrichSchools(collect([$school]));
        $kecamatanOptions = $scopeConfig['show_dropdown'] ? $this->buildKecamatanOptions($scope) : collect();
        $cards = $this->buildCardStats($scope, $filters);
        $routeBuilder = $this->makeRouteBuilder($filters);

        return view('school', [
            'schools' => $schools,
            'filters' => $filters,
            'kecamatanOptions' => $kecamatanOptions,
            'cardStats' => $cards,
            'totalSchools' => School::query()->count(),
            'currentScope' => $scope,
            'currentScopeLabel' => $scopeConfig['label'],
            'tableDescription' => $scopeConfig['table_description'],
            'showKecamatanDropdown' => $scopeConfig['show_dropdown'],
            'selectedKecamatan' => $filters['kecamatan'] ?? '',
            'routeBuilder' => $routeBuilder,
            'picOptions' => self::PIC_OPTIONS,
            'oldSchoolName' => $this->resolveOldSchoolName($request, $schools),
            'openCreateSchoolModal' => $this->shouldOpenCreateSchoolModal(),
        ]);
    }

    protected function applyScope(Builder $query, string $scope): void
    {
        $config = self::SCOPE_CONFIG[$scope] ?? self::SCOPE_CONFIG[self::DEFAULT_SCOPE];
        $matches = array_map(static fn($value) => Str::lower(trim($value)), $config['kota_matches'] ?? []);

        if (!empty($matches)) {
            $query->where(function (Builder $inner) use ($matches) {
                foreach ($matches as $value) {
                    $inner->orWhereRaw('LOWER(TRIM(kota)) = ?', [$value]);
                }
            });
        } else {
            $excluded = $this->primaryCityMatches();
            if (!empty($excluded)) {
                $query->whereNotIn(DB::raw('LOWER(TRIM(kota))'), $excluded);
            }
        }
    }

    protected function buildKecamatanOptions(string $scope)
    {
        $query = School::query()
            ->select('kecamatan')
            ->whereNotNull('kecamatan')
            ->whereRaw("TRIM(kecamatan) <> ''");

        $this->applyScope($query, $scope);

        return $query
            ->pluck('kecamatan')
            ->map(function (?string $value) {
                $value = trim((string) $value);
                if ($value === '') {
                    return null;
                }

                $value = preg_replace('/\s+/', ' ', $value);

                return Str::title(Str::lower($value));
            })
            ->filter()
            ->unique(fn($value) => Str::lower($value))
            ->sortBy(fn($value) => Str::lower($value))
            ->values();
    }

    protected function buildCardStats(string $currentScope, array $filters)
    {
        $baseQuery = [
            'search' => $filters['search'] !== '' ? $filters['search'] : null,
            'status' => $filters['status'] !== '' ? $filters['status'] : null,
        ];

        $cards = [];

        foreach (self::SCOPE_CONFIG as $key => $config) {
            $stat = $this->calculateScopeStat($key);
            $cards[] = [
                'key' => $key,
                'label' => $config['label'],
                'description' => $config['description'],
                'stat' => $stat,
                'stat_label' => $config['stat_label'],
                'active' => $key === $currentScope,
                'href' => route('schools.index', array_filter(array_merge($baseQuery, [
                    'scope' => $key,
                ]))),
            ];
        }

        return $cards;
    }

    protected function makeRouteBuilder(array $filters): Closure
    {
        $baseQuery = $this->baseRouteQuery($filters);

        return function (array $overrides = []) use ($baseQuery): array {
            $query = array_merge($baseQuery, $overrides);

            return array_filter($query, fn($value) => !is_null($value) && $value !== '');
        };
    }

    protected function baseRouteQuery(array $filters): array
    {
        return collect($filters)
            ->except(['kecamatan'])
            ->filter(fn($value) => !is_null($value) && $value !== '')
            ->all();
    }

    protected function calculateScopeStat(string $scope): int
    {
        $config = self::SCOPE_CONFIG[$scope] ?? self::SCOPE_CONFIG[self::DEFAULT_SCOPE];
        $query = School::query();
        $this->applyScope($query, $scope);

        $field = Str::lower($config['stat_label']) === 'kecamatan' ? 'kecamatan' : 'kota';
        $fieldExpr = $field === 'kecamatan' ? 'LOWER(TRIM(kecamatan))' : 'LOWER(TRIM(kota))';

        return (int) $query
            ->whereNotNull($field)
            ->whereRaw("TRIM({$field}) <> ''")
            ->distinct()
            ->count(DB::raw($fieldExpr));
    }

    protected function primaryCityMatches(): array
    {
        $cities = [];

        foreach (self::SCOPE_CONFIG as $key => $config) {
            if ($key === 'others') {
                continue;
            }

            foreach ($config['kota_matches'] ?? [] as $value) {
                $cities[] = Str::lower(trim($value));
            }
        }

        return array_values(array_unique($cities));
    }

    /**
     * Shape a consistent JSON response payload.
     */
    protected function toJsonResponse($data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    protected function uppercaseValue(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : Str::upper($value);
    }

    protected function sanitizeNullableString(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    protected function formatContactNumber(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return null;
        }

        $digits = ltrim($digits, '0');
        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '62')) {
            return '62' . substr($digits, 2);
        }

        if (Str::startsWith($digits, '8')) {
            return '62' . $digits;
        }

        return '62' . $digits;
    }

    protected function shouldOpenCreateSchoolModal(): bool
    {
        $errors = session('errors');
        if (!$errors instanceof \Illuminate\Support\ViewErrorBag) {
            return false;
        }

        return $errors->hasBag('schoolStore') && $errors->getBag('schoolStore')->any();
    }

    protected function resolveOldSchoolName(Request $request, \Illuminate\Support\Collection $schools): ?string
    {
        $oldSchoolId = (int) $request->old('school_id', 0);
        if ($oldSchoolId === 0) {
            return null;
        }

        $school = $schools->firstWhere('id', $oldSchoolId) ?? School::find($oldSchoolId);

        return $school?->name;
    }

    protected function enrichSchools(\Illuminate\Support\Collection $schools): \Illuminate\Support\Collection
    {
        return $schools->map(function (School $school) {
            $school->setAttribute('status_badge_class', $this->statusBadgeClass($school->status));
            $school->setAttribute('latest_schedule_payload', $this->buildLatestSchedulePayload($school));

            return $school;
        });
    }

    protected function statusBadgeClass(?string $status): string
    {
        return match (Str::lower(trim((string) $status))) {
            'baru' => 'badge-status badge-status-new',
            'pending' => 'badge-status badge-status-pending',
            'terjadwal' => 'badge-status badge-status-scheduled',
            'selesai' => 'badge-status badge-status-completed',
            default => 'badge-status',
        };
    }

    protected function buildLatestSchedulePayload(School $school): ?array
    {
        $schedule = $school->latestSchedule;
        if (!$schedule) {
            return null;
        }

        return [
            'school' => $school->name,
            'visit_date' => $schedule->visit_date,
            'visit_time' => $schedule->visit_time,
            'notes' => $schedule->notes,
            'schedule_id' => $schedule->id,
            'pic' => $school->pic,
            'delete_url' => route('schedule.destroy', $schedule),
        ];
    }
}

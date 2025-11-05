<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    private const DEFAULT_SCOPE = 'kota-bogor';

    private const SCOPE_CONFIG = [
        'kota-bogor' => [
            'label' => 'Kota Bogor',
            'description' => 'Pilih kecamatan untuk melihat data sekolah',
            'table_description' => 'Filter berdasarkan kecamatan di Kota Bogor.',
            'stat_label' => 'Kecamatan',
            'kota_matches' => ['kota bogor'],
            'show_dropdown' => true,
        ],
        'kabupaten-bogor' => [
            'label' => 'Kabupaten Bogor',
            'description' => 'Data sekolah yang terdaftar di Kabupaten Bogor',
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

        $query = School::query();
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

        $schools = $query->orderBy('name')->get();
        $kecamatanOptions = $scopeConfig['show_dropdown'] ? $this->buildKecamatanOptions($scope) : collect();

        if ($request->expectsJson()) {
            return $this->toJsonResponse($schools);
        }

        $cards = $this->buildCardStats($scope, $filters);

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
        ]);
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

        $kecamatanOptions = $scopeConfig['show_dropdown'] ? $this->buildKecamatanOptions($scope) : collect();
        $cards = $this->buildCardStats($scope, $filters);

        return view('school', [
            'schools' => collect([$school]),
            'filters' => $filters,
            'kecamatanOptions' => $kecamatanOptions,
            'cardStats' => $cards,
            'totalSchools' => School::query()->count(),
            'currentScope' => $scope,
            'currentScopeLabel' => $scopeConfig['label'],
            'tableDescription' => $scopeConfig['table_description'],
            'showKecamatanDropdown' => $scopeConfig['show_dropdown'],
        ]);
    }

    protected function applyScope(Builder $query, string $scope): void
    {
        $config = self::SCOPE_CONFIG[$scope] ?? self::SCOPE_CONFIG[self::DEFAULT_SCOPE];
        $matches = array_map(static fn ($value) => Str::lower(trim($value)), $config['kota_matches'] ?? []);

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
            ->distinct()
            ->orderByRaw('LOWER(TRIM(kecamatan))')
            ->pluck('kecamatan');
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
}

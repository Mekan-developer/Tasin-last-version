<?php

namespace App\Repositories;

use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ActivityLog::with('product:id,name')
            ->when($filters['search'] ?? '', fn ($q, $s) =>
                $q->where(fn ($inner) =>
                    $inner->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$s}%"))
                          ->orWhere('region', 'like', "%{$s}%")
                )
            )
            ->when($filters['region'] ?? '', fn ($q, $r) => $q->where('region', $r))
            ->when($filters['device'] ?? '', fn ($q, $d) => $q->where('device', $d))
            ->when($filters['period'] ?? '', function ($q, $p) {
                match ($p) {
                    'today'  => $q->whereDate('created_at', today()),
                    '7days'  => $q->where('created_at', '>=', now()->subDays(7)),
                    '30days' => $q->where('created_at', '>=', now()->subDays(30)),
                    default  => null,
                };
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function stats(): array
    {
        $total  = ActivityLog::count();
        $unique = ActivityLog::distinct('session')->count('session');
        $topReg = ActivityLog::selectRaw('region, count(*) as cnt')
            ->groupBy('region')
            ->orderByDesc('cnt')
            ->value('region') ?? '—';

        return [
            'total'     => $total,
            'unique'    => $unique,
            'topRegion' => $topReg,
        ];
    }
}

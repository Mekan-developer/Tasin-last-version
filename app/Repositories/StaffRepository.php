<?php

namespace App\Repositories;

use App\Models\SalaryTransaction;
use App\Models\Staff;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StaffRepository
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return Staff::when($filters['search'] ?? '', fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($filters['department'] ?? '', fn ($q, $d) => $q->where('department', $d))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function stats(): array
    {
        $active = Staff::where('is_active', true)->get(['salary', 'paid', 'debt']);
        return [
            'fund'      => (float) $active->sum('salary'),
            'paid'      => (float) $active->sum('paid'),
            'remaining' => (float) $active->sum(fn ($s) => max(0, $s->salary - $s->paid)),
            'debt'      => (float) $active->sum('debt'),
            'count'     => $active->count(),
        ];
    }

    public function create(array $data): Staff
    {
        return Staff::create($data);
    }

    public function update(Staff $staff, array $data): void
    {
        $staff->update($data);
    }

    public function delete(Staff $staff): void
    {
        $staff->delete();
    }

    public function pay(Staff $staff, float $amount, string $type): void
    {
        SalaryTransaction::create([
            'staff_id'   => $staff->id,
            'amount'     => $amount,
            'type'       => $type,
            'created_at' => now(),
        ]);

        match ($type) {
            'salary'   => $staff->increment('paid', $amount),
            'advance'  => $staff->increment('debt', $amount),
            'debt_pay' => $staff->decrement('debt', $amount),
            default    => null,
        };
    }
}
